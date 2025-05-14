<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController as UserLoginController;
use App\Http\Controllers\Auth\LogoutController as UserLogoutController;

use App\Http\Controllers\User\MeController as UserMeController;
use App\Http\Controllers\User\IndexController as UserIndexController;
use App\Http\Controllers\User\ShowController as UserShowController;

use App\Http\Controllers\Edo\Event\IndexController as EdoEventIndexController;
use App\Http\Controllers\Edo\Event\StoreController as EdoEventStoreController;
use App\Http\Controllers\Edo\Event\ShowController as EdoEventShowController;
use App\Http\Controllers\Edo\Event\UpdateController as EdoEventUpdateController;

//Route::get('/user', function (Request $request) {
//    return $request->user();
//})->middleware('auth:sanctum');

Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', UserLoginController::class);
    Route::post('/logout', UserLogoutController::class)->middleware('auth:sanctum');
});

Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::get('/users/me', UserMeController::class);
    Route::get('/users', UserIndexController::class);
    Route::get('/users/{id}', UserShowController::class);
});

Route::group(['prefix' => 'edo', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/events', EdoEventIndexController::class);
    Route::post('/events', EdoEventStoreController::class);
    Route::get('/events/{id}', EdoEventShowController::class);
    Route::post('/events/{id}', EdoEventUpdateController::class);
});
