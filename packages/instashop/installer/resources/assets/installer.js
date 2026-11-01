/* ═══════════════════════════════════════════════════════════════
   InstaShop Installer wizard — vanilla JS, no dependencies.

   Pipeline: migrations[] → seeders[] → finish, ONE item per AJAX
   request. Any number of items therefore cannot hit PHP timeouts,
   and a failed item is retried in place (the runner resumes from
   the exact same index). Progress bar + Persian percentage update
   after every item.
   ═══════════════════════════════════════════════════════════════ */
(function () {
    'use strict';

    var $ = function (s) { return document.querySelector(s); };

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
    };

    // resumable cursor — where the pipeline currently stands
    var cursor = { kind: 'migrate', index: 0, done: 0 };
    var running = false;

    /* ── helpers ─────────────────────────────────────────────── */

    function fa(n) { return Number(n).toLocaleString('fa-IR'); }

    var CHECK_SVG = '<svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>';

    function goStep(n) {
        document.querySelectorAll('.pane').forEach(function (p) {
            p.hidden = p.dataset.pane !== String(n);
        });
        document.querySelectorAll('.step').forEach(function (s) {
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
        el.bar.style.width = pct + '%';
        el.percent.textContent = fa(pct) + '٪';
    }

    function logLine(text, cls) {
        var d = document.createElement('div');
        d.className = cls || 'ok';
        d.textContent = text;
        el.log.appendChild(d);
        el.log.scrollTop = el.log.scrollHeight;
    }

    function hideError() {
        el.errBox.hidden = true;
        el.errDetail.textContent = '';
    }

    function showError(message, detail) {
        el.errMsg.textContent = message || 'خطای ناشناخته رخ داد.';
        el.errDetail.textContent = detail || '';
        el.errBox.hidden = false;
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
            el.phase.textContent = PHASE_LABELS[kind];
            el.task.textContent = 'مورد ' + fa(done + 1) + ' از ' + fa(TOTAL) + ' …';
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
                el.phase.textContent = 'نصب کامل شد';
                el.task.textContent = 'همه موارد با موفقیت انجام شد.';
                logLine('✔ InstaShop آماده استفاده است.');
                el.sumUrl.textContent = (fin && fin.app_url) || location.origin;
                el.sumDb.textContent = (fin && fin.db_name) || '—';
                el.sumStorage.textContent =
                    (fin && fin.storage_linked) ? 'ایجاد شد' : 'سیم‌لینک روی هاست غیرفعال است — فایل‌ها از مسیر جایگزین سرو می‌شوند (بدون مشکل)';
                goStep(5);
            })
            .catch(function (e) {
                showError(e.message, e.detail);
                logLine('✖ ' + (e.detail || e.message || 'خطا'), 'err');
                logLine('می‌توانید از دکمه «تلاش مجدد» ادامه دهید.', 'sub');
            })
            .then(function () { running = false; });
    }

    /* ── wiring ──────────────────────────────────────────────── */

    document.querySelectorAll('[data-goto]').forEach(function (b) {
        b.addEventListener('click', function () { goStep(Number(b.dataset.goto)); });
    });

    el.retry.addEventListener('click', function () {
        runInstall({ kind: cursor.kind, index: cursor.index, done: cursor.done });
    });

    if (el.dbForm) {
        el.dbForm.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var btn = el.dbSubmit;
            var original = btn.textContent;

            el.dbError.hidden = true;
            btn.disabled = true;
            btn.classList.add('loading');
            btn.textContent = 'در حال بررسی اتصال…';

            var payload = {};
            new FormData(el.dbForm).forEach(function (v, k) { payload[k] = v; });

            post('/install/database', payload)
                .then(function () {
                    // Writing .env reboots the app's runtime config (APP_NAME,
                    // session cookie name, …). Reloading re-establishes a clean
                    // session and the boot resume path (data-resume="1")
                    // automatically opens step 4 and starts the pipeline.
                    btn.textContent = 'در حال آماده‌سازی نصب…';
                    window.location.reload();
                })
                .catch(function (e) {
                    el.dbError.textContent = e.message || 'اتصال به دیتابیس برقرار نشد.';
                    el.dbError.hidden = false;
                })
                .then(function () {
                    btn.disabled = false;
                    btn.classList.remove('loading');
                    btn.textContent = original;
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
    goStep(1);
    // .env already exists (previous run crashed?) → resume the install phase
    if (document.body.dataset.resume === '1') {
        goStep(4);
        runInstall(null);
    }
})();
