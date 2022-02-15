<?php

namespace EscolaLms\Cart\Tests\Mocks;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'price' => $this->faker->numberBetween(100, 10000),
            'tax_rate' => 0,
        ];
    }
}
