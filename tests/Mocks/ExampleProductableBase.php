<?php

namespace EscolaLms\Cart\Tests\Mocks;

use EscolaLms\Core\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ExampleProductableBase extends Model
{
    protected $table = 'test_productables';

    protected $guarded = ['id'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'test_productables_users', 'test_productable_id', 'user_id');
    }

    public static function getTableName(): string
    {
        return 'test_productables';
    }

    public static function getUsersTableName(): string
    {
        return 'test_productables_users';
    }
}
