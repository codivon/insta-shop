<?php

namespace App\Livewire\Seller\Auth;

use App\Models\Seller;
use App\Models\Shop;
use App\Services\OtpService;
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
            $seller = Seller::create([
                'phone' => $this->phone,
                'instagram_username' => $this->instagram,
                'status' => 'pending',
                'phone_verified_at' => now(),
                'last_login_at' => now(),
            ]);
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
