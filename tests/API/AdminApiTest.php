<?php

namespace EscolaLms\Cart\Tests\API;

use EscolaLms\Cart\Database\Seeders\CartPermissionSeeder;
use EscolaLms\Cart\Events\ProductAttached;
use EscolaLms\Cart\Events\ProductDetached;
use EscolaLms\Cart\Facades\Shop;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Models\OrderItem;
use EscolaLms\Cart\Tests\Mocks\Product;
use EscolaLms\Cart\Tests\TestCase;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Core\Models\User;
use Event;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Schema;

class AdminApiTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private TestResponse $response;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(CartPermissionSeeder::class);
        Shop::registerProduct(Product::class);

        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole(UserRole::ADMIN);
    }

    public function test_list_orders()
    {
        $products = [
            ...Product::factory()->count(5)->create(),
        ];

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
            'product_type' => $products[0]->getMorphClass(),
            'product_id' => $products[0]->id,
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

        $this->response = $this->actingAs($this->user, 'api')->json('POST', '/api/admin/products/attach', [
            'product_id' => $product->getKey(),
            'product_type' => $product->getMorphClass(),
            'user_id' => $student->getKey(),
        ]);
        $this->response->assertOk();

        $product->refresh();
        $this->assertTrue($product->getOwnedByUserAttribute($student));

        $event->assertDispatched(ProductAttached::class);

        $this->response = $this->actingAs($this->user, 'api')->json('POST', '/api/admin/products/detach', [
            'product_id' => $product->getKey(),
            'product_type' => $product->getMorphClass(),
            'user_id' => $student->getKey(),
        ]);
        $this->response->assertOk();

        $product->refresh();
        $this->assertFalse($product->getOwnedByUserAttribute($student));

        $event->assertDispatched(ProductDetached::class);
    }
}
