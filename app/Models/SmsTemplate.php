<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * An editable SMS template («قالب پیامک»): body with {placeholder}
 * variables, per-variable sample values for live preview, enable switch
 * and (for pattern-based providers) the panel pattern code.
 */
class SmsTemplate extends Model
{
    protected $fillable = [
        'key', 'title', 'group', 'body', 'variables',
        'sample_data', 'enabled', 'pattern_code',
    ];

    protected function casts(): array
    {
        return [
            'variables'   => 'array',
            'sample_data' => 'array',
            'enabled'     => 'boolean',
        ];
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }
}
