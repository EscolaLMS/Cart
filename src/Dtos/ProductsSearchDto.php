<?php

namespace EscolaLms\Cart\Dtos;

use EscolaLms\Core\Dtos\Contracts\DtoContract;

class ProductsSearchDto implements DtoContract
{
    protected ?string $name;
    protected ?string $type;
    protected ?bool $free;
    protected ?string $productable_class;
    protected $productable_id;
    protected ?bool $purchasable = true;
    protected ?int $per_page;

    public function __construct(?string $name = null, ?string $type = null, ?bool $free = null, ?string $productable_type  = null, ?int $productable_id = null, ?bool $purchasable = true, ?int $per_page = null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->free = $free;
        $this->productable_type = $productable_type;
        $this->productable_id = $productable_id;
        $this->purchasable = $purchasable;
        $this->per_page = $per_page;
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'free' => $this->free,
            'productable_type' => $this->productable_type,
            'productable_id' => $this->productable_id,
            'purchasable' => $this->purchasable,
            'per_page' => $this->per_page,
        ];
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getFree(): ?bool
    {
        return $this->free;
    }

    public function getProductableType(): ?string
    {
        return $this->productable_type;
    }

    public function getProductableId(): ?int
    {
        return $this->productable_id;
    }

    public function getPurchasable(): ?bool
    {
        return $this->purchasable;
    }

    public function getPerPage(): ?int
    {
        return $this->per_page;
    }
}
