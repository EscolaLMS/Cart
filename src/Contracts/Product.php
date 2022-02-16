<?php

namespace EscolaLms\Cart\Contracts;

use EscolaLms\Cart\Contracts\Base\Buyable;
use EscolaLms\Cart\Contracts\Base\Taxable;
use EscolaLms\Cart\Models\Cart;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Core\Models\User;
use Illuminate\Database\Eloquent\Builder;

interface Product extends Buyable, Taxable
{
    /**
     * This method will filter results returned in Products list
     */
    public function scopeBuyableByUser(Builder $query, User $user): Builder;
    public function scopeOwnedByUser(Builder $query, User $user): Builder;

    /**
     * This method will be checked when adding Product to Cart
     */
    public function getBuyableByUserAttribute(?User $user = null): bool;
    public function getOwnedByUserAttribute(?User $user = null): bool;

    /** 
     * This method will be called when product was added to Cart
     */
    public function addedToCart(Cart $cart): void;

    /** 
     * This method will be called when product was bought
     */
    public function afterBought(Order $order): void;

    /** 
     * This method is used to attach bought Product to User (Customer)
     */
    public function attachToUser(User $User): void;

    /** 
     * This method is used to remove access to Product from User (Customer)
     */
    public function detachFromUser(User $User): void;

    /** 
     * Eloquent Model functionality required for polymorphic relations and resource listing
     */
    public function getKey();
    public function getMorphClass();
    public function toArray();
}
