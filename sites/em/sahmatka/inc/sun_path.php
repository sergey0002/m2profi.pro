<?php

if (!function_exists('sun_path_should_show')) {
    /**
     * @param mixed $v1
     * @param mixed $v2
     */
    function sun_path_should_show($v1, $v2)
    {
        if (!function_exists('window_orient_normalize')) {
            return false;
        }
        list($o1, $o2) = window_orient_normalize($v1, $v2);
        return $o1 !== null || $o2 !== null;
    }
}

if (!function_exists('sun_path_normalize_src')) {
    /**
     * Абсолютные URL em.* → локальный путь /sahmatka/...
     */
    function sun_path_normalize_src($src)
    {
        $src = trim((string)$src);
        if ($src === '') {
            return '';
        }
        if (preg_match('#https?://[^/]+(/sahmatka/.+)$#i', $src, $m)) {
            return $m[1];
        }
        return $src;
    }
}

if (!function_exists('render_plan_with_sun')) {
    /**
     * Планировка + оверлей солнца.
     *
     * @param string $imgSrc URL/путь image_pb
     * @param mixed $v1
     * @param mixed $v2
     * @param array{default_time?:string,toggle_default?:bool,img_style?:string,img_attrs?:string} $opts
     */
    function render_plan_with_sun($imgSrc, $v1, $v2, array $opts = [])
    {
        $src = sun_path_normalize_src($imgSrc);
        $imgStyle = $opts['img_style'] ?? 'max-height:400px;max-width:100%;width:100%;height:auto;';
        $imgAttrs = $opts['img_attrs'] ?? '';

        $imgHtml = '<img class="plan-with-sun__img" src="' . htmlspecialchars($src) . '"'
            . ' width="1000" height="1000"'
            . ' style="' . htmlspecialchars($imgStyle) . '"'
            . ' alt="" loading="eager" decoding="async" '
            . $imgAttrs
            . '>';

        if (!sun_path_should_show($v1, $v2)) {
            return $imgHtml;
        }

        static $uid = 0;
        $uid++;

        $defaultTime = $opts['default_time'] ?? 'day';
        if (!in_array($defaultTime, ['morning', 'day', 'evening'], true)) {
            $defaultTime = 'day';
        }
        $toggleDefault = array_key_exists('toggle_default', $opts)
            ? (bool)$opts['toggle_default']
            : true;

        $o1 = (int)$v1;
        $o2 = (int)$v2;
        $orientCode = $o1 ?: $o2;
        $orientDegMap = $GLOBALS['window_orient_deg'] ?? [
            1 => 0, 2 => 45, 3 => 90, 4 => 135,
            5 => 180, 6 => 225, 7 => 270, 8 => 315,
        ];
        $orientDeg = isset($orientDegMap[$orientCode]) ? (int)$orientDegMap[$orientCode] : 0;
        $base = '/sahmatka/images/sun/';
        $sunSrc = $base . 'sun.png';

        $toggleClass = 'sun-path__toggle' . ($toggleDefault ? ' is-on' : '');
        $togglePressed = $toggleDefault ? 'true' : 'false';
        $stageOff = $toggleDefault ? ' is-visible' : ' is-off';
        $controlsOff = $toggleDefault ? ' is-visible' : ' is-off';

        $times = [
            'morning' => 'Утро',
            'day' => 'День',
            'evening' => 'Вечер',
        ];

        $html = '<div class="plan-with-sun"'
            . ' data-sun-path'
            . ' data-orient-1="' . $o1 . '"'
            . ' data-orient-2="' . $o2 . '"'
            . ' data-orient-deg="' . $orientDeg . '"'
            . ' data-sun-default="' . htmlspecialchars($defaultTime) . '"'
            . ' data-toggle-default="' . ($toggleDefault ? '1' : '0') . '">';

        // вне frame → absolute к .apartment-plan-col / .mdl-main (зеркало компаса справа)
        $html .= '<button type="button" class="' . htmlspecialchars($toggleClass) . '"'
            . ' aria-pressed="' . $togglePressed . '"'
            . ' title="Ход солнца" aria-label="Показать ход солнца">'
            . '<img src="' . htmlspecialchars($sunSrc) . '" alt="" width="20" height="20" draggable="false">'
            . '</button>';

        $html .= '<div class="plan-with-sun__frame">';
        $html .= $imgHtml;

        $html .= '<div class="sun-path__stage' . $stageOff . '" aria-hidden="' . ($toggleDefault ? 'false' : 'true') . '">';
        // дуга восход→закат (не полный эллипс)
        $html .= '<svg class="sun-path__orbit" xmlns="http://www.w3.org/2000/svg" width="1" height="1" aria-hidden="true" focusable="false">';
        $html .= '<path class="sun-path__arc" d="" fill="none" stroke="#A8A8A8" stroke-width="1.75" stroke-linecap="round" stroke-dasharray="5 5" opacity="0.9"></path>';
        $html .= '</svg>';
        // серые маркеры — строго на концах дуги
        $html .= '<img class="sun-path__marker sun-path__marker--rise" src="' . htmlspecialchars($sunSrc) . '"'
            . ' alt="" width="26" height="26" draggable="false">';
        $html .= '<img class="sun-path__marker sun-path__marker--set" src="' . htmlspecialchars($sunSrc) . '"'
            . ' alt="" width="26" height="26" draggable="false">';
        $html .= '<div class="sun-path__label sun-path__label--rise"><span>Восход</span></div>';
        $html .= '<div class="sun-path__label sun-path__label--set"><span>Закат</span></div>';
        $html .= '<img class="sun-path__sun" src="' . htmlspecialchars($sunSrc) . '" alt="" width="28" height="28" draggable="false">';
        $html .= '</div>'; // stage
        $html .= '</div>'; // frame

        $html .= '<div class="sun-path__controls' . $controlsOff . '" role="radiogroup" aria-label="Время суток">';
        foreach ($times as $value => $label) {
            $isDefault = ($value === $defaultTime);
            $activeClass = $isDefault ? ' is-active' : '';
            $pressed = $isDefault ? 'true' : 'false';
            $html .= '<button type="button" class="sun-path__radio' . $activeClass . '"'
                . ' data-sun-time="' . htmlspecialchars($value) . '"'
                . ' role="radio" aria-checked="' . $pressed . '"'
                . ' aria-label="' . htmlspecialchars($label) . '">'
                . '<span class="sun-path__radio-dot" aria-hidden="true"></span>'
                . '<span class="sun-path__radio-text">' . htmlspecialchars($label) . '</span>'
                . '</button>';
        }
        $html .= '</div>';

        $html .= '</div>';

        return $html;
    }
}

if (!function_exists('render_sun_path_overlay')) {
    /** @deprecated */
    function render_sun_path_overlay($v1, $v2, array $opts = [])
    {
        return '';
    }
}
