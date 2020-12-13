<?php

use DoubleThreeDigital\Runway\Http\Controllers\ModelController;
use Illuminate\Support\Facades\Route;

Route::name('runway.')->prefix('runway')->group(function () {
    Route::get('/{model}', [ModelController::class, 'index'])->name('index');
    Route::get('/{model}/create', [ModelController::class, 'create'])->name('create');
    Route::post('/{model}/create', [ModelController::class, 'store'])->name('store');
    Route::get('/{model}/{record}', [ModelController::class, 'edit'])->name('edit');
    Route::post('/{model}/{record}', [ModelController::class, 'update'])->name('update');
    Route::delete('/{model}/{record}', [ModelController::class, 'destroy'])->name('destroy');
});
