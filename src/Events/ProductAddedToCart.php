<?php

namespace EscolaLms\Cart\Events;

use EscolaLms\Cart\Contracts\Product;
use EscolaLms\Cart\Models\Cart;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ProductAddedToCart
{
    use Dispatchable, SerializesModels;

    private Product $product;
    private Cart $cart;

    public function __construct(Product $product, Cart $cart)
    {
        $this->product = $product;
        $this->cart = $cart;
    }

    public function getProduct(): Product
    {
        return $this->product;
    }

    public function getCart(): Cart
    {
        return $this->cart;
    }
}
