<?php

namespace App\Livewire\Customer\Support;

use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Customer;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Support\AdminNotifier;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Customer — «پشتیبانی» (linked from the profile page): list of the
 * customer's own tickets + a create-ticket modal. Threads open in
 * shop.support.show.
 */
#[Layout('layouts.customer-slot')]
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
        $this->perPage   += 10;
    }

    /* ── create-ticket modal ── */

    public function openCreate(): void
    {
        $this->resetErrorBag();
        $this->subject    = '';
        $this->category   = 'other';
        $this->body       = '';
        $this->createOpen = true;
    }

    public function closeCreate(): void
    {
        $this->createOpen = false;
        $this->resetErrorBag();
    }

    public function submitTicket(): void
    {
        $this->validateWithToast(fn () => $this->validate([
            'subject'  => ['required', 'string', 'min:3', 'max:120'],
            'category' => ['required', 'in:'.implode(',', array_keys(SupportTicket::CATEGORIES))],
            'body'     => ['required', 'string', 'min:5', 'max:3000'],
        ], [
            'subject.required'  => 'موضوع تیکت را بنویسید.',
            'subject.min'       => 'موضوع تیکت حداقل ۳ کاراکتر است.',
            'subject.max'       => 'موضوع تیکت حداکثر ۱۲۰ کاراکتر است.',
            'category.required' => 'دسته‌بندی تیکت را انتخاب کنید.',
            'category.in'       => 'دسته‌بندی نامعتبر است.',
            'body.required'     => 'متن تیکت را بنویسید.',
            'body.min'          => 'متن تیکت حداقل ۵ کاراکتر است.',
            'body.max'          => 'متن تیکت حداکثر ۳۰۰۰ کاراکتر است.',
        ]));

        $ticket = SupportTicket::query()->create([
            'subject'         => trim($this->subject),
            'category'        => $this->category,
            'status'          => SupportTicket::STATUS_OPEN,
            'ticketable_type' => Customer::class,
            'ticketable_id'   => auth('customer')->id(),
            'last_reply_at'   => now(),
        ]);

        SupportTicketMessage::query()->create([
            'ticket_id'   => $ticket->id,
            'author_type' => Customer::class,
            'author_id'   => auth('customer')->id(),
            'body'        => trim($this->body),
        ]);

        $customer = auth('customer')->user();

        AdminNotifier::notify(
            'ticket.new',
            'تیکت پشتیبانی جدید از مشتری',
            ($customer?->full_name ?: 'مشتری').' — '.$ticket->categoryLabel().' — '.mb_substr($ticket->subject, 0, 80),
            route('admin.tickets.show', $ticket),
            'headset',
            $ticket,
        );

        $this->createOpen = false;

        session()->flash('customer-support-flash', 'تیکت شما ثبت شد؛ پاسخ پشتیبانی در همین صفحه نمایش داده می‌شود.');
    }

    public function getTicketsProperty(): Collection
    {
        $query = SupportTicket::query()
            ->where('ticketable_type', Customer::class)
            ->where('ticketable_id', auth('customer')->id())
            ->withCount('messages')
            ->orderByDesc('last_reply_at')
            ->orderByDesc('id');

        $total = (clone $query)->count();
        $rows  = $query->limit($this->perPage)->get();

        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    public function render()
    {
        return view('livewire.customer.support.tickets-page', [
            'tickets'    => $this->tickets,
            'categories' => SupportTicket::CATEGORIES,
        ])->title('پشتیبانی');
    }
}
