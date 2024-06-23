<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\User\AuthController;
use App\Http\Controllers\Api\User\ForgotPasswordController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [AuthController::class, 'login']);
Route::post('forgot-password', [ForgotPasswordController::class, 'forgot'])->name('manager.forgot-password');
Route::post('reset-password', [ForgotPasswordController::class, 'reset'])->name('manager.reset-password');
Route::middleware(['isUser'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});
