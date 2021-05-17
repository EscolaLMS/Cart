<?php

namespace Database\Factories\EscolaSoft\Cart\Models;

use EscolaSoft\Cart\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'price' => $this->faker->numberBetween(1, 1000)
        ];
    }
}