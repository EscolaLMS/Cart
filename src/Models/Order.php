<?php

namespace EscolaLms\Cart\Models;

use EscolaLms\Cart\Database\Factories\OrderFactory;
use EscolaLms\Cart\Enums\OrderStatus;
use EscolaLms\Cart\QueryBuilders\OrderModelQueryBuilder;
use EscolaLms\Core\Models\User;
use EscolaLms\Payments\Concerns\Payable;
use EscolaLms\Payments\Contracts\Payable as PayableContract;
use EscolaLms\Payments\Enums\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
 * @property string|null $client_name
 * @property string|null $client_street
 * @property string|null $client_postal
 * @property string|null $client_city
 * @property string|null $client_country
 * @property string|null $client_company
 * @property string|null $client_taxid
 * @property string|null $client_email
 * @property string|null $client_street_number
 * @property-read int $quantity
 * @property-read string $status_name
 * @property-read \EscolaLms\Cart\Support\OrderItemCollection|\EscolaLms\Cart\Models\OrderItem[] $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Payments\Models\Payment[] $payments
 * @property-read int|null $payments_count
 * @property-read User|null $user
 * @method static \EscolaLms\Cart\Database\Factories\OrderFactory factory(...$parameters)
 * @method static OrderModelQueryBuilder|Order newModelQuery()
 * @method static OrderModelQueryBuilder|Order newQuery()
 * @method static OrderModelQueryBuilder|Order query()
 * @method static OrderModelQueryBuilder|Order whereClientCity($value)
 * @method static OrderModelQueryBuilder|Order whereClientCompany($value)
 * @method static OrderModelQueryBuilder|Order whereClientCountry($value)
 * @method static OrderModelQueryBuilder|Order whereClientEmail($value)
 * @method static OrderModelQueryBuilder|Order whereClientName($value)
 * @method static OrderModelQueryBuilder|Order whereClientPostal($value)
 * @method static OrderModelQueryBuilder|Order whereClientStreet($value)
 * @method static OrderModelQueryBuilder|Order whereClientStreetNumber($value)
 * @method static OrderModelQueryBuilder|Order whereClientTaxid($value)
 * @method static OrderModelQueryBuilder|Order whereCreatedAt($value)
 * @method static OrderModelQueryBuilder|Order whereHasBuyable(string $buyable_type, int $buyable_id)
 * @method static OrderModelQueryBuilder|Order whereHasProduct(int $product_id)
 * @method static OrderModelQueryBuilder|Order whereHasProductable(\Illuminate\Database\Eloquent\Model $productable)
 * @method static OrderModelQueryBuilder|Order whereHasProductableClass(string $productable_type)
 * @method static OrderModelQueryBuilder|Order whereHasProductableClassAndId(string $productable_type, int $productable_id)
 * @method static OrderModelQueryBuilder|Order whereId($value)
 * @method static OrderModelQueryBuilder|Order whereStatus($value)
 * @method static OrderModelQueryBuilder|Order whereSubtotal($value)
 * @method static OrderModelQueryBuilder|Order whereTax($value)
 * @method static OrderModelQueryBuilder|Order whereTotal($value)
 * @method static OrderModelQueryBuilder|Order whereUpdatedAt($value)
 * @method static OrderModelQueryBuilder|Order whereUserId($value)
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

    public function getUser(): ?User
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

    public function newEloquentBuilder($query): OrderModelQueryBuilder
    {
        return new OrderModelQueryBuilder($query);
    }

    protected static function newFactory(): OrderFactory
    {
        return OrderFactory::new();
    }
}
