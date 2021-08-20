<?php

namespace EscolaLms\Cart\Services;

use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\QueryBuilders\OrderQueryBuilder;
use EscolaLms\Cart\Services\Contracts\OrderServiceContract;
use EscolaLms\Core\Dtos\OrderDto as SortDto;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class OrderService implements OrderServiceContract
{
    public function searchAndPaginateOrders(SortDto $sortDto, array $search = [], ?int $per_page  = 15): LengthAwarePaginator
    {
        /** @var OrderQueryBuilder $query */
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
        if (Arr::get($search, 'course_id')) {
            $query = $query->whereHasCourseId($search['course_id']);
        }
        if (Arr::get($search, 'author_id')) {
            $query = $query->whereHasCourseWithAuthorId($search['author_id']);
        }
        if (!is_null($sortDto->getOrder())) {
            /** @var OrderQueryBuilder $query */
            $query = $query->orderBy($sortDto->getOrderBy(), $sortDto->getOrder());
        }

        return $query->paginate($per_page ?? 15);
    }
}
