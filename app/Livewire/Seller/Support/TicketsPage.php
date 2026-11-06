<?php

namespace App\Livewire\Seller\Support;

use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Seller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Support\AdminNotifier;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Seller — «پشتیبانی» (linked from the profile page): list of the
 * seller's own tickets + a create-ticket modal (subject, category,
 * first message). Threads open in seller.support.show.
 */
#[Layout('layouts.seller-slot')]
class TicketsPage extends Component
{
    use NotifiesOnValidation;

    public bool $createOpen = false;

    public string $subject = '';

    public string $category = 'other';

    public string $body = '';

    public int $perPage = 10;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    public function loadMore(): void
    {
        $now = (int) (microtime(true) * 1000);

        if ($now - $this->lastLoadAt < 400) {
            return;
        }

        $this->lastLoadAt = $now;
        $this->perPage += 10;
    }

    /* ── create-ticket modal ── */

    public function openCreate(): void
    {
        $this->resetErrorBag();
        $this->subject = '';
        $this->category = 'other';
        $this->body = '';
        $this->createOpen = true;
    }

    public function closeCreate(): void
    {
        $this->createOpen = false;
        $this->resetErrorBag();
    }

    public function submitTicket(): void
    {
        // Rate limit (v0.21.0): 5 tickets/hour per author — each ticket
        // fires an admin notification + web push, floods must not reach it.
        if (RateLimiter::tooManyAttempts('ticket-seller:'.auth('seller')->id(), 5)) {
            $this->dispatch('panel-toast', message: 'تعداد تیکت‌های شما زیاد است — بعداً تلاش کنید.', type: 'error');

            return;
        }

        RateLimiter::hit('ticket-seller:'.auth('seller')->id());

        $this->validateWithToast(fn () => $this->validate([
            'subject' => ['required', 'string', 'min:3', 'max:120'],
            'category' => ['required', 'in:'.implode(',', array_keys(SupportTicket::CATEGORIES))],
            'body' => ['required', 'string', 'min:5', 'max:3000'],
        ], [
            'subject.required' => 'موضوع تیکت را بنویسید.',
            'subject.min' => 'موضوع تیکت حداقل ۳ کاراکتر است.',
            'subject.max' => 'موضوع تیکت حداکثر ۱۲۰ کاراکتر است.',
            'category.required' => 'دسته‌بندی تیکت را انتخاب کنید.',
            'category.in' => 'دسته‌بندی نامعتبر است.',
            'body.required' => 'متن تیکت را بنویسید.',
            'body.min' => 'متن تیکت حداقل ۵ کاراکتر است.',
            'body.max' => 'متن تیکت حداکثر ۳۰۰۰ کاراکتر است.',
        ]));

        $ticket = SupportTicket::query()->create([
            'subject' => trim($this->subject),
            'category' => $this->category,
            'status' => SupportTicket::STATUS_OPEN,
            'ticketable_type' => Seller::class,
            'ticketable_id' => auth('seller')->id(),
            'last_reply_at' => now(),
        ]);

        SupportTicketMessage::query()->create([
            'ticket_id' => $ticket->id,
            'author_type' => Seller::class,
            'author_id' => auth('seller')->id(),
            'body' => trim($this->body),
        ]);

        AdminNotifier::notify(
            'ticket.new',
            'تیکت پشتیبانی جدید از فروشنده',
            ($this->shop()?->name ?: 'فروشنده').' — '.$ticket->categoryLabel().' — '.mb_substr($ticket->subject, 0, 80),
            route('admin.tickets.show', $ticket),
            'headset',
            $ticket,
        );

        $this->createOpen = false;

        session()->flash('seller-support-flash', 'تیکت شما ثبت شد؛ پاسخ پشتیبانی در همین صفحه نمایش داده می‌شود.');
    }

    private function shop(): ?object
    {
        return auth('seller')->user()?->shop;
    }

    public function getTicketsProperty(): Collection
    {
        $query = SupportTicket::query()
            ->where('ticketable_type', Seller::class)
            ->where('ticketable_id', auth('seller')->id())
            ->withCount('messages')
            ->orderByDesc('last_reply_at')
            ->orderByDesc('id');

        $total = (clone $query)->count();
        $rows = $query->limit(min(max($this->perPage, 1), 50))->get();

        $this->hasMore = $total > min(max($this->perPage, 1), 50);

        return $rows;
    }

    public function render()
    {
        return view('livewire.seller.support.tickets-page', [
            'tickets' => $this->tickets,
            'categories' => SupportTicket::CATEGORIES,
        ])->title('پشتیبانی');
    }
}
