<?php


namespace EscolaSoft\Cart\Http\Swagger;


use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

interface OrderSwagger
{

    /**
     * @OA\Get(
     *      path="/api/orders",
     *      description="Get user orders",
     *      tags={"Orders"},
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
}