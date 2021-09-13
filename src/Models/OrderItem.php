<?php

namespace EscolaLms\Cart\Models;

use Treestoneit\ShoppingCart\Models\CartItem;

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
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $buyable
 * @property-read mixed $description
 * @property-read int $extra_fees
 * @property-read string $identifier
 * @property-read int|null $price
 * @property-read int $subtotal
 * @property-read int $total
 * @method static \Treestoneit\ShoppingCart\Models\CartItemCollection|static[] all($columns = ['*'])
 * @method static \Treestoneit\ShoppingCart\Models\CartItemCollection|static[] get($columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereBuyableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereBuyableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OrderItem extends CartItem
{
}
