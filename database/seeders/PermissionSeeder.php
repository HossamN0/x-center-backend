<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $actions = [
            // Basic CRUD
            'viewAny',
            'view',
            'create',
            'update',
            'delete',

            // Special actions
            'enroll',        // For courses/books
            'complete',      // Mark as completed
            'publish',       // Publish content
            'approve',       // Approve reviews/content
            'export',        // Export data
            'manage',        // Full management
        ];

        $resources = [
            'user',
            'role',
            'permission',
            'course',
            'course_chapter',
            'course_enrollment',
            'course_review',
            'course_exam',
            'exam_question',
            'exam_answer',
            'book',
            'book_enrollment',
            'book_review',
        ];

        collect($resources)
            ->crossJoin($actions)
            ->map(function ($set) {
                return implode('.', $set);
            })->each(function ($permission) {
                Permission::create(['name' => $permission]);
            });
    }
}
