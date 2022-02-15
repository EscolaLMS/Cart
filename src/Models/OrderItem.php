<?php

namespace EscolaLms\Cart\Models;

use EscolaLms\Cart\Support\OrderItemCollection;
use Illuminate\Database\Eloquent\Model;

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
