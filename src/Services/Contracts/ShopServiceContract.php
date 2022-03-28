<?php

namespace EscolaLms\Cart\Services\Contracts;

use Carbon\Carbon;
use EscolaLms\Cart\Dtos\ClientDetailsDto;
use EscolaLms\Cart\Models\Cart;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Services\CartManager;
use EscolaLms\Core\Models\User;
use EscolaLms\Payments\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\JsonResource;

interface ShopServiceContract
{
    public function cartForUser(User $user): Cart;

    public function cartManagerForCart(Cart $cart): CartManager;

    public function cartAsJsonResource(Cart $cart, ?int $taxRate = null): JsonResource;

    public function addProductToCart(Cart $cart, Product $buyable, int $quantity = 1): void;
    public function removeProductFromCart(Cart $cart, Product $buyable, int $quantity = 1): void;
    public function updateProductQuantity(Cart $cart, Product $buyable, int $quantity): void;

    public function purchaseCart(Cart $cart, ?ClientDetailsDto $clientDeails = null, array $parameters = []): Payment;

    public function getAbandonedCarts(Carbon $from, Carbon $to): Collection;
}
