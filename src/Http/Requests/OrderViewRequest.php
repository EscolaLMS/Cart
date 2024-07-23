<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Exceptions\OrderNotFoundException;
use EscolaLms\Cart\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class OrderViewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('view', $this->getOrder());
    }

    protected function prepareForValidation(): void
    {
        parent::prepareForValidation();
        $this->merge([
            'id' => $this->route('id')
        ]);
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', Rule::exists(Order::class, 'id')],
        ];
    }

    public function getId(): int
    {
        /** @var int $id */
        $id = $this->route('id');
        return $id;
    }

    public function getOrder(): Order
    {
        /** @var Order|null $order */
        $order = Order::find($this->getId());

        if (is_null($order)) {
            throw new OrderNotFoundException();
        }

        return $order;
    }
}
