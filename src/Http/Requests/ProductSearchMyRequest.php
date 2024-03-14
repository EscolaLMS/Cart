<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Dtos\PageDto;
use EscolaLms\Cart\Dtos\ProductSearchMyCriteriaDto;
use EscolaLms\Cart\Enums\ProductType;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Core\Dtos\OrderDto;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProductSearchMyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('viewMy', Product::class);
    }

    public function rules(): array
    {
        return [
            'type' => ['sometimes', Rule::in(ProductType::getValues())],
            'active' => ['sometimes', 'boolean'],
        ];
    }

    public function getCriteria(): ProductSearchMyCriteriaDto
    {
        return ProductSearchMyCriteriaDto::instantiateFromRequest($this);
    }

    public function getPage(): PageDto
    {
        return PageDto::instantiateFromRequest($this);
    }

    public function getOrder(): OrderDto
    {
        return OrderDto::instantiateFromRequest($this);
    }
}
