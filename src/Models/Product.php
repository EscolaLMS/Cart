<?php

namespace EscolaLms\Cart\Models;

use EscolaLms\Cart\Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Treestoneit\ShoppingCart\Buyable;
use Treestoneit\ShoppingCart\BuyableTrait;

/**
 * EscolaLms\Cart\Models\Product
 *
 * @property int $id
 * @property int $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\EscolaLms\Cart\Models\ProductFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Product whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Product extends Model implements Buyable
{
    use BuyableTrait, HasFactory;

    public function getBuyableDescription()
    {
        return 'Example product ' . $this->getKey();
    }

    public function getBuyablePrice(): int
    {
        return $this->price;
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
