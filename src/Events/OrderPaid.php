<?php


namespace EscolaLms\Cart\Events;

use EscolaLms\Cart\Models\Contracts\CanOrder;
use EscolaLms\Cart\Models\Order;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPaid
{
    use Dispatchable, SerializesModels;

    private Order $order;
    private CanOrder $user;

    public function __construct(Order $order, CanOrder $user)
    {
        $this->order = $order;
        $this->user = $user;
    }

    /**
     * @return Order
     */
    public function getOrder(): Order
    {
        return $this->order;
    }

    /**
     * @return CanOrder
     */
    public function getUser(): CanOrder
    {
        return $this->user;
    }
}
