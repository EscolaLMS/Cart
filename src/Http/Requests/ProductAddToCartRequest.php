<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductAddToCartRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('buy', $this->getProduct());
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists(Product::class, 'id')],
        ];
    }

    public function getId(): int
    {
        return $this->input('id');
    }

    public function getProduct(): Product
    {
        return Product::findOrFail($this->getId());
    }
}
