<?php

namespace EscolaLms\Cart\Http\Requests\Admin;

use EscolaLms\Cart\Enums\CartPermissionsEnum;
use Illuminate\Foundation\Http\FormRequest;

class ProductableListRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can(CartPermissionsEnum::MANAGE_PRODUCTS);
    }

    public function rules(): array
    {
        return [];
    }
}
