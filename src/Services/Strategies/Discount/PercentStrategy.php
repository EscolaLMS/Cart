<?php

namespace EscolaSoft\Cart\Services\Strategies\Discount;

use EscolaSoft\Cart\Services\Strategies\Abstracts\DiscountStrategy;

class PercentStrategy extends DiscountStrategy
{
    public function total(float $subtotal, float $tax): float
    {
        return $this->value(($subtotal + $tax) * ((100 - $this->discount->value) / 100));
    }
}