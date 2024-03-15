<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\User;
use EscolaLms\Cart\Rules\ProductableExistsRule;
use EscolaLms\Cart\Rules\ProductableRegisteredRule;
use Illuminate\Support\Facades\Gate;

class ProductableAttachRequest extends ProductRequest
{
    public function authorize(): bool
    {
        return Gate::allows('attachToProduct', Product::class);
    }

    public function rules(): array
    {
        return [
            'productable_id' => ['required', new ProductableExistsRule()],
            'productable_type' => ['required', 'string', new ProductableRegisteredRule()]
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

}
