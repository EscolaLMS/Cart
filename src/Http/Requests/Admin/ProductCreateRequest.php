<?php

namespace EscolaLms\Cart\Http\Requests\Admin;

use EscolaLms\Cart\Enums\ProductType;
use EscolaLms\Cart\Models\Category;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Rules\MinPrice;
use EscolaLms\Cart\Rules\ProductableRegisteredRule;
use EscolaLms\Cart\Rules\ProductProductablesRule;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProductCreateRequest extends ProductRequest
{
    public function authorize(): bool
    {
        return Gate::allows('create', Product::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'type' => ['required', Rule::in(ProductType::getValues())],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['required', 'integer', new MinPrice()],
            'price_old' => ['sometimes', 'nullable', 'integer', new MinPrice()],
            'tax_rate' => ['sometimes', 'numeric', 'between:0.00,100.00'],
            'extra_fees' => ['sometimes', 'integer', 'min:0'],
            'purchasable' => ['sometimes', 'boolean'],
            'teaser_url' => ['sometimes', 'nullable', 'string'],
            'poster' => ['sometimes', 'nullable', 'file', 'image'],
            'duration' => ['sometimes', 'nullable', 'string'],
            'limit_per_user' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit_total' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'productables' => ['array', App::make(ProductProductablesRule::class)],
            'productables.*.id' => ['integer'],
            'productables.*.class' => ['string', new ProductableRegisteredRule()],
            'productables.*.quantity' => ['sometimes', 'integer', 'min:1'],
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['integer', Rule::exists(Category::class, 'id')],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string'],
            'related_products' => ['sometimes', 'array'],
            'related_products.*' => ['integer'],
            ...$this->subscriptionRules(),
            ...$this->trialRules(),
            'fields' => ['nullable', 'array'],
        ];
    }

}
