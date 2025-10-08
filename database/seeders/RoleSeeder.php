<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createAdminRole();
        $this->createInstructorRole();
        $this->createStudentRole();
    }

    protected function createRole(RoleName $role, Collection $permissions): void
    {
        $newRole = Role::create(['name' => $role->value]);
        $newRole->permissions()->sync($permissions);
    }

    protected function createAdminRole(): void
    {
        $permission = Permission::all()->pluck('id');
        $this->createRole(RoleName::ADMIN, $permission);
    }

    protected function createInstructorRole(): void
    {
        $permission = Permission::query()
            ->where(function ($query) {
                $query->where(function ($subQuery) {
                    $subQuery->where('name', 'like', 'course.%')
                        ->orWhere('name', 'like', 'course_chapter.%')
                        ->orWhere('name', 'like', 'course_exam.%')
                        ->orWhere('name', 'like', 'exam_question.%')
                        ->orWhere('name', 'like', 'book.%');
                })
                    ->whereNotIn('name', [
                        'course.delete',
                        'course.enroll',
                        'course_chapter.delete',
                        'course_exam.delete',
                        'exam_question.delete',
                        'book.delete',
                        'book.enroll',
                    ]);
            })
            ->orWhere(function ($query) {
                $query->whereIn('name', [
                    'course_review.view',
                    'book_review.view'
                ]);
            })
            ->pluck('id');

        $this->createRole(RoleName::INSTRUCTOR, $permission);
    }

    public function createStudentRole(): void
    {
        $permission = Permission::query()
            ->whereIn('name', [
                'course.viewAny',
                'course.view',
                'course.enroll',
                'course_chapter.view',
                'course_exam.view',
                'exam_question.view',
                'exam_answer.create',
                'exam_answer.view',
                'book.viewAny',
                'book.view',
                'book.enroll',
                'course_review.create',
                'course_review.view',
                'book_review.create',
                'book_review.view',
            ])->pluck('id');

        $this->createRole(RoleName::STUDENT, $permission);
    }
}
