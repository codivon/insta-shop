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
 * Seller — «استوری‌های فروشگاه» (Instagram-style): image OR video stories
 * (video = direct link, never uploaded) with an optional attached product
 * (searchable picker), an external link WITH TITLE and per-story
 * likes/comments toggles (defaults ON). Add AND edit live in a MODAL.
 * Display window: quick 12/24/48h chips or a fully custom start/end
 * date-time — default 24h. The per-shop running-story limit and the
 * moderation mode come from the admin stories settings; in «manual» mode
 * a new story waits in the admin queue as «در انتظار تأیید».
 */
#[Layout('layouts.seller-slot')]
class StoriesPage extends Component
{
    use NotifiesOnValidation;
    use WithFileUploads;

    /** null = create mode, otherwise the story being edited. */
    public ?int $editingId = null;

    public bool $formOpen = false;

    public string $type = Story::TYPE_IMAGE;

    /** @var mixed Livewire temporary upload (image stories) */
    public $media;

    /**
     * @var mixed Livewire temporary upload — optional circular ICON of
     *            the story ring on the public storefront (each story is its own
     *            ring there; video stories have no poster, so they need this one).
     *            Empty → image stories use the media itself, video stories fall
     *            back to the shop logo.
     */
    public $icon;

    public string $videoUrl = '';

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
        $this->startDate = now()->toDateString();
        $this->startTime = now()->format('H:i');
        $this->endDate = now()->addHours(24)->toDateString();
        $this->endTime = now()->addHours(24)->format('H:i');
    }

    protected function shop(): Shop
    {
        return once(fn () => Shop::ensureForSeller(auth('seller')->user()));
    }

    // ── form lifecycle ───────────────────────────────────────────────

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

    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->formOpen = true;
        $this->resetErrorBag();
    }

    public function openEdit(int $storyId): void
    {
        $story = Story::query()
            ->where('shop_id', $this->shop()->id)
            ->find($storyId);

        if ($story === null) {
            return;
        }

        $this->resetForm();
        $this->editingId = $story->id;
        $this->type = $story->type;
        $this->videoUrl = (string) $story->video_url;
        $this->productId = $story->product_id !== null ? (string) $story->product_id : '';
        $this->caption = (string) $story->caption;
        $this->linkUrl = (string) $story->link_url;
        $this->linkTitle = (string) $story->link_title;
        $this->likesEnabled = $story->likes_enabled;
        $this->commentsEnabled = $story->comments_enabled;
        $this->startMode = 'custom';
        $this->startDate = $story->starts_at?->toDateString() ?: now()->toDateString();
        $this->startTime = $story->starts_at?->format('H:i') ?: now()->format('H:i');
        $this->duration = 'custom';
        $this->endDate = $story->ends_at?->toDateString() ?: now()->addHours(24)->toDateString();
        $this->endTime = $story->ends_at?->format('H:i') ?: now()->addHours(24)->format('H:i');
        $this->formOpen = true;
        $this->resetErrorBag();
    }

    public function closeForm(): void
    {
        $this->formOpen = false;
        $this->resetForm();
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

    /** Published products of this shop, filtered by the picker search. */
    public function getProductOptionsProperty(): Collection
    {
        $query = Product::query()
            ->where('shop_id', $this->shop()->id)
            ->where('status', 'published')
            ->orderBy('name');

        if (trim($this->productSearch) !== '') {
            $query->where('name', 'like', '%'.trim($this->productSearch).'%');
        }

        return $query->limit(20)->get(['id', 'name']);
    }

    public function getSelectedProductProperty(): ?Product
    {
        if ($this->productId === '' || ! ctype_digit($this->productId)) {
            return null;
        }

        return Product::query()
            ->where('shop_id', $this->shop()->id)
            ->find((int) $this->productId);
    }

    /** The story being edited — drives the modal's current-icon preview. */
    public function getEditingStoryProperty(): ?Story
    {
        if ($this->editingId === null) {
            return null;
        }

        return Story::query()
            ->where('shop_id', $this->shop()->id)
            ->find($this->editingId);
    }

    public function render()
    {
        return view('livewire.seller.stories-page', [
            'stories' => $this->stories,
            'productOptions' => $this->productOptions,
            'selectedProduct' => $this->selectedProduct,
            'editingStory' => $this->editingStory,
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

        $story = Story::query()->create($this->payload() + [
            'shop_id' => $this->shop()->id,
            'status' => $this->moderation === 'manual' ? Story::STATUS_PENDING : Story::STATUS_APPROVED,
        ]);

        ActivityLogger::log(
            'story.created',
            'استوری جدید برای فروشگاه «'.$this->shop()->name.'» از پنل فروشنده ثبت شد'.($story->status === Story::STATUS_PENDING ? ' (در انتظار تأیید مدیر)' : ''),
            subject: $story,
        );

        if ($story->status === Story::STATUS_PENDING) {
            AdminNotifier::notify(
                'story.pending',
                'استوری در انتظار تأیید ⏳',
                'فروشگاه «'.$this->shop()->name.'» استوری جدیدی ثبت کرد و منتظر تایید شماست.',
                route('admin.stories'),
                'zap',
                $story,
            );
        }

        $this->closeForm();
        $this->dispatch('panel-toast', message: $story->status === Story::STATUS_PENDING
            ? 'استوری ثبت شد و پس از تایید مدیر منتشر می‌شود. ⏳'
            : 'استوری منتشر شد. ✅', type: 'success');
    }

    public function updateStory(): void
    {
        $story = Story::query()
            ->where('shop_id', $this->shop()->id)
            ->find($this->editingId);

        if ($story === null) {
            $this->closeForm();

            return;
        }

        $this->validateWithToast(fn () => $this->validate($this->rules(), $this->messages()));

        // Replacing the poster/media of an image story → drop the old file.
        if ($this->type === Story::TYPE_IMAGE && $this->media && $story->media_path) {
            Storage::disk('public')->delete($story->media_path);
        }

        // Replacing the ring icon → drop the old file.
        if ($this->icon && $story->icon_path) {
            Storage::disk('public')->delete($story->icon_path);
        }

        $story->update($this->payload());

        ActivityLogger::log('story.updated', 'استوری #'.$story->id.' فروشگاه «'.$this->shop()->name.'» ویرایش شد', subject: $story);

        $this->closeForm();
        $this->dispatch('panel-toast', message: 'استوری ویرایش شد. ✅', type: 'success');
    }

    public function destroy(int $storyId): void
    {
        $story = Story::query()
            ->where('shop_id', $this->shop()->id)
            ->find($storyId);

        if ($story === null) {
            return;
        }

        if ($story->media_path) {
            Storage::disk('public')->delete($story->media_path);
        }

        if ($story->icon_path) {
            Storage::disk('public')->delete($story->icon_path);
        }

        $story->delete();

        ActivityLogger::log('story.deleted', 'استوری #'.$storyId.' فروشگاه «'.$this->shop()->name.'» توسط فروشنده حذف شد');

        $this->dispatch('panel-toast', message: 'استوری حذف شد. 🗑️', type: 'success');
    }

    // ── helpers ──────────────────────────────────────────────────────

    /** Shared create/update payload from the modal fields. */
    private function payload(): array
    {
        $startsAt = $this->startMode === 'now'
            ? now()
            : Carbon::createFromFormat('Y-m-d H:i', $this->startDate.' '.$this->startTime, config('app.timezone'));

        $endsAt = $this->duration !== 'custom'
            ? $startsAt->copy()->addHours((int) $this->duration)
            : Carbon::createFromFormat('Y-m-d H:i', $this->endDate.' '.$this->endTime, config('app.timezone'));

        $payload = [
            'product_id' => $this->productId !== '' ? (int) $this->productId : null,
            'type' => $this->type,
            'video_url' => $this->type === Story::TYPE_VIDEO ? $this->videoUrl : null,
            'media_path' => $this->type === Story::TYPE_IMAGE && $this->media
                ? $this->media->store('stories/'.$this->shop()->id, 'public')
                : null,
            'icon_path' => $this->icon
                ? $this->icon->store('stories/'.$this->shop()->id.'/icons', 'public')
                : null,
            'caption' => $this->caption !== '' ? $this->caption : null,
            'link_url' => $this->linkUrl !== '' ? $this->linkUrl : null,
            'link_title' => $this->linkUrl !== '' && $this->linkTitle !== '' ? $this->linkTitle : null,
            'likes_enabled' => $this->likesEnabled,
            'comments_enabled' => $this->commentsEnabled,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
        ];

        // Editing WITHOUT picking a new image keeps the current one.
        if ($this->editingId !== null && $payload['media_path'] === null) {
            unset($payload['media_path']);
        }

        // Editing WITHOUT picking a new icon keeps the current one.
        if ($this->editingId !== null && $payload['icon_path'] === null) {
            unset($payload['icon_path']);
        }

        return $payload;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->type = Story::TYPE_IMAGE;
        $this->media = null;
        $this->icon = null;
        $this->videoUrl = '';
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

    /** Direct-video link guard lives on the Story model (shared with admin). */
    protected function rules(): array
    {
        $isVideo = $this->type === Story::TYPE_VIDEO;
        // Editing an image story without replacing the file keeps the current one.
        $mediaRequired = ! $isVideo && $this->editingId === null;

        return [
            'type' => ['required', 'in:image,video'],
            'media' => array_filter([
                $mediaRequired ? 'required' : 'nullable',
                $isVideo ? null : 'image',
                $isVideo ? null : 'mimes:jpg,jpeg,png,webp',
                $isVideo ? null : 'max:5120',
            ]),
            'icon' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
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
            'media.required' => 'تصویر استوری را انتخاب کنید.',
            'media.image' => 'فایل باید تصویر باشد.',
            'media.mimes' => 'فرمت‌های مجاز: jpg، jpeg، png، webp.',
            'media.max' => 'حجم تصویر حداکثر ۵ مگابایت است.',
            'icon.image' => 'تصویر آیکون باید یک فایل تصویری باشد.',
            'icon.mimes' => 'فرمت‌های مجاز آیکون: jpg، jpeg، png، webp.',
            'icon.max' => 'حجم تصویر آیکون حداکثر ۲ مگابایت است.',
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
