<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Enums\CartPermissionsEnum;
use EscolaLms\Cart\Rules\ProductableExistsRule;
use EscolaLms\Cart\Rules\ProductableRegisteredRule;
use Illuminate\Foundation\Http\FormRequest;

class ProductableAddToCartRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can(CartPermissionsEnum::BUY_PRODUCTS);
    }

    public function rules(): array
    {
        return [
            'productable_id' => ['required', 'integer', new ProductableExistsRule()],
            'productable_type' => ['required', 'string', new ProductableRegisteredRule()]
        ];
    }

    public function getProductableId(): int
    {
        return $this->input('productable_id');
    }

    public function getProductableType(): string
    {
        return $this->input('productable_type');
    }
}
