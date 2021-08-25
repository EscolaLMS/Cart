<?php

namespace EscolaLms\Cart\Database\Factories\Models;

use Database\Factories\EscolaLms\Core\Models\UserFactory as CoreUserFactory;
use EscolaLms\Cart\Models\User;

class UserFactory extends CoreUserFactory
{
    protected $model = User::class;
}
