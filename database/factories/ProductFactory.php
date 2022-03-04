<?php

namespace EscolaLms\Cart\Database\Factories;

use EscolaLms\Cart\Enums\ProductType;
use EscolaLms\Cart\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
            'type' => ProductType::SINGLE,
            'description' => $this->faker->sentence(),
            'price' => 1000,
            'price_old' => null,
            'tax_rate' => 0,
            'limit_total' => null,
            'limit_per_user' => 1
        ];
    }

    public function single()
    {
        return $this->state([
            'type' => ProductType::SINGLE
        ]);
    }

    public function bundle()
    {
        return $this->state([
            'type' => ProductType::BUNDLE
        ]);
    }
}
