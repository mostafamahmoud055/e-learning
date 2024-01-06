<?php

use App\Http\Controllers\StudentAssessmentController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\StudentsCoursesController;
use App\Models\UserAssessment;

Route::get('Students/search', [StudentController::class,'filter']);
Route::apiResource('Students', StudentController::class);
Route::delete('StudentsCourses/removeStudentsFromCourse', [StudentsCoursesController::class,'removeStudentsFromCourse']);
Route::apiResource('StudentsCourses', StudentsCoursesController::class);

Route::post('finish-assessment-now', [StudentAssessmentController::class,'create']);

Route::get('finished-assessment/{id}', [StudentAssessmentController::class,'show']);


    