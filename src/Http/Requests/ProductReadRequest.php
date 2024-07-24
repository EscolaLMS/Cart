<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProductReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        $this->merge([
            'id' => $this->route('id')
        ]);
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists(Product::class, 'id')],
        ];
    }

    public function getId(): int
    {
        return (int) $this->input('id');
    }

    public function getProduct(): Product
    {
        /** @var Product $product */
        $product = Product::findOrFail($this->getId());
        return $product;
    }
}
