<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\Backup;
use App\Models\Setting;
use App\Support\ActivityLogger;
use App\Support\Backups\BackupManager;
use App\Support\Backups\BackupRunner;
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

/**
 * Admin — «پشتیبان‌گیری»: build the full DB backup, upload + restore an
 * archive (chunked upload, background restore, duplicate rows skipped),
 * browse / download / delete every archive. Newest always on top.
 */
#[Layout('layouts.admin-slot')]
#[Title('پشتیبان‌گیری')]
class BackupsPage extends Component
{
    use GuardsPermissions;
    use WithPagination;

    protected static string $permission = 'admin.backups';

    /** Delete-confirmation modal. */
    public ?int $deleteId = null;

    public function startBackup(): void
    {
        if (Backup::query()->running()->exists()) {
            $this->dispatch('panel-toast', message: 'یک عملیات پشتیبان‌گیری/بازگردانی در حال اجراست — کمی صبر کنید.', type: 'warning');

            return;
        }

        $manager = app(BackupManager::class);
        $backup = $manager->create(Backup::TYPE_MANUAL, auth('admin')->id());

        BackupRunner::dispatchBackup($backup);

        ActivityLogger::log('backup.started', 'پشتیبان‌گیری دستی شروع شد', after: [
            'backup_id' => $backup->id,
        ]);

        $this->dispatch('panel-toast', message: 'پشتیبان‌گیری در پس‌زمینه شروع شد — نتیجه را در همین صفحه ببینید. ✅', type: 'success');
    }

    public function confirmDelete(int $id): void
    {
        $this->deleteId = $id;
    }

    public function cancelDelete(): void
    {
        $this->deleteId = null;
    }

    public function deleteBackup(): void
    {
        $backup = Backup::query()->find($this->deleteId);

        if ($backup) {
            if ($backup->isRunning()) {
                $this->dispatch('panel-toast', message: 'نمی‌توان فایل در حال پردازش را حذف کرد.', type: 'warning');
                $this->deleteId = null;

                return;
            }

            $name = $backup->filename;
            app(BackupManager::class)->delete($backup);

            ActivityLogger::log('backup.deleted', 'فایل پشتیبان حذف شد', after: [
                'filename' => $name,
            ]);

            $this->dispatch('panel-toast', message: 'فایل پشتیبان حذف شد. 🗑️', type: 'success');
        }

        $this->deleteId = null;
    }

    /** Fired from the browser JS after the chunked upload finished. */
    #[On('backups.changed')]
    public function afterUpload(string $message = '', string $type = 'success'): void
    {
        if ($message !== '') {
            $this->dispatch('panel-toast', message: $message, type: $type);
        }
    }

    public function render()
    {
        $backups = Backup::query()->latest()->paginate(20);

        $cronStamp = (string) Setting::get('developer.cron_last_run', '');
        $cronAlive = $cronStamp !== ''
            && Carbon::parse($cronStamp)->diffInSeconds(now()) <= (int) config('instashop.cron_heartbeat_seconds', 130);

        $autoEnabled = (bool) Setting::get('backups.auto_enabled', false);
        $nextRun = BackupManager::nextAutoRunAt();

        return view('livewire.admin.backups.backups-page', [
            'backups' => $backups,
            'stats' => [
                'count' => Backup::query()->count(),
                'size' => (int) Backup::query()->where('status', Backup::STATUS_COMPLETED)->sum('size'),
            ],
            'running' => Backup::query()->running()->exists(),
            'cronAlive' => $cronAlive,
            'autoEnabled' => $autoEnabled,
            'nextRun' => $nextRun,
        ])->title('پشتیبان‌گیری')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — پشتیبان‌گیری']);
    }
}
