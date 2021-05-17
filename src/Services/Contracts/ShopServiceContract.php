<?php


namespace EscolaSoft\Cart\Services\Contracts;


use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Treestoneit\ShoppingCart\CartContract;

interface ShopServiceContract extends CartContract
{
    public function loadUserCart(Authenticatable $user);

    public function getResource(): JsonResponse;

}