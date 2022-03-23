<?php

namespace EscolaLms\Cart\Models;

use EscolaLms\Cart\Database\Factories\ProductFactory;
use EscolaLms\Cart\Models\Contracts\ProductInterface;
use EscolaLms\Cart\Models\Contracts\ProductTrait;
use EscolaLms\Cart\Models\User;
use EscolaLms\Cart\QueryBuilders\ProductModelQueryBuilder;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Core\Models\User as CoreUser;
use EscolaLms\Tags\Models\Tag;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * EscolaLms\Cart\Models\Product
 *
 * @OA\Schema (
 *      schema="Product",
 *      @OA\Property(
 *          property="id",
 *          description="id",
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="name",
 *          description="name",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="price",
 *          description="price",
 *          type="integer"
 *      )
 * )
 * 
 * @property int $id
 * @property string $name
 * @property string $type
 * @property int $price
 * @property int|null $price_old
 * @property int $tax_rate
 * @property int $extra_fees
 * @property bool $purchasable
 * @property string|null $teaser_url
 * @property string|null $description
 * @property string|null $poster_url
 * @property string|null $duration
 * @property int|null $limit_per_user
 * @property int|null $limit_total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Cart\Models\Category[] $categories
 * @property-read int|null $categories_count
 * @property-read bool $buyable_by_user
 * @property-read bool $owned_by_user
 * @property-read string|null $poster_absolute_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Cart\Models\ProductProductable[] $productables
 * @property-read int|null $productables_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|User[] $users
 * @property-read int|null $users_count
 * @method static \EscolaLms\Cart\Database\Factories\ProductFactory factory(...$parameters)
 * @method static ProductModelQueryBuilder|Product newModelQuery()
 * @method static ProductModelQueryBuilder|Product newQuery()
 * @method static ProductModelQueryBuilder|Product query()
 * @method static ProductModelQueryBuilder|Product whereCreatedAt($value)
 * @method static ProductModelQueryBuilder|Product whereDescription($value)
 * @method static ProductModelQueryBuilder|Product whereDoesntHaveProductablesNotBuyableByUser(?\EscolaLms\Core\Models\User $user = null)
 * @method static ProductModelQueryBuilder|Product whereDoesntHaveProductablesNotOwnedByUser(?\EscolaLms\Core\Models\User $user = null)
 * @method static ProductModelQueryBuilder|Product whereDuration($value)
 * @method static ProductModelQueryBuilder|Product whereExtraFees($value)
 * @method static ProductModelQueryBuilder|Product whereHasProductable(\Illuminate\Database\Eloquent\Model $productable)
 * @method static ProductModelQueryBuilder|Product whereHasProductableClass(string $productable_type)
 * @method static ProductModelQueryBuilder|Product whereHasProductableClassAndId(string $productable_type, int $productable_id)
 * @method static ProductModelQueryBuilder|Product whereId($value)
 * @method static ProductModelQueryBuilder|Product whereLimitPerUser($value)
 * @method static ProductModelQueryBuilder|Product whereLimitTotal($value)
 * @method static ProductModelQueryBuilder|Product whereName($value)
 * @method static ProductModelQueryBuilder|Product wherePosterUrl($value)
 * @method static ProductModelQueryBuilder|Product wherePrice($value)
 * @method static ProductModelQueryBuilder|Product wherePriceOld($value)
 * @method static ProductModelQueryBuilder|Product wherePurchasable($value)
 * @method static ProductModelQueryBuilder|Product whereTaxRate($value)
 * @method static ProductModelQueryBuilder|Product whereTeaserUrl($value)
 * @method static ProductModelQueryBuilder|Product whereType($value)
 * @method static ProductModelQueryBuilder|Product whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Product extends Model implements ProductInterface
{
    use ProductTrait;
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    protected $casts = [
        'purchasable' => 'bool'
    ];

    public function productables(): HasMany
    {
        return $this->hasMany(ProductProductable::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'products_users')->using(ProductUser::class);
    }

    public function tags(): MorphMany
    {
        return $this->morphMany(Tag::class, 'morphable');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'products_categories');
    }

    public function getBuyableDescription(): string
    {
        return $this->description ?? '';
    }

    public function getBuyablePrice(?array $options = null): int
    {
        return $this->price;
    }

    public function getExtraFees(?array $options = null): int
    {
        return $this->extra_fees ?? 0;
    }

    public function getTaxRate(): int
    {
        return $this->tax_rate ?? 0;
    }

    public function getBuyableByUserAttribute(?CoreUser $user = null): bool
    {
        return app(ProductServiceContract::class)->productIsBuyableByUser($this, $user ?? Auth::user());
    }

    public function getOwnedByUserAttribute(?CoreUser $user = null): bool
    {
        return app(ProductServiceContract::class)->productIsOwnedByUser($this, $user ?? Auth::user());
    }

    public function newEloquentBuilder($query): ProductModelQueryBuilder
    {
        return new ProductModelQueryBuilder($query);
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    public function getPosterAbsoluteUrlAttribute(): ?string
    {
        $path = $this->getRawOriginal('poster_url');
        if (!empty($path)) {
            return url(Storage::url($path));
        }
        return null;
    }
}
