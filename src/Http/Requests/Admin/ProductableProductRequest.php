<?php

namespace EscolaLms\Cart\Http\Requests\Admin;

use EscolaLms\Cart\Enums\CartPermissionsEnum;
use EscolaLms\Cart\Rules\ProductableExistsRule;
use EscolaLms\Cart\Rules\ProductableRegisteredRule;
use Illuminate\Foundation\Http\FormRequest;

class ProductableProductRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can(CartPermissionsEnum::MANAGE_PRODUCTS);
    }

    public function rules(): array
    {
        return [
            'productable_id' => ['required', new ProductableExistsRule()],
            'productable_type' => ['required', 'string', new ProductableRegisteredRule()],
        ];
    }

    public function getProductableId(): int
    {
        return $this->validated()['productable_id'];
    }

    public function getProductableType(): string
    {
        return $this->validated()['productable_type'];
    }
}
