<?php

namespace EscolaLms\Cart\Contracts;

use EscolaLms\Cart\Contracts\Base\BuyableTrait;
use EscolaLms\Cart\Models\Cart;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Support\Helper;
use EscolaLms\Core\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * @see \EscolaLms\Cart\Contracts\Product
 */
trait ProductTrait
{
    use BuyableTrait;

    public function scopeBuyableByUser(Builder $query, User $user): Builder
    {
        if (Helper::hasRelation($this, 'users')) {
            return $query->whereDoesntHave('users', fn (Builder $query) => $query->where('users.id', $user->getKey()));
        }
        if (Helper::hasRelation($user, $this->getTable())) {
            return $query->whereNotIn($this->getTable() . '.id', $user->{$this->getTable()}->pluck('id')->toArray());
        }
        throw new Exception(__('Product must implement `scopeBuyableByUser` method'));
    }

    public function scopeOwnedByUser(Builder $query, User $user): Builder
    {
        if (Helper::hasRelation($this, 'users')) {
            return $query->whereHas('users', fn (Builder $query) => $query->where('users.id', $user->getKey()));
        }
        if (Helper::hasRelation($user, $this->getTable())) {
            return $query->whereIn($this->getTable() . 'id', $user->{$this->getTable()}->pluck('id')->toArray());
        }
        throw new Exception(__('Product must implement `scopeOwnedByUser` method'));
    }

    public function getBuyableByUserAttribute(?User $user = null): bool
    {
        return !$this->getOwnedByUserAttribute($user ?? Auth::user());
    }

    public function getOwnedByUserAttribute(?User $user = null): bool
    {
        return $this->scopeOwnedByUser($this::query()->where($this->getTable() . '.id', $this->getKey()), $user ?? Auth::user())->exists();
    }

    public function addedToCart(Cart $cart): void
    {
        // do nothing
    }

    public function afterBought(Order $order): void
    {
        $this->attachToUser($order->user);
    }

    abstract public function attachToUser(User $user): void;

    abstract public function detachFromUser(User $user): void;
}
