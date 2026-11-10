<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Livewire\Concerns\NotifiesOnValidation;
use App\Models\Plan;
use App\Models\SellerSubscription;
use App\Support\ActivityLogger;
use App\Support\Fmt;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Admin — «پلن‌های اشتراک» (v0.22.0): CRUD over the sellable plans.
 * Each plan carries duration_days + price (Toman) and OPTIONALLY its own
 * commission rate (null = «طبق نرخ سراسری»). Deactivation is the safe
 * retirement path — hard delete is refused once subscriptions exist so
 * the sellers' history always keeps its plan title.
 */
#[Layout('layouts.admin-slot')]
class PlansPage extends Component
{
    use GuardsPermissions;
    use NotifiesOnValidation;

    protected static string $permission = 'admin.plans';

    /** null = create mode, otherwise the plan being edited. */
    public ?int $editingId = null;

    public bool $formOpen = false;

    public string $title = '';

    public string $tagline = '';

    public string $features = ''; // one feature per line

    public string $durationDays = '30';

    public string $price = '0';

    public string $commissionType = ''; // '' | percent | fixed

    public string $commissionValue = '0';

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
            $plan = Plan::query()->findOrFail($planId);
            $this->editingId = $plan->id;
            $this->title = (string) $plan->title;
            $this->tagline = (string) $plan->tagline;
            $this->features = implode("\n", (array) $plan->features);
            $this->durationDays = (string) $plan->duration_days;
            $this->price = (string) $plan->price;
            $this->commissionType = $plan->hasCommissionOverride() ? (string) $plan->commission_type : '';
            $this->commissionValue = (string) ($plan->commission_value ?? 0);
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

    public function updatedCommissionType(): void
    {
        $this->commissionValue = '0';
    }

    public function toggleActive(int $planId): void
    {
        $plan = Plan::query()->findOrFail($planId);
        $plan->forceFill(['is_active' => ! $plan->is_active])->save();

        ActivityLogger::log('plan.updated', 'پلن اشتراک «'.$plan->title.'» '.($plan->is_active ? 'فعال' : 'غیرفعال').' شد', subject: $plan);

        $this->dispatch('panel-toast', message: $plan->is_active ? 'پلن فعال شد. ✅' : 'پلن غیرفعال شد — دیگر در لیست خرید فروشندگان دیده نمی‌شود. ⛔', type: 'success');
    }

    public function delete(int $planId): void
    {
        $plan = Plan::query()->findOrFail($planId);

        $used = SellerSubscription::query()->where('plan_id', $planId)->exists();

        if ($used) {
            $this->addError('form', 'برای این پلن اشتراک ثبت شده است و حذفش تاریخچهٔ فروشندگان را می‌شکند — آن را «غیرفعال» کنید تا فقط از فروش خارج شود.');

            return;
        }

        ActivityLogger::log('plan.deleted', 'پلن اشتراک «'.$plan->title.'» حذف شد', before: $plan->only(['title', 'duration_days', 'price']), subject: $plan);
        $plan->delete();

        $this->dispatch('panel-toast', message: 'پلن حذف شد. 🗑', type: 'success');
    }

    public function save(): void
    {
        $this->validateWithToast(fn () => $this->validate($this->rules(), $this->messages()));

        $features = collect(preg_split('/\r\n|\r|\n/', $this->features) ?: [])
            ->map(fn ($line) => trim($line))
            ->filter(fn ($line) => $line !== '')
            ->take(8)
            ->values()
            ->all();

        $payload = [
            'title' => trim($this->title),
            'tagline' => trim($this->tagline) !== '' ? trim($this->tagline) : null,
            'features' => $features !== [] ? $features : null,
            'duration_days' => (int) $this->durationDays,
            'price' => (int) $this->price,
            'commission_type' => $this->commissionType !== '' ? $this->commissionType : null,
            'commission_value' => $this->commissionType !== '' ? (int) $this->commissionValue : null,
            'is_active' => $this->isActive,
            'sort' => (int) $this->sort,
        ];

        if ($this->editingId !== null) {
            $plan = Plan::query()->findOrFail($this->editingId);
            $before = $plan->only(['title', 'tagline', 'duration_days', 'price', 'commission_type', 'commission_value', 'is_active', 'sort']);
            $plan->update($payload);
            ActivityLogger::log('plan.updated', 'پلن اشتراک «'.$plan->title.'» ویرایش شد', subject: $plan, before: $before, after: $payload);
            $this->dispatch('panel-toast', message: 'پلن ذخیره شد. ✅', type: 'success');
        } else {
            $plan = Plan::query()->create($payload);
            ActivityLogger::log('plan.created', 'پلن اشتراک «'.$plan->title.'» ساخته شد ('.$plan->durationLabel().' — '.Fmt::money($plan->price).')', subject: $plan, after: $payload);
            $this->dispatch('panel-toast', message: 'پلن ساخته شد و در لیست فروشندگان ظاهر شد. ✅', type: 'success');
        }

        $this->formOpen = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->tagline = '';
        $this->features = '';
        $this->durationDays = '30';
        $this->price = '0';
        $this->commissionType = '';
        $this->commissionValue = '0';
        $this->isActive = true;
        $this->sort = '0';
        $this->resetErrorBag();
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:80'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'features' => ['nullable', 'string', 'max:1000'],
            'durationDays' => ['required', 'integer', 'min:1', 'max:3650'],
            'price' => ['required', 'integer', 'min:0', 'max:999999999999'],
            'commissionType' => ['nullable', 'in:percent,fixed'],
            'commissionValue' => [
                'required',
                'integer',
                'min:0',
                $this->commissionType === 'percent' ? 'max:100' : 'max:999999999999',
            ],
            'isActive' => ['boolean'],
            'sort' => ['required', 'integer', 'min:0', 'max:9999'],
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'عنوان پلن را وارد کنید.',
            'title.max' => 'عنوان پلن حداکثر ۸۰ نویسه است.',
            'durationDays.required' => 'مدت اعتبار (روز) را وارد کنید.',
            'durationDays.min' => 'مدت اعتبار باید حداقل ۱ روز باشد.',
            'price.required' => 'قیمت پلن را وارد کنید (۰ = رایگان).',
            'price.integer' => 'قیمت باید عدد باشد.',
            'commissionValue.required' => 'مقدار کمیسیون را وارد کنید.',
            'commissionValue.integer' => 'مقدار کمیسیون باید عدد باشد.',
            'commissionValue.min' => 'کمیسیون نمی‌تواند منفی باشد.',
            'commissionValue.max' => 'درصد کمیسیون حداکثر ۱۰۰ است.',
        ];
    }

    public function render()
    {
        return view('livewire.admin.plans-page', [
            'plans' => Plan::query()->orderBy('sort')->orderBy('id')->get(),
            'subscriptionCounts' => SellerSubscription::query()
                ->selectRaw('plan_id, count(*) as total')
                ->groupBy('plan_id')
                ->pluck('total', 'plan_id'),
        ])->title('پلن‌های اشتراک')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — پلن‌های اشتراک']);
    }
}
