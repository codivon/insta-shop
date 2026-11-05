#!/usr/bin/env python3
"""
Regenerate the PWA icons — v2 «gradient tile» design (v0.17.4).

Client ask (translated):
  - the PWA icon is «not beautiful at all» — redesign it
  - make its size on the splash screen SMALLER

Design: the brand glyph (extracted from the previous generation's alpha
channel) rendered in solid white, centered on a rounded gradient tile in
the classic Instagram ramp (yellow → orange → pink → purple, running
bottom-left → top-right) with a soft drop shadow. The tile occupies only
57% of the canvas on the `any` icons, so Chrome's splash screen and the
launcher both draw a visibly smaller mark than before.

Emitted per role (customer / seller):
  - {role}-192.png / {role}-512.png   purpose=any → transparent bg, tile @ 57%
  - {role}-maskable-512.png           full-bleed square, glyph @ 60% (safe zone)
  - {role}-180.png                    apple-touch: full-bleed, opaque
"""
from PIL import Image, ImageDraw, ImageFilter

ICONS = '/home/z/insta-shop/public/pwa/icons'
ROLES = ('customer', 'seller')

TILE_FRAC = 0.57        # tile size on `any` icons (smaller mark — client ask)
GLYPH_IN_TILE = 0.66    # glyph size relative to the tile
GLYPH_MASKABLE = 0.60   # glyph size on the full-bleed maskable icon
GLYPH_APPLE = 0.62      # glyph size on the apple-touch icon

# Instagram ramp, projected bottom-left (t=0) → top-right (t=1)
STOPS = [
    (0.00, (0xF9, 0xCE, 0x34)),   # sunny yellow
    (0.28, (0xFA, 0x7E, 0x1E)),   # orange
    (0.55, (0xD6, 0x29, 0x76)),   # brand pink
    (0.80, (0x96, 0x2F, 0xBF)),   # purple
    (1.00, (0x4F, 0x5B, 0xD5)),   # indigo edge
]


def ramp_color(t: float) -> tuple:
    t = max(0.0, min(1.0, t))
    for (t0, c0), (t1, c1) in zip(STOPS, STOPS[1:]):
        if t <= t1:
            f = 0.0 if t1 == t0 else (t - t0) / (t1 - t0)
            return tuple(round(c0[i] + (c1[i] - c0[i]) * f) for i in range(3))
    return STOPS[-1][1]


def gradient(size: int) -> Image.Image:
    """Instagram-ramp diagonal gradient (bottom-left → top-right)."""
    img = Image.new('RGB', (size, size))
    px = img.load()
    denom = 2 * (size - 1)
    for y in range(size):
        for x in range(size):
            t = (x + (size - 1 - y)) / denom
            px[x, y] = ramp_color(t)
    return img


def rounded_mask(size: int, frac: float, radius_frac: float) -> Image.Image:
    """Rounded-square mask for a tile of `frac*size` centered in `size`."""
    m = Image.new('L', (size, size), 0)
    d = ImageDraw.Draw(m)
    s = int(size * frac)
    off = (size - s) // 2
    d.rounded_rectangle([off, off, off + s, off + s], radius=int(s * radius_frac), fill=255)
    return m


def white_glyph(src_path: str, box: int) -> Image.Image:
    """Extract the glyph (alpha channel) from the source icon → solid white."""
    src = Image.open(src_path).convert('RGBA')
    alpha = src.getchannel('A')
    bbox = alpha.point(lambda p: 255 if p > 8 else 0).getbbox()
    assert bbox, 'no glyph found in ' + src_path

    a = alpha.crop(bbox)
    ratio = min(box / a.width, box / a.height)
    nw, nh = max(1, round(a.width * ratio)), max(1, round(a.height * ratio))
    a = a.resize((nw, nh), Image.LANCZOS).filter(ImageFilter.GaussianBlur(0.4))

    g = Image.new('RGBA', (nw, nh), (255, 255, 255, 0))
    g.putalpha(a)
    return g


def paste_center(base: Image.Image, layer: Image.Image, cx: int, cy: int):
    base.alpha_composite(layer, (cx - layer.width // 2, cy - layer.height // 2))


def make_any(src_path: str, size: int) -> Image.Image:
    """Transparent canvas + rounded gradient tile (57%) + white glyph."""
    canvas = Image.new('RGBA', (size, size), (0, 0, 0, 0))

    tile_frac = TILE_FRAC
    ts = int(size * tile_frac)
    # render the tile big for smooth edges, then scale down
    R = 4 if size >= 400 else 2
    big = size * R
    grad = gradient(big)

    mask = rounded_mask(big, tile_frac, 0.225)
    tile = Image.new('RGBA', (big, big), (0, 0, 0, 0))
    tile.paste(grad, (0, 0), mask)

    # soft drop shadow under the tile (splash/launcher depth)
    shadow = Image.new('RGBA', (big, big), (0, 0, 0, 0))
    sd = ImageDraw.Draw(shadow)
    so = (big - int(big * tile_frac)) // 2
    sd.rounded_rectangle([so, so + int(big * 0.012), so + int(big * tile_frac), so + int(big * tile_frac) + int(big * 0.012)],
                         radius=int(big * tile_frac * 0.225), fill=(15, 15, 35, 70))
    shadow = shadow.filter(ImageFilter.GaussianBlur(big * 0.02))
    canvas.alpha_composite(shadow.resize((size, size), Image.LANCZOS))

    # white glyph on the tile
    g = white_glyph(src_path, int(big * tile_frac * GLYPH_IN_TILE))
    paste_center(tile, g, big // 2, big // 2 - int(big * 0.004))

    canvas.alpha_composite(tile.resize((size, size), Image.LANCZOS))
    return canvas


def make_maskable(src_path: str, size: int = 512) -> Image.Image:
    """Full-bleed gradient square + white glyph inside the safe zone."""
    grad = gradient(size).convert('RGBA')
    g = white_glyph(src_path, int(size * GLYPH_MASKABLE))
    # faint shadow for legibility on the bright yellow corner
    paste_center(grad, g, size // 2, size // 2)
    return grad


def make_apple(src_path: str, size: int = 180) -> Image.Image:
    """Apple-touch icon — opaque, full-bleed (iOS rounds the corners)."""
    grad = gradient(size).convert('RGBA')
    g = white_glyph(src_path, int(size * GLYPH_APPLE))
    paste_center(grad, g, size // 2, size // 2)
    return grad.convert('RGB')


for role in ROLES:
    src = f'{ICONS}/{role}-512.png'
    # generate ALL variants from the untouched source first — saving the
    # 512 above overwrites the input file, so order matters here
    any512 = make_any(src, 512)
    any192 = any512.resize((192, 192), Image.LANCZOS)
    maskable = make_maskable(src)
    apple = make_apple(src)

    any512.save(f'{ICONS}/{role}-512.png')
    any192.save(f'{ICONS}/{role}-192.png')
    maskable.save(f'{ICONS}/{role}-maskable-512.png')
    apple.save(f'{ICONS}/{role}-180.png')
    print(role, 'tile design emitted')

print('done')
