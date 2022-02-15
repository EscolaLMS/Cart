<?php


namespace EscolaLms\Cart\Http\Swagger;

use EscolaLms\Cart\Http\Requests\CartItemRemoveFromCartRequest;
use EscolaLms\Cart\Http\Requests\PaymentRequest;
use EscolaLms\Cart\Http\Requests\ProductAddToCartRequest;
use EscolaLms\Cart\Http\Requests\ProductRemoveFromCartRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface CartSwagger
{

    /**
     * @OA\Get(
     *      path="/api/cart",
     *      description="Get cart details",
     *      tags={"Cart"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Bad request",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      )
     *   )
     */

    public function index(Request $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/api/cart/pay",
     *      description="Pay for cart",
     *      tags={"Cart"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\RequestBody(
     *          required=false,
     *          @OA\MediaType(
     *              mediaType="multipart/json",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"paymentMethodId"},
     *                  @OA\Property(
     *                      property="paymentMethodId",
     *                      type="string",
     *                  ),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Bad request",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      )
     *   )
     */
    public function pay(PaymentRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/api/cart/add",
     *      description="Add product to cart",
     *      tags={"Cart"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/json",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"product_id", "product_type"},
     *                  @OA\Property(
     *                      property="product_id",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="product_type",
     *                      type="string",
     *                  ),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Bad request",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      )
     *   )
     */
    public function add(ProductAddToCartRequest $request): JsonResponse;

    /**
     * @OA\Delete(
     *      path="/api/cart/{id}",
     *      description="Remove cart item from cart",
     *      tags={"Cart"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="id",
     *          description="cart item id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Bad request",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      )
     *   )
     */
    public function removeCartItem(CartItemRemoveFromCartRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/api/cart/remove",
     *      description="Remove product from cart",
     *      tags={"Cart"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/json",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"product_id", "product_type"},
     *                  @OA\Property(
     *                      property="product_id",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="product_type",
     *                      type="string",
     *                  ),
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          ),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Bad request",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      )
     *   )
     */
    public function removeProduct(ProductRemoveFromCartRequest $request): JsonResponse;
}
