<?php

namespace App\Livewire\Admin\Settings;

use App\Livewire\Admin\Concerns\GuardsPermissions;
use App\Models\SmsLog;
use App\Models\SmsTemplate;
use App\Support\Phone;
use App\Support\Sms\SmsManager;
use App\Support\Sms\SmsTemplates;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use App\Livewire\Concerns\NotifiesOnValidation;

/**
 * Admin — «مرکز پیامک» / قالب‌های پیامک.
 *
 * Grid of every outgoing-message template exactly like the reference
 * design: per-card enable toggle, variable chips, edit + test buttons;
 * edit dialog (عنوان / کد پترن / متن / متغیرها با درج در متن / پیش‌نمایش
 * زنده با داده نمونه) and a test dialog (پیش‌نمایش + موبایل آزمایشی +
 * ارسال واقعی اختیاری). Provider settings live in the adjacent section.
 */
#[Layout('layouts.admin-slot')]
#[Title('قالب‌های پیامک')]
class SmsTemplatesCenter extends Component
{
    use NotifiesOnValidation;
    use GuardsPermissions;

    protected static string $permission = 'admin.settings.sms';

    /* ── filters ── */
    public string $group = 'all';

    public string $search = '';

    /* ── edit modal ── */
    public bool $editOpen = false;

    public ?int $editId = null;

    public string $editKey = '';

    public string $editGroup = '';

    public string $editTitle = '';

    public string $editBody = '';

    public string $editPattern = '';

    public bool $editEnabled = true;

    /* ── test modal ── */
    public bool $testOpen = false;

    public string $testKey = '';

    public string $testPhone = '';

    public bool $testReal = false;

    public function mount(): void
    {
        $this->testPhone = '';
    }

    /* ───────────────────────── data ───────────────────────── */

    public function templates(): Collection
    {
        $order = array_keys(SmsTemplates::GROUPS);

        return SmsTemplate::query()->get()
            ->sort(function (SmsTemplate $a, SmsTemplate $b) use ($order) {
                $ga = array_search($a->group, $order, true) ?: 99;
                $gb = array_search($b->group, $order, true) ?: 99;

                return [$ga, $a->key] <=> [$gb, $b->key];
            })
            ->values();
    }

    public function counts(): array
    {
        $counts = ['all' => 0];

        foreach (SmsTemplate::query()->select('group')->selectRaw('COUNT(*) as n')->groupBy('group')->get() as $row) {
            $counts[$row->group] = (int) $row->n;
            $counts['all'] += (int) $row->n;
        }

        return $counts;
    }

    /** Templates after group+search filtering (for the grid). */
    public function filtered(): Collection
    {
        $q = $this->templates();

        if ($this->group !== 'all') {
            $q = $q->where('group', $this->group);
        }

        if (trim($this->search) !== '') {
            $needle = mb_strtolower(trim($this->search));
            $q = $q->filter(fn (SmsTemplate $t) => str_contains(mb_strtolower($t->title), $needle)
                || str_contains(mb_strtolower($t->key), $needle)
                || str_contains(mb_strtolower($t->body), $needle));
        }

        return $q->values();
    }

    /* ───────────────────────── card toggle ───────────────────────── */

    public function toggleEnabled(int $id): void
    {
        $tpl = SmsTemplate::query()->findOrFail($id);
        $tpl->enabled = ! $tpl->enabled;
        $tpl->save();

        SmsTemplates::flush($tpl->key);
    }

    /* ───────────────────────── edit modal ───────────────────────── */

    public function openEdit(int $id): void
    {
        $tpl = SmsTemplate::query()->findOrFail($id);

        $this->editId      = $tpl->id;
        $this->editKey     = $tpl->key;
        $this->editGroup   = $tpl->group;
        $this->editTitle   = $tpl->title;
        $this->editBody    = $tpl->body;
        $this->editPattern = (string) $tpl->pattern_code;
        $this->editEnabled = $tpl->enabled;

        $this->resetErrorBag();
        $this->editOpen = true;
    }

    public function closeEdit(): void
    {
        $this->editOpen = false;
        $this->reset(['editId', 'editKey', 'editGroup', 'editTitle', 'editBody', 'editPattern', 'editEnabled']);
        $this->resetErrorBag();
    }

    /** Live preview of the edited body (with sample data). */
    public function editPreview(): string
    {
        $sample = SmsTemplate::query()->find($this->editId)?->sample_data ?? [];

        $pairs = [];
        foreach ($this->editVariables() as $var) {
            $pairs['{'.$var.'}'] = (string) ($sample[$var] ?? '{'.$var.'}');
        }

        return strtr($this->editBody, $pairs);
    }

    public function editVariables(): array
    {
        return SmsTemplate::query()->find($this->editId)?->variables ?? [];
    }

    public function saveTemplate(): void
    {
        $this->validateWithToast(fn () => $this->validate([
            'editTitle'   => ['required', 'min:2', 'max:120'],
            'editBody'    => ['required', 'min:2', 'max:1000'],
            'editPattern' => ['nullable', 'string', 'max:120', 'regex:/^[A-Za-z0-9_\-\.]+$/'],
        ], [
            'editTitle.required'   => 'عنوان رویداد را وارد کنید.',
            'editBody.required'    => 'متن پیامک را وارد کنید.',
            'editPattern.regex'    => 'کد پترن فقط می‌تواند شامل حروف لاتین، عدد، خط تیره و زیرخط باشد.',
        ]));

        $tpl = SmsTemplate::query()->findOrFail($this->editId);

        $tpl->title        = $this->editTitle;
        $tpl->body         = $this->editBody;
        $tpl->pattern_code = trim($this->editPattern) !== '' ? trim($this->editPattern) : null;
        $tpl->enabled      = $this->editEnabled;
        $tpl->save();

        SmsTemplates::flush($tpl->key);

        $this->editOpen = false;
        $this->reset(['editId', 'editKey', 'editGroup', 'editTitle', 'editBody', 'editPattern', 'editEnabled']);

        // Close the dialog on the client too (Alpine state lives there).
        $this->dispatch('smstpl-close-edit');

        session()->flash('admin-smstpl-flash', 'قالب «'.$tpl->title.'» ذخیره شد.');
    }

    /* ───────────────────────── test modal ───────────────────────── */

    /** Open the test dialog directly from a card or the edit dialog. */
    public function openTest(string $key, bool $fromEdit = false): void
    {
        $this->testKey   = $key;
        $this->testReal  = false;
        $this->testPhone = '';

        if ($fromEdit) {
            $this->editOpen = false;
        }

        $this->resetErrorBag();
        $this->testOpen = true;
    }

    public function closeTest(): void
    {
        $this->testOpen = false;
        $this->reset(['testKey', 'testPhone', 'testReal']);
        $this->resetErrorBag();
    }

    public function sendTest(): void
    {
        if (trim($this->testKey) === '') {
            $this->addError('testPhone', 'قالبی برای تست انتخاب نشده است — دوباره روی دکمه «تست» قالب بزنید.');

            return;
        }

        $this->validateWithToast(fn () => $this->validate([
            'testPhone' => ['required'],
        ], [
            'testPhone.required' => 'شماره موبایل آزمایشی را وارد کنید.',
        ]));

        $normalized = Phone::normalize($this->testPhone);

        if (! $normalized || ! Phone::isValid($normalized)) {
            $this->addError('testPhone', 'شماره موبایل معتبر نیست. نمونه: 09123456789');

            return;
        }

        // «ارسال واقعی» unchecked → log driver only (پشت‌نمایش: written to
        // sms_logs + laravel log, never leaves the server). Sample data is
        // used so the sent text matches the live preview.
        $result = SmsTemplates::send(
            $this->testKey,
            $normalized,
            SmsTemplates::sampleData(SmsTemplates::find($this->testKey)),
            'admin',
            $this->testReal ? null : 'log',
        );

        $title = SmsTemplate::query()->where('key', $this->testKey)->value('title') ?? $this->testKey;

        $this->testOpen = false;
        $this->reset(['testKey', 'testPhone', 'testReal']);

        // Close the dialog on the client too (Alpine state lives there).
        $this->dispatch('smstpl-close-test');

        if ($result->ok) {
            session()->flash('admin-smstpl-flash', $this->testReal
                ? 'پیام تست از طریق پرووایدر فعال برای «'.$title.'» ارسال شد.'
                : 'پیام تست «'.$title.'» فقط پشت‌نمایش شد (در لاگ پیامک‌ها ثبت شد و پیامک واقعی ارسال نشد).');
        } else {
            session()->flash('admin-smstpl-error', 'ارسال تست ناموفق بود: '.($result->error ?: 'خطای ناشناخته'));
        }
    }

    /* ───────────────────────── view data ───────────────────────── */

    /** Persian title of the template being tested (test dialog header). */
    public function testTitle(): string
    {
        if ($this->testKey === '') {
            return '';
        }

        return SmsTemplate::query()->where('key', $this->testKey)->value('title') ?? $this->testKey;
    }

    public function recentLogs(): Collection
    {
        return SmsLog::query()->latest('id')->limit(8)->get();
    }

    public function render()
    {
        return view('livewire.admin.settings.sms-templates-center', [
            'templates' => $this->filtered(),
            'counts'    => $this->counts(),
            'logs'      => $this->recentLogs(),
        ])->title('قالب‌های پیامک')
            ->layout('layouts.admin-slot', ['breadcrumb' => 'مدیریت کل — پیامک — قالب‌های پیامک']);
    }
}
