<?php

namespace EscolaLms\Cart\Tests\Services;

use EscolaLms\Cart\Database\Seeders\CartPermissionSeeder;
use EscolaLms\Cart\Enums\PeriodEnum;
use EscolaLms\Cart\Events\ProductAttached;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\ProductUser;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Cart\Tests\TestCase;
use EscolaLms\Core\Models\User;
use EscolaLms\Core\Tests\CreatesUsers;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;

class ProductServiceTest extends TestCase
{
    use DatabaseTransactions, CreatesUsers;

    private ProductServiceContract $productService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(CartPermissionSeeder::class);

        $this->productService = app(ProductServiceContract::class);

        Carbon::setTestNow(Carbon::now()->startOfDay());
    }

    public function testAttachProductToUserSubscription(): void
    {
        Event::fake([ProductAttached::class]);

        $user = $this->makeStudent();
        $product = Product::factory()->subscription()->create();

        $this->productService->attachProductToUser($product, $user);

        $this->assertHasProductsUsers($product, $user);
        $this->assertProductAttachedEventDispatched($product, $user);
    }

    public function testAttachProductToUserSubscriptionWithTrial(): void
    {
        Event::fake([ProductAttached::class]);

        $user = $this->makeStudent();
        $product = Product::factory()->subscriptionWithTrial()->create();

        $this->productService->attachProductToUser($product, $user);

        $this->assertHasProductsUsers($product, $user);
        $this->assertProductAttachedEventDispatched($product, $user);
    }

    public function testAttachProductToUserSubscriptionUpdateEndDate(): void
    {
        Event::fake([ProductAttached::class]);

        $user = $this->makeStudent();
        $product = Product::factory()->subscriptionWithoutTrial()->state(['subscription_period' => PeriodEnum::DAILY, 'subscription_duration' => 14])->create();
        $endDate = Carbon::now()->addDays(14)->addDays(14);

        $this->productService->attachProductToUser($product, $user);
        $this->productService->attachProductToUser($product, $user);

        $this->assertHasProductsUsers($product, $user, $endDate);
    }

    public function testAttachProductToUserSubscriptionChargeOnceTrailPeriod(): void
    {
        Event::fake([ProductAttached::class]);

        $user = $this->makeStudent();
        $product = Product::factory()
            ->subscriptionWithTrial()
            ->state([
                'subscription_period' => PeriodEnum::YEARLY, 'subscription_duration' => 1,
                'trial_period' => PeriodEnum::DAILY, 'trial_duration' => 14
            ])
            ->create();
        $endDate = Carbon::now()->addDays(14)->addYear();

        $this->productService->attachProductToUser($product, $user);
        $this->productService->attachProductToUser($product, $user);

        $this->assertHasProductsUsers($product, $user, $endDate);
    }

    private function assertHasProductsUsers(Product $product, User $user, ?Carbon $endDate = null): void
    {
        $result = ProductUser::query()->where('product_id', $product->getKey())->where('user_id', $user->getKey())->first();

        $this->assertNotNull($result);

        $endDate = $endDate === null
            ? $product->has_trial
                ? PeriodEnum::calculatePeriod(Carbon::now(), $product->trial_period, $product->trial_duration)
                : PeriodEnum::calculatePeriod(Carbon::now(), $product->subscription_period, $product->subscription_duration)
            : $endDate;

        $this->assertEquals($result->end_date, $endDate);
    }

    private function assertProductAttachedEventDispatched(Product $product, User $user): void
    {
        Event::assertDispatchedTimes(ProductAttached::class);

        Event::assertDispatched(ProductAttached::class, function (ProductAttached $event) use ($user, $product) {
            return $event->getUser() === $user && $event->getProduct() === $product;
        });
    }
}
