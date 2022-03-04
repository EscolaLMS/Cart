<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductRemoveFromCartRequest extends FormRequest
{
    public function authorize()
    {
        return !!$this->user();
    }

    protected function prepareForValidation()
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
        return $this->validated()['id'];
    }

    public function getProduct(): Product
    {
        return Product::findOrFail($this->getId());
    }
}
