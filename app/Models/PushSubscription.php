<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * PushSubscription — a browser/device registered for Web Push so the
 * admin gets notified even when the panel is closed. The crypto keys
 * (p256dh/auth) come from pushManager.subscribe() with our VAPID key.
 *
 * @property int $id
 * @property string $endpoint_hash
 * @property string $endpoint
 * @property string $p256dh
 * @property string $auth
 * @property string|null $user_agent
 * @property string $device_label
 * @property \Illuminate\Support\Carbon|null $last_used_at
 * @property \Illuminate\Support\Carbon|null $created_at
 */
class PushSubscription extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = [
        'endpoint_hash', 'endpoint', 'p256dh', 'auth',
        'user_agent', 'device_label', 'last_used_at', 'created_at',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'created_at'   => 'datetime',
    ];
}
