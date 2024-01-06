<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

Route::get('/me', [ProfileController::class, 'index']);
Route::get('/view-my-courses', [ProfileController::class, 'ViewMyCourses']);
Route::put('/update/{user}', [ProfileController::class, 'updateProfile']);
Route::get('/profile/{username}/{code}', [ProfileController::class, 'share']);
