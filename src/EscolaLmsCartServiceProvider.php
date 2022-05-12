<?php

namespace EscolaLms\Cart;

use EscolaLms\Cart\Console\Commands\AbandonedCart;
use EscolaLms\Cart\Providers\AuthServiceProvider;
use EscolaLms\Cart\Providers\EventServiceProvider;
use EscolaLms\Cart\Services\Contracts\OrderServiceContract;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Cart\Services\OrderService;
use EscolaLms\Cart\Services\ProductService;
use EscolaLms\Cart\Services\ShopService;
use EscolaLms\Templates\EscolaLmsTemplatesServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Treestoneit\ShoppingCart\CartServiceProvider as TreestoneitCartServiceProvider;

/**
 * SWAGGER_VERSION
 */
class EscolaLmsCartServiceProvider extends ServiceProvider
{
    public $singletons = [
        ProductServiceContract::class => ProductService::class,
        OrderServiceContract::class => OrderService::class,
        ShopServiceContract::class => ShopService::class,
    ];

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/config.php', 'escolalms_cart');

        $this->app->register(AuthServiceProvider::class);
        $this->app->register(EventServiceProvider::class);

        if (!$this->app->getProviders(EscolaLms\Cart\EscolaLmsTemplatesServiceProvider::class)) {
            $this->app->register(EscolaLmsTemplatesServiceProvider::class);
        }
        if (!$this->app->getProviders(TreestoneitCartServiceProvider::class)) {
            $this->app->register(TreestoneitCartServiceProvider::class);
        }
    }

    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/config.php' => config_path('escolalms_cart.php'),
        ], 'escolalms_cart.config');

        $this->commands(AbandonedCart::class);
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->command('cart:abandoned-event')->dailyAt('1:00');
        });
    }
}
