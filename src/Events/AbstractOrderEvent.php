<?php

namespace EscolaLms\Cart\Events;

use EscolaLms\Cart\Models\Order;
use EscolaLms\Core\Models\User;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class AbstractOrderEvent
{
    use Dispatchable, SerializesModels;

    private Order $order;
    private User $user;

    public function __construct(Order $order, ?User $user = null)
    {
        $this->order = $order;
        $this->user = $user ?? $order->user;
    }

    public function getOrder(): Order
    {
        return $this->order;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getEmail(): ?string
    {
        return $this->getOrder()->client_email ?? $this->getUser()->email;
    }
}
