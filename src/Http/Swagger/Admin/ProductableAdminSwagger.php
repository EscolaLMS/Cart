<?php

namespace EscolaLms\Cart\Http\Swagger\Admin;

use EscolaLms\Cart\Http\Requests\Admin\ProductableAttachRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductableDetachRequest;
use Illuminate\Http\JsonResponse;

interface ProductableAdminSwagger
{
    /**
     * @OA\Post(
     *      path="/api/admin/productables/attach",
     *      description="Attach productable to user",
     *      tags={"Admin Products"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/json",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"productable_id", "productable_type", "user_id"},
     *                  @OA\Property(
     *                      property="productable_id",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="productable_type",
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
    public function attach(ProductableAttachRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/api/admin/productabless/detach",
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
     *                  required={"productable_id", "productable_type", "user_id"},
     *                  @OA\Property(
     *                      property="productable_id",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="productable_type",
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
    public function detach(ProductableDetachRequest $request): JsonResponse;
}
