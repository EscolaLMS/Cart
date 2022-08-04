<?php

namespace EscolaLms\Cart\Models;

use EscolaLms\Cart\Models\Contracts\Base\Taxable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Config;
use Treestoneit\ShoppingCart\Models\CartItem as BaseCartItem;

/**
 * EscolaLms\Cart\Models\CartItem
 *
 * @property int $id
 * @property int $cart_id
 * @property string $buyable_type
 * @property int $buyable_id
 * @property int $quantity
 * @property array|null $options
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $buyable
 * @property-read \EscolaLms\Cart\Models\Cart $cart
 * @property-read mixed $description
 * @property-read float|int $extra_fees
 * @property-read string $identifier
 * @property-read float|null $price
 * @property-read mixed $subtotal
 * @property-read int $tax
 * @property-read int $tax_rate
 * @property-read mixed $total
 * @property-read int $total_with_tax
 * @method static \Treestoneit\ShoppingCart\Models\CartItemCollection|static[] all($columns = ['*'])
 * @method static \Treestoneit\ShoppingCart\Models\CartItemCollection|static[] get($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereBuyableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereBuyableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereCartId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CartItem extends BaseCartItem
{
    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function getTaxRateAttribute(?float $rate = null): float
    {
        if (!$rate && Config::get('shopping-cart.tax.mode') == 'flat') {
            $rate = Config::get('shopping-cart.tax.rate');
        }
        if (!$rate) {
            $rate = $this->buyable instanceof Taxable
                ? $this->buyable->getTaxRate()
                : 0;
        }
        return $rate;
    }

    public function getTaxAttribute(?float $rate = null): int
    {
        return (int) round($this->getSubtotalAttribute() * ($this->getTaxRateAttribute($rate) / 100), 0);
    }

    public function getSubtotalAttribute()
    {
        return (int) round(parent::getSubtotalAttribute(), 0);
    }

    public function getTotalAttribute()
    {
        return (int) round(parent::getTotalAttribute(), 0);
    }

    public function getTotalWithTaxAttribute(?int $rate = null): int
    {
        return $this->total + $this->getTaxAttribute($rate);
    }
}
