<?php

namespace EscolaLms\Cart\Models;

use EscolaLms\Cart\Services\CartManager;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Treestoneit\ShoppingCart\Models\Cart as BaseCart;

/**
 * EscolaLms\Cart\Models\Cart
 *
 * @property int $id
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int $subtotal
 * @property-read int $tax
 * @property-read int $total
 * @property-read int $total_with_tax
 * @property-read \Treestoneit\ShoppingCart\Models\CartItemCollection|\EscolaLms\Cart\Models\CartItem[] $items
 * @property-read int|null $items_count
 * @property-read \EscolaLms\Cart\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Cart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cart query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereUserId($value)
 * @mixin \Eloquent
 */
class Cart extends BaseCart
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function getCartManagerAttribute(): CartManager
    {
        return app(ShopServiceContract::class)->cartManagerForCart($this);
    }

    public function getSubtotalAttribute(): int
    {
        return $this->cartManager->subtotalInt();
    }

    public function getTotalAttribute(): int
    {
        return $this->cartManager->total();
    }

    public function getTaxAttribute(?int $rate = null): int
    {
        return $this->cartManager->taxInt($rate);
    }

    public function getTotalWithTaxAttribute(?int $rate = null): int
    {
        return $this->total + $this->getTaxAttribute($rate);
    }
}
