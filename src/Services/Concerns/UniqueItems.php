<?php

namespace EscolaLms\Cart\Services\Concerns;

use Treestoneit\ShoppingCart\Buyable;
use Treestoneit\ShoppingCart\Models\CartItem;

trait UniqueItems
{
    public function addUnique(Buyable $item): self
    {
        if (!$this->hasItem($item)) {
            $this->add($item, 1);
        }

        return $this;
    }

    public function hasItem(Buyable $item): bool
    {
        return $this->findItem($item) !== null;
    }

    public function findItem(Buyable $item): ?CartItem
    {
        return $this->content()->firstWhere('buyable_id', $item->getBuyableIdentifier());
    }

    public function removeItem(Buyable $item): self
    {
        $item = $this->findItem($item);
        if ($item) {
            $this->remove($item->getKey());
        }

        return $this;
    }
}
