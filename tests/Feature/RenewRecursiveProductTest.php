<?php

namespace EscolaLms\Cart\Tests\Feature;

use EscolaLms\Cart\Enums\PeriodEnum;
use EscolaLms\Cart\Enums\SubscriptionStatus;
use EscolaLms\Cart\Jobs\RenewRecursiveProduct;
use EscolaLms\Cart\Jobs\RenewRecursiveProductUser;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Cart\Tests\TestCase;
use EscolaLms\Core\Models\User;
use EscolaLms\Core\Tests\CreatesUsers;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;

class RenewRecursiveProductTest extends TestCase
{
    use CreatesUsers, DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        Carbon::setTestNow(Carbon::now()->startOfDay());
    }

    public function testRenewRecursiveProductOnlyActive(): void
    {
        $product = Product::factory()->subscriptionWithoutTrial()->state(['subscription_period' => PeriodEnum::DAILY, 'subscription_duration' => 3, 'recursive' => true])->create();
        $user1 = $this->makeStudent();
        $user2 = $this->makeStudent();
        $user3 = $this->makeStudent();
        $user4 = $this->makeStudent();
        $user5 = $this->makeStudent();

        $product->users()->sync([
            $user1->getKey() => ['end_date' => Carbon::now()->addHours(2), 'status' => SubscriptionStatus::ACTIVE],
            $user2->getKey() => ['end_date' => Carbon::now()->subHour(), 'status' => SubscriptionStatus::ACTIVE],
            $user3->getKey() => ['end_date' => Carbon::now()->subHour(), 'status' => SubscriptionStatus::CANCELLED],
            $user4->getKey() => ['end_date' => Carbon::now()->subHour(), 'status' => SubscriptionStatus::EXPIRED],
            $user5->getKey() => ['end_date' => null, 'status' => null]
        ]);

        (new RenewRecursiveProduct())->handle(app(ProductServiceContract::class));

        Queue::assertPushed(RenewRecursiveProductUser::class, 1);
    }
}
