<?php

namespace EscolaLms\Cart\Http\Resources;

use EscolaLms\Auth\Traits\ResourceExtandable;
use EscolaLms\Cart\Models\OrderItem;
use EscolaLms\Cart\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    use ResourceExtandable;

    public function __construct(OrderItem $orderItem)
    {
        parent::__construct($orderItem);
    }

    protected function getOrderItem(): OrderItem
    {
        return $this->resource;
    }

    public function toArray($request): array
    {
        return self::apply([
            'price' =>  $this->getOrderItem()->price,
            'quantity' => $this->getOrderItem()->quantity,
            'subtotal' =>  $this->getOrderItem()->subtotal,
            'tax' =>  $this->getOrderItem()->tax,
            'total' => $this->getOrderItem()->total,
            'total_with_tax' => $this->getOrderItem()->total_with_tax,
            'order_id' => $this->getOrderItem()->order_id,
            'product_id' => $this->getOrderItem()->buyable_id,
            'product_type' => $this->getOrderItem()->buyable_type,
            $this->mergeWhen($this->getOrderItem()->buyable instanceof Product, fn () => ['product' => ProductResource::make($this->getOrderItem()->buyable)]),
        ], $this);
    }
}
