<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\ProductFile;
use App\Models\Shop;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Seller downloads one of their own product files (downloadable products).
 * Files live on the private `local` disk — never directly web-accessible.
 */
class ProductFileDownload extends Controller
{
    public function __invoke(ProductFile $file): StreamedResponse
    {
        $shop = Shop::query()->where('seller_id', auth('seller')->id())->first();

        abort_if(! $shop || $file->product->shop_id !== $shop->id, 404, 'فایل یافت نشد.');

        abort_unless(Storage::disk('local')->exists($file->file_path), 404, 'فایل روی دیسک یافت نشد.');

        return Storage::disk('local')->download($file->file_path, $this->safeName($file));
    }

    /** Download filename = original title + original extension. */
    protected function safeName(ProductFile $file): string
    {
        $ext = pathinfo($file->file_path, PATHINFO_EXTENSION);

        return $file->title.($ext !== '' ? '.'.$ext : '');
    }
}
