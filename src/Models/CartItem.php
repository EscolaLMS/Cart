<?php

namespace EscolaLms\Cart\Models;

use EscolaLms\Cart\Contracts\Taxable;
use Illuminate\Support\Facades\Config;
use Treestoneit\ShoppingCart\Models\CartItem as BaseCartItem;

class CartItem extends BaseCartItem
{
    public function getTaxRateAttribute(?int $rate = null): int
    {
        if (!$rate && Config::get('shopping-cart.tax.mode') == 'flat') {
            $rate = Config::get('shopping-cart.tax.rate');
        }
        if (!$rate) {
            $rate = $this->buyable instanceof Taxable
                ? $this->buyable->getTaxRate()
                : 0;
        }
        return $rate;
    }

    public function getTaxAttribute(?int $rate = null): int
    {
        return (int) round($this->getSubtotalAttribute() * ($this->getTaxRateAttribute($rate) / 100), 0);
    }

    public function getSubtotalAttribute()
    {
        return (int) round(parent::getSubtotalAttribute(), 0);
    }

    public function getTotalAttribute()
    {
        return (int) round(parent::getTotalAttribute(), 0);
    }

    public function getTotalWithTaxAttribute(?int $rate = null): int
    {
        return $this->total + $this->getTaxAttribute($rate);
    }
}
