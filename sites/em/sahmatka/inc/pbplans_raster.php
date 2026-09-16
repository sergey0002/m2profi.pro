<?php
/**
 * Растровая копия планировок: pbplans/*.svg → pbplans_png/*.png
 * Если PNG на диске есть — отдаём его URL, иначе исходный (SVG/прочее).
 */

/**
 * @param string $image_url  URL или путь из apartaments.image_pb
 * @param string $sahmatka_dir  Абсолютный путь к …/sahmatka (обычно __DIR__)
 * @param string|null $sahmatka_base_url  Базовый URL …/sahmatka/ (если null — собрать из image_url)
 * @return string URL для фида
 */
function em_pbplans_prefer_png($image_url, $sahmatka_dir, $sahmatka_base_url = null)
{
    $image_url = trim((string) $image_url);
    if ($image_url === '') {
        return $image_url;
    }

    $sahmatka_dir = rtrim(str_replace('\\', '/', $sahmatka_dir), '/') . '/';

    // Базовый URL sahmatka/
    if ($sahmatka_base_url === null || $sahmatka_base_url === '') {
        if (preg_match('#^(https?://[^/]+/sahmatka/)#i', $image_url, $m)) {
            $sahmatka_base_url = $m[1];
        } else {
            $sahmatka_base_url = 'https://em.m2profi.pro/sahmatka/';
        }
    }
    $sahmatka_base_url = rtrim($sahmatka_base_url, '/') . '/';

    // Относительный путь внутри sahmatka/
    if (stripos($image_url, $sahmatka_base_url) === 0) {
        $relative = substr($image_url, strlen($sahmatka_base_url));
    } elseif (preg_match('#/sahmatka/(.+)$#i', $image_url, $m)) {
        $relative = $m[1];
    } elseif (strpos($image_url, 'http://') === 0 || strpos($image_url, 'https://') === 0) {
        // Внешний URL не наш — не трогаем
        return $image_url;
    } else {
        $relative = ltrim($image_url, '/');
        if (stripos($relative, 'sahmatka/') === 0) {
            $relative = substr($relative, strlen('sahmatka/'));
        }
    }

    $relative = str_replace('\\', '/', $relative);
    $variants = em_pbplans_path_variants($relative);

    foreach ($variants as $rel) {
        $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
        if ($ext !== 'svg') {
            continue;
        }
        $png_relative = str_replace('pbplans/', 'pbplans_png/', $rel);
        $png_relative = preg_replace('/\.svg$/i', '.png', $png_relative);
        if (is_file($sahmatka_dir . $png_relative)) {
            return $sahmatka_base_url . em_pbplans_encode_url_path($png_relative);
        }
    }

    $ext0 = strtolower(pathinfo($variants[0], PATHINFO_EXTENSION));
    if ($ext0 !== 'svg') {
        if (strpos($image_url, 'http://') !== 0 && strpos($image_url, 'https://') !== 0) {
            return $sahmatka_base_url . em_pbplans_encode_url_path($variants[0]);
        }
        return $image_url;
    }

    // PNG нет — исходный URL (уже рабочий), без повторного encode
    if (strpos($image_url, 'http://') === 0 || strpos($image_url, 'https://') === 0) {
        return $image_url;
    }
    return $sahmatka_base_url . em_pbplans_encode_url_path($variants[0]);
}

/** Варианты пути: как в URL и после urldecode (файлы бывают 41,4.svg и 41%2C4.svg). */
function em_pbplans_path_variants($relative)
{
    $relative = ltrim(str_replace('\\', '/', (string) $relative), '/');
    $out = [];
    foreach ([$relative, rawurldecode($relative)] as $v) {
        if ($v !== '' && !in_array($v, $out, true)) {
            $out[] = $v;
        }
    }
    return $out;
}

/**
 * Кодирует сегменты пути для URL, слэши не трогает.
 */
function em_pbplans_encode_url_path($relative)
{
    $relative = ltrim(str_replace('\\', '/', (string) $relative), '/');
    $parts = explode('/', $relative);
    foreach ($parts as $i => $part) {
        $parts[$i] = rawurlencode($part);
    }
    return implode('/', $parts);
}
