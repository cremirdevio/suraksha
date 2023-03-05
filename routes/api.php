<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

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

Route::get('/', function () {
    return response()->json([
        'message' => 'Suraksha Restful Application Programming Interface Version 1'
    ]);
});

// Newsletter
Route::post('/newsletter/subscribe', [\App\Http\Controllers\NewsletterController::class, 'subscribe']);
Route::post('/newsletter/unsubscribe', [\App\Http\Controllers\NewsletterController::class, 'unsubscribe']);

// Authentication Flow
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'create']);
Route::post('/auth/forgot-password', [AuthController::class, 'sendResetLink']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware(['auth:sanctum'])->group(function () {
    // Authentication Flow contd.
    Route::get('/auth/user', [AuthController::class, 'user']);
    Route::post('/auth/token', [AuthController::class, 'token']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Users
    Route::get('/users/me', [UserController::class, 'me']);
    Route::post('/users/email-verification', [UserController::class, 'sendVerification'])->middleware(['throttle:6,1']);
    Route::put('/users', [UserController::class, 'update']);
    Route::post('/users/upload', [UserController::class, 'uploadAvatar']);
    Route::put('/users/password', [UserController::class, 'updatePassword']);
    Route::delete('/users/delete', [UserController::class, 'destroyAccount']);
});

/**
 * @hideFromAPIDocumentation
 */
Route::fallback(function () {
    return response()->json([
        'message' => 'Page not Found. If error persists, contact info@suraksha.com'
    ], 404);
});
