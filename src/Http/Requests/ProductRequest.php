<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\User;
use Illuminate\Foundation\Http\FormRequest;

abstract class ProductRequest extends FormRequest
{

    public function rules(): array
    {
        return [];
    }

    public function getId(): int
    {
        return $this->input('id') ? $this->input('id') : $this->route('id') ;
    }

    public function getProduct(): Product
    {
        return Product::findOrFail($this->getId());
    }

    public function getCartUser(): User
    {
        return User::findOrFail($this->user()->getKey());
    }
}
