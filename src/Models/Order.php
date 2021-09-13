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
 * @property int $id
 * @property int|null $user_id
 * @property int $status
 * @property int $total
 * @property int $subtotal
 * @property int $tax
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Cart\Models\Course[] $courses
 * @property-read int|null $courses_count
 * @property-read int $quantity
 * @property-read string $status_name
 * @property-read \Treestoneit\ShoppingCart\Models\CartItemCollection|\EscolaLms\Cart\Models\OrderItem[] $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Payments\Models\Payment[] $payments
 * @property-read int|null $payments_count
 * @property-read \EscolaLms\Cart\Models\User|null $user
 * @method static \EscolaLms\Cart\Database\Factories\OrderFactory factory(...$parameters)
 * @method static OrderQueryBuilder|Order newModelQuery()
 * @method static OrderQueryBuilder|Order newQuery()
 * @method static OrderQueryBuilder|Order query()
 * @method static OrderQueryBuilder|Order whereCreatedAt($value)
 * @method static OrderQueryBuilder|Order whereHasCourse(\EscolaLms\Courses\Models\Course $course)
 * @method static OrderQueryBuilder|Order whereHasCourseId(int $course_id)
 * @method static OrderQueryBuilder|Order whereHasCourseWithAuthor(\EscolaLms\Core\Models\User $author)
 * @method static OrderQueryBuilder|Order whereHasCourseWithAuthorId(int $author_id)
 * @method static OrderQueryBuilder|Order whereId($value)
 * @method static OrderQueryBuilder|Order whereStatus($value)
 * @method static OrderQueryBuilder|Order whereSubtotal($value)
 * @method static OrderQueryBuilder|Order whereTax($value)
 * @method static OrderQueryBuilder|Order whereTotal($value)
 * @method static OrderQueryBuilder|Order whereUpdatedAt($value)
 * @method static OrderQueryBuilder|Order whereUserId($value)
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

    public function newEloquentBuilder($query): OrderQueryBuilder
    {
        return new OrderQueryBuilder($query);
    }

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }
}
