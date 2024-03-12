<?php

namespace EscolaLms\Cart\Http\Controllers;

use EscolaLms\Cart\Http\Requests\PaymentCartRequest;
use EscolaLms\Cart\Http\Requests\PaymentProductRequest;
use EscolaLms\Cart\Http\Swagger\PaymentSwagger;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
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
            $cart = $this->shopService->cartForUser($request->user());

            $payment = $this->shopService->purchaseCart($cart, $request->toClientDetailsDto(), $request->except([
                'client_name',
                'client_email',
                'client_street',
                'client_street_number',
                'client_postal',
                'client_city',
                'client_country',
                'client_company',
                'client_taxid',
            ]));

            return $this->sendResponseForResource(PaymentResource::make($payment), __('Payment created'));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }

    public function payProduct(PaymentProductRequest $request): JsonResponse
    {
        try {
            $payment = $this->shopService->purchaseProduct($request->getProduct(), $request->user(), $request->toClientDetailsDto());

            return $this->sendResponseForResource(PaymentResource::make($payment), __('Payment created'));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }
}
