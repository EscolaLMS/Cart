<?php

namespace EscolaLms\Cart\Services\Strategies\Contracts;

interface DiscountStrategyContract
{
    public function total(float $subtotal, float $tax): float;
}