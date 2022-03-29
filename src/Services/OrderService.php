<?php

namespace EscolaLms\Cart\Services;

use EscolaLms\Cart\Dtos\ClientDetailsDto;
use EscolaLms\Cart\Dtos\OrdersSearchDto;
use EscolaLms\Cart\Enums\OrderStatus;
use EscolaLms\Cart\Events\OrderCancelled;
use EscolaLms\Cart\Events\OrderCreated;
use EscolaLms\Cart\Events\OrderPaid;
use EscolaLms\Cart\Events\ProductBought;
use EscolaLms\Cart\Models\Cart;
use EscolaLms\Cart\Models\CartItem;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Models\OrderItem;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\User;
use EscolaLms\Cart\QueryBuilders\OrderModelQueryBuilder;
use EscolaLms\Cart\Services\Contracts\OrderServiceContract;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Payments\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class OrderService implements OrderServiceContract
{
    protected ProductServiceContract $productService;

    public function __construct(ProductServiceContract $productService)
    {
        $this->productService = $productService;
    }

    public function searchAndPaginateOrders(OrdersSearchDto $searchDto, ?OrderDto $sortDto): LengthAwarePaginator
    {
        /** @var OrderModelQueryBuilder $query */
        $query = Order::query();

        if (!is_null($searchDto->getDateFrom())) {
            $query->where('created_at', '>=', $searchDto->getDateFrom());
        }

        if (!is_null($searchDto->getDateTo())) {
            $query->where('created_at', '<=', $searchDto->getDateTo());
        }

        if (!is_null($searchDto->getUserId())) {
            $query = $query->where('user_id', $searchDto->getUserId());
        }

        if (!is_null($searchDto->getProductId())) {
            $query = $query->whereHasBuyable(Product::class, $searchDto->getProductId());
        }


        if (!is_null($searchDto->getProductableType())) {
            $class = $searchDto->getProductableType();
            /** @var Model $model */
            $model = new $class();
            if (!is_null($searchDto->getProductableId())) {
                $query = $query->whereHasProductableClassAndId($model->getMorphClass(), $searchDto->getProductableId());
            } else {
                $query = $query->whereHasProductableClass($model->getMorphClass());
            }
        }

        if (!is_null($sortDto) && !is_null($sortDto->getOrder())) {
            $query = $query->orderBy($sortDto->getOrderBy(), $sortDto->getOrder());
        }

        return $query->paginate($searchDto->getPerPage() ?? 15);
    }

    public function find($id): Model
    {
        return Order::findOrFail($id);
    }

    public function createOrderFromCart(Cart $cart, ?ClientDetailsDto $clientDetailsDto = null): Order
    {
        return $this->createOrderFromCartManager(new CartManager($cart), $clientDetailsDto);
    }

    public function createOrderFromCartManager(CartManager $cartManager, ?ClientDetailsDto $clientDetailsDto = null): Order
    {
        $optionalClientDetailsDto = optional($clientDetailsDto);

        $cart = $cartManager->getModel();

        /** @var User $user */
        $user = User::find($cart->user_id);

        $user->orders()->where('status', OrderStatus::PROCESSING)->update(['status' => OrderStatus::CANCELLED]);

        $order = new Order($cart->getAttributes());
        $order->total = $cartManager->totalWithTax();
        $order->subtotal = $cartManager->total();
        $order->tax = $cartManager->taxInt();
        $order->status = OrderStatus::PROCESSING;
        $order->client_name = $optionalClientDetailsDto->getName() ?? $order->user->name;
        $order->client_email = $optionalClientDetailsDto->getEmail() ?? $order->user->email;
        $order->client_street = $optionalClientDetailsDto->getStreet();
        $order->client_street_number = $optionalClientDetailsDto->getStreetNumber();
        $order->client_postal = $optionalClientDetailsDto->getPostal();
        $order->client_city = $optionalClientDetailsDto->getCity();
        $order->client_country = $optionalClientDetailsDto->getCountry();
        $order->client_company = $optionalClientDetailsDto->getCompany();
        $order->client_taxid = $optionalClientDetailsDto->getTaxid();
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
            'name'         => $item->buyable->name ?? $item->buyable->title ?? null,
            'price'        => $item->price,
            'quantity'     => $item->quantity,
            'tax_rate'     => $item->tax_rate,
            'extra_fees'   => $item->extra_fees,
            'order_id'     => $order->getKey(),
        ]);
    }

    public function setPaid(Order $order): void
    {
        if ($order->status === OrderStatus::PAID) {
            return;
        }
        $this->setOrderStatus($order, OrderStatus::PAID);
        event(new OrderPaid($order));
        $this->processOrderItems($order);
    }

    public function setCancelled(Order $order): void
    {
        if ($order->status === OrderStatus::CANCELLED) {
            return;
        }
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
        Log::debug(__('Processing order items'), [
            'order' => $order->getKey(),
        ]);
        foreach ($order->items as $orderItem) {
            assert($orderItem instanceof OrderItem);

            $buyable = $orderItem->buyable;

            assert($buyable instanceof Product);

            event(new ProductBought($buyable, $order));
            $this->productService->attachProductToUser($buyable, $order->user, $orderItem->quantity ?? 1);
        }
    }
}
