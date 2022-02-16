<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Enums\CartPermissionsEnum;
use EscolaLms\Cart\Rules\ProductExistsRule;
use EscolaLms\Cart\Rules\ProductRegisteredRule;
use Illuminate\Foundation\Http\FormRequest;

class ProductAddToCartRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can(CartPermissionsEnum::BUY_PRODUCTS);
    }

    public function rules(): array
    {
        return [
            'product_type' => ['required', 'string', new ProductRegisteredRule],
            'product_id' => ['required', new ProductExistsRule()],
        ];
    }

    public function getProductType(): string
    {
        return $this->input('product_type');
    }

    /** 
     * @return string|int
     */
    public function getProductId()
    {
        return $this->input('product_id');
    }
}
