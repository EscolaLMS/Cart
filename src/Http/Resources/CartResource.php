<?php

namespace EscolaLms\Cart\Http\Resources;

use EscolaLms\Auth\Traits\ResourceExtandable;
use EscolaLms\Cart\Models\Cart;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class CartResource extends JsonResource
{
    use ResourceExtandable;

    protected ?int $taxRate = null;

    public function __construct(Cart $cart, ?int $taxRate = null)
    {
        $this->taxRate = $taxRate;
        parent::__construct($cart);
    }

    protected function getCart(): Cart
    {
        return $this->resource;
    }

    protected function getCartItemsResourceCollection(): ResourceCollection
    {
        return CartItemResource::collection($this->getCart()->items);
    }

    public function toArray($request): array
    {
        return self::apply([
            'total' => $this->getCart()->total,
            'subtotal' =>  $this->getCart()->subtotal,
            'tax' =>  $this->getCart()->getTaxAttribute($this->taxRate),
            'items' => $this->getCartItemsResourceCollection(),
            'total_with_tax' => $this->getCart()->total_with_tax,
        ], $this);
    }
}
