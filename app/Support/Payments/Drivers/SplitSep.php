<?php

namespace App\Support\Payments\Drivers;

use Shetabit\Multipay\Drivers\SEP\SEP;

/**
 * سپ الکترونیک (سامان) با پرداخت تسهیمی — vendor stays untouched; this
 * subclass rides the config MAP swap (PaymentService::applyRuntimeConfig).
 *
 * When the invoice carries «splitSettlements», the Token request includes
 * the multi-settle parameter documented in the official SEP guide
 * «راهنمای تراکنش تسویه به چند حساب» (نگارش ۱.۰، خرداد ۱۴۰۱):
 *
 *   SettlementIbanInfo[] = { IBAN, Amount (ریال), PurchaseID }
 *   • فقط روش دریافت توکن از این تراکنش پشتیبانی می‌کند (همین مسیر).
 *   • حداکثر طول آرایه ۹ است — SplitPlan هر سفارش با مقصدهای بیشتر را
 *     از پیش به مسیر کیف پول می‌فرستد (fail-closed).
 *   • Amount داخل هر slice ریال است — مثل مبلغ کل با همان نسبت تومان→ریال
 *     درایور وندور (HasIranCurrency) تبدیل می‌شود.
 *   • PurchaseID: تسویهٔ عادی (شبای غیردولتی) باید با ۳۰ کاراکتر «۰»
 *     مقداردهی شود — مستند سپ، «نکات مهم» صفحهٔ آخر.
 *
 * The remainder (commission + shipping) stays with the platform merchant
 * account; without the split detail the parent driver runs byte-for-byte
 * as before. A merchant whose SEP terminal has the multi-settle service
 * DISABLED fails the token request → PaymentService falls back to the
 * standard (wallet) flow automatically.
 */
class SplitSep extends SEP
{
    public function purchase(): string|int|null
    {
        $settlements = array_values((array) $this->invoice->getDetail('splitSettlements'));

        if ($settlements === []) {
            return parent::purchase(); // standard flow — identical behavior
        }

        $data = [
            'action' => 'token',
            'TerminalId' => $this->settings->terminalId,
            'Amount' => $this->convertAmountToRial($this->invoice->getAmount()),
            'ResNum' => $this->invoice->getUuid(),
            'RedirectUrl' => $this->settings->callbackUrl,
            'CellNumber' => $this->invoice->getDetail('mobile') ?? '',
            'ResNum1' => $this->invoice->getDetail('ResNum1') ?? '',
            'ResNum2' => $this->invoice->getDetail('ResNum2') ?? '',
            'ResNum3' => $this->invoice->getDetail('ResNum3') ?? '',
            'ResNum4' => $this->invoice->getDetail('ResNum4') ?? '',
            // تسویه به چند حساب — مستند رسمی سپ (SettlementIbanInfo)
            'SettlementIbanInfo' => array_map(fn (array $share): array => [
                'IBAN' => (string) $share['iban'],
                'Amount' => (int) $this->convertAmountToRial((int) $share['amount']),
                // تسویهٔ عادی (بدون شناسهٔ دولتی) = ۳۰ کاراکتر صفر
                'PurchaseID' => str_repeat('0', 30),
            ], $settlements),
        ];

        $response = $this->client->post($this->settings->apiGetToken, [
            'json' => $data,
        ]);

        if ($response->getStatusCode() !== 200) {
            $this->purchaseFailed(0);
        }

        $responseData = json_decode($response->getBody()->getContents(), true);

        if (($responseData['status'] ?? null) != 1) {
            $this->purchaseFailed((int) ($responseData['errorCode'] ?? 0));
        }

        $this->invoice->transactionId($responseData['token']);

        return $this->invoice->getTransactionId();
    }
}
