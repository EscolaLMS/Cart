<?php


namespace EscolaSoft\Cart\Http;


use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use EscolaSoft\Cart\Http\Resources\OrderResource;
use EscolaSoft\Cart\Http\Swagger\OrderSwagger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class OrderApiController extends EscolaLmsBaseController implements OrderSwagger
{
    public function index(Request $request): JsonResponse
    {
        try {
            return OrderResource::collection($request->user()->orders)->response();
        } catch (Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], 400);
        }
    }

}