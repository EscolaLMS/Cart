<?php

namespace EscolaLms\Cart\Tests\Feature;

use EscolaLms\Cart\Enums\PeriodEnum;
use EscolaLms\Cart\Enums\SubscriptionStatus;
use EscolaLms\Cart\Events\ProductBought;
use EscolaLms\Cart\Jobs\RenewRecursiveProductUser;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Services\Contracts\OrderServiceContract;
use EscolaLms\Cart\Tests\TestCase;
use EscolaLms\Core\Models\User;
use EscolaLms\Core\Tests\CreatesUsers;
use EscolaLms\Payments\Facades\PaymentGateway;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;

class RenewRecursiveProductUserTest extends TestCase
{
    use CreatesUsers, DatabaseTransactions;

    private OrderServiceContract $orderService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->orderService = app(OrderServiceContract::class);

        Queue::fake();
        Carbon::setTestNow(Carbon::now()->startOfDay());
    }

    public function testRenewRecursiveProductUserOnlyActive(): void
    {
        Event::fake([ProductBought::class]);
        PaymentGateway::fake();

        $user1 = $this->makeStudent();
        $product = Product::factory()->subscriptionWithoutTrial()->state(['subscription_period' => PeriodEnum::DAILY, 'subscription_duration' => 3, 'extra_fees' => 0])->create();
        $this->makeOrder($product, $user1);

        $product->users()->sync([
            $user1->getKey() => ['end_date' => Carbon::now()->subDays(3), 'status' => SubscriptionStatus::ACTIVE],
        ]);
        $productUser = $product->users()->where('user_id', $user1->getKey())->first()->pivot;


        $this->assertEquals(Carbon::now()->subDays(3), $productUser->end_date);
        $this->assertProductUserHas($user1, $product, Carbon::now()->subDays(3), SubscriptionStatus::ACTIVE);

        (new RenewRecursiveProductUser($product->users()->where('user_id', $user1->getKey())->first()->pivot))->handle(app(OrderServiceContract::class));

        $productUser->refresh();
        $this->assertEquals(Carbon::now(), $productUser->end_date);
        $this->assertProductUserHas($user1, $product, Carbon::now(), SubscriptionStatus::ACTIVE);
    }

    public function testRenewRecursiveProductUserOnlyActiveUnableToRenewDriver(): void
    {
        Event::fake([ProductBought::class]);
        Config::set('escola_settings.use_database', true);
        Config::set('escolalms_payments.default_gateway', 'free');

        $user1 = $this->makeStudent();
        $product = Product::factory()->subscriptionWithoutTrial()->state(['subscription_period' => PeriodEnum::DAILY, 'subscription_duration' => 3, 'extra_fees' => 0])->create();
        $this->makeOrder($product, $user1);

        $product->users()->sync([
            $user1->getKey() => ['end_date' => Carbon::now()->subDays(3), 'status' => SubscriptionStatus::ACTIVE],
        ]);
        $productUser = $product->users()->where('user_id', $user1->getKey())->first()->pivot;

        $this->assertEquals(Carbon::now()->subDays(3), $productUser->end_date);
        $this->assertProductUserHas($user1, $product, Carbon::now()->subDays(3), SubscriptionStatus::ACTIVE);

        (new RenewRecursiveProductUser($product->users()->where('user_id', $user1->getKey())->first()->pivot))->handle(app(OrderServiceContract::class));

        $productUser->refresh();
        $this->assertEquals(Carbon::now()->subDays(3), $productUser->end_date);
        $this->assertProductUserHas($user1, $product, Carbon::now()->subDays(3), SubscriptionStatus::ACTIVE);
    }

    private function makeOrder(Product $product, User $user): Order
    {
        $order = $this->orderService->createOrderFromProduct($product, $user->getKey());
        $paymentProcessor = $order->process();
        $paymentProcessor->purchase();

        return $order;
    }

    private function assertProductUserHas(User $user, Product $product, ?Carbon $endDate, ?string $status): void
    {
        $this->assertDatabaseHas('products_users', [
            'user_id' => $user->getKey(),
            'product_id' => $product->getKey(),
            'end_date' => $endDate,
            'status' => $status
        ]);
    }
}
