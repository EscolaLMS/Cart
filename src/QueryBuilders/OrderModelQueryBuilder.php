<?php

namespace EscolaLms\Cart\QueryBuilders;

use EscolaLms\Cart\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class OrderModelQueryBuilder extends Builder
{
    public function whereHasProduct(int $product_id): OrderModelQueryBuilder
    {
        return $this->whereHasBuyable(Product::class, $product_id);
    }

    public function whereHasBuyable(string $buyable_type, int $buyable_id): OrderModelQueryBuilder
    {
        return $this->whereHas('items', fn (Builder $query) => $query->where('buyable_type', $buyable_type)->where('buyable_id', $buyable_id));
    }

    public function whereHasProductableClass(string $productable_type): OrderModelQueryBuilder
    {
        return $this->whereHas(
            'items',
            fn (Builder $query) => $query->whereHas(
                'buyable',
                fn (Builder $subquery) => $subquery->whereHas(
                    'productables',
                    fn (Builder $subsubquery) => $subsubquery->where('productable_type', $productable_type)
                )
            )
        );
    }

    public function whereHasProductable(string $productable_type, int $productable_id): OrderModelQueryBuilder
    {
        return $this->whereHas(
            'items',
            fn (Builder $query) => $query->whereHas(
                'buyable',
                fn (Builder $subquery) => $subquery->whereHas(
                    'productables',
                    fn (Builder $subsubquery) => $subsubquery->where('productable_type', $productable_type)->where('productable_id', $productable_id)
                )
            )
        );
    }
}
