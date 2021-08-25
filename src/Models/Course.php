<?php

namespace EscolaLms\Cart\Models;

use Database\Factories\EscolaLms\Cart\Models\CourseFactory as CartCourseFactory;
use EscolaLms\Courses\Database\Factories\CourseFactory;
use Illuminate\Database\Eloquent\Model;
use Treestoneit\ShoppingCart\Buyable;
use Treestoneit\ShoppingCart\BuyableTrait;

/**
 * EscolaLms\Cart\Models\Course
 *
 * @property int $id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $title
 * @property string|null $summary
 * @property string|null $image_path
 * @property string|null $video_path
 * @property string|null $base_price
 * @property string|null $duration
 * @property int|null $author_id
 * @property-read \EscolaLms\Core\Models\User|null $author
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Categories\Models\Category[] $categories
 * @property-read int|null $categories_count
 * @property-read mixed $image_url
 * @property-read mixed $video_url
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Courses\Models\Lesson[] $lessons
 * @property-read int|null $lessons_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Courses\Models\CourseProgress[] $progress
 * @property-read int|null $progress_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Tags\Models\Tag[] $tags
 * @property-read int|null $tags_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Courses\Models\Topic[] $topic
 * @property-read int|null $topic_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\EscolaLms\Core\Models\User[] $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Course newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Course newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Course query()
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereAuthorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereBasePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereImagePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereSummary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Course whereVideoPath($value)
 * @mixin \Eloquent
 */
class Course extends \EscolaLms\Courses\Models\Course implements Buyable
{
    use BuyableTrait;

    public function getBuyablePrice(): float
    {
        return (float) $this->base_price;
    }

    public function alreadyBoughtBy(Model $user): bool
    {
        return $this->users()->where('users.id', $user->getKey())->exists();
    }

    protected static function newFactory(): CourseFactory
    {
        return CartCourseFactory::new();
    }
}
