<?php

namespace App\Support\Payments\Drivers;

use Shetabit\Multipay\Drivers\TorobPay\TorobPay;
use Shetabit\Multipay\Invoice;

/**
 * v0.30.0 — TorobPay (ترب‌پی) driver with the RIAL conversion the vendor
 * class forgets INSIDE the cartList.
 *
 * WHY THIS SUBCLASS EXISTS (accuracy report): the vendor TorobPay driver
 * converts ONLY the top-level `amount` with convertAmountToRial() (TOMAN
 * ×10) but builds cartList from RAW invoice amounts (getAmount() +
 * cartItems as passed) — so with the app-wide TOMAN convention the payload
 * would carry `amount` in RIAL and `cartList.totalAmount` in TOMAN at the
 * same time. TorobPay cross-checks the two (the official WooCommerce plugin
 * re-compares response.amount against the request) — sending a mixed-unit
 * payload risks a purchase rejection or a silently wrong financed amount.
 *
 * This subclass keeps the app convention (invoice amounts in TOMAN) and
 * converts the whole cartList to RIAL, exactly like the vendor SnappPay
 * normalizerCartList() does for its own payload.
 */
class BnplTorobPay extends TorobPay
{
    /**
     * RIAL-consistent cartList: totalAmount/tax/shipping per cart + every
     * cartItem amount converted from TOMAN (the invoice convention) to
     * RIAL (the gateway convention).
     */
    protected function generateCartList(): array
    {
        $carts = parent::generateCartList();

        foreach ($carts as &$cart) {
            foreach (['totalAmount', 'tax_amount', 'shipping_amount'] as $key) {
                if (isset($cart[$key])) {
                    $cart[$key] = $this->convertAmountToRial((int) $cart[$key]);
                }
            }

            foreach ($cart['cartItems'] ?? [] as &$item) {
                if (isset($item['amount'])) {
                    $item['amount'] = $this->convertAmountToRial((int) $item['amount']);
                }
            }
        }

        return $carts;
    }
}
