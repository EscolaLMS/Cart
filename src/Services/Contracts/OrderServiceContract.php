<?php

namespace EscolaLms\Cart\Services\Contracts;

use EscolaLms\Cart\Models\Cart;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Core\Dtos\OrderDto as SortDto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderServiceContract
{
    public function searchAndPaginateOrders(SortDto $sortDto, array $search = [], ?int $per_page = 15): LengthAwarePaginator;
    public function find(int $id): Model;
    public function createOrderFromCart(Cart $cart): Order;
    public function setPaid(Order $order): void;
    public function setCancelled(Order $order): void;
    public function setOrderStatus(Order $order, int $status): void;
    public function processOrderItems(Order $order): void;
}
