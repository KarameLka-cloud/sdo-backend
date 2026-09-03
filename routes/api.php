<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\EmployeeDirectory\EmployeeDirectoryController;
use App\Http\Controllers\LearningItemController;
use App\Http\Controllers\Mentorship\AdaptationPlanController;
use App\Http\Controllers\Mentorship\AdaptationPlanTemplateController;
use App\Http\Controllers\User\DepartmentController;
use App\Http\Controllers\User\PositionController;
use App\Http\Controllers\User\RoleController;
use App\Http\Controllers\User\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('login', LoginController::class)->middleware('throttle:5,1');
    Route::post('logout', LogoutController::class)->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('users')->group(function () {
        Route::get('me', [UserController::class, 'me']);

        // Staff directory: interns have no role and must not see the roster.
        Route::middleware('role:view_users')->group(function () {
            Route::get('/', [UserController::class, 'index']);
            Route::get('mentors', [UserController::class, 'mentors']);
            Route::get('department-heads', [UserController::class, 'departmentHeads']);
        });

        Route::middleware('role:full_access')->group(function () {
            Route::get('roles', [RoleController::class, 'index']);
            Route::post('assign-role', [RoleController::class, 'assignRole']);
            Route::post('revoke-role', [RoleController::class, 'revokeRole']);
        });

        Route::apiResource('departments', DepartmentController::class)->only(['index', 'show']);
        Route::apiResource('positions', PositionController::class)->only(['index', 'show']);
    });

    // Everyone reads the catalogue; only admins change it.
    Route::apiResource('learning-items', LearningItemController::class)
        ->parameters(['learning-items' => 'learningItem'])
        ->middlewareFor(['store', 'update', 'destroy'], 'role:full_access');

    // LDAP lookups are expensive, so the directory gets its own rate limit.
    Route::get('employees/search', [EmployeeDirectoryController::class, 'search'])
        ->middleware('throttle:30,1');

    Route::prefix('mentorship')->group(function () {
        Route::apiResource('adaptation-plan-templates', AdaptationPlanTemplateController::class)
            ->parameters(['adaptation-plan-templates' => 'adaptationPlanTemplate'])
            ->middlewareFor(['store', 'update', 'destroy'], 'role:full_access');

        // Intern-facing routes; declared before the {adaptationPlan} resource
        // so that "my" is not captured as an id.
        Route::prefix('adaptation-plans/my')->group(function () {
            Route::get('/', [AdaptationPlanController::class, 'my']);
            Route::patch('days/{dayId}/intern-comment', [AdaptationPlanController::class, 'updateMyInternComment']);
            Route::patch('days/{dayId}/tasks/{taskId}/status', [AdaptationPlanController::class, 'updateMyTaskStatus']);
        });

        Route::patch(
            'adaptation-plans/{adaptationPlan}/days/{dayId}',
            [AdaptationPlanController::class, 'updateDay']
        );
        Route::patch(
            'adaptation-plans/{adaptationPlan}/days/{dayId}/tasks/{taskId}/status',
            [AdaptationPlanController::class, 'updateTaskStatus']
        );

        Route::apiResource('adaptation-plans', AdaptationPlanController::class)
            ->parameters(['adaptation-plans' => 'adaptationPlan']);
    });
});
