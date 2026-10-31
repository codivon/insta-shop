<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['product_id', 'path', 'sort'])]
class ProductImage extends Model
{
    protected function casts(): array
    {
        return [
            'sort' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Public URL of the stored image. */
    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }

    /** Remove the underlying file from the public disk. */
    public function deleteFile(): void
    {
        Storage::disk('public')->delete($this->path);
    }
}
