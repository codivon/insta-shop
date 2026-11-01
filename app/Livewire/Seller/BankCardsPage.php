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

    public string $cardNumber = '';

    public string $cardSheba = '';

    public ?int $confirmDeleteId = null;

    /* ───────────────────────── modal ───────────────────────── */

    public function openAdd(): void
    {
        if ($this->cardsCount() >= self::MAX_CARDS) {
            session()->flash('cards-flash', 'حداکثر '.self::MAX_CARDS.' کارت می‌توانید اضافه کنید.');

            return;
        }

        $this->resetErrorBag();
        $this->cardName   = '';
        $this->cardNumber = '';
        $this->cardSheba  = '';
        $this->addOpen    = true;
    }

    public function closeAdd(): void
    {
        $this->addOpen    = false;
        $this->cardName   = '';
        $this->cardNumber = '';
        $this->cardSheba  = '';
        $this->resetErrorBag();
    }

    /* ─────────────────── live input hygiene ─────────────────── */

    public function updatedCardNumber(mixed $value): void
    {
        $this->cardNumber = Banking::digitsOnly((string) $value);
    }

    public function updatedCardSheba(mixed $value): void
    {
        $this->cardSheba = Banking::shebaNormalize((string) $value);
    }

    /* ───────────────────────── actions ───────────────────────── */

    public function submitCard(): void
    {
        $seller = auth('seller')->user();

        // belt & suspenders: updated* hooks normalize live input, but
        // normalize again server-side before validating.
        $this->cardName   = trim($this->cardName);
        $this->cardNumber = Banking::digitsOnly($this->cardNumber);
        $this->cardSheba  = Banking::shebaNormalize($this->cardSheba);

        $this->validate([
            'cardName'   => ['required', 'string', 'min:3', 'max:60'],
            'cardNumber' => ['required', 'digits:16', Rule::unique('bank_cards', 'card_number')->where('seller_id', $seller->id)],
            'cardSheba'  => ['required', 'string', 'size:26', 'starts_with:IR', Rule::unique('bank_cards', 'sheba')->where('seller_id', $seller->id)],
        ], [
            'cardName.required'          => 'نام و نام خانوادگی صاحب کارت را وارد کنید.',
            'cardName.min'               => 'نام باید حداقل ۳ حرف باشد.',
            'cardNumber.required'        => 'شماره کارت را وارد کنید.',
            'cardNumber.digits'          => 'شماره کارت باید ۱۶ رقم باشد.',
            'cardNumber.unique'          => 'این شماره کارت قبلاً ثبت شده است.',
            'cardSheba.required'         => 'شماره شبا را وارد کنید.',
            'cardSheba.size'             => 'شماره شبا باید IR + ۲۴ رقم باشد.',
            'cardSheba.starts_with'      => 'شماره شبا باید با IR شروع شود.',
            'cardSheba.unique'           => 'این شماره شبا قبلاً ثبت شده است.',
        ]);

        if (! Banking::validCard($this->cardNumber)) {
            $this->addError('cardNumber', 'شماره کارت معتبر نیست (رقم کنترل لان اشتباه است).');

            return;
        }

        if (! Banking::validSheba($this->cardSheba)) {
            $this->addError('cardSheba', 'شماره شبا معتبر نیست — رقم‌های کنترل را بررسی کنید.');

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
            'card_number' => $this->cardNumber,
            'sheba'       => $this->cardSheba,
            'is_default'  => $isFirst,
        ]);

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

        session()->flash('cards-flash', 'کارت پیش‌فرض برداشت تغییر کرد. ✅');
    }

    /* ───────────────────────── data ───────────────────────── */

    protected function cardsCount(): int
    {
        return $this->cards->count();
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
