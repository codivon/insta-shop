<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\BumpPlan;
use App\Models\ProductBump;
use App\Models\Setting;
use App\Support\ActivityLogger;
use App\Support\Fmt;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Admin — «طرح‌های نردبان» (v0.24.0, the client's own words: «یک صفحه جدا
 * طراحی کن در پنل مدیریت، بعد بتونیم طرح اضافه کنیم، مثلا 10 روز با این
 * قیمت و...»): full CRUD over the sellable bump plans. Each plan = a
 * duration (days) + a price (Toman); the SELLER later picks ONE plan and
 * pays from the wallet (BumpPage → BumpService). The feature's master
 * switch lives separately in «تنظیمات فروشگاه‌ها» (one on/off toggle).
 *
 * Deactivation is the safe retirement path — hard delete is refused once
 * bumps were sold with the plan so the sellers' history keeps its title.
 */
#[Layout('layouts.admin-slot')]
class BumpPlansPage extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;

    protected static string $permission = 'admin.bump.plans';

    /** null = create mode, otherwise the plan being edited. */
    public ?int $editingId = null;

    public bool $formOpen = false;

    public string $title = '';

    public string $days = '10';

    public string $price = '0';

    public bool $isActive = true;

    public string $sort = '0';

    public function toggleForm(?int $planId = null): void
    {
        $this->formOpen = ! $this->formOpen || ($planId !== null && $planId !== $this->editingId);

        if (! $this->formOpen) {
            $this->resetForm();

            return;
        }

        if ($planId !== null) {
            $plan = BumpPlan::query()->findOrFail($planId);
            $this->editingId = $plan->id;
            $this->title = (string) $plan->title;
            $this->days = (string) $plan->days;
            $this->price = (string) $plan->price;
            $this->isActive = $plan->is_active;
            $this->sort = (string) $plan->sort;
        }

        $this->resetErrorBag();
    }

    public function cancelForm(): void
    {
        $this->formOpen = false;
        $this->resetForm();
    }

    /** Open the create modal (distinct action so the modal never fights the edit toggle). */
    public function openCreate(): void
    {
        $this->resetForm();
        $this->editingId = null;
        $this->formOpen = true;
        $this->resetErrorBag();
    }

    public function toggleActive(int $planId): void
    {
        $plan = BumpPlan::query()->findOrFail($planId);
        $plan->forceFill(['is_active' => ! $plan->is_active])->save();

        ActivityLogger::log('bump_plan.updated', 'طرح نردبان «'.$plan->title.'» '.($plan->is_active ? 'فعال' : 'غیرفعال').' شد', subject: $plan);

        $this->dispatch('panel-toast', message: $plan->is_active ? 'طرح فعال شد. ✅' : 'طرح غیرفعال شد — دیگر در لیست خرید فروشندگان دیده نمی‌شود. ⛔', type: 'success');
    }

    public function delete(int $planId): void
    {
        $plan = BumpPlan::query()->findOrFail($planId);

        $used = ProductBump::query()->where('bump_plan_id', $planId)->exists();

        if ($used) {
            $this->addError('form', 'با این طرح نردبان فروخته شده و حذفش تاریخچهٔ فروشندگان را می‌شکند — آن را «غیرفعال» کنید تا فقط از فروش خارج شود.');

            return;
        }

        ActivityLogger::log('bump_plan.deleted', 'طرح نردبان «'.$plan->title.'» حذف شد', before: $plan->only(['title', 'days', 'price']), subject: $plan);
        $plan->delete();

        $this->dispatch('panel-toast', message: 'طرح نردبان حذف شد. 🗑', type: 'success');
    }

    public function save(): void
    {
        $this->validateWithToast(fn () => $this->validate($this->rules(), $this->messages()));

        $payload = [
            'title' => trim($this->title),
            'days' => (int) $this->days,
            'price' => (int) $this->price,
            'is_active' => $this->isActive,
            'sort' => (int) $this->sort,
        ];

        if ($this->editingId !== null) {
            $plan = BumpPlan::query()->findOrFail($this->editingId);
            $before = $plan->only(['title', 'days', 'price', 'is_active', 'sort']);
            $plan->update($payload);
            ActivityLogger::log('bump_plan.updated', 'طرح نردبان «'.$plan->title.'» ویرایش شد', subject: $plan, before: $before, after: $payload);
            $this->dispatch('panel-toast', message: 'طرح نردبان ذخیره شد. ✅', type: 'success');
        } else {
            $plan = BumpPlan::query()->create($payload);
            ActivityLogger::log('bump_plan.created', 'طرح نردبان «'.$plan->title.'» ساخته شد ('.$plan->daysLabel().' — '.Fmt::money($plan->price).')', subject: $plan, after: $payload);
            $this->dispatch('panel-toast', message: 'طرح نردبان ساخته شد و در پنل فروشندگان ظاهر شد. ✅', type: 'success');
        }

        $this->formOpen = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->days = '10';
        $this->price = '0';
        $this->isActive = true;
        $this->sort = '0';
        $this->resetErrorBag();
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:80'],
            'days' => ['required', 'integer', 'min:1', 'max:365'],
            'price' => ['required', 'integer', 'min:0', 'max:999999999999'],
            'isActive' => ['boolean'],
            'sort' => ['required', 'integer', 'min:0', 'max:9999'],
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'عنوان طرح را وارد کنید.',
            'title.max' => 'عنوان طرح حداکثر ۸۰ نویسه است.',
            'days.required' => 'مدت نردبان (روز) را وارد کنید.',
            'days.min' => 'مدت نردبان باید حداقل ۱ روز باشد.',
            'days.max' => 'حداکثر ۳۶۵ روز.',
            'price.required' => 'قیمت طرح را وارد کنید.',
            'price.integer' => 'قیمت باید عدد باشد.',
        ];
    }

    public function render()
    {
        $plans = BumpPlan::query()->orderBy('sort')->orderBy('days')->orderBy('id')->get();

        $soldCounts = ProductBump::query()
            ->selectRaw('bump_plan_id, count(*) as total')
            ->whereNotNull('bump_plan_id')
            ->groupBy('bump_plan_id')
            ->pluck('total', 'bump_plan_id');

        return view('livewire.admin.bump-plans-page', [
            'plans' => $plans,
            'soldCounts' => $soldCounts,
            'activeBumps' => ProductBump::query()->where('ends_at', '>', now())->count(),
            'bumpRevenue' => (int) ProductBump::query()->sum('price_paid'),
            'bumpEnabled' => (bool) Setting::get('shops.bump_enabled', false),
            'formPlan' => $this->editingId !== null && $this->formOpen ? BumpPlan::query()->find($this->editingId) : null,
        ])->title('طرح‌های نردبان')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — طرح‌های نردبان']);
    }
}
