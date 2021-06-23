<?php

namespace EscolaLms\Cart\Services\Contracts;

use EscolaLms\Cart\Models\Contracts\CanOrder;
use EscolaLms\Payments\Dtos\Contracts\PaymentMethodContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Treestoneit\ShoppingCart\Buyable;
use Treestoneit\ShoppingCart\CartContract;
use Treestoneit\ShoppingCart\Models\Cart;

interface ShopServiceContract extends CartContract
{
    public function loadUserCart(Authenticatable $user);

    public function getResource(): JsonResponse;

    public function addUnique(Buyable $item): self;

    public function removeItemFromCart(string $item): void;

    public function purchase(PaymentMethodContract $paymentMethod = null): void;

    public function setCart(Cart $cart): void;

    public function setUser(CanOrder $user): void;
}
