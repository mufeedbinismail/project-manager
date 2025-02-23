<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', [\App\Http\Controllers\AuthController::class, 'register'])->name('register');
Route::post('login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login');

Route::middleware('auth:api')->group(function () {
    Route::post('logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');

    Route::apiResource('users', 'UserController');
    Route::apiResource('projects', 'ProjectController');

    // Routes for assigning and un-assigning users to projects
    Route::post('projects/{project}/users', [\App\Http\Controllers\ProjectUserController::class, 'assignUsers'])->name('projects.assign-users');
    Route::delete('projects/{project}/users', [\App\Http\Controllers\ProjectUserController::class, 'unassignUsers'])->name('projects.unassign-users');
});
