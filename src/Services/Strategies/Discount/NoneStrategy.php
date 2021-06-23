<?php

namespace EscolaLms\Cart\Services\Strategies\Discount;

use EscolaLms\Cart\Services\Strategies\Contracts\DiscountStrategyContract;

class NoneStrategy implements DiscountStrategyContract
{
    public function total(float $subtotal, float $tax): float
    {
        return $subtotal + $tax;
    }
}