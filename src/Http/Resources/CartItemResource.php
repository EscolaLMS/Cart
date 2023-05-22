<?php

namespace EscolaLms\Cart\Http\Resources;

use EscolaLms\Auth\Traits\ResourceExtandable;
use EscolaLms\Cart\Models\CartItem;
use EscolaLms\Cart\Models\Product;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    use ResourceExtandable;

    public function __construct(CartItem $cart)
    {
        parent::__construct($cart);
    }

    protected function getCartItem(): CartItem
    {
        return $this->resource;
    }

    public function toArray($request): array
    {
        return self::apply([
            'id' => $this->getCartItem()->getKey(),
            'product_id' => $this->getCartItem()->buyable_id,
            'product_type' => $this->getCartItem()->buyable_type,
            $this->mergeWhen($this->getCartItem()->buyable instanceof Product, fn () => ['product' => ProductResource::make($this->getCartItem()->buyable)]),
            'price' => $this->getCartItem()->price,
            'price_with_tax' => $this->getCartItem()->price_with_tax,
            'quantity' => $this->getCartItem()->quantity,
            'subtotal' => $this->getCartItem()->subtotal,
            'total' => $this->getCartItem()->total,
            'tax_rate' => $this->getCartItem()->tax_rate,
            'tax' => $this->getCartItem()->tax,
            'total_with_tax' => $this->getCartItem()->total_with_tax,
        ], $this);
    }
}
