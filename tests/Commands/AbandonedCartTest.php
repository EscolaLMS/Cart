<?php

namespace EscolaLms\Cart\Tests\Commands;

use EscolaLms\Cart\Console\Commands\AbandonedCart;
use EscolaLms\Cart\Database\Seeders\CartPermissionSeeder;
use EscolaLms\Cart\Events\AbandonedCartEvent;
use EscolaLms\Cart\Facades\Shop;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\ProductProductable;
use EscolaLms\Cart\Tests\Mocks\ExampleProductable;
use EscolaLms\Cart\Tests\TestCase;
use EscolaLms\Cart\Tests\Traits\CreatesPaymentMethods;
use EscolaLms\Core\Enums\UserRole;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;

class AbandonedCartTest extends TestCase
{
    use DatabaseTransactions, CreatesPaymentMethods;

    public function setUp(): void
    {
        parent::setUp();

        $this->seed(CartPermissionSeeder::class);
        Shop::registerProductableClass(ExampleProductable::class);
        $this->user = config('auth.providers.users.model')::factory()->create();
        $this->user->guard_name = 'api';
        $this->user->assignRole(UserRole::STUDENT);

        $this->product = Product::factory()->single()->create();
        $this->productable = ExampleProductable::factory()->create();
        $this->product->productables()->save(new ProductProductable([
            'productable_type' => ExampleProductable::class,
            'productable_id' => $this->productable->getKey()
        ]));
    }

    public function testAbandonedCartCommand(): void
    {
        Event::fake([AbandonedCartEvent::class]);

        $this->actingAs($this->user, 'api')->postJson('/api/cart/products', [
            'id' => $this->product->getKey()
        ])->assertOk();
        $this->assertNotNull($this->user->cart->getKey());
        $this->assertContains($this->product->getKey(), $this->user->cart->items->pluck('buyable_id')->toArray());

        $this->artisan(AbandonedCart::class)->assertSuccessful();
        Event::assertDispatched(AbandonedCartEvent::class, function (AbandonedCartEvent $event) {
            $this->assertEquals($this->user->getKey(), $event->getUser()->getKey());
            return true;
        });
    }

    public function testEmptyAbandonedCart(): void
    {
        Event::fake([AbandonedCartEvent::class]);

        $this->travelTo(now()->subDays(2));

        $this->actingAs($this->user, 'api')->postJson('/api/cart/products', [
            'id' => $this->product->getKey()
        ])->assertOk();
        $this->assertNotNull($this->user->cart->getKey());
        $this->assertContains($this->product->getKey(), $this->user->cart->items->pluck('buyable_id')->toArray());

        $this->travelBack();

        $this->artisan(AbandonedCart::class)->assertSuccessful();
        Event::assertNotDispatched(AbandonedCartEvent::class);
    }
}