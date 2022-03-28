<?php

namespace EscolaLms\Cart\Services;

use EscolaLms\Cart\Dtos\ClientDetailsDto;
use EscolaLms\Cart\Events\ProductAddedToCart;
use EscolaLms\Cart\Events\ProductRemovedFromCart;
use EscolaLms\Cart\Http\Resources\CartResource;
use EscolaLms\Cart\Models\Cart;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Services\CartManager;
use EscolaLms\Cart\Services\Contracts\OrderServiceContract;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Core\Models\User;
use EscolaLms\Payments\Enums\PaymentStatus;
use EscolaLms\Payments\Models\Payment;
use Illuminate\Http\Resources\Json\JsonResource;
use InvalidArgumentException;

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

    public function purchaseCart(Cart $cart, ?ClientDetailsDto $clientDetailsDto = null, array $parameters = []): Payment
    {
        $cartManager = $this->cartManagerForCart($cart);

        $order = $this->orderService->createOrderFromCartManager($cartManager, $clientDetailsDto);

        $paymentProcessor = $order->process();
        $paymentProcessor->purchase($parameters);
        $payment = $paymentProcessor->getPayment();

        if ($payment->status->is(PaymentStatus::PAID)) {
            $this->orderService->setPaid($order);
        } elseif ($payment->status->is(PaymentStatus::CANCELLED)) {
            $this->orderService->setCancelled($order);
        }

        $cartManager->destroy();

        return $payment;
    }

    public function cartAsJsonResource(Cart $cart, ?int $taxRate = null): JsonResource
    {
        return CartResource::make($cart, $taxRate);
    }

    public function removeProductFromCart(Cart $cart, Product $product, int $quantity = 1): void
    {
        $cartManager = $this->cartManagerForCart($cart);

        $item = $cartManager->findProduct($product);
        if ($item) {
            $quantity = $quantity > $item->quantity ? $item->quantity : $quantity;
            $remaining = $item->quantity - $quantity;

            if ($remaining === 0) {
                $cartManager->remove($item->getKey());
            } else {
                $cartManager->updateQuantity($item->getKey(), $remaining);
            }

            event(new ProductRemovedFromCart($product, $cart, $quantity, $remaining));
        }
    }

    public function addProductToCart(Cart $cart, Product $product, int $quantity = 1): void
    {
        if (!$product->getBuyableByUserAttribute($cart->user)) {
            return;
        }

        $cartManager = $this->cartManagerForCart($cart);

        $item = $cartManager->findBuyable($product);

        if ($item) {
            $total = $item->quantity + $quantity;
            $cartManager->updateQuantity($item->getKey(), $total);
        } else {
            $total = $quantity;
            $cartManager->add($product, $quantity);
        }

        event(new ProductAddedToCart($product, $cart, $quantity, $total));
    }

    public function updateProductQuantity(Cart $cart, Product $product, int $quantity): void
    {
        if ($quantity < 0) {
            throw new InvalidArgumentException(__('Quantity can not be negative'));
        }

        $cartManager = $this->cartManagerForCart($cart);
        $item = $cartManager->findBuyable($product);
        $current = $item ? $item->quantity : 0;

        if ($current > $quantity) {
            $this->removeProductFromCart($cart, $product, $current - $quantity);
        } elseif ($quantity > $current) {
            $this->addProductToCart($cart, $product, $quantity - $current);
        }
    }
}
