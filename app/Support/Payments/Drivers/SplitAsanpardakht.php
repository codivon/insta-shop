<?php

namespace App\Support\Payments\Drivers;

use Shetabit\Multipay\Drivers\Asanpardakht\Asanpardakht;

/**
 * آسان‌پرداخت با تسهیم — vendor stays untouched; this subclass rides the
 * config MAP swap (PaymentService::applyRuntimeConfig).
 *
 * When the invoice carries «splitServices», the Token request (POST
 * /v1/Token) includes the seller share slices and each slice carries its
 * own IBAN; the remainder (commission + shipping) stays with the
 * merchant. The vendor driver's own error table proves the sharing API
 * exists server-side: 482 «settlement slices does not match total
 * amount», 483 «unregistered iban», 491 «too many settlement
 * destinations», 578 «no default sharing is defined for merchant» —
 * i.e. the IBANs must be pre-registered/confirmed for the merchant
 * inside the AsanPardakht panel, otherwise the purchase fails and
 * PaymentService falls back to the standard (wallet) flow.
 *
 * Amounts arrive in تومان from SplitPlan and are converted to ریال with
 * the same currency ratio the parent uses for the invoice total.
 */
class SplitAsanpardakht extends Asanpardakht
{
    public function token(): array
    {
        $services = array_values((array) $this->invoice->getDetail('splitServices'));

        if ($services === []) {
            return parent::token(); // standard flow — identical behavior
        }

        if (str_contains($this->settings->callbackUrl, '?')) {
            $query = '&'.http_build_query(['invoice' => $this->invoice->getUuid()]);
        } else {
            $query = '?'.http_build_query(['invoice' => $this->invoice->getUuid()]);
        }

        return $this->callApi('POST', self::TokenURL, [
            'serviceTypeId' => 1,
            'merchantConfigurationId' => $this->settings->merchantConfigID,
            'localInvoiceId' => $this->invoice->getUuid(),
            'amountInRials' => $this->convertAmountToRial($this->invoice->getAmount()),
            'localDate' => $this->getTime()['content'],
            'callbackURL' => $this->settings->callbackUrl.$query,
            'paymentId' => '0',
            'additionalData' => '',
            'services' => array_map(fn (array $share): array => [
                'serviceName' => (string) $share['serviceName'],
                'amount' => (int) $this->convertAmountToRial((int) $share['amount']),
                'iban' => (string) $share['iban'],
            ], $services),
        ]);
    }
}
