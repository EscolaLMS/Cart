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
            return $this->sendResponse(OrderResource::collection($request->user()->orders)->toArray($request), __("Your orders history"));
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), 400);
        }
    }
}
