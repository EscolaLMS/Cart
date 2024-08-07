<?php

namespace EscolaLms\Cart\Models;

use EscolaLms\Cart\Contracts\Productable;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Throwable;

/**
 * EscolaLms\Cart\Models\ProductProductable
 *
 * @property int $id
 * @property int $product_id
 * @property string $productable_type
 * @property int $productable_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $quantity
 * @property-read Productable|null $canonical_productable
 * @property-read \EscolaLms\Cart\Models\Product|null $product
 * @property-read Model|\Eloquent $productable
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProductable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProductable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProductable query()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProductable whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProductable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProductable whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProductable whereProductableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProductable whereProductableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProductable whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ProductProductable whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ProductProductable extends Model
{
    protected $table = 'products_productables';

    protected $guarded = ['id'];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productable(): MorphTo
    {
        return $this->morphTo();
    }

    public function getCanonicalProductableAttribute(): ?Productable
    {
        $productable = $this->productable;
        if ($productable instanceof Productable) {
            return $productable;
        }
        try {
            return app(ProductServiceContract::class)->findProductable(get_class($productable), $productable->getKey());
        } catch (Throwable $ex) {
            // do nothing
        }
        return null;
    }
}
