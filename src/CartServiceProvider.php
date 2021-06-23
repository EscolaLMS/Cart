<?php

namespace EscolaLms\Cart;

use EscolaLms\Cart\Models\User;
use EscolaLms\Cart\Providers\EventServiceProvider;
use EscolaLms\Cart\Services\Contracts\OrderProcessingServiceContract;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Cart\Services\OrderProcessingService;
use EscolaLms\Cart\Services\ShopService;
use Illuminate\Support\ServiceProvider;

/**
 * SWAGGER_VERSION
 */
class CartServiceProvider extends ServiceProvider
{
    public $singletons = [
        ShopServiceContract::class => ShopService::class
    ];

    public function boot()
    {
        $this->loadRoutesFrom(__DIR__ . '/routes.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->app['router']->aliasMiddleware('role', \Spatie\Permission\Middlewares\RoleMiddleware::class);

        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/cart.php', 'cart');
        $this->app->register(EventServiceProvider::class);
        $this->app->singleton(OrderProcessingServiceContract::class, OrderProcessingService::class);
    }

    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__ . '/../config/cart.php' => config_path('cart.php'),
        ], 'cart.config');
    }

    public static function getCartOwnerModel(): string
    {
        return config('cart.cart_owner_model', User::class);
    }
}
