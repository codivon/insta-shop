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

    public function mount(): void
    {
        //
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

        $this->redirect(route('shop.home'), navigate: true);
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
