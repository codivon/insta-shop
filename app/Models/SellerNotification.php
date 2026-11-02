<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * SellerNotification — an in-app notification row for the SELLER panel
 * bell / «نوتیفیکیشن‌ها» page. Created through App\Support\SellerNotifier
 * which also (optionally) fans the message out as a Web Push to that
 * seller's registered devices (works when the browser is closed).
 *
 * @property int $id
 * @property int $seller_id
 * @property string $type
 * @property string $title
 * @property string $body
 * @property string $icon
 * @property string|null $link
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property bool $is_read
 * @property \Illuminate\Support\Carbon|null $read_at
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class SellerNotification extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'seller_id', 'type', 'title', 'body', 'icon', 'link',
        'subject_type', 'subject_id', 'is_read', 'read_at', 'created_at',
    ];

    protected $casts = [
        'seller_id'  => 'integer',
        'subject_id' => 'integer',
        'is_read'    => 'boolean',
        'read_at'    => 'datetime',
        'created_at' => 'datetime',
    ];

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeLatest($query)
    {
        return $query->orderByDesc('id');
    }

    /** Mark this notification as read (idempotent). */
    public function markRead(): void
    {
        if (! $this->is_read) {
            $this->forceFill(['is_read' => true, 'read_at' => now()])->save();
        }
    }
}
