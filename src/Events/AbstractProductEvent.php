<?php

namespace EscolaLms\Cart\Events;

use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class AbstractProductEvent
{
    use Dispatchable, SerializesModels;

    private Product $product;
    private User $user;

    public function __construct(Product $product, User $user)
    {
        $this->product = $product;
        $this->user = $user;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
