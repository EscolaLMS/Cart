<?php

namespace EscolaLms\Cart\QueryBuilders;

use EscolaLms\Cart\Facades\Shop;
use EscolaLms\Core\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ProductModelQueryBuilder extends Builder
{
    public function whereHasProductableClass(string $productable_type): ProductModelQueryBuilder
    {
        return $this->whereHas('productables', fn (Builder $query) => $query->where('productable_type', $productable_type));
    }

    public function whereHasProductable(Model $productable): ProductModelQueryBuilder
    {
        return $this->whereHasProductableClassAndId($productable->getMorphClass(), $productable->getKey());
    }

    public function whereHasProductableClassAndId(string $productable_type, int $productable_id): ProductModelQueryBuilder
    {
        return $this->whereHas('productables', fn (Builder $query) => $query->where('productable_type', $productable_type)->where('productable_id', $productable_id));
    }

    public function whereOwnedByUser(?User $user = null): Builder
    {
        return $this->whereHas('users', fn (Builder $query) => $query->where('users.id', $user->getKey()));
    }

    public function whereHasProductablesOwnedByUser(?User $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $this->whereHas('productables', fn (Builder $query) => $query->whereHas('productable', fn (Builder $subquery) => $subquery->ownedByUser($user)));
    }

    public function whereHasProductablesNotOwnedByUser(?User $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $this->whereHas('productables', fn (Builder $query) => $query->whereHas('productable', fn (Builder $subquery) => $subquery->notOwnedByUser($user)));
    }

    public function whereDoesntHaveProductablesNotOwnedByUser(?User $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $this->whereDoesntHave('productables', fn (Builder $query) => $query->whereHas('productable', fn (Builder $subquery) => $subquery->notOwnedByUser($user)));
    }

    public function whereHasProductablesBuyableByUser(?User $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $this->whereHas('productables', fn (Builder $query) => $query->whereHas('productable', fn (Builder $subquery) => $subquery->buyableByUser($user)));
    }

    public function whereHasProductablesNotBuyableByUser(?User $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $this->whereHas('productables', fn (Builder $query) => $query->whereHas('productable', fn (Builder $subquery) => $subquery->notBuyableByUser($user)));
    }

    public function whereDoesntHaveProductablesNotBuyableByUser(?User $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $this->whereDoesntHave('productables', fn (Builder $query) => $query->whereHas('productable', fn (Builder $subquery) => $subquery->notBuyableByUser($user)));
    }
}
