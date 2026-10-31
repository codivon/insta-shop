<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Landing (project introduction)
|--------------------------------------------------------------------------
*/
Route::view('/', 'landing')->name('landing');

/*
|--------------------------------------------------------------------------
| Admin panel  (desktop-first, responsive)  — guard: admin
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {
    Route::view('/', 'admin.dashboard')->name('dashboard');

    // Livewire full-page: appearance & theme customization
    Route::get('/settings/appearance', \App\Livewire\Admin\AppearanceSettings::class)
        ->name('settings.appearance');
});

/*
|--------------------------------------------------------------------------
| Seller panel  (mobile app shell)  — guard: seller
|--------------------------------------------------------------------------
*/
Route::prefix('seller')->name('seller.')->group(function () {
    Route::view('/', 'seller.home')->name('home');
});

/*
|--------------------------------------------------------------------------
| Customer shop  (mobile app shell)  — guard: customer
|--------------------------------------------------------------------------
*/
Route::prefix('shop')->name('shop.')->group(function () {
    Route::view('/', 'customer.home')->name('home');
});

/*
|--------------------------------------------------------------------------
| Session-isolation probes (LOCAL ONLY) — health check for panel sessions
|--------------------------------------------------------------------------
*/
if (app()->isLocal()) {
    $probe = fn (string $panel) => function () use ($panel) {
        $prev = session('probe');
        $now  = 'hit-'.substr((string) microtime(true), -6);

        session(['probe' => $now]);

        return response()->json([
            'panel'        => $panel,
            'cookie'       => config('session.cookie'),
            'table'        => config('session.table'),
            'previous_hit' => $prev,
            'this_hit'     => $now,
        ]);
    };

    Route::get('/admin/__probe', $probe('admin'));
    Route::get('/seller/__probe', $probe('seller'));
    Route::get('/shop/__probe', $probe('customer'));
}
