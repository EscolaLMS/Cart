<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductSetQuantityInCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('buy', $this->getProduct());
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists(Product::class, 'id')],
            'quantity' => array_merge(['sometimes', 'integer', 'min:0'], $this->getMaxQuantityRule()),
        ];
    }

    public function getMaxQuantityRule(): array
    {
        /** @var Product|null $product */
        $product = Product::find($this->input('id'));

        if (!$product || is_null($product->limit_per_user)) {
            return [];
        }

        return ['max:' . $product->limit_per_user];
    }

    public function getId(): int
    {
        return (int) $this->input('id');
    }

    public function getProduct(): Product
    {
        /** @var Product $product */
        $product = Product::findOrFail($this->getId());
        return $product;
    }

    public function getQuantity(): int
    {
        return $this->input('quantity', 1);
    }
}
