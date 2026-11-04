<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoryLike extends Model
{
    protected $fillable = ['story_id', 'customer_id'];

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
