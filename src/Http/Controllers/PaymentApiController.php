<?php

namespace EscolaLms\Cart\Http\Controllers;

use EscolaLms\Cart\Http\Requests\PaymentCartRequest;
use EscolaLms\Cart\Http\Requests\PaymentProductRequest;
use EscolaLms\Cart\Http\Swagger\PaymentSwagger;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaLms\Core\Models\User;
use EscolaLms\Payments\Http\Resources\PaymentResource;
use Illuminate\Http\JsonResponse;

class PaymentApiController extends EscolaLmsBaseController implements PaymentSwagger
{
    protected ShopServiceContract $shopService;

    public function __construct(ShopServiceContract $shopService)
    {
        $this->shopService = $shopService;
    }

    public function pay(PaymentCartRequest $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $request->user();
            $cart = $this->shopService->cartForUser($user);

            $payment = $this->shopService->purchaseCart(
                $cart,
                $request->toClientDetailsDto(),
                $request->getAdditionalPaymentParameters()
            );

            return $this->sendResponseForResource(PaymentResource::make($payment), __('Payment created'));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    public function payProduct(PaymentProductRequest $request): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $request->user();
            $payment = $this->shopService->purchaseProduct(
                $request->getProduct(),
                $user,
                $request->toClientDetailsDto(),
                $request->getAdditionalPaymentParameters()
            );

            return $this->sendResponseForResource(PaymentResource::make($payment), __('Payment created'));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }
}
