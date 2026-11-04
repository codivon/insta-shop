<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * ActivityLog — one row per meaningful event on the site (audit trail).
 * Written exclusively through App\Support\ActivityLogger which never lets
 * a logging failure break the actual request.
 *
 * @property int $id
 * @property string $actor_type admin|seller|customer|system
 * @property int|null $actor_id
 * @property string $actor_name
 * @property string $action
 * @property string $title
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property array|null $before
 * @property array|null $after
 * @property string|null $ip
 * @property string|null $user_agent
 * @property Carbon|null $created_at
 */
class ActivityLog extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'actor_type', 'actor_id', 'actor_name', 'action', 'title',
        'subject_type', 'subject_id', 'before', 'after', 'ip', 'user_agent', 'created_at',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
        'actor_id' => 'integer',
        'subject_id' => 'integer',
        'created_at' => 'datetime',
    ];

    /** Newest first query. */
    public function scopeLatest($query)
    {
        return $query->orderByDesc('id');
    }

    /** Human label for the actor type. */
    public function actorLabel(): string
    {
        return match ($this->actor_type) {
            'admin' => 'مدیر',
            'seller' => 'فروشنده',
            'customer' => 'مشتری',
            default => 'سیستم',
        };
    }
}
