<?php

namespace EscolaLms\Cart\Tests\Services;

use Carbon\Carbon;
use EscolaLms\Cart\Database\Seeders\CartPermissionSeeder;
use EscolaLms\Cart\Facades\Shop;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\ProductProductable;
use EscolaLms\Cart\Services\CartManager;
use EscolaLms\Cart\Tests\Mocks\ExampleProductable;
use EscolaLms\Cart\Tests\TestCase;
use EscolaLms\Cart\Tests\Traits\CreatesPaymentMethods;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Core\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Testing\TestResponse;

class CartManagerTest extends TestCase
{
    use DatabaseTransactions;
    use CreatesPaymentMethods;

    private User $user;
    private TestResponse $response;
    private CartManager $cartManager;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(CartPermissionSeeder::class);
        Shop::registerProductableClass(ExampleProductable::class);

        $this->cartManager = app(CartManager::class);
        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole(UserRole::STUDENT);
    }

    public function test_abandoned_carts()
    {
        $user = $this->user;
        /** @var Product $product */
        $product = Product::factory()->single()->create();
        $productable = ExampleProductable::factory()->create();
        $product->productables()->save(new ProductProductable([
            'productable_type' => ExampleProductable::class,
            'productable_id' => $productable->getKey()
        ]));

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/products', [
            'id' => $product->getKey()
        ]);
        $this->response->assertOk();

        $this->assertNotNull($user->cart->getKey());
        $this->assertContains($product->getKey(), $user->cart->items->pluck('buyable_id')->toArray());

        $abandonedCarts = $this->cartManager->getAbandonedCarts(Carbon::now()->subHours(48), Carbon::now());

        $this->assertEquals(1, $abandonedCarts->count());
    }

    public function test_empty_abandoned_carts()
    {
        $user = $this->user;
        /** @var Product $product */
        $product = Product::factory()->single()->create();
        $productable = ExampleProductable::factory()->create();
        $product->productables()->save(new ProductProductable([
            'productable_type' => ExampleProductable::class,
            'productable_id' => $productable->getKey()
        ]));

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/cart/products', [
            'id' => $product->getKey()
        ]);
        $this->response->assertOk();

        $this->assertNotNull($user->cart->getKey());
        $this->assertContains($product->getKey(), $user->cart->items->pluck('buyable_id')->toArray());

        $abandonedCarts = $this->cartManager->getAbandonedCarts(Carbon::now()->subHours(48), Carbon::now()->subHours(24));

        $this->assertEquals(0, $abandonedCarts->count());
    }
}
