<?php

namespace EscolaLms\Cart\Services\Strategies\Abstracts;

use EscolaLms\Cart\Models\Discount;
use EscolaLms\Cart\Services\Strategies\Contracts\DiscountStrategyContract;

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

    protected function value(int $value): int
    {
        return $value >= 0 ? $value : 0;
    }
}
