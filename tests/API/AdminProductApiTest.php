<?php

namespace EscolaLms\Cart\Tests\API;

use EscolaLms\Cart\Database\Seeders\CartPermissionSeeder;
use EscolaLms\Cart\Events\ProductableAttached;
use EscolaLms\Cart\Events\ProductableDetached;
use EscolaLms\Cart\Events\ProductAttached;
use EscolaLms\Cart\Events\ProductDetached;
use EscolaLms\Cart\Facades\Shop;
use EscolaLms\Cart\Http\Resources\ProductResource;
use EscolaLms\Cart\Models\Category;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\ProductProductable;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Cart\Tests\Mocks\ExampleProductable;
use EscolaLms\Cart\Tests\Mocks\ExampleProductableBase;
use EscolaLms\Cart\Tests\TestCase;
use EscolaLms\Core\Enums\UserRole;
use Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;

class AdminProductApiTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private TestResponse $response;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(CartPermissionSeeder::class);
        Shop::registerProductableClass(ExampleProductable::class);

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole(UserRole::ADMIN);
    }

    public function test_attach_and_detach_product()
    {
        $event = Event::fake();

        /** @var Product $product */
        $product = Product::factory()->create();
        $student = config('auth.providers.users.model')::factory()->create();
        $student->guard_name = 'api';
        $student->assignRole(UserRole::STUDENT);

        $this->assertFalse($product->getOwnedByUserAttribute($student));

        $this->response = $this->actingAs($this->user, 'api')->json('POST', '/api/admin/products/' . $product->getKey() . '/attach', [
            'user_id' => $student->getKey(),
        ]);
        $this->response->assertOk();

        $product->refresh();
        $this->assertTrue($product->getOwnedByUserAttribute($student));

        $event->assertDispatched(ProductAttached::class);

        $this->response = $this->actingAs($this->user, 'api')->json('POST', '/api/admin/products/' . $product->getKey() . '/detach', [
            'user_id' => $student->getKey(),
        ]);
        $this->response->assertOk();

        $product->refresh();
        $this->assertFalse($product->getOwnedByUserAttribute($student));

        $event->assertDispatched(ProductDetached::class);
    }

    public function test_attach_and_detach_productable()
    {
        $eventFake = Event::fake();

        /** @var Product $product */
        $product = Product::factory()->create();
        /** @var ExampleProductable $productable */
        $productable = ExampleProductable::factory()->create();
        $product->productables()->save(new ProductProductable([
            'productable_type' => $productable->getMorphClass(),
            'productable_id' => $productable->getKey()
        ]));

        $student = config('auth.providers.users.model')::factory()->create();
        $student->guard_name = 'api';
        $student->assignRole(UserRole::STUDENT);

        $this->assertFalse($productable->getOwnedByUserAttribute($student));

        $this->response = $this->actingAs($this->user, 'api')->json('POST', '/api/admin/productables/attach', [
            'user_id' => $student->getKey(),
            'productable_id' => $productable->getKey(),
            'productable_type' => ExampleProductable::class,
        ]);
        $this->response->assertOk();

        $product->refresh();
        $this->assertTrue($productable->getOwnedByUserAttribute($student));
        $this->assertTrue(app(ProductServiceContract::class)->productIsOwnedByUser($product, $student, true));

        $eventFake->assertDispatched(ProductableAttached::class, fn (ProductableAttached $event) => $event->getProductable()->getKey() === $productable->getKey());

        $this->response = $this->actingAs($this->user, 'api')->json('POST', '/api/admin/productables/detach', [
            'user_id' => $student->getKey(),
            'productable_id' => $productable->getKey(),
            'productable_type' => ExampleProductable::class,
        ]);
        $this->response->assertOk();

        $product->refresh();
        $this->assertFalse($productable->getOwnedByUserAttribute($student));

        $eventFake->assertDispatched(ProductableDetached::class, fn (ProductableDetached $event) => $event->getProductable()->getKey() === $productable->getKey());
    }

    public function test_create_product()
    {
        /** @var ExampleProductable $productable */
        $productable = ExampleProductable::factory()->create();

        $productData = Product::factory()->make()->toArray();
        $productData['productables'] = [
            [
                'class' => ExampleProductable::class,
                'id' => $productable->getKey()
            ]
        ];

        /** @var Category $category */
        $category = Category::create(['name' => 'test']);
        /** @var Category $category2 */
        $category2 = Category::create(['name' => 'test2']);

        $productData['categories'] = [$category->getKey(), $category2->getKey()];

        $this->response = $this->actingAs($this->user, 'api')->json('POST', '/api/admin/products', $productData);
        $this->response->assertCreated();

        $productId = $this->response->json()['data']['id'];
        $product = Product::find($productId);

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/products', ['productable_id' => $productable->getKey(), 'productable_type' => $productable->getMorphClass()]);
        $this->response->assertOk();

        $this->response->assertJsonFragment([
            ProductResource::make($product)->toArray(null),
        ]);
    }

    public function test_update_product()
    {
        $product = Product::factory()->single()->create();

        $productData = Product::factory()->single()->make()->toArray();

        $this->response = $this->actingAs($this->user, 'api')->json('PUT', '/api/admin/products/' . $product->getKey(), $productData);
        $this->response->assertOk();
    }

    public function test_search_products()
    {
        $user = $this->user;

        /** @var Product $product */
        $product = Product::factory()->create();
        $productable = ExampleProductable::factory()->create();
        $product->productables()->save(new ProductProductable([
            'productable_type' => $productable->getMorphClass(),
            'productable_id' => $productable->getKey()
        ]));
        /** @var Product $product2 */
        $product2 = Product::factory()->create();
        $productable2 = ExampleProductable::factory()->create();
        $product2->productables()->save(new ProductProductable([
            'productable_type' => $productable->getMorphClass(),
            'productable_id' => $productable2->getKey()
        ]));
        /** @var Product $product2 */
        $product3 = Product::factory()->create(['purchasable' => false]);
        $productable3 = ExampleProductable::factory()->create();
        $product3->productables()->save(new ProductProductable([
            'productable_type' => $productable->getMorphClass(),
            'productable_id' => $productable3->getKey()
        ]));

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/admin/products', ['productable_type' => ExampleProductable::class]);
        $this->response->assertOk();

        $this->response->assertJsonFragment([
            ProductResource::make($product->refresh())->toArray(null),
        ]);
        $this->response->assertJsonFragment([
            ProductResource::make($product2->refresh())->toArray(null),
        ]);
        $this->response->assertJsonFragment([
            ProductResource::make($product3->refresh())->toArray(null),
        ]);

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/admin/products', ['productable_id' => $productable->getKey(), 'productable_type' => ExampleProductable::class]);
        $this->response->assertOk();

        $this->response->assertJsonFragment([
            ProductResource::make($product->refresh())->toArray(null),
        ]);
        $this->response->assertJsonMissing([
            ProductResource::make($product2->refresh())->toArray(null),
        ]);
        $this->response->assertJsonMissing([
            ProductResource::make($product3->refresh())->toArray(null),
        ]);
    }

    public function test_get_registered_productables_list()
    {
        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/productables/registered');
        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'data' => [
                ExampleProductableBase::class => ExampleProductable::class
            ]
        ]);
    }

    public function test_list_productables()
    {
        $productables = ExampleProductable::factory()->count(10)->create();

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/productables/');
        $this->response->assertOk();
        $this->response->assertJsonCount(10, 'data');
        $this->response->assertJsonFragment([
            'productable_id' => $productables->get(0)->id,
            'productable_type' => ExampleProductable::class,
            'name' => $productables->get(0)->getName(),
        ]);
    }

    public function test_find_single_product_for_productable()
    {
        /** @var Product $product */
        $product = Product::factory()->create();
        $productable = ExampleProductable::factory()->create();
        $product->productables()->save(new ProductProductable([
            'productable_type' => $productable->getMorphClass(),
            'productable_id' => $productable->getKey()
        ]));

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/productables/product', [
            'productable_type' => ExampleProductable::class,
            'productable_id' => $productable->getKey(),
        ]);
        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'data' => ProductResource::make($product->refresh())->toArray(null)
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/productables/product', [
            'productable_type' => ExampleProductableBase::class,
            'productable_id' => $productable->getKey(),
        ]);
        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'data' => ProductResource::make($product->refresh())->toArray(null)
        ]);
    }
}
