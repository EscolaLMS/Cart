<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderSearchRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('viewAny', Order::class);
    }

    public function rules()
    {
        return [
            'date_from' => ['sometimes', 'date'],
            'date_to' => ['sometimes', 'date'],
            'user_id' => ['sometimes', 'integer'],
            'author_id' => ['sometimes', 'integer'],
            'course_id' => ['sometimes', 'integer'],
            'per_page' => ['sometimes', 'integer'],
            'page' => ['sometimes', 'integer'],
            'order_by' => ['sometimes', Rule::in(['created_at', 'updated_at', 'user_id'])],
            'order' => ['sometimes', Rule::in(['ASC', 'DESC'])],
        ];
    }
}
