<?php

namespace EscolaLms\Cart\Http\Controllers;

use EscolaLms\Cart\Http\Requests\PaymentRequest;
use EscolaLms\Cart\Http\Swagger\CartSwagger;
use EscolaLms\Cart\Models\Course;
use EscolaLms\Cart\Services\Contracts\ShopServiceContract;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaLms\Payments\Dtos\PaymentMethodDto;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        return $this->sendResponse($this->shopService->getCartData(), __("Cart data fetched"));
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
        return $this->sendSuccess(__("Course added to cart"));
    }

    public function deleteCourse(string $course, Request $request): JsonResponse
    {
        $this->shopService->loadUserCart($request->user());
        $this->shopService->removeItemFromCart($course);

        return $this->sendSuccess(__("Course removed from cart"));
    }

    public function pay(PaymentRequest $request): JsonResponse
    {
        try {
            $this->shopService->loadUserCart($request->user());
            $paymentMethodDto = PaymentMethodDto::instantiateFromRequest($request);
            $this->shopService->purchase($paymentMethodDto);

            return $this->sendSuccess(__("Payment successful"));
        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }
}
