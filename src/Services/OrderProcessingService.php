<?php

namespace EscolaLms\Cart\Services;

use EscolaLms\Cart\Models\Contracts\CanOrder;
use EscolaLms\Cart\Models\OrderItem;
use EscolaLms\Cart\Services\Contracts\OrderProcessingServiceContract;
use EscolaLms\Courses\Models\Course;
use EscolaLms\Courses\ValueObjects\CourseProgressCollection;
use Illuminate\Database\Eloquent\Model;
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
        if ($user instanceof Model) {
            $course->users()->syncWithoutDetaching([$user->getKey()]);
            CourseProgressCollection::make($user, $course);
        }
    }
}
