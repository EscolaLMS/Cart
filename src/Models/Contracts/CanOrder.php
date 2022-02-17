<?php

namespace EscolaLms\Cart\Models\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read \Illuminate\Support\Collection|\EscolaLms\Cart\Models\Order[] $orders
 * @property-read \Treestoneit\ShoppingCart\Models\Cart|null $cart
 */
interface CanOrder extends Authenticatable
{
    public function orders(): HasMany;

    public function cart(): HasOne;
}
