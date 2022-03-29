<?php

namespace EscolaLms\Cart\Events;

use EscolaLms\Cart\Models\Cart;
use EscolaLms\Cart\Models\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class AbstractCartEvent
{
    use Dispatchable, SerializesModels;

    private Cart $cart;
    private User $user;

    public function __construct(Cart $cart, ?User $user = null)
    {
        $this->cart = $cart;
        $this->user = $user ?? $cart->user;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
