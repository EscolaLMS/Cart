<?php

namespace EscolaLms\Cart\Contracts\Base;

use Treestoneit\ShoppingCart\Taxable as BaseTaxable;

interface Taxable extends BaseTaxable
{
    public function getTaxRate(): int;
}
