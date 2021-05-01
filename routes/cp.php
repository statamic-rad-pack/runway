<?php

use DoubleThreeDigital\Runway\Http\Controllers\ModelController;
use DoubleThreeDigital\Runway\Http\Controllers\ModelListingButtonController;
use Illuminate\Support\Facades\Route;

Route::name('runway.')->prefix('runway')->group(function () {
    Route::get('/{resourceHandle}', [ModelController::class, 'index'])->name('index');
    Route::post('/{resourceHandle}/listing-buttons', [ModelListingButtonController::class, 'index'])->name('listing-buttons');

    Route::get('/{resourceHandle}/create', [ModelController::class, 'create'])->name('create');
    Route::post('/{resourceHandle}/create', [ModelController::class, 'store'])->name('store');
    Route::get('/{resourceHandle}/{record}', [ModelController::class, 'edit'])->name('edit');
    Route::post('/{resourceHandle}/{record}', [ModelController::class, 'update'])->name('update');
    Route::delete('/{resourceHandle}/{record}', [ModelController::class, 'destroy'])->name('destroy');
});
