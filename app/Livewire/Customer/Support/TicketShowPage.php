<?php

namespace App\Livewire\Customer\Support;

use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Customer;
use App\Models\SupportTicket;
use App\Models\SupportTicketMessage;
use App\Support\AdminNotifier;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Customer — thread of ONE support ticket: read the support answers,
 * reply (re-opens the ticket for the admin) and close it when settled.
 */
#[Layout('layouts.customer-slot')]
class TicketShowPage extends Component
{
    use NotifiesOnValidation;

    public SupportTicket $ticket;

    public string $replyBody = '';

    public function mount(SupportTicket $ticket): void
    {
        // ownership: customers only see their own tickets
        if ($ticket->ticketable_type !== Customer::class || (int) $ticket->ticketable_id !== (int) auth('customer')->id()) {
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
            'replyBody.min'      => 'متن پاسخ حداقل ۲ کاراکتر است.',
            'replyBody.max'      => 'متن پاسخ حداکثر ۳۰۰۰ کاراکتر است.',
        ]));

        SupportTicketMessage::query()->create([
            'ticket_id'   => $this->ticket->id,
            'author_type' => Customer::class,
            'author_id'   => auth('customer')->id(),
            'body'        => trim($this->replyBody),
        ]);

        // a customer reply always puts the ball back into the admin's court
        $this->ticket->update([
            'status'        => SupportTicket::STATUS_OPEN,
            'last_reply_at' => now(),
        ]);

        AdminNotifier::notify(
            'ticket.reply',
            'پاسخ مشتری در تیکت پشتیبانی',
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
        return view('livewire.customer.support.ticket-show-page')->title($this->ticket->subject);
    }
}
