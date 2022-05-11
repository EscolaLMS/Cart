<?php

namespace EscolaLms\Cart\Http\Resources;

use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\ProductProductable;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Categories\Http\Resources\CategoryResource;
use EscolaLms\Tags\Models\Tag;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ProductResource extends BaseProductResource
{
    public function toArray($request): array
    {
        return array_merge(
            parent::toArray($request),
            ['related_products' => BaseProductResource::collection($this->getProduct()->relatedProducts)]
        );
    }
}
