<?php

namespace EscolaLms\Cart\Services\Contracts;

use EscolaLms\Core\Dtos\OrderDto as SortDto;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface OrderServiceContract
{
    public function searchAndPaginateOrders(SortDto $sortDto, array $search = [], ?int $per_page = 15): LengthAwarePaginator;

    public function find(int $id):Model;
}
