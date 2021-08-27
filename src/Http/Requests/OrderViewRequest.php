<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderViewRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('viewAny', Order::class);
    }
}
