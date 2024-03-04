<?php

namespace EscolaLms\Cart\Http\Requests\Admin;

use EscolaLms\Cart\Enums\PeriodEnum;
use EscolaLms\Cart\Enums\ProductType;
use EscolaLms\Cart\Models\Category;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Rules\MinPrice;
use EscolaLms\Cart\Rules\PosterRule;
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
            'type' => ['sometimes', Rule::in(ProductType::getValues())],
            'description' => ['sometimes', 'nullable', 'string', 'max:65535'],
            'price' => ['sometimes', 'integer', new MinPrice()],
            'price_old' => ['sometimes', 'nullable', 'integer', new MinPrice()],
            'tax_rate' => ['sometimes', 'numeric', 'between:0.00,100.00'],
            'extra_fees' => ['sometimes', 'integer', 'min:0'],
            'purchasable' => ['sometimes', 'boolean'],
            'teaser_url' => ['sometimes', 'nullable', 'string'],
            'poster_url' => ['sometimes', 'nullable', 'string'],
            'poster' => [new PosterRule($this->route('id'))],
            'duration' => ['sometimes', 'nullable', 'string'],
            'limit_per_user' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'limit_total' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'productables' => ['sometimes', 'array', App::make(ProductProductablesRule::class)],
            'productables.*.id' => ['integer'],
            'productables.*.class' => ['string', new ProductableRegisteredRule()],
            'productables.*.quantity' => ['sometimes', 'integer', 'min:1'],
            'categories' => ['sometimes', 'array'],
            'categories.*' => ['integer', Rule::exists(Category::class, 'id')],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string'],
            'related_products' => ['sometimes', 'array'],
            'related_products.*' => ['integer'],
            // subscription
            'subscription_period' => ['required_if:type,' . ProductType::SUBSCRIPTION, Rule::in(PeriodEnum::getValues())],
            'subscription_duration' => ['required_if:type,' . ProductType::SUBSCRIPTION, 'integer', 'gt:0'],
            'recursive' => ['required_if:type,' . ProductType::SUBSCRIPTION, 'boolean'],
            // trial
            'has_trial' => ['required_if:type,' . ProductType::SUBSCRIPTION, 'boolean'],
            'trial_period' => ['required_if:type,' . ProductType::SUBSCRIPTION . ',has_trial,true', Rule::in(PeriodEnum::getValues())],
            'trial_duration' => ['required_if:type,' . ProductType::SUBSCRIPTION . ',has_trial,true', 'integer', 'gt:0'],
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
