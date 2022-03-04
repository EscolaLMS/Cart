<?php

namespace EscolaLms\Cart\Models\Contracts\Base;

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

    public function getOptions(): array
    {
        return [];
    }
}
