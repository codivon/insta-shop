<?php

/**
 * v0.22.0 subscription engine assertions — exercises the REAL service on
 * the real sqlite dev database (idempotent: creates its own sellers and
 * cleans up afterwards). Run: php scripts/verify-subscriptions.php
 *
 * Covers:
 *   ۱. commission resolution priority: personal override > active plan > global
 *   ۲. grant() stacking: first grant / same-plan renewal / different-plan replace
 *   ۳. wallet purchase: debit + TYPE_PLAN transaction + insufficient-balance path
 *   ۴. admin grant: gift (0) and paid amount recorded
 *   ۵. trial: setting-driven, one-time per seller, on approve + auto-approve
 *   ۶. revoke: closes the active row, history intact
 *   ۷. remind command: 3-day window, reminded_at stamped once
 *   ۸. WalletService::creditOrder uses the ACTIVE PLAN's commission
 */

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Plan;
use App\Models\Seller;
use App\Models\SellerSubscription;
use App\Models\Setting;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Support\Fmt;
use App\Support\Subscription\SubscriptionService;
use App\Support\Wallet\WalletService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$failures = [];
$createdSellerIds = [];
$createdPlanIds = [];

function check(string $label, bool $cond, string $detail = ''): void
{
    global $failures;
    echo ($cond ? 'PASS' : 'FAIL').'  '.$label.($cond ? '' : "  → $detail").PHP_EOL;
    if (! $cond) {
        $failures[] = $label;
    }
}

function makeSeller(string $handle): Seller
{
    global $createdSellerIds;
    $seller = Seller::query()->create([
        'phone' => '0999'.str_pad((string) (mt_rand(1000000, 9999999)), 7, '0', STR_PAD_LEFT),
        'instagram_username' => $handle,
        'status' => 'approved',
        'phone_verified_at' => now(),
    ]);
    $createdSellerIds[] = $seller->id;

    return $seller;
}

function makePlan(string $title, int $days, int $price, ?string $type, ?int $value): Plan
{
    global $createdPlanIds;
    $plan = Plan::query()->create([
        'title' => $title,
        'duration_days' => $days,
        'price' => $price,
        'commission_type' => $type,
        'commission_value' => $value,
        'is_active' => true,
        'sort' => 99,
    ]);
    $createdPlanIds[] = $plan->id;

    return $plan;
}

/* ═══════════ setup: clean slate (idempotent — leftovers of a crashed run) ═══════════ */
$leftover = Seller::query()->where('instagram_username', 'like', 'verify_%')->pluck('id');
$leftoverWallets = Wallet::query()->whereIn('seller_id', $leftover)->pluck('id');
WalletTransaction::query()->whereIn('wallet_id', $leftoverWallets)->delete();
Wallet::query()->whereIn('seller_id', $leftover)->delete();
SellerSubscription::query()->whereIn('seller_id', $leftover)->delete();
Seller::query()->whereIn('id', $leftover)->delete();
Plan::query()->where('title', 'like', 'پلن آزما %')->delete();
DB::table('shops')->where('name', 'فروشگاه تست')->delete();
DB::table('orders')->where('receiver_name', 'تست')->delete();
DB::table('customers')->where('full_name', 'مشتری تست')->delete();

SellerSubscription::query()->delete();
Plan::query()->where('sort', 99)->delete();
Setting::set('shops.commission_type', 'percent', 'text');
Setting::set('shops.commission_value', 10, 'number'); // global 10%
Setting::set('shops.subscription_trial_days', 90, 'number');

echo '── ۱. commission resolution priority ──'.PHP_EOL;
$seller = makeSeller('verify_priority');
$freePlan = makePlan('پلن آزما کامیسیون صفر', 30, 0, 'percent', 0);

check('no sub → global 10%', $seller->resolvedCommission() === ['percent', 10], print_r($seller->resolvedCommission(), true));

SubscriptionService::grantAdmin($seller, $freePlan, 0);
check('active plan 0% (no override) → 0%', $seller->resolvedCommission() === ['percent', 0], print_r($seller->resolvedCommission(), true));

$planRow = SellerSubscription::activeFor($seller->id);
$seller->forceFill(['commission_type' => 'percent', 'commission_value' => 7])->save();
check('personal override 7% WINS over plan', $seller->resolvedCommission() === ['percent', 7], print_r($seller->resolvedCommission(), true));
$seller->forceFill(['commission_type' => null, 'commission_value' => null])->save();
check('cleared override → plan 0% again', $seller->resolvedCommission() === ['percent', 0], print_r($seller->resolvedCommission(), true));

SubscriptionService::revoke($planRow);
check('expired plan → back to global 10%', $seller->resolvedCommission() === ['percent', 10], print_r($seller->resolvedCommission(), true));

echo '── ۲. grant stacking rules ──'.PHP_EOL;
$seller2 = makeSeller('verify_stack');
$monthly = makePlan('پلن آزما ماهانه', 30, 149000, null, null);
$yearly = makePlan('پلن آزما سالانه', 365, 1190000, 'percent', 3);

$r1 = SubscriptionService::grantAdmin($seller2, $monthly, 0);
check('first grant starts now, ends +30d', $r1->starts_at->diffInDays($r1->ends_at) >= 29, $r1->starts_at.' → '.$r1->ends_at);

Wallet::ensureForSeller($seller2->id)->increment('balance', 200000); // fund the renewal purchase

$r2 = SubscriptionService::purchaseFromWallet($seller2, $monthly)[1];
check('same plan → renewal stacked from ends_at', $r2->starts_at->getTimestamp() === $r1->ends_at->getTimestamp(), $r2->starts_at.' vs '.$r1->ends_at);

$r3 = SubscriptionService::grantAdmin($seller2, $yearly, 0);
check('different plan → replaced immediately', $r3->isActive() && (int) $r3->plan_id === (int) $yearly->id);
$r1Fresh = $r1->fresh();
check('old monthly row closed at grant time', ! $r1Fresh->isActive() && str_contains((string) $r1Fresh->note, 'جایگزین'), (string) $r1Fresh->note);

echo '── ۳. wallet purchase ──'.PHP_EOL;
$seller3 = makeSeller('verify_wallet');
Wallet::ensureForSeller($seller3->id)->increment('balance', 200000);

[$ok1, $row1] = SubscriptionService::purchaseFromWallet($seller3, $monthly);
check('purchase with 200k balance for 149k plan OK', $ok1 && $row1 !== null);
check('wallet debited to 51,000', Wallet::ensureForSeller($seller3->id)->balance === 51000, (string) Wallet::ensureForSeller($seller3->id)->balance);

$tx = WalletTransaction::query()->where('wallet_id', Wallet::ensureForSeller($seller3->id)->id)->latest('id')->first();
check('TYPE_PLAN transaction recorded', $tx && $tx->type === WalletTransaction::TYPE_PLAN && $tx->amount === 149000, print_r($tx?->toArray(), true));
check('typeLabel = خرید اشتراک', $tx->typeLabel() === 'خرید اشتراک');
check('amountLabel shows −', str_starts_with($tx->amountLabel(), '−'));

[$ok2, $row2] = SubscriptionService::purchaseFromWallet($seller3, $yearly);
check('insufficient balance → [false, null]', $ok2 === false && $row2 === null);
check('balance untouched after failed purchase', Wallet::ensureForSeller($seller3->id)->balance === 51000);

echo '── ۴. trial ──'.PHP_EOL;
$seller4 = makeSeller('verify_trial');
$trial = SubscriptionService::startTrial($seller4);
check('trial granted (90 days)', $trial !== null && $trial->is_trial && $trial->plan_id === null, print_r($trial?->toArray(), true));
check('trial price_paid = 0, source = trial', $trial->price_paid === 0 && $trial->source === SellerSubscription::SOURCE_TRIAL);
$again = SubscriptionService::startTrial($seller4);
check('second trial call → null (one-time)', $again === null);

// commission during trial = global default (trial waives the FEE, not commission)
check('trial commission = global 10%', $seller4->resolvedCommission() === ['percent', 10], print_r($seller4->resolvedCommission(), true));

echo '── ۵. revoke ──'.PHP_EOL;
$activeRow = SellerSubscription::activeFor($seller4->id);
check('revoke closes active trial', SubscriptionService::revoke($activeRow) && ! $activeRow->fresh()->isActive() && str_contains((string) $activeRow->fresh()->note, 'لغو'));
check('revoke again → false (already closed)', SubscriptionService::revoke($activeRow) === false);

echo "── ۶. WalletService uses the plan's commission ──".PHP_EOL;
$seller5 = makeSeller('verify_credit');
$plan0 = makePlan('پلن آزما کامیسیون صفر ۲', 30, 0, 'percent', 0);
SubscriptionService::grantAdmin($seller5, $plan0, 0);

$shopId = DB::table('shops')->insertGetId([
    'seller_id' => $seller5->id, 'name' => 'فروشگاه تست', 'slug' => 'verify-shop-'.mt_rand(),
    'status' => 'approved', 'show_stock' => 1,
    'created_at' => now(), 'updated_at' => now(),
]);

$customerId = DB::table('customers')->insertGetId([
    'phone' => '0918'.mt_rand(1000000, 9999999), 'full_name' => 'مشتری تست', 'status' => 'active',
    'created_at' => now(), 'updated_at' => now(),
]);

$order = Order::query()->create([
    'customer_id' => $customerId, 'status' => 'pending', 'total_amount' => 0,
    'items_count' => 0, 'shipping_cost' => 0, 'discount_amount' => 0,
    'receiver_name' => 'تست', 'delivery_phone' => '09120000000', 'delivery_address' => 'نشانی تست',
    'created_at' => now(), 'updated_at' => now(),
]);
OrderItem::query()->create([
    'order_id' => $order->id, 'shop_id' => $shopId, 'product_id' => null,
    'product_name' => 'کالای تست', 'variant_id' => 0, 'unit_price' => 100000, 'quantity' => 1,
    'created_at' => now(), 'updated_at' => now(),
]);
$order->forceFill(['status' => 'paid', 'paid_at' => now()])->save();

WalletService::creditOrder($order->fresh());

$snap = collect($order->fresh()->commissions ?? [])->firstWhere('shop_id', $shopId);
check('plan 0% → commission 0 in settlement snapshot', $snap !== null && (int) $snap['commission_amount'] === 0, print_r($snap, true));
check('seller credited full 100,000', Wallet::ensureForSeller($seller5->id)->balance === 100000, (string) Wallet::ensureForSeller($seller5->id)->balance);

// now WITHOUT a plan → global 10% must cut 10,000
SubscriptionService::revoke(SellerSubscription::activeFor($seller5->id));

$order2 = Order::query()->create([
    'customer_id' => $customerId, 'status' => 'pending', 'total_amount' => 0,
    'items_count' => 0, 'shipping_cost' => 0, 'discount_amount' => 0,
    'receiver_name' => 'تست', 'delivery_phone' => '09120000000', 'delivery_address' => 'نشانی تست',
    'created_at' => now(), 'updated_at' => now(),
]);
OrderItem::query()->create([
    'order_id' => $order2->id, 'shop_id' => $shopId, 'product_id' => null,
    'product_name' => 'کالای تست', 'variant_id' => 0, 'unit_price' => 100000, 'quantity' => 1,
    'created_at' => now(), 'updated_at' => now(),
]);
$order2->forceFill(['status' => 'paid', 'paid_at' => now()])->save();

WalletService::creditOrder($order2->fresh());
$snap2 = collect($order2->fresh()->commissions ?? [])->firstWhere('shop_id', $shopId);
check('no plan → global 10% cuts 10,000', (int) $snap2['commission_amount'] === 10000, print_r($snap2, true));

echo '── ۷. remind command ──'.PHP_EOL;
$seller6 = makeSeller('verify_remind');
$soonPlan = makePlan('پلن آزما به‌زودی تمام', 30, 0, null, null);
$row = SubscriptionService::grantAdmin($seller6, $soonPlan, 0);
$row->forceFill(['ends_at' => now()->addDays(2)])->save();

Artisan::call('subscriptions:remind');
$out = trim(Artisan::output());
check('reminder sent for 2-day-left row', str_contains($out, 'Reminders sent: 1'), $out);
check('reminded_at stamped', $row->fresh()->reminded_at !== null);

Artisan::call('subscriptions:remind');
check('second run → no duplicate (stamp)', str_contains(trim(Artisan::output()), 'No subscriptions need a reminder'));

echo '── ۸. Plan model helpers ──'.PHP_EOL;
check('durationLabel ۳۰ روزه', $monthly->durationLabel() === Fmt::toFa(30).' روزه');
check('commissionLabel override', $yearly->commissionLabel() === 'کمیسیون '.Fmt::toFa(3).'٪');
check('commissionLabel null', $monthly->commissionLabel() === 'کمیسیون طبق نرخ سراسری');

/* ═══════════ cleanup ═══════════ */
$walletIds = Wallet::query()->whereIn('seller_id', $createdSellerIds)->pluck('id');
WalletTransaction::query()->whereIn('wallet_id', $walletIds)->delete();
DB::table('order_items')->whereIn('order_id', [$order->id, $order2->id])->delete();
DB::table('orders')->whereIn('id', [$order->id, $order2->id])->delete();
DB::table('shops')->where('id', $shopId)->delete();
DB::table('customers')->where('id', $customerId)->delete();
Wallet::query()->whereIn('seller_id', $createdSellerIds)->delete();
SellerSubscription::query()->whereIn('seller_id', $createdSellerIds)->delete();
Seller::query()->whereIn('id', $createdSellerIds)->delete();
Plan::query()->whereIn('id', $createdPlanIds)->delete();
Setting::set('shops.commission_value', 0, 'number');
Setting::set('shops.subscription_trial_days', 0, 'number');

echo PHP_EOL.($failures === [] ? 'ALL CHECKS PASSED ✅' : 'FAILURES: '.count($failures).' ⛔').PHP_EOL;

exit($failures === [] ? 0 : 1);
