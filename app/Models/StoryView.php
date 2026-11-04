<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per (story × viewer): logged-in customers get customer_id,
 * guests get an anonymous visitor_token (session-based) — so the story
 * analytics (daily-views chart, unique viewers) cover EVERY viewer,
 * not only logged-in ones. customer rows double as the Instagram
 * ring «seen» marks.
 */
class StoryView extends Model
{
    protected $fillable = ['story_id', 'customer_id', 'visitor_token'];

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
