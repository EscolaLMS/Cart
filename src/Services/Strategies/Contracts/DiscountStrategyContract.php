<?php

namespace EscolaLms\Cart\Services\Strategies\Contracts;

interface DiscountStrategyContract
{
    public function total(int $subtotal, int $tax): int;
}
