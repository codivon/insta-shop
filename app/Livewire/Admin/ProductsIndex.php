<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;

use App\Models\Product;
use App\Models\Shop;
use App\Support\Fmt;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

/**
 * Admin — products management (list / search / filter / delete).
 */
#[Layout('layouts.admin-slot')]
class ProductsIndex extends Component
{
    use GuardsPermissions;

    protected static string $permission = 'admin.products';
    public string $search = '';

    #[Url]
    public string $status = 'all'; // all|published|draft

    #[Url]
    public int $shopId = 0;

    public int $perPage = 12;

    public bool $hasMore = true;

    private int $lastLoadAt = 0;

    public function mount(): void
    {
        if (! in_array($this->status, ['all', 'published', 'draft'], true)) {
            $this->status = 'all';
        }
    }

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'status', 'shopId'], true)) {
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

    public function deleteProduct(int $productId): void
    {
        $product = Product::query()->with('images')->find($productId);

        if (! $product) {
            return;
        }

        // Remove image files from disk before deleting rows.
        foreach ($product->images as $image) {
            $image->deleteFile();
        }

        $name = $product->name;
        $product->delete();

        session()->flash('admin-catalog-flash', 'محصول «'.$name.'» حذف شد.');
    }

    // ── Computed data ────────────────────────────────────────────────

    public function getStatsProperty(): array
    {
        $byStatus = Product::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'all' => (int) $byStatus->sum(),
            'published' => (int) ($byStatus['published'] ?? 0),
            'draft' => (int) ($byStatus['draft'] ?? 0),
        ];
    }

    /** Shops for the filter dropdown. */
    public function getShopOptionsProperty(): Collection
    {
        return Shop::query()->orderBy('name')->get(['id', 'name']);
    }

    public function getProductsProperty(): Collection
    {
        $term = Fmt::toLatin($this->search);

        $query = Product::query()
            ->when($term !== '', fn ($q) => $q->where(fn ($w) => $w
                ->where('name', 'like', "%{$this->search}%")
                ->orWhereHas('shop', fn ($s) => $s->where('name', 'like', "%{$this->search}%"))))
            ->when($this->status !== 'all', fn ($q) => $q->where('status', $this->status))
            ->when($this->shopId > 0, fn ($q) => $q->where('shop_id', $this->shopId))
            ->with(['shop:id,name', 'mainImage'])
            ->latest('id');

        $total = (clone $query)->count();
        $rows = $query->limit($this->perPage)->get();

        $this->hasMore = $total > $this->perPage;

        return $rows;
    }

    public function render()
    {
        return view('livewire.admin.products-index', [
            'products' => $this->products,
            'stats' => $this->stats,
            'shopOptions' => $this->shopOptions,
            'hasMore' => $this->hasMore,
        ])->title('مدیریت محصولات')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — محصولات']);
    }
}
