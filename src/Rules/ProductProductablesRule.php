<?php

namespace EscolaLms\Cart\Rules;

use EscolaLms\Cart\Enums\ProductType;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Arr;

class ProductProductablesRule implements Rule, DataAwareRule
{
    private ProductServiceContract $productService;
    protected array $data = [];
    private string $message;

    public function __construct(ProductServiceContract $productService)
    {
        $this->productService = $productService;
    }

    public function passes($attribute, $value): bool
    {
        if (!is_array($value)) {
            $this->message = __(':field must be an array.');
            return false;
        }

        if ($this->data['type'] === ProductType::SINGLE && count($value) > 1) {
            $this->message = __('Product with type `single` can have only one Productable');
            return false;
        }
        foreach ($value as $productable) {
            if (!Arr::has($productable, ['id', 'class'])) {
                $this->message = __('Each productable must be an array with keys `id` and `class`');
                return false;
            }

            $class = $productable['class'];
            $id = $productable['id'];

            $productable = $this->productService->findProductable($productable['class'], $productable['id']);
            if (is_null($productable)) {
                $this->message = __('Productable of class :class with id :id does not exist', ['class' => $class, 'id' => $id]);
                return false;
            }
        }

        return true;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }
}
