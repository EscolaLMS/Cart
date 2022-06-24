<?php

namespace EscolaLms\Cart\Dtos;

use EscolaLms\Core\Dtos\Contracts\DtoContract;
use Illuminate\Support\Carbon;

class OrdersSearchDto implements DtoContract
{
    protected ?Carbon $date_from;
    protected ?Carbon $date_to;
    protected ?int $user_id;
    protected ?int $product_id;
    protected ?int $productable_id;
    protected ?string $productable_type;
    protected ?int $status;
    protected ?int $per_page;

    public function __construct(
        ?Carbon $date_from = null,
        ?Carbon $date_to = null,
        ?int $user_id = null,
        ?int $product_id = null,
        ?int $productable_id = null,
        ?string $productable_type = null,
        ?int $status = null,
        ?int $per_page = null
    ) {
        $this->date_from = $date_from;
        $this->date_to = $date_to;
        $this->user_id = $user_id;
        $this->product_id = $product_id;
        $this->productable_id = $productable_id;
        $this->productable_type = $productable_type;
        $this->status = $status;
        $this->per_page = $per_page;
    }

    public function toArray(): array
    {
        return [
            'date_from' => $this->date_from,
            'date_to' => $this->date_to,
            'user_id' => $this->user_id,
            'product_id' => $this->product_id,
            'productable_id' => $this->productable_id,
            'productable_type' => $this->productable_type,
            'status' => $this->status,
            'per_page' => $this->per_page,
        ];
    }

    public function getDateFrom(): ?Carbon
    {
        return $this->date_from;
    }

    public function getDateTo(): ?Carbon
    {
        return $this->date_to;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function getProductId(): ?int
    {
        return $this->product_id;
    }

    public function getProductableId(): ?int
    {
        return $this->productable_id;
    }

    public function getProductableType(): ?string
    {
        return $this->productable_type;
    }

    public function getPerPage(): ?int
    {
        return $this->per_page;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }
}
