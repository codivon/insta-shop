<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Support ticket opened by a seller or a customer towards the admin
 * support team. Status flow:
 *
 *   open     → needs admin attention (new ticket or user replied)
 *   answered → support replied, waiting on the author
 *   closed   → settled (admin or the author closed it)
 */
class SupportTicket extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_ANSWERED = 'answered';

    public const STATUS_CLOSED = 'closed';

    public const STATUSES = [self::STATUS_OPEN, self::STATUS_ANSWERED, self::STATUS_CLOSED];

    /** Ticket categories shown in the creation form. */
    public const CATEGORIES = [
        'orders' => 'سفارش‌ها',
        'payment' => 'پرداخت',
        'wallet' => 'کیف پول و برداشت',
        'products' => 'محصولات',
        'shop' => 'فروشگاه',
        'feedback' => 'پیشنهاد و انتقاد',
        'other' => 'سایر',
    ];

    protected $fillable = ['subject', 'category', 'status', 'last_reply_at', 'ticketable_type', 'ticketable_id'];

    protected function casts(): array
    {
        return [
            'last_reply_at' => 'datetime',
        ];
    }

    public function ticketable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return HasMany<SupportTicketMessage, $this> */
    public function messages(): HasMany
    {
        return $this->hasMany(SupportTicketMessage::class, 'ticket_id');
    }

    /** @return HasOne<SupportTicketMessage, $this> */
    public function latestMessage(): HasOne
    {
        return $this->hasOne(SupportTicketMessage::class, 'ticket_id')->latestOfMany('id');
    }

    public function isOpen(): bool
    {
        return $this->status === self::STATUS_OPEN;
    }

    /** Refresh the activity pointer after any new message. */
    public function touchReply(): void
    {
        $this->update(['last_reply_at' => now()]);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_ANSWERED => 'پاسخ داده شد',
            self::STATUS_CLOSED => 'بسته شده',
            default => 'در انتظار بررسی',
        };
    }

    public function statusClasses(): string
    {
        return match ($this->status) {
            self::STATUS_ANSWERED => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300',
            self::STATUS_CLOSED => 'bg-slate-100 text-slate-500 dark:bg-slate-800 dark:text-slate-400',
            default => 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300',
        };
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? self::CATEGORIES['other'];
    }

    /** «فروشنده» or «مشتری» — the author side of the conversation. */
    public function authorLabel(): string
    {
        return $this->ticketable_type === Seller::class ? 'فروشنده' : 'مشتری';
    }
}
