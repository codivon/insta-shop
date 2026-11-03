<?php

namespace App\Livewire\Seller\Support;

use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Seller;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Support\AdminNotifier;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Seller — thread of ONE support ticket: read the support answers,
 * reply (re-opens the ticket for the admin) and close it when settled.
 *
 * hideTabbar → chat-like focused page (no bottom tabs); focus → the
 * layout switches <main> to a flex column so the page owns an inner
 * scroller and the reply composer stays glued to the bottom.
 */
#[Layout('layouts.seller-slot', ['hideTabbar' => true, 'focus' => true])]
class TicketShowPage extends Component
{
    use NotifiesOnValidation;

    public SupportTicket $ticket;

    public string $replyBody = '';

    public function mount(SupportTicket $ticket): void
    {
        // ownership: sellers only see their own tickets
        if ($ticket->ticketable_type !== Seller::class || (int) $ticket->ticketable_id !== (int) auth('seller')->id()) {
            abort(404);
        }

        $this->ticket = $ticket->load(['messages.author']);
    }

    public function submitReply(): void
    {
        $this->validateWithToast(fn () => $this->validate([
            'replyBody' => ['required', 'string', 'min:2', 'max:3000'],
        ], [
            'replyBody.required' => 'متن پاسخ را بنویسید.',
            'replyBody.min' => 'متن پاسخ حداقل ۲ کاراکتر است.',
            'replyBody.max' => 'متن پاسخ حداکثر ۳۰۰۰ کاراکتر است.',
        ]));

        SupportTicketMessage::query()->create([
            'ticket_id' => $this->ticket->id,
            'author_type' => Seller::class,
            'author_id' => auth('seller')->id(),
            'body' => trim($this->replyBody),
        ]);

        // a seller reply always puts the ball back into the admin's court
        $this->ticket->update([
            'status' => SupportTicket::STATUS_OPEN,
            'last_reply_at' => now(),
        ]);

        AdminNotifier::notify(
            'ticket.reply',
            'پاسخ فروشنده در تیکت پشتیبانی',
            mb_substr($this->ticket->subject, 0, 90),
            route('admin.tickets.show', $this->ticket),
            'headset',
            $this->ticket,
        );

        $this->replyBody = '';
        $this->ticket->refresh()->load('messages.author');
    }

    public function closeTicket(): void
    {
        $this->ticket->update(['status' => SupportTicket::STATUS_CLOSED]);
    }

    public function render()
    {
        return view('livewire.seller.support.ticket-show-page', [
            'categories' => SupportTicket::CATEGORIES,
        ])->title($this->ticket->subject);
    }
}
