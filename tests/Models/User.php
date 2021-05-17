<?php

namespace EscolaSoft\Cart\Tests\Models;

use EscolaLms\Core\Models\User as UserCore;
use EscolaSoft\Cart\Models\Traits\CanTransaction;
use EscolaSoft\Cart\Tests\database\factories\UserFactory;

class User extends UserCore
{
    use CanTransaction;

    public static function newFactory()
    {
        return UserFactory::new();
    }
}