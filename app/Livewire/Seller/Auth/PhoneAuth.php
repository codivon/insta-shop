<?php

namespace App\Livewire\Seller\Auth;

use App\Models\Seller;
use App\Models\Setting;
use App\Models\Shop;
use App\Services\OtpService;
use App\Support\ActivityLogger;
use App\Support\AdminNotifier;
use App\Support\Phone;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.mobile-auth')]
#[Title('ورود فروشنده')]
class PhoneAuth extends Component
{
    /** login | register */
    public string $mode = 'login';

    /** phone | code */
    public string $step = 'phone';

    public string $phone = '';

    public string $instagram = '';

    public string $code = '';

    public ?string $devCode = null;

    /** null = unchecked, true = taken, false = available (register mode) */
    public ?bool $instagramTaken = null;

    public int $resendIn = 0;

    protected function rules(): array
    {
        if ($this->step === 'code') {
            return ['code' => ['required', 'digits:6']];
        }

        return match ($this->mode) {
            'register' => [
                'phone' => ['required', 'string'],
                'instagram' => ['required', 'string', 'min:2', 'max:80', 'regex:/^[a-zA-Z0-9._]+$/u'],
            ],
            default => [
                'phone' => ['required', 'string'],
            ],
        };
    }

    protected function messages(): array
    {
        return [
            'phone.required' => 'شماره موبایل را وارد کنید.',
            'instagram.required' => 'شناسه اینستاگرام فروشگاه را وارد کنید.',
            'instagram.regex' => 'شناسه اینستاگرام فقط می‌تواند حروف انگلیسی، عدد، نقطه و زیرخط باشد.',
            'instagram.min' => 'شناسه اینستاگرام حداقل ۲ کاراکتر است.',
            'instagram.max' => 'شناسه اینستاگرام حداکثر ۸۰ کاراکتر است.',
            'code.required' => 'کد تأیید را وارد کنید.',
            'code.digits' => 'کد تأیید باید ۶ رقم باشد.',
        ];
    }

    public function updatedInstagram(string $value): void
    {
        $this->instagram = ltrim(strtolower(trim($value)), '@');
        $this->peekInstagram();
    }

    /** Live uniqueness feedback while typing the shop handle. */
    protected function peekInstagram(): void
    {
        $this->instagramTaken = null;

        if ($this->mode !== 'register' || $this->instagram === '') {
            return;
        }

        if (! preg_match('/^[a-zA-Z0-9._]{2,80}$/', $this->instagram)) {
            return; // format errors surface on submit
        }

        $this->instagramTaken = Seller::query()
            ->where('instagram_username', $this->instagram)
            ->exists();
    }

    /** Step 1 — request the OTP (login: any seller phone; register: new seller) */
    public function sendOtp(): void
    {
        $this->resetErrorBag();
        $this->validate();

        $normalized = Phone::normalize($this->phone);

        if (! Phone::isValid($normalized)) {
            $this->addError('phone', 'شماره موبایل ایران را صحیح وارد کنید (مثال: ۰۹۱۲۳۴۵۶۷۸۹).');

            return;
        }

        $purpose = $this->mode === 'register' ? 'register' : 'login';

        if ($this->mode === 'register') {
            $phoneTaken = Seller::where('phone', $normalized)->exists();
            if ($phoneTaken) {
                $this->addError('phone', 'این شماره قبلاً ثبت شده است. وارد شوید یا شماره دیگری بزنید.');

                return;
            }

            $instaTaken = Seller::where('instagram_username', $this->instagram)->exists();
            if ($instaTaken) {
                $this->addError('instagram', 'این شناسه اینستاگرام قبلاً استفاده شده است.');

                return;
            }
        } else {
            // Login — the seller must already exist. Decide BEFORE the code
            // goes out: the user must never see «کد ارسال شد» for a number
            // that can never log in (the old flow only failed at verify()).
            $seller = Seller::where('phone', $normalized)->first();

            if (! $seller) {
                $this->addError('phone', 'فروشنده‌ای با این شماره ثبت نشده است. ابتدا ثبت‌نام کنید.');

                return;
            }

            if ($seller->status === 'suspended') {
                $this->addError('phone', 'حساب فروشندگی شما تعلیق شده است. با پشتیبانی تماس بگیرید.');

                return;
            }
        }

        $result = app(OtpService::class)->send($normalized, 'seller', $purpose);

        if (! $result['ok']) {
            if ($result['retry_after'] !== null) {
                $this->resendIn = $result['retry_after'];
                $this->step = 'code';
                $this->dispatch('otp-countdown', seconds: $result['retry_after']);
            } else {
                $this->addError('phone', $result['error'] ?? 'ارسال کد ناموفق بود.');
            }

            return;
        }

        $this->phone = $normalized;
        $this->devCode = $result['dev_code'];
        $this->resendIn = OtpService::RESEND_SECONDS;
        $this->step = 'code';
        $this->code = '';
        $this->dispatch('otp-countdown', seconds: OtpService::RESEND_SECONDS);
    }

    /** Fill the code box from the on-screen test code (log driver). */
    public function autofillTestCode(): void
    {
        if ($this->devCode === null) {
            return;
        }

        $this->code = $this->devCode;
        $this->resetErrorBag('code');
        $this->dispatch('otp-autofilled');
    }

    /** Step 2 — verify; on register also create the seller record */
    public function verify(): void
    {
        $this->resetErrorBag();
        $this->validate();

        $purpose = $this->mode === 'register' ? 'register' : 'login';
        $result = app(OtpService::class)->verify($this->phone, 'seller', $this->code, $purpose);

        if (! $result['ok']) {
            $this->addError('code', $result['error'] ?? 'کد نامعتبر است.');

            return;
        }

        if ($this->mode === 'register') {
            // «تایید خودکار فروشنده» (batch 79): when the admin enables it,
            // new sellers skip the pending queue entirely.
            $autoApproved = (bool) Setting::get('shops.auto_approve_seller', false);

            $seller = Seller::create([
                'phone' => $this->phone,
                'instagram_username' => $this->instagram,
                'status' => $autoApproved ? 'approved' : 'pending',
                'phone_verified_at' => now(),
                'last_login_at' => now(),
            ]);

            ActivityLogger::log(
                'seller.new',
                'فروشنده جدید ثبت‌نام کرد — @'.$this->instagram.($autoApproved ? ' (تایید خودکار)' : ' (در انتظار تایید)'),
                subject: $seller,
                after: ['status' => $autoApproved ? 'approved' : 'pending'],
            );

            AdminNotifier::notify(
                'seller.new',
                'فروشنده جدید 🏪 @'.$this->instagram,
                $autoApproved ? 'با تایید خودکار فعال شد.' : 'در انتظار تایید شماست.',
                route('admin.users.sellers'),
                'store',
                $seller,
            );
        } else {
            $seller = Seller::where('phone', $this->phone)->first();

            if (! $seller) {
                $this->addError('code', 'فروشنده‌ای با این شماره یافت نشد. ثبت‌نام کنید.');

                return;
            }

            if ($seller->status === 'suspended') {
                $this->addError('code', 'حساب فروشندگی شما تعلیق شده است. با پشتیبانی تماس بگیرید.');

                return;
            }

            $seller->forceFill(['last_login_at' => now()])->save();
        }

        Auth::guard('seller')->login($seller, remember: true);
        session()->regenerate();

        // Every seller owns exactly one shop — create it on first login
        // (sellers may build their catalog while still pending approval).
        Shop::ensureForSeller($seller);

        $this->redirect(route('seller.home'), navigate: true);
    }

    public function setMode(string $mode): void
    {
        $this->mode = $mode === 'register' ? 'register' : 'login';
        $this->step = 'phone';
        $this->code = '';
        $this->devCode = null;
        $this->instagramTaken = null;
        $this->resetErrorBag();
    }

    public function backToPhone(): void
    {
        $this->step = 'phone';
        $this->code = '';
        $this->devCode = null;
        $this->resendIn = 0;
        $this->resetErrorBag();
    }

    /** Countdown tick (wire:poll.1000ms) for the resend window */
    public function tick(): void
    {
        if ($this->resendIn > 0) {
            $this->resendIn--;
        }
    }

    public function render()
    {
        return view('livewire.seller.auth.phone-auth');
    }
}
