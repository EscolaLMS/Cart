<?php

namespace EscolaLms\Cart\Http\Requests\Admin;

use Illuminate\Support\Facades\Gate;

class ProductManuallyTriggerRequest extends ProductReadRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manuallyTrigger', $this->getProduct());
    }
}
