<?php

namespace EscolaLms\Cart\Tests\API;

use EscolaLms\Cart\Database\Seeders\CartPermissionSeeder;
use EscolaLms\Cart\Events\ProductableAttached;
use EscolaLms\Cart\Events\ProductableDetached;
use EscolaLms\Cart\Events\ProductAttached;
use EscolaLms\Cart\Events\ProductDetached;
use EscolaLms\Cart\Facades\Shop;
use EscolaLms\Cart\Http\Resources\ProductResource;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Models\OrderItem;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\ProductProductable;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Cart\Tests\Mocks\ExampleProductable;
use EscolaLms\Cart\Tests\TestCase;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Core\Models\User;
use Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;

class AdminApiTest extends TestCase
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

    public function test_list_orders()
    {
        $products = [
            ...Product::factory()->count(5)->create(),
        ];
        foreach ($products as $product) {
            $productable = ExampleProductable::factory()->create();
            $product->productables()->save(new ProductProductable([
                'productable_type' => ExampleProductable::class,
                'productable_id' => $productable->getKey()
            ]));
        }

        $orders = [];
        foreach ($products as $product) {
            /** @var Order $order */
            $order = Order::factory()->for(User::factory()->create())->create();
            $orderItem = new OrderItem();
            $orderItem->buyable()->associate($product);
            $orderItem->quantity = 1;
            $orderItem->order_id = $order->getKey();
            $orderItem->save();
            $orders[] = $order;
        }

        $totalCount = min(15, Order::count());
        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/orders');
        $this->response->assertStatus(200);
        $this->assertDataCountLessThanOrEqual($this->response, $totalCount);

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/orders', [
            'user_id' => $orders[0]->user_id,
        ]);
        $this->response->assertStatus(200);
        $this->assertDataCountLessThanOrEqual($this->response, 1);

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/orders', [
            'productable_type' => ExampleProductable::class,
            'productable_id' => $productable->id,
        ]);
        $this->response->assertStatus(200);
        $this->assertDataCountLessThanOrEqual($this->response, 1);

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/orders', [
            'date_from' => Carbon::now()->addDay(1)->toISOString(),
        ]);
        $this->response->assertStatus(200);
        $this->response->assertJsonCount(0, 'data');

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/orders', [
            'date_to' => Carbon::now()->addDay(1)->toISOString(),
        ]);
        $this->response->assertStatus(200);
        $this->assertDataCountLessThanOrEqual($this->response, 10);

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/orders', [
            'date_to' => Carbon::now()->subDay(1)->toISOString(),
        ]);
        $this->response->assertStatus(200);
        $this->assertDataCountLessThanOrEqual($this->response, 0);
    }

    private function assertDataCountLessThanOrEqual($response, $count)
    {
        $this->assertLessThanOrEqual($count, count($response->getData()->data));
    }

    public function test_fetch_order()
    {
        $product = Product::factory()->create();

        /** @var Order $order */
        $order = Order::factory()->for(User::factory()->create())->create();
        $orderItem = new OrderItem();
        $orderItem->buyable()->associate($product);
        $orderItem->quantity = 1;
        $orderItem->order_id = $order->getKey();
        $orderItem->save();

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/orders/' . $order->id);
        $this->response->assertStatus(200);

        $this->assertEquals($this->response->getData()->data->id, $order->id);
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
            'productable_type' => ExampleProductable::class,
            'productable_id' => $productable->getKey()
        ]));

        $student = config('auth.providers.users.model')::factory()->create();
        $student->guard_name = 'api';
        $student->assignRole(UserRole::STUDENT);

        $this->assertFalse($productable->getOwnedByUserAttribute($student));

        $this->response = $this->actingAs($this->user, 'api')->json('POST', '/api/admin/productables/attach', [
            'user_id' => $student->getKey(),
            'productable_id' => $productable->getKey(),
            'productable_type' => ExampleProductable::class
        ]);
        $this->response->assertOk();

        $product->refresh();
        $this->assertTrue($productable->getOwnedByUserAttribute($student));
        $this->assertTrue(app(ProductServiceContract::class)->productIsOwnedByUser($product, $student, true));

        $eventFake->assertDispatched(ProductableAttached::class, fn (ProductableAttached $event) => $event->getProductable()->getKey() === $productable->getKey());

        $this->response = $this->actingAs($this->user, 'api')->json('POST', '/api/admin/productables/detach', [
            'user_id' => $student->getKey(),
            'productable_id' => $productable->getKey(),
            'productable_type' => ExampleProductable::class
        ]);
        $this->response->assertOk();

        $product->refresh();
        $this->assertFalse($productable->getOwnedByUserAttribute($student));

        $eventFake->assertDispatched(ProductableDetached::class, fn (ProductableDetached $event) => $event->getProductable()->getKey() === $productable->getKey());
    }

    public function test_create_product()
    {
        $productData = Product::factory()->make()->toArray();
        $this->response = $this->actingAs($this->user, 'api')->json('POST', '/api/admin/products', $productData);
        $this->response->assertCreated();
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
            'productable_type' => ExampleProductable::class,
            'productable_id' => $productable->getKey()
        ]));
        /** @var Product $product2 */
        $product2 = Product::factory()->create();
        $productable2 = ExampleProductable::factory()->create();
        $product2->productables()->save(new ProductProductable([
            'productable_type' => ExampleProductable::class,
            'productable_id' => $productable2->getKey()
        ]));
        /** @var Product $product2 */
        $product3 = Product::factory()->create(['purchasable' => false]);
        $productable3 = ExampleProductable::factory()->create();
        $product3->productables()->save(new ProductProductable([
            'productable_type' => ExampleProductable::class,
            'productable_id' => $productable3->getKey()
        ]));

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/admin/products', ['productable_type' => $productable->getMorphClass()]);
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

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/admin/products', ['productable_id' => $productable->getKey(), 'productable_type' => $productable->getMorphClass()]);
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
}
