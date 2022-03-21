<?php

namespace EscolaLms\Cart\Tests\API;

use EscolaLms\Cart\Database\Seeders\CartPermissionSeeder;
use EscolaLms\Cart\Facades\Shop;
use EscolaLms\Cart\Http\Resources\ProductResource;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\ProductProductable;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Cart\Tests\Mocks\ExampleProductable;
use EscolaLms\Cart\Tests\TestCase;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Core\Models\User;
use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Templates\Events\ManuallyTriggeredEvent;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;

class ProductApiTest extends TestCase
{
    use DatabaseTransactions, CreatesUsers;

    private User $user;
    private TestResponse $response;
    private ShopServiceContract $shopService;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(CartPermissionSeeder::class);
        Shop::registerProductableClass(ExampleProductable::class);

        $this->shopService = app(ShopServiceContract::class);
        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole(UserRole::STUDENT);
    }

    public function test_list_products()
    {
        $user = $this->user;

        /** @var Product $product */
        $product = Product::factory()->create();
        /** @var Product $product2 */
        $product2 = Product::factory()->create();

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/products');
        $this->response->assertOk();

        $this->response->assertJsonFragment([
            'data' => [
                ProductResource::make($product->refresh())->toArray(null),
                ProductResource::make($product2->refresh())->toArray(null),
            ]
        ]);
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

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/products', ['productable_type' => ExampleProductable::class]);
        $this->response->assertOk();

        $this->response->assertJsonFragment([
            ProductResource::make($product->refresh())->toArray(null),
        ]);
        $this->response->assertJsonFragment([
            ProductResource::make($product2->refresh())->toArray(null),
        ]);
        $this->response->assertJsonMissing([
            ProductResource::make($product3->refresh())->toArray(null),
        ]);

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/products', ['productable_id' => $productable->getKey(), 'productable_type' => ExampleProductable::class]);
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

    public function testTriggerEventManuallyForUsers(): void
    {
        Event::fake([ManuallyTriggeredEvent::class]);

        $student = $this->makeStudent();
        $product = Product::factory()->create();
        $product->users()->sync($student);

        $admin = $this->makeAdmin();
        $this->response = $this->actingAs($admin, 'api')
            ->json('GET', "/api/admin/products/{$product->getKey()}/trigger-event-manually");
        $this->response->assertOk();

        Event::assertDispatched(ManuallyTriggeredEvent::class, function (ManuallyTriggeredEvent $event) use ($student) {
            $this->assertEquals($student->getKey(), $event->getUser()->getKey());
            return true;
        });
    }
}
