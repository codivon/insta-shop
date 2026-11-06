<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtpCode extends Model
{
    protected $fillable = [
        'phone', 'code', 'purpose', 'panel',
        'attempts', 'max_attempts', 'expires_at', 'consumed_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'consumed_at' => 'datetime',
        ];
    }

    public function isValid(): bool
    {
        return $this->consumed_at === null
            && $this->expires_at->isFuture()
            && $this->attempts < $this->max_attempts;
    }

    /**
     * Atomic consume — a concurrent double-submit can never both succeed
     * (check-then-act race): only the FIRST update that still sees
     * consumed_at IS NULL wins.
     */
    public function markConsumed(): void
    {
        if ($this->consumed_at !== null) {
            return;
        }

        $updated = static::query()
            ->whereKey($this->getKey())
            ->whereNull('consumed_at')
            ->update(['consumed_at' => now()]);

        if ($updated > 0) {
            $this->forceFill(['consumed_at' => now()]);
        }
    }

    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    /** Scope: latest active OTP for a phone in a panel */
    public function scopeActiveFor($query, string $phone, string $panel)
    {
        return $query->where('phone', $phone)
            ->where('panel', $panel)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->latest()
            ->limit(1);
    }
}
