<?php

namespace EscolaLms\Cart\Events;

use EscolaLms\Cart\Models\Product;
use EscolaLms\Core\Models\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class AbstractProductEvent
{
    use Dispatchable, SerializesModels;

    private Product $product;
    private User $user;
    private int $quantity;

    public function __construct(Product $product, User $user, int $quantity = 1)
    {
        $this->product = $product;
        $this->user = $user;
        $this->quantity = $quantity;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }
}
