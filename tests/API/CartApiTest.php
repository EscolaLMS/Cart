<?php

namespace EscolaLms\Cart\Tests\API;

use EscolaLms\Cart\Database\Seeders\CartPermissionSeeder;
use EscolaLms\Cart\Facades\Shop;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Cart\Tests\Mocks\Product;
use EscolaLms\Cart\Tests\TestCase;
use EscolaLms\Cart\Tests\Traits\CreatesPaymentMethods;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Core\Models\User;
use EscolaLms\Payments\Models\Payment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;

class CartApiTest extends TestCase
{
    use DatabaseTransactions;
    use CreatesPaymentMethods;

    private User $user;
    private TestResponse $response;
    private ShopServiceContract $shopService;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(CartPermissionSeeder::class);
        Shop::registerProduct(Product::class);

        $this->shopService = app(ShopServiceContract::class);
        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole(UserRole::STUDENT);
    }

    public function test_add_course_to_cart()
    {
        $user = $this->user;
        /** @var Product $product */
        $product = Product::factory()->create();

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/add', [
            'product_id' => $product->getKey(),
            'product_type' => $product->getMorphClass()
        ]);
        $this->response->assertOk();

        $this->assertNotNull($user->cart->getKey());
        $this->assertContains($product->getKey(), $user->cart->items->pluck('buyable_id')->toArray());
    }

    public function test_cart_items_list()
    {
        $user = $this->user;
        /** @var Product $product */
        $product = Product::factory()->create();

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/add', [
            'product_id' => $product->getKey(),
            'product_type' => $product->getMorphClass()
        ]);
        $this->response->assertOk();

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/cart');
        $this->response->assertOk();

        $responseContent = $this->response->json();
        $this->assertTrue($responseContent['success']);
        $this->assertNotEmpty($responseContent['message']);
        $this->assertNotEmpty($responseContent['data']);
        $this->assertNotEmpty($responseContent['data']['items']);
        $cartItemsId = array_map(function ($item) {
            return $item['product_id'];
        }, $responseContent['data']['items']);
        $this->assertContains($product->getKey(), $cartItemsId);
    }

    public function test_send_payment_method_and_pay()
    {
        $user = $this->user;

        /** @var Product $product */
        $product = Product::factory()->create([
            'price' => 1000
        ]);

        $cart = $this->shopService->cartForUser($user);
        $this->shopService->addProductToCart($cart, $product);

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/pay', ['paymentMethodId' => $this->getPaymentMethodId()]);
        $this->response->assertOk();
    }

    public function test_get_orders()
    {
        $user = $this->user;

        /** @var Product $product */
        $product = Product::factory()->create([
            'price' => 1000
        ]);
        /** @var Product $productWithTax */
        $productWithTax = Product::factory()->create([
            'price' => 1000,
            'tax_rate' => 10,
        ]);

        $cart = $this->shopService->cartForUser($user);
        $this->shopService->addProductToCart($cart, $product);
        $this->shopService->addProductToCart($cart, $productWithTax);

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/pay', ['paymentMethodId' => $this->getPaymentMethodId()]);
        $this->response->assertOk();

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/orders');
        $this->response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => [
                    [
                        'status' => 'PAID',
                        'total' => 2100,
                        'subtotal' => 2000,
                        'tax' => 100
                    ]
                ]
            ])
            ->assertJsonCount(3)
            ->assertJsonCount(2, 'data.0.items');

        $order_id = $this->response->json('data.0.id');
        $payment = Payment::where('payable_id', $order_id)->first();
        $this->assertEquals(2100, $payment->amount);
    }
}
