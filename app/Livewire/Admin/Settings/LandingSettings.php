<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\Setting;
use App\Support\ActivityLogger;
use App\Support\LandingContent;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Admin — «صفحهٔ اصلی» settings section (inside the settings hub).
 *
 * Full customization of the landing page: every text/list on the page
 * is editable here, whole sections can be HIDDEN and REORDERED.
 *
 * Layout (order + enabled map) persists IMMEDIATELY on every move /
 * toggle; content edits save per-section via saveSection(key) so an
 * admin can iterate on one block without a giant single form.
 *
 * Lists are edited through generic dotted-path add/remove (addRow /
 * removeRow) — e.g. 'stats.items', 'footer.groups.0.links' — with
 * per-path blank-row shapes; all inputs bind wire:model into
 * $content.{section}.{...} and are persisted as JSON settings.
 */
#[Title('تنظیمات صفحهٔ اصلی')]
class LandingSettings extends Component
{
    use GuardsPermissions;

    protected static string $permission = 'admin.settings.landing';

    /** Max rows per editable list — keeps payloads sane. */
    private const MAX_ROWS = 24;

    public array $order = [];

    public array $enabled = [];

    /** key => full content array (defaults merged over at mount). */
    public array $content = [];

    public function mount(): void
    {
        $layout = LandingContent::layout();

        $this->order = $layout['order'];
        $this->enabled = $layout['enabled'];

        foreach (array_keys($this->editorMeta()) as $key) {
            $this->content[$key] = LandingContent::section($key);
        }
    }

    /* ── layout: reorder / show-hide (persisted instantly) ───────── */

    public function moveUp(string $key): void
    {
        $this->move($key, -1);
    }

    public function moveDown(string $key): void
    {
        $this->move($key, +1);
    }

    private function move(string $key, int $direction): void
    {
        $index = array_search($key, $this->order, true);

        if ($index === false || ! isset($this->order[$index + $direction])) {
            return;
        }

        $swap = $index + $direction;
        [$this->order[$index], $this->order[$swap]] = [$this->order[$swap], $this->order[$index]];

        $this->persistLayout();
    }

    public function toggleSection(string $key): void
    {
        if (! isset(LandingContent::sections()[$key])) {
            return;
        }

        $this->enabled[$key] = ! ($this->enabled[$key] ?? true);

        $this->persistLayout();
    }

    private function persistLayout(): void
    {
        $this->order = array_values(array_intersect($this->order, array_keys(LandingContent::sections())));

        Setting::set('landing.layout', [
            'order' => $this->order,
            'enabled' => $this->enabled,
        ], 'json');

        $this->dispatch('panel-toast', message: 'چیدمان صفحهٔ اصلی به‌روز شد. ✅', type: 'success');
    }

    /* ── generic list row editing (dotted paths into $content) ───── */

    /**
     * Append a blank row — path points at the LIST
     * (e.g. 'stats.items', 'footer.groups.0.links', 'panels.cards').
     */
    public function addRow(string $path): void
    {
        $rows = &$this->resolve($path);

        if (count($rows) >= self::MAX_ROWS) {
            return;
        }

        $rows[] = $this->blankRow($path);
    }

    /**
     * Remove one row — path points at the exact ROW
     * (e.g. 'stats.items.2'). The last remaining row is never removed
     * so sections can never end up structurally empty by accident.
     */
    public function removeRow(string $path): void
    {
        $parts = explode('.', $path);
        $index = (int) array_pop($parts);
        $rows = &$this->resolve(implode('.', $parts));

        if (! isset($rows[$index]) || count($rows) <= 1) {
            return;
        }

        unset($rows[$index]);
        $rows = array_values($rows);
    }

    /** Walk (creating when needed) the dotted path inside $content. */
    private function &resolve(string $path): array
    {
        $ref = &$this->content;

        foreach (explode('.', $path) as $segment) {
            if (! isset($ref[$segment]) || ! is_array($ref[$segment])) {
                $ref[$segment] = [];
            }

            $ref = &$ref[$segment];
        }

        return $ref;
    }

    /** Blank-row shape per target list. */
    private function blankRow(string $path): array|string
    {
        $segments = explode('.', $path);
        $leaf = end($segments);

        return match (true) {
            $path === 'stats.items' => ['n' => '', 'l' => ''],
            $path === 'hero.ticks',
            $path === 'infra.items' => '',
            $path === 'features.small' => ['icon' => 'check', 't' => ''],
            $path === 'panels.cards' => [
                'icon' => 'star', 'title' => '', 'badge' => '',
                'link_label' => '', 'link_href' => '#', 'items' => [''],
            ],
            str_starts_with($path, 'panels.cards.') && $leaf === 'items' => '',
            $path === 'how.steps' => ['icon' => 'check', 'title' => '', 'desc' => ''],
            $path === 'faq.items' => ['q' => '', 'a' => ''],
            $path === 'footer.groups' => ['title' => '', 'links' => [['label' => '', 'href' => '']]],
            $leaf === 'links' => ['label' => '', 'href' => ''],
            default => '',
        };
    }

    /* ── content persistence ─────────────────────────────────────── */

    public function saveSection(string $key): void
    {
        $meta = $this->editorMeta()[$key] ?? null;

        if ($meta === null || ! isset($this->content[$key])) {
            return;
        }

        $data = $this->sanitize($this->content[$key], $key);

        $snapshot = ActivityLogger::settingsSnapshot(["landing.content.{$key}"]);

        Setting::set("landing.content.{$key}", $data, 'json');

        ActivityLogger::logSettingsChanged(
            "صفحهٔ اصلی — بخش «{$meta['label']}» ویرایش شد",
            $snapshot,
            ["landing.content.{$key}" => 'ویرایش شد'],
        );

        $this->dispatch('panel-toast', message: "بخش «{$meta['label']}» ذخیره شد. ✅", type: 'success');
    }

    public function resetSection(string $key): void
    {
        $meta = $this->editorMeta()[$key] ?? null;

        if ($meta === null) {
            return;
        }

        $this->content[$key] = LandingContent::defaults()[$key];

        $snapshot = ActivityLogger::settingsSnapshot(["landing.content.{$key}"]);

        Setting::set("landing.content.{$key}", $this->content[$key], 'json');

        ActivityLogger::logSettingsChanged(
            "صفحهٔ اصلی — بخش «{$meta['label']}» به پیش‌فرض بازگشت",
            $snapshot,
            ["landing.content.{$key}" => 'بازگردانی به پیش‌فرض'],
        );

        $this->dispatch('panel-toast', message: "بخش «{$meta['label']}» به پیش‌فرض برگشت. ↩️", type: 'success');
    }

    /** Trim strings recursively + a few numeric coercions. */
    private function sanitize(array $data, string $key): array
    {
        array_walk_recursive($data, function (&$value): void {
            $value = is_string($value) ? trim($value) : $value;
        });

        if ($key === 'infra') {
            $data['speed'] = max(6, min(120, (int) ($data['speed'] ?? 32)));
        }

        return $data;
    }

    public function render()
    {
        return view('livewire.admin.settings.landing-settings', [
            'sectionsMeta' => $this->editorMeta(),
        ]);
    }

    /**
     * Editor registry = the movable sections PLUS the footer (footer is
     * NOT movable — it always stays the last element of the page — but
     * it is fully EDITABLE, so it must appear in the editors list).
     */
    private function editorMeta(): array
    {
        return LandingContent::sections() + [
            'footer' => [
                'label' => 'فوتر',
                'icon' => 'list',
                'description' => 'معرفی، منوهای دسترسی سریع و خط کپی‌رایت',
            ],
        ];
    }
}
