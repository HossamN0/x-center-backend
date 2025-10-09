<?php

namespace App;

use App\Models\Course;
use App\Models\CourseChapter;
use App\Models\CourseEnrollment;

trait CourseAuthorization
{
    protected function authorizeCourseAccess($courseId, $user)
    {
        if (!$user) {
            return false;
        }
        if ($user->isInstructor()) {
            $course = Course::where('id', $courseId)
                ->where('instructor_id', $user->id)
                ->first();

            if (!$course) {
                return false;
            }
        }

        if ($user->isStudent()) {
            $enrollment = CourseEnrollment::where('course_id', $courseId)
                ->where('student_id', $user->id)
                ->where('status', 'accepted')
                ->first();

            if (!$enrollment) {
                return false;
            }
        }

        return true;
    }

    protected function authorizeChapterAccess($chapterId, $user)
    {
        $chapter = CourseChapter::findOrFail($chapterId);
        return $this->authorizeCourseAccess($chapter->course_id, $user);
    }
}
