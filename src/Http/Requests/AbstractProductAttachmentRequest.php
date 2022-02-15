<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Enums\CartPermissionsEnum;
use EscolaLms\Cart\Models\User;
use EscolaLms\Cart\Rules\ProductExistsRule;
use EscolaLms\Cart\Rules\ProductRegisteredRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class AbstractProductAttachmentRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can(CartPermissionsEnum::ATTACH_PRODUCTS);
    }

    public function rules(): array
    {
        return [
            'product_type' => ['required', 'string', new ProductRegisteredRule],
            'product_id' => ['required', new ProductExistsRule()],
            'user_id' => ['required', 'integer', Rule::exists(User::class, 'id')],
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

    public function getUser(): User
    {
        return User::findOrFail($this->input('user_id'));
    }
}
