<?php

namespace EscolaLms\Cart\Http\Resources;

use EscolaLms\Cart\Enums\OrderStatus;
use EscolaLms\Cart\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderExportResource extends JsonResource
{
    public function __construct(Order $order)
    {
        parent::__construct($order);
    }

    protected function getOrder(): Order
    {
        return $this->resource;
    }

    public function toArray($request): array
    {
        return [
            'id' => $this->getOrder()->getKey(),
            'status' => OrderStatus::getName($this->getOrder()->status),
            'items' => OrderItemExportResource::collection($this->getOrder()->items),
            'total' => $this->getOrder()->total,
            'subtotal' => $this->getOrder()->subtotal,
            'tax' => $this->getOrder()->tax,
            'created_at' => $this->getOrder()->created_at,
            'user_id' => $this->getOrder()->user_id,
            'client_name' => $this->getOrder()->client_name,
            'client_email' => $this->getOrder()->client_email,
            'client_street' => $this->getOrder()->client_street,
            'client_street_number' => $this->getOrder()->client_street_number,
            'client_postal' => $this->getOrder()->client_postal,
            'client_city' => $this->getOrder()->client_city,
            'client_country' => $this->getOrder()->client_country,
            'client_company' => $this->getOrder()->client_company,
            'client_taxid' => $this->getOrder()->client_taxid,
        ];
    }
}
