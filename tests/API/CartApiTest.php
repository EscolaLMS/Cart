<?php

namespace EscolaSoft\Cart\Tests\API;

use EscolaLms\Courses\Models\Course;
use EscolaSoft\Cart\Models\Product;
use EscolaSoft\Cart\Services\Contracts\ShopServiceContract;
use EscolaSoft\Cart\Tests\Models\User;
use EscolaSoft\Cart\Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class CartApiTest extends TestCase
{
    use WithoutMiddleware, DatabaseTransactions;

    private $response;
    private ShopServiceContract $shopServiceContract;

    public function setUp(): void
    {
        parent::setUp();

        $this->shopServiceContract = app(ShopServiceContract::class);
    }

    /**
     * @test
     */
    public function test_cart_items_list()
    {
        $course = Course::factory()->create();
        $user = User::factory()->create();
        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/course/' . $course->getKey());
        $this->response->assertStatus(200);

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/cart');
        $this->assertObjectHasAttribute('items', $this->response->getData());
        $this->assertNotEmpty($this->response->getData()->items);
        $cartItemsId = array_map(function ($item) {
            return $item->id;
        }, $this->response->getData()->items);
        $this->assertTrue(in_array($course->getKey(), $cartItemsId));
    }

    public function test_add_course_to_cart()
    {
        $course = Course::factory()->create();
        $user = User::factory()->create();

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/course/' . $course->getKey());
        $this->response->assertStatus(200);
        $this->assertTrue((bool)$user->cart->getKey());
        $this->assertTrue(in_array($course->getKey(), $user->cart->items->pluck('buyable_id')->toArray()));
    }

    public function test_send_payment_method_and_pay(): array
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 10
        ]);

        $this->shopServiceContract->loadUserCart($user);
        $this->shopServiceContract->add($product, 1);

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/pay', ['paymentMethodId' => null]);
        $this->response->assertOk();

        return [$user];
    }

    /**
     * @depends test_send_payment_method_and_pay
     */
    public function test_get_orders(array $payload): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 10
        ]);

        $this->shopServiceContract->loadUserCart($user);
        $this->shopServiceContract->add($product, 1);

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/pay', ['paymentMethodId' => null]);

        $this->response->assertOk();

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/orders');
        $this->response->assertOk()
            ->assertJson([['status' => 'PAID', 'total' => 10, 'subtotal' => 10, 'tax' => 0]])
            ->assertJsonCount(1)
            ->assertJsonCount(1, '0.items');
    }
}