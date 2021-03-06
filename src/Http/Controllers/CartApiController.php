<?php

namespace EscolaLms\Cart\Http\Controllers;

use EscolaLms\Cart\Http\Requests\AddMissingProductsRequest;
use EscolaLms\Cart\Http\Requests\CartItemRemoveFromCartRequest;
use EscolaLms\Cart\Http\Requests\PaymentRequest;
use EscolaLms\Cart\Http\Requests\ProductableAddToCartRequest;
use EscolaLms\Cart\Http\Requests\ProductRemoveFromCartRequest;
use EscolaLms\Cart\Http\Requests\ProductSetQuantityInCartRequest;
use EscolaLms\Cart\Http\Swagger\CartSwagger;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaLms\Payments\Http\Resources\PaymentResource;
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

    public function setProductQuantity(ProductSetQuantityInCartRequest $request): JsonResponse
    {
        $product = $request->getProduct();
        $user = $request->user();
        $cart = $this->shopService->cartForUser($user);
        if (!$this->productService->productIsBuyableByUser($product, $user)) {
            return $this->sendError(__("You can not add this product to cart"), 403);
        }
        $this->shopService->updateProductQuantity($cart, $product, $request->getQuantity());
        return $this->sendSuccess(__('Product quantity changed'));
    }

    public function addMissingProducts(AddMissingProductsRequest $request): JsonResponse
    {
        $user = $request->user();
        $cart = $this->shopService->cartForUser($user);
        $this->shopService->addMissingProductsToCart($cart, $request->input('products', []));
        return $this->sendSuccess(__('Missing (buyable) products added to Cart'));
    }

    public function addProductable(ProductableAddToCartRequest $request): JsonResponse
    {
        $productable = $this->productService->findProductable($request->getProductableType(), $request->getProductableId());
        $product = $this->productService->findSingleProductForProductable($productable);
        if (!$product) {
            return $this->sendError(__('Single Product for this productable does not exist'), 404);
        }
        $cart = $this->shopService->cartForUser($request->user());
        if (!$this->productService->productIsBuyableByUser($product, $request->user())) {
            return $this->sendError(__("You can not add this product to cart"), 403);
        }
        $this->shopService->addProductToCart($cart, $product, 1);
        return $this->sendSuccess(__("Product added to cart"));
    }

    public function remove(ProductRemoveFromCartRequest $request): JsonResponse
    {
        $product = $request->getProduct();
        $user = $request->user();
        $cart = $this->shopService->cartForUser($user);
        $this->shopService->updateProductQuantity($cart, $product, 0);
        return $this->sendSuccess(__("Product removed from cart"));
    }

    public function pay(PaymentRequest $request): JsonResponse
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
}
