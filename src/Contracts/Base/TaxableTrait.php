<?php

namespace EscolaLms\Cart\Contracts\Base;

/**
 * @see \EscolaLms\Cart\Contracts\Taxable
 */
trait TaxableTrait
{
    abstract public function getTaxRate(): int;
}
