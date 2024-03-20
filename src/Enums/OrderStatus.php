<?php

namespace EscolaLms\Cart\Enums;

use EscolaLms\Core\Enums\BasicEnum;

class OrderStatus extends BasicEnum
{
    public const PROCESSING = 0;
    public const PAID = 1;
    public const CANCELLED = 2;

    public const TRIAL_PROCESSING = 3;
    public const TRIAL_PAID = 4;
    public const TRIAL_CANCELLED = 5;

}
