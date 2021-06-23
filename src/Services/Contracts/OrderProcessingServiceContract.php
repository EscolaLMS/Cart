<?php

namespace EscolaLms\Cart\Services\Contracts;

use EscolaLms\Cart\Models\Contracts\CanOrder;
use Illuminate\Support\Collection;

interface OrderProcessingServiceContract
{
    public function processOrderItems(Collection $orderItems, CanOrder $user): void;
}
