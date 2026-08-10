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

if (!function_exists('render_sun_path_overlay')) {
    /**
     * Разметка оверлея солнца. Пустая строка, если ориентации нет.
     *
     * @param mixed $v1
     * @param mixed $v2
     * @param array{default_time?:string,toggle_default?:bool} $opts
     */
    function render_sun_path_overlay($v1, $v2, array $opts = [])
    {
        if (!sun_path_should_show($v1, $v2)) {
            return '';
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

        $base = '/sahmatka/images/sun/';
        $ellipseSrc = $base . 'ellipse.png';
        $sunSrc = $base . 'sun.png';
        $name = 'sun-time-' . $uid;

        $toggleClass = 'sun-path__toggle' . ($toggleDefault ? ' is-on' : '');
        $togglePressed = $toggleDefault ? 'true' : 'false';
        $stageHidden = $toggleDefault ? '' : ' is-off';
        $controlsHidden = $toggleDefault ? '' : ' is-off';

        $times = [
            'morning' => 'Утро',
            'day' => 'День',
            'evening' => 'Вечер',
        ];

        $html = '<button type="button" class="' . htmlspecialchars($toggleClass) . '"'
            . ' aria-pressed="' . $togglePressed . '"'
            . ' title="Ход солнца" aria-label="Показать ход солнца">'
            . '<img src="' . htmlspecialchars($sunSrc) . '" alt="" width="20" height="20" draggable="false">'
            . '</button>';

        $html .= '<div class="sun-path__stage' . $stageHidden . '">';
        $html .= '<img class="sun-path__ellipse" src="' . htmlspecialchars($ellipseSrc) . '" alt="" draggable="false">';
        $html .= '<div class="sun-path__label sun-path__label--rise">'
            . '<img src="' . htmlspecialchars($sunSrc) . '" alt="" class="sun-path__label-icon" draggable="false">'
            . '<span>Восход</span></div>';
        $html .= '<div class="sun-path__label sun-path__label--set">'
            . '<img src="' . htmlspecialchars($sunSrc) . '" alt="" class="sun-path__label-icon" draggable="false">'
            . '<span>Закат</span></div>';
        $html .= '<img class="sun-path__sun" src="' . htmlspecialchars($sunSrc) . '" alt="" width="28" height="28" draggable="false">';
        $html .= '</div>';

        $html .= '<div class="sun-path__controls' . $controlsHidden . '" role="radiogroup" aria-label="Время суток">';
        foreach ($times as $value => $label) {
            $id = $name . '-' . $value;
            $checked = ($value === $defaultTime) ? ' checked' : '';
            $html .= '<label class="sun-path__radio" for="' . htmlspecialchars($id) . '">'
                . '<input type="radio" id="' . htmlspecialchars($id) . '" name="' . htmlspecialchars($name) . '"'
                . ' value="' . htmlspecialchars($value) . '"' . $checked . '>'
                . '<span class="sun-path__radio-dot" aria-hidden="true"></span>'
                . '<span class="sun-path__radio-text">' . htmlspecialchars($label) . '</span>'
                . '</label>';
        }
        $html .= '</div>';

        return $html;
    }
}
