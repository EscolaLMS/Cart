<?php

namespace EscolaLms\Cart\Http\Resources;

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
