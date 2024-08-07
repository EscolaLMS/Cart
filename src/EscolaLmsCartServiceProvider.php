<?php

namespace EscolaLms\Cart;

use EscolaLms\Cart\Console\Commands\AbandonedCart;
use EscolaLms\Cart\Providers\AuthServiceProvider;
use EscolaLms\Cart\Providers\EventServiceProvider;
use EscolaLms\Cart\Providers\ScheduleServiceProvider;
use EscolaLms\Cart\Providers\SettingsServiceProvider;
use EscolaLms\Cart\Services\Contracts\OrderServiceContract;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Cart\Services\OrderService;
use EscolaLms\Cart\Services\ProductService;
use EscolaLms\Cart\Services\ShopService;
use EscolaLms\Templates\EscolaLmsTemplatesServiceProvider;
use Illuminate\Support\ServiceProvider;
use Treestoneit\ShoppingCart\CartServiceProvider as TreestoneitCartServiceProvider;

/**
 * SWAGGER_VERSION
 */
class EscolaLmsCartServiceProvider extends ServiceProvider
{
    const CONFIG_KEY = 'escolalms_cart';

    public $singletons = [
        ProductServiceContract::class => ProductService::class,
        OrderServiceContract::class => OrderService::class,
        ShopServiceContract::class => ShopService::class,
    ];

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'cart');

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config.php', 'escolalms_cart');

        $this->app->register(AuthServiceProvider::class);
        $this->app->register(EventServiceProvider::class);
        $this->app->register(SettingsServiceProvider::class);
        $this->app->register(ScheduleServiceProvider::class);

        if (!$this->app->getProviders(EscolaLmsTemplatesServiceProvider::class)) {
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
    }
}
