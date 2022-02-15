<?php

namespace EscolaLms\Cart\Events;

use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Models\User;
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
}
