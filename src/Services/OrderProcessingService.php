<?php

namespace EscolaLms\Cart\Services;

use EscolaLms\Cart\Models\Contracts\CanOrder;
use EscolaLms\Cart\Models\OrderItem;
use EscolaLms\Cart\Services\Contracts\OrderProcessingServiceContract;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\Models\Traits\HasCourses;
use EscolaLms\Courses\ValueObjects\CourseProgressCollection;
use Illuminate\Support\Collection;

class OrderProcessingService implements OrderProcessingServiceContract
{
    public function processOrderItems(Collection $orderItems, CanOrder $user): void
    {
        foreach ($orderItems as $orderItem) {
            assert($orderItem instanceof OrderItem);
            $buyable = $orderItem->buyable;

            if ($buyable instanceof Course) {
                $this->processBuyingCourse($buyable, $user);
            }
        }
    }

    private function processBuyingCourse(Course $course, CanOrder $user)
    {
        if (in_array(HasCourses::class, class_uses_recursive($user), true)) {
            $user->courses()->attach($course->getKey());
            CourseProgressCollection::make($user, $course);
        }
    }
}
