<?php

namespace EscolaLms\Cart\Events;

use EscolaLms\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class AbstractProductableEvent
{
    use Dispatchable, SerializesModels;

    private Model $productable;
    private User $user;
    private int $quantity;

    public function __construct(Model $productable, User $user, int $quantity = 1)
    {
        $this->productable = $productable;
        $this->user = $user;
        $this->quantity = $quantity;
    }

    public function getProductable(): Model
    {
        return $this->productable;
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
