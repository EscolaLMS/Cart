<?php

namespace EscolaLms\Cart\Http\Resources;

use EscolaLms\Core\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthorResource extends JsonResource
{
    public function __construct(User $user)
    {
        parent::__construct($user);
    }

    protected function getAuthor(): User
    {
        return $this->resource;
    }

    public function toArray($request): array
    {
        return [
            'id' => $this->getAuthor()->getKey(),
            'name' => $this->getAuthor()->name,
            'first_name' => $this->getAuthor()->first_name,
            'last_name' => $this->getAuthor()->last_name,
        ];
    }
}
