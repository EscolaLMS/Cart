<?php

namespace EscolaLms\Cart\Tests\Mocks;

use EscolaLms\Cart\Contracts\Product as ContractsProduct;
use EscolaLms\Cart\Contracts\ProductTrait;
use EscolaLms\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model implements ContractsProduct
{
    use ProductTrait;
    use HasFactory;

    protected $table = 'test_products';

    public function getBuyableDescription(): string
    {
        return 'Example product ' . $this->getKey();
    }

    public function getBuyablePrice(?array $options = null): int
    {
        return $this->price;
    }

    public function getTaxRate(): int
    {
        return $this->tax_rate;
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'test_products_users', 'test_product_id', 'user_id');
    }

    public function attachToUser(User $user): void
    {
        $this->users()->syncWithoutDetaching($user->getKey());
    }

    public function detachFromUser(User $user): void
    {
        $this->users()->detach($user->getKey());
    }

    protected static function newFactory(): ProductFactory
    {
        return ProductFactory::new();
    }
}
