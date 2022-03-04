<?php

namespace EscolaLms\Cart\QueryBuilders;

use EscolaLms\Cart\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class OrderItemModelQueryBuilder extends Builder
{
    public function whereProductId(int $product_id): OrderItemModelQueryBuilder
    {
        return $this->whereBuyableClassAndId(Product::class, $product_id);
    }

    public function whereBuyableClassAndId(string $buyable_type, int $buyable_id): OrderItemModelQueryBuilder
    {
        return $this->where('buyable_type', $buyable_type)->where('buyable_id', $buyable_id);
    }

    public function whereHasProductable(Model $productable): OrderModelQueryBuilder
    {
        return $this->whereHasProductableClassAndId($productable->getMorphClass(), $productable->getKey());
    }

    public function whereHasProductableClassAndId(string $productable_type, int $productable_id): OrderModelQueryBuilder
    {
        return $this->whereHas(
            'buyable',
            fn (Builder $query) => $query->whereHas(
                'productables',
                fn (Builder $subquery) => $subquery->where('productable_type', $productable_type)->where('productable_id', $productable_id)
            )
        );
    }
}
