<?php

namespace EscolaLms\Cart\Tests\Models;

use EscolaLms\Cart\Database\Factories\UserFactory;
use EscolaLms\Cart\Models\Contracts\CanOrder as ContractsCanOrder;
use EscolaLms\Cart\Models\Traits\CanOrder;
use EscolaLms\Core\Models\User as CoreUser;
use EscolaLms\Courses\Models\Traits\HasCourses;
use EscolaLms\Payments\Concerns\Billable;
use EscolaLms\Payments\Contracts\Billable as ContractsBillable;

class User extends CoreUser implements ContractsBillable, ContractsCanOrder
{
    use CanOrder;
    use Billable;
    use HasCourses;

    protected function getTraitOwner(): self
    {
        return $this;
    }

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }
}
