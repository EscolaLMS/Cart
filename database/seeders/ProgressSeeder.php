<?php

namespace EscolaLms\Cart\Database\Seeders;

use EscolaLms\Cart\Models\Course;
use EscolaLms\Cart\Models\User;
use EscolaLms\Core\Enums\UserRole;
use EscolaLms\Courses\Enum\ProgressStatus;
use EscolaLms\Courses\Repositories\CourseProgressRepository;
use EscolaLms\Courses\Services\ProgressService;

class ProgressSeeder
{
    public function run()
    {
        $students = User::role(UserRole::STUDENT)->whereHas('courses')->take(10)->get();
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
                $progressService->update($course, $student, []);
            }
        }
    }
}
