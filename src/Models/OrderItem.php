<?php

namespace EscolaLms\Cart\Models;

use EscolaLms\Cart\QueryBuilders\OrderItemModelQueryBuilder;
use EscolaLms\Cart\Support\OrderItemCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
 * @property float $tax_rate
 * @property string|null $name
 * @property-read Model|\Eloquent $buyable
 * @property-read string|null $description
 * @property-read int $subtotal
 * @property-read int $tax
 * @property-read int $total
 * @property-read int $total_with_tax
 * @property-read \EscolaLms\Cart\Models\Order $order
 * @method static OrderItemCollection|static[] all($columns = ['*'])
 * @method static OrderItemCollection|static[] get($columns = ['*'])
 * @method static OrderItemModelQueryBuilder|OrderItem newModelQuery()
 * @method static OrderItemModelQueryBuilder|OrderItem newQuery()
 * @method static OrderItemModelQueryBuilder|OrderItem query()
 * @method static OrderItemModelQueryBuilder|OrderItem whereBuyableClassAndId(string $buyable_type, int $buyable_id)
 * @method static OrderItemModelQueryBuilder|OrderItem whereBuyableId($value)
 * @method static OrderItemModelQueryBuilder|OrderItem whereBuyableType($value)
 * @method static OrderItemModelQueryBuilder|OrderItem whereCreatedAt($value)
 * @method static OrderItemModelQueryBuilder|OrderItem whereExtraFees($value)
 * @method static OrderItemModelQueryBuilder|OrderItem whereHasProductable(\Illuminate\Database\Eloquent\Model $productable)
 * @method static OrderItemModelQueryBuilder|OrderItem whereHasProductableClassAndId(string $productable_type, int $productable_id)
 * @method static OrderItemModelQueryBuilder|OrderItem whereId($value)
 * @method static OrderItemModelQueryBuilder|OrderItem whereName($value)
 * @method static OrderItemModelQueryBuilder|OrderItem whereOptions($value)
 * @method static OrderItemModelQueryBuilder|OrderItem whereOrderId($value)
 * @method static OrderItemModelQueryBuilder|OrderItem wherePrice($value)
 * @method static OrderItemModelQueryBuilder|OrderItem whereProductId(int $product_id)
 * @method static OrderItemModelQueryBuilder|OrderItem whereQuantity($value)
 * @method static OrderItemModelQueryBuilder|OrderItem whereTaxRate($value)
 * @method static OrderItemModelQueryBuilder|OrderItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['options' => 'array'];

    public function buyable(): MorphTo
    {
        return $this->morphTo('buyable');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
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

    public function newEloquentBuilder($query): OrderItemModelQueryBuilder
    {
        return new OrderItemModelQueryBuilder($query);
    }
}
