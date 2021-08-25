<?php


namespace EscolaLms\Cart\Database\Factories\Models;

use EscolaLms\Cart\Enums\OrderStatus;
use EscolaLms\Cart\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'total' => 0,
            'subtotal' => 0,
            'tax' => 0,
            'status' => OrderStatus::PAID
        ];
    }
}
