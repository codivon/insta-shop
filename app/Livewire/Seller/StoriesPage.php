<?php

namespace App\Livewire\Seller;

use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Shop;
use App\Models\Story;
use App\Support\ActivityLogger;
use App\Support\AdminNotifier;
use App\Support\Fmt;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Seller — «استوری‌های فروشگاه» (Instagram-style): post image stories with
 * an optional attached product + external link and a display window
 * (quick 12/24/48h chips or a fully custom start/end date-time, default
 * 24h). The per-shop running-story limit and the moderation mode come
 * from the admin stories settings; in «manual» mode a new story waits in
 * the admin queue as «در انتظار تأیید».
 */
#[Layout('layouts.seller-slot')]
class StoriesPage extends Component
{
    use NotifiesOnValidation;
    use WithFileUploads;

    /** @var mixed Livewire temporary upload (image) */
    public $media;

    public string $productId = '';

    public string $caption = '';

    public string $linkUrl = '';

    /** now | custom */
    public string $startMode = 'now';

    /** Gregorian Y-m-d (jdate-picker shows Jalali) */
    public string $startDate = '';

    /** H:i */
    public string $startTime = '';

    /** 12 | 24 | 48 | custom (hours) */
    public string $duration = '24';

    public string $endDate = '';

    public string $endTime = '';

    public function mount(): void
    {
        $this->startDate = now()->toDateString();
        $this->startTime = now()->format('H:i');
        $this->endDate = now()->addHours(24)->toDateString();
        $this->endTime = now()->addHours(24)->format('H:i');
    }

    protected function shop(): Shop
    {
        return once(fn () => Shop::ensureForSeller(auth('seller')->user()));
    }

    // ── computed data ────────────────────────────────────────────────

    /** How many RUNNING stories this shop already has. */
    public function getRunningCountProperty(): int
    {
        return Story::query()
            ->where('shop_id', $this->shop()->id)
            ->running()
            ->count();
    }

    /** Admin-configured limit of simultaneous running stories per shop. */
    public function getActiveLimitProperty(): int
    {
        return max(1, (int) Setting::get('stories.max_active', 3));
    }

    /** auto = goes live immediately · manual = waits for admin approval. */
    public function getModerationProperty(): string
    {
        return Setting::get('stories.moderation', 'auto') === 'manual' ? 'manual' : 'auto';
    }

    public function getStoriesProperty(): Collection
    {
        return Story::query()
            ->where('shop_id', $this->shop()->id)
            ->withCount(['likes', 'comments'])
            ->orderByDesc('id')
            ->limit(60)
            ->get();
    }

    /** Published products of this shop (attached-product picker). */
    public function getProductOptionsProperty(): Collection
    {
        return Product::query()
            ->where('shop_id', $this->shop()->id)
            ->where('status', 'published')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function render()
    {
        return view('livewire.seller.stories-page', [
            'stories' => $this->stories,
            'productOptions' => $this->productOptions,
            'runningCount' => $this->runningCount,
            'activeLimit' => $this->activeLimit,
            'moderation' => $this->moderation,
        ])->title('استوری‌های فروشگاه');
    }

    // ── actions ──────────────────────────────────────────────────────

    public function createStory(): void
    {
        $this->validateWithToast(fn () => $this->validate($this->rules(), $this->messages()));

        $limit = $this->activeLimit;
        $running = $this->runningCount;

        if ($running >= $limit) {
            $this->addError('media', 'سقف استوری فعال شما '.Fmt::fa($limit).' است — ابتدا منتظر منقضی‌شدن یکی از استوری‌ها بمانید یا با پشتیبانی تماس بگیرید.');
            $this->dispatch('panel-toast', message: 'سقف استوری فعال پر است ('.Fmt::fa($running).'/'.Fmt::fa($limit).').', type: 'error');

            return;
        }

        $startsAt = $this->startMode === 'now'
            ? now()
            : Carbon::createFromFormat('Y-m-d H:i', $this->startDate.' '.$this->startTime, config('app.timezone'));

        $endsAt = $this->duration !== 'custom'
            ? $startsAt->copy()->addHours((int) $this->duration)
            : Carbon::createFromFormat('Y-m-d H:i', $this->endDate.' '.$this->endTime, config('app.timezone'));

        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            $this->addError('endDate', 'زمان پایان باید بعد از زمان شروع باشد.');

            return;
        }

        $manual = $this->moderation === 'manual';

        $story = Story::query()->create([
            'shop_id' => $this->shop()->id,
            'product_id' => $this->productId !== '' ? (int) $this->productId : null,
            'type' => Story::TYPE_IMAGE,
            'media_path' => $this->media->store('stories/'.$this->shop()->id, 'public'),
            'caption' => $this->caption !== '' ? $this->caption : null,
            'link_url' => $this->linkUrl !== '' ? $this->linkUrl : null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => $manual ? Story::STATUS_PENDING : Story::STATUS_APPROVED,
        ]);

        ActivityLogger::log(
            'story.created',
            'استوری جدید برای فروشگاه «'.$this->shop()->name.'» از پنل فروشنده ثبت شد'.($manual ? ' (در انتظار تأیید مدیر)' : ''),
            subject: $story,
        );

        if ($manual) {
            AdminNotifier::notify(
                'story.pending',
                'استوری در انتظار تأیید ⏳',
                'فروشگاه «'.$this->shop()->name.'» استوری جدیدی ثبت کرد و منتظر تایید شماست.',
                route('admin.stories'),
                'zap',
                $story,
            );
        }

        $this->resetForm();
        $this->dispatch('panel-toast', message: $manual
            ? 'استوری ثبت شد و پس از تایید مدیر منتشر می‌شود. ⏳'
            : 'استوری منتشر شد. ✅', type: 'success');
    }

    public function destroy(int $storyId): void
    {
        $story = Story::query()
            ->where('shop_id', $this->shop()->id)
            ->find($storyId);

        if ($story === null) {
            return;
        }

        Storage::disk('public')->delete($story->media_path);
        $story->delete();

        ActivityLogger::log('story.deleted', 'استوری #'.$storyId.' فروشگاه «'.$this->shop()->name.'» توسط فروشنده حذف شد');

        $this->dispatch('panel-toast', message: 'استوری حذف شد. 🗑️', type: 'success');
    }

    // ── helpers ──────────────────────────────────────────────────────

    private function resetForm(): void
    {
        $this->media = null;
        $this->productId = '';
        $this->caption = '';
        $this->linkUrl = '';
        $this->startMode = 'now';
        $this->duration = '24';
        $this->startDate = now()->toDateString();
        $this->startTime = now()->format('H:i');
        $this->endDate = now()->addHours(24)->toDateString();
        $this->endTime = now()->addHours(24)->format('H:i');
    }

    protected function rules(): array
    {
        return [
            'media' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:500'],
            'linkUrl' => ['nullable', 'url', 'max:500'],
            'productId' => ['nullable', 'integer'],
            'startMode' => ['required', 'in:now,custom'],
            'startDate' => ['required_if:startMode,custom', 'nullable', 'date'],
            'startTime' => ['required_if:startMode,custom', 'nullable', 'date_format:H:i'],
            'duration' => ['required', 'in:12,24,48,custom'],
            'endDate' => ['required_if:duration,custom', 'nullable', 'date'],
            'endTime' => ['required_if:duration,custom', 'nullable', 'date_format:H:i'],
        ];
    }

    protected function messages(): array
    {
        return [
            'media.required' => 'تصویر استوری را انتخاب کنید.',
            'media.image' => 'فایل باید تصویر باشد.',
            'media.mimes' => 'فرمت‌های مجاز: jpg، jpeg، png، webp.',
            'media.max' => 'حجم تصویر حداکثر ۵ مگابایت است.',
            'caption.max' => 'متن استوری حداکثر ۵۰۰ کاراکتر است.',
            'linkUrl.url' => 'لینک باید یک آدرس اینترنتی معتبر باشد (با https:// شروع شود).',
            'linkUrl.max' => 'لینک حداکثر ۵۰۰ کاراکتر است.',
            'productId.integer' => 'محصول انتخاب‌شده معتبر نیست.',
            'startDate.required_if' => 'تاریخ شروع را انتخاب کنید.',
            'startDate.date' => 'تاریخ شروع نامعتبر است.',
            'startTime.required_if' => 'ساعت شروع را وارد کنید.',
            'startTime.date_format' => 'ساعت شروع باید به شکل HH:MM باشد.',
            'endDate.required_if' => 'برای مدت دلخواه، تاریخ پایان را وارد کنید.',
            'endDate.date' => 'تاریخ پایان نامعتبر است.',
            'endTime.required_if' => 'برای مدت دلخواه، ساعت پایان را وارد کنید.',
            'endTime.date_format' => 'ساعت پایان باید به شکل HH:MM باشد.',
        ];
    }
}
