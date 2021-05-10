<?php

namespace EscolaSoft\Cart\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Treestoneit\ShoppingCart\Buyable;
use Treestoneit\ShoppingCart\BuyableTrait;

class Product extends Model implements Buyable
{
    use BuyableTrait, HasFactory;

    public function getBuyableDescription()
    {
        return 'Example product ' . $this->getKey();
    }

    public function getBuyablePrice()
    {
        return $this->price;
    }
}