<?php

namespace App\Livewire\Admin\Notifications;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\AdminNotification;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin-slot')]
#[Title('نوتیفیکیشن‌ها')]
class NotificationsPage extends Component
{
    use GuardsPermissions;
    use WithPagination;

    protected static string $permission = 'admin.notifications';

    /** all | unread */
    public string $filter = 'all';

    public function updatedFilter(): void
    {
        $this->resetPage();
    }

    public function markRead(int $id): void
    {
        AdminNotification::query()->whereKey($id)->first()?->markRead();
    }

    public function markAllRead(): void
    {
        AdminNotification::query()->unread()->update(['is_read' => true, 'read_at' => now()]);
    }

    public function clearRead(): void
    {
        AdminNotification::query()->where('is_read', true)->delete();
    }

    public function render()
    {
        $items = AdminNotification::query()
            ->when($this->filter === 'unread', fn ($q) => $q->unread())
            ->latest()
            ->paginate(20);

        return view('livewire.admin.notifications.notifications-page', [
            'items'       => $items,
            'unreadCount' => AdminNotification::query()->unread()->count(),
        ])->title('نوتیفیکیشن‌ها')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — نوتیفیکیشن‌ها']);
    }
}
