<?php


namespace EscolaLms\Cart\Http\Swagger;


use EscolaLms\Cart\Http\Requests\PaymentRequest;
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
     *      path="/api/cart/course/{course}",
     *      description="Add course to cart",
     *      tags={"Cart"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="course",
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

    public function addCourse(int $course, Request $request): JsonResponse;

    /**
     * @OA\Delete(
     *      path="/api/cart/course/{course}",
     *      description="Remove course from cart",
     *      tags={"Cart"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="course",
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

    public function deleteCourse(string $course, Request $request): JsonResponse;

    /**
     * @OA\Post(
     *      path="/api/cart/pay",
     *      description="Pay for cart",
     *      tags={"Cart"},
     *      security={
     *          {"passport": {}},
     *      },
     *      @OA\Parameter(
     *          name="paymentMethodId",
     *          required=false,
     *          in="query",
     *          @OA\Schema(
     *              type="string",
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

    public function pay(PaymentRequest $request): JsonResponse;
}