<?php
/**
 * Эталон images/compas.svg: красный сектор (#E30613) смещён ~37° по часовой от севера.
 * 1) Выравниваем север (красный сектор строго вверх) — база для кода 1.
 * 2) Остальные коды — поворот базы на 45°·(код−1).
 *
 * php sites/em/sahmatka/tools/rotate_compass_images.php
 */
$src = __DIR__ . '/../../../../images/compas.svg';
$dir = __DIR__ . '/../images/compas';
$cx = 64.5;
$cy = 64.5;

$deg = [
    1 => 0,
    2 => 45,
    3 => 90,
    4 => 135,
    5 => 180,
    6 => 225,
    7 => 270,
    8 => 315,
];

$raw = file_get_contents($src);
if (!preg_match('/<svg[^>]*>(.*)<\/svg>/s', $raw, $m)) {
    fwrite(STDERR, "Cannot parse source SVG\n");
    exit(1);
}
$inner = trim($m[1]);

// Убираем встроенный наклон 33.1554° у окружностей — поворот задаём снаружи целиком
$inner = preg_replace(
    '/\s+transform="rotate\(33\.1554[^"]*\)"/',
    '',
    $inner
);

// Центр красного сектора (окна) — ориентир направления
$rx = (74.3865 + 68.1196 + 60.1443) / 3;
$ry = (50.007 + 68.3094 + 63.0993) / 3;
$dx = $rx - $cx;
$dy = $ry - $cy;
// Азимут по часовой стрелке от севера (вверх = 0°)
$bearingRed = rad2deg(atan2($dx, -$dy));
$alignNorth = round(-$bearingRed, 4);

echo "Red sector bearing in original: {$bearingRed}°\n";
echo "Align north correction: {$alignNorth}°\n";

if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$buildSvg = function (float $angle) use ($cx, $cy, $inner) {
    $svg = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $svg .= '<svg width="129" height="129" viewBox="0 0 129 129" fill="none" xmlns="http://www.w3.org/2000/svg">' . "\n";
    $svg .= '<g transform="rotate(' . $angle . ' ' . $cx . ' ' . $cy . ')">' . "\n";
    $svg .= $inner . "\n";
    $svg .= '</g>' . "\n";
    $svg .= '</svg>' . "\n";
    return $svg;
};

// База: север (красный сектор) строго вверх
file_put_contents($dir . '/_base-north.svg', $buildSvg($alignNorth));
echo "Written: {$dir}/_base-north.svg (north up)\n";

foreach ($deg as $code => $dirAngle) {
    $total = round($alignNorth + $dirAngle, 4);
    $path = $dir . '/' . $code . '.svg';
    file_put_contents($path, $buildSvg($total));
    echo "Written: $path (bearing {$dirAngle}°, total rotate {$total}°)\n";
}

echo "Done.\n";
