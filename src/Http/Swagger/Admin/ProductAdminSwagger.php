<?php

namespace EscolaLms\Cart\Http\Swagger\Admin;

use EscolaLms\Cart\Http\Requests\ProductAttachRequest;
use EscolaLms\Cart\Http\Requests\ProductDetachRequest;
use Illuminate\Http\JsonResponse;

interface ProductAdminSwagger
{
    /**
     * @OA\Post(
     *      path="/api/admin/products/attach",
     *      description="Attach product to user",
     *      tags={"Admin Product"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/json",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"product_id", "product_type", "user_id"},
     *                  @OA\Property(
     *                      property="product_id",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="product_type",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="user_id",
     *                      type="integer",
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
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
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
    public function attach(ProductAttachRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/api/admin/products/detach",
     *      description="Detach product from user",
     *      tags={"Admin Product"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/json",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"product_id", "product_type", "user_id"},
     *                  @OA\Property(
     *                      property="product_id",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="product_type",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="user_id",
     *                      type="integer",
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
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="message",
     *                  type="string"
     *              )
     *          )
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
    public function detach(ProductDetachRequest $request): JsonResponse;
}
