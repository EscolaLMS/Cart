<?php

namespace EscolaLms\Cart\Http\Resources;

use EscolaLms\Cart\Enums\OrderStatus;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
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
        ];
    }
}
