<?php

namespace EscolaSoft\Cart\Tests\Models;

use EscolaLms\Core\Models\User as UserCore;
use EscolaSoft\Cart\Models\Traits\HasOrders;
use EscolaSoft\Cart\Tests\database\factories\UserFactory;

class User extends UserCore
{
    use HasOrders;

    public static function newFactory()
    {
        return UserFactory::new();
    }
}