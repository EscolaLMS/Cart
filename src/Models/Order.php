<?php

namespace EscolaLms\Cart\Models;

use EscolaLms\Cart\CartServiceProvider;
use EscolaLms\Cart\Database\Factories\OrderFactory;
use EscolaLms\Cart\Enums\OrderStatus;
use EscolaLms\Cart\QueryBuilders\OrderQueryBuilder;
use EscolaLms\Courses\Models\Course as BasicCourse;
use EscolaLms\Payments\Concerns\Payable;
use EscolaLms\Payments\Contracts\Billable;
use EscolaLms\Payments\Contracts\Payable as PayableContract;
use EscolaLms\Payments\Enums\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Treestoneit\ShoppingCart\Models\Cart;

/**
 * EscolaLms\Cart\Models\Order
 *
 * @OA\Schema(
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
 * @property int $id
 * @property int|null $user_id
 * @property int $status
 * @property float $total
 * @property float $subtotal
 * @property float $tax
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int $quantity
 * @property-read string $status_name
 * @property-read \Treestoneit\ShoppingCart\Models\CartItemCollection|\EscolaLms\Cart\Models\OrderItem[] $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Payments\Models\Payment[] $payments
 * @property-read int|null $payments_count
 * @property-read \EscolaLms\Cart\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUserId($value)
 * @mixin \Eloquent
 */
class Order extends Cart implements PayableContract
{
    use Payable;
    use HasFactory;

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
        return $this->belongsTo(CartServiceProvider::getCartOwnerModel());
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
        return (int) ($this->total * 100); // because someone decided to use floats instead of integers representing minor currency unit
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

    public function newEloquentBuilder($query): OrderQueryBuilder
    {
        return new OrderQueryBuilder($query);
    }

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }
}
