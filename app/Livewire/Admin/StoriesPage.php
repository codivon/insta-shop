<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Product;
use App\Models\Shop;
use App\Models\Story;
use App\Support\ActivityLogger;
use App\Support\SellerNotifier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Admin — stories management (استوری‌ها): every shop story in one table
 * with status filter tabs, approve/reject of the pending queue (manual
 * moderation mode from the stories settings), delete, and FULL create
 * power — the admin can post a story for ANY shop: image OR video (video
 * = direct link, never uploaded), caption, external link WITH TITLE,
 * per-story likes/comments toggles, attached product (searchable) and a
 * custom display window (default 24h, like the seller panel).
 */
#[Layout('layouts.admin-slot')]
class StoriesPage extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;
    use WithFileUploads;

    protected static string $permission = 'admin.stories';

    /** all | pending | running | scheduled | expired | rejected */
    #[Url]
    public string $filter = 'all';

    public int $perPage = 12;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    // ── create form ──────────────────────────────────────────────────
    public bool $creating = false;

    public string $type = Story::TYPE_IMAGE;

    /** @var mixed Livewire temporary upload (image stories) */
    public $media;

    public string $videoUrl = '';

    public string $shopId = '';

    public string $productId = '';

    /** Search term of the attached-product picker. */
    public string $productSearch = '';

    public string $caption = '';

    public string $linkUrl = '';

    public string $linkTitle = '';

    public bool $likesEnabled = true;

    public bool $commentsEnabled = true;

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
        $this->startTime = now()->format('H:i');
        $this->endTime = now()->addHours(24)->format('H:i');
        $this->startDate = now()->toDateString();
        $this->endDate = now()->addHours(24)->toDateString();
    }

    public function updating(string $property): void
    {
        if ($property === 'filter') {
            $this->perPage = 12;
            $this->hasMore = true;
        }
    }

    public function loadMore(): void
    {
        $now = (int) (microtime(true) * 1000);

        if ($now - $this->lastLoadAt < 400) {
            return;
        }

        $this->lastLoadAt = $now;
        $this->perPage += 12;
    }

    // ── create form helpers ──────────────────────────────────────────

    public function updatedShopId(): void
    {
        $this->productId = '';
        $this->productSearch = '';
    }

    public function updatedType(): void
    {
        if ($this->type === Story::TYPE_VIDEO) {
            $this->media = null;
        } else {
            $this->videoUrl = '';
        }

        $this->resetErrorBag(['media', 'videoUrl']);
    }

    public function updatedStartMode(): void
    {
        if ($this->startMode === 'custom' && $this->startDate === '') {
            $this->startDate = now()->toDateString();
            $this->startTime = now()->format('H:i');
        }
    }

    public function toggleCreate(): void
    {
        $this->creating = ! $this->creating;

        if ($this->creating) {
            $this->resetErrorBag();
        }
    }

    // ── computed data ────────────────────────────────────────────────

    public function getCountsProperty(): array
    {
        $now = now();

        $approved = Story::query()->where('status', Story::STATUS_APPROVED);

        return [
            'all' => Story::count(),
            'pending' => Story::where('status', Story::STATUS_PENDING)->count(),
            'running' => (clone $approved)->running()->count(),
            'scheduled' => (clone $approved)->where('starts_at', '>', $now)->count(),
            'expired' => Story::where('status', Story::STATUS_APPROVED)->where('ends_at', '<=', $now)->count(),
            'rejected' => Story::where('status', Story::STATUS_REJECTED)->count(),
        ];
    }

    public function getStoriesProperty(): Collection
    {
        $rows = $this->filteredQuery()
            ->with([
                'shop:id,name,logo_path,seller_id',
                'product:id,name',
            ])
            ->withCount(['likes', 'comments'])
            ->orderByDesc('id')
            ->limit($this->perPage)
            ->get();

        $this->hasMore = $this->filteredQuery()->count() > $this->perPage;

        return $rows;
    }

    /** Shops for the create-form picker (newest first). */
    public function getShopOptionsProperty(): Collection
    {
        return Shop::query()->orderBy('name')->get(['id', 'name']);
    }

    /** Published products of the picked shop, filtered by the picker search. */
    public function getProductOptionsProperty(): Collection
    {
        if ($this->shopId === '' || ! ctype_digit((string) $this->shopId)) {
            return collect();
        }

        $query = Product::query()
            ->where('shop_id', (int) $this->shopId)
            ->where('status', 'published')
            ->orderBy('name');

        if (trim($this->productSearch) !== '') {
            $query->where('name', 'like', '%'.trim($this->productSearch).'%');
        }

        return $query->limit(20)->get(['id', 'name']);
    }

    public function render()
    {
        return view('livewire.admin.stories-page', [
            'stories' => $this->stories,
            'counts' => $this->counts,
            'shopOptions' => $this->shopOptions,
            'productOptions' => $this->productOptions,
        ])->title('استوری‌ها')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — استوری‌ها']);
    }

    // ── moderation actions ───────────────────────────────────────────

    public function approve(int $storyId): void
    {
        $story = Story::query()->findOrFail($storyId);

        if ($story->status === Story::STATUS_APPROVED) {
            return;
        }

        $story->forceFill(['status' => Story::STATUS_APPROVED])->save();

        SellerNotifier::notify(
            $story->shop->seller_id,
            'story.status',
            'استوری شما تأیید و منتشر شد ✅',
            'استوری فروشگاه «'.$story->shop->name.'» توسط پشتیبانی تأیید شد و اکنون در دسترس مشتریان است.',
            route('seller.stories'),
            'zap',
            $story,
        );

        ActivityLogger::log('story.approved', 'استوری #'.$story->id.' فروشگاه «'.$story->shop->name.'» تأیید شد', subject: $story);

        $this->dispatch('panel-toast', message: 'استوری تأیید و منتشر شد. ✅', type: 'success');
    }

    public function reject(int $storyId): void
    {
        $story = Story::query()->findOrFail($storyId);

        if ($story->status === Story::STATUS_REJECTED) {
            return;
        }

        $story->forceFill(['status' => Story::STATUS_REJECTED])->save();

        SellerNotifier::notify(
            $story->shop->seller_id,
            'story.status',
            'استوری شما تأیید نشد ⛔',
            'استوری فروشگاه «'.$story->shop->name.'» توسط پشتیبانی تأیید نشد. در صورت نیاز با پشتیبانی تماس بگیرید.',
            route('seller.stories'),
            'zap',
            $story,
        );

        ActivityLogger::log('story.rejected', 'استوری #'.$story->id.' فروشگاه «'.$story->shop->name.'» رد شد', subject: $story);

        $this->dispatch('panel-toast', message: 'استوری رد شد.', type: 'success');
    }

    public function destroy(int $storyId): void
    {
        $story = Story::query()->findOrFail($storyId);

        if ($story->media_path) {
            Storage::disk('public')->delete($story->media_path);
        }

        $story->delete();

        ActivityLogger::log('story.deleted', 'استوری #'.$storyId.' فروشگاه «'.$story->shop->name.'» حذف شد');

        $this->dispatch('panel-toast', message: 'استوری حذف شد. 🗑️', type: 'success');
    }

    // ── create ───────────────────────────────────────────────────────

    public function createStory(): void
    {
        $this->validateWithToast(fn () => $this->validate($this->rules(), $this->messages()));

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

        $path = null;

        if ($this->type === Story::TYPE_IMAGE) {
            $path = $this->media->store("stories/{$this->shopId}", 'public');
        }

        $story = Story::query()->create([
            'shop_id' => (int) $this->shopId,
            'product_id' => $this->productId !== '' ? (int) $this->productId : null,
            'type' => $this->type,
            'media_path' => $this->type === Story::TYPE_IMAGE ? $path : null,
            'video_url' => $this->type === Story::TYPE_VIDEO ? $this->videoUrl : null,
            'caption' => $this->caption !== '' ? $this->caption : null,
            'link_url' => $this->linkUrl !== '' ? $this->linkUrl : null,
            'link_title' => $this->linkUrl !== '' && $this->linkTitle !== '' ? $this->linkTitle : null,
            'likes_enabled' => $this->likesEnabled,
            'comments_enabled' => $this->commentsEnabled,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            // The admin IS the approver — admin-made stories go straight live.
            'status' => Story::STATUS_APPROVED,
        ]);

        ActivityLogger::log(
            'story.created',
            'استوری جدید برای فروشگاه «'.$story->shop->name.'» از پنل مدیریت ثبت شد',
            subject: $story,
        );

        $this->resetForm();
        $this->creating = false;
        $this->filter = 'all';
        $this->dispatch('panel-toast', message: 'استوری ثبت و منتشر شد. ✅', type: 'success');
    }

    // ── helpers ──────────────────────────────────────────────────────

    private function resetForm(): void
    {
        $this->type = Story::TYPE_IMAGE;
        $this->media = null;
        $this->videoUrl = '';
        $this->shopId = '';
        $this->productId = '';
        $this->productSearch = '';
        $this->caption = '';
        $this->linkUrl = '';
        $this->linkTitle = '';
        $this->likesEnabled = true;
        $this->commentsEnabled = true;
        $this->startMode = 'now';
        $this->duration = '24';
        $this->startDate = now()->toDateString();
        $this->startTime = now()->format('H:i');
        $this->endDate = now()->addHours(24)->toDateString();
        $this->endTime = now()->addHours(24)->format('H:i');
    }

    private function filteredQuery(): Builder
    {
        $query = Story::query();
        $now = now();

        match ($this->filter) {
            'pending' => $query->where('status', Story::STATUS_PENDING),
            'running' => $query->where('status', Story::STATUS_APPROVED)->running(),
            'scheduled' => $query->where('status', Story::STATUS_APPROVED)->where('starts_at', '>', $now),
            'expired' => $query->where('status', Story::STATUS_APPROVED)->where('ends_at', '<=', $now),
            'rejected' => $query->where('status', Story::STATUS_REJECTED),
            default => null,
        };

        return $query;
    }

    protected function rules(): array
    {
        $isVideo = $this->type === Story::TYPE_VIDEO;

        return [
            'shopId' => ['required', 'integer', 'exists:shops,id'],
            'type' => ['required', 'in:image,video'],
            'media' => array_filter([
                $isVideo ? 'nullable' : 'required',
                $isVideo ? null : 'image',
                $isVideo ? null : 'mimes:jpg,jpeg,png,webp',
                $isVideo ? null : 'max:5120',
            ]),
            'videoUrl' => [$isVideo ? 'required' : 'nullable', 'url', 'max:500',
                function (string $attribute, mixed $value, \Closure $fail) use ($isVideo): void {
                    if ($isVideo && $value !== '' && ! Story::isDirectVideoUrl((string) $value)) {
                        $fail('لینک ویدیو باید مستقیم به فایل ویدیو باشد (mp4، webm، ogg یا m3u8).');
                    }
                },
            ],
            'caption' => ['nullable', 'string', 'max:500'],
            'linkUrl' => ['nullable', 'url', 'max:500'],
            'linkTitle' => ['nullable', 'required_with:linkUrl', 'string', 'max:150'],
            'likesEnabled' => ['boolean'],
            'commentsEnabled' => ['boolean'],
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
            'shopId.required' => 'فروشگاه را انتخاب کنید.',
            'shopId.exists' => 'فروشگاه انتخاب‌شده معتبر نیست.',
            'media.required' => 'تصویر استوری را انتخاب کنید.',
            'media.image' => 'فایل باید تصویر باشد.',
            'media.mimes' => 'فرمت‌های مجاز: jpg، jpeg، png، webp.',
            'media.max' => 'حجم تصویر حداکثر ۵ مگابایت است.',
            'videoUrl.required' => 'لینک ویدیو را وارد کنید.',
            'videoUrl.url' => 'لینک ویدیو باید یک آدرس اینترنتی معتبر باشد.',
            'videoUrl.max' => 'لینک ویدیو حداکثر ۵۰۰ کاراکتر است.',
            'caption.max' => 'متن استوری حداکثر ۵۰۰ کاراکتر است.',
            'linkUrl.url' => 'لینک باید یک آدرس اینترنتی معتبر باشد (با https:// شروع شود).',
            'linkUrl.max' => 'لینک حداکثر ۵۰۰ کاراکتر است.',
            'linkTitle.required_with' => 'برای لینک، یک عنوان وارد کنید (عنوان روی استوری نمایش داده می‌شود).',
            'linkTitle.max' => 'عنوان لینک حداکثر ۱۵۰ کاراکتر است.',
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
