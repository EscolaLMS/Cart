<?php

namespace EscolaLms\Cart\Models\Traits;

use EscolaLms\Cart\Models\Order;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Treestoneit\ShoppingCart\Models\Cart;

trait CanOrder
{
    public function orders(): HasMany
    {
        /* @var $this User */
        return $this->hasMany(Order::class, 'user_id');
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class, 'user_id');
    }
}
