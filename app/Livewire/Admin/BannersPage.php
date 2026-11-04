<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Banner;
use App\Support\ActivityLogger;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

/**
 * Admin — home hero banners (بنرها و اسلایدها): the slider on the customer
 * home page. Create / edit / reorder (sort) / activate / delete — the
 * slide order IS the slider order.
 */
#[Layout('layouts.admin-slot')]
class BannersPage extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;
    use WithFileUploads;

    protected static string $permission = 'admin.banners';

    /** null = create mode, otherwise the banner being edited. */
    public ?int $editingId = null;

    public bool $formOpen = false;

    /** @var mixed Livewire temporary upload (image) */
    public $image;

    public string $title = '';

    public string $linkUrl = '';

    public string $sort = '0';

    public bool $isActive = true;

    public function toggleForm(?int $bannerId = null): void
    {
        $this->formOpen = ! $this->formOpen || ($bannerId !== null && $bannerId !== $this->editingId);

        if (! $this->formOpen) {
            $this->resetForm();

            return;
        }

        if ($bannerId !== null) {
            $banner = Banner::query()->findOrFail($bannerId);
            $this->editingId = $banner->id;
            $this->title = (string) $banner->title;
            $this->linkUrl = (string) $banner->link_url;
            $this->sort = (string) $banner->sort;
            $this->isActive = $banner->is_active;
            $this->image = null;
        }

        $this->resetErrorBag();
    }

    public function cancelForm(): void
    {
        $this->formOpen = false;
        $this->resetForm();
    }

    public function toggleActive(int $bannerId): void
    {
        $banner = Banner::query()->findOrFail($bannerId);
        $banner->forceFill(['is_active' => ! $banner->is_active])->save();

        ActivityLogger::log('banner.updated', 'بنر «'.($banner->title ?: '#'.$banner->id).'» '.($banner->is_active ? 'فعال' : 'غیرفعال').' شد', subject: $banner);

        $this->dispatch('panel-toast', message: $banner->is_active ? 'بنر فعال شد. ✅' : 'بنر غیرفعال شد. ⛔', type: 'success');
    }

    public function moveUp(int $bannerId): void
    {
        $this->move($bannerId, 'up');
    }

    public function moveDown(int $bannerId): void
    {
        $this->move($bannerId, 'down');
    }

    public function save(): void
    {
        $this->validateWithToast(fn () => $this->validate($this->rules(), $this->messages()));

        $payload = [
            'title' => $this->title !== '' ? $this->title : null,
            'link_url' => $this->linkUrl !== '' ? $this->linkUrl : null,
            'sort' => (int) $this->sort,
            'is_active' => $this->isActive,
        ];

        if ($this->editingId !== null) {
            $banner = Banner::query()->findOrFail($this->editingId);

            if ($this->image) {
                Storage::disk('public')->delete($banner->image_path);
                $payload['image_path'] = $this->image->store('banners', 'public');
            }

            $banner->update($payload);

            ActivityLogger::log('banner.updated', 'بنر «'.($banner->title ?: '#'.$banner->id).'» ویرایش شد', subject: $banner);
            $this->dispatch('panel-toast', message: 'بنر ویرایش شد. ✅', type: 'success');
        } else {
            $payload['image_path'] = $this->image->store('banners', 'public');

            $banner = Banner::query()->create($payload);

            ActivityLogger::log('banner.created', 'بنر جدید «'.($banner->title ?: '#'.$banner->id).'» ساخته شد', subject: $banner);
            $this->dispatch('panel-toast', message: 'بنر ساخته شد. ✅', type: 'success');
        }

        $this->cancelForm();
    }

    public function destroy(int $bannerId): void
    {
        $banner = Banner::query()->findOrFail($bannerId);

        Storage::disk('public')->delete($banner->image_path);
        $banner->delete();

        ActivityLogger::log('banner.deleted', 'بنر «'.($banner->title ?: '#'.$bannerId).'» حذف شد');

        $this->dispatch('panel-toast', message: 'بنر حذف شد. 🗑️', type: 'success');
    }

    // ── computed ─────────────────────────────────────────────────────

    public function getBannersProperty(): Collection
    {
        return Banner::query()->orderBy('sort')->orderBy('id')->get();
    }

    public function render()
    {
        return view('livewire.admin.banners-page', [
            'banners' => $this->banners,
        ])->title('بنرها و اسلایدها')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — بنرها و اسلایدها']);
    }

    // ── helpers ──────────────────────────────────────────────────────

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->image = null;
        $this->title = '';
        $this->linkUrl = '';
        $this->sort = (string) ((int) Banner::query()->max('sort') + 1);
        $this->isActive = true;
    }

    /** Move one slot up/down, then reindex — order stays clean forever. */
    private function move(int $bannerId, string $direction): void
    {
        $ids = Banner::query()
            ->where('position', 'home_hero')
            ->orderBy('sort')
            ->orderBy('id')
            ->pluck('id');

        $index = $ids->search($bannerId);

        if ($index === false) {
            return;
        }

        $target = $direction === 'up' ? $index - 1 : $index + 1;

        if ($target < 0 || $target >= $ids->count()) {
            return;
        }

        $ids->splice($index, 1)->splice($target, 0, [$bannerId]);

        foreach ($ids->values() as $i => $id) {
            Banner::query()->whereKey($id)->update(['sort' => $i + 1]);
        }
    }

    protected function rules(): array
    {
        $required = $this->editingId === null;

        return [
            'title' => ['nullable', 'string', 'max:150'],
            'image' => [$required ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'linkUrl' => ['nullable', 'url', 'max:500'],
            'sort' => ['required', 'integer', 'min:0', 'max:9999'],
            'isActive' => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'image.required' => 'تصویر بنر را انتخاب کنید.',
            'image.image' => 'فایل باید تصویر باشد.',
            'image.mimes' => 'فرمت‌های مجاز: jpg، jpeg، png، webp.',
            'image.max' => 'حجم تصویر حداکثر ۵ مگابایت است.',
            'title.max' => 'عنوان حداکثر ۱۵۰ کاراکتر است.',
            'linkUrl.url' => 'لینک باید یک آدرس اینترنتی معتبر باشد (با https:// شروع شود).',
            'linkUrl.max' => 'لینک حداکثر ۵۰۰ کاراکتر است.',
            'sort.required' => 'ترتیب نمایش را وارد کنید.',
            'sort.integer' => 'ترتیب باید عدد باشد.',
        ];
    }
}
