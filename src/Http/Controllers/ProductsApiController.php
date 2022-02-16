<?php

namespace EscolaLms\Cart\Http\Controllers;

use EscolaLms\Cart\Http\Requests\ProductListRequest;
use EscolaLms\Cart\Http\Resources\ProductResource;
use EscolaLms\Cart\Http\Swagger\ProductSwagger;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use Illuminate\Http\JsonResponse;

class ProductsApiController extends EscolaLmsBaseController implements ProductSwagger
{
    protected ShopServiceContract $shopService;

    public function __construct(ShopServiceContract $shopService)
    {
        $this->shopService = $shopService;
    }

    public function index(ProductListRequest $request): JsonResponse
    {
        $products = $this->shopService->listProductsBuyableByUser($request->user(), $request->getProductType());
        return $this->sendResponseForResource(ProductResource::collection($products));
    }
}
