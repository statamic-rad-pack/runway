<?php

use DoubleThreeDigital\Runway\Http\Controllers\ResourceActionController;
use DoubleThreeDigital\Runway\Http\Controllers\ResourcesController;
use DoubleThreeDigital\Runway\Http\Controllers\ResourceController;
use DoubleThreeDigital\Runway\Http\Controllers\ResourceListingController;
use Illuminate\Support\Facades\Route;

Route::name('runway.')->prefix('runway')->group(function () {
    Route::get('/', [ResourcesController::class, 'index'])->name('dashboard');
    Route::get('/{resourceHandle}', [ResourceController::class, 'index'])->name('index');

    Route::get('/{resourceHandle}/listing-api', [ResourceListingController::class, 'index'])->name('listing-api');
    Route::post('/{resourceHandle}/actions', [ResourceActionController::class, 'runAction'])->name('actions.run');
    Route::post('/{resourceHandle}/actions/list', [ResourceActionController::class, 'bulkActionsList'])->name('actions.bulk');

    Route::get('/{resourceHandle}/create', [ResourceController::class, 'create'])->name('create');
    Route::post('/{resourceHandle}/create', [ResourceController::class, 'store'])->name('store');
    Route::get('/{resourceHandle}/{record}', [ResourceController::class, 'edit'])->name('edit');
    Route::patch('/{resourceHandle}/{record}', [ResourceController::class, 'update'])->name('update');
    Route::delete('/{resourceHandle}/{record}', [ResourceController::class, 'destroy'])->name('destroy');
});
