<?php

namespace EscolaLms\Cart\Tests\API;

use EscolaLms\Cart\Database\Seeders\CartPermissionSeeder;
use EscolaLms\Cart\Enums\ProductType;
use EscolaLms\Cart\Enums\QuantityOperationEnum;
use EscolaLms\Cart\Events\ProductAddedToCart;
use EscolaLms\Cart\Events\ProductBought;
use EscolaLms\Cart\Events\ProductRemovedFromCart;
use EscolaLms\Cart\Facades\Shop;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\ProductProductable;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Cart\Tests\Mocks\ExampleProductable;
use EscolaLms\Cart\Tests\TestCase;
use EscolaLms\Cart\Tests\Traits\CreatesPaymentMethods;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Core\Models\User;
use EscolaLms\Payments\Facades\PaymentGateway;
use EscolaLms\Payments\Models\Payment;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;
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
        Shop::registerProductableClass(ExampleProductable::class);

        $this->shopService = app(ShopServiceContract::class);
        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole(UserRole::STUDENT);
    }

    protected function createProductForTesting(): Product
    {
        $product = Product::factory()->single()->create();
        $productable = ExampleProductable::factory()->create();
        $product->productables()->save(new ProductProductable([
            'productable_type' => $productable->getMorphClass(),
            'productable_id' => $productable->getKey()
        ]));
        return $product;
    }

    public function test_add_product_to_cart(): void
    {
        $user = $this->user;

        $product = $this->createProductForTesting();

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/products', [
            'id' => $product->getKey()
        ]);
        $this->response->assertOk();

        $this->assertNotNull($user->cart->getKey());
        $this->assertContains($product->getKey(), $user->cart->items->pluck('buyable_id')->toArray());
    }

    public function test_add_productable_to_cart(): void
    {
        $user = $this->user;

        $product = $this->createProductForTesting();
        $productable = $product->productables->first()->productable;

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/add', [
            'productable_type' => ExampleProductable::class,
            'productable_id' => $productable->getKey()
        ]);
        $this->response->assertOk();

        $this->assertNotNull($user->cart->getKey());
        $this->assertContains($product->getKey(), $user->cart->items->pluck('buyable_id')->toArray());
    }

    public function test_remove_last_product_from_cart(): void
    {
        $user = $this->user;
        /** @var Product $product */
        $product = Product::factory()->create();

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/products', [
            'id' => $product->getKey(),
        ]);
        $this->response->assertOk();

        $this->assertNotNull($user->cart->getKey());
        $this->assertContains($product->getKey(), $user->cart->items->pluck('buyable_id')->toArray());

        $this->response = $this->actingAs($user, 'api')->json('DELETE', '/api/cart/products/' . $product->getKey());
        $this->response->assertOk();

        $user->refresh();
        $this->assertNull($user->cart);
    }

    public function test_add_product_to_cart_nullable_limit_per_user(): void
    {
        $user = $this->user;
        /** @var Product $product */
        $product = Product::factory([
            'limit_per_user' => null,
        ])->create();

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/products', [
            'id' => $product->getKey(),
        ]);
        $this->response->assertOk();

        $this->assertNotNull($user->cart->getKey());
    }

    public function test_remove_product_from_cart(): void
    {
        $eventFake = Event::fake();

        $user = $this->user;
        /** @var Product $product */
        $product = Product::factory()->create();
        /** @var Product $product2 */
        $product2 = Product::factory()->create();
        /** @var Product $product2 */
        $product3 = Product::factory()->create();

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/products', [
            'id' => $product->getKey(),
        ]);
        $this->response->assertOk();
        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/products', [
            'id' => $product2->getKey(),
        ]);
        $this->response->assertOk();
        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/products', [
            'id' => $product3->getKey(),
        ]);
        $this->response->assertOk();

        $cart = $user->cart;

        $eventFake->assertDispatched(
            ProductAddedToCart::class,
            fn(ProductAddedToCart $event) => $event->getProduct()->getKey() === $product->getKey()
                && $event->getUser()->getKey() === $user->getKey()
                && $event->getCart()->getKey() === $cart->getKey()
        );

        $this->assertNotNull($cart);
        $this->assertNotNull($cart->getKey());
        $this->assertContains($product->getKey(), $cart->items->pluck('buyable_id')->toArray());
        $this->assertContains($product2->getKey(), $cart->items->pluck('buyable_id')->toArray());
        $this->assertContains($product3->getKey(), $cart->items->pluck('buyable_id')->toArray());

        $this->response = $this->actingAs($user, 'api')->json('DELETE', '/api/cart/products/' . $product->getKey());
        $this->response->assertOk();

        $cart->refresh();

        $this->assertNotContains($product->getKey(), $cart->items->pluck('buyable_id')->toArray());
        $this->assertContains($product2->getKey(), $cart->items->pluck('buyable_id')->toArray());
        $this->assertContains($product3->getKey(), $cart->items->pluck('buyable_id')->toArray());

        $this->response = $this->actingAs($user, 'api')->json('DELETE', '/api/cart/products/' . $product2->getKey());
        $this->response->assertOk();

        $cart->refresh();

        $this->assertNotContains($product->getKey(), $cart->items->pluck('buyable_id')->toArray());
        $this->assertNotContains($product2->getKey(), $cart->items->pluck('buyable_id')->toArray());
        $this->assertContains($product3->getKey(), $cart->items->pluck('buyable_id')->toArray());

        $eventFake->assertDispatched(
            ProductRemovedFromCart::class,
            fn(ProductRemovedFromCart $event) => $event->getProduct()->getKey() === $product->getKey()
                && $event->getUser()->getKey() === $user->getKey()
                && $event->getCart()->getKey() === $cart->getKey()
        );
    }

    public function test_cart_items_list(): void
    {
        $user = $this->user;

        $product = $this->createProductForTesting();
        $productable = $product->productables->first()->productable;

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/add', [
            'productable_type' => ExampleProductable::class,
            'productable_id' => $productable->getKey()
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

    public function test_pay(): void
    {
        $eventFake = Event::fake(ProductBought::class);
        $paymentsFake = PaymentGateway::fake();

        $user = $this->user;

        /** @var Product $product */
        $product = Product::factory()->create([
            'price' => 1000,
            'purchasable' => true,
        ]);

        $cart = $this->shopService->cartForUser($user);
        $this->shopService->addProductToCart($cart, $product);

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/pay');
        $this->response->assertCreated();

        $eventFake->assertDispatched(ProductBought::class, fn(ProductBought $event) => $event->getProduct()->getKey() === $product->getKey());

        $product->refresh();

        $this->assertTrue($product->getOwnedByUserAttribute($user));
    }

    public function test_pay_product(): void
    {
        Event::fake(ProductBought::class);
        PaymentGateway::fake();

        $user = $this->user;

        /** @var Product $product */
        $product = Product::factory()->create([
            'price' => 1000,
            'purchasable' => true,
        ]);

        $this->actingAs($user, 'api')
            ->postJson('/api/cart/pay/products/' . $product->getKey())
            ->assertCreated();

        Event::assertDispatched(ProductBought::class, fn(ProductBought $event) => $event->getProduct()->getKey() === $product->getKey());

        $product->refresh();

        $this->assertTrue($product->getOwnedByUserAttribute($user));
    }

    public function test_pay_subscription(): void
    {
        Event::fake(ProductBought::class);
        PaymentGateway::fake();

        $user = $this->user;

        /** @var Product $product */
        $product = Product::factory()
            ->subscriptionWithTrial()
            ->create([
                'price' => 1000,
                'purchasable' => true,
            ]);

        $this->actingAs($user, 'api')
            ->postJson('/api/cart/pay/products/' . $product->getKey())
            ->assertCreated();

        Event::assertDispatched(ProductBought::class, fn(ProductBought $event) => $event->getProduct()->getKey() === $product->getKey() && $event->getProduct()->type === ProductType::SUBSCRIPTION);

        $product->refresh();

        $this->assertTrue($product->getOwnedByUserAttribute($user));
    }

    public function test_pay_for_free_products(): void
    {
        $eventFake = Event::fake(ProductBought::class);

        $user = $this->user;

        /** @var Product $product */
        $product = Product::factory()->create([
            'price' => 0,
            'purchasable' => true,
        ]);

        $cart = $this->shopService->cartForUser($user);
        $this->shopService->addProductToCart($cart, $product);

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/pay');
        $this->response->assertCreated();

        $eventFake->assertDispatched(ProductBought::class, fn(ProductBought $event) => $event->getProduct()->getKey() === $product->getKey());

        $product->refresh();

        $this->assertTrue($product->getOwnedByUserAttribute($user));
    }

    public function test_get_orders(): void
    {
        $paymentsFake = PaymentGateway::fake();

        $user = $this->user;

        /** @var Product $product */
        $product = Product::factory()->create([
            'price' => 1000,
            'purchasable' => true,
        ]);
        /** @var Product $productWithTax */
        $productWithTax = Product::factory()->create([
            'price' => 1000,
            'tax_rate' => 10,
            'purchasable' => true,
        ]);

        $cart = $this->shopService->cartForUser($user);
        $this->shopService->addProductToCart($cart, $product);
        $this->shopService->addProductToCart($cart, $productWithTax);

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/pay');
        $this->response->assertCreated();

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
            ->assertJsonCount(4)
            ->assertJsonCount(2, 'data.0.items');

        $order_id = $this->response->json('data.0.id');
        $payment = Payment::where('payable_id', $order_id)->first();
        $this->assertEquals(2100, $payment->amount);
    }

    public function test_limit_per_user(): void
    {
        $paymentsFake = PaymentGateway::fake();

        $user = $this->user;

        /** @var Product $product */
        $product = Product::factory()->create([
            'price' => 1000,
            'purchasable' => true,
            'limit_per_user' => 1,
        ]);

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/products/' . $product->getKey());
        $this->response->assertOk();
        $this->response->assertJson(
            fn(AssertableJson $json) => $json->where('data.id', $product->getKey())
                ->where('data.buyable', true)
                ->where('data.owned', false)
                ->where('data.limit_per_user', 1)
                ->etc()
        );

        $this->assertFalse($product->getOwnedByUserAttribute($user));

        $cart = $this->shopService->cartForUser($user);
        $this->shopService->addProductToCart($cart, $product);
        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/pay');
        $this->response->assertCreated();

        $this->assertTrue($product->getOwnedByUserAttribute($user));

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/products/' . $product->getKey());

        $this->response->assertOk();
        $this->response->assertJson(
            fn(AssertableJson $json) => $json->where('data.id', $product->getKey())
                ->where('data.buyable', false)
                ->where('data.owned', true)
                ->where('data.limit_per_user', 1)
                ->etc()
        );
    }

    public function test_added_more_than_limit_per_user(): void
    {
        $user = $this->user;

        /** @var Product $product */
        $product = Product::factory()->create([
            'price' => 1000,
            'purchasable' => true,
            'limit_per_user' => 1,
        ]);

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/products/' . $product->getKey());
        $this->response->assertOk();
        $this->response->assertJson(
            fn(AssertableJson $json) => $json->where('data.id', $product->getKey())
                ->where('data.buyable', true)
                ->where('data.owned', false)
                ->where('data.limit_per_user', 1)
                ->etc()
        );

        $this->assertFalse($product->getOwnedByUserAttribute($user));

        $this->response = $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/cart/products', ['id' => $product->getKey(), 'quantity' => 2]);
        $this->response->assertUnprocessable();
        $this->response->assertJson(
            fn(AssertableJson $json) => $json->where('message', 'The quantity must not be greater than 1.')
                ->where('errors.quantity', ['The quantity must not be greater than 1.'])
                ->etc()
        );

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/cart');
        $this->response->assertOk();
        $this->response->assertJson(
            fn(AssertableJson $json) => $json
                ->where('data.total', 0)
                ->where('data.subtotal', 0)
                ->where('data.tax', 0)
                ->where('data.items', [])
                ->etc()
        );
    }

    public function test_user_had_and_add_more_than_limit_per_user(): void
    {
        $paymentsFake = PaymentGateway::fake();

        $user = $this->user;

        /** @var Product $product */
        $product = Product::factory()->create([
            'price' => 1000,
            'purchasable' => true,
            'limit_per_user' => 3,
        ]);

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/products/' . $product->getKey());
        $this->response->assertOk();
        $this->response->assertJson(
            fn(AssertableJson $json) => $json->where('data.id', $product->getKey())
                ->where('data.buyable', true)
                ->where('data.owned', false)
                ->where('data.limit_per_user', 3)
                ->etc()
        );

        $this->assertFalse($product->getOwnedByUserAttribute($user));

        $this->response = $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/cart/products', ['id' => $product->getKey(), 'quantity' => 2]);
        $this->response->assertOk();
        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/pay');
        $this->response->assertCreated();

        $this->assertTrue($product->getOwnedByUserAttribute($user));

        $this->response = $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/cart/products', ['id' => $product->getKey(), 'quantity' => 2]);
        $this->response->assertUnprocessable();

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/cart');
        $this->response->assertOk();
        $this->response->assertJson(
            fn(AssertableJson $json) => $json
                ->where('data.total', 0)
                ->where('data.subtotal', 0)
                ->where('data.tax', 0)
                ->where('data.items', [])
                ->etc()
        );
    }

    public function test_quantity_change_message(): void
    {
        $user = $this->user;

        /** @var Product $product */
        $product = Product::factory()->create([
            'price' => 1000,
            'purchasable' => true,
            'limit_per_user' => 3,
        ]);

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/products/' . $product->getKey());
        $this->response->assertOk();
        $this->response->assertJson(
            fn(AssertableJson $json) => $json->where('data.id', $product->getKey())
                ->where('data.buyable', true)
                ->where('data.owned', false)
                ->where('data.limit_per_user', 3)
                ->etc()
        );

        $this->response = $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/cart/products', ['id' => $product->getKey(), 'quantity' => 2]);
        $this->response->assertOk();
        $this->response->assertJson(
            fn(AssertableJson $json) => $json->where('message', 'Product quantity changed')
                ->where('data', [
                    'operation' => QuantityOperationEnum::INCREMENT,
                    'difference' => 2,
                    'buyable' => true,
                    'limit' => 3,
                    'quantity_owned' => 0,
                    'quantity_in_cart' => 2,
                ])
                ->etc()
        );

        $this->response = $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/cart/products', ['id' => $product->getKey(), 'quantity' => 1]);
        $this->response->assertOk();
        $this->response->assertJson(
            fn(AssertableJson $json) => $json->where('message', 'Product quantity changed')
                ->where('data', [
                    'operation' => QuantityOperationEnum::DECREMENT,
                    'difference' => 1,
                    'buyable' => true,
                    'limit' => 3,
                    'quantity_owned' => 0,
                    'quantity_in_cart' => 1,
                ])
                ->etc()
        );

        $this->response = $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/cart/products', ['id' => $product->getKey(), 'quantity' => 1]);
        $this->response->assertOk();
        $this->response->assertJson(
            fn(AssertableJson $json) => $json->where('message', 'Product quantity changed')
                ->where('data', [
                    'operation' => QuantityOperationEnum::UNCHANGED,
                    'difference' => 0,
                    'buyable' => true,
                    'limit' => 3,
                    'quantity_owned' => 0,
                    'quantity_in_cart' => 1,
                ])
                ->etc()
        );
    }

    public function test_quantity_change_buyable(): void
    {
        $paymentsFake = PaymentGateway::fake();
        $user = $this->user;

        /** @var Product $product */
        $product = Product::factory()->create([
            'price' => 1000,
            'purchasable' => true,
            'limit_per_user' => 3,
        ]);

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/products/' . $product->getKey());
        $this->response->assertOk();
        $this->response->assertJson(
            fn(AssertableJson $json) => $json->where('data.id', $product->getKey())
                ->where('data.buyable', true)
                ->where('data.owned', false)
                ->where('data.limit_per_user', 3)
                ->etc()
        );

        $this->response = $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/cart/products', ['id' => $product->getKey(), 'quantity' => 2]);
        $this->response->assertOk();
        $this->response->assertJson(
            fn(AssertableJson $json) => $json->where('message', 'Product quantity changed')
                ->where('data', [
                    'operation' => QuantityOperationEnum::INCREMENT,
                    'difference' => 2,
                    'buyable' => true,
                    'limit' => 3,
                    'quantity_owned' => 0,
                    'quantity_in_cart' => 2,
                ])
                ->etc()
        );

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/pay');
        $this->response->assertCreated();

        $this->assertTrue($product->getOwnedByUserAttribute($user));

        $this->response = $this
            ->actingAs($user, 'api')
            ->json('POST', '/api/cart/products', ['id' => $product->getKey(), 'quantity' => 1]);
        $this->response->assertOk();
        $this->response->assertJson(
            fn(AssertableJson $json) => $json->where('message', 'Product quantity changed')
                ->where('data', [
                    'operation' => QuantityOperationEnum::INCREMENT,
                    'difference' => 1,
                    'buyable' => false,
                    'limit' => 3,
                    'quantity_owned' => 2,
                    'quantity_in_cart' => 1,
                ])
                ->etc()
        );
    }

    public function test_add_missing_products(): void
    {
        $user = $this->user;

        $product = $this->createProductForTesting();
        $product2 = $this->createProductForTesting();
        $product3 = $this->createProductForTesting();

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/products', [
            'id' => $product->getKey()
        ]);
        $this->response->assertOk();

        $this->assertNotNull($user->cart->getKey());
        $this->assertContains($product->getKey(), $user->cart->items->pluck('buyable_id')->toArray());
        $this->assertNotContains($product2->getKey(), $user->cart->items->pluck('buyable_id')->toArray());
        $this->assertNotContains($product3->getKey(), $user->cart->items->pluck('buyable_id')->toArray());


        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/missing', [
            'products' => [$product2->getKey(), $product3->getKey()]
        ]);
        $this->response->assertOk();

        $this->assertNotNull($user->cart->refresh());
        $this->assertContains($product->getKey(), $user->cart->items->pluck('buyable_id')->toArray());
        $this->assertContains($product2->getKey(), $user->cart->items->pluck('buyable_id')->toArray());
        $this->assertContains($product3->getKey(), $user->cart->items->pluck('buyable_id')->toArray());
    }
}
