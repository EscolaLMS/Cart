<?php

namespace EscolaLms\Cart\Services;

use EscolaLms\Cart\Contracts\Productable;
use EscolaLms\Cart\Dtos\ProductsSearchDto;
use EscolaLms\Cart\Enums\ConstantEnum;
use EscolaLms\Cart\Enums\ProductType;
use EscolaLms\Cart\Events\ProductableAttached;
use EscolaLms\Cart\Events\ProductableDetached;
use EscolaLms\Cart\Events\ProductAttached;
use EscolaLms\Cart\Events\ProductDetached;
use EscolaLms\Cart\Models\Cart;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\ProductProductable;
use EscolaLms\Cart\Models\ProductUser;
use EscolaLms\Cart\Services\Contracts\ProductServiceContract;
use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Core\Models\User;
use EscolaLms\Files\Helpers\FileHelper;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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
            $table = $model->getTable();
            $morphClass = $model->getMorphClass();

            $nameColumn = $model->getNameColumn() ? ($table . '.' . $model->getNameColumn()) : ('"' . __('Unknown') . '"');
            $productables = $model::query()->getQuery()->select(
                $table . '.id AS productable_id',
                ($nameColumn . ' AS name'),
                'products.id as single_product_id',
            )->leftJoin(
                'products_productables',
                fn (JoinClause $join) => $join
                    ->on('products_productables.productable_id', '=', $table . '.id')
                    ->where('products_productables.productable_type', '=', $morphClass)
            )->leftJoin(
                'products',
                fn (JoinClause $join) => $join
                    ->on('products.id', '=', 'products_productables.product_id')
                    ->where('products.type', '=', ProductType::SINGLE)
            )->distinct()->get();

            $resultsCollection = $productables->map(function ($row) use ($productableClass, $morphClass) {
                $row->productable_type = $productableClass;
                $row->morph_class = $morphClass;
                return $row;
            });

            $uniqueCollection = [];
            foreach ($resultsCollection as $row) {
                $existingRow = isset($uniqueCollection[$row->productable_id]);
                if (false === $existingRow) {
                    $uniqueCollection[$row->productable_id] = $row;
                } elseif ($row->single_product_id) {
                    $uniqueCollection[$row->productable_id]->single_product_id = $row->single_product_id;
                }
            }

            $collection = $collection->merge($uniqueCollection);
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
        throw new InvalidArgumentException(__('Unregistered Productable Class: :class', ['class' => $productableClass]));
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

        if (!is_null($searchDto->getTags())) {
            $query->whereHas('tags', fn (Builder $query) => $query->whereIn('title', $searchDto->getTags()));
        }

        if (!is_null($orderDto) && !is_null($orderDto->getOrder())) {
            $query = $query->orderBy($orderDto->getOrderBy(), $orderDto->getOrder());
        }

        return $query->paginate($searchDto->getPerPage() ?? 15);
    }

    public function productIsPurchasableOrOwnedByUser(Product $product, User $user): bool
    {
        return $product->purchasable || $this->productIsOwnedByUser($product, $user);
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

    public function productIsBuyableByUser(Product $product, User $user, bool $check_productables = false, ?int $quantity = null): bool
    {
        $limit_per_user = $product->limit_per_user;
        $limit_total = $product->limit_total;

        $is_purchasable = $product->purchasable;

        if (is_null($quantity)) {
            $quantity = $this->productQuantityInCart($user, $product) + 1;
        }

        $is_under_limit_per_user = is_null($limit_per_user) || ($product->getOwnedByUserQuantityAttribute($user) + $quantity <= $limit_per_user);
        $is_under_limit_total = is_null($limit_total) || (($product->users_count ?? $product->users()->sum('quantity')) + $quantity <= $limit_total);
        $is_productables_buyable = !$check_productables || $this->productProductablesAllBuyableByUser($product, $user);
        Log::debug(__('Checking if product is buyable'), [
            'user' => $user->getKey(),
            'product' => [
                'id' => $product->getKey(),
                'name' => $product->name,
                'limit_per_user' => $limit_per_user,
                'limit_total' => $limit_total,
            ],
            'owned_quantity' => !is_null($limit_per_user) ? $product->getOwnedByUserQuantityAttribute($user) : 'not counted (unlimited product)',
            'purchasable' => $is_purchasable,
            'limit_per_user' => $is_under_limit_per_user,
            'limit_total' => $is_under_limit_total,
            'productables' => $is_productables_buyable,
        ]);
        return $is_purchasable && $is_under_limit_per_user && $is_under_limit_total && $is_productables_buyable;
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
            return $productable->toJsonResourceForShop($productProductable);
        }
        return new JsonResource([
            'id' => null,
            'morph_class' => null,
            'productable_id' => $productProductable->productable_id,
            'productable_type' => $productProductable->productable_type,
            'quantity' => $productProductable->quantity,
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
        if (ProductType::isSubscriptionType($product->type) && !ProductType::isSubscriptionType($data['type'])) {
            throw new Exception(__('Product with subscription type cannot have type changed'));
        }

        if (
            ProductType::isSubscriptionType($product->type) &&
            (
                $product->subscription_period !== $data['subscription_period']
                || $product->subscription_duration !== $data['subscription_duration']
                || $product->recursive !== $data['recursive']
            )
        ) {
            throw new Exception(__('Subscription fields cannot be edited'));
        }

        if (
            ($product->type === ProductType::SUBSCRIPTION_ALL_IN || (isset($data['type']) && $data['type'] === ProductType::SUBSCRIPTION_ALL_IN))
            && !empty($data['productables'])
        ) {
            throw new Exception(__('Products cannot be assigned to all-in subscription type.'));
        }

        $relatedProducts = $data['related_products'] ?? null;
        unset($data['related_products']);

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
                if ($singleProductable && $singleProductable->getKey() !== $product->getKey()) {
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

        if ($poster) {
            $product->poster_url = FileHelper::getFilePath($poster, ConstantEnum::DIRECTORY . "/{$product->getKey()}/posters");
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

        if (!is_null($relatedProducts)) {
            $product->relatedProducts()->sync($relatedProducts);
        }

        return $product;
    }

    private function saveProductProductables(Product $product, array $productables)
    {
        if ($product->type === ProductType::SINGLE && count($productables) > 1) {
            throw new Exception(__('Product with type SINGLE can contain only one single Productable'));
        }

        $productablesCollection = (new Collection($productables))->keyBy('id');

        foreach ($product->productables as $existingProductable) {
            $productableInUpdateData = $productablesCollection->first(
                fn (array $productable) => $productable['id'] === $existingProductable->productable_id
                    && $this->canonicalProductableClass($productable['class']) === $this->canonicalProductableClass($existingProductable->productable_type)
            );

            if (is_null($productableInUpdateData)) {
                $existingProductable->delete();
            } else {
                $existingProductable->quantity = ($product->type === ProductType::SINGLE) ? 1 : ($productableInUpdateData['quantity'] ?? 1);
                if ($existingProductable->isDirty('quantity')) {
                    $existingProductable->save();
                }
                $productablesCollection->forget($productableInUpdateData['id']);
            }
        }

        foreach ($productablesCollection as $newProductable) {
            $class = $this->canonicalProductableClass($newProductable['class']);
            $model = new $class();

            assert($model instanceof Model);

            $product->productables()->save(new ProductProductable([
                'productable_id' => $newProductable['id'],
                'productable_type' => $model->getMorphClass(),
                'quantity' => $product->type === ProductType::SINGLE ? 1 : ($newProductable['quantity'] ?? 1),
            ]));
        }

        $product->load('productables');
    }

    public function attachProductToUser(Product $product, User $user, int $quantity = 1): void
    {
        Log::debug(__('Attaching product to user'), [
            'product' => [
                'id' => $product->getKey(),
                'name' => $product->name,
                'limit_per_user' => $product->limit_per_user,
            ],
            'user' => [
                'id' => $user->getKey(),
                'email' => $user->email,
            ],
        ]);

        if (!is_null($product->limit_per_user) && $product->limit_per_user < $quantity) {
            $quantity = $product->limit_per_user;
        }

        if ($quantity === 0) {
            return;
        }

        $productUserPivot = ProductUser::query()->firstOrCreate(['user_id' => $user->getKey(), 'product_id' => $product->getKey()], ['quantity' => $quantity]);

        if (!$productUserPivot->wasRecentlyCreated && !is_null($product->limit_per_user)) {
            if ($product->limit_per_user < ($productUserPivot->quantity + $quantity)) {
                $quantity = $product->limit_per_user - $productUserPivot->quantity;
            }
            $productUserPivot->quantity += $quantity;
            $productUserPivot->save();
        }

        if ($quantity === 0) {
            return;
        }

        foreach ($product->productables as $productProductable) {
            Log::debug(__('Checking if productable can be processed'));
            if ($this->isProductableClassRegistered($productProductable->productable_type)) {
                $productable = $this->findProductable($productProductable->productable_type, $productProductable->productable_id);
                if (is_null($productable)) {
                    Log::debug([
                        'product' => [
                            'id' => $product->getKey(),
                            'name' => $product->name,
                            'extended_model' => $productProductable->productable_type,
                            'extended_model_id' => $productProductable->productable_id,
                        ],
                        'message' => __('Attached product is not exists')
                    ]);
                    throw new Exception(__('Attached product is not exists'));
                }
                $this->attachProductableToUser($productable, $user, $productProductable->quantity * $quantity, $product);
            }
        }
        event(new ProductAttached($product, $user, $quantity));
    }

    public function detachProductFromUser(Product $product, User $user, int $quantity = 1): void
    {
        $productUserPivot = ProductUser::where(['user_id' => $user->getKey(), 'product_id' => $product->getKey()])->first();

        if (!$productUserPivot) {
            return;
        }

        $new_quantity = $productUserPivot->quantity - $quantity;
        if ($new_quantity < 0) {
            $new_quantity = 0;
            $quantity = $productUserPivot->quantity;
        }

        if ($quantity === 0) {
            return;
        }

        if ($new_quantity === 0) {
            $productUserPivot->delete();
        } else {
            $productUserPivot->quantity = $new_quantity;
            $productUserPivot->save();
        }

        foreach ($product->productables as $productProductable) {
            if ($this->isProductableClassRegistered($productProductable->productable_type)) {
                $productable = $this->findProductable($productProductable->productable_type, $productProductable->productable_id);
                $this->detachProductableFromUser($productable, $user, $productProductable->quantity * $quantity, $product);
            }
        }

        event(new ProductDetached($product, $user, $quantity));
    }

    public function attachProductableToUser(Productable $productable, User $user, int $quantity = 1, ?Product $product = null): void
    {
        Log::debug(__('Attaching productable to user'), [
            'productable' => [
                'id' => $productable->getKey(),
                'name' => $productable->getName(),
            ],
            'user' => [
                'id' => $user->getKey(),
                'email' => $user->email,
            ],
            'quantity' => $quantity,
        ]);
        assert($productable instanceof Model);
        try {
            $productable->attachToUser($user, $quantity, $product);
            Log::debug(
                'Productable (should be) attached to user.',
                [
                    'productable_owned' => $productable->getOwnedByUserAttribute($user),
                    'productable_owned_through_product' => $this->productableIsOwnedByUserThroughProduct($productable, $user),
                ]
            );
        } catch (Exception $ex) {
            Log::error(__('Failed to attach productable to user'), [
                'exception' => $ex->getMessage(),
            ]);
        }
        event(new ProductableAttached($productable, $user, $quantity));
    }

    public function detachProductableFromUser(Productable $productable, User $user, int $quantity = 1, ?Product $product = null): void
    {
        assert($productable instanceof Model);
        $productable->detachFromUser($user, $quantity, $product);
        event(new ProductableDetached($productable, $user, $quantity));
    }

    public function productableIsOwnedByUserThroughProduct(Productable $productable, User $user): bool
    {
        return Product::query()->whereHasProductable($productable)->whereHasUser($user)->exists();
    }

    public function canDetachProductableFromUser(Productable $productable, User $user): bool
    {
        return !$this->productableIsOwnedByUserThroughProduct($productable, $user);
    }

    private function productQuantityInCart(User $user, Product $product): int
    {
        $cart = Cart::where('user_id', $user->getAuthIdentifier())->latest()->firstOrCreate([
            'user_id' => $user->getAuthIdentifier(),
        ]);
        if (!is_null($cart)) {
            $cartItem = $cart
                ->items()
                ->whereHas('buyable', fn (Builder $query) => $query->where('products.id', '=', $product->getKey()))
                ->first();
            return !is_null($cartItem) ? $cartItem->quantity : 0;
        }
        return 0;
    }
}
