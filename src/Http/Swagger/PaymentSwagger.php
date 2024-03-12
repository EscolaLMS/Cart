<?php


namespace EscolaLms\Cart\Http\Swagger;

use EscolaLms\Cart\Http\Requests\PaymentCartRequest;
use EscolaLms\Cart\Http\Requests\PaymentProductRequest;
use Illuminate\Http\JsonResponse;

interface PaymentSwagger
{
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
}
