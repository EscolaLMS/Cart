<?php

namespace EscolaLms\Cart\Policies;

use EscolaLms\Cart\Enums\CartPermissionsEnum;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Core\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->can(CartPermissionsEnum::LIST_ALL_ORDERS);
    }

    public function view(User $user, Order $order)
    {
        return $user->can(CartPermissionsEnum::LIST_ALL_ORDERS)
            || $user->getKey() === $order->user_id;
    }

    public function create(User $user)
    {
        return true;
    }

    public function update(?User $user, Order $order)
    {
        return false;
    }

    public function delete(?User $user, Order $order)
    {
        return false;
    }
}
