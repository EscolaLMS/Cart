<?php

namespace EscolaLms\Cart\Http\Requests\Admin;

use EscolaLms\Cart\Enums\CartPermissionsEnum;
use EscolaLms\Cart\Models\User;
use EscolaLms\Cart\Rules\ProductableExistsRule;
use EscolaLms\Cart\Rules\ProductableRegisteredRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductableDetachRequest extends FormRequest
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
            'user_id' => ['required', 'integer', Rule::exists(User::class, 'id')],
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

    public function getUser(): User
    {
        return User::findOrFail($this->input('user_id'));
    }
}
