<?php

namespace EscolaLms\Cart\Database\Factories\Models;

use EscolaLms\Cart\Models\Course;
use EscolaLms\Courses\Database\Factories\CourseFactory as BaseCourseFactory;

class CourseFactory extends BaseCourseFactory
{
    protected $model = Course::class;
}
