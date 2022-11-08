<?php

namespace EscolaLms\Cart\Providers;

use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Policies\OrderPolicy;
use EscolaLms\Cart\Policies\ProductPolicy;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Order::class => OrderPolicy::class,
        Product::class => ProductPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        if (!$this->app->routesAreCached() && method_exists(Passport::class, 'routes')) {
            Passport::routes();
        }

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('applications:export')->dailyAt('1:00');
        });
    }
}
