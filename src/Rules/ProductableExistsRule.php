<?php

namespace EscolaLms\Cart\Rules;

use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;

class ProductableExistsRule implements Rule, DataAwareRule
{
    protected array $data = [];

    protected string $productable_class_field = 'productable_type';

    public function __construct(string $productable_class_field = 'productable_type')
    {
        $this->productable_class_field = $productable_class_field;
    }

    public function passes($attribute, $value): bool
    {
        return array_key_exists($this->productable_class_field, $this->data)
            && class_exists($this->data[$this->productable_class_field])
            && app(ProductServiceContract::class)->findProductable($this->data[$this->productable_class_field], $value);
    }

    public function message(): string
    {
        return __('Product with id :input and class :class must exist.', ['class' => $this->data[$this->productable_class_field]]);
    }

    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }
}
