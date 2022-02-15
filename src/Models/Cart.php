<?php

namespace EscolaLms\Cart\Models;

use EscolaLms\Cart\Services\CartManager;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Treestoneit\ShoppingCart\Models\Cart as BaseCart;

class Cart extends BaseCart
{
    private ?CartManager $cartManager = null;

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function getCartManager(): CartManager
    {
        return $this->cartManager ?? ($this->cartManager = app(ShopServiceContract::class)->cartManagerForCart($this));
    }

    public function getSubtotalAttribute(): int
    {
        return $this->getCartManager()->subtotalInt();
    }

    public function getTotalAttribute(): int
    {
        return $this->getCartManager()->total();
    }

    public function getTaxAttribute(?int $rate = null): int
    {
        return $this->getCartManager()->taxInt($rate);
    }

    public function getTotalWithTaxAttribute(?int $rate = null): int
    {
        return $this->total + $this->getTaxAttribute($rate);
    }
}
