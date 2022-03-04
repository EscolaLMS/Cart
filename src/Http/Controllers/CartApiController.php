<?php

namespace EscolaLms\Cart\Http\Controllers;

use EscolaLms\Cart\Http\Requests\CartItemRemoveFromCartRequest;
use EscolaLms\Cart\Http\Requests\PaymentRequest;
use EscolaLms\Cart\Http\Requests\ProductableAddToCartRequest;
use EscolaLms\Cart\Http\Requests\ProductAddToCartRequest;
use EscolaLms\Cart\Http\Requests\ProductRemoveFromCartRequest;
use EscolaLms\Cart\Http\Swagger\CartSwagger;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaLms\Payments\Dtos\PaymentMethodDto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartApiController extends EscolaLmsBaseController implements CartSwagger
{
    protected ProductServiceContract $productService;
    protected ShopServiceContract $shopService;

    public function __construct(ProductServiceContract $productService, ShopServiceContract $shopService)
    {
        $this->productService = $productService;
        $this->shopService = $shopService;
    }

    public function index(Request $request): JsonResponse
    {
        $cart = $this->shopService->cartForUser($request->user());
        return $this->sendResponseForResource($this->shopService->cartAsJsonResource($cart), __("Cart data fetched"));
    }

    public function add(ProductAddToCartRequest $request): JsonResponse
    {
        $product = $request->getProduct();
        $user = $request->user();
        $cart = $this->shopService->cartForUser($user);
        if (!$this->productService->productIsBuyableByUser($product, $user)) {
            return $this->sendError(__("You can not add this product to cart"), 403);
        }
        $this->shopService->addUniqueProductToCart($cart, $product);
        return $this->sendSuccess(__("Product added to cart"));
    }

    public function addProductable(ProductableAddToCartRequest $request): JsonResponse
    {
        $productable = $this->productService->findProductable($request->getProductableType(), $request->getProductableId());
        $product = $this->productService->findSingleProductForProductable($productable);
        $cart = $this->shopService->cartForUser($request->user());
        if (!$this->productService->productIsBuyableByUser($product, $request->user())) {
            return $this->sendError(__("You can not add this product to cart"), 403);
        }
        $this->shopService->addUniqueProductToCart($cart, $product);
        return $this->sendSuccess(__("Product added to cart"));
    }

    public function remove(ProductRemoveFromCartRequest $request): JsonResponse
    {
        $product = $request->getProduct();
        $user = $request->user();
        $cart = $this->shopService->cartForUser($user);
        $this->shopService->removeProductFromCart($cart, $product);
        return $this->sendSuccess(__("Product removed from cart"));
    }

    public function removeCartItem(CartItemRemoveFromCartRequest $request): JsonResponse
    {
        $cart = $this->shopService->cartForUser($request->user());
        $this->shopService->removeItemFromCart($cart, $request->getCartItemId());
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
