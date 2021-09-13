<?php

namespace EscolaLms\Cart\Services\Concerns;

use EscolaLms\Cart\Enums\DiscountValueType;
use EscolaLms\Cart\Models\Discount;
use EscolaLms\Cart\Services\Strategies\Contracts\DiscountStrategyContract;
use EscolaLms\Cart\Services\Strategies\Discount\NoneStrategy;

trait Discounts
{
    protected ?Discount $discount;

    public function total(int $taxRate = null): int
    {
        return $this->getDiscountStrategy()->total($this->subtotal(), $this->tax($taxRate));
    }

    public function getDiscount(): ?Discount
    {
        return null;
        //TODO: move discounts to separate package or implement them here, but for now it doesn't work
        /*
        $userDiscountId = $this->getModel()->user_discount_id;

        if (is_null($userDiscountId)) {
            return null;
        }

        if (isset($this->discount) && !is_null($this->discount)) {
            return $this->discount;
        }

        $userDiscount = $this->discountService->findUserDiscount($userDiscountId);

        if (is_null($userDiscount)) {
            return null;
        }

        return $this->discount = $userDiscount->discount;
        */
    }

    private function getDiscountStrategy(): DiscountStrategyContract
    {
        $this->discount = $this->getDiscount();
        if (is_null($this->discount)) {
            return new NoneStrategy;
        }

        $discountType = DiscountValueType::getName($this->discount->value_type);
        $className = 'EscolaLms\\Cart\\Services\\Strategies\\Discount\\' . ucfirst(strtolower($discountType)) . 'Strategy';

        if (!class_exists($className)) {
            throw new \RuntimeException($className . ' strategy is not exists.');
        }

        return new $className($this->discount);
    }
}
