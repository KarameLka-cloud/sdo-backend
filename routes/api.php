<?php

use App\Http\Controllers\User\DepartmentController;
use App\Http\Controllers\User\PositionController;
use App\Http\Controllers\Mentorship\AdaptationPlanController;
use App\Http\Controllers\Mentorship\AdaptationPlanTemplateController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;

use App\Http\Controllers\User\UserController;
use App\Http\Controllers\User\RoleController;

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
    Route::get('me', fn() => response()->json(Auth::user()));
    Route::get('mentors', [UserController::class, 'mentors']);
    Route::get('department-heads', [UserController::class, 'departmentHeads']);
    Route::get('/', [UserController::class, 'index']);
    Route::get('roles', [RoleController::class, 'index']);
    Route::post('assign-role', [RoleController::class, 'assignRole'])->middleware('role:full_access');
    Route::post('revoke-role', [RoleController::class, 'revokeRole'])->middleware('role:full_access');
    Route::apiResource('departments', DepartmentController::class)->only(['index', 'show']);
    Route::apiResource('departments', DepartmentController::class)->only(['store', 'update', 'destroy'])->middleware('role:full_access');
    Route::apiResource('positions', PositionController::class)->only(['index', 'show']);
    Route::apiResource('positions', PositionController::class)->only(['store', 'update', 'destroy'])->middleware('role:full_access');
});

Route::group(['prefix' => 'education', 'middleware' => 'auth:sanctum'], function () {
    Route::apiResource('events', EducationEventController::class)->only(['index', 'show']);
    Route::apiResource('events', EducationEventController::class)->only(['store', 'update', 'destroy'])->middleware('role:full_access');
    Route::apiResource('courses', EducationCourseController::class)->only(['index', 'show']);
    Route::apiResource('courses', EducationCourseController::class)->only(['store', 'update', 'destroy'])->middleware('role:full_access');
    Route::apiResource('webinars', EducationWebinarController::class)->only(['index', 'show']);
    Route::apiResource('webinars', EducationWebinarController::class)->only(['store', 'update', 'destroy'])->middleware('role:full_access');
    Route::apiResource('tests', EducationTestController::class)->only(['index', 'show']);
    Route::apiResource('tests', EducationTestController::class)->only(['store', 'update', 'destroy'])->middleware('role:full_access');
});

Route::group(['prefix' => 'edo', 'middleware' => 'auth:sanctum'], function () {
    Route::apiResource('events', EdoEventController::class)->only(['index', 'show']);
    Route::apiResource('events', EdoEventController::class)->only(['store', 'update', 'destroy'])->middleware('role:full_access');
    Route::apiResource('courses', EdoCourseController::class)->only(['index', 'show']);
    Route::apiResource('courses', EdoCourseController::class)->only(['store', 'update', 'destroy'])->middleware('role:full_access');
    Route::apiResource('tests', EdoTestController::class)->only(['index', 'show']);
    Route::apiResource('tests', EdoTestController::class)->only(['store', 'update', 'destroy'])->middleware('role:full_access');
});

Route::group(['prefix' => 'mentorship', 'middleware' => 'auth:sanctum'], function () {
    Route::apiResource('adaptation-plan-templates', AdaptationPlanTemplateController::class)->only(['index', 'show']);
    Route::apiResource('adaptation-plan-templates', AdaptationPlanTemplateController::class)->only(['store', 'update', 'destroy'])->middleware('role:full_access');
    Route::get('adaptation-plans/my', [AdaptationPlanController::class, 'my']);
    Route::patch('adaptation-plans/my/days/{dayId}/intern-comment', [AdaptationPlanController::class, 'updateMyInternComment']);
    Route::patch('adaptation-plans/my/days/{dayId}/tasks/{taskId}/status', [AdaptationPlanController::class, 'updateMyTaskStatus']);
    Route::patch('adaptation-plans/{id}/days/{dayId}', [AdaptationPlanController::class, 'updateDay']);
    Route::patch('adaptation-plans/{id}/days/{dayId}/tasks/{taskId}/status', [AdaptationPlanController::class, 'updateTaskStatus']);
    Route::get('adaptation-plans/all', [AdaptationPlanController::class, 'all']);
    Route::apiResource('adaptation-plans', AdaptationPlanController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
});
