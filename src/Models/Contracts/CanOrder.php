<?php

namespace EscolaLms\Cart\Models\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

interface CanOrder extends Authenticatable
{
    public function orders(): HasMany;

    public function cart(): HasOne;
}
