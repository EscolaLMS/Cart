<?php

namespace EscolaSoft\Cart\Http;

use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaLms\Core\Http\Resources\Status;
use EscolaLms\Courses\ValueObjects\CourseContent;
use EscolaSoft\Cart\Http\Swagger\CartSwagger;
use EscolaSoft\Cart\Models\Course;
use EscolaSoft\Cart\Services\Contracts\ShopServiceContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartApiController extends EscolaLmsBaseController implements CartSwagger
{
    private ShopServiceContract $cartService;

    /**
     * CartController constructor.
     * @param ShopServiceContract $cartService
     */
    public function __construct(ShopServiceContract $cartService)
    {
        $this->cartService = $cartService;
    }

    public function index(Request $request): JsonResponse
    {
        $this->cartService->loadUserCart($request->user());

        return new JsonResponse([
            'total' => $this->cartService->total(),
            'subtotal' => $this->cartService->subtotal(),
            'tax' => $this->cartService->tax(),
            'items' => $this->cartService->content()->pluck('buyable')->toArray(),
            'discount' => $this->cartService->getDiscount()
        ]);
    }

    public function addCourse(int $course, Request $request): JsonResponse
    {
        $course = Course::where('id', '=', $course)->first();
//        if (CourseContent::make($course)->isOwner($request->user())) {
//            return $this->sendError("User already has this course", 400);
//        }
        $this->cartService->loadUserCart($request->user());
        $this->cartService->addUnique($course);
        return (new Status(true))->response();
    }

    public function deleteCourse(string $course, Request $request): JsonResponse
    {
        $this->cartService->loadUserCart($request->user());
        $this->cartService->removeItemFromCart($course);

        return (new Status(true))->response();
    }


}