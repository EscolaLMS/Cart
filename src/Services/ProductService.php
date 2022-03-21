<?php

namespace EscolaLms\Cart\Services;

use EscolaLms\Cart\Contracts\Productable;
use EscolaLms\Cart\Dtos\ProductsSearchDto;
use EscolaLms\Cart\Enums\ProductType;
use EscolaLms\Cart\Events\ProductableAttached;
use EscolaLms\Cart\Events\ProductableDetached;
use EscolaLms\Cart\Events\ProductAttached;
use EscolaLms\Cart\Events\ProductDetached;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\ProductProductable;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Core\Models\User;
use EscolaLms\Tags\Models\Tag;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ProductService implements ProductServiceContract
{
    protected array $productables = [];
    protected array $productablesMorphs = [];

    public function registerProductableClass(string $productableClass): void
    {
        if (!is_a($productableClass, Model::class, true)) {
            throw new InvalidArgumentException(__('Productable class must represent Eloquent Model'));
        }
        if (!is_a($productableClass, Productable::class, true)) {
            throw new InvalidArgumentException(__('Class must implement Productable interface'));
        }
        if (!in_array($productableClass, $this->productables)) {
            $this->productables[] = $productableClass;
            $model = new $productableClass();
            assert($model instanceof Model);
            $this->productablesMorphs[$model->getMorphClass()] = $productableClass;
        }
    }

    public function isProductableClassRegistered(string $productableClass): bool
    {
        if (in_array($productableClass, $this->productables)) {
            return true;
        }
        $model = new $productableClass();
        assert($model instanceof Model);
        return array_key_exists($model->getMorphClass(), $this->productablesMorphs);
    }

    public function listRegisteredProductableClasses(): array
    {
        return $this->productables;
    }

    public function listRegisteredMorphClasses(): array
    {
        return $this->productablesMorphs;
    }

    public function listAllProductables(): Collection
    {
        $collection = Collection::empty();
        foreach ($this->listRegisteredProductableClasses() as $productableClass) {
            /** @var Model&Productable $model */
            $model = new $productableClass();
            $morphClass = $model->getMorphClass();
            $nameColumn = $model->getNameColumn() ?? ('"' . __('Unknown') . '"');
            $productables = $model::query()->getQuery()->select(
                'id AS productable_id',
                ($nameColumn . ' AS name'),
            )->get();
            $collection = $collection->merge($productables->map(function ($row) use ($productableClass, $morphClass) {
                $row->productable_type = $productableClass;
                $row->morph_class = $morphClass;
                return $row;
            }));
        }
        return $collection;
    }

    public function canonicalProductableClass(string $productableClass): ?string
    {
        if (in_array($productableClass, $this->productables)) {
            return $productableClass;
        }
        $model = new $productableClass();
        assert($model instanceof Model);
        if (array_key_exists($model->getMorphClass(), $this->productablesMorphs)) {
            return $this->productablesMorphs[$model->getMorphClass()];
        }
        throw new InvalidArgumentException(__('Unregistered Productable Class'));
    }

    public function findProductable(string $productableClass, $productableId): ?Productable
    {
        return $this->canonicalProductableClass($productableClass)::find($productableId);
    }

    public function findSingleProductForProductable(Productable $productable): ?Product
    {
        return Product::where('type', ProductType::SINGLE)->whereHasProductable($productable)->first();
    }

    public function searchAndPaginateProducts(ProductsSearchDto $searchDto, ?OrderDto $orderDto = null): LengthAwarePaginator
    {
        $query = Product::query();

        if (!is_null($searchDto->getName())) {
            $query->whereRaw('LOWER(name) LIKE (?)', ['%' . Str::lower($searchDto->getName()) . '%']);
        }

        if (!is_null($searchDto->getType())) {
            $query->where('type', '=', $searchDto->getType());
        }

        if (!is_null($searchDto->getFree())) {
            if ($searchDto->getFree()) {
                $query = $query->where('price', '=', 0);
            } else {
                $query = $query->where('price', '!=', 0);
            }
        }

        if (!is_null($searchDto->getPurchasable())) {
            $query = $query->where('purchasable', '=', $searchDto->getPurchasable());
        }

        if (!is_null($searchDto->getProductableType())) {
            $class = $searchDto->getProductableType();
            /** @var Model $model */
            $model = new $class();
            if (!is_null($searchDto->getProductableId())) {
                $query = $query->whereHasProductableClassAndId($model->getMorphClass(), $searchDto->getProductableId());
            } else {
                $query = $query->whereHasProductableClass($model->getMorphClass());
            }
        }

        if (!is_null($orderDto) && !is_null($orderDto->getOrder())) {
            $query = $query->orderBy($orderDto->getOrderBy(), $orderDto->getOrder());
        }

        return $query->paginate($searchDto->getPerPage() ?? 15);
    }

    public function productIsBuyableOrOwnedByUser(Product $product, User $user, bool $check_productables = false): bool
    {
        return $this->productIsBuyableByUser($product, $user, $check_productables) || $this->productIsOwnedByUser($product, $user, $check_productables);
    }

    public function productIsOwnedByUser(Product $product, User $user, bool $check_productables = false): bool
    {
        return $product->users()->where('users.id', $user->getKey())->exists()
            || ($check_productables && $this->productProductablesAllOwnedByUser($product, $user));
    }

    public function productProductablesAllOwnedByUser(Product $product, User $user): bool
    {
        return Product::where('products.id', $product->getKey())->whereDoesntHaveProductablesNotOwnedByUser($user)->exists();
    }

    public function productIsBuyableByUser(Product $product, User $user, bool $check_productables = false): bool
    {
        $limit_per_user = $product->limit_per_user ?? 1;
        $limit_total = $product->limit_total;
        return $product->purchasable
            && ($product->users()->where('users.id', $user->getKey())->count() < $limit_per_user)
            && (is_null($limit_total) || $product->users()->count() < $limit_total)
            && (!$check_productables || $this->productProductablesAllBuyableByUser($product, $user));
    }

    public function productProductablesAllBuyableByUser(Product $product, User $user): bool
    {
        return Product::where('products.id', $product->getKey())->whereDoesntHaveProductablesNotBuyableByUser($user)->exists();
    }

    /** 
     * Maps productable to JsonResource
     * Returns (almost) empty JsonResource if productable does not exist in database anymore
     * 
     * @see \EscolaLms\Cart\Http\Resources\ProductableGenericResource
     */
    public function mapProductProductableToJsonResource(ProductProductable $productProductable): JsonResource
    {
        $productable = $this->findProductable($productProductable->productable_type, $productProductable->productable_id);
        if ($productable) {
            return $productable->toJsonResourceForShop();
        }
        return new JsonResource([
            'id' => null,
            'morph_class' => null,
            'productable_id' => $productProductable->productable_id,
            'productable_type' => $productProductable->productable_type,
            'name' => null,
            'description' => null,
        ]);
    }

    public function create(array $data): Product
    {
        return $this->update(new Product(), $data);
    }

    public function update(Product $product, array $data): Product
    {
        $poster = $data['poster'] ?? null;
        unset($data['poster']);

        $productables = $data['productables'] ?? null;
        unset($data['productables']);

        $categories = $data['categories'] ?? null;
        unset($data['categories']);

        $tags = $data['tags'] ?? null;
        unset($data['tags']);

        if (($data['type'] ?? $product->type ?? ProductType::SINGLE)  === ProductType::SINGLE && !empty($productables)) {
            if (count($productables) > 1) {
                throw new Exception(__('Product with type SINGLE can contain only one single Productable'));
            }
            if (count($productables) === 1) {
                $singleProductable = $this->findSingleProductForProductable($this->findProductable($productables[0]['class'], $productables[0]['id']));
                if ($singleProductable) {
                    throw new Exception(
                        __(
                            'Only one Product with type SINGLE can exist for Productable :productable_type:::productable_id',
                            [
                                'productable_type' => $productables[0]['class'],
                                'productable_id' => $productables[0]['id']
                            ]
                        )
                    );
                }
            }
        }

        $product->fill($data);
        $product->save();
        $product->refresh();

        if ($poster instanceof UploadedFile) {
            $poster_url = $poster->storePublicly('products/' . $product->getKey() . '/posters');
            $product->poster_url = $poster_url;
            $product->save();
        }

        if (!is_null($productables)) {
            $this->saveProductProductables($product, $productables);
        }
        if ($product->type === ProductType::SINGLE && $product->productables->count() > 1) {
            $productables = $product->productables;
            $firstProductable = $productables->shift(1);
            $productables->each(fn (ProductProductable $productProductable) => $productProductable->delete());
        }

        if (!is_null($categories)) {
            $product->categories()->sync($categories);
        }

        if (!is_null($tags)) {
            $product->tags()->delete();

            $tags = array_map(function ($tag) {
                return ['title' => $tag];
            }, $tags);

            $product->tags()->createMany($tags);
        }

        return $product;
    }

    private function saveProductProductables(Product $product, array $productables)
    {
        if ($product->type === ProductType::SINGLE && count($productables) > 1) {
            throw new Exception(__('Product with type SINGLE can contain only one single Productable'));
        }

        $productablesCollection = (new Collection($productables))->keyBy('id');

        foreach ($product->productables as $currentProductable) {
            $existing = $productablesCollection->first(fn (array $productable) => $productable['id'] === $currentProductable->productable_id && $this->canonicalProductableClass($productable['class']) ===  $this->canonicalProductableClass($currentProductable->productable_type));
            if (is_null($existing)) {
                $currentProductable->delete();
            } else {
                $productablesCollection->forget($existing['id']);
            }
        }

        foreach ($productablesCollection as $newProductable) {
            $class = $this->canonicalProductableClass($newProductable['class']);
            $model = new $class();

            assert($model instanceof Model);

            $product->productables()->save(new ProductProductable([
                'productable_id' => $newProductable['id'],
                'productable_type' => $model->getMorphClass(),
            ]));
        }
    }

    public function attachProductToUser(Product $product, User $user): void
    {
        $product->users()->syncWithoutDetaching($user->getKey());
        foreach ($product->productables as $productProductable) {
            if ($this->isProductableClassRegistered($productProductable->productable_type)) {
                $productable = $this->findProductable($productProductable->productable_type, $productProductable->productable_id);
                $this->attachProductableToUser($productable, $user);
            }
        }
        event(new ProductAttached($product, $user));
    }

    public function detachProductFromUser(Product $product, User $user): void
    {
        $product->users()->detach($user->getKey());
        foreach ($product->productables as $productProductable) {
            if ($this->isProductableClassRegistered($productProductable->productable_type)) {
                $productable = $this->findProductable($productProductable->productable_type, $productProductable->productable_id);
                $this->detachProductableFromUser($productable, $user);
            }
        }
        event(new ProductDetached($product, $user));
    }

    public function attachProductableToUser(Productable $productable, User $user): void
    {
        assert($productable instanceof Model);
        $productable->attachToUser($user);
        event(new ProductableAttached($productable, $user));
    }

    public function detachProductableFromUser(Productable $productable, User $user): void
    {
        assert($productable instanceof Model);
        $productable->detachFromUser($user);
        event(new ProductableDetached($productable, $user));
    }
}
