<?php

use DoubleThreeDigital\Runway\Http\Controllers\CP\ResourceActionController;
use DoubleThreeDigital\Runway\Http\Controllers\CP\ResourceController;
use DoubleThreeDigital\Runway\Http\Controllers\CP\ResourceListingController;
use Illuminate\Support\Facades\Route;

Route::name('runway.')->prefix('runway')->group(function () {
    Route::get('/{resource}', [ResourceController::class, 'index'])->name('index');

    Route::get('{resource}/listing-api', [ResourceListingController::class, 'index'])->name('listing-api');
    Route::post('{resource}/actions', [ResourceActionController::class, 'runAction'])->name('actions.run');
    Route::post('{resource}/actions/list', [ResourceActionController::class, 'bulkActionsList'])->name('actions.bulk');

    Route::get('{resource}/create', [ResourceController::class, 'create'])->name('create');
    Route::post('{resource}/create', [ResourceController::class, 'store'])->name('store');
    Route::get('{resource}/{model}', [ResourceController::class, 'edit'])->name('edit');
    Route::patch('{resource}/{model}', [ResourceController::class, 'update'])->name('update');
});
