<?php

namespace EscolaLms\Cart\Rules;

use EscolaLms\Cart\Facades\Shop;
use Illuminate\Contracts\Validation\Rule;

class ProductableRegisteredRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        return class_exists($value) && Shop::isProductableClassRegistered($value);
    }

    public function message(): string
    {
        return __('Class :input must represent registered Productable type.');
    }
}
