<?php

namespace EscolaLms\Cart\Models;

use EscolaLms\Cart\Support\OrderItemCollection;
use Illuminate\Database\Eloquent\Model;

/**
 * EscolaLms\Cart\Models\OrderItem
 *
 * @property int $id
 * @property int $order_id
 * @property string $buyable_type
 * @property int $buyable_id
 * @property int $quantity
 * @property array|null $options
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $price
 * @property int $extra_fees
 * @property int $tax_rate
 * @property-read Model|\Eloquent $buyable
 * @property-read string|null $description
 * @property-read int $subtotal
 * @property-read int $tax
 * @property-read int $total
 * @property-read int $total_with_tax
 * @method static OrderItemCollection|static[] all($columns = ['*'])
 * @method static OrderItemCollection|static[] get($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereBuyableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereBuyableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereExtraFees($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['options' => 'array'];

    public function buyable()
    {
        return $this->morphTo('buyable');
    }

    public function newCollection(array $models = [])
    {
        return new OrderItemCollection($models);
    }

    public function getDescriptionAttribute(): ?string
    {
        return optional($this->buyable)->getBuyableDescription();
    }

    public function getPriceAttribute(): int
    {
        return $this->getRawOriginal('price') ?? optional($this->buyable)->getBuyablePrice() ?? 0;
    }

    public function getSubtotalAttribute(): int
    {
        return $this->getPriceAttribute() * $this->quantity;
    }

    public function getTotalAttribute(): int
    {
        return $this->getSubtotalAttribute() + $this->extra_fees;
    }

    public function getTaxAttribute(): int
    {
        return (int) round($this->getSubtotalAttribute() * ($this->tax_rate / 100), 0);
    }

    public function getTotalWithTaxAttribute(): int
    {
        return $this->total + $this->tax;
    }
}
