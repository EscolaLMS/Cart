<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Dtos\ClientDetailsDto;
use EscolaLms\Cart\Enums\CartPermissionsEnum;
use EscolaLms\Cart\Models\User;
use Illuminate\Foundation\Http\FormRequest;

abstract class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(CartPermissionsEnum::BUY_PRODUCTS);
    }

    public function rules(): array
    {
        return [
            'client_name' => ['sometimes', 'string'],
            'client_email' => ['sometimes', 'email'],
            'client_street' => ['sometimes', 'string'],
            'client_street_number' => ['sometimes', 'string'],
            'client_postal' => ['sometimes', 'string'],
            'client_city' => ['sometimes', 'string'],
            'client_country' => ['sometimes', 'string'],
            'client_company' => ['sometimes', 'string'],
            'client_taxid' => ['sometimes', 'string', 'required_with:client_company'],
        ];
    }

    public function toClientDetailsDto(): ClientDetailsDto
    {
        return new ClientDetailsDto(
            $this->input('client_name'),
            $this->input('client_email'),
            $this->input('client_street'),
            $this->input('client_street_number'),
            $this->input('client_city'),
            $this->input('client_postal'),
            $this->input('client_country'),
            $this->input('client_company'),
            $this->input('client_taxid')
        );
    }

    public function getAdditionalPaymentParameters(): array
    {
        return $this->except([
            'client_name',
            'client_email',
            'client_street',
            'client_street_number',
            'client_postal',
            'client_city',
            'client_country',
            'client_company',
            'client_taxid',
        ]);
    }

    public function getCartUser(): User
    {
        return User::findOrFail($this->user()->getKey());
    }
}
