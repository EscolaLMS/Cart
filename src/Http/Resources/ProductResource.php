<?php

namespace EscolaLms\Cart\Http\Resources;

use EscolaLms\Cart\Contracts\Product;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ProductResource extends JsonResource
{
    public function __construct(Product $product)
    {
        parent::__construct($product);
    }

    protected function getProduct(): Product
    {
        return $this->resource;
    }

    public function toArray($request)
    {
        return [
            'product_id' => $this->getProduct()->getKey(),
            'product_type' => $this->getProduct()->getMorphClass(),
            'buyable' => $this->getProduct()->getBuyableByUserAttribute(Auth::user()),
            'owned' => $this->getProduct()->getOwnedByUserAttribute(Auth::user()),
        ];
    }
}
