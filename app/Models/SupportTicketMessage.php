<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * One message inside a support ticket conversation.
 * The author can be the ticket owner (seller/customer) or the support admin.
 */
class SupportTicketMessage extends Model
{
    protected $fillable = ['ticket_id', 'author_type', 'author_id', 'body'];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function author(): MorphTo
    {
        return $this->morphTo();
    }

    /** Sent by the support team (admin side). */
    public function isStaff(): bool
    {
        return $this->author_type === Admin::class;
    }

    public function authorLabel(): string
    {
        return match ($this->author_type) {
            Admin::class    => 'پشتیبانی',
            Seller::class   => 'فروشنده',
            Customer::class => 'مشتری',
            default         => 'کاربر',
        };
    }
}
