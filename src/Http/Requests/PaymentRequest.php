<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Dtos\ClientDetailsDto;
use EscolaLms\Cart\Enums\CartPermissionsEnum;
use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user()->can(CartPermissionsEnum::BUY_PRODUCTS);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
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
}
