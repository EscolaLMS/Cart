<?php

namespace EscolaLms\Cart\Http\Swagger\Admin;

use EscolaLms\Cart\Http\Requests\Admin\ProductableAttachRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductableDetachRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductableListRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductableProductRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductableRegisteredListRequest;
use Illuminate\Http\JsonResponse;

interface ProductableAdminSwagger
{
    /**
     * @OA\Get(
     *      path="/api/admin/productables/",
     *      description="Get list of all productables",
     *      tags={"Admin Product"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          ),
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *              ),
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
    public function index(ProductableListRequest $request): JsonResponse;

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
     *      path="/api/admin/productables/detach",
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

    /**
     * @OA\Post(
     *      path="/api/admin/productables/registered",
     *      description="List of registered Productable types",
     *      tags={"Admin Product"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Response(
     *          response=200,
     *          description="successful operation",
     *          @OA\MediaType(
     *              mediaType="application/json",
     *          ),
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="data",
     *                  type="array"
     *              ),
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
    public function registered(ProductableRegisteredListRequest $request): JsonResponse;

    /**
     * @OA\Get(
     *      path="/api/admin/productables/product",
     *      description="Get single product for this productable (if it exists)",
     *      tags={"Admin Product"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="productable_type",
     *          description="Productable class",
     *          required=true,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="productable_id",
     *          description="Productable id",
     *          required=true,
     *          in="query",
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
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="data",
     *                  type="object",
     *                  @OA\Schema(ref="#/components/schemas/Product")
     *              ),
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
    public function product(ProductableProductRequest $request): JsonResponse;
}
