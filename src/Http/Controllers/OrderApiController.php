<?php

namespace EscolaLms\Cart\Http\Controllers;

use EscolaLms\Cart\Http\Resources\OrderResource;
use EscolaLms\Cart\Http\Swagger\OrderSwagger;
use EscolaLms\Core\Http\Controllers\EscolaLmsBaseController;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderApiController extends EscolaLmsBaseController implements OrderSwagger
{
    public function index(Request $request): JsonResponse
    {
        try {
            return $this->sendResponseForResource(OrderResource::collection($request->user()->orders), __("Your orders history"));
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }
}
