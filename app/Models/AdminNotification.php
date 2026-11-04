<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * AdminNotification — an in-app notification for the admin panel bell.
 * Created through App\Support\AdminNotifier which also (optionally)
 * fans the message out as a Web Push to all subscribed devices.
 *
 * @property int $id
 * @property string $type
 * @property string $title
 * @property string $body
 * @property string $icon
 * @property string|null $link
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property bool $is_read
 * @property Carbon|null $read_at
 * @property Carbon|null $created_at
 */
class AdminNotification extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'type', 'title', 'body', 'icon', 'link',
        'subject_type', 'subject_id', 'is_read', 'read_at', 'created_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'subject_id' => 'integer',
        'read_at' => 'datetime',
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
