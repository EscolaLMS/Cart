<?php

namespace EscolaLms\Cart\Models;

use EscolaLms\Cart\Contracts\Productable;
use EscolaLms\Cart\Database\Factories\ProductFactory;
use EscolaLms\Cart\Enums\ProductType;
use EscolaLms\Cart\Models\Contracts\ProductInterface;
use EscolaLms\Cart\Models\Contracts\ProductTrait;
use EscolaLms\Cart\Models\User;
use EscolaLms\Cart\QueryBuilders\ProductModelQueryBuilder;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Core\Models\User as CoreUser;
use EscolaLms\Tags\Models\Tag;
use Illuminate\Database\Eloquent\Collection;
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
 * @property int $id
 * @property string $name
 * @property string $type
 * @property int $price
 * @property int|null $price_old
 * @property float $tax_rate
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
 * @property-read Collection|\EscolaLms\Cart\Models\Category[] $categories
 * @property-read int|null $categories_count
 * @property-read Collection $authors
 * @property-read bool $buyable_by_user
 * @property-read int $calculated_duration
 * @property-read bool $owned_by_user
 * @property-read int $owned_by_user_quantity
 * @property-read string|null $poster_absolute_url
 * @property-read Collection|\EscolaLms\Cart\Models\ProductProductable[] $productables
 * @property-read int|null $productables_count
 * @property-read Collection|Product[] $relatedProducts
 * @property-read int|null $related_products_count
 * @property-read Collection|Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read Collection|User[] $users
 * @property-read int|null $users_count
 * @property-read int|null $available_quantity
 * @property-read int $sold_quantity
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
 * @method static ProductModelQueryBuilder|Product whereHasUser(\EscolaLms\Core\Models\User $user)
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
 * @property string|null $subscription_period
 * @property int|null $subscription_duration
 * @property bool $recursive
 * @property bool|null $has_trial
 * @property string|null $trial_period
 * @property int|null $trial_duration
 * @property-read string|null $poster_url_or_productable_thumbnail
 * @method static ProductModelQueryBuilder|Product whereHasTrial($value)
 * @method static ProductModelQueryBuilder|Product whereRecursive($value)
 * @method static ProductModelQueryBuilder|Product whereSubscriptionDuration($value)
 * @method static ProductModelQueryBuilder|Product whereSubscriptionPeriod($value)
 * @method static ProductModelQueryBuilder|Product whereTrialDuration($value)
 * @method static ProductModelQueryBuilder|Product whereTrialPeriod($value)
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
        'purchasable' => 'bool',
        'has_trial' => 'bool',
    ];

    public function productables(): HasMany
    {
        return $this->hasMany(ProductProductable::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'products_users')->using(ProductUser::class)->withPivot('quantity');
    }

    public function tags(): MorphMany
    {
        return $this->morphMany(Tag::class, 'morphable');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'products_categories');
    }

    public function relatedProducts()
    {
        return $this->belongsToMany(Product::class, 'related_product', 'product_id', 'related_product_id');
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

    public function getTaxRate(): float
    {
        return $this->tax_rate ?? 0;
    }

    public function getBuyableByUserAttribute(?CoreUser $user = null, ?int $quantity = null): bool
    {
        return app(ProductServiceContract::class)->productIsBuyableByUser($this, $user ?? Auth::user(), false, $quantity);
    }

    public function getOwnedByUserAttribute(?CoreUser $user = null): bool
    {
        return app(ProductServiceContract::class)->productIsOwnedByUser($this, $user ?? Auth::user());
    }

    public function getOwnedByUserQuantityAttribute(?CoreUser $user = null): int
    {
        $user = $user ?? Auth::user();
        return optional(optional($this->users->where('id', '=', $user->getKey())->first())->pivot)->quantity ?? 0;
    }

    public function newEloquentBuilder($query): ProductModelQueryBuilder
    {
        return new ProductModelQueryBuilder($query);
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }

    public function getPosterUrlOrProductableThumbnailAttribute(): ?string
    {
        $path = $this->getRawOriginal('poster_url');

        if (!empty($path)) {
            return $path;
        }

        if ($this->type === ProductType::SINGLE) {
            /** @var ProductProductable $first */
            $first = $this->productables->first();
            if ($first) {
                $productable = $first->getCanonicalProductableAttribute();
                if ($productable) {
                    return $productable->getThumbnail();
                }
            }
        }

        return null;
    }

    public function getPosterAbsoluteUrlAttribute(): ?string
    {
        $path = $this->getPosterUrlOrProductableThumbnailAttribute();
        if (!empty($path)) {
            return preg_match('/^(http|https):.*$/', $path) ? $path : url(Storage::url($path));
        }
        return null;
    }

    public function getAuthorsAttribute(): Collection
    {
        $authors = new Collection();
        foreach ($this->productables as $productable) {
            $productableModel = $productable->getCanonicalProductableAttribute();
            if ($productableModel instanceof Productable) {
                $authors = $authors->merge($productableModel->getProductableAuthors());
            }
        }
        return $authors->unique('id');
    }

    public function getCalculatedDurationAttribute(): int
    {
        $duration = 0;
        foreach ($this->productables as $productable) {
            $productableModel = $productable->productable;
            if ($productableModel instanceof Productable) {
                $duration += $productableModel->getProductableDuration();
            }
        }
        return $duration;
    }

    public function getAvailableQuantityAttribute(): ?int
    {
        return is_null($this->limit_total) ? null : $this->limit_total - $this->sold_quantity;
    }

    public function getSoldQuantityAttribute(): int
    {
        return $this->users()->sum('quantity');
    }

    public function getGrossPrice(): int
    {
        return $this->getBuyablePrice() + $this->getTax();
    }

    public function getTax(): int
    {
        return (int) round($this->getBuyablePrice() * $this->getTaxRate() / 100, 0);
    }
}
