<?php

namespace EscolaLms\Cart\Events;

use EscolaLms\Cart\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class AbstractProductableEvent
{
    use Dispatchable, SerializesModels;

    private Model $productable;
    private User $user;

    public function __construct(Model $productable, User $user)
    {
        $this->productable = $productable;
        $this->user = $user;
    }

    public function getProductable(): Model
    {
        return $this->productable;
    }

    public function getUser(): User
    {
        return $this->user;
    }
}
