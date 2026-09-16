#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""EM pbplans SVG → pbplans_png (.png) + HTML report by home_id."""

from __future__ import annotations

import argparse
import html
import io
import json
import os
import re
import shutil
import subprocess
import sys
import time
from collections import defaultdict
from dataclasses import asdict, dataclass
from datetime import datetime, timezone
from pathlib import Path
from typing import Iterable
from PIL import Image

try:
    from mozjpeg_lossless_optimization import optimize as mozjpeg_optimize
except ImportError:  # pragma: no cover
    mozjpeg_optimize = None  # type: ignore

try:
    import zopfli.png as zopfli_png
except ImportError:  # pragma: no cover
    zopfli_png = None  # type: ignore

try:
    from playwright.sync_api import sync_playwright
except ImportError:  # pragma: no cover
    sync_playwright = None  # type: ignore

HERE = Path(__file__).resolve().parent
EM_ROOT = HERE.parent
DEFAULT_SRC = EM_ROOT / "sahmatka" / "pbplans"
DEFAULT_DST_JPG = EM_ROOT / "sahmatka" / "pbplans_jpg"
DEFAULT_DST_PNG = EM_ROOT / "sahmatka" / "pbplans_png"
DEFAULT_REPORT = HERE / "reports" / "report.html"
DEFAULT_JSONL = HERE / "reports" / "last_run.jsonl"

# Большая сторона выхода = TARGET_LONG_SIDE, меньшая пропорционально (апскейл ок).
TARGET_LONG_SIDE = 1000
JPEG_QUALITY_DEFAULT = 90
IMG_PREVIEW_CSS = "max-height:300px;max-width:100%;background:#fff;object-fit:contain;"

VIEWBOX_RE = re.compile(
    r'viewBox\s*=\s*["\']\s*([-\d.]+)\s+([-\d.]+)\s+([-\d.]+)\s+([-\d.]+)\s*["\']',
    re.I,
)
SIZE_ATTR_RE = re.compile(r'(width|height)\s*=\s*["\']([^"\']+)["\']', re.I)


@dataclass
class JobResult:
    home_id: str
    rel_svg: str
    rel_out: str
    status: str  # ok | skip | error
    message: str = ""
    width: int = 0
    height: int = 0
    bytes_in: int = 0
    bytes_out: int = 0


def fmt_kb(n: int) -> str:
    """Human size in KiB for reports."""
    if n <= 0:
        return "—"
    return f"{n / 1024:.1f} КБ"

def parse_args(argv: list[str] | None = None) -> argparse.Namespace:
    p = argparse.ArgumentParser(description="Convert EM pbplans SVG → JPG/PNG")
    p.add_argument("--src", type=Path, default=DEFAULT_SRC)
    p.add_argument(
        "--format",
        choices=("png", "jpg"),
        default="png",
        help="Output format (default: png → pbplans_png; jpg → pbplans_jpg / ..jpg, legacy)",
    )
    p.add_argument(
        "--dst",
        type=Path,
        default=None,
        help="Output root (default: pbplans_jpg or pbplans_png by --format)",
    )
    p.add_argument("--home", action="append", default=[], help="Only this home_id (repeatable)")
    p.add_argument("--force", action="store_true", help="Overwrite existing outputs")
    p.add_argument("--limit", type=int, default=0, help="Stop after N conversions attempted")
    p.add_argument("--quality", type=int, default=JPEG_QUALITY_DEFAULT, help="JPEG quality only")
    p.add_argument("--report", type=Path, default=DEFAULT_REPORT)
    p.add_argument("--jsonl", type=Path, default=DEFAULT_JSONL)
    p.add_argument(
        "--backend",
        choices=("auto", "playwright", "inkscape"),
        default="auto",
    )
    p.add_argument(
        "--browser-channel",
        default="msedge",
        help="Playwright channel: msedge | chrome | chromium",
    )
    args = p.parse_args(argv)
    if args.dst is None:
        args.dst = DEFAULT_DST_PNG if args.format == "png" else DEFAULT_DST_JPG
    return args


def home_id_of(rel: Path) -> str:
    parts = rel.parts
    return parts[0] if parts else "_"


def out_name_from_svg(svg_path: Path, fmt: str) -> str:
    stem = svg_name_stem(svg_path)
    if fmt == "jpg":
        return stem + "..jpg"  # legacy Avito JPG naming
    return stem + ".png"


def svg_name_stem(svg_path: Path) -> str:
    name = svg_path.name
    if name.lower().endswith(".svg"):
        return name[:-4]
    return svg_path.stem


def jpg_name_from_svg(svg_path: Path) -> str:
    """Back-compat alias."""
    return out_name_from_svg(svg_path, "jpg")


def iter_svgs(src: Path, homes: list[str]) -> list[Path]:
    if not src.is_dir():
        raise SystemExit(f"Source not found: {src}")
    homes_set = set(homes) if homes else None
    out: list[Path] = []
    for p in sorted(src.rglob("*.svg")):
        if not p.is_file():
            continue
        rel = p.relative_to(src)
        if homes_set is not None and home_id_of(rel) not in homes_set:
            continue
        out.append(p)
    return out


def parse_svg_size(svg_text: str) -> tuple[int, int]:
    """Return (width, height) pixels from viewBox or width/height attrs."""
    m = VIEWBOX_RE.search(svg_text)
    if m:
        w = float(m.group(3))
        h = float(m.group(4))
        if w > 0 and h > 0:
            return max(1, int(round(w))), max(1, int(round(h)))

    attrs = {k.lower(): v for k, v in SIZE_ATTR_RE.findall(svg_text)}
    def num(v: str) -> float | None:
        mm = re.match(r"^\s*([0-9.]+)", v)
        return float(mm.group(1)) if mm else None

    if "width" in attrs and "height" in attrs:
        w = num(attrs["width"])
        h = num(attrs["height"])
        if w and h and w > 0 and h > 0:
            return max(1, int(round(w))), max(1, int(round(h)))

    return 1000, 1000


def ensure_svg_dimensions(svg_text: str, w: int, h: int) -> str:
    """Inject width/height on root <svg> if missing (Illustrator exports)."""
    # strip XML declaration for inline HTML
    svg_text = re.sub(r"<\?xml[^?]*\?>", "", svg_text, count=1, flags=re.I).lstrip()

    def repl(m: re.Match[str]) -> str:
        tag = m.group(0)
        lower = tag.lower()
        if "width=" not in lower:
            tag = tag[:-1] + f' width="{w}"' + tag[-1]
        if "height=" not in lower:
            # re-read because tag may have grown
            if "height=" not in tag.lower():
                tag = tag[:-1] + f' height="{h}"' + tag[-1]
        return tag

    return re.sub(r"<svg\b[^>]*>", repl, svg_text, count=1, flags=re.I)


def fit_rgb(im: Image.Image, long_side: int = TARGET_LONG_SIDE) -> Image.Image:
    """Scale so max(width, height) == long_side (upscale OK). Aspect ratio kept."""
    if im.mode != "RGB":
        if im.mode in ("RGBA", "LA") or (im.mode == "P" and "transparency" in im.info):
            rgba = im.convert("RGBA")
            bg = Image.new("RGB", rgba.size, (255, 255, 255))
            bg.paste(rgba, mask=rgba.split()[-1])
            im = bg
        else:
            im = im.convert("RGB")

    w, h = im.size
    if w < 1 or h < 1:
        return im
    long = max(w, h)
    scale = long_side / float(long)
    nw = max(1, int(round(w * scale)))
    nh = max(1, int(round(h * scale)))
    if (nw, nh) != (w, h):
        im = im.resize((nw, nh), Image.Resampling.LANCZOS)
    return im


def rgba_on_white(png_bytes: bytes) -> Image.Image:
    im = Image.open(io.BytesIO(png_bytes)).convert("RGBA")
    bg = Image.new("RGB", im.size, (255, 255, 255))
    bg.paste(im, mask=im.split()[-1])
    return bg


def save_jpeg_optimized(im: Image.Image, out_path: Path, quality: int) -> int:
    """Save JPEG then MozJPEG lossless Huffman optimize (no visual quality loss)."""
    buf = io.BytesIO()
    im.save(buf, "JPEG", quality=quality, optimize=True, progressive=True)
    data = buf.getvalue()
    if mozjpeg_optimize is not None:
        try:
            data = mozjpeg_optimize(data)
        except Exception as e:  # noqa: BLE001
            print(f"mozjpeg warn: {e}")
    out_path.write_bytes(data)
    return len(data)


def which_oxipng() -> str | None:
    env = os.environ.get("OXIPNG")
    if env and Path(env).is_file():
        return env
    found = shutil.which("oxipng")
    if found:
        return found
    # winget default layout
    winget_root = Path(os.environ.get("LOCALAPPDATA", "")) / "Microsoft" / "WinGet" / "Packages"
    if winget_root.is_dir():
        for p in winget_root.glob("Shssoichiro.Oxipng*/**/oxipng.exe"):
            return str(p)
    local = HERE / "bin" / "oxipng.exe"
    if local.is_file():
        return str(local)
    return None


def save_png_optimized(im: Image.Image, out_path: Path) -> int:
    """Save PNG then lossless recompress: prefer oxipng CLI, else Zopfli."""
    buf = io.BytesIO()
    im.save(buf, "PNG", optimize=True, compress_level=9)
    data = buf.getvalue()
    out_path.parent.mkdir(parents=True, exist_ok=True)
    out_path.write_bytes(data)

    oxi = which_oxipng()
    if oxi:
        try:
            # -o max = max compression; --strip safe; in-place
            r = subprocess.run(
                [oxi, "-o", "max", "--strip", "safe", str(out_path)],
                capture_output=True,
                text=True,
                timeout=180,
                check=False,
            )
            if r.returncode != 0:
                print(f"oxipng warn: {(r.stderr or r.stdout or '')[:300]}")
            return out_path.stat().st_size
        except Exception as e:  # noqa: BLE001
            print(f"oxipng warn: {e}")

    if zopfli_png is not None:
        try:
            data = zopfli_png.optimize(out_path.read_bytes())
            out_path.write_bytes(data)
        except Exception as e:  # noqa: BLE001
            print(f"zopfli warn: {e}")
    return out_path.stat().st_size


def which_inkscape() -> str | None:
    return shutil.which("inkscape")


def render_inkscape(svg_path: Path) -> bytes:
    ink = which_inkscape()
    if not ink:
        raise RuntimeError("inkscape not in PATH")
    import tempfile

    with tempfile.TemporaryDirectory(prefix="svg2jpg_") as td:
        out_png = Path(td) / "out.png"
        # Inkscape 1.x
        cmd_new = [
            ink,
            str(svg_path),
            f"--export-filename={out_png}",
            "--export-type=png",
            f"--export-width={TARGET_LONG_SIDE}",
        ]
        # Inkscape 0.9x (prod svg2png style)
        cmd_old = [
            ink,
            "-z",
            f"--export-png={out_png}",
            str(svg_path),
        ]
        last_err = ""
        for cmd in (cmd_new, cmd_old):
            try:
                r = subprocess.run(
                    cmd,
                    capture_output=True,
                    text=True,
                    timeout=120,
                    check=False,
                )
                if out_png.is_file() and out_png.stat().st_size > 0:
                    return out_png.read_bytes()
                last_err = (r.stderr or r.stdout or "").strip()[:500]
            except Exception as e:  # noqa: BLE001
                last_err = str(e)
        raise RuntimeError(f"inkscape failed: {last_err}")


class PlaywrightRenderer:
    def __init__(self, channel: str = "msedge") -> None:
        if sync_playwright is None:
            raise RuntimeError("playwright not installed")
        self.channel = channel
        self._pw = None
        self._browser = None
        self._page = None

    def __enter__(self) -> "PlaywrightRenderer":
        self._pw = sync_playwright().start()
        launch_kwargs: dict = {"headless": True}
        if self.channel and self.channel != "chromium":
            launch_kwargs["channel"] = self.channel
        try:
            self._browser = self._pw.chromium.launch(**launch_kwargs)
        except Exception:
            # fallback: bundled chromium if installed
            launch_kwargs.pop("channel", None)
            self._browser = self._pw.chromium.launch(**launch_kwargs)
        self._page = self._browser.new_page(
            viewport={"width": TARGET_LONG_SIDE + 40, "height": TARGET_LONG_SIDE + 40},
            device_scale_factor=1,
        )
        return self

    def __exit__(self, *exc) -> None:  # noqa: ANN002
        if self._browser:
            self._browser.close()
        if self._pw:
            self._pw.stop()

    def render_png(self, svg_path: Path) -> bytes:
        assert self._page is not None
        raw = svg_path.read_text(encoding="utf-8", errors="replace")
        w, h = parse_svg_size(raw)
        # Rasterize so the longer side is TARGET_LONG_SIDE (vector → sharp at any size)
        long = max(w, h) or 1
        scale = TARGET_LONG_SIDE / float(long)
        rw = max(1, int(round(w * scale)))
        rh = max(1, int(round(h * scale)))
        body_svg = ensure_svg_dimensions(raw, rw, rh)
        page_html = (
            "<!doctype html><html><head><meta charset='utf-8'>"
            "<style>html,body{margin:0;padding:0;background:#ffffff;}"
            f"svg{{display:block;width:{rw}px;height:{rh}px;background:#ffffff;}}</style>"
            "</head><body>"
            f"{body_svg}"
            "</body></html>"
        )
        self._page.set_viewport_size({"width": rw + 8, "height": rh + 8})
        self._page.set_content(page_html, wait_until="load")
        self._page.wait_for_timeout(150)
        loc = self._page.locator("svg").first
        loc.wait_for(state="visible", timeout=30000)
        return loc.screenshot(type="png", omit_background=False)


def pick_backend(name: str) -> str:
    if name != "auto":
        return name
    if which_inkscape():
        return "inkscape"
    if sync_playwright is not None:
        return "playwright"
    raise SystemExit("No backend: install playwright or put inkscape in PATH")


def convert_one(
    svg_path: Path,
    src_root: Path,
    dst_root: Path,
    *,
    fmt: str,
    force: bool,
    quality: int,
    backend: str,
    renderer: PlaywrightRenderer | None,
) -> JobResult:
    rel = svg_path.relative_to(src_root)
    home = home_id_of(rel)
    out_rel = rel.with_name(out_name_from_svg(svg_path, fmt))
    out_path = dst_root / out_rel
    rel_svg_s = rel.as_posix()
    rel_out_s = out_rel.as_posix()
    bytes_in = svg_path.stat().st_size if svg_path.is_file() else 0

    if out_path.is_file() and not force:
        if out_path.stat().st_mtime >= svg_path.stat().st_mtime:
            return JobResult(
                home,
                rel_svg_s,
                rel_out_s,
                "skip",
                "up-to-date",
                bytes_in=bytes_in,
                bytes_out=out_path.stat().st_size,
            )

    try:
        if backend == "inkscape":
            png = render_inkscape(svg_path)
        elif backend == "playwright":
            if renderer is None:
                raise RuntimeError("playwright renderer not started")
            png = renderer.render_png(svg_path)
        else:
            raise RuntimeError(f"unknown backend {backend}")

        rgb = fit_rgb(rgba_on_white(png), TARGET_LONG_SIDE)
        out_path.parent.mkdir(parents=True, exist_ok=True)
        if fmt == "png":
            nbytes = save_png_optimized(rgb, out_path)
        else:
            nbytes = save_jpeg_optimized(rgb, out_path, quality)
        return JobResult(
            home,
            rel_svg_s,
            rel_out_s,
            "ok",
            width=rgb.size[0],
            height=rgb.size[1],
            bytes_in=bytes_in,
            bytes_out=nbytes,
        )
    except Exception as e:  # noqa: BLE001
        return JobResult(
            home,
            rel_svg_s,
            rel_out_s,
            "error",
            message=str(e)[:800],
            bytes_in=bytes_in,
        )

def rel_url_from_report(report_path: Path, target: Path) -> str:
    try:
        rel = os.path.relpath(str(target.resolve()), str(report_path.parent.resolve()))
        return Path(rel).as_posix()
    except Exception:  # noqa: BLE001
        return target.resolve().as_uri()


def write_report(
    report_path: Path,
    results: list[JobResult],
    *,
    src_root: Path,
    dst_root: Path,
    started: datetime,
    finished: datetime,
    backend: str,
    fmt: str,
) -> None:
    report_path.parent.mkdir(parents=True, exist_ok=True)
    by_home: dict[str, list[JobResult]] = defaultdict(list)
    for r in results:
        by_home[r.home_id].append(r)

    def home_sort_key(hid: str):
        return (0, int(hid)) if hid.isdigit() else (1, hid)

    counts = defaultdict(int)
    for r in results:
        counts[r.status] += 1

    out_label = "PNG" if fmt == "png" else "JPG"
    parts: list[str] = []
    parts.append("<!doctype html><html lang='ru'><head><meta charset='utf-8'>")
    parts.append(f"<title>EM pbplans SVG→{out_label} report</title>")
    parts.append(
        "<style>"
        "body{font-family:system-ui,Segoe UI,sans-serif;margin:24px;background:#f6f7f8;color:#222}"
        "h1{font-size:1.4rem}h2{margin-top:2rem;padding:.4rem .6rem;background:#e8eef5;border-radius:6px}"
        "table{border-collapse:collapse;width:100%;background:#fff;margin:.5rem 0 1.5rem}"
        "th,td{border:1px solid #ccd;padding:8px;vertical-align:top;width:50%}"
        "th{background:#f0f3f7;text-align:left}"
        ".meta{color:#555;font-size:.95rem}"
        ".err{color:#b00020}.skip{color:#666}.ok{color:#0a7a2f}"
        ".path{font-size:12px;word-break:break-all;color:#444;margin-top:6px}"
        ".size{font-size:13px;font-weight:600;margin:4px 0}"
        "</style></head><body>"
    )
    parts.append(f"<h1>Конвертация планировок SVG → {out_label}</h1>")
    parts.append("<div class='meta'>")
    parts.append(f"<div>Старт: {html.escape(started.isoformat())}</div>")
    parts.append(f"<div>Финиш: {html.escape(finished.isoformat())}</div>")
    parts.append(f"<div>Backend: {html.escape(backend)} · format={html.escape(fmt)} · long_side={TARGET_LONG_SIDE}</div>")
    parts.append(f"<div>SRC: {html.escape(str(src_root))}</div>")
    parts.append(f"<div>DST: {html.escape(str(dst_root))}</div>")
    parts.append(
        f"<div>Всего: {len(results)} · "
        f"<span class='ok'>ok={counts['ok']}</span> · "
        f"<span class='skip'>skip={counts['skip']}</span> · "
        f"<span class='err'>error={counts['error']}</span></div>"
    )
    parts.append("</div>")

    for hid in sorted(by_home.keys(), key=home_sort_key):
        rows = by_home[hid]
        parts.append(f"<h2>Дом {html.escape(hid)} <small>({len(rows)} файл.)</small></h2>")
        parts.append(
            f"<table><thead><tr><th>Было (SVG)</th><th>Стало ({out_label})</th></tr></thead><tbody>"
        )
        for r in rows:
            svg_abs = src_root / r.rel_svg
            out_abs = dst_root / r.rel_out
            svg_href = rel_url_from_report(report_path, svg_abs)
            out_href = rel_url_from_report(report_path, out_abs)

            bytes_in = r.bytes_in or (svg_abs.stat().st_size if svg_abs.is_file() else 0)
            bytes_out = r.bytes_out or (out_abs.stat().st_size if out_abs.is_file() else 0)

            status_cls = r.status
            status_note = f"<div class='{status_cls}'>{html.escape(r.status)}"
            if r.message:
                status_note += f" — {html.escape(r.message)}"
            if r.width and r.height:
                status_note += f" · {r.width}×{r.height}"
            status_note += "</div>"

            left = (
                f"<td>{status_note}"
                f"<div class='size'>Размер: {html.escape(fmt_kb(bytes_in))}</div>"
                f"<div class='path'>{html.escape(r.rel_svg)}</div>"
                f"<img src='{html.escape(svg_href)}' alt='svg' style='{IMG_PREVIEW_CSS}'>"
                f"</td>"
            )
            if out_abs.is_file():
                right = (
                    f"<td>"
                    f"<div class='size'>Размер: {html.escape(fmt_kb(bytes_out))}</div>"
                    f"<div class='path'>{html.escape(r.rel_out)}</div>"
                    f"<img src='{html.escape(out_href)}' alt='out' style='{IMG_PREVIEW_CSS}'></td>"
                )
            else:
                right = (
                    f"<td><div class='size'>Размер: —</div>"
                    f"<div class='path'>{html.escape(r.rel_out)}</div><em>нет файла</em></td>"
                )
            parts.append(f"<tr>{left}{right}</tr>")
        parts.append("</tbody></table>")

    parts.append("</body></html>")
    report_path.write_text("\n".join(parts), encoding="utf-8")


def write_jsonl(path: Path, results: Iterable[JobResult]) -> None:
    path.parent.mkdir(parents=True, exist_ok=True)
    with path.open("w", encoding="utf-8") as f:
        for r in results:
            f.write(json.dumps(asdict(r), ensure_ascii=False) + "\n")


def main(argv: list[str] | None = None) -> int:
    args = parse_args(argv)
    src = args.src.resolve()
    dst = args.dst.resolve()
    fmt = args.format
    backend = pick_backend(args.backend)
    svgs = iter_svgs(src, args.home)
    if args.limit and args.limit > 0:
        svgs = svgs[: args.limit]

    print(
        f"backend={backend} format={fmt} long_side={TARGET_LONG_SIDE} "
        f"src={src} dst={dst} files={len(svgs)}"
    )
    started = datetime.now(timezone.utc)
    t0 = time.time()
    results: list[JobResult] = []

    renderer: PlaywrightRenderer | None = None
    try:
        if backend == "playwright":
            renderer = PlaywrightRenderer(channel=args.browser_channel)
            renderer.__enter__()

        for i, svg in enumerate(svgs, 1):
            r = convert_one(
                svg,
                src,
                dst,
                fmt=fmt,
                force=args.force,
                quality=args.quality,
                backend=backend,
                renderer=renderer,
            )
            results.append(r)
            mark = {"ok": "+", "skip": ".", "error": "!"}[r.status]
            print(
                f"[{i}/{len(svgs)}] {mark} {r.rel_svg} -> {r.rel_out} "
                f"({r.status}) {fmt_kb(r.bytes_in)} -> {fmt_kb(r.bytes_out)} {r.message}"
            )
    finally:
        if renderer is not None:
            renderer.__exit__(None, None, None)

    finished = datetime.now(timezone.utc)
    write_report(
        args.report.resolve(),
        results,
        src_root=src,
        dst_root=dst,
        started=started,
        finished=finished,
        backend=backend,
        fmt=fmt,
    )
    write_jsonl(args.jsonl.resolve(), results)
    elapsed = time.time() - t0
    ok = sum(1 for r in results if r.status == "ok")
    skip = sum(1 for r in results if r.status == "skip")
    err = sum(1 for r in results if r.status == "error")
    print(f"done in {elapsed:.1f}s ok={ok} skip={skip} error={err}")
    print(f"report: {args.report.resolve()}")
    return 1 if err else 0


if __name__ == "__main__":
    sys.exit(main())
