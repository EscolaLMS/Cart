<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Models\Order;
use Illuminate\Foundation\Http\FormRequest;

class OrderViewRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('view', $this->getOrder());
    }

    public function rules()
    {
        return [];
    }

    public function getOrder(): Order
    {
        return Order::findOrFail($this->route('id'));
    }
}
