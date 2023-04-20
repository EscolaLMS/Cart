<?php

namespace EscolaLms\Cart\Http\Requests\Admin;

use EscolaLms\Cart\Exceptions\ProductNotFoundException;
use EscolaLms\Cart\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProductReadRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('view', $this->getProduct());
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
        return $this->route('id');
    }

    public function getProduct(): Product
    {
        $product = Product::find($this->getId());

        if ($product === null) {
            throw new ProductNotFoundException();
        }

        return $product;
    }
}
