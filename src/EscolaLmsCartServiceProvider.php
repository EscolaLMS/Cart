<?php

namespace EscolaLms\Cart;

use EscolaLms\Cart\Providers\AuthServiceProvider;
use EscolaLms\Cart\Services\Contracts\OrderServiceContract;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Cart\Services\OrderService;
use EscolaLms\Cart\Services\ShopService;
use Illuminate\Support\ServiceProvider;
use Treestoneit\ShoppingCart\CartServiceProvider as TreestoneitCartServiceProvider;

/**
 * SWAGGER_VERSION
 */
class EscolaLmsCartServiceProvider extends ServiceProvider
{
    public $singletons = [
        ShopServiceContract::class => ShopService::class,
        OrderServiceContract::class => OrderService::class,
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
    }
}
