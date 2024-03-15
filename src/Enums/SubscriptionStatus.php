<?php

namespace EscolaLms\Cart\Enums;

use EscolaLms\Core\Enums\BasicEnum;

class SubscriptionStatus extends BasicEnum
{
    public const ACTIVE = 'active';
    public const CANCELLED = 'cancelled';
    public const EXPIRED = 'expired';
}
