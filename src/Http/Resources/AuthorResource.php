<?php

namespace EscolaLms\Cart\Http\Resources;

use EscolaLms\Core\Models\User;
use EscolaLms\ModelFields\Enum\MetaFieldVisibilityEnum;
use EscolaLms\ModelFields\Facades\ModelFields;
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
        $fields = array_merge(
            $this->getAuthor()->toArray(),
            ['categories' => $this->categories],
            ModelFields::getExtraAttributesValues($this->getAuthor(), MetaFieldVisibilityEnum::PUBLIC)
        );
        return $fields;
    }
}
