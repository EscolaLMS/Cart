<?php

namespace EscolaLms\Cart\Contracts;

use EscolaLms\Cart\Http\Resources\ProductableGenericResource;
use EscolaLms\Cart\Support\ModelHelper;
use EscolaLms\Core\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

/**
 * @see \EscolaLms\Cart\Contracts\Productable
 */
trait ProductableTrait
{
    public function scopeBuyableByUser(Builder $query, User $user): Builder
    {
        try {
            return $this->scopeNotOwnedByUser($query, $user);
        } catch (Exception $ex) {
        }
        throw new Exception(__('Productable must implement `scopeBuyableByUser` method'));
    }

    public function scopeNotBuyableByUser(Builder $query, User $user): Builder
    {
        try {
            return $this->scopeOwnedByUser($query, $user);
        } catch (Exception $ex) {
        }
        throw new Exception(__('Productable must implement `scopeNotBuyableByUser` method'));
    }

    public function scopeOwnedByUser(Builder $query, User $user): Builder
    {
        if (ModelHelper::hasRelation($this, 'users')) {
            return $query->whereHas('users', fn (Builder $query) => $query->where('users.id', $user->getKey()));
        }
        if (ModelHelper::hasRelation($user, $this->getTable())) {
            return $query->whereIn($this->getTable() . 'id', $user->{$this->getTable()}->pluck('id')->toArray());
        }
        throw new Exception(__('Productable must implement `scopeOwnedByUser` method'));
    }

    public function scopeNotOwnedByUser(Builder $query, User $user): Builder
    {
        if (ModelHelper::hasRelation($this, 'users')) {
            return $query->whereDoesntHave('users', fn (Builder $query) => $query->where('users.id', $user->getKey()));
        }
        if (ModelHelper::hasRelation($user, $this->getTable())) {
            return $query->whereNotIn($this->getTable() . '.id', $user->{$this->getTable()}->pluck('id')->toArray());
        }
        throw new Exception(__('Productable must implement `scopeNotOwnedByUser` method'));
    }

    public function getBuyableByUserAttribute(?User $user = null): bool
    {
        return $this->scopeBuyableByUser($this::query()->where($this->getTable() . '.id', $this->getKey()), $user ?? Auth::user())->exists();
    }

    public function getOwnedByUserAttribute(?User $user = null): bool
    {
        return $this->scopeOwnedByUser($this::query()->where($this->getTable() . '.id', $this->getKey()), $user ?? Auth::user())->exists();
    }

    public function attachToUser(User $user): void
    {
        if (ModelHelper::hasRelation($this, 'users') && $this->users() instanceof BelongsToMany) {
            $this->users()->syncWithoutDetaching($user->getKey());
        } elseif (ModelHelper::hasRelation($user, $this->getTable()) && $user->{$this->getTable()}() instanceof BelongsToMany) {
            $user->{$this->getTable()}()->syncWithoutDetaching($this->getKey());
        }
        throw new Exception(__('Productable must implement `attachToUser` method'));
    }

    public function detachFromUser(User $user): void
    {
        if (ModelHelper::hasRelation($this, 'users') && $this->users() instanceof BelongsToMany) {
            $this->users()->detach($user->getKey());
        } elseif (ModelHelper::hasRelation($user, $this->getTable()) && $user->{$this->getTable()}() instanceof BelongsToMany) {
            $user->{$this->getTable()}()->detach($this->getKey());
        }
        throw new Exception(__('Productable must implement `detachFromUser` method'));
    }

    public function toJsonResourceForShop(): JsonResource
    {
        return ProductableGenericResource::make($this);
    }

    public function getName(): string
    {
        return $this->name ?? $this->title ?? __('No name field');
    }

    public function getDescription(): ?string
    {
        return $this->description ?? __('No description field');
    }

    public function getApiReadUrl(): ?string
    {
        return null;
    }
}
