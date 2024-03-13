<?php

namespace EscolaLms\Cart\Http\Requests\Admin;

use EscolaLms\Cart\Enums\PeriodEnum;
use EscolaLms\Cart\Enums\ProductType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class ProductRequest extends FormRequest
{
    protected function subscriptionRules(): array
    {
        return [
            'subscription_period' => [$this->requiredIfSubscription(), Rule::in(PeriodEnum::getValues())],
            'subscription_duration' => [$this->requiredIfSubscription(), 'integer', 'gt:0'],
            'recursive' => [$this->requiredIfSubscription(), 'boolean'],
        ];
    }

    protected function trialRules(): array
    {
        return [
            'has_trial' => [$this->requiredIfSubscription(), 'boolean'],
            'trial_period' => ['nullable', 'required_if:has_trial,true', Rule::in(PeriodEnum::getValues())],
            'trial_duration' => ['nullable', 'required_if:has_trial,true', 'integer', 'gt:0'],
        ];
    }

    protected function requiredIfSubscription(): string
    {
        return 'required_if:type,' . ProductType::SUBSCRIPTION . ',' . ProductType::SUBSCRIPTION_ALL_IN;
    }
}
