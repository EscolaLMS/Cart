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
    private int $quantity_change;
    private ?int $quantity_in_cart;

    public function __construct(Product $product, Cart $cart, int $quantity_change = 1, ?int $quantity_in_cart = null, ?User $user = null)
    {
        $this->product = $product;
        $this->cart = $cart;
        $this->quantity_change = $quantity_change;
        $this->quantity_in_cart = $quantity_in_cart;
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

    public function getQuantityChange(): int
    {
        return $this->quantity_change;
    }

    public function getQuantityInCart(): ?int
    {
        return $this->quantity_in_cart;
    }
}
