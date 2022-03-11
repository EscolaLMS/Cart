<?php

namespace EscolaLms\Cart\Services\Contracts;

use EscolaLms\Cart\Contracts\Productable;
use EscolaLms\Cart\Dtos\ProductsSearchDto;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\ProductProductable;
use EscolaLms\Core\Dtos\OrderDto;
use EscolaLms\Core\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;

interface ProductServiceContract
{
    public function registerProductableClass(string $productableClass): void;
    public function isProductableClassRegistered(string $productableClass): bool;
    public function listRegisteredProductableClasses(): array;
    public function canonicalProductableClass(string $productableClass): ?string;

    public function findSingleProductForProductable(Productable $productable): ?Product;
    public function findProductable(string $productableClass, $productId): ?Productable;

    public function mapProductProductableToJsonResource(ProductProductable $productProductable): JsonResource;

    public function searchAndPaginateProducts(ProductsSearchDto $searchDto, ?OrderDto $orderDto = null): LengthAwarePaginator;

    public function productIsBuyableOrOwnedByUser(Product $product, User $user, bool $check_productables = false);
    public function productIsBuyableByUser(Product $product, User $user, bool $check_productables = false);
    public function productIsOwnedByUser(Product $product, User $user, bool $check_productables = false);
    public function productProductablesAllOwnedByUser(Product $product, User $user): bool;
    public function productProductablesAllBuyableByUser(Product $product, User $user): bool;

    public function create(array $data): Product;
    public function update(Product $product, array $data): Product;

    public function attachProductToUser(Product $product, User $user): void;
    public function detachProductFromUser(Product $product, User $user): void;
    public function attachProductableToUser(Productable $productable, User $user): void;
    public function detachProductableFromUser(Productable $productable, User $user): void;
}