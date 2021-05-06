<?php

namespace EscolaSoft\Cart\Models\Traits;

use EscolaLms\Core\Models\User;
use EscolaSoft\Cart\Models\Order;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Treestoneit\ShoppingCart\Models\Cart;

trait HasOrders
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