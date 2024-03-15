<?php

namespace EscolaLms\Cart\Http\Controllers;

use EscolaLms\Cart\Exceptions\InactiveSubscription;
use EscolaLms\Cart\Http\Requests\ProductableAttachRequest;
use EscolaLms\Cart\Http\Swagger\ProductablesSwagger;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use Illuminate\Http\JsonResponse;

class ProductablesApiController extends EscolaLmsBaseController implements ProductablesSwagger
{
    protected ProductServiceContract $productService;

    public function __construct(ProductServiceContract $productService)
    {
        $this->productService = $productService;
    }

    public function attach(ProductableAttachRequest $request): JsonResponse
    {
        $user = $request->user();
        $activeSubscription = $this->productService->hasActiveSubscriptionAllIn($user);

        if (!$activeSubscription) {
            throw new InactiveSubscription();
        }

        $productable = $this->productService->findProductable($request->getProductableType(), $request->getProductableId());
        $this->productService->attachProductableToUser($productable, $request->getCartUser(), 1, $activeSubscription);

        return $this->sendSuccess(__('Productable attached to user'));
    }
}
