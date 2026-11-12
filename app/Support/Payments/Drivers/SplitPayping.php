<?php

namespace App\Support\Payments\Drivers;

use Shetabit\Multipay\Drivers\Payping\Payping;
use Shetabit\Multipay\Exceptions\PurchaseFailedException;

/**
 * پی‌پینگ با پرداخت تسهیمی — vendor stays untouched; this subclass rides
 * the config MAP swap (PaymentService::applyRuntimeConfig).
 *
 * When the invoice carries «splitItems», the purchase goes to the SHARED
 * endpoint (POST /v3/pay/shared — official OpenAPI statics.payping.ir/
 * openapi.json) and every seller's share rides along as
 *   items[] = { amount (تومان), userIdentity, description }
 * while the remainder (commission + shipping) stays in the platform
 * merchant account. Amounts are تومان across the whole PayPing v3 API.
 *
 * The recipient (userIdentity) is the seller's registered PayPing
 * identity — InstaShop sends the seller's login mobile. Without the
 * split detail the parent driver runs byte-for-byte as before.
 */
class SplitPayping extends Payping
{
    public function purchase(): string|int|null
    {
        $items = array_values((array) $this->invoice->getDetail('splitItems'));

        if ($items === []) {
            return parent::purchase(); // standard flow — identical behavior
        }

        $data = [
            'amount' => $this->convertAmountToToman($this->invoice->getAmount()),
            'returnUrl' => $this->settings->callbackUrl,
            'payerIdentity' => $this->extractDetail('mobile') ?: $this->extractDetail('email'),
            'payerName' => $this->extractDetail('name'),
            'description' => $this->extractDetail('description'),
            'clientRefId' => $this->invoice->getUuid(),
            'items' => array_map(static fn (array $share): array => [
                'amount' => (int) $share['amount'],
                'userIdentity' => (string) $share['userIdentity'],
                'description' => (string) ($share['description'] ?? ''),
            ], $items),
        ];

        $response = $this->client->request(
            'POST',
            $this->sharedPurchaseUrl(),
            [
                'json' => $data,
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'bearer '.$this->settings->merchantId,
                ],
                'http_errors' => false,
            ]
        );

        $body = @json_decode($response->getBody()->getContents(), true);
        $statusCode = $response->getStatusCode();

        if ($statusCode !== 200) {
            $message = is_array($body) ? array_pop($body) : $this->statusCodeToMessage($statusCode);

            throw new PurchaseFailedException($message);
        }

        $paymentCode = $this->responseValue($body, 'paymentCode');

        if (empty($paymentCode)) {
            throw new PurchaseFailedException($this->statusCodeToMessage($statusCode));
        }

        $this->invoice->transactionId($paymentCode);

        return $this->invoice->getTransactionId();
    }

    /**
     * The vendor's extractResponseValue()/convertStatusCodeToMessage()
     * are PRIVATE — mirror them here (case-insensitive key lookup + the
     * status-code map) so the subclass never reaches into parent privates.
     */
    private function responseValue(mixed $body, string $name): mixed
    {
        if (! is_array($body)) {
            return null;
        }

        $key = array_find(
            array_keys($body),
            static fn ($key): bool => strcasecmp((string) $key, $name) === 0,
        );

        return $key === null ? null : $body[$key];
    }

    private function statusCodeToMessage(int $statusCode): string
    {
        $messages = [
            400 => 'مشکلی در ارسال درخواست وجود دارد',
            401 => 'عدم دسترسی',
            403 => 'دسترسی غیر مجاز',
            404 => 'آیتم درخواستی مورد نظر موجود نمی باشد',
            500 => 'مشکلی در سرور درگاه پرداخت رخ داده است',
            503 => 'سرور درگاه پرداخت در حال حاضر قادر به پاسخگویی نمی باشد',
        ];

        return $messages[$statusCode] ?? 'خطای ناشناخته ای در درگاه پرداخت رخ داده است';
    }

    /** The /v3/pay/shared endpoint — explicit config wins, else derived. */
    private function sharedPurchaseUrl(): string
    {
        $explicit = trim((string) ($this->settings->apiSharedPurchaseUrl ?? ''));

        if ($explicit !== '') {
            return $explicit;
        }

        // https://api.payping.ir/v3/pay → https://api.payping.ir/v3/pay/shared
        $derived = preg_replace('#/pay/?(\?.*)?$#', '/pay/shared', rtrim((string) $this->settings->apiPurchaseUrl, '/'));

        return is_string($derived) && $derived !== '' ? $derived : (string) $this->settings->apiPurchaseUrl;
    }

    /** Parent's extractDetails is private — mirror it here. */
    private function extractDetail(string $name): mixed
    {
        return empty($this->invoice->getDetails()[$name]) ? null : $this->invoice->getDetails()[$name];
    }
}
