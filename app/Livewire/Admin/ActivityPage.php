<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\ActivityLog;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin — «فعالیت‌های اخیر» (recent activities / audit trail).
 * Every meaningful event lands here with its actor and — for edits —
 * the before/after values rendered as a readable diff.
 */
#[Layout('layouts.admin-slot')]
#[Title('فعالیت‌های اخیر')]
class ActivityPage extends Component
{
    use GuardsPermissions;
    use WithPagination;

    protected static string $permission = 'admin.activity';

    /** all | admin | seller | customer | system */
    public string $actor = 'all';

    /** expanded row ids (before/after diff) */
    public array $expanded = [];

    public function updatedActor(): void
    {
        $this->resetPage();
    }

    public function toggleExpand(int $id): void
    {
        if (in_array($id, $this->expanded, true)) {
            $this->expanded = array_values(array_diff($this->expanded, [$id]));
        } else {
            $this->expanded[] = $id;
        }
    }

    public function render()
    {
        $items = ActivityLog::query()
            ->when($this->actor !== 'all', fn ($q) => $q->where('actor_type', $this->actor))
            ->latest()
            ->paginate(25);

        return view('livewire.admin.activity.activity-page', [
            'items' => $items,
        ])->title('فعالیت‌های اخیر')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — فعالیت‌های اخیر']);
    }
}
