<?php

namespace EscolaLms\Cart\Services\Contracts;

use EscolaLms\Cart\Models\CartItem;
use EscolaLms\Cart\Models\Contracts\Base\Buyable;
use EscolaLms\Cart\Models\Product;

interface CartManagerContract
{
    public function subtotalInt(): int;
    public function taxInt(?int $rate = null): int;
    public function total(): int;
    public function totalWithTax(?int $rate = null): int;
    public function hasBuyable(Buyable $buyable): bool;
    public function findBuyable(Buyable $buyable): ?CartItem;
    public function hasProduct(Product $product): bool;
    public function findProduct(Product $buyable): ?CartItem;
}
