"""
Genplan people slicer — Photoshop-like trim to opaque alpha.
- Never crop into opaque pixels (bbox of alpha + EDGE_PAD transparent ring).
- Preserve source alpha when present; RGB strips get border flood-fill.
- Peak midpoints → 9 frames x 2 rows → walk.png / walk_left.png.
"""
from __future__ import annotations

import os
from collections import deque
from typing import List, Tuple

import numpy as np
from PIL import Image

SRC_MAP = {
    "person_m1": r"C:\laragon\www\m2profi.pro\sites\em\.doc\image_4701816.png",
    "person_m2": r"C:\laragon\www\m2profi.pro\sites\em\.doc\image_4702224.png",
    "person_w1": r"C:\laragon\www\m2profi.pro\sites\em\.doc\27.png",
    "person_w2": r"C:\laragon\www\m2profi.pro\sites\em\.doc\234234.png",
    "person_w3": r"C:\laragon\www\m2profi.pro\sites\em\.doc\123123.png",
}

OUT_DIR = r"C:\laragon\www\m2profi.pro\sites\em\sahmatka\template\default\img\genplan_life"
COLS = 9
# 1px like Photoshop "trim" safety — keeps anti-aliased edge, no empty margin
EDGE_PAD = 1
MIN_A = 32


def harden(a: np.ndarray) -> np.ndarray:
    out = a.astype(np.uint8).copy()
    out[out < MIN_A] = 0
    out[out >= 150] = 255
    return out


def flood_fill_bg(rgb: np.ndarray, tol: float) -> np.ndarray:
    h, w, _ = rgb.shape
    corners = np.concatenate(
        [
            rgb[:16, :16].reshape(-1, 3),
            rgb[:16, w - 16 :].reshape(-1, 3),
            rgb[h - 16 :, :16].reshape(-1, 3),
            rgb[h - 16 :, w - 16 :].reshape(-1, 3),
        ],
        axis=0,
    ).astype(np.float64)
    bg = np.median(corners, axis=0)
    dist = np.sqrt(((rgb.astype(np.float64) - bg) ** 2).sum(axis=2))
    seed_ok = dist < tol
    mask = np.zeros((h, w), dtype=bool)
    q: deque = deque()
    for x in range(w):
        for y in (0, h - 1):
            if seed_ok[y, x] and not mask[y, x]:
                mask[y, x] = True
                q.append((y, x))
    for y in range(h):
        for x in (0, w - 1):
            if seed_ok[y, x] and not mask[y, x]:
                mask[y, x] = True
                q.append((y, x))
    while q:
        y, x = q.popleft()
        for ny, nx in ((y - 1, x), (y + 1, x), (y, x - 1), (y, x + 1)):
            if 0 <= ny < h and 0 <= nx < w and not mask[ny, nx] and seed_ok[ny, nx]:
                mask[ny, nx] = True
                q.append((ny, nx))
    return mask


def clear_floor_row(rgb: np.ndarray, alpha: np.ndarray) -> np.ndarray:
    """Clear bright low-sat floor under feet; avoid punching holes in white shoes."""
    a = alpha.copy()
    ys = np.where(a >= MIN_A)[0]
    if ys.size == 0:
        return a
    y0, y1 = int(ys.min()), int(ys.max())
    # только самый низ силуэта
    cut = y0 + int((y1 - y0) * 0.88)
    mx = rgb.max(axis=2).astype(np.float32)
    mn = rgb.min(axis=2).astype(np.float32)
    sat = np.where(mx <= 1, 0.0, (mx - mn) / np.maximum(mx, 1.0))
    floorish = (sat < 0.07) & (mx > 200) & (a > 0)
    floorish[:cut, :] = False

    h, w = a.shape
    pad = np.pad(a == 0, 1, constant_values=True)
    near_t = pad[:-2, 1:-1] | pad[2:, 1:-1] | pad[1:-1, :-2] | pad[1:-1, 2:]
    seeds = np.argwhere(floorish & near_t)
    if seeds.size == 0:
        return harden(a)
    q = deque([(int(y), int(x)) for y, x in seeds])
    seen = np.zeros_like(a, dtype=bool)
    for y, x in q:
        seen[y, x] = True
        a[y, x] = 0
    while q:
        y, x = q.popleft()
        for ny, nx in ((y - 1, x), (y + 1, x), (y, x - 1), (y, x + 1)):
            if 0 <= ny < h and 0 <= nx < w and floorish[ny, nx] and not seen[ny, nx]:
                seen[ny, nx] = True
                a[ny, nx] = 0
                q.append((ny, nx))
    return harden(a)


def row_bands(alpha: np.ndarray) -> List[Tuple[int, int]]:
    h = alpha.shape[0]
    row = (alpha >= MIN_A).mean(axis=1)
    thr = max(0.003, float(np.percentile(row[row > 0], 40)) * 0.35) if (row > 0).any() else 0.01
    segs = []
    st = None
    for i, v in enumerate(row > thr):
        if v and st is None:
            st = i
        if (not v) and st is not None:
            if i - 1 - st > h * 0.1:
                segs.append((st, i - 1))
            st = None
    if st is not None and h - 1 - st > h * 0.1:
        segs.append((st, h - 1))
    segs = sorted(segs, key=lambda s: s[1] - s[0], reverse=True)[:2]
    segs = sorted(segs, key=lambda s: s[0])
    if len(segs) < 2:
        mid = h // 2
        return [(0, mid - 1), (mid, h - 1)]
    return segs


def load_rgba(path: str) -> np.ndarray:
    im = Image.open(path)
    if im.mode == "RGBA":
        arr = np.array(im)
        if (arr[:, :, 3] == 0).mean() > 0.12:
            arr[:, :, 3] = harden(arr[:, :, 3])
            return arr
    rgb = np.array(im.convert("RGB"))
    bg_mask = flood_fill_bg(rgb, tol=20.0)
    alpha = np.full(rgb.shape[:2], 255, dtype=np.uint8)
    alpha[bg_mask] = 0
    alpha = harden(alpha)
    for y0, y1 in row_bands(alpha):
        alpha[y0 : y1 + 1, :] = clear_floor_row(rgb[y0 : y1 + 1, :, :], alpha[y0 : y1 + 1, :])
    return np.dstack([rgb, alpha])


def largest_component(bin_mask: np.ndarray) -> np.ndarray:
    h, w = bin_mask.shape
    visited = np.zeros_like(bin_mask, dtype=bool)
    best_cells = None
    best_n = 0
    for y in range(h):
        for x in range(w):
            if not bin_mask[y, x] or visited[y, x]:
                continue
            q = deque([(y, x)])
            visited[y, x] = True
            cells = [(y, x)]
            while q:
                cy, cx = q.popleft()
                for ny, nx in ((cy - 1, cx), (cy + 1, cx), (cy, cx - 1), (cy, cx + 1)):
                    if 0 <= ny < h and 0 <= nx < w and bin_mask[ny, nx] and not visited[ny, nx]:
                        visited[ny, nx] = True
                        q.append((ny, nx))
                        cells.append((ny, nx))
            if len(cells) > best_n:
                best_n = len(cells)
                best_cells = cells
    out = np.zeros_like(bin_mask, dtype=bool)
    if best_cells:
        for y, x in best_cells:
            out[y, x] = True
    return out


def peak_centers(col: np.ndarray, n: int = COLS) -> List[int]:
    k = 15
    sm = np.convolve(col, np.ones(k) / k, mode="same")
    peaks = []
    for i in range(3, len(sm) - 3):
        if sm[i] >= sm[i - 1] and sm[i] >= sm[i + 1] and sm[i] > max(sm.mean() * 0.45, 0.02):
            peaks.append((float(sm[i]), i))
    peaks.sort(reverse=True)
    chosen = []
    min_dist = max(28, len(sm) // (n * 2 + 1))
    for _, x in peaks:
        if all(abs(x - c) >= min_dist for c in chosen):
            chosen.append(int(x))
        if len(chosen) >= n:
            break
    if len(chosen) < n:
        xs = np.where(col > 0.01)[0]
        lo, hi = (int(xs[0]), int(xs[-1])) if xs.size else (0, len(col) - 1)
        return [int(round(lo + (i + 0.5) * (hi - lo) / n)) for i in range(n)]
    return sorted(chosen)


def trim_to_alpha(cell: np.ndarray) -> np.ndarray:
    """Photoshop-like trim: crop to opaque bbox, then force EDGE_PAD transparent ring."""
    a = cell[:, :, 3]
    ys, xs = np.where(a >= MIN_A)
    if ys.size == 0:
        return np.zeros((8, 8, 4), dtype=np.uint8)
    y0 = max(0, int(ys.min()))
    y1 = min(cell.shape[0] - 1, int(ys.max()))
    x0 = max(0, int(xs.min()))
    x1 = min(cell.shape[1] - 1, int(xs.max()))
    crop = cell[y0 : y1 + 1, x0 : x1 + 1, :].copy()
    pad = EDGE_PAD
    h, w = crop.shape[:2]
    out = np.zeros((h + 2 * pad, w + 2 * pad, 4), dtype=np.uint8)
    out[pad : pad + h, pad : pad + w, :] = crop
    return out


def clear_gap_floor(rgb: np.ndarray, alpha: np.ndarray) -> np.ndarray:
    """Remove bright floor leftovers; do not eat grey clothes/shorts."""
    a = alpha.copy()
    h, w = a.shape
    mx = rgb.max(axis=2).astype(np.float32)
    mn = rgb.min(axis=2).astype(np.float32)
    sat = np.where(mx <= 1, 0.0, (mx - mn) / np.maximum(mx, 1.0))
    # только очень светлый пол (не оливковые шорты)
    floorish = (sat < 0.10) & (mx > 205) & (a >= MIN_A)
    floorish[: int(h * 0.58), :] = False
    pad = np.pad(a < MIN_A, 1, constant_values=True)
    near_t = pad[:-2, 1:-1] | pad[2:, 1:-1] | pad[1:-1, :-2] | pad[1:-1, 2:]
    border_floor = floorish & near_t
    if border_floor.any():
        bg = rgb[border_floor].astype(np.float32).mean(axis=0)
        dist = np.sqrt(((rgb.astype(np.float32) - bg) ** 2).sum(axis=2))
        floorish = floorish & (dist < 38)
    seeds = np.argwhere(floorish & near_t)
    if seeds.size == 0:
        return harden(a)
    q = deque([(int(y), int(x)) for y, x in seeds])
    seen = np.zeros_like(a, dtype=bool)
    for y, x in q:
        seen[y, x] = True
        a[y, x] = 0
    while q:
        y, x = q.popleft()
        for ny, nx in ((y - 1, x), (y + 1, x), (y, x - 1), (y, x + 1)):
            if 0 <= ny < h and 0 <= nx < w and floorish[ny, nx] and not seen[ny, nx]:
                seen[ny, nx] = True
                a[ny, nx] = 0
                q.append((ny, nx))
    return harden(a)


def valley_bounds(col: np.ndarray, centers: List[int]) -> List[Tuple[int, int]]:
    """Split strip at density valleys between peaks (not equal-width windows)."""
    W = len(col)
    if not centers:
        return [(0, W)]
    k = max(9, W // 80)
    sm = np.convolve(col, np.ones(k) / k, mode="same")
    cuts = [0]
    for i in range(len(centers) - 1):
        a, b = centers[i], centers[i + 1]
        if b <= a + 1:
            cuts.append((a + b) // 2)
            continue
        seg = sm[a:b]
        vi = a + int(np.argmin(seg))
        # чуть отодвинуть от пиков, если долина плоская
        cuts.append(int(vi))
    cuts.append(W)
    bounds = []
    for i in range(len(cuts) - 1):
        x0, x1 = cuts[i], cuts[i + 1]
        # маленький зазор на стыке — меньше чужих конечностей
        if i > 0:
            x0 = min(x1 - 4, x0 + 1)
        if i < len(cuts) - 2:
            x1 = max(x0 + 4, x1 - 1)
        bounds.append((max(0, x0), min(W, x1)))
    return bounds


def extract_row_frames(arr: np.ndarray, y0: int, y1: int) -> List[Image.Image]:
    band = arr[y0 : y1 + 1, :, :]
    a = band[:, :, 3]
    col = (a >= MIN_A).mean(axis=0).astype(np.float64)
    centers = peak_centers(col, COLS)
    bounds = valley_bounds(col, centers)
    frames = []
    for x0, x1 in bounds:
        cell = band[:, x0:x1, :].copy()
        # 1) главный силуэт
        keep = largest_component(cell[:, :, 3] >= MIN_A)
        cell[:, :, 3] = np.where(keep, harden(cell[:, :, 3]), 0).astype(np.uint8)
        # 2) пол между ног
        cell[:, :, 3] = clear_gap_floor(cell[:, :, :3], cell[:, :, 3])
        # 3) после чистки пола снова только крупнейший компонент
        keep2 = largest_component(cell[:, :, 3] >= MIN_A)
        cell[:, :, 3] = np.where(keep2, cell[:, :, 3], 0).astype(np.uint8)
        # 4) trim строго по opaque (+1px прозрачная рамка)
        trimmed = trim_to_alpha(cell)
        frames.append(Image.fromarray(trimmed, "RGBA"))
    while len(frames) < COLS:
        frames.append(Image.new("RGBA", (8, 16), (0, 0, 0, 0)))
    return frames[:COLS]


def unify_bottom(frames: List[Image.Image], max_w: int, max_h: int) -> List[Image.Image]:
    """Same cell size for CSS sheet only; bottom-align feet."""
    out = []
    for fr in frames:
        c = Image.new("RGBA", (max_w, max_h), (0, 0, 0, 0))
        ox = (max_w - fr.size[0]) // 2
        oy = max_h - fr.size[1]
        c.paste(fr, (ox, oy), fr)
        out.append(c)
    return out


def compose(frames: List[Image.Image], path: str) -> None:
    fw, fh = frames[0].size
    sheet = Image.new("RGBA", (fw * len(frames), fh), (0, 0, 0, 0))
    for i, fr in enumerate(frames):
        sheet.paste(fr, (i * fw, 0), fr)
    sheet.save(path)


def process(key: str, src: str) -> None:
    arr = load_rgba(src)
    bands = row_bands(arr[:, :, 3])
    top = extract_row_frames(arr, bands[0][0], bands[0][1])
    bot = extract_row_frames(arr, bands[1][0], bands[1][1])
    os.makedirs(OUT_DIR, exist_ok=True)
    # preview frames — tight trim по прозрачности (разные размеры ок)
    for i, fr in enumerate(top, 1):
        fr.save(os.path.join(OUT_DIR, f"{key}_frame{i:02d}.png"))
    for i, fr in enumerate(bot, 10):
        fr.save(os.path.join(OUT_DIR, f"{key}_frame{i:02d}.png"))
    # sheets — только здесь выравниваем размер ячеек под CSS
    tw = max(f.size[0] for f in top)
    th = max(f.size[1] for f in top)
    bw = max(f.size[0] for f in bot)
    bh = max(f.size[1] for f in bot)
    compose(unify_bottom(top, tw, th), os.path.join(OUT_DIR, f"{key}_walk.png"))
    compose(unify_bottom(bot, bw, bh), os.path.join(OUT_DIR, f"{key}_walk_left.png"))
    sizes = ", ".join(f"{f.size[0]}x{f.size[1]}" for f in bot[:3])
    print(f"{key}: sheet R{tw}x{th} L{bw}x{bh}; left frames e.g. {sizes}")


def main() -> None:
    for key, src in SRC_MAP.items():
        if not os.path.exists(src):
            print("missing", key, src)
            continue
        print("processing", key)
        process(key, src)
    print("done ->", OUT_DIR)


if __name__ == "__main__":
    main()
