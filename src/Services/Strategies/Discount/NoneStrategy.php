<?php

namespace EscolaSoft\Cart\Services\Strategies\Discount;

use EscolaSoft\Cart\Services\Strategies\Contracts\DiscountStrategyContract;

class NoneStrategy implements DiscountStrategyContract
{
    public function total(float $subtotal, float $tax): float
    {
        return $subtotal + $tax;
    }
}