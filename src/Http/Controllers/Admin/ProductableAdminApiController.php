<?php

namespace EscolaLms\Cart\Http\Controllers\Admin;

use EscolaLms\Cart\Http\Requests\Admin\ProductableAttachRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductableDetachRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductableRegisteredListRequest;
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

    public function registered(ProductableRegisteredListRequest $request): JsonResponse
    {
        return $this->sendResponse($this->productService->listRegisteredProductableClasses(), __('List of registered Productable types'));
    }
}
