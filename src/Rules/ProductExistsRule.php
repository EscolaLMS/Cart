<?php

namespace EscolaLms\Cart\Rules;

use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class ProductExistsRule implements Rule, DataAwareRule
{
    protected array $data = [];

    public function passes($attribute, $value): bool
    {
        return array_key_exists('product_type', $this->data)
            && class_exists($this->data['product_type'])
            && ($this->data['product_type'])::find($value);
    }

    public function message(): string
    {
        return __('Product with id :attribute and class :class must exist.', ['class' => $this->data['product_type']]);
    }

    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }
}
