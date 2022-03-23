<?php

namespace EscolaLms\Cart\Contracts;

use EscolaLms\Cart\Models\Cart;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\ProductUser;
use EscolaLms\Core\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait CanOrderTrait
{
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'products_users')->using(ProductUser::class);
    }

    public function orders(): HasMany
    {
        /** @var User $this */
        return $this->hasMany(Order::class, 'user_id');
    }

    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class, 'user_id');
    }
}
