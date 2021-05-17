<?php

namespace EscolaSoft\Cart\Services\Strategies\Abstracts;

use EscolaSoft\Cart\Models\Discount;
use EscolaSoft\Cart\Services\Strategies\Contracts\DiscountStrategyContract;

abstract class DiscountStrategy implements DiscountStrategyContract
{
    protected Discount $discount;

    /**
     * AbstractStrategy constructor.
     * @param Discount $discount
     */
    public function __construct(Discount $discount)
    {
        $this->discount = $discount;
    }

    protected function value(float $value): float
    {
        return $value >= 0 ? round($value, 2) : 0;
    }
}