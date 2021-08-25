<?php

namespace EscolaLms\Cart\Database\Factories\Models;

use EscolaLms\Cart\Models\Product;
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
