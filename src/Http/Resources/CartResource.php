<?php

namespace EscolaLms\Cart\Http\Resources;

use EscolaLms\Auth\Traits\ResourceExtandable;
use EscolaLms\Cart\Models\Cart;
use Illuminate\Http\Resources\Json\JsonResource;

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

    public function toArray($request)
    {
        return self::apply([
            'total' => $this->getCart()->total,
            'subtotal' =>  $this->getCart()->subtotal,
            'tax' =>  $this->getCart()->getTaxAttribute($this->taxRate),
            'items' => CartItemResource::collection($this->getCart()->items)
        ], $this);
    }
}
