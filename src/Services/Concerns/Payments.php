<?php

namespace EscolaLms\Cart\Services\Concerns;

use EscolaLms\Cart\Events\EscolaLmsCartOrderSuccessTemplateEvent;
use EscolaLms\Cart\Services\Contracts\OrderProcessingServiceContract;
use EscolaLms\Payments\Dtos\Contracts\PaymentMethodContract;
use EscolaLms\Payments\Enums\PaymentStatus;
use EscolaLms\Cart\Enums\OrderStatus;
use EscolaLms\Cart\Events\EscolaLmsCartOrderCancelledTemplateEvent;
use EscolaLms\Cart\Events\EscolaLmsCartOrderPaidTemplateEvent;
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
        event(new EscolaLmsCartOrderSuccessTemplateEvent($this->getUser(), $order));
        return $order;
    }

    protected function setPaid(Order $order): void
    {
        $this->setOrderStatus($order, OrderStatus::PAID);
        $this->clearCartBeforeDestroy($order);
        $this->destroy();
        event(new EscolaLmsCartOrderPaidTemplateEvent($this->getUser(), $order));
    }

    protected function setCancelled(Order $order): void
    {
        $this->setOrderStatus($order, OrderStatus::CANCELLED);
        $this->clearCartBeforeDestroy($order);
        $this->destroy();
        event(new EscolaLmsCartOrderCancelledTemplateEvent($this->getUser(), $order));
    }

    private function clearCartBeforeDestroy(Order $order): void
    {
        $orderProcessingContract = app(OrderProcessingServiceContract::class);
        $orderProcessingContract->processOrderItems($order->items, $this->getUser());
        $this->items()->each(fn ($item) => $this->remove($item->getKey()));
    }

    private function setOrderStatus(Order $order, int $status): void
    {
        $order->update([
            'status' => $status
        ]);
    }
}
