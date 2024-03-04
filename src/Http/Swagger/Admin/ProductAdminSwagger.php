<?php

namespace EscolaLms\Cart\Http\Swagger\Admin;

use EscolaLms\Cart\Http\Requests\Admin\ProductAttachRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductCreateRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductDeleteRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductDetachRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductManuallyTriggerRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductReadRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductSearchRequest;
use EscolaLms\Cart\Http\Requests\Admin\ProductUpdateRequest;
use Illuminate\Http\JsonResponse;

interface ProductAdminSwagger
{
    /**
     * @OA\Get(
     *      path="/api/admin/products",
     *      description="Search products",
     *      tags={"Admin Products"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="order_by",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              enum={"created_at","updated_at","name"}
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="order",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              enum={"ASC", "DESC"}
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          description="Pagination Page Number",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *              default=1,
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Pagination Per Page",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *               default=15,
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="productable_id",
     *          description="Productable ID (for example Course Id)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="productable_type",
     *          description="Productable type (class) - required if productable_id is sent",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="free",
     *          description="Find free (price = 0)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="boolean",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="name",
     *          description="Partial name filter",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="type",
     *          description="Type (`single`, `bundle`, `subscription`)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="purchasable",
     *          description="Purchasable filter",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="boolean",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="tags",
     *          description="Tags",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="array",
     *              @OA\Items(type="string")
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
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/Product")
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
    public function index(ProductSearchRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/api/admin/products/",
     *      description="Create product",
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
     *                  required={"name", "price"},
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="price",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="price_old",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="tax_rate",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="extra_fees",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="purchasable",
     *                      type="boolean",
     *                  ),
     *                  @OA\Property(
     *                      property="teaser_url",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="poster",
     *                      description="image upload",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="duration",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="limit_per_user",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="limit_total",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="productables",
     *                      type="array",
     *                      @OA\Items(
     *                          @OA\Schema(
     *                              type="object",
     *                              @OA\Property(
     *                                  property="id",
     *                                  type="integer"
     *                              ),
     *                              @OA\Property(
     *                                  property="class",
     *                                  type="string"
     *                              ),
     *                          )
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="categories",
     *                      type="array",
     *                      @OA\Items(type="integer")
     *                  ),
     *                  @OA\Property(
     *                      property="tags",
     *                      type="array",
     *                      @OA\Items(type="string")
     *                  ),
     *                  @OA\Property(
     *                      property="related_products",
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
    public function create(ProductCreateRequest $request): JsonResponse;

    /**
     * @OA\Get(
     *      path="/api/admin/products/{id}",
     *      description="Read product",
     *      tags={"Admin Products"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="id",
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
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
     *                  @OA\Items(ref="#/components/schemas/Product")
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
    public function read(ProductReadRequest $request): JsonResponse;

    /**
     * @OA\Put(
     *      path="/api/admin/products/{id}",
     *      description="Create product",
     *      tags={"Admin Product"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/json",
     *              @OA\Schema(
     *                  type="object",
     *                  required={},
     *                  @OA\Property(
     *                      property="name",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="price",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="price_old",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="tax_rate",
     *                      type="float",
     *                  ),
     *                  @OA\Property(
     *                      property="extra_fees",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="purchasable",
     *                      type="boolean",
     *                  ),
     *                  @OA\Property(
     *                      property="teaser_url",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="poster",
     *                      description="image upload",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="duration",
     *                      type="string",
     *                  ),
     *                  @OA\Property(
     *                      property="limit_per_user",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="limit_total",
     *                      type="integer",
     *                  ),
     *                  @OA\Property(
     *                      property="productables",
     *                      type="array",
     *                      @OA\Items(
     *                          @OA\Schema(
     *                              type="object",
     *                              @OA\Property(
     *                                  property="id",
     *                                  type="integer"
     *                              ),
     *                              @OA\Property(
     *                                  property="class",
     *                                  type="string"
     *                              ),
     *                          )
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="categories",
     *                      type="array",
     *                      @OA\Items(type="integer")
     *                  ),
     *                  @OA\Property(
     *                      property="tags",
     *                      type="array",
     *                      @OA\Items(type="string")
     *                  ),
     *                  @OA\Property(
     *                      property="related_products",
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
    public function update(ProductUpdateRequest $request): JsonResponse;

    /**
     * @OA\Delete(
     *      path="/api/admin/products/{id}",
     *      description="Delete product",
     *      tags={"Admin Products"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="id",
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
     *          @OA\Schema(
     *              type="object",
     *              @OA\Property(
     *                  property="success",
     *                  type="boolean"
     *              ),
     *              @OA\Property(
     *                  property="data",
     *                  type="array",
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
    public function delete(ProductDeleteRequest $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/api/admin/products/{id}/attach",
     *      description="Attach product to user",
     *      tags={"Admin Product"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Product",
     *          @OA\Schema(
     *             type="integer",
     *         ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/json",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"user_id"},
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
     *      path="/api/admin/products/{id}/detach",
     *      description="Detach product from user",
     *      tags={"Admin Product"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="id",
     *          description="id of Product",
     *          @OA\Schema(
     *             type="integer",
     *         ),
     *          required=true,
     *          in="path"
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="multipart/json",
     *              @OA\Schema(
     *                  type="object",
     *                  required={"user_id"},
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

    /**
     * @OA\Post(
     *     path="/api/admin/products/{id}/trigger-event-manually/{idTemplate}",
     *     summary="Manually triggered event for users of the product",
     *     tags={"Admin Product"},
     *     security={
     *         {"passport": {}},
     *     },
     *     @OA\Parameter(
     *          name="id",
     *          description="id of Product",
     *          @OA\Schema(
     *             type="integer",
     *         ),
     *          required=true,
     *          in="path"
     *      ),
     *     @OA\Parameter(
     *          name="idTemplate",
     *          description="id of Template",
     *          @OA\Schema(
     *             type="integer",
     *         ),
     *          required=true,
     *          in="path"
     *      ),
     *     @OA\Response(
     *          response=200,
     *          description="Event dispatched successfully",
     *      ),
     *     @OA\Response(
     *          response=401,
     *          description="endpoint requires authentication",
     *      ),
     *     @OA\Response(
     *          response=403,
     *          description="user doesn't have required access rights",
     *      ),
     *     @OA\Response(
     *          response=422,
     *          description="one of the parameters has invalid format",
     *      ),
     *     @OA\Response(
     *          response=500,
     *          description="server-side error",
     *      ),
     * )
     */
    public function triggerEventManuallyForUsers(ProductManuallyTriggerRequest $request): JsonResponse;
}
