<?php

namespace EscolaLms\Cart\Http\Requests\Admin;

use EscolaLms\Cart\Models\Category;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Rules\ProductableRegisteredRule;
use EscolaLms\Cart\Rules\ProductProductablesRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProductUpdateRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('update', $this->getProduct());
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'integer', 'min:0'],
            'price_old' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'tax_rate' => ['sometimes', 'integer', 'min:0'],
            'extra_fees' => ['sometimes', 'integer', 'min:0'],
            'purchasable' => ['sometimes', 'boolean'],
            'teaser_url' => ['sometimes', 'nullable', 'string'],
            'poster_url' => ['sometimes', 'nullable', 'string'],
            'poster' => ['sometimes', 'nullable', 'file', 'image'],
            'duration' => ['sometimes', 'nullable', 'string'],
            'limit_per_user' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit_total' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'productables' => ['sometimes', 'array', App::make(ProductProductablesRule::class)],
            'productables.*.id' => ['integer'],
            'productables.*.class' => ['string', new ProductableRegisteredRule()],
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['integer', Rule::in(Category::class, 'id')],
        ];
    }

    public function getId(): int
    {
        return $this->route('id');
    }

    public function getProduct(): Product
    {
        return Product::findOrFail($this->getId());
    }
}
