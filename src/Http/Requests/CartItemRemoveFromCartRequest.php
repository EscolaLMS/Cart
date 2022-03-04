<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Models\CartItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CartItemRemoveFromCartRequest extends FormRequest
{
    public function authorize()
    {
        return !!$this->user();
    }

    protected function prepareForValidation()
    {
        parent::prepareForValidation();
        $this->merge([
            'id' => $this->route('id')
        ]);
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists(CartItem::class, 'id')],
        ];
    }

    public function getCartItemId(): int
    {
        return $this->route('id');
    }
}
