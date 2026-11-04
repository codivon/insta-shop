<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * PushSubscription — a browser/device registered for Web Push so panel
 * notifications arrive even when the browser is closed. The crypto keys
 * (p256dh/auth) come from pushManager.subscribe() with our VAPID key.
 *
 * audience/seller_id scope the device to ONE panel: admin devices receive
 * admin pushes only; a seller device receives that seller's pushes only.
 *
 * @property int $id
 * @property string $endpoint_hash
 * @property string $endpoint
 * @property string $p256dh
 * @property string $auth
 * @property string|null $user_agent
 * @property string $device_label
 * @property string $audience
 * @property int|null $seller_id
 * @property Carbon|null $last_used_at
 * @property Carbon|null $created_at
 */
class PushSubscription extends Model
{
    public const UPDATED_AT = null;

    public const AUDIENCE_ADMIN = 'admin';

    public const AUDIENCE_SELLER = 'seller';

    protected $fillable = [
        'endpoint_hash', 'endpoint', 'p256dh', 'auth',
        'user_agent', 'device_label', 'audience', 'seller_id',
        'last_used_at', 'created_at',
    ];

    protected $casts = [
        'seller_id' => 'integer',
        'last_used_at' => 'datetime',
        'created_at' => 'datetime',
    ];
}
