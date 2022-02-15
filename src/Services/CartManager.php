<?php

namespace EscolaLms\Cart\Services;

use EscolaLms\Cart\Contracts\Base\Buyable;
use EscolaLms\Cart\Facades\Shop;
use EscolaLms\Cart\Models\Cart;
use EscolaLms\Cart\Models\CartItem;
use EscolaLms\Cart\Services\Contracts\CartManagerContract;
use Illuminate\Database\Eloquent\Model;
use Treestoneit\ShoppingCart\CartManager as BaseCartManager;

class CartManager extends BaseCartManager implements CartManagerContract
{
    protected int $total = 0;

    public function __construct(Cart $cart)
    {
        parent::__construct($cart);
        $this->removeNonexistingBuyables();
    }

    private function removeNonexistingBuyables(): void
    {
        /** @var CartItem $item */
        foreach ($this->content() as $item) {
            if (
                !class_exists($item->buyable_type)
                || !Shop::registeredProduct($item->buyable_type)
                || is_null($item->buyable)
            ) {
                $this->remove($item->getKey());
            }
        }
    }

    public function subtotal(): float
    {
        return round(parent::subtotal(), 0);
    }

    public function subtotalInt(): int
    {
        return (int) self::subtotal();
    }

    public function tax($rate = null): float
    {
        return round(parent::tax($rate), 0);
    }

    public function taxInt(?int $rate = null): int
    {
        return (int) self::tax($rate);
    }

    /** 
     * CartItem total = subtotal + additional fees independen from quantity;
     * Tax is NOT included in this, to get total with tax use `totalWithTax()` method
     */
    public function total(): int
    {
        if (!$this->total) {
            $this->total = (int) $this->items()->sumRounded(fn (CartItem $item) => $item->total, 0);
        }

        return $this->total;
    }

    public function totalWithTax(?int $rate = null): int
    {
        return $this->total() + $this->taxInt($rate);
    }

    public function hasBuyable(Buyable $buyable): bool
    {
        assert($buyable instanceof Model);
        return $this->findBuyable($buyable) !== null;
    }

    public function findBuyable(Buyable $buyable): ?CartItem
    {
        assert($buyable instanceof Model);
        return $this->content()->where('buyable_id', $buyable->getKey())->where('buyable_type', $buyable->getMorphClass())->first();
    }
}
