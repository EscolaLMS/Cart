<?php


namespace EscolaSoft\Cart\Models;


use Treestoneit\ShoppingCart\Buyable;
use Treestoneit\ShoppingCart\BuyableTrait;

class Course extends \EscolaLms\Courses\Models\Course implements Buyable
{
    use BuyableTrait;

    public function getBuyablePrice(): float
    {
        return (float)$this->price;
    }
}