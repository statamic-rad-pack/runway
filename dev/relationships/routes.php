<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Statamic\Facades\Entry;
use Statamic\Facades\User;

if (app()->isLocal()) {
    Route::get('__dev/login/{email}', function (Request $request, string $email) {
        $user = User::findByEmail($email);
        abort_unless($user, 404);
        Auth::guard(config('statamic.users.guards.cp'))->login($user);
        $request->session()->regenerate();

        return redirect('/cp');
    });

    Route::get('acceptance-data', fn () => Entry::find('models-acceptance')->toAugmentedArray(['single', 'ordered']));
}

Route::statamic('acceptance', 'acceptance');
