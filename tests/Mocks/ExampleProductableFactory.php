<?php

namespace EscolaLms\Cart\Tests\Mocks;

use Illuminate\Database\Eloquent\Factories\Factory;

class ExampleProductableFactory extends Factory
{
    protected $model = ExampleProductable::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word(),
        ];
    }
}
