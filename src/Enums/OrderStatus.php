<?php

namespace EscolaSoft\Cart\Enums;

use EscolaLms\Core\Enums\BasicEnum;

class OrderStatus extends BasicEnum
{
    public const PROCESSING = 0;
    public const PAID = 1;
    public const CANCELLED = 2;
}