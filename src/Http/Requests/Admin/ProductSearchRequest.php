<?php

namespace EscolaLms\Cart\Http\Requests\Admin;

use EscolaLms\Cart\Dtos\ProductsSearchDto;
use EscolaLms\Cart\Enums\ProductType;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Rules\ProductableRegisteredRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProductSearchRequest extends FormRequest
{
    public function authorize()
    {
        return Gate::allows('viewAny', Product::class);
    }

    public function rules(): array
    {
        return [
            'productable_id' => ['sometimes', 'integer'],
            'productable_type' => ['required_with:productable_id', 'string', new ProductableRegisteredRule()],
            'type' => ['sometimes', Rule::in(ProductType::getValues())],
            'name' => ['sometimes', 'string'],
            'free' => ['sometimes', 'boolean'],
            'purchasable' => ['sometimes', 'boolean'],
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string'],
            'per_page' => ['sometimes', 'integer'],
            'page' => ['sometimes', 'integer'],
            'order_by' => ['sometimes', Rule::in(['name', 'created_at', 'updated_at', 'price', 'price_old', 'tax_rate', 'type', 'purchasable'])],
            'order' => ['sometimes', Rule::in(['ASC', 'DESC'])],
        ];
    }

    public function getProductableId(): ?int
    {
        return $this->validated()['productable_id'] ?? null;
    }

    public function getProductableType(): ?string
    {
        return $this->validated()['productable_type'] ?? null;
    }

    public function getType(): ?string
    {
        return $this->validated()['type'] ?? null;
    }

    public function getName(): ?string
    {
        return $this->validated()['name'] ?? null;
    }

    public function getFree(): ?bool
    {
        return $this->validated()['free'] ?? null;
    }

    public function getPurchasable(): ?bool
    {
        return $this->validated()['purchasable'] ?? null;
    }

    public function getPerPage(): ?int
    {
        return $this->validated()['per_page'] ?? null;
    }

    public function getTags(): ?array
    {
        return $this->validated()['tags'] ?? null;
    }

    public function toDto(): ProductsSearchDto
    {
        return new ProductsSearchDto(
            $this->getName(),
            $this->getType(),
            $this->getFree(),
            $this->getProductableType(),
            $this->getProductableId(),
            $this->getPurchasable(),
            $this->getPerPage(),
            $this->getTags(),
        );
    }
}
