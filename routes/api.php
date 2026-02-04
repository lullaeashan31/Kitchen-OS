<?php

use App\Http\Controllers\Api\AttendanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Attendance API
Route::post('/attendance/clock', [AttendanceController::class, 'store']);

// Ingredient Cost Calculation
Route::post('/ingredients/calculate-cost', [\App\Http\Controllers\API\IngredientCostController::class, 'calculate'])->name('ingredients.calculate_cost');
