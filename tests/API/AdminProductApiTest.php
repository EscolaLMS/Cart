<?php

namespace EscolaLms\Cart\Tests\API;

use EscolaLms\Auth\Database\Seeders\AuthPermissionSeeder;
use EscolaLms\Cart\Database\Seeders\CartPermissionSeeder;
use EscolaLms\Cart\Enums\ConstantEnum;
use EscolaLms\Cart\Enums\ProductType;
use EscolaLms\Cart\EscolaLmsCartServiceProvider;
use EscolaLms\Cart\Events\ProductableAttached;
use EscolaLms\Cart\Events\ProductableDetached;
use EscolaLms\Cart\Events\ProductAttached;
use EscolaLms\Cart\Events\ProductBought;
use EscolaLms\Cart\Events\ProductDetached;
use EscolaLms\Cart\Facades\Shop;
use EscolaLms\Cart\Http\Resources\BaseProductResource;
use EscolaLms\Cart\Http\Resources\ProductDetailedResource;
use EscolaLms\Cart\Http\Resources\ProductResource;
use EscolaLms\Cart\Models\Category;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\ProductProductable;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Cart\Tests\Mocks\ExampleProductable;
use EscolaLms\Cart\Tests\Mocks\ExampleProductableBase;
use EscolaLms\Cart\Tests\TestCase;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Payments\Facades\PaymentGateway;
use Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Illuminate\Testing\Fluent\AssertableJson;

class AdminProductApiTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private TestResponse $response;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(AuthPermissionSeeder::class);
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

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/products/' . $product->getKey());
        $this->response->assertOk();

        $this->response->assertJsonFragment(ProductDetailedResource::make($product)->toArray(null));

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

    public function test_detach_bought_productable()
    {
        $eventFake = Event::fake(ProductBought::class, ProductableDetached::class);
        $paymentsFake = PaymentGateway::fake();

        $student = config('auth.providers.users.model')::factory()->create();
        $student->guard_name = 'api';
        $student->assignRole(UserRole::STUDENT);

        /** @var Product $product */
        $product = Product::factory()->create([
            'price' => 1000,
            'purchasable' => true,
        ]);
        /** @var ExampleProductable $productable */
        $productable = ExampleProductable::factory()->create();
        $product->productables()->save(new ProductProductable([
            'productable_type' => $productable->getMorphClass(),
            'productable_id' => $productable->getKey()
        ]));

        $shopService = app(ShopServiceContract::class);
        $cart = $shopService->cartForUser($student);
        $shopService->addProductToCart($cart, $product);

        $this->response = $this->actingAs($student, 'api')->json('POST', '/api/cart/pay');
        $this->response->assertCreated();

        $eventFake->assertDispatched(ProductBought::class, fn (ProductBought $event) => $event->getProduct()->getKey() === $product->getKey());

        $product->refresh();

        $this->assertTrue($product->getOwnedByUserAttribute($student));

        $this->response = $this->actingAs($this->user, 'api')->json('POST', '/api/admin/productables/detach', [
            'user_id' => $student->getKey(),
            'productable_id' => $productable->getKey(),
            'productable_type' => ExampleProductable::class,
        ]);
        $this->response->assertForbidden();

        $eventFake->assertNotDispatched(ProductableDetached::class);
    }

    public function test_create_product()
    {
        /** @var ExampleProductable $productable */
        $productable = ExampleProductable::factory()->create();

        $productData = Product::factory()->make()->toArray();

        $productSecoond = Product::factory()->single()->create();
        $productSecoond->relatedProducts()->sync(Product::factory(5)->create()->pluck('id')->toArray());

        $productData = Product::factory()->single()->make()->toArray();
        $productData['related_products'] = array_merge(Product::factory(5)->create()->pluck('id')->toArray(), [$productSecoond->getKey()]);

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

        $productData['tags'] = ['tag1', 'tag2'];

        $this->response = $this->actingAs($this->user, 'api')->json('POST', '/api/admin/products', $productData);
        $this->response->assertCreated();
        $this->response->assertJson(
            fn (AssertableJson $json) =>
            $json->has(
                'data',
                fn (AssertableJson $json) =>
                $json
                    ->has(
                        'related_products',
                        fn (AssertableJson $json) =>
                        $json->each(
                            fn (AssertableJson $json) =>
                            $json
                                ->where('id', fn ($id) => in_array($id, $productData['related_products']))
                                ->missing('related_products')
                                ->etc()
                        )->etc()
                    )->etc()
            )->etc()
        );

        $productId = $this->response->json()['data']['id'];
        $product = Product::find($productId);

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/products', ['productable_id' => $productable->getKey(), 'productable_type' => $productable->getMorphClass()]);
        $this->response->assertOk();
        $this->response->assertJsonFragment(json_decode(ProductResource::make($product)->toJson(null), true));
    }

    public function test_create_product_min_price()
    {
        Config::set(EscolaLmsCartServiceProvider::CONFIG_KEY . '.min_product_price', 10);
        /** @var ExampleProductable $productable */
        $productable = ExampleProductable::factory()->create();

        $productData = Product::factory()->single()->make(['price' => 5, 'price_old' => 8])->toArray();

        $productData['productables'] = [
            [
                'class' => ExampleProductable::class,
                'id' => $productable->getKey()
            ]
        ];

        /** @var Category $category */
        $category = Category::create(['name' => 'test']);

        $productData['categories'] = [$category->getKey()];

        $this->response = $this->actingAs($this->user, 'api')->json('POST', '/api/admin/products', $productData);
        $this->response->assertUnprocessable();
        $this->response->assertJson(
            fn (AssertableJson $json) =>
            $json
                ->where('message', 'The given data was invalid.')
                ->where('errors', [
                    'price' => ['Field price must be greater or equal than 10.'],
                    'price_old' => ['Field price old must be greater or equal than 10.'],
                ])
                ->etc()
        );
        Config::set(EscolaLmsCartServiceProvider::CONFIG_KEY . '.min_product_price', 0);
    }

    public function test_update_product_min_price()
    {
        Config::set(EscolaLmsCartServiceProvider::CONFIG_KEY . '.min_product_price', 10);
        /** @var ExampleProductable $productable */
        $productable = ExampleProductable::factory()->create();

        $product = Product::factory()->single()->create();
        $productData = Product::factory()->single()->make(['price' => 5, 'price_old' => 8])->toArray();

        $productData['productables'] = [
            [
                'class' => ExampleProductable::class,
                'id' => $productable->getKey()
            ]
        ];

        /** @var Category $category */
        $category = Category::create(['name' => 'test']);

        $productData['categories'] = [$category->getKey()];

        $this->response = $this->actingAs($this->user, 'api')->json('PUT', '/api/admin/products/' . $product->getKey(), $productData);
        $this->response->assertUnprocessable();
        $this->response->assertJson(
            fn (AssertableJson $json) =>
            $json
                ->where('message', 'The given data was invalid.')
                ->where('errors', [
                    'price' => ['Field price must be greater or equal than 10.'],
                    'price_old' => ['Field price old must be greater or equal than 10.'],
                ])
                ->etc()
        );
        Config::set(EscolaLmsCartServiceProvider::CONFIG_KEY . '.min_product_price', 0);
    }

    public function test_update_product()
    {
        $product = Product::factory()->single()->create();
        $productSecoond = Product::factory()->single()->create();
        $productSecoond->relatedProducts()->sync(Product::factory(5)->create()->pluck('id')->toArray());

        $productData = Product::factory()->single()->make()->toArray();
        $productData['related_products'] = array_merge(Product::factory(5)->create()->pluck('id')->toArray(), [$productSecoond->getKey()]);
        $this->response = $this->actingAs($this->user, 'api')->json('PUT', '/api/admin/products/' . $product->getKey(), $productData);
        $this->response->assertOk();
        $this->response->assertJson(
            fn (AssertableJson $json) =>
            $json->has(
                'data',
                fn (AssertableJson $json) =>
                $json
                    ->where('id', fn (int $id) => $id === $product->getKey())
                    ->has(
                        'related_products',
                        fn (AssertableJson $json) =>
                        $json->each(
                            fn (AssertableJson $json) =>
                            $json
                                ->where('id', fn ($id) => in_array($id, $productData['related_products']))
                                ->missing('related_products')
                                ->etc()
                        )
                            ->etc()
                    )->etc()
            )->etc()
        );
    }

    public function test_update_product_quantity()
    {
        /** @var ExampleProductable $productable */
        $productable = ExampleProductable::factory()->create();

        /** @var ExampleProductable $productable */
        $productable2 = ExampleProductable::factory()->create();

        /** @var Product $product */
        $product = Product::factory()->bundle()->create();

        $product->productables()->create([
            'productable_id' => $productable->getKey(),
            'productable_type' => $productable->getMorphClass(),
        ]);
        $product->productables()->create([
            'productable_id' => $productable2->getKey(),
            'productable_type' => $productable2->getMorphClass(),
        ]);

        $productSecond = Product::factory()->bundle()->create();
        $productSecond->relatedProducts()->sync(Product::factory(5)->create()->pluck('id')->toArray());

        $productData = Product::factory()->bundle()->make()->toArray();
        $productData['related_products'] = array_merge(Product::factory(5)->create()->pluck('id')->toArray(), [$productSecond->getKey()]);
        $productData['productables'] = [
            [
                'id' => $productable->getKey(),
                'class' => ExampleProductable::class,
                'quantity' => 2
            ]
        ];

        $this->response = $this->actingAs($this->user, 'api')->json('PUT', '/api/admin/products/' . $product->getKey(), $productData);
        $this->response->assertOk();

        $this->response->assertJson(
            fn (AssertableJson $json) =>
            $json->has(
                'data',
                fn (AssertableJson $json) =>
                $json->where('id', fn (int $id) => $id === $product->getKey())
                    ->has(
                        'productables',
                        fn (AssertableJson $json) =>
                        $json->first(
                            fn (AssertableJson $json) =>
                            $json->where('productable_id', $productable->getKey())
                                ->where('quantity', 2)
                                ->etc()
                        )->etc()
                    )
                    ->has(
                        'related_products',
                        fn (AssertableJson $json) =>
                        $json->each(
                            fn (AssertableJson $json) =>
                            $json->where('id', fn ($id) => in_array($id, $productData['related_products']))
                                ->missing('related_products')
                                ->etc()
                        )
                            ->etc()
                    )->etc()
            )->etc()
        );
    }

    public function test_search_products()
    {
        $user = $this->user;

        /** @var Product $product */
        $product = Product::factory()->create([
            'name' => 'First Second Third',
        ]);
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

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/admin/products', ['name' => 'Second']);
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

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/admin/products', ['type' => ProductType::SINGLE]);
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

    public function test_search_products_with_sort()
    {
        $user = $this->user;
        $productable = ExampleProductable::factory()->create();

        /** @var Product $product */
        $productOne = Product::factory()->create([
            'name' => 'First',
            'price' => 300,
            'price_old' => 310,
            'tax_rate' => 30,
        ]);
        $productOne->productables()->save(new ProductProductable([
            'productable_type' => $productable->getMorphClass(),
            'productable_id' => $productable->getKey()
        ]));

        $productTwo = Product::factory()->create([
            'name' => 'Second',
            'price' => 100,
            'price_old' => 110,
            'tax_rate' => 10,
        ]);
        $productTwo->productables()->save(new ProductProductable([
            'productable_type' => $productable->getMorphClass(),
            'productable_id' => $productable->getKey()
        ]));

        $productThree = Product::factory()->create([
            'name' => 'Third',
            'price' => 200,
            'price_old' => 210,
            'tax_rate' => 20,
        ]);

        $productThree->productables()->save(new ProductProductable([
            'productable_type' => $productable->getMorphClass(),
            'productable_id' => $productable->getKey()
        ]));

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/admin/products', ['order_by' => 'price', 'order' => 'ASC']);

        $this->response->assertOk();

        $this->assertTrue($this->response->json('data.0.price') === $productTwo->price);
        $this->assertTrue($this->response->json('data.1.price') === $productThree->price);
        $this->assertTrue($this->response->json('data.2.price') === $productOne->price);

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/admin/products', ['order_by' => 'price_old', 'order' => 'ASC']);

        $this->response->assertOk();

        $this->assertTrue($this->response->json('data.0.price_old') === $productTwo->price_old);
        $this->assertTrue($this->response->json('data.1.price_old') === $productThree->price_old);
        $this->assertTrue($this->response->json('data.2.price_old') === $productOne->price_old);
        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/admin/products', ['order_by' => 'tax_rate', 'order' => 'ASC']);

        $this->response->assertOk();

        $this->assertTrue($this->response->json('data.0.tax_rate') === $productTwo->tax_rate);
        $this->assertTrue($this->response->json('data.1.tax_rate') === $productThree->tax_rate);
        $this->assertTrue($this->response->json('data.2.tax_rate') === $productOne->tax_rate);
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

        /** @var ExampleProductable $productable */
        $productable = $productables->get(0);

        /** @var Product $bundle */
        $bundle = Product::factory()->bundle()->create();
        $bundle->productables()->create([
            'productable_type' => $productable->getMorphClass(),
            'productable_id' => $productable->getKey(),
            'quantity' => 1,
        ]);

        /** @var Product $product */
        $product = Product::factory()->single()->create();
        $product->productables()->create([
            'productable_type' => $productable->getMorphClass(),
            'productable_id' => $productable->getKey(),
            'quantity' => 1,
        ]);

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/productables/');
        $this->response->assertOk();
        $this->response->assertJsonCount(10, 'data');
        $this->response->assertJsonFragment([
            'productable_id' => $productable->getKey(),
            'productable_type' => ExampleProductable::class,
            'single_product_id' => $product->getKey(),
            'name' => $productable->getName(),
        ]);
        $this->response->assertJsonFragment([
            'productable_id' => $productables->get(1)->getKey(),
            'productable_type' => ExampleProductable::class,
            'single_product_id' => null,
            'name' => $productables->get(1)->getName(),
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

    public function test_update_product_poster_from_existing_file(): void
    {
        Storage::fake();

        $product = Product::factory()->single()->create();
        $directoryPath = ConstantEnum::DIRECTORY . "/{$product->getKey()}/posters";
        UploadedFile::fake()->image('poster.jpg')->storeAs($directoryPath, 'poster.jpg');
        $posterPath = "{$directoryPath}/poster.jpg";

        $response = $this->actingAs($this->user, 'api')->json('PUT', '/api/admin/products/' . $product->getKey(), [
            'poster' => $posterPath,
        ])->assertOk();

        $data = $response->getData()->data;
        Storage::assertExists($data->poster_path);
    }
}
