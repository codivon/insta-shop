<?php

namespace App\Livewire\Admin\Support;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Admin;
use App\Models\Customer;
use App\Models\Seller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Support\SellerNotifier;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Admin — threaded view of ONE support ticket: reply to the
 * seller/customer, close or reopen the ticket.
 */
#[Layout('layouts.admin-slot')]
#[Title('جزئیات تیکت')]
class TicketShow extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;

    protected static string $permission = 'admin.tickets.show';

    public SupportTicket $ticket;

    public string $replyBody = '';

    public function mount(SupportTicket $ticket): void
    {
        // ticketable may be a Seller OR a Customer — only sellers have a
        // shop relation, so eager-load it per class (a plain
        // ticketable.shop would throw on customer tickets).
        $this->ticket = $ticket->load('messages.author');
        $this->ticket->loadMorph('ticketable', [
            Seller::class => ['shop'],
        ]);
    }

    public function submitReply(): void
    {
        $this->validateWithToast(fn () => $this->validate([
            'replyBody' => ['required', 'string', 'min:2', 'max:3000'],
        ], [
            'replyBody.required' => 'متن پاسخ را بنویسید.',
            'replyBody.min' => 'متن پاسخ حداقل ۲ کاراکتر است.',
            'replyBody.max' => 'متن پاسخ حداکثر ۳۰۰۰ کاراکتر است.',
        ]), 'ارسال پاسخ ناموفق بود');

        SupportTicketMessage::query()->create([
            'ticket_id' => $this->ticket->id,
            'author_type' => Admin::class,
            'author_id' => auth('admin')->id(),
            'body' => trim($this->replyBody),
        ]);

        $this->ticket->update([
            'status' => SupportTicket::STATUS_ANSWERED,
            'last_reply_at' => now(),
        ]);

        // notify the author on their side (seller bell; customers read it in the thread)
        if ($this->ticket->ticketable instanceof Seller) {
            SellerNotifier::notify(
                $this->ticket->ticketable,
                'ticket.reply',
                'پاسخ پشتیبانی به تیکت شما',
                mb_substr($this->ticket->subject, 0, 90),
                route('seller.support.show', $this->ticket),
                'headset',
                $this->ticket,
            );
        } elseif ($this->ticket->ticketable instanceof Customer) {
            // customers have no bell yet — keep the admin-side audit only
        }

        $this->replyBody = '';
        $this->ticket->refresh()->load('messages.author');
        $this->ticket->loadMorph('ticketable', [
            Seller::class => ['shop'],
        ]);

        session()->flash('admin-ticket-flash', 'پاسخ ارسال شد.');
    }

    public function closeTicket(): void
    {
        $this->ticket->update(['status' => SupportTicket::STATUS_CLOSED]);
    }

    public function reopenTicket(): void
    {
        $this->ticket->update(['status' => SupportTicket::STATUS_OPEN]);
    }

    /** Author display name (store name for sellers, name for customers). */
    public function authorName(SupportTicket $ticket): string
    {
        $author = $ticket->ticketable;

        if ($author === null) {
            return '—';
        }

        if ($author instanceof Seller) {
            return (string) ($author->shop?->name ?: $author->full_name ?: '—');
        }

        return (string) ($author->full_name ?? '—');
    }

    public function render()
    {
        return view('livewire.admin.support.ticket-show');
    }
}
