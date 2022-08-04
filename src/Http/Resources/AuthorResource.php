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
        $author = $this->getAuthor();

        return array_merge(
            [
                'id' => $author->getKey(),
                'first_name' => $author->first_name,
                'last_name' => $author->last_name,
                'path_avatar' => $author->path_avatar,
                'categories' => $author->categories,
            ],
            ModelFields::getExtraAttributesValues($author, MetaFieldVisibilityEnum::PUBLIC)
        );
    }
}
