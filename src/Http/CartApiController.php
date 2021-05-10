<?php

namespace EscolaSoft\Cart\Http;

use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaLms\Core\Http\Resources\Status;
use EscolaLms\Courses\ValueObjects\CourseContent;
use EscolaSoft\Cart\Dtos\PaymentMethodAsId;
use EscolaSoft\Cart\Http\Requests\PaymentRequest;
use EscolaSoft\Cart\Http\Swagger\CartSwagger;
use EscolaSoft\Cart\Models\Course;
use EscolaSoft\Cart\Services\Contracts\ShopServiceContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartApiController extends EscolaLmsBaseController implements CartSwagger
{
    private ShopServiceContract $shopService;

    /**
     * CartController constructor.
     * @param ShopServiceContract $cartService
     */
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
        $course = Course::where('id', '=', $course)->first();
//        if (CourseContent::make($course)->isOwner($request->user())) {
//            return $this->sendError("User already has this course", 400);
//        }
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
            $this->shopService->purchase();

            return (new Status(true))->response();
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 400);
        }
    }

}