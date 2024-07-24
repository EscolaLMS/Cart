<?php

namespace EscolaLms\Cart\Http\Resources;

use EscolaLms\Cart\Models\OrderItem;
use EscolaLms\Cart\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemExportResource extends JsonResource
{
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
        return [
            'price' =>  $this->getOrderItem()->price,
            'quantity' => $this->getOrderItem()->quantity,
            'subtotal' =>  $this->getOrderItem()->subtotal,
            'tax' =>  $this->getOrderItem()->tax,
            'total' => $this->getOrderItem()->total,
            'total_with_tax' => $this->getOrderItem()->total_with_tax,
            // @phpstan-ignore-next-line
            $this->mergeWhen($this->getOrderItem()->buyable instanceof Product, fn () => ['product_name' => $this->getOrderItem()->buyable->name]),
        ];
    }
}
