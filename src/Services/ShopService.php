<?php

namespace EscolaLms\Cart\Services;

use Carbon\Carbon;
use EscolaLms\Cart\Dtos\ClientDetailsDto;
use EscolaLms\Cart\Enums\OrderStatus;
use EscolaLms\Cart\Enums\ProductType;
use EscolaLms\Cart\Enums\QuantityOperationEnum;
use EscolaLms\Cart\Events\ProductAddedToCart;
use EscolaLms\Cart\Events\ProductRemovedFromCart;
use EscolaLms\Cart\Http\Resources\CartResource;
use EscolaLms\Cart\Models\Cart;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Services\Contracts\OrderServiceContract;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Core\Models\User;
use EscolaLms\Payments\Enums\PaymentStatus;
use EscolaLms\Payments\Models\Payment;
use Illuminate\Database\Eloquent\Collection;
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

    public function purchaseCart(Cart $cart, ?ClientDetailsDto $clientDetails = null, array $parameters = []): Payment
    {
        $cartManager = $this->cartManagerForCart($cart);

        $order = $this->orderService->createOrderFromCartManager($cartManager, $clientDetails);

        $paymentProcessor = $order->process();
        $paymentProcessor->purchase($parameters);
        $payment = $paymentProcessor->getPayment();

        if ($payment->status->is(PaymentStatus::CANCELLED)) {
            $this->orderService->setCancelled($order);
        }

        $cartManager->destroy();

        return $payment;
    }

    public function purchaseProduct(Product $product, User $user, ?ClientDetailsDto $clientDetails = null, array $parameters = []): Payment
    {
        $order = $this->orderService->createOrderFromProduct($product, $user->getKey(), $clientDetails);

        $paymentProcessor = $order->process();

        $parameters['type'] = $product->type;
        if (ProductType::isSubscriptionType($product->type)) {
            $parameters += $product->getSubscriptionParameters();
            $parameters += $order->status === OrderStatus::TRIAL_PROCESSING ? $product->getTrailParameters() : [];
        }

        $paymentProcessor->purchase($parameters);
        $payment = $paymentProcessor->getPayment();

        if ($payment->status->is(PaymentStatus::CANCELLED)) {
            $this->orderService->setCancelled($order);
        }

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

    public function updateProductQuantity(Cart $cart, Product $product, int $quantity): array
    {
        if ($quantity < 0) {
            throw new InvalidArgumentException(__('Quantity can not be negative'));
        }

        $cartManager = $this->cartManagerForCart($cart);
        $item = $cartManager->findBuyable($product);
        $current = $item ? $item->quantity : 0;

        if ($current > $quantity) {
            $difference = $current - $quantity;
            $operation = QuantityOperationEnum::DECREMENT;
            $this->removeProductFromCart($cart, $product, $difference);
        } elseif ($quantity > $current) {
            $difference = $quantity - $current;
            $operation = QuantityOperationEnum::INCREMENT;
            $this->addProductToCart($cart, $product, $difference);
        }
        return [
            'operation' => $operation ?? QuantityOperationEnum::UNCHANGED,
            'difference' => $difference ?? 0,
            'quantity_owned' => $product->getOwnedByUserQuantityAttribute($cart->user),
            'quantity_in_cart' => $quantity,
            'limit' => $product->limit_per_user,
            // quantity + 1 to check if it is possible to add another one to cart
            'buyable' => $product->getBuyableByUserAttribute($cart->user, $quantity + 1),
        ];
    }

    public function getAbandonedCarts(Carbon $from, Carbon $to): Collection
    {
        return Cart::query()
            ->where([
                ['updated_at', '>=', $from],
                ['updated_at', '<=', $to],
            ])
            ->whereRaw("(SELECT count(cart_items.id) FROM cart_items WHERE cart_items.cart_id = carts.id AND cart_items.updated_at > '{$to}' GROUP BY cart_items.cart_id) is null
                and (SELECT count(cart_items.id) FROM cart_items WHERE cart_items.cart_id = carts.id AND cart_items.updated_at >= '{$from}' and cart_items.updated_at <= '{$to}' GROUP BY cart_items.cart_id) > 0")
            ->get();
    }

    public function addMissingProductsToCart(Cart $cart, array $products): void
    {
        $cartManager = $this->cartManagerForCart($cart);
        foreach ($products as $product) {
            /** @var Product $productModel */
            $productModel = Product::find($product);
            if (!$cartManager->hasProduct($productModel) && $this->productService->productIsBuyableByUser($productModel, $cart->user)) {
                $cartManager->add($productModel, 1);
            }
        }
    }
}
