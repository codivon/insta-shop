<?php

namespace InstaShop\Installer\Http\Controllers;

use App\Models\Admin;
use App\Support\Color;
use App\Support\Theme;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Throwable;

/**
 * First-run admin registration — shown ONLY after a successful install
 * while the admins table is still empty (guarded end-to-end):
 *
 *   - HandleInstallerAccess lets /install/admin-setup through ONLY when the
 *     app is installed AND no admin exists yet (see the middleware).
 *   - show()/store() re-check Admin::count() as a second line of defence.
 *
 * The page also captures the store owner's theme choice (default: the
 * Instagram gradient) plus a few OPTIONAL store settings — every one of
 * them can be changed later from Admin → Appearance/Settings.
 */
class AdminSetupController extends Controller
{
    /** Does at least one admin account exist? */
    public static function adminExists(): bool
    {
        try {
            return Admin::count() > 0;
        } catch (Throwable) {
            // DB unreachable → fail CLOSED: never expose the setup page.
            return true;
        }
    }

    /* ══════════════════════ Setup page ══════════════════════ */

    public function show()
    {
        if (self::adminExists()) {
            return redirect()->to('/');
        }

        $css = realpath(__DIR__.'/../../../resources/assets/installer.css') ?: '';
        $js = realpath(__DIR__.'/../../../resources/assets/installer.js') ?: '';

        return view('installer::admin-setup', [
            'swatches' => $this->swatches(),
            'defaultPreset' => Theme::DEFAULTS['theme.preset'],
            'assetVersion' => substr(md5(md5_file($css).md5_file($js)), 0, 10),
        ]);
    }

    /**
     * Visual preset swatches for the picker — CSS gradient strings
     * (installer pages do not load Tailwind, so PRESETS['gradient']
     * Tailwind classes are converted to real CSS here).
     *
     * @return array<string, array{label:string, hint:string, css:string}>
     */
    protected function swatches(): array
    {
        $out = [];

        foreach (Theme::PRESETS as $slug => $p) {
            $stops = Theme::GRADIENTS[$slug] ?? null;

            if (! $stops) {
                $shades = Color::shades($p['hex']);
                $stops = [$shades[500], $shades[700]];
            }

            $out[$slug] = [
                'label' => $p['label'],
                'hint' => $p['hint'],
                'css' => 'linear-gradient(135deg, '.implode(', ', $stops).')',
            ];
        }

        return $out;
    }

    /* ══════════════════════ Create the admin ══════════════════════ */

    public function store(Request $request): JsonResponse
    {
        if (self::adminExists()) {
            return response()->json([
                'ok' => false,
                'message' => 'حساب مدیر از قبل ساخته شده است؛ این صفحه فقط برای اولین راه‌اندازی است.',
            ], 403);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'email' => ['required', 'email:rfc', 'max:190', 'unique:admins,email'],
            'password' => ['required', 'string', 'min:8', 'max:64'],
            'password_confirmation' => ['required', 'string', 'same:password'],

            // optional — theme + store defaults
            'theme_preset' => ['nullable', 'string', 'in:'.implode(',', array_keys(Theme::PRESETS))],
            'store_name' => ['nullable', 'string', 'max:60'],
            'store_phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s]{4,20}$/'],
            'store_instagram' => ['nullable', 'string', 'max:60', 'regex:/^[A-Za-z0-9._]{1,60}$/'],
        ], [
            'required' => 'فیلد :attribute الزامی است.',
            'email' => 'ایمیل معتبر وارد کنید.',
            'unique' => 'این ایمیل قبلاً ثبت شده است.',
            'min' => ':attribute حداقل باید :min کاراکتر باشد.',
            'max' => ':attribute بیش از حد طولانی است.',
            'same' => 'تکرار رمز عبور با رمز عبور یکسان نیست.',
            'regex' => 'مقدار :attribute معتبر نیست.',
        ], [
            'name' => 'نام و نام خانوادگی',
            'email' => 'ایمیل',
            'password' => 'رمز عبور',
            'password_confirmation' => 'تکرار رمز عبور',
            'theme_preset' => 'پوسته',
            'store_name' => 'نام فروشگاه',
            'store_phone' => 'شماره تماس',
            'store_instagram' => 'آیدی اینستاگرام',
        ]);

        $admin = Admin::create([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'password' => $data['password'], // hashed automatically (model cast)
            'is_super' => true,
        ]);

        /* ---- theme + optional store defaults (all changeable later) ---- */
        $preset = $data['theme_preset'] ?? Theme::DEFAULTS['theme.preset'];

        $settings = [
            'theme.preset' => $preset,
            'theme.primary' => Theme::PRESETS[$preset]['hex'],
        ];

        if (($data['store_name'] ?? '') !== '') {
            $settings['theme.app_name'] = trim($data['store_name']);
        }

        if (($data['store_phone'] ?? '') !== '') {
            $settings['store.phone'] = trim($data['store_phone']);
        }

        if (($data['store_instagram'] ?? '') !== '') {
            $settings['store.instagram'] = ltrim(trim($data['store_instagram']), '@');
        }

        Setting::setMany($settings);

        /* ---- auto-login so the owner lands inside the panel ---- */
        try {
            Auth::guard('admin')->login($admin);
            $request->session()->regenerate();
        } catch (Throwable) {
            // never block the setup itself on session quirks
        }

        return response()->json([
            'ok' => true,
            'message' => 'حساب مدیر ساخته شد. در حال انتقال به پنل مدیریت…',
            'redirect' => '/admin',
        ]);
    }
}
