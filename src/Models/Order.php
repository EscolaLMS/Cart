<?php

namespace EscolaSoft\Cart\Models;

use EscolaLms\Core\Models\User;
use EscolaSoft\Cart\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Treestoneit\ShoppingCart\Models\Cart;

class Order extends Cart
{
    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getQuantityAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getStatusNameAttribute(): string
    {
        return OrderStatus::getName($this->status);
    }

}