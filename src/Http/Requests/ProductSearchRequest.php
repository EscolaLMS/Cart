<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Http\Requests\Admin\ProductSearchRequest as AdminProductSearchRequest;
use Illuminate\Support\Arr;

class ProductSearchRequest extends AdminProductSearchRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules(): array
    {
        return Arr::except(parent::rules(), 'purchasable');
    }

    public function getPurchasable(): ?bool
    {
        return true;
    }
}
