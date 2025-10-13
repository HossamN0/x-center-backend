<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseChapterController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseEnrollmentsController;
use App\Http\Controllers\CourseExamController;
use App\Http\Controllers\CourseReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');

// course
Route::get('/course', [CourseController::class, 'index'])->name('course.view');
Route::get('/course/{id}', [CourseController::class, 'show'])->name('course.viewAny');
Route::post('/course', [CourseController::class, 'store'])
    ->middleware('can:course.create')->name('course.create');
Route::delete('/course/{id}', [CourseController::class, 'destroy'])
    ->middleware('can:course.delete')->name('course.delete');
Route::put('/course/{id}', [CourseController::class, 'update'])
    ->middleware('can:course.update')->name('course.update');
Route::post('/course_image/{id}', [CourseController::class, 'editImage'])
    ->middleware('can:course.update')->name('course.update');

// course chapters
Route::post('/course/chapter', [CourseChapterController::class, 'store'])
    ->middleware('can:course_chapter.create')->name('course.chapter.create');
Route::put('/course/chapter/{id}', [CourseChapterController::class, 'update'])
    ->middleware('can:course_chapter.update')->name('course.chapter.create');
Route::get('/course/{id}/chapter', [CourseChapterController::class, 'index'])
    ->middleware('can:course_chapter.view')->name('course.chapter.view');
Route::get('/course/chapter/{id}', [CourseChapterController::class, 'show'])
    ->middleware('can:course_chapter.view')->name('course.chapter.viewAny');
Route::delete('/course/chapter/{id}', [CourseChapterController::class, 'destroy'])
    ->middleware('can:course_chapter.delete')->name('course.chapter.delete');

// course enroll
Route::post('/course/enroll', [CourseEnrollmentsController::class, 'store'])
    ->middleware('can:course_enrollment.create')->name('course.enroll.create');
Route::put('/course/enroll/{id}', [CourseEnrollmentsController::class, 'update'])
    ->middleware('can:course_enrollment.update')->name('course.enroll.update');

//course review
Route::post('/course/review', [CourseReviewController::class, 'store'])
    ->middleware('can:course_review.create')->name('course.review.create');
Route::put('/course/review/{id}', [CourseReviewController::class, 'update'])
    ->middleware('can:course_review.update')->name('course.review.update');
Route::delete('/course/review/{id}', [CourseReviewController::class, 'destroy'])
    ->middleware('can:course_review.delete')->name('course.review.delete');

// course exam
Route::post('/course/exam', [CourseExamController::class, 'store'])
    ->middleware('can:course_exam.create')->name('course.exam.create');
Route::get('/course/exam/{id}', [CourseExamController::class, 'show'])
    ->middleware('can:course_exam.view')->name('course.exam.viewAny');
Route::delete('/course/exam/{id}', [CourseExamController::class, 'destroy'])
    ->middleware('can:course_exam.delete')->name('course.exam.delete');
Route::post('/exam/submit', [CourseExamController::class, 'Submit'])
    ->middleware('can:course_exam.submit')->name('exam.submit');
