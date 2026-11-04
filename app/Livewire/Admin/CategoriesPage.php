<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Category;
use App\Support\ActivityLogger;
use Closure;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Admin — the platform-wide category tree (Basalam-style, 3 levels):
 *
 *  - add / rename / delete categories (sellers can NOT),
 *  - reorder siblings AND re-parent between branches via native
 *    drag & drop («کشیدن و رها کردن»),
 *  - level-3 nodes load on demand so the seeded 1116-node tree stays fast.
 */
#[Layout('layouts.admin-slot')]
class CategoriesPage extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;

    protected static string $permission = 'admin.categories';

    /* ── add form ── */
    public string $newName = '';

    public int $newParentId = 0;

    public string $newSlug = '';

    public string $newImage = '';

    /* ── inline edit ── */
    public ?int $editingId = null;

    public string $editingName = '';

    public string $editingSlug = '';

    public string $editingImage = '';

    /** Level-2 category ids whose children are currently visible. */
    public array $expanded = [];

    public function toggleExpand(int $id): void
    {
        if (in_array($id, $this->expanded, true)) {
            $this->expanded = array_values(array_diff($this->expanded, [$id]));

            return;
        }

        $this->expanded[] = $id;
    }

    /* ══════════ add ══════════ */

    public function add(): void
    {
        $this->validateWithToast(fn () => $this->validate([
            'newName' => ['required', 'string', 'min:2', 'max:120'],
            // 0 = «دسته اصلی (ریشه)» — a real parent check only for > 0
            'newParentId' => ['integer', 'min:0', function (string $attribute, mixed $value, Closure $fail) {
                if ((int) $value > 0 && ! Category::query()->whereKey((int) $value)->exists()) {
                    $fail('والد انتخاب‌شده معتبر نیست.');
                }
            }],
            'newSlug' => ['nullable', 'string', 'max:190', 'regex:/^[a-z0-9\x{0600}-\x{06FF}]+(?:-[a-z0-9\x{0600}-\x{06FF}]+)*$/u'],
            'newImage' => ['nullable', 'string', 'max:400'],
        ], [
            'newName.required' => 'نام دسته را وارد کنید.',
            'newName.min' => 'نام دسته حداقل ۲ کاراکتر است.',
            'newSlug.regex' => 'نامک فقط می‌تواند شامل حروف کوچک انگلیسی/فارسی، عدد و خط تیره باشد.',
        ]));

        $maxSort = (int) Category::query()
            ->where('parent_id', $this->newParentId > 0 ? $this->newParentId : null)
            ->max('sort');

        Category::query()->create([
            'parent_id' => $this->newParentId > 0 ? $this->newParentId : null,
            'name' => trim($this->newName),
            'slug' => $this->uniqueSlug(trim($this->newSlug) !== '' ? trim($this->newSlug) : null),
            'image' => trim($this->newImage) !== '' ? trim($this->newImage) : null,
            'basalam_id' => null,
            'sort' => $maxSort + 1,
        ]);

        ActivityLogger::log('category.created', 'Category', null, ['name' => trim($this->newName)]);

        $this->reset(['newName', 'newSlug', 'newImage']);
        $this->newParentId = 0;

        $this->dispatch('toast', message: 'دسته‌بندی اضافه شد.');
    }

    /* ══════════ rename ══════════ */

    public function startEdit(int $id): void
    {
        $category = Category::query()->find($id);

        if (! $category) {
            return;
        }

        $this->editingId = $category->id;
        $this->editingName = $category->name;
        $this->editingSlug = (string) ($category->slug ?? '');
        $this->editingImage = (string) ($category->image ?? '');
    }

    public function updateEdit(): void
    {
        if (! $this->editingId) {
            return;
        }

        $this->validateWithToast(fn () => $this->validate([
            'editingName' => ['required', 'string', 'min:2', 'max:120'],
            'editingSlug' => ['nullable', 'string', 'max:190', 'regex:/^[a-z0-9\x{0600}-\x{06FF}]+(?:-[a-z0-9\x{0600}-\x{06FF}]+)*$/u'],
            'editingImage' => ['nullable', 'string', 'max:400'],
        ], [
            'editingName.required' => 'نام دسته را وارد کنید.',
        ]));

        $category = Category::query()->find($this->editingId);

        if (! $category) {
            return;
        }

        $category->update([
            'name' => trim($this->editingName),
            'slug' => trim($this->editingSlug) !== '' ? $this->uniqueSlug(trim($this->editingSlug), $category->id) : null,
            'image' => trim($this->editingImage) !== '' ? trim($this->editingImage) : null,
        ]);

        $this->cancelEdit();

        $this->dispatch('toast', message: 'دسته‌بندی به‌روزرسانی شد.');
    }

    public function cancelEdit(): void
    {
        $this->editingId = null;
        $this->editingName = '';
        $this->editingSlug = '';
        $this->editingImage = '';
    }

    /* ══════════ delete ══════════ */

    public function remove(int $id): void
    {
        $category = Category::query()->find($id);

        if (! $category) {
            return;
        }

        if ($category->children()->exists()) {
            $this->dispatch('toast', message: 'اول زیر‌دسته‌های این دسته را حذف کنید.', type: 'error');

            return;
        }

        ActivityLogger::log('category.deleted', 'Category', subject: $category, after: ['name' => $category->name]);

        // products keep their data; their category becomes «بدون دسته» (FK nullOnDelete)
        $category->delete();

        $this->dispatch('toast', message: 'دسته‌بندی حذف شد.');
    }

    /* ══════════ drag & drop + arrows ══════════ */

    /**
     * Drop handler: $pos ∈ before | after | inside.
     * before/after → sibling of the target; inside → child of the target.
     */
    public function moveCat(int $dragId, int $targetId, string $pos): void
    {
        $drag = Category::query()->find($dragId);
        $target = Category::query()->find($targetId);

        if (! $drag || ! $target || $drag->id === $target->id) {
            return;
        }

        if (! in_array($pos, ['before', 'after', 'inside'], true)) {
            return;
        }

        // never drop a node inside its own subtree (cycle)
        $ancestor = $target;

        while ($ancestor !== null) {
            if ($ancestor->id === $drag->id) {
                $this->dispatch('toast', message: 'نمی‌توان دسته را داخل زیر‌شاخهٔ خودش برد.', type: 'error');

                return;
            }

            $ancestor = $ancestor->parent;
        }

        if ($pos === 'inside') {
            $maxSort = (int) Category::query()->where('parent_id', $target->id)->max('sort');
            $drag->parent_id = $target->id;
            $drag->sort = $maxSort + 1;
            $drag->save();
        } else {
            // sibling of the target: park the drag out of the way, shift the
            // target's tail right by one, then slot the drag into the gap.
            $drag->sort = 0;
            $drag->save();

            $parentId = $target->parent_id;

            if ($pos === 'before') {
                Category::query()->where('parent_id', $parentId)->where('sort', '>=', $target->sort)->increment('sort');
                $drag->sort = (int) $target->sort;
            } else {
                Category::query()->where('parent_id', $parentId)->where('sort', '>', $target->sort)->increment('sort');
                $drag->sort = (int) $target->sort + 1;
            }

            $drag->parent_id = $parentId;
            $drag->save();
        }

        $this->normalizeSiblings($drag->parent_id);
    }

    public function moveUp(int $id): void
    {
        $this->swapWithNeighbor($id, 'up');
    }

    public function moveDown(int $id): void
    {
        $this->swapWithNeighbor($id, 'down');
    }

    private function swapWithNeighbor(int $id, string $dir): void
    {
        $category = Category::query()->find($id);

        if (! $category) {
            return;
        }

        $neighborQuery = Category::query()->where('parent_id', $category->parent_id);

        $neighbor = $dir === 'up'
            ? (clone $neighborQuery)->where('sort', '<', $category->sort)->orderByDesc('sort')->first()
            : (clone $neighborQuery)->where('sort', '>', $category->sort)->orderBy('sort')->first();

        if ($neighbor) {
            [$category->sort, $neighbor->sort] = [$neighbor->sort, $category->sort];
            $category->save();
            $neighbor->save();
        }
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getRootsProperty(): Collection
    {
        return Category::query()
            ->whereNull('parent_id')
            ->withCount('children')
            ->orderBy('sort')
            ->orderBy('id')
            ->get();
    }

    /** Level-2 nodes grouped by their root id. */
    public function getSubsProperty(): Collection
    {
        $rootIds = $this->roots->pluck('id');

        return Category::query()
            ->whereIn('parent_id', $rootIds)
            ->withCount('children')
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->groupBy('parent_id');
    }

    /** Level-3 nodes of the expanded level-2 nodes, grouped by parent. */
    public function getLevel3Property(): Collection
    {
        if ($this->expanded === []) {
            return collect();
        }

        return Category::query()
            ->whereIn('parent_id', $this->expanded)
            ->withCount('children')
            ->orderBy('sort')
            ->orderBy('id')
            ->get()
            ->groupBy('parent_id');
    }

    /** Parent options for the «افزودن دسته» select (roots + level-2). */
    public function getParentOptionsProperty(): Collection
    {
        return $this->roots
            ->flatMap(function ($root) {
                $out = [[
                    'id' => $root->id,
                    'label' => $root->name.' (دسته اصلی)',
                ]];

                foreach ($this->subs->get($root->id, collect()) as $sub) {
                    $out[] = [
                        'id' => $sub->id,
                        'label' => $root->name.' › '.$sub->name,
                    ];
                }

                return $out;
            });
    }

    public function render()
    {
        return view('livewire.admin.categories-page', [
            'total' => Category::query()->count(),
            'parentOptions' => $this->parentOptions,
            'roots' => $this->roots,
            'subs' => $this->subs,
            'level3' => $this->level3,
        ])->title('مدیریت دسته‌بندی‌ها')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — دسته‌بندی‌ها']);
    }

    // ── Helpers ──────────────────────────────────────────────────────

    /** Re-number siblings (1..n) preserving current order. */
    private function normalizeSiblings(?int $parentId): void
    {
        $siblings = Category::query()
            ->where('parent_id', $parentId)
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        foreach ($siblings as $index => $sibling) {
            if ($sibling->sort !== $index + 1) {
                $sibling->sort = $index + 1;
                $sibling->saveQuietly();
            }
        }
    }

    /** Slug guaranteed unique (disambiguated with a numeric suffix). */
    private function uniqueSlug(?string $base, ?int $ignoreId = null): ?string
    {
        if ($base === null || $base === '') {
            return null;
        }

        $slug = $base;
        $i = 1;

        while (Category::query()->where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
