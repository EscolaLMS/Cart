<?php

namespace EscolaLms\Cart\Http\Controllers;

use EscolaLms\Cart\Http\Requests\ProductReadRequest;
use EscolaLms\Cart\Http\Requests\ProductRecursiveCancelRequest;
use EscolaLms\Cart\Http\Requests\ProductSearchMyRequest;
use EscolaLms\Cart\Http\Requests\ProductSearchRequest;
use EscolaLms\Cart\Http\Resources\MyProductResource;
use EscolaLms\Cart\Http\Resources\ProductResource;
use EscolaLms\Cart\Http\Swagger\ProductSwagger;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaLms\Core\Models\User;
use Illuminate\Http\JsonResponse;
use EscolaLms\Core\Dtos\OrderDto as SortDto;

class ProductApiController extends EscolaLmsBaseController implements ProductSwagger
{
    protected ProductServiceContract $productService;
    protected ShopServiceContract $shopService;

    public function __construct(ProductServiceContract $productService, ShopServiceContract $shopService)
    {
        $this->productService = $productService;
        $this->shopService = $shopService;
    }

    public function index(ProductSearchRequest $request): JsonResponse
    {
        $sortDto = SortDto::instantiateFromRequest($request);
        $productsSearchDto = $request->toDto();
        $products = $this->productService->searchAndPaginateProducts($productsSearchDto, $sortDto);
        return $this->sendResponseForResource(ProductResource::collection($products));
    }

    public function read(ProductReadRequest $request): JsonResponse
    {
        return $this->sendResponseForResource(ProductResource::make($request->getProduct()), __('Product fetched'));
    }

    public function indexMy(ProductSearchMyRequest $request): JsonResponse
    {
        $results = $this->productService->searchMy($request->getCriteria(), $request->getPage(), $request->getOrder());

        return $this->sendResponseForResource(MyProductResource::collection($results));
    }

    public function cancel(ProductRecursiveCancelRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $this->productService->cancelActiveRecursiveProduct($request->getProduct(), $user);

        return $this->sendSuccess(__('Subscription cancelled successfully'));
    }
}
