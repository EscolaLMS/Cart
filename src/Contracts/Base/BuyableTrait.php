<?php

namespace EscolaLms\Cart\Contracts\Base;

/**
 * @see \EscolaLms\Cart\Contracts\Buyable
 * @see \Treestoneit\ShoppingCart\BuyableTrait
 */
trait BuyableTrait
{
    /**
     * @return int|string
     */
    public function getBuyableIdentifier()
    {
        return $this->getKey();
    }

    public function getExtraFees(): int
    {
        return 0;
    }

    public function getOptions(): array
    {
        return [];
    }

    abstract public function getBuyableDescription(): string;
    abstract public function getBuyablePrice(): int;
}
