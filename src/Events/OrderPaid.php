<?php


namespace EscolaSoft\Cart\Events;


use EscolaSoft\Cart\Models\Order;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderPaid
{
    use Dispatchable, SerializesModels;

    private Order $order;
    private Authenticatable $user;

    public function __construct(Order $order, Authenticatable $user)
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
     * @return Authenticatable
     */
    public function getUser(): Authenticatable
    {
        return $this->user;
    }

}