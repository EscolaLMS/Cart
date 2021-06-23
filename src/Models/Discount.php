<?php

namespace EscolaLms\Cart\Models;

use EscolaLms\Cart\Enums\DiscountValueType;
use EscolaLms\Payments\Services\PaymentsService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\App;
use NumberFormatter;

/**
 * EscolaLms\Cart\Models\Discount
 *
 * @property int $id
 * @property string|null $name
 * @property string $code
 * @property \Illuminate\Support\Carbon $activated_at
 * @property \Illuminate\Support\Carbon|null $deactivated_at
 * @property int|null $limit_usage
 * @property int|null $limit_per_user
 * @property int $value_type
 * @property int $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read string $type
 * @property-read string $value_string
 * @method static \Illuminate\Database\Eloquent\Builder|Discount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount newQuery()
 * @method static \Illuminate\Database\Query\Builder|Discount onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount query()
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereActivatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereDeactivatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereLimitPerUser($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereLimitUsage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Discount whereValueType($value)
 * @method static \Illuminate\Database\Query\Builder|Discount withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Discount withoutTrashed()
 * @mixin \Eloquent
 */
class Discount extends Model
{
    use SoftDeletes, HasFactory;

    public $table = 'discounts';

    protected $dates = ['deleted_at', 'created_at', 'activated_at', 'deactivated_at'];

    public $fillable = [
        'code',
        'activated_at',
        'deactivated_at',
        'name',
        'limit_usage',
        'limit_per_user',
        'value_type',
        'value',
        'payment_methods',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'code' => 'string',
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
        'name' => 'string',
        'limit_per_user' => 'integer',
        'value_type' => 'integer',
        'value' => 'integer',
        'payment_methods' => 'array'
    ];

    public function getTypeAttribute(): string
    {
        return DiscountValueType::getName($this->value_type);
    }

    public function getValueStringAttribute(): string
    {
        $numberFormatter = NumberFormatter::create(App::currentLocale(), NumberFormatter::DEFAULT_STYLE);
        switch ($this->value_type) {
            case DiscountValueType::PERCENT:
                return $this->value . '%';
            case DiscountValueType::AMOUNT:
                return $numberFormatter->formatCurrency($this->value, app(PaymentsService::class)->getPaymentsConfig()->getDefaultCurrency());
            default:
                return $numberFormatter->format($this->value);
        }
    }
}
