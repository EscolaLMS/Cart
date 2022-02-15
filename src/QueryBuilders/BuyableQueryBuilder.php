<?php

namespace EscolaLms\Cart\QueryBuilders;

use Illuminate\Database\Eloquent\Builder;

class BuyableQueryBuilder extends Builder
{
    public function whereHasBuyableId($buyable_id): BuyableQueryBuilder
    {
        return $this->whereHas('items', fn (Builder $query) => $query->where('buyable_id', $buyable_id));
    }

    public function whereHasBuyableType(string $buyable_type): BuyableQueryBuilder
    {
        return $this->whereHas('items', fn (Builder $query) => $query->where('buyable_type', $buyable_type));
    }
}
