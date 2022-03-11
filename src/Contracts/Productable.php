<?php

namespace EscolaLms\Cart\Contracts;

use EscolaLms\Core\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\JsonResource;

interface Productable
{
    /**
     * This method will filter Productables (Models of buyable things represented by Product) checking if they are buyable by user
     */
    public function scopeBuyableByUser(Builder $query, User $user): Builder;

    /**
     * This method will filter Productables (Models of buyable things represented by Product) checking if they are not buyable by user (this defines if Product is not buyable as a whole)
     */
    public function scopeNotBuyableByUser(Builder $query, User $user): Builder;

    /**
     * This method will filter Productables (Models of buyable things represented by Product) checking if they are owned by user
     */
    public function scopeOwnedByUser(Builder $query, User $user): Builder;

    /**
     * This method will filter Productables (Models of buyable things represented by Product) checking if they are not owned by user
     */
    public function scopeNotOwnedByUser(Builder $query, User $user): Builder;

    /**
     * This method will be checked when adding Product to Cart
     */
    public function getBuyableByUserAttribute(?User $user = null): bool;

    /**
     * This method will be checked when adding Product to Cart
     */
    public function getOwnedByUserAttribute(?User $user = null): bool;

    /**
     * Method for attaching Productable to User (Used when Admin gifts Product/Productable to User and after buying Product)
     */
    public function attachToUser(User $user): void;

    /**
     * Method for detaching Productable from User (Used when Admin manually removes Product/Productable from User)
     */
    public function detachFromUser(User $user): void;

    /**
     * Get JsonResource representing this Productable (used in listing Products)
     */
    public function toJsonResourceForShop(): JsonResource;

    /**
     * Get productable name
     */
    public function getName(): string;

    /**
     * Get productable name column
     */
    public function getNameColumn(): ?string;

    /**
     * Get productable description
     */
    public function getDescription(): ?string;

    /**
     * Get productable description
     */
    public function getApiReadUrl(): ?string;

    /** 
     * Default Eloquent Model functionality
     */
    public function getTable();

    /** 
     * Default Eloquent Model functionality
     */
    public function getKey();

    /** 
     * Default Eloquent Model functionality (required for polymorphic relations and resource listing)
     */
    public function getMorphClass();
}
