<?php

namespace EscolaLms\Cart\Rules;

use EscolaLms\Cart\EscolaLmsCartServiceProvider;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Config;

class MinPrice implements Rule
{
    protected int $min;
    public function __construct()
    {
        $this->min = Config::get(EscolaLmsCartServiceProvider::CONFIG_KEY . '.min_product_price', 0);
    }

    public function passes($attribute, $value): bool
    {
        return $value >= $this->min;
    }

    public function message(): string
    {
        return __('Field :attribute must be greater or equal than ' . $this->min . '.');
    }
}
