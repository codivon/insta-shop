{{-- One tree row: grip + thumb + name/slug + badges + actions. --}}
@php
    $isEditing = $editingId === $category->id;
    $hasChildren = (int) $category->children_count > 0;
    $isExpanded = in_array($category->id, $expanded);
    $children = match (true) {
        $depth === 0 => $subs->get($category->id, collect()),
        $depth === 1 => $level3->get($category->id, collect()),
        default      => collect(),
    };
@endphp

<div class="cat-row"
     wire:key="cat-row-{{ $category->id }}">

    @if ($isEditing)
        <form wire:submit="updateEdit" class="rounded-2xl border-2 border-primary-300 bg-primary-50/40 p-3 dark:border-primary-700 dark:bg-primary-950/20 {{ $depth > 0 ? 'sm:mr-'.($depth * 5) : '' }}">
            <div class="grid gap-2 sm:grid-cols-4">
                <input type="text" wire:model="editingName" maxlength="120" placeholder="نام دسته"
                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                <input type="text" wire:model="editingSlug" dir="ltr" maxlength="190" placeholder="نامک (اختیاری)"
                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                <input type="text" wire:model="editingImage" dir="ltr" maxlength="400" placeholder="تصویر (URL)"
                       class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold outline-none focus:border-primary-400 dark:border-slate-700 dark:bg-slate-800">
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 rounded-xl bg-primary-600 px-3 py-2 text-[11px] font-extrabold text-white transition hover:bg-primary-700 active:scale-95">ذخیره</button>
                    <button type="button" wire:click="cancelEdit" class="rounded-xl border border-slate-200 px-3 py-2 text-[11px] font-bold text-slate-500 transition hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">انصراف</button>
                </div>
            </div>
            @error('editingName') <p class="mt-1 text-[9.5px] font-bold text-rose-500">{{ $message }}</p> @enderror
            @error('editingSlug') <p class="mt-1 text-[9.5px] font-bold text-rose-500">{{ $message }}</p> @enderror
        </form>
    @else
        <div class="cat-handle group flex items-center gap-2 rounded-2xl border border-slate-200 bg-white px-2.5 py-2 transition hover:border-slate-300 dark:border-slate-800 dark:bg-slate-950/40 {{ $depth === 1 ? 'bg-slate-50/60 dark:bg-slate-900/40' : '' }} {{ $depth === 2 ? 'bg-slate-50 dark:bg-slate-900/60' : '' }} {{ $depth > 0 ? 'sm:mr-'.($depth * 5) : '' }}"
             draggable="true"
             :class="dragCls({{ $category->id }})"
             x-on:dragstart="start({{ $category->id }})"
             x-on:dragover.prevent.stop="over({{ $category->id }}, $event)"
             x-on:dragleave.stop="leave()"
             x-on:drop.prevent.stop="drop({{ $category->id }})"
             x-on:dragend="dragId = 0; overId = 0; pos = ''">
            <span class="cursor-grab shrink-0 text-slate-300 transition group-hover:text-slate-400 active:cursor-grabbing dark:text-slate-600" title="بکشید و رها کنید">
                <x-icon name="grip-vertical" class="size-4" />
            </span>

            <span class="grid size-9 shrink-0 place-items-center overflow-hidden rounded-xl bg-slate-100 dark:bg-slate-800">
                @if ($category->image)
                    <img src="{{ $category->image }}" alt="" loading="lazy" class="size-9 object-contain p-0.5">
                @else
                    <x-icon name="tag" class="size-3.5 text-slate-400" />
                @endif
            </span>

            <div class="min-w-0 flex-1">
                <div class="truncate text-xs font-extrabold text-slate-700 dark:text-slate-200">{{ $category->name }}</div>
                <div class="truncate text-[9.5px] text-slate-400" dir="ltr">
                    {{ $category->slug ?? '—' }} @if ($hasChildren) · {{ \App\Support\Fmt::fa($category->children_count) }} زیر‌دسته @endif
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-1">
                @if ($hasChildren)
                    <button type="button" wire:click="toggleExpand({{ $category->id }})"
                            class="grid size-7 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-primary-600 dark:hover:bg-slate-800"
                            :aria-expanded="$wire.expanded.includes({{ $category->id }}) ? 'true' : 'false'"
                            aria-label="نمایش زیر‌دسته‌ها">
                        <span class="grid place-items-center transition-transform" :class="$wire.expanded.includes({{ $category->id }}) ? 'rotate-180' : ''">
                            <x-icon name="chevron-down" class="size-4" />
                        </span>
                    </button>
                @endif
                <button type="button" wire:click="moveUp({{ $category->id }})" wire:loading.attr="disabled" wire:target="moveUp, moveDown, moveCat"
                        class="grid size-7 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-primary-600 dark:hover:bg-slate-800" aria-label="بالا">
                    <x-icon name="chevron-up" class="size-3.5" />
                </button>
                <button type="button" wire:click="moveDown({{ $category->id }})" wire:loading.attr="disabled" wire:target="moveUp, moveDown, moveCat"
                        class="grid size-7 place-items-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-primary-600 dark:hover:bg-slate-800" aria-label="پایین">
                    <x-icon name="chevron-down" class="size-3.5" />
                </button>
                <button type="button" wire:click="startEdit({{ $category->id }})"
                        class="grid size-7 place-items-center rounded-lg text-slate-400 transition hover:bg-primary-50 hover:text-primary-600 dark:hover:bg-slate-800" aria-label="ویرایش">
                    <x-icon name="edit" class="size-3.5" />
                </button>
                <button type="button" wire:click="remove({{ $category->id }})" wire:loading.attr="disabled" wire:target="remove"
                        wire:confirm="این دسته حذف شود؟ محصولات بدون دسته می‌شوند."
                        class="grid size-7 place-items-center rounded-lg text-slate-400 transition hover:bg-rose-50 hover:text-rose-600 dark:hover:bg-rose-950/40" aria-label="حذف">
                    <x-icon name="trash" class="size-3.5" />
                </button>
            </div>
        </div>
    @endif
</div>

@if ($hasChildren && ($isExpanded || $depth === 0))
    <div class="mt-1 space-y-1.5 border-r-2 border-dashed border-slate-200 pr-2 dark:border-slate-800">
        @foreach ($children as $child)
            @include('livewire.admin.partials.category-row', ['category' => $child, 'depth' => $depth + 1])
        @endforeach
    </div>
@endif
