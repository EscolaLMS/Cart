<?php

namespace EscolaLms\Cart\Http;

use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaLms\Core\Http\Resources\Status;
use EscolaLms\Payments\Dtos\PaymentMethodDto;
use EscolaLms\Cart\Http\Requests\PaymentRequest;
use EscolaLms\Cart\Http\Swagger\CartSwagger;
use EscolaLms\Cart\Models\Course;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;

class CartApiController extends EscolaLmsBaseController implements CartSwagger
{
    private ShopServiceContract $shopService;

    public function __construct(ShopServiceContract $shopService)
    {
        $this->shopService = $shopService;
    }

    public function index(Request $request): JsonResponse
    {
        $this->shopService->loadUserCart($request->user());

        return $this->shopService->getResource();
    }

    public function addCourse(int $course, Request $request): JsonResponse
    {
        /** @var Course&Model $course */
        $course = Course::where('id', '=', $course)->first();
        if ($course->alreadyBoughtBy($request->user())) {
            return $this->sendError("User already has this course", 400);
        }
        $this->shopService->loadUserCart($request->user());
        $this->shopService->addUnique($course);
        return (new Status(true))->response();
    }

    public function deleteCourse(string $course, Request $request): JsonResponse
    {
        $this->shopService->loadUserCart($request->user());
        $this->shopService->removeItemFromCart($course);

        return (new Status(true))->response();
    }

    public function pay(PaymentRequest $request): JsonResponse
    {
        try {
            $this->shopService->loadUserCart($request->user());
            $paymentMethodDto = PaymentMethodDto::instantiateFromRequest($request);
            $this->shopService->purchase($paymentMethodDto);

            return (new Status(true))->response();
        } catch (\Exception $e) {

            dd($e);
            return new JsonResponse(['message' => $e->getMessage()], 400);
        }
    }
}
