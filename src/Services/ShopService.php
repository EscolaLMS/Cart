<?php

namespace EscolaLms\Cart\Services;

use EscolaLms\Cart\Enums\OrderStatus;
use EscolaLms\Cart\Models\Contracts\CanOrder;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Treestoneit\ShoppingCart\CartManager;
use Treestoneit\ShoppingCart\Models\Cart;

class ShopService extends CartManager implements ShopServiceContract
{
    use Concerns\UniqueItems;
    use Concerns\AvoidDeleted;
    use Concerns\Discounts;
    use Concerns\Payments;

    protected CanOrder $user;

    public function __construct(Cart $cart)
    {
        parent::__construct($cart);
    }

    public static function fromUserId(Authenticatable $user): self
    {
        assert($user instanceof CanOrder);
        $shop = app(ShopServiceContract::class);
        $shop->setCart(
            self::cartFromUser($user)
        );
        $shop->setUser($user);

        assert($shop instanceof ShopService);
        return $shop;
    }

    public static function cartFromUser(Authenticatable $user): Cart
    {
        return Cart::where('user_id', $user->getAuthIdentifier())->firstOrNew([
            'user_id' => $user->getAuthIdentifier(),
        ]);
    }

    public function setCart(Cart $cart): void
    {
        $this->cart = $cart;
        $this->refreshCart();
    }

    public function setUser(CanOrder $user): void
    {
        $this->user = $user;
    }

    public function loadUserCart(Authenticatable $user): self
    {
        assert($user instanceof CanOrder);
        $loadedCart = self::fromUserId($user);
        $this->avoidDeletedItems();
        return $loadedCart;
    }

    public function attachTo(Authenticatable $user): self
    {
        assert($user instanceof CanOrder);
        $this->user = $user;
        return parent::attachTo($user);
    }

    public function getStatus(): int
    {
        if ($this->getModel() instanceof Order) {
            return $this->getModel()->status ?? OrderStatus::PROCESSING;
        }
        return OrderStatus::PROCESSING;
    }

    /**
     * @return CanOrder
     */
    public function getUser(): CanOrder
    {
        return $this->user;
    }

    public function getCartData(): array
    {
        return [
            'total' => $this->moneyFormat($this->total()),
            'subtotal' => $this->moneyFormat($this->subtotal()),
            'tax' => $this->moneyFormat($this->tax()),
            'items' => $this->content()->pluck('buyable')->toArray(),
            'discount' => $this->getDiscount()
        ];
    }

    public function getResource(): JsonResponse
    {
        return new JsonResponse($this->getCartData());
    }

    public function removeItemFromCart(string $item): void
    {
        $item = $this->content()->firstWhere('buyable_id', $item);

        if ($item) {
            $this->remove($item->getKey());
        }
    }

    private function moneyFormat(int $value): string
    {
        $quotient = intdiv($value, 100);
        $remainder = $value - ($quotient * 100);

        $result = (string) $quotient . '.';
        if ($remainder < 10) {
            return $result . '0' . (string) $remainder;
        }
        return $result . (string) $remainder;
    }
}
