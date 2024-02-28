<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\TransactionController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
// USERS
    Route::get('/user', [UserController::class, 'index']);

    Route::post('/user', [UserController::class, 'store']);

    Route::get('/user/{id}', [UserController::class, 'show']);

    // Route::get('/user/edit/{id}', [UserController::class, 'edit']);

    Route::put('/user/edit/{id}', [UserController::class, 'update']);

    Route::delete('/user/delete/{id}', [UserController::class, 'destroy']);

// CATEGORIES

    Route::get('/category/{id_user}', [CategoryController::class, 'index']);

    Route::post('/category/{id_user}', [CategoryController::class, 'store']);

    Route::delete('/category/{id_user}/delete/{id_category}', [CategoryController::class, 'destroy']);

// TRANSACTIONS

    Route::get('/transaction/{id_user}', [TransactionController::class, 'index']);

    Route::post('/transaction/{id_user}', [TransactionController::class, 'store']);

    Route::delete('/transaction/{id_user}/delete/{id_transaction}', [TransactionController::class, 'destroy']);

    Route::get('/transaction/{id_user}', [TransactionController::class, 'index']);


