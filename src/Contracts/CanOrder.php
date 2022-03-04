<?php

namespace EscolaLms\Cart\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

interface CanOrder extends Authenticatable
{
    public function products(): BelongsToMany;

    public function orders(): HasMany;

    public function cart(): HasOne;
}
