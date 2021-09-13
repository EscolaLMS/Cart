<?php

namespace EscolaLms\Cart\Services\Strategies\Discount;

use EscolaLms\Cart\Services\Strategies\Contracts\DiscountStrategyContract;

class NoneStrategy implements DiscountStrategyContract
{
    public function total(int $subtotal, int $tax): int
    {
        return $subtotal + $tax;
    }
}
