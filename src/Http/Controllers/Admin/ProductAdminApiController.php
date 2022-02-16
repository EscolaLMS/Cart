<?php

namespace EscolaLms\Cart\Http\Controllers\Admin;

use EscolaLms\Cart\Events\ProductAttached;
use EscolaLms\Cart\Events\ProductDetached;
use EscolaLms\Cart\Http\Requests\ProductAttachRequest;
use EscolaLms\Cart\Http\Requests\ProductDetachRequest;
use EscolaLms\Cart\Http\Swagger\Admin\ProductAdminSwagger;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use Illuminate\Http\JsonResponse;

class ProductAdminApiController extends EscolaLmsBaseController implements ProductAdminSwagger
{
    protected ShopServiceContract $shopService;

    public function __construct(ShopServiceContract $shopService)
    {
        $this->shopService = $shopService;
    }

    public function attach(ProductAttachRequest $request): JsonResponse
    {
        $product = $this->shopService->findProduct($request->getProductType(), $request->getProductId());
        $user = $request->getUser();
        $product->attachToUser($user);
        event(new ProductAttached($product, $user));
        return $this->sendSuccess(__('Product attached to user'));
    }

    public function detach(ProductDetachRequest $request): JsonResponse
    {
        $product = $this->shopService->findProduct($request->getProductType(), $request->getProductId());
        $user = $request->getUser();
        $product->detachFromUser($user);
        event(new ProductDetached($product, $user));
        return $this->sendSuccess(__('Product detached from user'));
    }
}
