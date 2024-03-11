<?php


namespace EscolaLms\Cart\Http\Swagger;

use EscolaLms\Cart\Http\Requests\AddMissingProductsRequest;
use EscolaLms\Cart\Http\Requests\CartItemRemoveFromCartRequest;
use EscolaLms\Cart\Http\Requests\PaymentCartRequest;
use EscolaLms\Cart\Http\Requests\PaymentProductRequest;
use EscolaLms\Cart\Http\Requests\ProductableAddToCartRequest;
use EscolaLms\Cart\Http\Requests\ProductAddToCartRequest;
use EscolaLms\Cart\Http\Requests\ProductRemoveFromCartRequest;
use EscolaLms\Cart\Http\Requests\ProductSetQuantityInCartRequest;
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
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/json",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"paymentMethodId"},
     *                  @OA\Property(
     *                      property="paymentMethodId",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="client_name",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="client_email",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="client_street",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="client_street_number",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="client_postal",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="client_city",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="client_country",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="client_company",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="client_taxid",
     *                      type="string"
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
    public function pay(PaymentCartRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/api/cart/pay/products/{id}",
     *      description="Pay for single product",
     *      tags={"Cart"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *           name="id",
     *           description="Product id",
     *           required=true,
     *           in="path",
     *           @OA\Schema(
     *               type="integer",
     *           ),
     *       ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/json",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"paymentMethodId"},
     *                  @OA\Property(
     *                      property="paymentMethodId",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="client_name",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="client_email",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="client_street",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="client_street_number",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="client_postal",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="client_city",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="client_country",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="client_company",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="client_taxid",
     *                      type="string"
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
    public function payProduct(PaymentProductRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/api/cart/products",
     *      description="Add product to cart and/or set product quantity",
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
     *                  required={"id"},
     *                  @OA\Property(
     *                      property="id",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="quantity",
     *                      type="integer",
     *                  )
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
    public function setProductQuantity(ProductSetQuantityInCartRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/api/cart/missing",
     *      description="Add missing products to Cart",
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
     *                  required={"id"},
     *                  @OA\Property(
     *                      property="products",
     *                      type="array",
     *                      @OA\Items(type="integer")
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
    public function addMissingProducts(AddMissingProductsRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/api/cart/add",
     *      description="Add productable to cart",
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
     *                  required={"productable_id", "productable_type"},
     *                  @OA\Property(
     *                      property="productable_id",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="productable_type",
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
    public function addProductable(ProductableAddToCartRequest $request): JsonResponse;

    /**
     * @OA\Delete(
     *      path="/api/cart/products/{id}",
     *      description="Remove product from cart",
     *      tags={"Cart"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="id",
     *          description="product id",
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
    public function remove(ProductRemoveFromCartRequest $request): JsonResponse;
}
