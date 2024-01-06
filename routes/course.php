<?php

use App\Http\Controllers\AssessmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\QuestionController;

Route::get('/Courses/trash', [CourseController::class, 'trash']);
Route::get('/Courses/AllCourses', [CourseController::class, 'AllCourses']);
Route::put('/Courses/{course}/restore', [CourseController::class, 'restore']);
Route::delete('/Courses/{course}/force-delete', [CourseController::class, 'forceDelete']);

Route::get('/Lessons/trash', [LessonController::class, 'trash']);
Route::put('/Lessons/{lesson}/restore', [LessonController::class, 'restore']);
Route::delete('/Lessons/{lesson}/force-delete', [LessonController::class, 'forceDelete']);

Route::delete('/Resource/{id}/delete', [LessonController::class, 'ResourceDelete']);

Route::apiResource('Courses', CourseController::class);

Route::apiResource('Modules', ModuleController::class);

Route::apiResource('Lessons', LessonController::class);

Route::apiResource('Assessments', AssessmentController::class);

Route::apiResource('Questions', QuestionController::class);

Route::post('attachFileToQuestion', [QuestionController::class, 'attachFileToQuestion']);
