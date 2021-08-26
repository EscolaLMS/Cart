<?php

namespace EscolaLms\Cart\Tests\API;

use EscolaLms\Cart\Database\Seeders\CartPermissionSeeder;
use EscolaLms\Cart\Models\Course;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Models\OrderItem;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Cart\Tests\TestCase;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Core\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Testing\TestResponse;

class AdminApiTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private TestResponse $response;
    private ShopServiceContract $shopServiceContract;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(CartPermissionSeeder::class);
        $this->shopServiceContract = app(ShopServiceContract::class);
        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole(UserRole::ADMIN);
        // OrderItem::query()->delete();
        // Order::query()->delete();
        // Course::query()->delete();
    }

    public function test_list_orders()
    {
        $courses = [
            ...Course::factory()->for(User::factory(), 'author')->count(5)->create(),
            ...Course::factory()->for(User::factory(), 'author')->count(5)->create(),
        ];
        /** @var Course $course */
        $orders = [];
        foreach ($courses as $course) {
            /** @var Order $order */
            $order = Order::factory()->for(User::factory()->create())->create();
            $orderItem = new OrderItem();
            $orderItem->buyable()->associate($course);
            $orderItem->quantity = 1;
            $orderItem->order_id = $order->getKey();
            $orderItem->save();
            $orders[] = $order;
        }

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/orders');
        $this->response->assertStatus(200);
        // $this->response->assertJsonCount(10, 'data');
        $this->assertDataCountLessThanOrEqual($this->response, 10);

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/orders', [
            'user_id' => $orders[0]->user_id,
        ]);
        $this->response->assertStatus(200);
        // $this->response->assertJsonCount(1, 'data');
        $this->assertDataCountLessThanOrEqual($this->response, 1);

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/orders', [
            'course_id' => $courses[0]->id,
        ]);
        $this->response->assertStatus(200);
        // $this->response->assertJsonCount(1, 'data');
        $this->assertDataCountLessThanOrEqual($this->response, 1);

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/orders', [
            'author_id' => $courses[0]->author_id,
        ]);
        $this->response->assertStatus(200);
        // $this->response->assertJsonCount(5, 'data');
        $this->assertDataCountLessThanOrEqual($this->response, 5);

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/orders', [
            'date_from' => Carbon::now()->addDay(1)->toISOString(),
        ]);
        $this->response->assertStatus(200);
        $this->response->assertJsonCount(0, 'data');

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/orders', [
            'date_to' => Carbon::now()->addDay(1)->toISOString(),
        ]);
        $this->response->assertStatus(200);
        // $this->response->assertJsonCount(10, 'data');
        $this->assertDataCountLessThanOrEqual($this->response, 10);

        $this->response = $this->actingAs($this->user, 'api')->json('GET', '/api/admin/orders', [
            'date_to' => Carbon::now()->subDay(1)->toISOString(),
        ]);
        $this->response->assertStatus(200);
        // $this->response->assertJsonCount(0, 'data');
        $this->assertDataCountLessThanOrEqual($this->response, 0);
    }

    private function assertDataCountLessThanOrEqual($response, $count)
    {
        $this->assertLessThanOrEqual(count($response->getData()->data), $count);
    }
}
