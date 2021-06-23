<?php

namespace EscolaLms\Cart\Services\Strategies\Discount;

use EscolaLms\Cart\Services\Strategies\Abstracts\DiscountStrategy;

class AmountStrategy extends DiscountStrategy
{
    public function total(float $subtotal, float $tax): float
    {
        return $this->value(($subtotal + $tax) - $this->discount->value);
    }
}