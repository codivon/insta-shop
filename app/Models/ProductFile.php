<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['product_id', 'title', 'price', 'discount_percent', 'status', 'file_path', 'file_size', 'sort'])]
class ProductFile extends Model
{
    protected function casts(): array
    {
        return [
            'price'            => 'integer',
            'discount_percent' => 'integer',
            'file_size'        => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Final payable price after the discount. */
    public function finalPrice(): int
    {
        if ($this->discount_percent <= 0) {
            return (int) $this->price;
        }

        return (int) floor($this->price * (100 - min(100, $this->discount_percent)) / 100);
    }

    /** Public URL (download is served through an authorized route later). */
    public function downloadUrl(): string
    {
        return route('seller.products.files.download', ['file' => $this->id]);
    }

    public function humanSize(): string
    {
        $bytes = (int) $this->file_size;

        if ($bytes <= 0) {
            return '—';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $i = (int) floor(log($bytes, 1024));

        return round($bytes / (1024 ** $i), 1).' '.$units[$i];
    }

    public function deleteFile(): void
    {
        if ($this->file_path && Storage::disk('local')->exists($this->file_path)) {
            Storage::disk('local')->delete($this->file_path);
        }
    }
}
