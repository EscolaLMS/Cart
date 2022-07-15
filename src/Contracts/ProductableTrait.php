<?php

namespace EscolaLms\Cart\Contracts;

use EscolaLms\Cart\Http\Resources\ProductableGenericResource;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\ProductProductable;
use EscolaLms\Cart\Support\ModelHelper;
use EscolaLms\Core\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

/**
 * @see \EscolaLms\Cart\Contracts\Productable
 */
trait ProductableTrait
{
    /**
     * Default implementation considers Productable buyable only if it is not already owned by User, effectively limiting User to owning only 1 item of a given Productable
     * This must be modified for Productables that can be bought more than once
     */
    public function scopeBuyableByUser(Builder $query, User $user): Builder
    {
        try {
            return $this->scopeNotOwnedByUser($query, $user);
        } catch (Exception $ex) {
        }
        throw new Exception(__('Productable must implement `scopeBuyableByUser` method'));
    }

    /**
     * Default implementation considers Productable not buyable if it is already owned by User, effectively limiting User to owning only 1 item of a given Productable
     * This must be modified for Productables that can be bought more than once
     */
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

    /**
     * Default implementation considers Productable buyable only if it is not already owned by User, effectively limiting User to owning only 1 item of a given Productable
     * This must be modified for Productables that can be bought more than once
     */
    public function getBuyableByUserAttribute(?User $user = null): bool
    {
        return $this->scopeBuyableByUser($this::query()->where($this->getTable() . '.id', $this->getKey()), $user ?? Auth::user())->exists();
    }

    /**
     * Default implementation considers Productable not buyable if it is already owned by User, effectively limiting User to owning only 1 item of a given Productable
     * This must be modified for Productables that can be bought more than once
     */
    public function getOwnedByUserAttribute(?User $user = null): bool
    {
        return $this->scopeOwnedByUser($this::query()->where($this->getTable() . '.id', $this->getKey()), $user ?? Auth::user())->exists();
    }

    public function attachToUser(User $user, int $quantity = 1, ?Product $product = null): void
    {
        if (ModelHelper::hasRelation($this, 'users') && $this->users() instanceof BelongsToMany) {
            $this->users()->syncWithoutDetaching($user->getKey());
        } elseif (ModelHelper::hasRelation($user, $this->getTable()) && $user->{$this->getTable()}() instanceof BelongsToMany) {
            $user->{$this->getTable()}()->syncWithoutDetaching($this->getKey());
        } else {
            throw new Exception(__('Productable must implement `attachToUser` method'));
        }
    }

    public function detachFromUser(User $user, int $quantity = 1, ?Product $product = null): void
    {
        if (ModelHelper::hasRelation($this, 'users') && $this->users() instanceof BelongsToMany) {
            $this->users()->detach($user->getKey());
        } elseif (ModelHelper::hasRelation($user, $this->getTable()) && $user->{$this->getTable()}() instanceof BelongsToMany) {
            $user->{$this->getTable()}()->detach($this->getKey());
        } else {
            throw new Exception(__('Productable must implement `detachFromUser` method'));
        }
    }

    public function toJsonResourceForShop(?ProductProductable $productProductable = null): JsonResource
    {
        return ProductableGenericResource::make($this, $productProductable);
    }

    public function getName(): string
    {
        if (empty($this->getNameColumn())) {
            return __('Unknown');
        }
        return $this->{$this->getNameColumn()};
    }

    public function getNameColumn(): ?string
    {
        if (Schema::hasColumn($this->getTable(), 'name')) {
            return 'name';
        }
        if (Schema::hasColumn($this->getTable(), 'title')) {
            return 'title';
        }
        return null;
    }

    public function getDescription(): ?string
    {
        return $this->description ?? __('No description field');
    }

    public function getApiReadUrl(): ?string
    {
        return null;
    }

    public function getThumbnail(): ?string
    {
        $attributes = $this->attributesToArray();
        foreach (['thumbnail', 'poster_url', 'image_path'] as $possible_key) {
            if (array_key_exists($possible_key, $attributes)) {
                return $this->{$possible_key};
            }
        }
        return null;
    }

    public function getProductableAuthors(): Collection
    {
        if (ModelHelper::hasRelation($this, 'authors')) {
            return $this->authors;
        }
        return new Collection();
    }

    public function getProductableDuration(): int
    {
        return (int) ($this->duration ?? 0);
    }
}
