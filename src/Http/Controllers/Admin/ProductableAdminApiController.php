<?php

namespace EscolaLms\Cart\Http\Controllers\Admin;

use EscolaLms\Cart\Http\Requests\Admin\ProductableAttachRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductableDetachRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductableListRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductableProductRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductableRegisteredListRequest;
use EscolaLms\Cart\Http\Resources\ProductResource;
use EscolaLms\Cart\Http\Swagger\Admin\ProductableAdminSwagger;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use Illuminate\Http\JsonResponse;

class ProductableAdminApiController extends EscolaLmsBaseController implements ProductableAdminSwagger
{
    protected ProductServiceContract $productService;
    protected ShopServiceContract $shopService;

    public function __construct(ProductServiceContract $productService, ShopServiceContract $shopService)
    {
        $this->productService = $productService;
        $this->shopService = $shopService;
    }

    public function index(ProductableListRequest $request): JsonResponse
    {
        return $this->sendResponse($this->productService->listAllProductables()->toArray(), __('List of Productables'));
    }

    public function attach(ProductableAttachRequest $request): JsonResponse
    {
        $productable = $this->productService->findProductable($request->getProductableType(), $request->getProductableId());
        $user = $request->getUser();
        $this->productService->attachProductableToUser($productable, $user);
        return $this->sendSuccess(__('Productable attached to user'));
    }

    public function detach(ProductableDetachRequest $request): JsonResponse
    {
        $productable = $this->productService->findProductable($request->getProductableType(), $request->getProductableId());
        $user = $request->getUser();
        $this->productService->detachProductableFromUser($productable, $user);
        return $this->sendSuccess(__('Productable detached from user'));
    }

    public function product(ProductableProductRequest $request): JsonResponse
    {
        $productable = $this->productService->findProductable($request->getProductableType(), $request->getProductableId());
        $product = $this->productService->findSingleProductForProductable($productable);
        if ($product) {
            return $this->sendResponseForResource(ProductResource::make($product), __('Single Product for Productable found'));
        }
        return $this->sendError(__('Single Product for this productable does not exist'), 404);
    }

    public function registered(ProductableRegisteredListRequest $request): JsonResponse
    {
        return $this->sendResponse($this->productService->listRegisteredMorphClasses(), __('List of registered Productable types (keys = Morph/Base classes, values = Productable classes)'));
    }
}
