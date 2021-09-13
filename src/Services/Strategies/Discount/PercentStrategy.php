<?php

namespace EscolaLms\Cart\Services\Strategies\Discount;

use EscolaLms\Cart\Services\Strategies\Abstracts\DiscountStrategy;

class PercentStrategy extends DiscountStrategy
{
    public function total(int $subtotal, int $tax): int
    {
        $result = intdiv(($subtotal + $tax) * (100 - $this->discount->value), 100);
        return $this->value($result);
    }
}
