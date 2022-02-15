<?php

namespace EscolaLms\Cart\Contracts\Base;

use Treestoneit\ShoppingCart\Buyable as BaseBuyable;

interface Buyable extends BaseBuyable
{
    public function getBuyableDescription(): string;
    public function getBuyablePrice(?array $options = null): int;
    public function getExtraFees(): int;
}
