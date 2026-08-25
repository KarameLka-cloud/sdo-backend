<?php

use App\Http\Controllers\User\DepartmentController;
use App\Http\Controllers\User\PositionController;
use App\Http\Controllers\Mentorship\AdaptationPlanController;
use App\Http\Controllers\Mentorship\AdaptationPlanTemplateController;
use App\Http\Controllers\EmployeeDirectory\EmployeeDirectoryController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;

use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\RoleController;

use App\Http\Controllers\LearningItemController;

Route::group(['prefix' => 'auth'], function () {
    Route::post('login', LoginController::class)->middleware('throttle:5,1');
    Route::post('logout', LogoutController::class)->middleware('auth:sanctum');
});

Route::group(['prefix' => 'users', 'middleware' => 'auth:sanctum'], function () {
    Route::get('me', fn() => response()->json(Auth::user()?->loadMissing('roles')));
    Route::get('mentors', [UserController::class, 'mentors']);
    Route::get('department-heads', [UserController::class, 'departmentHeads']);
    Route::get('/', [UserController::class, 'index']);
    Route::get('roles', [RoleController::class, 'index']);
    Route::post('assign-role', [RoleController::class, 'assignRole'])->middleware('role:full_access');
    Route::post('revoke-role', [RoleController::class, 'revokeRole'])->middleware('role:full_access');
    Route::apiResource('departments', DepartmentController::class)->only(['index', 'show']);
    Route::apiResource('positions', PositionController::class)->only(['index', 'show']);
});

Route::group(['prefix' => 'learning-items', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/', [LearningItemController::class, 'index']);
    Route::get('{id}', [LearningItemController::class, 'show']);
    Route::post('/', [LearningItemController::class, 'store'])->middleware('role:full_access');
    Route::match(['put', 'patch'], '{id}', [LearningItemController::class, 'update'])->middleware('role:full_access');
    Route::delete('{id}', [LearningItemController::class, 'destroy'])->middleware('role:full_access');
});

Route::group(['prefix' => 'employee-directory', 'middleware' => 'auth:sanctum'], function () {
    Route::get('search', [EmployeeDirectoryController::class, 'search']);
});

Route::group(['prefix' => 'mentorship', 'middleware' => 'auth:sanctum'], function () {
    Route::apiResource('adaptation-plan-templates', AdaptationPlanTemplateController::class)->only(['index', 'show']);
    Route::apiResource('adaptation-plan-templates', AdaptationPlanTemplateController::class)->only(['store', 'update', 'destroy'])->middleware('role:full_access');
    Route::get('adaptation-plans/my', [AdaptationPlanController::class, 'my']);
    Route::patch('adaptation-plans/my/days/{dayId}/intern-comment', [AdaptationPlanController::class, 'updateMyInternComment']);
    Route::patch('adaptation-plans/my/days/{dayId}/tasks/{taskId}/status', [AdaptationPlanController::class, 'updateMyTaskStatus']);
    Route::patch('adaptation-plans/{id}/days/{dayId}', [AdaptationPlanController::class, 'updateDay']);
    Route::patch('adaptation-plans/{id}/days/{dayId}/tasks/{taskId}/status', [AdaptationPlanController::class, 'updateTaskStatus']);
    Route::apiResource('adaptation-plans', AdaptationPlanController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
});
