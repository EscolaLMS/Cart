<?php

namespace EscolaLms\Cart\Http\Requests;

use EscolaLms\Cart\Models\Product;

class PaymentProductRequest extends PaymentRequest
{

    public function getProductId(): int
    {
        return (int) $this->route('id');
    }

    public function getProduct(): Product
    {
        return Product::findOrFail($this->getProductId());
    }
}
