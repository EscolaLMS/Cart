<?php

namespace EscolaLms\Cart\Models;

use EscolaLms\Cart\Database\Factories\OrderFactory;
use EscolaLms\Cart\Enums\OrderStatus;
use EscolaLms\Cart\QueryBuilders\BuyableQueryBuilder;
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
 * @property int $id
 * @property int|null $user_id
 * @property int $status
 * @property int $total
 * @property int $subtotal
 * @property int $tax
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read int $quantity
 * @property-read string $status_name
 * @property-read \EscolaLms\Cart\Support\OrderItemCollection|\EscolaLms\Cart\Models\OrderItem[] $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Payments\Models\Payment[] $payments
 * @property-read int|null $payments_count
 * @property-read \EscolaLms\Cart\Models\User|null $user
 * @method static \EscolaLms\Cart\Database\Factories\OrderFactory factory(...$parameters)
 * @method static BuyableQueryBuilder|Order newModelQuery()
 * @method static BuyableQueryBuilder|Order newQuery()
 * @method static BuyableQueryBuilder|Order query()
 * @method static BuyableQueryBuilder|Order whereCreatedAt($value)
 * @method static BuyableQueryBuilder|Order whereHasBuyableId($buyable_id)
 * @method static BuyableQueryBuilder|Order whereHasBuyableType(string $buyable_type)
 * @method static BuyableQueryBuilder|Order whereId($value)
 * @method static BuyableQueryBuilder|Order whereStatus($value)
 * @method static BuyableQueryBuilder|Order whereSubtotal($value)
 * @method static BuyableQueryBuilder|Order whereTax($value)
 * @method static BuyableQueryBuilder|Order whereTotal($value)
 * @method static BuyableQueryBuilder|Order whereUpdatedAt($value)
 * @method static BuyableQueryBuilder|Order whereUserId($value)
 * @mixin \Eloquent
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
