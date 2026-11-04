<?php

namespace App\Livewire\Admin\Support;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\Seller;
use App\Models\SupportTicket;
use App\Support\JDate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * Admin — «تیکت پشتیبانی»: every ticket opened by sellers and
 * customers, filterable by status with lazy loading and an
 * open-ticket badge in the sidebar.
 */
#[Layout('layouts.admin-slot')]
#[Title('تیکت پشتیبانی')]
class TicketsIndex extends Component
{
    use GuardsPermissions;

    protected static string $permission = 'admin.tickets';

    public string $statusFilter = 'open'; // open | answered | closed | all

    public int $perPage = 20;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    public function updating(string $property): void
    {
        if ($property === 'statusFilter') {
            $this->perPage = 20;
            $this->hasMore = true;
        }
    }

    /** Triggered by the IntersectionObserver sentinel at the bottom of the list. */
    public function loadMore(): void
    {
        $now = (int) (microtime(true) * 1000);

        if ($now - $this->lastLoadAt < 500) {
            return;
        }

        $this->lastLoadAt = $now;
        $this->perPage += 20;
    }

    /** Status tab counts (60 s cache — the sidebar badge uses the same key). */
    public function getCountsProperty(): array
    {
        return Cache::remember('ip:tickets:counts:v1', 60, function () {
            $rows = SupportTicket::query()
                ->selectRaw('status, COUNT(*) as c')
                ->groupBy('status')
                ->pluck('c', 'status');

            return [
                'open' => (int) ($rows[SupportTicket::STATUS_OPEN] ?? 0),
                'answered' => (int) ($rows[SupportTicket::STATUS_ANSWERED] ?? 0),
                'closed' => (int) ($rows[SupportTicket::STATUS_CLOSED] ?? 0),
            ];
        });
    }

    public function getTicketsProperty(): Collection
    {
        $query = SupportTicket::query()
            // ticketable only — Customer has no shop relation; the seller's
            // shop name is resolved lazily inside authorName()
            ->with(['latestMessage', 'ticketable'])
            ->when($this->statusFilter !== 'all', fn ($q) => $q->where('status', $this->statusFilter))
            ->orderByRaw('CASE WHEN status = ? THEN 0 ELSE 1 END ASC', [SupportTicket::STATUS_OPEN])
            ->orderByDesc('last_reply_at')
            ->orderByDesc('id');

        $total = (clone $query)->count();
        $rows = $query->limit($this->perPage)->get();

        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    /** Last activity string (Jalali). */
    public function activityLabel(SupportTicket $ticket): string
    {
        if ($ticket->last_reply_at === null) {
            return JDate::date($ticket->created_at);
        }

        return JDate::dateTime($ticket->last_reply_at);
    }

    /** Author display name (store name for sellers, name for customers). */
    public function authorName(SupportTicket $ticket): string
    {
        $author = $ticket->ticketable;

        if ($author === null) {
            return '—';
        }

        if ($author instanceof Seller) {
            return (string) ($author->shop?->name ?: $author->full_name ?: '—');
        }

        return (string) ($author->full_name ?? '—');
    }

    public function render()
    {
        return view('livewire.admin.support.tickets-index', [
            'tickets' => $this->tickets,
            'counts' => $this->counts,
        ]);
    }
}
