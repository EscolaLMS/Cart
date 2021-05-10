<?php


namespace EscolaSoft\Cart\Services\Concerns;


use EscolaLms\Core\Models\Config;
use EscolaSoft\Cart\Dtos\Contracts\PaymentMethodContract;
use EscolaSoft\Cart\Enums\OrderStatus;
use EscolaSoft\Cart\Events\OrderCancelled;
use EscolaSoft\Cart\Events\OrderPaid;
use EscolaSoft\Cart\Models\Order;
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

//        $request = $this->getPaymentStrategy()->purchase($this->total(), $paymentMethod, 'Order ID: ' . $order->getKey());

        if (true /*$request->isSuccessful()*/) {
            $this->setPaid($order);
        } elseif ($request->isCancelled()) {
            $this->setCancelled($order);
        } else {
            throw new RuntimeException("Payment failed: " . $request->getMessage());
        }
    }

    public function createOrder(): Order
    {
        if (!isset($this->user)) {
            throw new RuntimeException("User must be initialized, to create order");
        }

        $this->user->orders()->where('status', OrderStatus::PROCESSING)->update(['status' => OrderStatus::CANCELLED]);

        $order = new Order($this->getModel()->getAttributes());
        $order->total = $this->total();
        $order->subtotal = $this->subtotal();
        $order->tax = $this->tax();
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

    protected function getPaymentConfig(): array
    {
        return Config::get('payments');
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