<?php

namespace EscolaLms\Cart\Facades;

use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void registerProduct(string $productClass)
 * @method static bool registeredProduct(string $productClass)
 * 
 * @see \EscolaLms\Cart\Services\Contracts\ShopServiceContract
 */
class Shop extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ShopServiceContract::class;
    }
}
