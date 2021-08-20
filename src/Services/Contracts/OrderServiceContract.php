<?php

namespace EscolaLms\Cart\Services\Contracts;

use EscolaLms\Core\Dtos\OrderDto as SortDto;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderServiceContract
{
    public function searchAndPaginateOrders(SortDto $sortDto, array $search = [], ?int $per_page = 15): LengthAwarePaginator;
}
