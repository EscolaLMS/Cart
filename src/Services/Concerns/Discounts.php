<?php


namespace EscolaSoft\Cart\Services\Concerns;


use EscolaSoft\Cart\Enums\DiscountValueType;
use EscolaSoft\Cart\Models\Discount;
use EscolaSoft\Cart\Services\Strategies\Contracts\DiscountStrategyContract;
use EscolaSoft\Cart\Services\Strategies\Discount\NoneStrategy;

trait Discounts
{
    protected ?Discount $discount;

    public function total(int $taxRate = null): float
    {
        return $this->getDiscountStrategy()->total($this->subtotal(), $this->tax($taxRate));
    }

    public function getDiscount(): ?Discount
    {
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
    }

    private function getDiscountStrategy(): DiscountStrategyContract
    {
        $this->discount = $this->getDiscount();
        if (is_null($this->discount)) {
            return new NoneStrategy;
        }

        $discountType = DiscountValueType::getName($this->discount->value_type);
        $className = 'EscolaSoft\\Cart\\Services\\Strategies\\Discount\\' . ucfirst(strtolower($discountType)) . 'Strategy';

        if (!class_exists($className)) {
            throw new \RuntimeException($className . ' strategy is not exists.');
        }

        return new $className($this->discount);
    }

}