<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::prefix('customers')->controller(CustomerController::class)->group(function (): void {
    Route::get('/', 'index');
    Route::get('/{id}', 'show');
    Route::post('/', 'store');
    Route::put('/{id}', 'update');
    Route::put('/{id}/status', 'changeStatus');
    Route::delete('/{id}', 'destroy');
});

Route::prefix('suppliers')->controller(SupplierController::class)->group(function (): void {
    Route::get('/', 'index');
    Route::get('/{id}', 'show');
    Route::post('/', 'store');
    Route::put('/{id}', 'update');
    Route::put('/{id}/status', 'changeStatus');
    Route::delete('/{id}', 'destroy');
});
