//
// InstaShop — Jalali (Solar Hijri) date engine + Alpine datepicker
//
// Zero-dependency Persian datepicker used across admin / seller / customer
// panels. The hidden input carries the machine value (Gregorian Y-m-d) so
// Livewire validation & DB stay unchanged; the visible input shows Jalali.
//

/* ── Jalali ⇄ Gregorian conversion (jalaali algorithm) ─────────────── */
function div(a, b) { return ~~(a / b); }
function mod(a, b) { return a - ~~(a / b) * b; }

function jalCal(jy) {
    const breaks = [-61, 9, 38, 199, 426, 686, 756, 818, 1111, 1181, 1210, 1635, 2060, 2097, 2192, 2262, 2324, 2394, 2456, 3178];
    let bl = breaks.length, gy = jy + 621, leapJ = -14, jp = breaks[0], jm, jump = 0, leap, n, i;

    for (i = 1; i < bl; i += 1) {
        jm = breaks[i];
        jump = jm - jp;
        if (jy < jm) break;
        leapJ = leapJ + div(jump, 33) * 8 + div(mod(jump, 33), 4);
        jp = jm;
    }
    n = jy - jp;

    leapJ = leapJ + div(n, 33) * 8 + div(mod(n, 33) + 3, 4);
    if (mod(jump, 33) === 4 && jump - n === 4) leapJ += 1;

    const leapG = div(gy, 4) - div((div(gy, 100) + 1) * 3, 4) - 150;
    const march = 20 + leapJ - leapG;

    if (jump - n < 6) n = n - jump + div(jump + 4, 33) * 33;
    leap = mod(mod(n + 1, 33) - 1, 4);
    if (leap === -1) leap = 4;

    return { leap: leap, gy: gy, march: march };
}

function j2d(jy, jm, jd) {
    const r = jalCal(jy);
    return g2d(r.gy, 3, r.march) + (jm - 1) * 31 - div(jm, 7) * (jm - 7) + jd - 1;
}

function d2j(jdn) {
    let gy = d2g(jdn).gy, jy = gy - 621, jd, jm, k;
    const r = jalCal(jy);
    let jdn1f = g2d(gy, 3, r.march);

    k = jdn - jdn1f;
    if (k >= 0) {
        if (k <= 185) {
            jm = 1 + div(k, 31);
            jd = mod(k, 31) + 1;
            return { jy: jy, jm: jm, jd: jd };
        }
        k -= 186;
    } else {
        jy -= 1;
        k += 179;
        if (r.leap === 1) k += 1;
    }
    jm = 7 + div(k, 30);
    jd = mod(k, 30) + 1;
    return { jy: jy, jm: jm, jd: jd };
}

function g2d(gy, gm, gd) {
    let d = div((gy + div(gm - 8, 6) + 100100) * 1461, 4)
        + div(153 * mod(gm + 9, 12) + 2, 5)
        + gd - 34840408;
    d = d - div(div(gy + 100100 + div(gm - 8, 6), 100) * 3, 4) + 752;
    return d;
}

function d2g(jdn) {
    let j = 4 * jdn + 139361631;
    j = j + div(div(4 * jdn + 183187720, 146097) * 3, 4) * 4 - 3908;
    const i = div(mod(j, 1461), 4) * 5 + 308;
    const gd = div(mod(i, 153), 5) + 1;
    const gm = mod(div(i, 153), 12) + 1;
    const gy = div(j, 1461) - 100100 + div(8 - gm, 6);
    return { gy: gy, gm: gm, gd: gd };
}

/* Persian digits */
const FA = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
export function faNum(v) { return String(v).replace(/\d/g, (d) => FA[Number(d)]); }

export const J_MONTHS = ['فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور', 'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'];
const J_MONTH_DAYS = [31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29];

export function isJLeap(jy) { return jalCal(jy).leap === 0; }
export function jMonthLen(jy, jm) { return jm <= 6 ? 31 : (jm <= 11 ? 30 : (isJLeap(jy) ? 30 : 29)); }

/** ISO 'Y-m-d' → {jy, jm, jd} */
export function isoToJalali(iso) {
    if (!iso || !/^\d{4}-\d{2}-\d{2}$/.test(iso)) return null;
    const [y, m, d] = iso.split('-').map(Number);
    return d2j(g2d(y, m, d));
}

/** {jy, jm, jd} → ISO 'Y-m-d' */
export function jalaliToIso(jy, jm, jd) {
    const g = d2g(j2d(jy, jm, jd));
    const pad = (n) => String(n).padStart(2, '0');
    return `${g.gy}-${pad(g.gm)}-${pad(g.gd)}`;
}

/** Pretty Jalali: ۱۴۰۵/۰۸/۱۱ */
export function isoToFa(iso) {
    const j = isoToJalali(iso);
    if (!j) return '';
    const pad = (n) => String(n).padStart(2, '0');
    return faNum(`${j.jy}/${pad(j.jm)}/${pad(j.jd)}`);
}

/* ── Alpine component ─────────────────────────────────────────────────
   Usage (Blade component x-jdate-picker wraps this):
   x-data="jdatePicker({ model: 'wireProp', iso: '2026-01-01' })"
   The Blade component wires $wire updates automatically. */
export function jdatePicker(config) {
    return {
        open: false,
        iso: config.iso || '',
        viewJy: 0,
        viewJm: 1,
        months: J_MONTHS,
        weekdays: ['ش', 'ی', 'د', 'س', 'چ', 'پ', 'ج'],

        init() {
            const j = isoToJalali(this.iso) || this.todayJalali();
            this.viewJy = j.jy;
            this.viewJm = j.jm;
            this.$watch('open', (v) => {
                if (v) {
                    const s = isoToJalali(this.iso) || this.todayJalali();
                    this.viewJy = s.jy;
                    this.viewJm = s.jm;
                }
            });
        },

        todayJalali() {
            return isoToJalali(this.todayIso());
        },

        todayIso() {
            const d = new Date();
            const pad = (n) => String(n).padStart(2, '0');
            return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
        },

        get selected() {
            return isoToJalali(this.iso);
        },

        get monthName() {
            return J_MONTHS[this.viewJm - 1] + ' ' + faNum(this.viewJy);
        },

        get days() {
            const len = jMonthLen(this.viewJy, this.viewJm);
            const firstIso = jalaliToIso(this.viewJy, this.viewJm, 1);
            // Saturday = 0 ... Friday = 6 (Jalali week starts Saturday)
            const firstDow = (new Date(firstIso + 'T00:00:00').getDay() + 1) % 7;
            const cells = [];
            for (let i = 0; i < firstDow; i++) cells.push(null);
            for (let d = 1; d <= len; d++) cells.push(d);
            return cells;
        },

        get canGoPrev() { return this.viewJy > 1300; },
        get canGoNext() { return this.viewJy < 1500; },

        move(delta) {
            let m = this.viewJm + delta, y = this.viewJy;
            if (m < 1) { m = 12; y -= 1; }
            if (m > 12) { m = 1; y += 1; }
            this.viewJm = m;
            this.viewJy = y;
        },

        pick(day) {
            if (!day) return;
            this.iso = jalaliToIso(this.viewJy, this.viewJm, day);
            this.$dispatch('jdate-changed', { iso: this.iso, name: config.name || '' });
            if (config.onPick) config.onPick(this.iso);
            this.open = false;
        },

        pickToday() {
            const t = this.todayJalali();
            this.viewJy = t.jy;
            this.viewJm = t.jm;
            this.pick(t.jd);
        },

        clear() {
            this.iso = '';
            this.$dispatch('jdate-changed', { iso: '', name: config.name || '' });
            if (config.onClear) config.onClear();
            this.open = false;
        },

        faNum,
        isoToFa,
    };
}

// Register for Alpine (app.js imports Alpine bundle via Livewire — the
// component is attached on window so x-data can reference it).
window.jdatePicker = jdatePicker;
window.jdateUtils = { isoToJalali, jalaliToIso, isoToFa, faNum, jMonthLen, isJLeap };
