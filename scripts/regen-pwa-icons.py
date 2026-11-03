#!/usr/bin/env python3
"""
Regenerate the PWA icons from the current full-bleed source icons.

User asks (translated):
  - make the installed PWA app icon SMALLER (less full-bleed, more padding)
  - no background tile — icon blends with the page like a transparent PNG
    (splash screen draws the icon straight on the white page)

What this does per role (customer / seller):
  * extracts the white glyph from the source gradient tile (whiteness mask)
  * rebuilds a brand gradient fill for the glyph from the source corners
  * emits:
      - 512 / 192  purpose=any      → transparent bg, glyph @ 66%
      - maskable 512                → transparent bg, glyph @ 56% (safe zone)
      - apple-touch 180             → white bg (iOS kills alpha → black), glyph @ 66%
"""
from PIL import Image, ImageFilter

ICONS = '/home/z/insta-shop/public/pwa/icons'
ROLES = ('customer', 'seller')

S_LO, S_HI = 0.08, 0.22      # saturation below S_LO = glyph, above S_HI = bg
V_LO, V_HI = 0.55, 0.80      # brightness guard

def clamp(x, a=0.0, b=1.0):
    return max(a, min(b, x))

def first_opaque_corner(px, w, h):
    """walk diagonally inward until an opaque pixel is found (alpha-safe)."""
    for d in range(min(w, h) // 2):
        yield d

def sample(img):
    """return pixel accessor + size, compositing alpha on WHITE if any."""
    if img.mode in ('RGBA', 'LA', 'PA'):
        base = Image.new('RGBA', img.size, (255, 255, 255, 255))
        base.alpha_composite(img.convert('RGBA'))
        img = base.convert('RGB')
    else:
        img = img.convert('RGB')
    return img

def glyph_mask(img):
    px = img.load()
    w, h = img.size
    m = Image.new('L', (w, h), 0)
    mp = m.load()
    for y in range(h):
        for x in range(w):
            r, g, b = px[x, y]
            mx, mn = max(r, g, b), min(r, g, b)
            v = mx / 255.0
            s = (mx - mn) / mx if mx > 0 else 0.0
            wf = clamp((S_HI - s) / (S_HI - S_LO))
            vf = clamp((v - V_LO) / (V_HI - V_LO))
            mp[x, y] = int(255 * (wf * vf) ** 0.9)
    return m

def gradient_ends(img):
    """dominant linear-gradient axis + the two stop colors."""
    w, h = img.size
    px = img.load()
    TL, TR, BL, BR = px[3, 3], px[w - 4, 3], px[3, h - 4], px[w - 4, h - 4]
    Tm, Bm, Lm, Rm = px[w // 2, 3], px[w // 2, h - 4], px[3, h // 2], px[w - 4, h // 2]
    dh = sum(abs(a - b) for a, b in zip(TR, TL)) + sum(abs(a - b) for a, b in zip(BR, BL)) + sum(abs(a - b) for a, b in zip(Rm, Lm))
    dv = sum(abs(a - b) for a, b in zip(BL, TL)) + sum(abs(a - b) for a, b in zip(BR, TR)) + sum(abs(a - b) for a, b in zip(Bm, Tm))
    if dv >= dh:
        start = tuple((TL[i] + TR[i]) // 2 for i in range(3))
        end = tuple((BL[i] + BR[i]) // 2 for i in range(3))
        axis = 'v'
    else:
        start = tuple((TL[i] + BL[i]) // 2 for i in range(3))
        end = tuple((TR[i] + BR[i]) // 2 for i in range(3))
        axis = 'h'
    return start, end, axis

def gradient_glyph(src_path, target_size, glyph_frac, bg=None):
    src = sample(Image.open(src_path))
    mask = glyph_mask(src)
    bbox = mask.point(lambda p: 255 if p >= 60 else 0).getbbox()
    assert bbox, 'no glyph found in ' + src_path

    g = src.crop(bbox)
    gm = mask.crop(bbox)

    tw = int(target_size * glyph_frac)
    ratio = min(tw / g.width, tw / g.height)
    nw, nh = max(1, round(g.width * ratio)), max(1, round(g.height * ratio))
    gs = g.resize((nw, nh), Image.LANCZOS)
    ms = gm.resize((nw, nh), Image.LANCZOS).filter(ImageFilter.GaussianBlur(0.5))

    start, end, axis = gradient_ends(src)
    grad = Image.new('RGB', (nw, nh))
    gp = grad.load()
    for y in range(nh):
        for x in range(nw):
            t = (y / max(1, nh - 1)) if axis == 'v' else (x / max(1, nw - 1))
            gp[x, y] = tuple(round(start[i] + (end[i] - start[i]) * t) for i in range(3))

    glyph = grad.copy()
    glyph.putalpha(ms)

    canvas = Image.new('RGBA', (target_size, target_size), (0, 0, 0, 0) if bg is None else (*bg, 255))
    canvas.alpha_composite(glyph, ((target_size - nw) // 2, (target_size - nh) // 2))
    return canvas, (start, end, axis, bbox)

for role in ROLES:
    p = f'{ICONS}/{role}-512.png'
    # generate ALL variants from the untouched source first — saving the
    # 512 above overwrites the input file, so order matters here
    any512, info = gradient_glyph(p, 512, 0.66)
    any192 = any512.resize((192, 192), Image.LANCZOS)
    maskable512 = gradient_glyph(p, 512, 0.56)[0]
    apple180 = gradient_glyph(p, 180, 0.66, bg=(255, 255, 255))[0]
    any512.save(f'{ICONS}/{role}-512.png')
    any192.save(f'{ICONS}/{role}-192.png')
    maskable512.save(f'{ICONS}/{role}-maskable-512.png')
    apple180.save(f'{ICONS}/{role}-180.png')
    print(role, 'gradient', info)
print('done')
