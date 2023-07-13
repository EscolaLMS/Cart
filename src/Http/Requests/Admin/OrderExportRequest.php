<?php

namespace EscolaLms\Cart\Http\Requests\Admin;

use EscolaLms\Cart\Enums\ExportFormatEnum;
use EscolaLms\Cart\Models\Order;
use Illuminate\Validation\Rule;

class OrderExportRequest extends OrderSearchRequest
{
    public function authorize()
    {
        return $this->user('api') && $this->user('api')->can('export', Order::class);
    }

    public function rules()
    {
        return array_merge(parent::rules(), [
            'format' => ['sometimes', 'string', Rule::in(ExportFormatEnum::getValues())],
        ]);
    }
}
