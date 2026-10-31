<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsLog extends Model
{
    protected $fillable = [
        'panel', 'driver', 'phone', 'template', 'message',
        'status', 'error', 'provider_message_id', 'meta',
    ];

    protected function casts(): array
    {
        return ['meta' => 'array'];
    }

    public function scopeSuccessful($query): mixed
    {
        return $query->where('status', 'sent');
    }

    public function scopeFailed($query): mixed
    {
        return $query->where('status', 'failed');
    }
}
