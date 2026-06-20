<?php

if (!function_exists('window_orient_is_valid_code')) {
    function window_orient_is_valid_code($code)
    {
        $code = (int)$code;
        return $code >= 1 && $code <= 8;
    }
}

if (!function_exists('window_orient_normalize')) {
    /**
     * @return array{0: ?int, 1: ?int}
     */
    function window_orient_normalize($v1, $v2)
    {
        $o1 = (int)$v1;
        $o2 = (int)$v2;
        if ($o1 <= 0) {
            $o1 = null;
        }
        if ($o2 <= 0) {
            $o2 = null;
        }
        if ($o1 !== null && !window_orient_is_valid_code($o1)) {
            $o1 = null;
        }
        if ($o2 !== null && !window_orient_is_valid_code($o2)) {
            $o2 = null;
        }
        if ($o1 !== null && $o2 !== null && $o1 === $o2) {
            $o2 = null;
        }
        return [$o1, $o2];
    }
}

if (!function_exists('window_orient_validate')) {
    function window_orient_validate($v1, $v2)
    {
        foreach ([$v1, $v2] as $v) {
            if ($v === null || $v === '') {
                continue;
            }
            if (!window_orient_is_valid_code($v)) {
                return ['ok' => false, 'error' => 'Недопустимый код ориентации окон'];
            }
        }
        if ($v1 !== null && $v2 !== null && (int)$v1 === (int)$v2) {
            return ['ok' => false, 'error' => 'Направления окон не должны совпадать'];
        }
        return ['ok' => true, 'error' => ''];
    }
}

if (!function_exists('window_orient_labels')) {
    function window_orient_labels($v1, $v2, $short = false)
    {
        $dict = $short
            ? ($GLOBALS['window_orient_short'] ?? [])
            : ($GLOBALS['window_orient'] ?? []);
        $parts = [];
        foreach ([(int)$v1, (int)$v2] as $code) {
            if ($code > 0 && isset($dict[$code])) {
                $parts[] = $dict[$code];
            }
        }
        return implode(', ', $parts);
    }
}

if (!function_exists('window_orient_codes')) {
    function window_orient_codes($v1, $v2)
    {
        $parts = [];
        foreach ([(int)$v1, (int)$v2] as $code) {
            if ($code > 0) {
                $parts[] = (string)$code;
            }
        }
        return implode(', ', $parts);
    }
}

if (!function_exists('window_orient_compass_url')) {
    /** URL картинки компаса для одного направления (1–8). */
    function window_orient_compass_url($code)
    {
        $code = (int)$code;
        $base = $GLOBALS['window_orient_compass_url'] ?? '/sahmatka/images/compas/';
        return rtrim($base, '/') . '/' . $code . '.svg';
    }
}

if (!function_exists('render_window_compass_images')) {
    /**
     * 0 направлений — пустая строка (компас скрыт).
     * 1 направление — одна картинка, 2 — две картинки.
     */
    function render_window_compass_images($v1, $v2, $size = 110)
    {
        list($o1, $o2) = window_orient_normalize($v1, $v2);
        $codes = array_values(array_filter([$o1, $o2]));
        if (!$codes) {
            return '';
        }

        $dict = $GLOBALS['window_orient'] ?? [];
        $html = '<div class="window-compass-list">';
        foreach ($codes as $code) {
            $label = $dict[$code] ?? '';
            $html .= '<img src="' . htmlspecialchars(window_orient_compass_url($code)) . '"'
                . ' width="' . (int)$size . '" height="' . (int)$size . '"'
                . ' alt="' . htmlspecialchars($label) . '"'
                . ' class="window-compass-img" loading="lazy">';
        }
        $html .= '</div>';

        return $html;
    }
}

if (!function_exists('render_window_compass')) {
    /** @deprecated Используйте render_window_compass_images() */
    function render_window_compass($v1, $v2, $size = 120, $show_label = true)
    {
        $html = render_window_compass_images($v1, $v2, $size);
        if ($show_label && $html !== '') {
            $label = window_orient_labels($v1, $v2);
            $html .= '<div class="window-compass__label">' . ($label !== '' ? htmlspecialchars($label) : '—') . '</div>';
        }
        return $html;
    }
}
