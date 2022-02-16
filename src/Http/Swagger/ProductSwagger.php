<?php

namespace EscolaLms\Cart\Http\Swagger;

use EscolaLms\Cart\Http\Requests\ProductListRequest;
use Illuminate\Http\JsonResponse;

interface ProductSwagger
{
    /**
     * @OA\Get(
     *      path="/api/products",
     *      description="Get products",
     *      tags={"Products"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="product_type",
     *          description="Product type (class)",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="page",
     *          description="Page",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="integer",
     *          ),
     *      ),
     *      @OA\Parameter(
     *          name="per_page",
     *          description="Limit per page",
     *          required=false,
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
    public function index(ProductListRequest $request): JsonResponse;
}
