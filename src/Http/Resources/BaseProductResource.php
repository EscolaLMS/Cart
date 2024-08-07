<?php

namespace EscolaLms\Cart\Http\Resources;

use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\ProductProductable;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Categories\Http\Resources\CategoryResource;
use EscolaLms\Core\Models\User;
use EscolaLms\Tags\Models\Tag;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class BaseProductResource extends JsonResource
{
    public function __construct(Product $product)
    {
        parent::__construct($product);
    }

    protected function getProduct(): Product
    {
        return $this->resource;
    }

    public function toArray($request): array
    {
        /** @var User|null $user */
        // @phpstan-ignore-next-line
        $user = $request ? $request->user() : Auth::user();
        $data = [
            'id' => $this->getProduct()->getKey(),
            'type' => $this->getProduct()->type,
            'name' => $this->getProduct()->name,
            'description' => $this->getProduct()->description,
            'price' => $this->getProduct()->getBuyablePrice(),
            'price_old' => $this->getProduct()->price_old,
            'tax_rate' => $this->getProduct()->getTaxRate(),
            'extra_fees' => $this->getProduct()->getExtraFees(),
            'purchasable' => $this->getProduct()->purchasable,
            'duration' => $this->getProduct()->duration,
            'calculated_duration' => $this->getProduct()->getCalculatedDurationAttribute(),
            'limit_per_user' => $this->getProduct()->limit_per_user,
            'limit_total' => $this->getProduct()->limit_total,
            'productables' => $this->getProduct()->productables->map(fn (ProductProductable $productProductable) => app(ProductServiceContract::class)->mapProductProductableToJsonResource($productProductable)->toArray($request))->toArray(),
            'teaser_url' => $this->getProduct()->teaser_url,
            'poster_path' => $this->getProduct()->getPosterUrlOrProductableThumbnailAttribute(),
            'poster_url' => $this->getProduct()->poster_absolute_url,
            'buyable' => $user && $this->getProduct()->getBuyableByUserAttribute($user),
            'owned' => $user && $this->getProduct()->getOwnedByUserAttribute($user),
            'owned_quantity' => $user ? $this->getProduct()->getOwnedByUserQuantityAttribute($user) : 0,
            'categories' => CategoryResource::collection($this->getProduct()->categories)->toArray($request),
            'tags' => $this->getProduct()->tags->map(fn (Tag $tag) => $tag->title)->toArray(),
            'updated_at' => $this->getProduct()->updated_at,
            'authors' => AuthorResource::collection($this->getProduct()->getAuthorsAttribute())->toArray($request),
            'available_quantity' => $this->getProduct()->available_quantity,
            'sold_quantity' => $this->getProduct()->sold_quantity,
            'gross_price' => $this->getProduct()->getGrossPrice(),
            'subscription_period' => $this->getProduct()->subscription_period,
            'subscription_duration' => $this->getProduct()->subscription_duration,
            'recursive' => $this->getProduct()->recursive,
            'has_trial' => $this->getProduct()->has_trial,
            'trial_period' => $this->getProduct()->trial_period,
            'trial_duration' => $this->getProduct()->trial_duration,
            'fields' => $this->getProduct()->fields,
        ];
        return $data;
    }
}
