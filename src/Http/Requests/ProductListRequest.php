<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Enums\CartPermissionsEnum;
use EscolaLms\Cart\Rules\ProductRegisteredRule;
use Illuminate\Foundation\Http\FormRequest;

class ProductListRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can(CartPermissionsEnum::LIST_PRODUCTS);
    }

    public function rules(): array
    {
        return [
            'product_type' => ['sometimes', 'string', new ProductRegisteredRule()],
        ];
    }

    public function getProductType(): ?string
    {
        return $this->input('product_type');
    }
}
