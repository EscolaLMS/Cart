<?php

namespace EscolaLms\Cart\Models\Contracts\Base;

use Treestoneit\ShoppingCart\Buyable as BaseBuyable;

interface Buyable extends BaseBuyable
{
    public function getBuyableDescription(): string;
    // @phpstan-ignore-next-line
    public function getBuyablePrice(?array $options = null): int;
    public function getExtraFees(): int;
}
