<?php

namespace EscolaLms\Cart\Http\Requests\Admin;

use EscolaLms\Cart\Dtos\OrdersSearchDto;
use EscolaLms\Cart\Enums\OrderStatus;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Rules\ProductableRegisteredRule;
use EscolaLms\Core\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class OrderSearchRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('viewAny', Order::class);
    }

    public function rules()
    {
        return [
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date'],
            'user_id' => ['sometimes', 'integer', Rule::exists(User::class, 'id')],
            'product_id' => ['sometimes', 'integer', Rule::exists(Product::class, 'id')],
            'productable_id' => ['sometimes', 'integer'],
            'productable_type' => ['sometimes', 'string', new ProductableRegisteredRule()],
            'per_page' => ['sometimes', 'integer'],
            'page' => ['sometimes', 'integer'],
            'order_by' => ['sometimes', Rule::in(['created_at', 'updated_at', 'user_id'])],
            'order' => ['sometimes', Rule::in(['ASC', 'DESC'])],
            'status' => ['sometimes', Rule::in(OrderStatus::getValues())]
        ];
    }

    public function getDateFrom(): ?Carbon
    {
        return $this->has('date_from') ? Carbon::make($this->validated()['date_from']) : null;
    }

    public function getDateTo(): ?Carbon
    {
        return $this->has('date_to') ? Carbon::make($this->validated()['date_to']) : null;
    }

    public function getUserId(): ?int
    {
        return $this->validated()['user_id'] ?? null;
    }

    public function getProductId(): ?int
    {
        return $this->validated()['product_id'] ?? null;
    }

    public function getProductableId(): ?int
    {
        return $this->validated()['productable_id'] ?? null;
    }

    public function getProductableType(): ?string
    {
        return $this->validated()['productable_type'] ?? null;
    }

    public function getPerPage(): ?int
    {
        return $this->validated()['per_page'] ?? null;
    }

    public function getStatus(): ?int
    {
        return $this->validated()['status'] ?? null;
    }

    public function toDto(): OrdersSearchDto
    {
        return new OrdersSearchDto(
            $this->getDateFrom(),
            $this->getDateTo(),
            $this->getUserId(),
            $this->getProductId(),
            $this->getProductableId(),
            $this->getProductableType(),
            $this->getStatus(),
            $this->getPerPage(),
        );
    }
}
