<?php

namespace EscolaSoft\Cart\Models;

use EscolaSoft\Cart\Enums\DiscountValueType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        switch ($this->value_type) {
            case DiscountValueType::PERCENT:
                return $this->value . '%';
            case DiscountValueType::AMOUNT:
                return '£' . $this->value;
            default:
                return $this->value;
        }
    }

}