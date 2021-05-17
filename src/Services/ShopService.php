<?php

namespace EscolaSoft\Cart\Services;

use EscolaSoft\Cart\Enums\OrderStatus;
use EscolaSoft\Cart\Services\Contracts\ShopServiceContract;
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

    protected Authenticatable $user;

    public function __construct(Cart $cart)
    {
        parent::__construct($cart);
    }

    public static function fromUserId(Authenticatable $user): self
    {
        $shop = app(ShopServiceContract::class);
        $shop->setCart(
            self::cartFromUser($user)
        );
        $shop->setUser($user);

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

    public function setUser(Authenticatable $user): void
    {
        $this->user = $user;
    }

    public function loadUserCart(Authenticatable $user): self
    {
        $loadedCart = self::fromUserId($user);
        $this->avoidDeletedItems();
        return $loadedCart;
    }

    public function attachTo(Authenticatable $user): self
    {
        $this->user = $user;
        return parent::attachTo($user);
    }

    public function getStatus(): int
    {
        return $this->getModel()->status ?? OrderStatus::PROCESSING;
    }

    /**
     * @return Authenticatable
     */
    public function getUser(): Authenticatable
    {
        return $this->user;
    }

    public function getResource(): JsonResponse
    {
        return new JsonResponse([
            'total' => $this->moneyFormat($this->total()),
            'subtotal' => $this->moneyFormat($this->subtotal()),
            'tax' => $this->moneyFormat($this->tax()),
            'items' => $this->content()->pluck('buyable')->toArray(),
            'discount' => $this->getDiscount()
        ]);
    }

    public function removeItemFromCart(string $item): void
    {
        $item = $this->content()->firstWhere('buyable_id', $item);

        if ($item) {
            $this->remove($item->getKey());
        }
    }

    private function moneyFormat(float $value): string
    {
        return number_format($value, 2, '.', '');
    }
}