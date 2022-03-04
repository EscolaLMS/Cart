<?php

namespace EscolaLms\Cart\Http\Controllers\Admin;

use EscolaLms\Cart\Http\Requests\Admin\ProductAttachRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductCreateRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductDeleteRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductDetachRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductReadRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductSearchRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductUpdateRequest;
use EscolaLms\Cart\Http\Resources\ProductResource;
use EscolaLms\Cart\Http\Swagger\Admin\ProductAdminSwagger;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use Illuminate\Http\JsonResponse;

class ProductAdminApiController extends EscolaLmsBaseController implements ProductAdminSwagger
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
        $products = $this->productService->searchAndPaginateProducts($request->toDto(), OrderDto::instantiateFromRequest($request));
        return $this->sendResponseForResource(ProductResource::collection($products));
    }

    public function create(ProductCreateRequest $request): JsonResponse
    {
        $product = $this->productService->create($request->validated());
        return $this->sendResponseForResource(ProductResource::make($product), __('Product created'));
    }

    public function read(ProductReadRequest $request): JsonResponse
    {
        return $this->sendResponseForResource(ProductResource::make($request->getProduct()), __('Product fetched'));
    }

    public function update(ProductUpdateRequest $request): JsonResponse
    {
        $product = $this->productService->update($request->getProduct(), $request->validated());
        return $this->sendResponseForResource(ProductResource::make($product), __('Product updated'));
    }

    public function delete(ProductDeleteRequest $request): JsonResponse
    {
        $request->getProduct()->delete();
        return $this->sendResponse([], __('Product deleted'));
    }

    public function attach(ProductAttachRequest $request): JsonResponse
    {
        $this->productService->attachProductToUser($request->getProduct(), $request->getUser());
        return $this->sendSuccess(__('Product attached to user'));
    }

    public function detach(ProductDetachRequest $request): JsonResponse
    {
        $this->productService->detachProductFromUser($request->getProduct(), $request->getUser());
        return $this->sendSuccess(__('Product detached from user'));
    }
}
