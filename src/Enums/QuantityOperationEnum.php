<?php

namespace EscolaLms\Cart\Enums;

use EscolaLms\Core\Enums\BasicEnum;

class QuantityOperationEnum extends BasicEnum
{
    public const INCREMENT = 'increment';
    public const DECREMENT = 'decrement';
    public const UNCHANGED = 'unchanged';
}
