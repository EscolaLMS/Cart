<?php

namespace EscolaLms\Cart\Http\Resources;

use EscolaLms\Auth\Models\User as UserAuth;
use EscolaLms\Core\Models\User as UserCore;
use EscolaLms\ModelFields\Enum\MetaFieldVisibilityEnum;
use EscolaLms\ModelFields\Facades\ModelFields;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthorResource extends JsonResource
{
    public function __construct(UserCore $user)
    {
        parent::__construct($user);
    }

    protected function getAuthor(): UserCore
    {
        if ($this->resource instanceof UserAuth) {
            return $this->resource;
        }
        return new UserAuth($this->resource->toArray());
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
