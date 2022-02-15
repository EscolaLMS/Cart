<?php

namespace EscolaLms\Cart\Services;

use EscolaLms\Cart\Contracts\Product;
use EscolaLms\Cart\Enums\OrderStatus;
use EscolaLms\Cart\Events\OrderCancelled;
use EscolaLms\Cart\Events\OrderCreated;
use EscolaLms\Cart\Events\OrderPaid;
use EscolaLms\Cart\Events\ProductBought;
use EscolaLms\Cart\Models\Cart;
use EscolaLms\Cart\Models\CartItem;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Models\OrderItem;
use EscolaLms\Cart\Models\User;
use EscolaLms\Cart\QueryBuilders\BuyableQueryBuilder;
use EscolaLms\Cart\Services\Contracts\OrderServiceContract;
use EscolaLms\Core\Dtos\OrderDto as SortDto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class OrderService implements OrderServiceContract
{
    public function searchAndPaginateOrders(SortDto $sortDto, array $search = [], ?int $per_page  = 15): LengthAwarePaginator
    {
        /** @var BuyableQueryBuilder $query */
        $query = Order::query();

        if (Arr::get($search, 'date_from')) {
            $query->where('created_at', '>=', Carbon::parse($search['date_from']));
        }
        if (Arr::get($search, 'date_to')) {
            $query->where('created_at', '<=', Carbon::parse($search['date_to']));
        }
        if (Arr::get($search, 'user_id')) {
            $query = $query->where('user_id', $search['user_id']);
        }
        if (Arr::get($search, 'product_id')) {
            $query = $query->whereHasBuyableId($search['product_id']);
        }
        if (Arr::get($search, 'product_type')) {
            $query = $query->whereHasBuyableType($search['product_type']);
        }
        if (!is_null($sortDto->getOrder())) {
            $query = $query->orderBy($sortDto->getOrderBy(), $sortDto->getOrder());
        }

        return $query->paginate($per_page ?? 15);
    }

    public function find($id): Model
    {
        return Order::findOrFail($id);
    }

    public function createOrderFromCart(Cart $cart): Order
    {
        /** @var User $user */
        $user = User::find($cart->user_id);

        $user->orders()->where('status', OrderStatus::PROCESSING)->update(['status' => OrderStatus::CANCELLED]);

        $cartManager = new CartManager($cart);

        $order = new Order($cart->getAttributes());
        $order->total = $cartManager->totalWithTax();
        $order->subtotal = $cartManager->total();
        $order->tax = $cartManager->taxInt();
        $order->status = OrderStatus::PROCESSING;
        $order->save();

        foreach ($cart->items as $item) {
            $this->storeCartItemAsOrderItem($order, $item);
        }

        event(new OrderCreated($order));

        return $order;
    }

    public function storeCartItemAsOrderItem(Order $order, CartItem $item): OrderItem
    {
        return OrderItem::create([
            'buyable_type' => $item->buyable_type,
            'buyable_id'   => $item->buyable_id,
            'price'        => $item->price,
            'quantity'     => $item->quantity,
            'tax_rate'     => $item->tax_rate,
            'extra_fees'   => $item->extra_fees,
            'order_id'     => $order->getKey(),
        ]);
    }

    public function setPaid(Order $order): void
    {
        $this->setOrderStatus($order, OrderStatus::PAID);
        event(new OrderPaid($order));
        $this->processOrderItems($order);
    }

    public function setCancelled(Order $order): void
    {
        $this->setOrderStatus($order, OrderStatus::CANCELLED);
        event(new OrderCancelled($order));
    }

    public function setOrderStatus(Order $order, int $status): void
    {
        if (!in_array($status, OrderStatus::getValues())) {
            throw new InvalidArgumentException();
        }
        $order->update([
            'status' => $status
        ]);
    }

    public function processOrderItems(Order $order): void
    {
        foreach ($order->items as $orderItem) {
            assert($orderItem instanceof OrderItem);

            $buyable = $orderItem->buyable;

            assert($buyable instanceof Product);

            $buyable->afterBought($order);

            event(new ProductBought($buyable, $order->user));
        }
    }
}
