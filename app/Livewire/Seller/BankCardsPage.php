<?php

namespace App\Livewire\Seller;

use App\Models\BankCard;
use App\Support\Banking;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Seller — کارت‌های بانکی (bank cards):
 * reached from the profile AND the wallet. Lists the seller's cards as
 * visual bank cards, add-card modal (full name / card number / sheba
 * with Luhn + mod-97 validation), delete + set-default. The default
 * card pre-fills the wallet withdrawal sheba.
 */
#[Layout('layouts.seller-slot')]
class BankCardsPage extends Component
{
    public const MAX_CARDS = 5;

    /* ── add-card modal ── */
    public bool $addOpen = false;

    public string $cardName = '';

    /** Display value — «6037 6912 3456 7890» (groups of 4, as the user types). */
    public string $cardNumber = '';

    /** Numeric part of the sheba ONLY (۲۴ رقم) — the «IR» prefix lives outside the input. */
    public string $shebaDigits = '';

    public ?int $confirmDeleteId = null;

    /* ───────────────────────── modal ───────────────────────── */

    public function openAdd(): void
    {
        if ($this->cardsCount() >= self::MAX_CARDS) {
            session()->flash('cards-flash', 'حداکثر '.self::MAX_CARDS.' کارت می‌توانید اضافه کنید.');

            return;
        }

        $this->resetErrorBag();
        $this->cardName     = '';
        $this->cardNumber   = '';
        $this->shebaDigits  = '';
        $this->addOpen      = true;
    }

    public function closeAdd(): void
    {
        $this->addOpen     = false;
        $this->cardName    = '';
        $this->cardNumber  = '';
        $this->shebaDigits = '';
        $this->resetErrorBag();
    }

    /* ─────────────────── live input hygiene ─────────────────── */

    /**
     * Live 4-4-4-4 grouping WHILE TYPING: the input always shows
     * «6037 6912 …», the raw digits are re-joined on submit and
     * stored un-spaced in the database.
     */
    public function updatedCardNumber(mixed $value): void
    {
        $digits = Banking::digitsOnly((string) $value);

        $this->cardNumber = trim(implode(' ', str_split($digits, 4)));
    }

    /** Only the 24-digit part is typed here — «IR» is a fixed prefix outside the field. */
    public function updatedShebaDigits(mixed $value): void
    {
        $this->shebaDigits = mb_substr(Banking::digitsOnly((string) $value), 0, 24);
    }

    /* ───────────────────────── actions ───────────────────────── */

    public function submitCard(): void
    {
        $seller = auth('seller')->user();

        // belt & suspenders: updated* hooks normalize live input, but
        // normalize again server-side before validating.
        $this->cardName    = trim($this->cardName);
        $this->cardNumber  = Banking::digitsOnly($this->cardNumber); // ← DB stores it un-spaced
        $this->shebaDigits = Banking::digitsOnly($this->shebaDigits);

        $fullSheba = 'IR'.$this->shebaDigits;

        $this->validate([
            'cardName'    => ['required', 'string', 'min:3', 'max:60'],
            'cardNumber'  => ['required', 'digits:16', Rule::unique('bank_cards', 'card_number')->where('seller_id', $seller->id)],
            'shebaDigits' => ['required', 'digits:24'],
        ], [
            'cardName.required'      => 'نام و نام خانوادگی صاحب کارت را وارد کنید.',
            'cardName.min'           => 'نام باید حداقل ۳ حرف باشد.',
            'cardNumber.required'    => 'شماره کارت را وارد کنید.',
            'cardNumber.digits'      => 'شماره کارت باید ۱۶ رقم باشد.',
            'cardNumber.unique'      => 'این شماره کارت قبلاً ثبت شده است.',
            'shebaDigits.required'   => 'رقم‌های شبا را وارد کنید.',
            'shebaDigits.digits'     => 'بعد از IR باید دقیقاً ۲۴ رقم وارد شود.',
        ]);

        if (! Banking::validCard($this->cardNumber)) {
            $this->addError('cardNumber', 'شماره کارت معتبر نیست (رقم کنترل لان اشتباه است).');

            return;
        }

        if (! Banking::validSheba($fullSheba)) {
            $this->addError('shebaDigits', 'شماره شبا معتبر نیست — رقم‌های کنترل را بررسی کنید.');

            return;
        }

        if (BankCard::query()->where('seller_id', $seller->id)->where('sheba', $fullSheba)->exists()) {
            $this->addError('shebaDigits', 'این شماره شبا قبلاً ثبت شده است.');

            return;
        }

        if ($this->cardsCount() >= self::MAX_CARDS) {
            $this->addError('cardNumber', 'حداکثر '.self::MAX_CARDS.' کارت می‌توانید اضافه کنید.');

            return;
        }

        $isFirst = $this->cardsCount() === 0;

        BankCard::query()->create([
            'seller_id'   => $seller->id,
            'full_name'   => trim($this->cardName),
            'card_number' => $this->cardNumber, // raw 16 digits, no spaces
            'sheba'       => $fullSheba,        // stored as IR + 24 digits
            'is_default'  => $isFirst,
        ]);

        unset($this->cards); // bust the per-request computed cache

        $this->closeAdd();

        session()->flash('cards-flash', 'کارت بانکی ثبت شد. ✅');
    }

    public function askDelete(int $cardId): void
    {
        $this->confirmDeleteId = $cardId;
    }

    public function cancelDelete(): void
    {
        $this->confirmDeleteId = null;
    }

    public function deleteCard(int $cardId): void
    {
        $seller = auth('seller')->user();

        $card = BankCard::query()
            ->where('seller_id', $seller->id)
            ->find($cardId);

        if ($card) {
            $wasDefault = $card->is_default;
            $card->delete();

            // promote the first remaining card when the default is gone
            if ($wasDefault) {
                BankCard::query()
                    ->where('seller_id', $seller->id)
                    ->orderBy('id')
                    ->first()
                    ?->update(['is_default' => true]);
            }
        }

        unset($this->cards);

        $this->confirmDeleteId = null;

        session()->flash('cards-flash', 'کارت حذف شد.');
    }

    public function makeDefault(int $cardId): void
    {
        $seller = auth('seller')->user();

        BankCard::query()
            ->where('seller_id', $seller->id)
            ->update(['is_default' => false]);

        BankCard::query()
            ->where('seller_id', $seller->id)
            ->whereKey($cardId)
            ->update(['is_default' => true]);

        unset($this->cards);

        session()->flash('cards-flash', 'کارت پیش‌فرض برداشت تغییر کرد. ✅');
    }

    /* ───────────────────────── data ───────────────────────── */

    protected function cardsCount(): int
    {
        // direct count (not the cached computed) — submitCard calls this
        // BEFORE creating, and the fresh number must not poison the render.
        return BankCard::query()->where('seller_id', auth('seller')->id())->count();
    }

    /** Raw typed digits of the card number (without spaces). */
    public function getCardDigitsProperty(): string
    {
        return Banking::digitsOnly($this->cardNumber);
    }

    /** Luhn result for the live ✓/✗ badge (null = not complete yet). */
    public function getCardValidProperty(): ?bool
    {
        if (strlen($this->cardDigits) !== 16) {
            return null;
        }

        return Banking::validCard($this->cardDigits);
    }

    /** mod-97 result for the live ✓/✗ badge (null = not complete yet). */
    public function getShebaValidProperty(): ?bool
    {
        if (strlen($this->shebaDigits) !== 24) {
            return null;
        }

        return Banking::validSheba('IR'.$this->shebaDigits);
    }

    public function getCardsProperty(): Collection
    {
        return BankCard::query()
            ->where('seller_id', auth('seller')->id())
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get();
    }

    public function render()
    {
        return view('livewire.seller.bank-cards-page', [
            'cards' => $this->cards,
        ])->title('کارت‌های بانکی');
    }
}
