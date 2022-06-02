<?php

namespace EscolaLms\Cart\Http\Resources;

use EscolaLms\Cart\Contracts\Productable;
use EscolaLms\Cart\Facades\Shop;
use EscolaLms\Cart\Models\ProductProductable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductableGenericResource extends JsonResource
{
    protected ?ProductProductable $productProductable = null;

    public function __construct(Productable $productable, ?ProductProductable $productProductable = null)
    {
        assert($productable instanceof Model);
        parent::__construct($productable);
        $this->productProductable = $productProductable;
    }

    public function getProductable(): Productable
    {
        return $this->resource;
    }

    public function getProductProductable(): ?ProductProductable
    {
        return $this->productProductable;
    }

    public function toArray($request): array
    {
        return [
            'id' => $this->getProductable()->getKey(),
            'morph_class' => $this->getProductable()->getMorphClass(),
            'productable_id' => $this->getProductable()->getKey(),
            'productable_type' => Shop::canonicalProductableClass($this->getProductable()->getMorphClass()),
            'quantity' => $this->getProductProductable() ? $this->getProductProductable()->quantity : 1,
            'name' => $this->getProductable()->getName(),
            'description' => $this->getProductable()->getDescription(),
        ];
    }
}
