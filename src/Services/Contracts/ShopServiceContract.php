<?php

namespace EscolaLms\Cart\Services\Contracts;

use EscolaLms\Cart\Models\Cart;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Services\CartManager;
use EscolaLms\Core\Models\User;
use EscolaLms\Payments\Dtos\Contracts\PaymentMethodContract;
use Illuminate\Http\Resources\Json\JsonResource;

interface ShopServiceContract
{
    public function cartForUser(User $user): Cart;

    public function cartManagerForCart(Cart $cart): CartManager;

    public function cartAsJsonResource(Cart $cart, ?int $taxRate = null): JsonResource;

    public function addUniqueProductToCart(Cart $cart, Product $buyable): void;
    public function addProductToCart(Cart $cart, Product $buyable): void;
    public function updateProductQuantity(Cart $cart, Product $buyable, int $quantity): void;
    public function removeProductFromCart(Cart $cart, Product $buyable): void;
    public function removeItemFromCart(Cart $cart, int $cartItemId): void;

    public function purchaseCart(Cart $cart, PaymentMethodContract $paymentMethod = null): void;
}
