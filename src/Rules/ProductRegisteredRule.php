<?php

namespace EscolaLms\Cart\Rules;

use EscolaLms\Cart\Facades\Shop;
use Illuminate\Contracts\Validation\Rule;

class ProductRegisteredRule implements Rule
{
    public function passes($attribute, $value): bool
    {
        return class_exists($value) && Shop::registeredProduct($value);
    }

    public function message(): string
    {
        return __('Class :input must represent registered Product type.');
    }
}
