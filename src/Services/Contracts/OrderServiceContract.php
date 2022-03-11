<?php

namespace EscolaLms\Cart\Services\Contracts;

use EscolaLms\Cart\Dtos\ClientDetailsDto;
use EscolaLms\Cart\Dtos\OrdersSearchDto;
use EscolaLms\Cart\Models\Cart;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Services\CartManager;
use EscolaLms\Core\Dtos\OrderDto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderServiceContract
{
    public function searchAndPaginateOrders(OrdersSearchDto $searchDto, ?OrderDto $sortDto): LengthAwarePaginator;

    public function find(int $id): Model;

    public function createOrderFromCart(Cart $cart, ?ClientDetailsDto $clientDetailsDto = null): Order;
    public function createOrderFromCartManager(CartManager $cart, ?ClientDetailsDto $clientDetailsDto = null): Order;

    public function setPaid(Order $order): void;
    public function setCancelled(Order $order): void;
    public function setOrderStatus(Order $order, int $status): void;

    public function processOrderItems(Order $order): void;
}
