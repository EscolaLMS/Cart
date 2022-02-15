<?php

namespace EscolaLms\Cart\Services\Contracts;

use EscolaLms\Cart\Contracts\Base\Buyable;
use EscolaLms\Cart\Models\CartItem;

interface CartManagerContract
{
    public function subtotalInt(): int;
    public function taxInt(?int $rate = null): int;
    public function total(): int;
    public function totalWithTax(?int $rate = null): int;
    public function hasBuyable(Buyable $buyable): bool;
    public function findBuyable(Buyable $buyable): ?CartItem;
}
