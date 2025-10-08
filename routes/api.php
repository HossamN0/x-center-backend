<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');

Route::post('/course', [CourseController::class, 'store'])
    ->middleware('can:course.create')->name('course.create');
Route::get('/course', [CourseController::class, 'index'])->name('course.view');
Route::get('/course/{id}', [CourseController::class, 'show'])->name('course.viewAny');
Route::delete('/course/{id}', [CourseController::class, 'destroy'])
    ->middleware('can:course.delete')->name('course.delete');
Route::put('/course/{id}', [CourseController::class, 'update'])
    ->middleware('can:course.update')->name('course.update');
