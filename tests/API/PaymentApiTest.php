<?php

namespace EscolaLms\Cart\Tests\API;

use EscolaLms\Cart\Database\Seeders\CartPermissionSeeder;
use EscolaLms\Cart\Enums\ProductType;
use EscolaLms\Cart\Events\ProductBought;
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
use EscolaLms\Settings\EscolaLmsSettingsServiceProvider;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Config;
use EscolaLms\Settings\Database\Seeders\PermissionTableSeeder;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\TestResponse;

class PaymentApiTest extends TestCase
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
            ->postJson('/api/product/' . $product->getKey() . '/pay')
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
            ->subscriptionWithoutTrial()
            ->create([
                'price' => 1000,
                'purchasable' => true,
            ]);

        $this->actingAs($user, 'api')
            ->postJson('/api/product/' . $product->getKey() . '/pay')
            ->assertCreated()
            ->assertJsonFragment([
                'amount' => 1000
            ]);

        Event::assertDispatched(ProductBought::class, fn(ProductBought $event) => $event->getProduct()->getKey() === $product->getKey() && $event->getProduct()->type === ProductType::SUBSCRIPTION);

        $product->refresh();

        $this->assertTrue($product->getOwnedByUserAttribute($user));
    }

    public function test_pay_subscription_with_trial(): void
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
            ->postJson('/api/product/' . $product->getKey() . '/pay')
            ->assertCreated()
            ->assertJsonFragment([
                'amount' => 100
            ]);

        Event::assertDispatched(ProductBought::class, fn(ProductBought $event) => $event->getProduct()->getKey() === $product->getKey() && $event->getProduct()->type === ProductType::SUBSCRIPTION);

        $product->refresh();

        $this->assertTrue($product->getOwnedByUserAttribute($user));

        $this->actingAs($user, 'api')
            ->postJson('/api/product/' . $product->getKey() . '/pay')
            ->assertCreated()
            ->assertJsonFragment([
                'amount' => 1000
            ]);
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
}
