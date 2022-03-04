<?php

namespace EscolaLms\Cart\Tests\Mocks;

use EscolaLms\Cart\Contracts\Productable;
use EscolaLms\Cart\Contracts\ProductableTrait;
use EscolaLms\Core\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ExampleProductable extends Model implements Productable
{
    use ProductableTrait;
    use HasFactory;

    protected $table = 'test_productables';

    protected $guarded = ['id'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'test_productables_users', 'test_productable_id', 'user_id');
    }

    public function attachToUser(User $user): void
    {
        $this->users()->syncWithoutDetaching($user->getKey());
    }

    public function detachFromUser(User $user): void
    {
        $this->users()->detach($user->getKey());
    }

    protected static function newFactory(): ExampleProductableFactory
    {
        return ExampleProductableFactory::new();
    }
}
