<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TransactionsController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/transactions', [TransactionsController::class, 'index']);
    Route::post('/transactions', [TransactionsController::class, 'store']);
});


Route::post('/broadcasting/auth', function (Request $request) {
    // Authenticate user using token manually
    if ($user = User::query()->where('id', $request->user()->id ?? null)->first()) {
        return Broadcast::auth($request);
    }

    return response()->json(['message' => 'Unauthorized'], 403);
})->middleware('auth:sanctum');
