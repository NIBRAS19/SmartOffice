<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\RoleController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);
        Route::post('refresh', [AuthController::class, 'refresh']);
        Route::get('permissions', [AuthController::class, 'permissions']);
    });

    // User routes
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])
            ->middleware('permission:users.view');
        
        Route::post('/', [UserController::class, 'store'])
            ->middleware('permission:users.create');
        
        Route::get('/{user}', [UserController::class, 'show']);
        
        Route::put('/{user}', [UserController::class, 'update']);
        
        Route::delete('/{user}', [UserController::class, 'destroy'])
            ->middleware('permission:users.delete');
        
        Route::post('/{user}/roles', [UserController::class, 'assignRoles'])
            ->middleware('role:admin');
        
        Route::get('/department/{departmentId}', [UserController::class, 'byDepartment'])
            ->middleware('permission:users.view');
    });

    // Department routes
    Route::prefix('departments')->group(function () {
        Route::get('/', [DepartmentController::class, 'index'])
            ->middleware('permission:departments.view');
        
        Route::post('/', [DepartmentController::class, 'store'])
            ->middleware('permission:departments.create');
        
        Route::get('/{department}', [DepartmentController::class, 'show'])
            ->middleware('permission:departments.view');
        
        Route::put('/{department}', [DepartmentController::class, 'update']);
        
        Route::delete('/{department}', [DepartmentController::class, 'destroy'])
            ->middleware('permission:departments.delete');
        
        Route::post('/{department}/assign-manager', [DepartmentController::class, 'assignManager'])
            ->middleware('role:admin');
        
        Route::get('/{department}/statistics', [DepartmentController::class, 'statistics'])
            ->middleware('permission:departments.view');
    });

    // Task routes
    Route::prefix('tasks')->group(function () {
        Route::get('/', [TaskController::class, 'index'])
            ->middleware('permission:tasks.view');
        
        Route::post('/', [TaskController::class, 'store'])
            ->middleware('permission:tasks.create');
        
        Route::get('/my-tasks', [TaskController::class, 'myTasks']);
        
        Route::get('/statistics', [TaskController::class, 'statistics']);
        
        Route::get('/{task}', [TaskController::class, 'show']);
        
        Route::put('/{task}', [TaskController::class, 'update']);
        
        Route::delete('/{task}', [TaskController::class, 'destroy'])
            ->middleware('permission:tasks.delete');
        
        Route::patch('/{task}/complete', [TaskController::class, 'complete']);
        
        Route::patch('/{task}/status', [TaskController::class, 'updateStatus']);
        
        Route::patch('/{task}/reassign', [TaskController::class, 'reassign'])
            ->middleware('permission:tasks.assign');
    });

    // Role routes (Admin only)
    Route::prefix('roles')->middleware('role:admin')->group(function () {
        Route::get('/', [RoleController::class, 'index']);
        Route::post('/', [RoleController::class, 'store']);
        Route::get('/permissions', [RoleController::class, 'permissions']);
        Route::get('/{role}', [RoleController::class, 'show']);
        Route::put('/{role}', [RoleController::class, 'update']);
        Route::delete('/{role}', [RoleController::class, 'destroy']);
        Route::post('/{role}/permissions/attach', [RoleController::class, 'attachPermissions']);
        Route::post('/{role}/permissions/detach', [RoleController::class, 'detachPermissions']);
    });
});

// Fallback route for undefined API endpoints
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint not found',
    ], 404);
});