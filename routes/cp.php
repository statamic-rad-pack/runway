<?php

use Illuminate\Support\Facades\Route;
use StatamicRadPack\Runway\Http\Controllers\CP\PublishedModelsController;
use StatamicRadPack\Runway\Http\Controllers\CP\ResourceActionController;
use StatamicRadPack\Runway\Http\Controllers\CP\ResourceController;
use StatamicRadPack\Runway\Http\Controllers\CP\ResourceListingController;
use StatamicRadPack\Runway\Http\Controllers\CP\ResourceRevisionsController;
use StatamicRadPack\Runway\Http\Controllers\CP\RestoreResourceRevisionController;

Route::name('runway.')->prefix('runway')->group(function () {
    Route::get('/{resource}', [ResourceController::class, 'index'])->name('index');

    Route::get('{resource}/listing-api', [ResourceListingController::class, 'index'])->name('listing-api');
    Route::post('{resource}/actions', [ResourceActionController::class, 'runAction'])->name('actions.run');
    Route::post('{resource}/actions/list', [ResourceActionController::class, 'bulkActionsList'])->name('actions.bulk');

    Route::get('{resource}/create', [ResourceController::class, 'create'])->name('create');
    Route::post('{resource}/create', [ResourceController::class, 'store'])->name('store');
    Route::get('{resource}/{model}', [ResourceController::class, 'edit'])->name('edit');
    Route::patch('{resource}/{model}', [ResourceController::class, 'update'])->name('update');

    Route::group(['prefix' => '{resource}/{model}'], function () {
        Route::post('publish', [PublishedModelsController::class, 'store'])->name('published.store');
        Route::post('unpublish', [PublishedModelsController::class, 'destroy'])->name('published.destroy');

        Route::resource('revisions', ResourceRevisionsController::class, [
            'as' => 'revisions',
            'only' => ['index', 'store', 'show'],
        ])->names([
            'index' => 'revisions.index',
            'store' => 'revisions.store',
            'show' => 'revisions.show',
        ])->parameters(['revisions' => 'revisionId']);

        Route::post('restore-revision', RestoreResourceRevisionController::class)->name('restore-revision');
    });
});
