{{-- ─────────────────────────────────────────────────────────────────────────
  Stale-tab killer — included by every panel layout.

  Long-lived phone tabs (users keep dozens open for days) keep running an
  OLD app.js + OLD component DOM after a deploy. Any Livewire morph between
  the stale client and the NEW server markup can then repair the page at
  the wrong level and displace whole layout blocks (the «bottom tab bar
  floats mid-page» class of bugs).

  The layout stamps the sha256 of the built manifest into a meta tag; this
  script re-reads the manifest whenever the tab becomes visible again and
  hard-reloads the page ONCE when a deploy changed the build. After the
  reload the meta matches again → no loop.
──────────────────────────────────────────────────────────────────────── --}}
@php
    $manifestPath = public_path('build/manifest.json');
    $appBuildHash = is_file($manifestPath) ? hash_file('sha256', $manifestPath) : 'no-build';
@endphp
<meta name="app-build" content="{{ $appBuildHash }}">
<script>
    (function () {
        // $appBuildHash is a plain hex digest (or 'no-build') — safe to inline
        // through the escaped double-brace echo below as a quoted JS string.
        var expected = "{{ $appBuildHash }}";
        var lastCheck = 0;

        function sha256Hex(text) {
            return crypto.subtle.digest('SHA-256', new TextEncoder().encode(text)).then(function (buf) {
                return Array.prototype.map.call(new Uint8Array(buf), function (b) {
                    return ('0' + b.toString(16)).slice(-2);
                }).join('');
            });
        }

        function check() {
            var now = Date.now();
            if (now - lastCheck < 60000) { return; }
            lastCheck = now;
            if (!window.crypto || !crypto.subtle) { return; }
            fetch('/build/manifest.json', { cache: 'no-store' })
                .then(function (r) { return r.ok ? r.text() : null; })
                .then(function (t) {
                    if (t === null) { return; }
                    return sha256Hex(t).then(function (hex) {
                        if (hex !== expected) {
                            try { sessionStorage.setItem('ip-build-reload', String(Date.now())); } catch (e) {}
                            window.location.reload();
                        }
                    });
                })
                .catch(function () { /* offline / blocked — keep the page */ });
        }

        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') { check(); }
        });
        window.addEventListener('pageshow', function (e) { if (e.persisted) { check(); } });
        window.addEventListener('focus', check);
    })();
</script>
