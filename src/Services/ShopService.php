<?php

namespace EscolaLms\Cart\Services;

use EscolaLms\Cart\Contracts\Product;
use EscolaLms\Cart\Http\Resources\CartResource;
use EscolaLms\Cart\Models\Cart;
use EscolaLms\Cart\Services\CartManager;
use EscolaLms\Cart\Services\Contracts\OrderServiceContract;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Core\Models\User;
use EscolaLms\Payments\Dtos\Contracts\PaymentMethodContract;
use EscolaLms\Payments\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ShopService implements ShopServiceContract
{
    protected OrderServiceContract $orderService;

    protected array $products = [];
    protected array $productsMorphs = [];

    public function __construct(OrderServiceContract $orderService)
    {
        $this->orderService = $orderService;
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

    public function removeProductFromCart(Cart $cart, Product $buyable): void
    {
        assert($buyable instanceof Model);

        $cartManager = $this->cartManagerForCart($cart);

        $item = $cartManager->findBuyable($buyable);
        if ($item) {
            $cartManager->remove($item->getKey());
        }
    }

    public function removeItemFromCart(Cart $cart, int $cartItemId): void
    {
        $cartManager = $this->cartManagerForCart($cart);
        $cartManager->remove($cartItemId);
    }

    public function addUniqueProductToCart(Cart $cart, Product $buyable): void
    {
        assert($buyable instanceof Model);

        if (!$buyable->buyableByUser($cart->user)) {
            return;
        }

        $cartManager = $this->cartManagerForCart($cart);
        if (!$cartManager->hasBuyable($buyable)) {
            $cartManager->add($buyable, 1);
        }
    }

    public function addProductToCart(Cart $cart, Product $buyable): void
    {
        assert($buyable instanceof Model);

        if (!$buyable->buyableByUser($cart->user)) {
            return;
        }

        $this->cartManagerForCart($cart)->add($buyable, 1);
    }

    public function updateProductQuantity(Cart $cart, Product $buyable, int $quantity): void
    {
        assert($buyable instanceof Model);

        $cartManager = $this->cartManagerForCart($cart);

        $item = $cartManager->findBuyable($buyable);
        if ($item) {
            $cartManager->updateQuantity($item->getKey(), $quantity);
        }
    }

    public function registerProduct(string $productClass): void
    {
        if (!is_a($productClass, Product::class, true)) {
            throw new InvalidArgumentException(__('Class must implement Product interface'));
        }
        if (!in_array($productClass, $this->products)) {
            $this->products[] = $productClass;
            $model = new $productClass();
            assert($model instanceof Model);
            $this->productsMorphs[$model->getMorphClass()] = $productClass;
        }
    }

    public function registeredProduct(string $productClass): bool
    {
        if (in_array($productClass, $this->products)) {
            return true;
        }
        $model = new $productClass();
        assert($model instanceof Model);
        return array_key_exists($model->getMorphClass(), $this->productsMorphs);
    }

    public function registeredProducts(): array
    {
        return $this->products;
    }

    public function canonicalProductClass(string $productClass): ?string
    {
        if (in_array($productClass, $this->products)) {
            return $productClass;
        }
        $model = new $productClass();
        assert($model instanceof Model);
        if (array_key_exists($model->getMorphClass(), $this->productsMorphs)) {
            return $this->productMorphs[$model->getMorphClass()];
        }
        throw new InvalidArgumentException(__('Unknown Product Class'));
    }

    public function findProduct(string $productClass, $productId): ?Product
    {
        return $this->canonicalProductClass($productClass)::find($productId);
    }

    public function listProductsBuyableByUser(User $user, ?string $productClass = null): Collection
    {
        if (!is_null($productClass)) {
            $canonicalProductClass = $this->canonicalProductClass($productClass);
            if ($canonicalProductClass) {
                return Collection::make($canonicalProductClass::buyableByUser($user)->paginate()->items());
            }
            throw new InvalidArgumentException(__('Unknown Product Class'));
        }

        $collection = new Collection();
        foreach ($this->products as $productClass) {
            $collection->push(...$productClass::buyableByUser($user)->paginate()->items());
        }
        return $collection;
    }
}
