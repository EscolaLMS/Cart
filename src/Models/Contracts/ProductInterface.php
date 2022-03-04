<?php

namespace EscolaLms\Cart\Models\Contracts;

use EscolaLms\Cart\Models\Contracts\Base\Buyable;
use EscolaLms\Cart\Models\Contracts\Base\Taxable;

interface ProductInterface extends Buyable, Taxable
{
}
