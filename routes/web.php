<?php

use App\Helpers\VideoStream;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});


Route::get('/home/stream', function () {
    $video_path = public_path('images/resources/Other/Learn Typescript In Arabic 2022/2022.mp4');
    $stream = new VideoStream($video_path);
    $stream->start(); 
});