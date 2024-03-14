<?php

namespace EscolaLms\Cart\Http\Resources;

use EscolaLms\Cart\Models\Product;
use EscolaLms\Cart\Models\ProductProductable;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

/**
 * @OA\Schema(
 *     schema="MyProductResource",
 *      @OA\Property(
 *          property="id",
 *          type="integer",
 *      ),
 *      @OA\Property(
 *          property="type",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="name",
 *          type="string",
 *      ),
 *      @OA\Property(
 *          property="is_active",
 *          type="boolean",
 *       ),
 *      @OA\Property(
 *          property="end_date",
 *          type="string",
 *          format="date-time"
 *       ),
 *      @OA\Property(
 *          property="status",
 *          type="string",
 *       ),
 *      @OA\Property(
 *          property="productables",
 *          type="array",
 *           @OA\Items(
 *               @OA\Property(
 *                   property="productable_class",
 *                   type="string"
 *               ),
 *               @OA\Property(
 *                   property="productable_id",
 *                   type="string",
 *              ),
 *           )
 *       )
 *       ),
 * )
 *
 * @mixin Product
 */
class MyProductResource extends JsonResource
{
    public function toArray($request): array
    {
        $productUserPivot = $this->users()->where('user_id', $request->user()->getKey())->first()?->pivot;

        return [
            'id' => $this->getKey(),
            'type' => $this->type,
            'name' => $this->name,
            'is_active' => !$productUserPivot?->end_date || $productUserPivot?->end_date >= Carbon::now(),
            'end_date' => $productUserPivot?->end_date,
            'status' => $productUserPivot?->status,
            'productables' => $this->productables->map(fn(ProductProductable $productProductable) => [
                'productable_class' => $productProductable->productable_type,
                'productable_id' => $productProductable->productable_id
            ]),
        ];
    }
}
