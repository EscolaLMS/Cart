<?php

namespace EscolaLms\Cart\Tests\API;

use EscolaLms\Cart\Database\Seeders\CartPermissionSeeder;
use EscolaLms\Cart\Enums\OrderStatus;
use EscolaLms\Cart\Facades\Shop;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Models\OrderItem;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\ProductProductable;
use EscolaLms\Cart\Tests\Mocks\ExampleProductable;
use EscolaLms\Cart\Tests\TestCase;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Core\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;

class AdminOrderApiTest extends TestCase
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
                'productable_type' => $productable->getMorphClass(),
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

        $order->status = OrderStatus::CANCELLED;
        $order->save();

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

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/orders', [
            'status' => OrderStatus::CANCELLED,
        ]);
        $this->response->assertStatus(200);
        $this->assertJsonCount(1, 'data');
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
}
