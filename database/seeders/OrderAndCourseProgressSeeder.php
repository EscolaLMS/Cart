<?php

namespace Database\Seeders\EscolaLms\Cart;

use EscolaLms\Cart\Models\Course;
use EscolaLms\Cart\Models\Order;
use EscolaLms\Cart\Models\OrderItem;
use EscolaLms\Cart\Models\User;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Core\Models\User as ModelsUser;
use EscolaLms\Courses\Enum\ProgressStatus;
use EscolaLms\Courses\Models\Lesson;
use EscolaLms\Courses\Models\Topic;
use EscolaLms\Courses\Models\TopicContent\Audio;
use EscolaLms\Courses\Models\TopicContent\Image;
use EscolaLms\Courses\Models\TopicContent\OEmbed;
use EscolaLms\Courses\Models\TopicContent\RichText;
use EscolaLms\Courses\Models\TopicContent\Video;
use EscolaLms\Courses\Repositories\CourseProgressRepository;
use EscolaLms\Courses\Services\ProgressService;
use EscolaLms\Payments\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Foundation\Testing\WithFaker;

class OrderAndCourseProgressSeeder extends Seeder
{
    use WithFaker;

    public function run()
    {
        $student = User::role(UserRole::STUDENT)->first();
        if (!$student) {
            // This will only be called if Users were not seeded before CartSeeder was Called
            $users = User::factory()->count(10)->create();
            /** @var User $user */
            foreach ($users as $user) {
                $user->assignRole(UserRole::STUDENT);
            }
        }

        $course = Course::first();
        if (!$course) {
            // This will only be called if CourseSeeder was not called before CartSeeder
            $courses = Course::factory()
                ->count(rand(5, 10))
                ->has(Lesson::factory()
                    ->has(
                        Topic::factory()
                            ->count(rand(5, 10))
                            ->afterCreating(function ($topic) {
                                $content = $this->getRandomRichContent();
                                if (method_exists($content, 'updatePath')) {
                                    $content = $content->updatePath($topic->id)->create();
                                } else {
                                    $content = $content->create();
                                }

                                $topic->topicable()->associate($content)->save();
                            })
                    )
                    ->count(rand(5, 10)))
                ->create();
        }

        $students = User::role(UserRole::STUDENT)->take(10)->get();
        /** @var User $student */
        foreach ($students as $student) {
            $coursesForOrder = Course::query()->inRandomOrder()->take(rand(1, 3))->get();
            $price = $coursesForOrder->reduce(fn ($acc, Course $course) => $acc + $course->getBuyablePrice(), 0);

            /** @var Order $order */
            $order = Order::factory()->has(Payment::factory()->state([
                'amount' => $price,
                'billable_id' => $student->getKey(),
                'billable_type' => ModelsUser::class,
            ]))
                ->afterCreating(
                    fn (Order $order) => $order->items()->saveMany(
                        $coursesForOrder->map(
                            function (Course $course) {
                                return OrderItem::query()->make([
                                    'quantity' => 1,
                                    'buyable_id' => $course->getKey(),
                                    'buyable_type' => Course::class,
                                ]);
                            }
                        )
                    )
                )->create([
                    'user_id' => $student->getKey(),
                    'total' => $price,
                    'subtotal' => $price,
                ]);

            $student->courses()->saveMany($coursesForOrder);
        }

        $progressService = app(ProgressService::class);
        $progressRepository = app(CourseProgressRepository::class);
        foreach ($students as $student) {
            $progressedCourses = $progressService->getByUser($student);
            foreach ($progressedCourses as $course) {
                /** @var Course $course */
                foreach ($course->topic as $topic) {
                    $status = ProgressStatus::getRandomValue();
                    $progressRepository->updateInTopic($topic, $student, $status, $status !== ProgressStatus::INCOMPLETE ? rand(60, 300) : null);
                    if ($status === ProgressStatus::IN_PROGRESS) {
                        $progressService->ping($student, $topic);
                    }
                }
                $result = $progressService->update($course, $student, []);
            }
        }
    }

    private function getRandomRichContent()
    {
        $classes = [RichText::factory(), Audio::factory(), Video::factory(), Image::factory(), OEmbed::factory()];

        return $classes[array_rand($classes)];
    }
}
