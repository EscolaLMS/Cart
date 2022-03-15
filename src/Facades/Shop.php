<?php

namespace EscolaLms\Cart\Facades;

use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void registerProductableClass(string $productableClass)
 * @method static bool isProductableClassRegistered(string $productableClass)
 * @method static string canonicalProductableClass(string $productableClass)
 * @method static array listRegisteredProductableClasses()
 * 
 * @see \EscolaLms\Cart\Services\Contracts\ProductServiceContract
 */
class Shop extends Facade
{
    protected static function getFacadeAccessor()
    {
        return ProductServiceContract::class;
    }
}
