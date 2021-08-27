<?php

namespace EscolaLms\Cart\Http\Controllers\Admin;

use EscolaLms\Cart\Enums\CartPermissionsEnum;
use EscolaLms\Cart\Http\Requests\OrderSearchRequest;
use EscolaLms\Cart\Http\Requests\OrderViewRequest;
use EscolaLms\Cart\Http\Resources\OrderResource;
use EscolaLms\Cart\Http\Swagger\Admin\OrderAdminSwagger;
use EscolaLms\Cart\Services\Contracts\OrderServiceContract;
use EscolaLms\Core\Dtos\OrderDto as SortDto;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class OrderAdminApiController extends EscolaLmsBaseController implements OrderAdminSwagger
{
    private OrderServiceContract $orderService;

    public function __construct(OrderServiceContract $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(OrderSearchRequest $request): JsonResponse
    {
        $sortDto = SortDto::instantiateFromRequest($request);
        $search = Arr::except($request->validated(), ['per_page', 'page', 'order_by', 'order']);
        if ($request->user()->can(CartPermissionsEnum::LIST_AUTHORED_COURSE_ORDERS) && $request->user()->cannot(CartPermissionsEnum::LIST_ALL_ORDERS)) {
            $search['author_id'] = $request->user()->getKey();
        }
        $paginatedResults = $this->orderService->searchAndPaginateOrders($sortDto, $search, $request->input('per_page'));
        return $this->sendResponseForResource(OrderResource::collection($paginatedResults), __("Order search results"));
    }

    public function show(int $order, OrderSearchRequest $request): JsonResponse
    {
        $order = $this->orderService->find($id);
        return $this->sendResponseForResource(OrderResource::make($order), __("Order fetched"));
    }
}
