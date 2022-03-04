<?php

namespace EscolaLms\Cart\Models;

use EscolaLms\Categories\Models\Category as BaseCategory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * EscolaLms\Cart\Models\Category
 *
 * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property bool $is_active
 * @property int|null $parent_id
 * @property string|null $icon
 * @property string|null $icon_class
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|BaseCategory[] $children
 * @property-read int|null $children_count
 * @property-read string $name_with_breadcrumbs
 * @property-read BaseCategory|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Cart\Models\Product[] $products
 * @property-read int|null $products_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Illuminate\Foundation\Auth\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereIconClass($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Category extends BaseCategory
{
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'products_categories');
    }
}
