<?php

namespace EscolaLms\Cart\Http\Resources;

use EscolaLms\Dictionaries\Models\Category;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *      schema="ProductCategorySimpleResource",
 *      @OA\Property(
 *          property="id",
 *          type="integer"
 *      ),
 *      @OA\Property(
 *          property="name",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="name_with_breadcrumbs",
 *          type="string"
 *      ),
 *      @OA\Property(
 *          property="slug",
 *          type="string"
 *      ),
 * )
 *
 * @mixin Category
 */
class CategorySimpleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->getKey(),
            'name' => $this->name,
            'name_with_breadcrumbs' => $this->name_with_breadcrumbs,
            'slug' => $this->slug,
        ];
    }
}
