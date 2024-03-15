<?php

namespace EscolaLms\Cart\Enums;

use EscolaLms\Core\Enums\BasicEnum;
use Illuminate\Support\Carbon;

class PeriodEnum extends BasicEnum
{
    public const DAILY = 'daily';
    public const MONTHLY = 'monthly';
    public const YEARLY = 'yearly';

    public static function calculatePeriod(?Carbon $carbon, ?string $period, ?int $duration): ?Carbon
    {
        if (!$carbon) {
            return null;
        }

        return match ($period) {
            self::DAILY => $carbon->addDays($duration),
            self::MONTHLY => $carbon->addMonths($duration),
            self::YEARLY => $carbon->addYears($duration),
            default => null
        };
    }
}
