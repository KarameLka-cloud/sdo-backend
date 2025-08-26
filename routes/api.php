<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;

use App\Http\Controllers\User\UserController;

use App\Http\Controllers\Edo\EdoEventController;
use App\Http\Controllers\Edo\EdoCourseController;
use App\Http\Controllers\Edo\EdoTestController;
use App\Http\Controllers\Education\EducationEventController;
use App\Http\Controllers\Education\EducationCourseController;
use App\Http\Controllers\Education\EducationWebinarController;
use App\Http\Controllers\Education\EducationTestController;

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', LoginController::class);
    Route::post('logout', LogoutController::class)->middleware('auth:sanctum');
});

Route::group(['prefix' => 'users', 'middleware' => 'auth:sanctum'], function () {
    Route::get('me', function (Request $request) {
        return $request->user();
    });
    Route::apiResource('/', UserController::class);
});

Route::group(['prefix' => 'education', 'middleware' => 'auth:sanctum'], function () {
    Route::apiResource('events', EducationEventController::class);
    Route::apiResource('courses', EducationCourseController::class);
    Route::apiResource('webinars', EducationWebinarController::class);
    Route::apiResource('tests', EducationTestController::class);

});

Route::group(['prefix' => 'edo', 'middleware' => 'auth:sanctum'], function () {
    Route::apiResource('events', EdoEventController::class);
    Route::apiResource('courses', EdoCourseController::class);
    Route::apiResource('tests', EdoTestController::class);
});
