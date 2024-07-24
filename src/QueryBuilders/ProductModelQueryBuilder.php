<?php

namespace EscolaLms\Cart\QueryBuilders;

use EscolaLms\Cart\Contracts\Productable;
use EscolaLms\Cart\Facades\Shop;
use EscolaLms\Core\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class ProductModelQueryBuilder extends Builder
{
    public function whereHasProductableClass(string $productable_type): ProductModelQueryBuilder
    {
        return $this->whereHas('productables', fn (Builder $query) => $query->where('productable_type', $productable_type));
    }

    public function whereHasProductable(Model|Productable $productable): ProductModelQueryBuilder
    {
        return $this->whereHasProductableClassAndId($productable->getMorphClass(), $productable->getKey());
    }

    public function whereHasProductableClassAndId(string $productable_type, int $productable_id): ProductModelQueryBuilder
    {
        return $this->whereHas('productables', fn (Builder $query) => $query->where('productable_type', $productable_type)->where('productable_id', $productable_id));
    }

    public function whereHasUser(User $user): ProductModelQueryBuilder
    {
        return $this->whereHas('users', fn (Builder $query) => $query->where('users.id', $user->getKey()));
    }

    public function whereHasUserWithProductType(User $user, string $productType, ?bool $active = true): ProductModelQueryBuilder
    {
        return $this->whereHas('users', fn (Builder $query) => $query
            ->where('users.id', $user->getKey())
            ->where('type', $productType)
            ->when($active, fn(Builder $query) => $query->whereDate('end_date', '>=', Carbon::now()))
            ->when(!$active, fn(Builder $query) => $query->whereDate('end_date', '<=', Carbon::now()))
            ->orderBy('end_date', 'desc')
        );
    }

    public function whereDoesntHaveProductablesNotOwnedByUser(?User $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $this->whereDoesntHave('productables', fn (Builder $query) => $query->whereHas('productable', function (Builder $subquery) use ($user) {
            // We need to change queried model to the one that implements Productable class and has NotOwnedByUser scope method
            $class = Shop::canonicalProductableClass(get_class($subquery->getModel()));
            // @phpstan-ignore-next-line
            return $subquery->setModel(new $class)->notOwnedByUser($user);
        }));
    }

    public function whereDoesntHaveProductablesNotBuyableByUser(?User $user = null): Builder
    {
        $user = $user ?? Auth::user();
        return $this->whereDoesntHave('productables', fn (Builder $query) => $query->whereHas('productable', function (Builder $subquery) use ($user) {
            // We need to change queried model to the one that implements Productable class and has NotBuyableByUser scope method
            $class = Shop::canonicalProductableClass(get_class($subquery->getModel()));
            // @phpstan-ignore-next-line
            return $subquery->setModel(new $class)->notBuyableByUser($user);
        }));
    }
}
