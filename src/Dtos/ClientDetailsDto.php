<?php

namespace EscolaLms\Cart\Dtos;

class ClientDetailsDto
{
    protected ?string $name = null;
    protected ?string $email = null;
    protected ?string $street = null;
    protected ?string $street_number = null;
    protected ?string $city = null;
    protected ?string $postal = null;
    protected ?string $country = null;
    protected ?string $company = null;
    protected ?string $taxid = null;

    public function __construct(
        ?string $name = null,
        ?string $email = null,
        ?string $street = null,
        ?string $street_number = null,
        ?string $city = null,
        ?string $postal = null,
        ?string $country = null,
        ?string $company = null,
        ?string $taxid = null
    ) {
        $this->name = $name;
        $this->email = $email;
        $this->street = $street;
        $this->street_number = $street_number;
        $this->city = $city;
        $this->postal = $postal;
        $this->country = $country;
        $this->company = $company;
        $this->taxid = $taxid;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function getStreetNumber(): ?string
    {
        return $this->street_number;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getPostal(): ?string
    {
        return $this->postal;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function getTaxid(): ?string
    {
        return $this->taxid;
    }
}
