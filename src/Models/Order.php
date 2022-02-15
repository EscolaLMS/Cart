<?php

namespace EscolaLms\Cart\Models;

use EscolaLms\Cart\Database\Factories\OrderFactory;
use EscolaLms\Cart\Enums\OrderStatus;
use EscolaLms\Cart\QueryBuilders\BuyableQueryBuilder;
use EscolaLms\Cart\QueryBuilders\OrderQueryBuilder;
use EscolaLms\Courses\Models\Course as BasicCourse;
use EscolaLms\Payments\Concerns\Payable;
use EscolaLms\Payments\Contracts\Billable;
use EscolaLms\Payments\Contracts\Payable as PayableContract;
use EscolaLms\Payments\Enums\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * EscolaLms\Cart\Models\Order
 *
 * @OA\Schema (
 *      schema="Order",
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="user_id",
 *          description="user_id",
 *          type="integer"
 *      )
 * )
 *
 */
class Order extends Model implements PayableContract
{
    use Payable;
    use HasFactory;

    protected $guarded = ['id'];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function courses(): HasManyThrough
    {
        return $this->hasManyThrough(Course::class, OrderItem::class, 'order_id', 'id', 'id', 'buyable_id')->whereIn('buyable_type', [
            Course::class,
            BasicCourse::class,
        ]);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getQuantityAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getStatusNameAttribute(): string
    {
        return OrderStatus::getName($this->status);
    }

    public function getBillable(): ?Billable
    {
        return $this->user;
    }

    public function getPaymentAmount(): int
    {
        return $this->total;
    }

    public function getPaymentCurrency(): ?Currency
    {
        return null; // will use default currency if left as null; can be changed during payment processing step
    }

    public function getPaymentDescription(): string
    {
        return '';
    }

    public function getPaymentOrderId(): ?string
    {
        return $this->getKey();
    }

    public function newEloquentBuilder($query): BuyableQueryBuilder
    {
        return new BuyableQueryBuilder($query);
    }

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }
}
