<?php

namespace EscolaLms\Cart\Tests\API;

use EscolaLms\Cart\Models\Course;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\User;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Cart\Tests\TestCase;
use EscolaLms\Cart\Tests\Traits\CreatesPaymentMethods;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class CartApiTest extends TestCase
{
    use WithoutMiddleware;
    use DatabaseTransactions;
    use CreatesPaymentMethods;

    private $response;
    private ShopServiceContract $shopServiceContract;

    public function setUp(): void
    {
        parent::setUp();

        $this->shopServiceContract = app(ShopServiceContract::class);
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

    public function test_send_payment_method_and_pay()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 10
        ]);

        $this->shopServiceContract->loadUserCart($user);
        $this->shopServiceContract->add($product, 1);

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/pay', ['paymentMethodId' => $this->getPaymentMethodId()]);
        $this->response->assertOk();
    }

    public function test_get_orders()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 10
        ]);

        $this->shopServiceContract->loadUserCart($user);
        $this->shopServiceContract->add($product, 1);

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/pay', ['paymentMethodId' => $this->getPaymentMethodId()]);

        $this->response->assertOk();

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/orders');
        $this->response->assertOk()
            ->assertJson([['status' => 'PAID', 'total' => 10, 'subtotal' => 10, 'tax' => 0]])
            ->assertJsonCount(1)
            ->assertJsonCount(1, '0.items');
    }

    public function test_buy_course()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $this->shopServiceContract->loadUserCart($user);
        $this->shopServiceContract->add($course, 1);

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/pay', ['paymentMethodId' => $this->getPaymentMethodId()]);

        $this->response->assertOk();

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/orders');
        $this->response->assertOk()
            ->assertJson([['status' => 'PAID', 'total' => 11.99, 'subtotal' => 11.99, 'tax' => 0]])
            ->assertJsonCount(1)
            ->assertJsonCount(1, '0.items');

        $user->refresh();
        $course->refresh();
        $this->assertTrue($course->alreadyBoughtBy($user));
    }
}
