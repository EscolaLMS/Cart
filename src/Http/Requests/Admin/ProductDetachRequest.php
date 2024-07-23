<?php

namespace EscolaLms\Cart\Http\Requests\Admin;

use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProductDetachRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('detach', $this->getProduct());
    }

    protected function prepareForValidation(): void
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
        /** @var int $id */
        $id = $this->route('id');
        return $id;
    }

    public function getProduct(): Product
    {
        /** @var Product $product */
        $product = Product::findOrFail($this->getProductId());
        return $product;
    }

    public function getCartUser(): User
    {
        return User::findOrFail($this->input('user_id'));
    }
}
