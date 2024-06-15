<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:api')->group(function () {
    Route::get('questions', [UserController::class, 'getQuestions']);
    Route::post('answers', [UserController::class, 'saveAnswers']);
    Route::post('upload-image', [UserController::class, 'uploadImage']);
    Route::get('recommendations', [UserController::class, 'getRecommendations']);
});
