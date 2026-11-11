<?php

namespace App\Livewire\Seller;

use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Plan;
use App\Models\Seller;
use App\Models\SellerSubscription;
use App\Models\Shop;
use App\Models\Wallet;
use App\Support\ActivityLogger;
use App\Support\Fmt;
use App\Support\Subscription\SubscriptionService;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Seller — «اشتراک و ارتقا» (v0.22.0):
 * status card (active plan, days left, the RESOLVED commission rate),
 * the sellable plans grid and a wallet-purchase confirm modal.
 *
 * Payment today = wallet balance (the commission the seller already
 * earned) or a manual admin grant — the Instagram-seller way. Online
 * gateway checkout for plans can ride on the same service later.
 */
#[Layout('layouts.seller-slot')]
class SubscriptionPage extends Component
{
    use NotifiesOnValidation;

    /* ── purchase confirm modal ── */
    public bool $buyOpen = false;

    public ?int $buyPlanId = null;

    public function openBuy(int $planId): void
    {
        $plan = Plan::query()->where('is_active', true)->find($planId);

        if ($plan === null) {
            return;
        }

        // one-time plan already bought → the seller can never buy it again
        if ($plan->isOneTime()
            && SellerSubscription::query()
                ->where('seller_id', (int) auth('seller')->id())
                ->where('plan_id', $planId)
                ->exists()) {
            $this->addError('buy', 'این پلن «یک‌بارمصرف» است و شما قبلاً یک بار آن را خریداری کرده‌اید.');

            return;
        }

        $this->resetErrorBag();
        $this->buyPlanId = $planId;
        $this->buyOpen = true;
    }

    public function closeBuy(): void
    {
        $this->buyOpen = false;
        $this->buyPlanId = null;
        $this->resetErrorBag();
    }

    /** Confirm modal → debit the wallet and activate. */
    public function confirmBuy(): void
    {
        $seller = auth('seller')->user();

        $plan = Plan::query()->where('is_active', true)->find($this->buyPlanId);

        if ($seller === null || $plan === null) {
            $this->closeBuy();

            return;
        }

        [$ok, $row, $error] = SubscriptionService::purchaseFromWallet($seller, $plan);

        if (! $ok) {
            $this->addError('buy', $error ?? 'موجودی کیف پول شما برای این پلن کافی نیست. با فروش بیشتر، کیف پول‌تان شارژ می‌شود — یا با پشتیبانی تماس بگیرید.');

            return;
        }

        ActivityLogger::log(
            'subscription.purchased',
            'فروشنده @'.($seller->instagram_username ?: $seller->phone).' اشتراک «'.$plan->title.'» را از کیف پول خرید — '.Fmt::money($plan->price),
            subject: $row,
            after: ['plan_id' => $plan->id, 'price' => (int) $plan->price, 'ends_at' => $row->ends_at->toDateTimeString()],
        );

        $this->buyOpen = false;
        $this->buyPlanId = null;

        session()->flash('subscription-flash', 'اشتراک «'.$plan->title.'» فعال شد 🎉 مبلغ از کیف پول شما کسر شد.');
    }

    /* ───────────────────────── data ───────────────────────── */

    protected function seller(): ?Seller
    {
        return auth('seller')->user();
    }

    public function render()
    {
        Shop::ensureForSeller(auth('seller')->user()); // defensive shop bootstrap

        $seller = $this->seller();

        $active = $seller?->activeSubscription();
        $history = SellerSubscription::query()
            ->where('seller_id', (int) $seller?->id)
            ->with('plan:id,title')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        [$commissionType, $commissionValue] = $seller?->resolvedCommission() ?? ['percent', 0];

        $wallet = $seller ? Wallet::ensureForSeller($seller->id) : null;

        // one-time plans the seller has EVER bought — their buy button is locked
        $ownedPlanIds = SellerSubscription::query()
            ->where('seller_id', (int) $seller?->id)
            ->whereNotNull('plan_id')
            ->pluck('plan_id');

        return view('livewire.seller.subscription-page', [
            'active' => $active,
            'history' => $history,
            'plans' => Plan::sellable(),
            'ownedPlanIds' => $ownedPlanIds,
            'walletBalance' => (int) ($wallet?->balance ?? 0),
            'commissionLabel' => $commissionType === 'fixed'
                ? Fmt::money($commissionValue).' ثابت به‌ازای هر سفارش'
                : Fmt::toFa($commissionValue).'٪ از فروش هر سفارش',
            'buyPlan' => $this->buyPlanId ? Plan::find($this->buyPlanId) : null,
        ])->title('اشتراک و ارتقا');
    }
}
