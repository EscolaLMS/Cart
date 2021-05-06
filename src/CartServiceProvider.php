<?php

namespace EscolaSoft\Cart;

use EscolaSoft\Cart\Services\Contracts\ShopServiceContract;
use EscolaSoft\Cart\Services\ShopService;
use Illuminate\Support\ServiceProvider;

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
    }
}