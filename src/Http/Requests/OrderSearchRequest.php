<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Http\Requests\Admin\OrderSearchRequest as AdminOrderSearchRequest;
use EscolaLms\Cart\Models\Order;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class OrderSearchRequest extends AdminOrderSearchRequest
{
    public function authorize()
    {
        return Gate::allows('viewOwn', Order::class);
    }

    public function getUserId(): ?int
    {
        return Auth::user()->getKey();
    }

    public function rules()
    {
        return Arr::except(parent::rules(), ['user_id']);
    }
}
