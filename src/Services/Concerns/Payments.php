<?php

namespace EscolaLms\Cart\Services\Concerns;

use EscolaLms\Payments\Dtos\Contracts\PaymentMethodContract;
use EscolaLms\Payments\Enums\PaymentStatus;
use EscolaLms\Cart\Enums\OrderStatus;
use EscolaLms\Cart\Events\OrderCancelled;
use EscolaLms\Cart\Events\OrderPaid;
use EscolaLms\Cart\Models\Order;
use RuntimeException;

trait Payments
{
    /**
     * @param PaymentMethodContract $paymentMethod
     * @throws RuntimeException
     */
    public function purchase(PaymentMethodContract $paymentMethod = null): void
    {
        $order = $this->createOrder();
        $paymentProcessor = $order->process();
        $paymentProcessor->purchase($paymentMethod);
        $payment = $paymentProcessor->getPayment();

        if ($payment->status->is(PaymentStatus::PAID)) {
            $this->setPaid($order);
        } elseif ($payment->status->is(PaymentStatus::CANCELLED)) {
            $this->setCancelled($order);
        }
    }

    public function createOrder(): Order
    {
        if (!isset($this->user)) {
            throw new RuntimeException("User must be initialized, to create order");
        }

        $this->getUser()->orders()->where('status', OrderStatus::PROCESSING)->update(['status' => OrderStatus::CANCELLED]);

        $order = new Order($this->getModel()->getAttributes());
        $order->total = (int) $this->total();
        $order->subtotal = (int) $this->subtotal();
        $order->tax = (int) $this->tax();
        $order->status = OrderStatus::PROCESSING;
        $order->save();

        $items = [];
        foreach ($this->getModel()->items as $item) {
            $items[] = array_merge(
                $item->only(['buyable_type', 'buyable_id', 'quantity']),
                ['order_id' => $order->getKey()]
            );
        }
        $order->items()->insert($items);

        return $order;
    }

    protected function setPaid(Order $order): void
    {
        $this->setOrderStatus($order, OrderStatus::PAID);
        event(new OrderPaid($order, $this->getUser()));
        $this->destroy();
    }

    protected function setCancelled(Order $order): void
    {
        $this->setOrderStatus($order, OrderStatus::CANCELLED);
        event(new OrderCancelled($order, $this->getUser()));
        $this->destroy();
    }

    private function setOrderStatus(Order $order, int $status): void
    {
        $order->update([
            'status' => $status
        ]);
    }
}
