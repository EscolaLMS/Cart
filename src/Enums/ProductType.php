<?php

namespace EscolaLms\Cart\Enums;

use EscolaLms\Core\Enums\BasicEnum;

class ProductType extends BasicEnum
{
    public const SINGLE = 'single';
    public const BUNDLE = 'bundle';
    public const SUBSCRIPTION = 'subscription';
    public const SUBSCRIPTION_ALL_IN = 'subscription-all-in';

    public static function subscriptionTypes(): array
    {
        return [
            self::SUBSCRIPTION,
            self::SUBSCRIPTION_ALL_IN,
        ];
    }

    public static function isSubscriptionType(?string $productType): bool
    {
        return in_array($productType, static::subscriptionTypes());
    }
}
