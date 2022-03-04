<?php

namespace EscolaLms\Cart\Events;

use EscolaLms\Cart\Models\Cart;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Core\Models\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class AbstractProductCartEvent
{
    use Dispatchable, SerializesModels;

    private Product $product;
    private User $user;
    private Cart $cart;

    public function __construct(Product $product, Cart $cart, ?User $user = null)
    {
        $this->product = $product;
        $this->cart = $cart;
        $this->user = $user ?? $cart->user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }
}
