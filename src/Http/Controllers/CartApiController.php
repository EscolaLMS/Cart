<?php

namespace EscolaLms\Cart\Http\Controllers;

use EscolaLms\Cart\Http\Requests\CartItemRemoveFromCartRequest;
use EscolaLms\Cart\Http\Requests\PaymentRequest;
use EscolaLms\Cart\Http\Requests\ProductAddToCartRequest;
use EscolaLms\Cart\Http\Requests\ProductRemoveFromCartRequest;
use EscolaLms\Cart\Http\Swagger\CartSwagger;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaLms\Payments\Dtos\PaymentMethodDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartApiController extends EscolaLmsBaseController implements CartSwagger
{
    protected ShopServiceContract $shopService;

    public function __construct(ShopServiceContract $shopService)
    {
        $this->shopService = $shopService;
    }

    public function index(Request $request): JsonResponse
    {
        $cart = $this->shopService->cartForUser($request->user());
        return $this->sendResponseForResource($this->shopService->cartAsJsonResource($cart), __("Cart data fetched"));
    }

    public function add(ProductAddToCartRequest $request): JsonResponse
    {
        $cart = $this->shopService->cartForUser($request->user());
        $buyable = $this->shopService->findProduct($request->getProductType(), $request->getProductId());
        if (!$buyable->getBuyableByUserAttribute($request->user())) {
            return $this->sendError(__("You can not add this product to cart"), 403);
        }
        $this->shopService->addUniqueProductToCart($cart, $buyable);
        return $this->sendSuccess(__("Product added to cart"));
    }

    public function removeCartItem(CartItemRemoveFromCartRequest $request): JsonResponse
    {
        $cart = $this->shopService->cartForUser($request->user());
        $this->shopService->removeItemFromCart($cart, $request->getCartItemId());
        return $this->sendSuccess(__("Product removed from cart"));
    }

    public function removeProduct(ProductRemoveFromCartRequest $request): JsonResponse
    {
        $cart = $this->shopService->cartForUser($request->user());
        $buyable = $this->shopService->findProduct($request->getProductType(), $request->getProductId());
        $this->shopService->removeProductFromCart($cart, $buyable);
        return $this->sendSuccess(__("Product removed from cart"));
    }

    public function pay(PaymentRequest $request): JsonResponse
    {
        try {
            $cart = $this->shopService->cartForUser($request->user());
            $paymentMethodDto = PaymentMethodDto::instantiateFromRequest($request);
            $this->shopService->purchaseCart($cart, $paymentMethodDto);

            return $this->sendSuccess(__("Payment successful"));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }
}
