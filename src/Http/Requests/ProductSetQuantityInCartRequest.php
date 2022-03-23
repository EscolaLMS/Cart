<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductSetQuantityInCartRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('buy', $this->getProduct());
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists(Product::class, 'id')],
            'quantity' => ['sometimes', 'integer', 'min:0'],
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

    public function getQuantity(): int
    {
        return $this->input('quantity', 1);
    }
}
