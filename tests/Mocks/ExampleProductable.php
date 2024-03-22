<?php

namespace EscolaLms\Cart\Tests\Mocks;

use EscolaLms\Cart\Contracts\Productable;
use EscolaLms\Cart\Contracts\ProductableTrait;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExampleProductable extends ExampleProductableBase implements Productable
{
    use ProductableTrait;
    use HasFactory;

    public function attachToUser(User $user, int $quantity = 1, ?Product $product = null): void
    {
        $productUser = $product?->users()->where('user_id', $user->getKey())->first()?->pivot;
        $this->users()->syncWithoutDetaching([$user->getKey() => ['end_date' => $productUser?->end_date]]);
    }

    public function detachFromUser(User $user, int $quantity = 1, ?Product $product = null): void
    {
        $this->users()->detach($user->getKey());
    }

    public function getMorphClass()
    {
        return ExampleProductableBase::class;
    }

    protected static function newFactory(): ExampleProductableFactory
    {
        return ExampleProductableFactory::new();
    }

    public static function getMorphClassStatic(): string
    {
        return parent::class;
    }
}
