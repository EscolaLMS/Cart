<?php

namespace EscolaLms\Cart\Http\Controllers;

use EscolaLms\Cart\Http\Requests\OrderSearchRequest;
use EscolaLms\Cart\Http\Requests\OrderViewRequest;
use EscolaLms\Cart\Http\Resources\OrderResource;
use EscolaLms\Cart\Http\Swagger\OrderSwagger;
use EscolaLms\Cart\Services\Contracts\OrderServiceContract;
use EscolaLms\Core\Dtos\OrderDto as SortDto;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use Illuminate\Http\JsonResponse;

class OrderApiController extends EscolaLmsBaseController implements OrderSwagger
{
    protected OrderServiceContract $orderService;

    public function __construct(OrderServiceContract $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index(OrderSearchRequest $request): JsonResponse
    {
        $sortDto = SortDto::instantiateFromRequest($request);
        $searchOrdersDto = $request->toDto();
        $paginatedResults = $this->orderService->searchAndPaginateOrders($searchOrdersDto, $sortDto);
        return $this->sendResponseForResource(OrderResource::collection($paginatedResults), __("Your orders history"));
    }

    public function read(OrderViewRequest $request): JsonResponse
    {
        return $this->sendResponseForResource(OrderResource::make($request->getOrder()), __("Order fetched"));
    }
}
