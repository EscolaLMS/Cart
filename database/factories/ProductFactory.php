<?php

namespace EscolaLms\Cart\Database\Factories;

use EscolaLms\Cart\Enums\PeriodEnum;
use EscolaLms\Cart\Enums\ProductType;
use EscolaLms\Cart\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'type' => ProductType::SINGLE,
            'description' => $this->faker->sentence(),
            'price' => 1000,
            'price_old' => null,
            'tax_rate' => 0,
            'limit_total' => null,
            'limit_per_user' => 1,
            'fields' => [
                $this->faker->word => $this->faker->word
            ]
        ];
    }

    public function single(): self
    {
        return $this->state([
            'type' => ProductType::SINGLE
        ]);
    }

    public function bundle(): self
    {
        return $this->state([
            'type' => ProductType::BUNDLE
        ]);
    }

    public function subscription(?string $productType = null): self
    {
        $hasTrial = $this->faker->boolean;

        return $this->state([
            'type' => $productType ?? ProductType::SUBSCRIPTION,
            'subscription_period' => $this->faker->randomElement(PeriodEnum::getValues()),
            'subscription_duration' => $this->faker->numberBetween(1, 10),
            'recursive' => $this->faker->boolean,
            'has_trial' => $hasTrial,
            'trial_period' => $hasTrial ? $this->faker->randomElement(PeriodEnum::getValues()) : null,
            'trial_duration' => $hasTrial ? $this->faker->numberBetween(1, 10) : null,
        ]);
    }

    public function subscriptionWithTrial(?string $productType = null): self
    {
        return $this->state([
            'type' => $productType ?? ProductType::SUBSCRIPTION,
            'subscription_period' => $this->faker->randomElement(PeriodEnum::getValues()),
            'subscription_duration' => $this->faker->numberBetween(1, 10),
            'recursive' => $this->faker->boolean,
            'has_trial' => true,
            'trial_period' => $this->faker->randomElement(PeriodEnum::getValues()),
            'trial_duration' => $this->faker->numberBetween(1, 10),
        ]);
    }

    public function subscriptionWithoutTrial(?string $productType = null): self
    {
        return $this->state([
            'type' => $productType ?? ProductType::SUBSCRIPTION,
            'subscription_period' => $this->faker->randomElement(PeriodEnum::getValues()),
            'subscription_duration' => $this->faker->numberBetween(1, 10),
            'recursive' => $this->faker->boolean,
            'has_trial' => false,
            'trial_period' => null,
            'trial_duration' => null,
        ]);
    }
}
