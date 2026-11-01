/* ═══════════════════════════════════════════════════════════════
   InstaShop Installer — vanilla JS, no dependencies.

   Two pages share this file (guarded by body[data-page]):
     • wizard       — the install pipeline
     • admin-setup  — first-run admin registration

   Pipeline: migrations[] → seeders[] → finish, ONE item per AJAX
   request. Any number of items therefore cannot hit PHP timeouts,
   and a failed item is retried in place (the runner resumes from
   the exact same index). Progress bar + Persian percentage update
   after every item.

   Every actionable button gets a spinner while it works (.loading).
   ═══════════════════════════════════════════════════════════════ */
(function () {
    'use strict';

    var $ = function (s) { return document.querySelector(s); };
    var $$ = function (s) { return Array.prototype.slice.call(document.querySelectorAll(s)); };

    var CSRF = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
    var NM = Number(document.body.dataset.migrations || 0);
    var NS = Number(document.body.dataset.seeders || 0);
    var TOTAL = NM + NS + 1; // + final "finish" step

    var ORDER = ['migrate', 'seed', 'finish'];
    var PHASE_LABELS = {
        migrate: 'اجرای مهاجرت‌های دیتابیس',
        seed: 'اجرای داده‌های اولیه (سیدرها)',
        finish: 'مراحل پایانی',
    };

    /* ── element registry (null-safe: both pages) ────────────── */
    var el = {
        bar: $('#bar'),
        percent: $('#percent'),
        phase: $('#phaseTitle'),
        task: $('#task'),
        log: $('#log'),
        errBox: $('#installError'),
        errMsg: $('#installErrorMsg'),
        errDetail: $('#installErrorDetail'),
        retry: $('#retryBtn'),
        dbForm: $('#dbForm'),
        dbError: $('#dbError'),
        dbSubmit: $('#dbSubmit'),
        sumUrl: $('#sumUrl'),
        sumDb: $('#sumDb'),
        sumStorage: $('#sumStorage'),
        adminCta: $('#adminCta'),
        enterApp: $('#enterApp'),
        enterAppDefault: $('#enterAppDefault'),
        dbSubmitLabel: $('#dbSubmitLabel'),
    };

    // resumable cursor — where the pipeline currently stands
    var cursor = { kind: 'migrate', index: 0, done: 0 };
    var running = false;

    /* ── helpers ─────────────────────────────────────────────── */

    function fa(n) { return Number(n).toLocaleString('fa-IR'); }

    var CHECK_SVG = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>';

    /* loader management for EVERY actionable control */
    function spin(btn) {
        if (! btn || btn.disabled) return false;
        btn.classList.add('loading');
        btn.setAttribute('aria-busy', 'true');
        if (btn.tagName === 'BUTTON') btn.disabled = true;
        return true;
    }

    function stopSpin(btn) {
        if (! btn) return;
        btn.classList.remove('loading');
        btn.removeAttribute('aria-busy');
        if (btn.tagName === 'BUTTON' && btn !== el.dbSubmit && btn !== el.setupSubmit) btn.disabled = false;
    }

    function goStep(n) {
        $$('.pane').forEach(function (p) {
            p.hidden = p.dataset.pane !== String(n);
        });
        $$('.step').forEach(function (s) {
            var k = Number(s.dataset.stepDot);
            var dot = s.querySelector('.dot');
            s.classList.toggle('active', k === n);
            s.classList.toggle('done', k < n);
            dot.innerHTML = k < n ? CHECK_SVG : fa(k);
        });
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function setProgress(done) {
        var pct = TOTAL ? Math.round((done / TOTAL) * 100) : 100;
        if (el.bar) el.bar.style.width = pct + '%';
        if (el.percent) el.percent.textContent = fa(pct) + '٪';
    }

    function logLine(text, cls) {
        if (! el.log) return;
        var d = document.createElement('div');
        d.className = cls || 'ok';
        d.textContent = text;
        el.log.appendChild(d);
        el.log.scrollTop = el.log.scrollHeight;
    }

    function hideError() {
        if (el.errBox) el.errBox.hidden = true;
        if (el.errDetail) el.errDetail.textContent = '';
    }

    function showError(message, detail) {
        if (el.errMsg) el.errMsg.textContent = message || 'خطای ناشناخته رخ داد.';
        if (el.errDetail) el.errDetail.textContent = detail || '';
        if (el.errBox) el.errBox.hidden = false;
    }

    function post(url, payload) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload || {}),
        }).then(function (res) {
            return res.json().catch(function () { return null; }).then(function (data) {
                if (!res.ok || !data || data.ok === false) {
                    var err = new Error((data && data.message) || ('خطای سرور (' + res.status + ')'));
                    err.detail = data && data.detail;
                    err.errors = data && data.errors;
                    throw err;
                }
                return data;
            });
        });
    }

    function callStep(kind, index) {
        if (kind === 'migrate') return post('/install/migrate', { index: index });
        if (kind === 'seed') return post('/install/seed', { index: index });
        return post('/install/finish', {});
    }

    function itemCount(kind) {
        return kind === 'migrate' ? NM : kind === 'seed' ? NS : 1;
    }

    /* ── the pipeline ────────────────────────────────────────── */

    function runInstall(from) {
        if (running) return;
        running = true;
        hideError();
        if (el.retry) spin(el.retry);

        var kind = (from && from.kind) || 'migrate';
        var index = (from && from.index) || 0;
        var done = (from && from.done) || 0;
        var fin = null;

        var consume = function (r) {
            if (kind === 'finish') fin = r;
            done += 1;
            index += 1;
            setProgress(done);
            logLine('✔ ' + (r.message || 'انجام شد'));
            var detail = (r.detail || '').split('\n').filter(Boolean);
            detail.slice(-2).forEach(function (l) { logLine(l.trim(), 'sub'); });
        };

        var step = function () {
            cursor = { kind: kind, index: index, done: done };
            if (el.phase) el.phase.textContent = PHASE_LABELS[kind];
            if (el.task) el.task.textContent = 'مورد ' + fa(done + 1) + ' از ' + fa(TOTAL) + ' …';
            return callStep(kind, index).then(consume);
        };

        var chain = function () {
            if (index < itemCount(kind)) {
                return step().then(chain);
            }
            var nextIdx = ORDER.indexOf(kind) + 1;
            if (nextIdx >= ORDER.length) return Promise.resolve();
            kind = ORDER[nextIdx];
            index = 0;
            return chain();
        };

        chain()
            .then(function () {
                setProgress(TOTAL);
                if (el.phase) el.phase.textContent = 'نصب کامل شد';
                if (el.task) el.task.textContent = 'همه موارد با موفقیت انجام شد.';
                logLine('✔ InstaShop آماده استفاده است.');
                if (el.sumUrl) el.sumUrl.textContent = (fin && fin.app_url) || location.origin;
                if (el.sumDb) el.sumDb.textContent = (fin && fin.db_name) || '—';
                if (el.sumStorage) el.sumStorage.textContent =
                    (fin && fin.storage_linked) ? 'ایجاد شد' : 'سیم‌لینک روی هاست غیرفعال است — فایل‌ها از مسیر جایگزین سرو می‌شوند (بدون مشکل)';

                // no admin yet → hand the owner to first-run registration
                if (fin && fin.needs_admin && el.adminCta && el.enterApp && el.enterAppDefault) {
                    el.adminCta.hidden = false;
                    el.enterApp.hidden = false;
                    el.enterAppDefault.hidden = true;
                } else if (el.adminCta && el.enterApp && el.enterAppDefault) {
                    el.adminCta.hidden = true;
                    el.enterApp.hidden = true;
                    el.enterAppDefault.hidden = false;
                }

                goStep(5);
            })
            .catch(function (e) {
                showError(e.message, e.detail);
                logLine('✖ ' + (e.detail || e.message || 'خطا'), 'err');
                logLine('می‌توانید از دکمه «تلاش مجدد» ادامه دهید.', 'sub');
            })
            .then(function () {
                running = false;
                if (el.retry) stopSpin(el.retry);
            });
    }

    /* ── wiring · wizard page ────────────────────────────────── */

    // step-nav buttons — brief spinner for feedback, then switch
    $$('[data-goto]').forEach(function (b) {
        b.addEventListener('click', function () {
            if (! spin(b)) return;
            setTimeout(function () {
                goStep(Number(b.dataset.goto));
                stopSpin(b);
            }, 250);
        });
    });

    if (el.retry) {
        el.retry.addEventListener('click', function () {
            runInstall({ kind: cursor.kind, index: cursor.index, done: cursor.done });
        });
    }

    if (el.dbForm) {
        el.dbForm.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var btn = el.dbSubmit;
            var label = el.dbSubmitLabel || btn;
            var original = label.textContent;

            el.dbError.hidden = true;
            btn.disabled = true;
            btn.classList.add('loading');
            label.textContent = 'در حال بررسی اتصال…';

            var payload = {};
            new FormData(el.dbForm).forEach(function (v, k) { payload[k] = v; });

            post('/install/database', payload)
                .then(function () {
                    // Writing .env reboots the app's runtime config (APP_NAME,
                    // session cookie name, …). Reloading re-establishes a clean
                    // session and the boot resume path (data-resume="1")
                    // automatically opens step 4 and starts the pipeline.
                    label.textContent = 'در حال آماده‌سازی نصب…';
                    window.location.reload();
                })
                .catch(function (e) {
                    el.dbError.textContent = e.message || 'اتصال به دیتابیس برقرار نشد.';
                    el.dbError.hidden = false;
                    btn.disabled = false;
                    btn.classList.remove('loading');
                    label.textContent = original;
                });
        });
    }

    /* ── wiring · admin-setup page ───────────────────────────── */

    var setupForm = $('#setupForm');

    if (setupForm) {
        // theme swatch selection
        $$('#swatchGrid .swatch').forEach(function (s) {
            s.addEventListener('click', function () {
                $$('#swatchGrid .swatch').forEach(function (o) {
                    o.classList.remove('is-selected');
                    o.setAttribute('aria-checked', 'false');
                });
                s.classList.add('is-selected');
                s.setAttribute('aria-checked', 'true');
                $('#themePreset').value = s.dataset.swatch;
            });
        });

        setupForm.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var btn = $('#setupSubmit');
            var box = $('#setupError');

            box.hidden = true;
            box.textContent = '';
            btn.classList.add('loading');
            btn.setAttribute('aria-busy', 'true');
            btn.disabled = true;

            var payload = {};
            new FormData(setupForm).forEach(function (v, k) { payload[k] = v; });

            post('/install/admin-setup', payload)
                .then(function (r) {
                    btn.textContent = (r && r.message) || 'در حال انتقال…';
                    window.location.href = (r && r.redirect) || '/admin';
                })
                .catch(function (e) {
                    var msg = e.message || 'ثبت حساب انجام نشد.';
                    if (e.errors) {
                        var first = null;
                        Object.keys(e.errors).some(function (k) {
                            first = e.errors[k] && e.errors[k][0];
                            return !!first;
                        });
                        if (first) msg = first;
                    }
                    box.textContent = msg;
                    box.hidden = false;
                    btn.classList.remove('loading');
                    btn.removeAttribute('aria-busy');
                    btn.disabled = false;
                });
        });
    }

    /* theme toggle — same behaviour as the app's x-theme-toggle */
    var toggle = $('#themeToggle');
    if (toggle) {
        toggle.addEventListener('click', function () {
            var dark = document.documentElement.classList.toggle('dark');
            try { localStorage.setItem('ip-mode', dark ? 'dark' : 'light'); } catch (e) {}
        });
    }

    /* ── boot ────────────────────────────────────────────────── */
    if (document.body.dataset.page === 'admin-setup') {
        return; // nothing else to boot on the setup page
    }

    goStep(1);
    // .env already exists (previous run crashed?) → resume the install phase
    if (document.body.dataset.resume === '1') {
        goStep(4);
        runInstall(null);
    }
})();
