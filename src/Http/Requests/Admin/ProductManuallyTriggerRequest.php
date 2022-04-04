<?php

namespace EscolaLms\Cart\Http\Requests\Admin;

use EscolaLms\Templates\Models\Template;
use Illuminate\Support\Facades\Gate;

class ProductManuallyTriggerRequest extends ProductReadRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manuallyTrigger', $this->getProduct());
    }

    public function getTemplate(): ?Template
    {
        return Template::findOrFail($this->route('idTemplate'));
    }
}
