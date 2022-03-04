<?php

namespace EscolaLms\Cart\Services;

use EscolaLms\Cart\Events\ProductAddedToCart;
use EscolaLms\Cart\Http\Resources\CartResource;
use EscolaLms\Cart\Models\Cart;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Services\CartManager;
use EscolaLms\Cart\Services\Contracts\OrderServiceContract;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Core\Models\User;
use EscolaLms\Payments\Dtos\Contracts\PaymentMethodContract;
use EscolaLms\Payments\Enums\PaymentStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopService implements ShopServiceContract
{
    protected OrderServiceContract $orderService;
    protected ProductServiceContract $productService;

    public function __construct(OrderServiceContract $orderService, ProductServiceContract $productService)
    {
        $this->orderService = $orderService;
        $this->productService = $productService;
    }

    public function cartForUser(User $user): Cart
    {
        return Cart::where('user_id', $user->getAuthIdentifier())->latest()->firstOrCreate([
            'user_id' => $user->getAuthIdentifier(),
        ]);
    }

    public function cartManagerForCart(Cart $cart): CartManager
    {
        return new CartManager($cart);
    }

    public function purchaseCart(Cart $cart, PaymentMethodContract $paymentMethod = null): void
    {
        $order = $this->orderService->createOrderFromCart($cart);

        $paymentProcessor = $order->process();
        $paymentProcessor->purchase($paymentMethod);
        $payment = $paymentProcessor->getPayment();

        if ($payment->status->is(PaymentStatus::PAID)) {
            $this->orderService->setPaid($order);
        } elseif ($payment->status->is(PaymentStatus::CANCELLED)) {
            $this->orderService->setCancelled($order);
        }

        $this->cartManagerForCart($cart)->destroy();
    }

    public function cartAsJsonResource(Cart $cart, ?int $taxRate = null): JsonResource
    {
        return CartResource::make($cart, $taxRate);
    }

    public function removeProductFromCart(Cart $cart, Product $product): void
    {
        $cartManager = $this->cartManagerForCart($cart);

        $item = $cartManager->findProduct($product);
        if ($item) {
            $cartManager->remove($item->getKey());
        }
    }

    public function removeItemFromCart(Cart $cart, int $cartItemId): void
    {
        $cartManager = $this->cartManagerForCart($cart);
        $cartManager->remove($cartItemId);
    }

    public function addUniqueProductToCart(Cart $cart, Product $product): void
    {
        if (!$product->getBuyableByUserAttribute($cart->user)) {
            return;
        }

        $cartManager = $this->cartManagerForCart($cart);

        if (!$cartManager->hasBuyable($product)) {
            $cartManager->add($product, 1);

            event(new ProductAddedToCart($product, $cart));
        }
    }

    public function addProductToCart(Cart $cart, Product $product): void
    {
        if (!$product->getBuyableByUserAttribute($cart->user)) {
            return;
        }

        $this->cartManagerForCart($cart)->add($product, 1);
        event(new ProductAddedToCart($product, $cart));
    }

    public function updateProductQuantity(Cart $cart, Product $product, int $quantity): void
    {
        $cartManager = $this->cartManagerForCart($cart);

        $item = $cartManager->findBuyable($product);
        if ($item) {
            $cartManager->updateQuantity($item->getKey(), $quantity);
        }
    }
}
