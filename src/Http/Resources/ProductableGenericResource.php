<?php

namespace EscolaLms\Cart\Http\Resources;

use EscolaLms\Cart\Contracts\Productable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductableGenericResource extends JsonResource
{
    public function __construct(Productable $productable)
    {
        assert($productable instanceof Model);
        parent::__construct($productable);
    }

    public function getProductable(): Productable
    {
        return $this->resource;
    }

    public function toArray($request)
    {
        return [
            'id' => $this->getProductable()->getKey(),
            'class' => $this->getProductable()->getMorphClass(),
            'name' => $this->getProductable()->getName(),
            'description' => $this->getProductable()->getDescription(),
        ];
    }
}
