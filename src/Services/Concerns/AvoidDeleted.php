<?php


namespace EscolaSoft\Cart\Services\Concerns;


trait AvoidDeleted
{
    private function avoidDeletedItems(): void
    {
        foreach ($this->content() as $item) {
            if (is_null($item->buyable)) {
                $this->remove($item->getKey());
            }
        }
    }
}