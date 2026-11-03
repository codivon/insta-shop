<?php

use App\Http\Controllers\Admin\BackupDownload;
use App\Http\Controllers\Admin\BackupUpload;
use App\Http\Controllers\Admin\PushSubscriptionController;
use App\Http\Controllers\Auth\PanelLogout;
use App\Http\Controllers\Seller\ProductFileDownload;
use App\Http\Controllers\Shop\PaymentCallback;
use App\Http\Controllers\Shop\PaymentRedirect;
use App\Livewire\Admin\ActivityPage;
use App\Livewire\Admin\BackupsPage;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\DiscountsIndex;
use App\Livewire\Admin\LocationsPage;
use App\Livewire\Admin\Access\AdminsIndex;
use App\Livewire\Admin\Access\RolesIndex;
use App\Livewire\Admin\Auth\Login;
use App\Livewire\Admin\Developer\DeveloperSettings;
use App\Livewire\Admin\Developer\SystemLogs;
use App\Livewire\Admin\FinancePage;
use App\Livewire\Admin\IncomePage;
use App\Livewire\Admin\Notifications\NotificationsPage;
use App\Livewire\Admin\OrdersIndex;
use App\Livewire\Admin\OrderShow;
use App\Livewire\Admin\ProductForm as AdminProductForm;
use App\Livewire\Admin\ProfilePage as AdminProfilePage;
use App\Livewire\Admin\ProductsIndex;
use App\Livewire\Admin\SettlementsPage;
use App\Livewire\Admin\ShopsIndex;
use App\Livewire\Admin\Users\CustomersIndex;
use App\Livewire\Admin\Users\CustomerShow;
use App\Livewire\Admin\Users\SellerShow;
use App\Livewire\Admin\Users\SellersIndex;
use App\Livewire\Admin\WithdrawalsPage;
use App\Livewire\Admin\Settings\SmsSettings;
use App\Livewire\Admin\Settings\SmsTemplatesCenter;
use App\Livewire\Admin\Settings\SettingsHub;
use App\Livewire\Admin\Support\TicketShow;
use App\Livewire\Admin\Support\TicketsIndex;
use App\Livewire\Customer\AddressesPage;
use App\Livewire\Customer\Auth\PhoneLogin;
use App\Livewire\Customer\CartPage;
use App\Livewire\Customer\CategoriesPage;
use App\Livewire\Customer\CheckoutPage;
use App\Livewire\Customer\FavoritesPage;
use App\Livewire\Customer\Home as CustomerHome;
use App\Livewire\Customer\OrderDetailPage;
use App\Livewire\Customer\OrderResultPage;
use App\Livewire\Customer\OrdersPage;
use App\Livewire\Customer\ProductShow;
use App\Livewire\Customer\ProfilePage;
use App\Livewire\Customer\Support\TicketShowPage as CustomerTicketShowPage;
use App\Livewire\Customer\Support\TicketsPage as CustomerTicketsPage;
use App\Livewire\Storefront\StorefrontShow;
use App\Livewire\Seller\AttributeManager;
use App\Livewire\Seller\Auth\PhoneAuth;
use App\Livewire\Seller\BankCardsPage;
use App\Livewire\Seller\CategoryManager;
use App\Livewire\Seller\DiscountForm;
use App\Livewire\Seller\DiscountIndex;
use App\Livewire\Seller\FilteringManager;
use App\Livewire\Seller\GuidePage;
use App\Livewire\Seller\Home;
use App\Livewire\Seller\Notifications\NotificationsPage as SellerNotificationsPage;
use App\Livewire\Seller\OrdersPage as SellerOrdersPage;
use App\Livewire\Seller\ProductForm;
use App\Livewire\Seller\ProductViewsPage;
use App\Livewire\Seller\ProfilePage as SellerProfilePage;
use App\Livewire\Seller\ReportsPage;
use App\Livewire\Seller\ShippingPage;
use App\Livewire\Seller\ShopEdit;
use App\Livewire\Seller\ShopIndex;
use App\Livewire\Seller\Support\TicketShowPage;
use App\Livewire\Seller\Support\TicketsPage;
use App\Livewire\Seller\WalletPage;
use App\Models\Product;
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

    Route::middleware(['panel.auth', 'admin.permission'])->group(function () {
        Route::get('/', Dashboard::class)->name('dashboard');

        // ── Activity trail (فعالیت‌های اخیر — audit log) ──
        Route::get('/activity', ActivityPage::class)->name('activity');

        // ── Notifications (همهٔ اتفاق‌های سایت + پوش موبایل/سیستم) ──
        Route::get('/notifications', NotificationsPage::class)->name('notifications');
        Route::post('/notifications/subscribe', [PushSubscriptionController::class, 'store'])->name('notifications.subscribe');
        Route::delete('/notifications/subscribe', [PushSubscriptionController::class, 'destroy'])->name('notifications.unsubscribe');

        // ── Users management ──
        Route::get('/users/sellers', SellersIndex::class)->name('users.sellers');
        Route::get('/users/sellers/{seller}', SellerShow::class)->name('users.sellers.show');
        Route::get('/users/customers', CustomersIndex::class)->name('users.customers');
        Route::get('/users/customers/{customer}', CustomerShow::class)->name('users.customers.show');

        // ── Catalog management (shops & products) ──
        Route::get('/shops', ShopsIndex::class)->name('shops');
        Route::get('/products', ProductsIndex::class)->name('products');
        Route::get('/products/create', AdminProductForm::class)->name('products.create');
        Route::get('/products/{product}/edit', AdminProductForm::class)->name('products.edit');
        Route::get('/discounts', DiscountsIndex::class)->name('discounts');

        // ── Platform income (کمیسیون‌های دریافتی — batch 81) ──
        Route::get('/income', IncomePage::class)->name('income');

        // ── Finance center: financial reports + settlements + withdrawals ──
        Route::get('/finance', FinancePage::class)->name('finance');
        Route::get('/settlements', SettlementsPage::class)->name('settlements');
        Route::get('/withdrawals', WithdrawalsPage::class)->name('withdrawals');

        // ── Locations (استان‌ها و شهرها — add/remove ONLY here) ──
        Route::get('/locations', LocationsPage::class)->name('locations');

        // ── Admin profile (ویرایش پروفایل مدیر + تغییر رمز) ──
        Route::get('/profile', AdminProfilePage::class)->name('profile');

        // ── Orders management ──
        Route::get('/orders', OrdersIndex::class)->name('orders');
        Route::get('/orders/{order}', OrderShow::class)->name('orders.show');

        // ── Support tickets (تیکت پشتیبانی مشتریان و فروشندگان) ──
        Route::get('/tickets', TicketsIndex::class)->name('tickets');
        Route::get('/tickets/{ticket}', TicketShow::class)->name('tickets.show');

        // ── Backups (پشتیبان‌گیری از کل دیتابیس + بارگذاری/بازگردانی) ──
        Route::get('/backups', BackupsPage::class)->name('backups');
        Route::get('/backups/{backup}/download', BackupDownload::class)->name('backups.download');
        Route::post('/backups/upload', [BackupUpload::class, 'store'])->name('backups.upload');

        // ── Settings hub: ONE sidebar menu, sections rendered inside the
        //    page itself (عمومی / ظاهر / پیامک / درگاه پرداخت). Old
        //    sub-routes redirect here and keep their names so existing
        //    role permissions stay valid.
        Route::get('/settings', SettingsHub::class)->name('settings');

        Route::get('/settings/general', fn () => redirect()->route('admin.settings', ['tab' => 'general']))
            ->name('settings.general');
        Route::get('/settings/appearance', fn () => redirect()->route('admin.settings', ['tab' => 'appearance']))
            ->name('settings.appearance');
        Route::get('/settings/sms', fn () => redirect()->route('admin.settings', ['tab' => 'sms']))
            ->name('settings.sms');

        // ── SMS template center: STANDALONE page (outside the settings
        //    hub sidebar) — reached only from «پیامک و پرووایدر» section.
        Route::get('/settings/sms/templates', SmsTemplatesCenter::class)->name('settings.sms.templates');
        Route::get('/settings/payment', fn () => redirect()->route('admin.settings', ['tab' => 'payment']))
            ->name('settings.payment');

        // ── Managers: admins list (and roles live under access.roles) ──
        Route::get('/admins', AdminsIndex::class)->name('admins');

        // ── Access control: roles & permissions ──
        Route::get('/access/roles', RolesIndex::class)->name('access.roles');

        // ── Developer: settings (cron, maintenance, updates, migrations) ──
        //    Developer pages are super-admin ONLY (سازنده) — they are
        //    intentionally NOT registered in config/admin-permissions.php
        //    so no role can ever grant them.
        Route::get('/developer/settings', DeveloperSettings::class)->name('developer.settings');
        Route::get('/developer/logs', SystemLogs::class)->name('developer.logs');

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
        Route::get('/shop/attributes', AttributeManager::class)->name('shop.attributes');
        Route::get('/products/create', ProductForm::class)->name('products.create');
        Route::get('/products/{product}/edit', ProductForm::class)->name('products.edit');
        Route::get('/categories', CategoryManager::class)->name('categories');

        // ── Downloadable product files (private disk, seller-only) ──
        Route::get('/products/files/{file}/download', ProductFileDownload::class)
            ->name('products.files.download');

        // ── Phase 5: shop orders (read-only) ──
        Route::get('/orders', SellerOrdersPage::class)->name('orders');

        // ── Wallet (balance + deposits from paid orders + withdrawals) ──
        Route::get('/wallet', WalletPage::class)->name('wallet');

        // ── Bank cards (profile + wallet destination accounts) ──
        Route::get('/cards', BankCardsPage::class)->name('cards');

        // ── Shipping methods (روش‌های ارسال فروشگاه) ──
        Route::get('/shipping', ShippingPage::class)->name('shipping');

        // ── Storefront filters (فیلترینگ صفحهٔ فروشگاه) ──
        Route::get('/filters', FilteringManager::class)->name('filters');

        // ── Discount codes (کدهای تخفیف فروشگاه) ──
        Route::get('/discounts', DiscountIndex::class)->name('discounts');
        Route::get('/discounts/create', DiscountForm::class)->name('discounts.create');
        Route::get('/discounts/{discount}/edit', DiscountForm::class)->name('discounts.edit');

        // ── Reports (orders & product-visit analytics) ──
        Route::get('/reports', ReportsPage::class)->name('reports');
        Route::get('/reports/product-views', ProductViewsPage::class)->name('reports.views');

        // ── Seller notifications (فقط اعلان‌های خود فروشنده + پوش موبایل/سیستم) ──
        Route::get('/notifications', SellerNotificationsPage::class)->name('notifications');
        Route::post('/notifications/subscribe', [PushSubscriptionController::class, 'store'])->name('notifications.subscribe');
        Route::delete('/notifications/subscribe', [PushSubscriptionController::class, 'destroy'])->name('notifications.unsubscribe');

        // ── Support tickets (گفتگو با پشتیبانی مدیر) ──
        Route::get('/support', TicketsPage::class)->name('support');
        Route::get('/support/{ticket}', TicketShowPage::class)->name('support.show');

        // ── Seller profile (identity, shortcuts, theme, logout) ──
        Route::get('/profile', SellerProfilePage::class)->name('profile');
        Route::get('/guide', GuidePage::class)->name('guide');

        Route::post('/logout', PanelLogout::class)->name('logout');
    });
});

/*
|--------------------------------------------------------------------------
| Customer shop  (mobile app shell)
|--------------------------------------------------------------------------
| Browsing (خانه، دسته‌بندی، محصول، سبد خرید) is PUBLIC — the login wall
| stands only at checkout, orders, favorites and profile.
*/
Route::prefix('shop')->name('shop.')->group(function () {
    Route::middleware('panel.guest')->group(function () {
        Route::get('/login', PhoneLogin::class)->name('login');
    });

    /*
     | ─────────────────────────────────────────────────────────────
     |  PUBLIC customer-app pages — NO login required.
     |
     |  Guests browse the home feed and categories, open product
     |  pages and fill their cart freely (the guest cart rides a
     |  shared domain cookie); the login wall stands only at
     |  «ادامه و پرداخت» (checkout) and the account pages below.
     | ─────────────────────────────────────────────────────────────
     */
    Route::get('/', CustomerHome::class)->name('home');
    Route::get('/categories', CategoriesPage::class)->name('categories');
    Route::get('/cart', CartPage::class)->name('cart');

    /*
     | Legacy numeric product links (/shop/product/{id}) — 301-redirect
     | to the new slug address دامنه/shop/{آیدی اینستا}/{اسلاگ} so
     | already-shared links keep working.
     */
    Route::get('/product/{productId}', function (int $productId) {
        $product = Product::query()
            ->where('status', 'published')
            ->whereHas('shop', fn ($q) => $q->publiclyVisible())
            ->with(['shop.seller'])
            ->find($productId);

        abort_if(! $product, 404);

        // A shop without any instagram handle has no storefront URL —
        // publicUrl() falls back to THIS route → a redirect to itself
        // would loop forever; 404 instead.
        $target = $product->publicUrl();
        abort_if($target === route('shop.product.show', ['productId' => $productId]), 404);

        return redirect()->to($target, 301);
    })->name('product.show');

    Route::middleware('panel.auth')->group(function () {
        Route::get('/favorites', FavoritesPage::class)->name('favorites');
        Route::get('/profile', ProfilePage::class)->name('profile');
        Route::get('/addresses', AddressesPage::class)->name('addresses');

        // ── Phase 5: checkout & orders ──
        // Login is required HERE (at checkout) — browsing and filling the
        // cart is open to guests.
        Route::get('/checkout', CheckoutPage::class)->name('checkout');
        Route::get('/orders', OrdersPage::class)->name('orders');
        Route::get('/orders/{order}', OrderDetailPage::class)->name('orders.show');

        // ── Support tickets (گفتگو با پشتیبانی مدیر) ──
        Route::get('/support', CustomerTicketsPage::class)->name('support');
        Route::get('/support/{ticket}', CustomerTicketShowPage::class)->name('support.show');

        Route::get('/payment/{order}/go', PaymentRedirect::class)->name('payment.go');
        Route::get('/payment/{order}/result', OrderResultPage::class)->name('payment.result');

        Route::post('/logout', PanelLogout::class)->name('logout');
    });

    /*
     | Gateway callback — public (banks POST/redirect from outside our
     | panel.auth flow; the order is identified by its gateway reference,
     | not by the session). Same browser keeps the customer cookie anyway.
     */
    Route::match(['get', 'post'], '/payment/callback', PaymentCallback::class)->name('payment.callback');

    /*
     | Legacy customer-app shop links (/shop/s/{slug}) — 301-redirect
     | to the new public storefront so already-shared links keep working.
     |
     | MUST be registered BEFORE the catch-all /{instagram}/{slug}
     | product route — otherwise «s» would be treated as an instagram
     | handle and every legacy shop link would 404.
     */
    Route::get('/s/{slug}', function (string $slug) {
        $shop = \App\Models\Shop::query()->publiclyVisible()->where('slug', $slug)->first();

        abort_if(! $shop, 404);

        $handle = $shop->seller?->instagram_username ?: $shop->instagram_username;

        abort_if(! $handle, 404);

        return redirect()->to(route('shop.storefront', ['instagram' => $handle]), 301);
    })->name('store.show');

    /*
     | ─────────────────────────────────────────────────────────────
     |  PUBLIC product page inside a storefront — no login required.
     |
     |  Address shape requested by the seller community:
     |      دامنه/shop/{آیدی اینستا}/{اسلاگ محصول}
     |  Registered AFTER every specific /shop/* route above so real
     |  app pages (cart, checkout, orders, …) always win.
     | ─────────────────────────────────────────────────────────────
     */
    Route::get('/{instagram}/{slug}', ProductShow::class)->name('storefront.product');

    /*
     | ─────────────────────────────────────────────────────────────
     |  PUBLIC seller storefront — no login required.
     |
     |  The seller's shop front page lives at the seller's instagram
     | handle:  دامنه/shop/{آیدی اینستا}
     | (the bare  دامنه/{آیدی اینستا}  form is served by the fallback
     | route at the bottom of this file).
     |
     | Registered AFTER every specific /shop/* route above, so real
     | app pages (cart, profile, …) always win; any other single
     | segment is treated as an instagram handle.
     | ─────────────────────────────────────────────────────────────
     */
    Route::get('/{instagram}', StorefrontShow::class)->name('storefront');
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

/*
|--------------------------------------------------------------------------
| Direct storefront address —  دامنه/{آیدی اینستا}
|--------------------------------------------------------------------------
| The user-preferred, shortest shareable form. The fallback fires ONLY
| when no other route matched, so panels/api/assets are never touched:
| if the segment resolves to a publicly visible shop (by the seller's
| instagram handle) the public storefront is rendered, otherwise a
| regular 404 — exactly like any unknown path.
*/
Route::fallback(StorefrontShow::class);
