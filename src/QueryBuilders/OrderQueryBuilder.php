<?php

namespace EscolaLms\Cart\QueryBuilders;

use EscolaLms\Core\Models\User;
use EscolaLms\Courses\Models\Course;
use Illuminate\Database\Eloquent\Builder;

class OrderQueryBuilder extends Builder
{
    public function whereHasCourse(Course $course)
    {
        return $this->whereHasCourseId($course->getKey());
    }

    public function whereHasCourseId(int $course_id)
    {
        return $this->whereHas('courses', fn (Builder $query) => $query->where('courses.id', $course_id));
    }

    public function whereHasCourseWithAuthor(User $author)
    {
        return $this->whereHasCourseWithAuthorId($author->getKey());
    }

    public function whereHasCourseWithAuthorId(int $author_id)
    {
        return $this->whereHas('courses', fn (Builder $query) => $query->where('author_id', $author_id));
    }
}