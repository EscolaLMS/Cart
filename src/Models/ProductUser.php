<?php

namespace EscolaLms\Cart\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * EscolaLms\Cart\Models\ProductUser
 *
 * @property int $id
 * @property int $product_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $quantity
 * @property-read \EscolaLms\Cart\Models\Product|null $product
 * @property-read \EscolaLms\Cart\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|ProductUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductUser query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductUser whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductUser whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductUser whereUserId($value)
 * @property string|null $end_date
 * @property string|null $status
 * @method static \Illuminate\Database\Eloquent\Builder|ProductUser whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductUser whereStatus($value)
 * @mixin \Eloquent
 */
class ProductUser extends Pivot
{
    protected $table = 'products_users';

    protected $casts = [
        'end_date' => 'datetime'
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
