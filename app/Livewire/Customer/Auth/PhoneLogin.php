<?php

namespace App\Livewire\Customer\Auth;

use App\Models\Customer;
use App\Services\OtpService;
use App\Support\Phone;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.mobile-auth')]
#[Title('ورود مشتری')]
class PhoneLogin extends Component
{
    /** phone | code */
    public string $step = 'phone';

    public string $phone = '';

    public string $code = '';

    public ?string $devCode = null;

    public int $resendIn = 0;

    /** Where the login wall intercepted the visitor (persisted across the OTP steps). */
    public ?string $intent = null;

    public function mount(): void
    {
        // Flash from AuthenticatePanel — grabbed into a component property
        // NOW, because the flash itself would expire before verify() runs.
        $this->intent = session('auth-intent');
    }

    protected function rules(): array
    {
        return $this->step === 'phone'
            ? ['phone' => ['required', 'string']]
            : ['code' => ['required', 'digits:6']];
    }

    protected function messages(): array
    {
        return [
            'phone.required' => 'شماره موبایل را وارد کنید.',
            'phone.string' => 'شماره موبایل نامعتبر است.',
            'code.required' => 'کد تأیید را وارد کنید.',
            'code.digits' => 'کد تأیید باید ۶ رقم باشد.',
        ];
    }

    /** Step 1 — request the OTP */
    public function sendOtp(): void
    {
        $this->resetErrorBag();
        $this->validate();

        $normalized = Phone::normalize($this->phone);

        if (! Phone::isValid($normalized)) {
            $this->addError('phone', 'شماره موبایل ایران را صحیح وارد کنید (مثال: ۰۹۱۲۳۴۵۶۷۸۹).');

            return;
        }

        $result = app(OtpService::class)->send($normalized, 'customer');

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

    /** Step 2 — verify & login (or auto-register) */
    public function verify(): void
    {
        $this->resetErrorBag();
        $this->validate();

        $result = app(OtpService::class)->verify($this->phone, 'customer', $this->code, 'login');

        if (! $result['ok']) {
            $this->addError('code', $result['error'] ?? 'کد نامعتبر است.');

            return;
        }

        $customer = Customer::firstOrCreate(
            ['phone' => $this->phone],
            ['status' => 'active', 'phone_verified_at' => now()],
        );

        if ($customer->status === 'blocked') {
            $this->addError('code', 'حساب شما مسدود شده است. با پشتیبانی تماس بگیرید.');

            return;
        }

        $customer->forceFill([
            'phone_verified_at' => $customer->phone_verified_at ?? now(),
            'last_login_at' => now(),
        ])->save();

        Auth::guard('customer')->login($customer, remember: true);
        session()->regenerate();

        // The guest cart (shared cookie) is merged by MergeGuestCart
        // middleware on the very next request — no action needed here.

        // Return the customer to where the login wall intercepted them
        // (usually «ادامه و پرداخت» / checkout) instead of the home feed.
        // The property is client-tamperable → only same-origin URLs pass.
        $fallback = route('shop.home');
        $intent   = (string) ($this->intent ?? '');

        $this->redirect(
            str_starts_with($intent, url('/').'/') ? $intent : $fallback,
            navigate: true,
        );
    }

    /** Step back to phone entry */
    public function backToPhone(): void
    {
        $this->step = 'phone';
        $this->code = '';
        $this->devCode = null;
        $this->resendIn = 0;
        $this->resetErrorBag();
    }

    public function render()
    {
        return view('livewire.customer.auth.phone-login');
    }
}
