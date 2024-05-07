<?php

namespace EscolaLms\Cart\Jobs;

use EscolaLms\Cart\Dtos\ClientDetailsDto;
use EscolaLms\Cart\Enums\OrderStatus;
use EscolaLms\Cart\Enums\ProductType;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\ProductUser;
use EscolaLms\Cart\Models\User;
use EscolaLms\Cart\Services\Contracts\OrderServiceContract;
use EscolaLms\Payments\Enums\PaymentStatus;
use EscolaLms\Payments\Facades\PaymentGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RenewRecursiveProductUser implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private ProductUser $productUser;

    public function __construct(ProductUser $productUser)
    {
        $this->productUser = $productUser;

    }

    public function getProductUser(): ProductUser
    {
        return $this->productUser;
    }

    public function handle(OrderServiceContract $orderService): void
    {
        $product = Product::find($this->productUser->product_id);
        $user = User::find($this->productUser->user_id);

        /** @var Order $order */
        $order = $user->orders()
            ->whereIn('status', [OrderStatus::PAID, OrderStatus::TRIAL_PAID])
            ->whereRelation('items', fn(Builder $query) => $query
                ->whereMorphRelation('buyable', [Product::class], 'buyable_id', '=', $product->getKey())
            )
            ->orderBy('created_at', 'desc')
            ->first();

        $prevPayment = $order->payments()
            ->whereIn('status', [PaymentStatus::PAID(), PaymentStatus::REFUNDED()])
            ->orderBy('created_at', 'desc')
            ->first();

        $paymentDriver = PaymentGateway::driver($prevPayment->driver);

        if (!$paymentDriver->ableToRenew()) {
            return;
        }

        $newOrder = $orderService->createOrderFromProduct($product, $user->getKey(), $this->getClientDetailsDto($order));

        $paymentProcessor = $newOrder->process();

        $parameters = [
            'return_url' => url('/'),
            'email' => $user->email,
            'type' => $product->type,
            'gateway' => $prevPayment->driver,
            'gateway_order_id' => $prevPayment->gateway_order_id
        ];

        if (ProductType::isSubscriptionType($product->type)) {
            $parameters += $product->getSubscriptionParameters();
        }

        $paymentProcessor->purchase($parameters);
        $payment = $paymentProcessor->getPayment();

        if ($payment->status->is(PaymentStatus::CANCELLED)) {
            $orderService->setCancelled($newOrder);
        }
    }

    private function getClientDetailsDto(Order $order): ClientDetailsDto
    {
        return new ClientDetailsDto(
            $order->client_name,
            $order->client_email,
            $order->client_street,
            $order->client_street_number,
            $order->client_city,
            $order->client_postal,
            $order->client_country,
            $order->client_company,
            $order->client_taxid,
        );
    }
}
