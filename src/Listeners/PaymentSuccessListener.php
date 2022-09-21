<?php

namespace EscolaLms\Cart\Listeners;

use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Services\Contracts\OrderServiceContract;
use EscolaLms\Payments\Events\PaymentSuccess;

class PaymentSuccessListener
{
    protected OrderServiceContract $orderService;

    public function __construct(OrderServiceContract $orderService)
    {
        $this->orderService = $orderService;
    }

    public function handle(PaymentSuccess $event)
    {
        $payment = $event->getPayment();
        if ($payment->payable instanceof Order) {
            $this->orderService->setPaid($payment->payable->refresh());
        }
    }
}
