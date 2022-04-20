<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProductReadRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        parent::prepareForValidation();
        $this->merge([
            'id' => $this->route('id')
        ]);
    }

    public function rules()
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
