<?php

namespace EscolaLms\Cart\Http\Resources;

use EscolaLms\Cart\Enums\OrderStatus;
use EscolaLms\Cart\Models\Order;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function __construct(Order $order)
    {
        parent::__construct($order);
    }

    protected function getOrder(): Order
    {
        return $this->resource;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->resource->getKey(),
            'status' => OrderStatus::getName($this->status),
            'items' => $this->items->toArray(),
            'total' => $this->total,
            'subtotal' => $this->subtotal,
            'tax' => $this->tax,
            'created_at' => $this->created_at,
            'user_id' => $this->user_id,
            'client_name' => $this->client_name,
            'client_street' => $this->client_street,
            'client_postal' => $this->client_postal,
            'client_city' => $this->client_city,
            'client_country' => $this->client_country,
            'client_company' => $this->client_company,
            'client_taxid' => $this->client_taxid,
        ];
    }
}
