<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChargeController;

Route::post('/charges/csv-import', [ChargeController::class, 'csvImport'])->middleware('guest');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
