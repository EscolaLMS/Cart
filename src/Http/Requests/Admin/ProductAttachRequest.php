<?php

namespace EscolaLms\Cart\Http\Requests\Admin;

use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProductAttachRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('attach', $this->getProduct());
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
            'id' => ['required', Rule::exists(Product::class, 'id')],
            'user_id' => ['required', 'integer', Rule::exists(User::class, 'id')],
        ];
    }

    public function getProductId(): int
    {
        return $this->route('id');
    }

    public function getProduct(): Product
    {
        return Product::findOrFail($this->getProductId());
    }

    public function getCartUser(): User
    {
        return User::findOrFail($this->input('user_id'));
    }
}
