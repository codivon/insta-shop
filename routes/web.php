<?php

use App\Http\Controllers\Auth\PanelLogout;
use App\Livewire\Admin\AppearanceSettings;
use App\Livewire\Admin\Auth\Login;
use App\Livewire\Customer\Auth\PhoneLogin;
use App\Livewire\Customer\CartPage;
use App\Livewire\Customer\FavoritesPage;
use App\Livewire\Customer\Home as CustomerHome;
use App\Livewire\Customer\ProductShow;
use App\Livewire\Customer\ShopShow;
use App\Livewire\Seller\Auth\PhoneAuth;
use App\Livewire\Seller\CategoryManager;
use App\Livewire\Seller\Home;
use App\Livewire\Seller\ProductForm;
use App\Livewire\Seller\ShopEdit;
use App\Livewire\Seller\ShopIndex;
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
    Route::middleware('panel.guest')->group(function () {
        Route::get('/login', Login::class)->name('login');
    });

    Route::middleware('panel.auth')->group(function () {
        Route::view('/', 'admin.dashboard')->name('dashboard');

        // Livewire full-page: appearance & theme customization
        Route::get('/settings/appearance', AppearanceSettings::class)
            ->name('settings.appearance');

        Route::post('/logout', PanelLogout::class)->name('logout');
    });
});

/*
|--------------------------------------------------------------------------
| Seller panel  (mobile app shell)  — guard: seller
|--------------------------------------------------------------------------
*/
Route::prefix('seller')->name('seller.')->group(function () {
    Route::middleware('panel.guest')->group(function () {
        Route::get('/login', PhoneAuth::class)->name('login');
    });

    Route::middleware('panel.auth')->group(function () {
        Route::get('/', Home::class)->name('home');

        // ── Phase 3: My Shop (catalog management) ──
        Route::get('/shop', ShopIndex::class)->name('shop');
        Route::get('/shop/edit', ShopEdit::class)->name('shop.edit');
        Route::get('/products/create', ProductForm::class)->name('products.create');
        Route::get('/products/{product}/edit', ProductForm::class)->name('products.edit');
        Route::get('/categories', CategoryManager::class)->name('categories');

        Route::post('/logout', PanelLogout::class)->name('logout');
    });
});

/*
|--------------------------------------------------------------------------
| Customer shop  (mobile app shell)  — guard: customer
|--------------------------------------------------------------------------
*/
Route::prefix('shop')->name('shop.')->group(function () {
    Route::middleware('panel.guest')->group(function () {
        Route::get('/login', PhoneLogin::class)->name('login');
    });

    Route::middleware('panel.auth')->group(function () {
        Route::get('/', CustomerHome::class)->name('home');

        // ── Phase 4: customer storefront ──
        Route::get('/s/{slug}', ShopShow::class)->name('store.show');
        Route::get('/product/{productId}', ProductShow::class)->name('product.show');
        Route::get('/cart', CartPage::class)->name('cart');
        Route::get('/favorites', FavoritesPage::class)->name('favorites');

        Route::post('/logout', PanelLogout::class)->name('logout');
    });
});

/*
|--------------------------------------------------------------------------
| Session-isolation probes (LOCAL ONLY) — health check for panel sessions
|--------------------------------------------------------------------------
*/
if (app()->isLocal()) {
    $probe = fn (string $panel) => function () use ($panel) {
        $prev = session('probe');
        $now = 'hit-'.substr((string) microtime(true), -6);

        session(['probe' => $now]);

        return response()->json([
            'panel' => $panel,
            'cookie' => config('session.cookie'),
            'table' => config('session.table'),
            'previous_hit' => $prev,
            'this_hit' => $now,
        ]);
    };

    Route::get('/admin/__probe', $probe('admin'));
    Route::get('/seller/__probe', $probe('seller'));
    Route::get('/shop/__probe', $probe('customer'));

    // Simulates a Livewire AJAX call: panel is resolved from the
    // X-Instashop-Panel header or the Referer path (not from the URI).
    Route::get('/livewire/__probe', fn () => response()->json([
        'cookie' => config('session.cookie'),
        'table' => config('session.table'),
    ]));
}
