<?php
/**
 * Генератор изображения простой капчи (Captcha Image Generator)
 *
 * Этот скрипт генерирует изображение с случайным кодом и сохраняет его значение в сессии.
 * Капча используется как fallback-механизм при сбое Google/Yandex reCAPTCHA.
 *
 * Особенности:
 * - Генерация случайного кода из разрешённых символов
 * - Вывод изображения с фоном, линиями и наклонным текстом
 * - Сохранение кода в сессии с привязкой к идентификатору формы
 * - Поддержка нескольких форм на одной странице
 * - Защита от отсутствия шрифта и отсутствия параметра form
 * - Детальное логирование для отладки
 *
 * Использование:
 * <img src="/captcha/image.php?form=form1" alt="CAPTCHA">
 *
 * @version 2025.04
 * @author Защита форм
 */

// Начинаем сессию (если ещё не начата)
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

// --- НАЧАЛО БЛОКА ОТЛАДКИ ---
error_log("--- НАЧАЛО /captcha/image.php ---");
error_log("Captcha Session ID: " . session_id());
error_log("Captcha Session Data BEFORE generation: " . print_r($_SESSION, true));
// --- КОНЕЦ БЛОКА ОТЛАДКИ ---

// ==============================
// Настройки капчи
// ==============================

/**
 * Ширина изображения в пикселях
 * @var int
 */
$width = 170;

/**
 * Высота изображения в пикселях
 * @var int
 */
$height = 50;

/**
 * Размер шрифта в pt
 * @var int
 */
$font_size = 25;

/**
 * Путь к шрифту (должен быть в формате TTF)
 * Относительный путь от корня сайта
 * @var string
 */
$font_path = './verdana.ttf';

/**
 * Реальный путь к файлу шрифта
 * @var string|false
 */
$font = realpath($font_path);

// Проверка существования файла шрифта
if (!$font || !file_exists($font)) {
    error_log("Ошибка CAPTCHA: Шрифт не найден по пути: " . ($font ?: $font_path));
    http_response_code(500);
    die('Ошибка CAPTCHA: файл шрифта не найден.');
}

/**
 * Длина генерируемого кода
 * @var int
 */
$chars_length = 3;

/**
 * Допустимые символы для капчи (исключены 0,1,7,O,l для лучшей читаемости)
 * @var string
 */
$captcha_characters = '2345689';

// ==============================
// Создание изображения
// ==============================

/**
 * Ресурс изображения
 * @var resource
 */
$image = imagecreatetruecolor($width, $height);

/**
 * Цвет фона (тёмно-синий)
 * @var int
 */
$bg_color = imagecolorallocate($image, 6, 10, 46);

/**
 * Цвет текста (золотистый)
 * @var int
 */
$font_color = imagecolorallocate($image, 255, 213, 0);

// Заливаем фон
imagefilledrectangle($image, 0, 0, $width, $height, $bg_color);

// Добавляем случайные линии для усложнения OCR
$vert_line = round($width / 5);
$line_color = imagecolorallocate($image, 255, 255, 255);
for ($i = 0; $i < $vert_line; $i++) {
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $height), rand(0, $width), $line_color);
}

// Расчёт позиций символов
$xw = $width / $chars_length;
$font_gap = $xw / 2 - $font_size / 2;
$digit = '';

// Генерация случайного кода и отрисовка каждого символа
for ($i = 0; $i < $chars_length; $i++) {
    // Выбираем случайный символ
    $letter = $captcha_characters[rand(0, strlen($captcha_characters) - 1)];
    $digit .= $letter;

    // Определяем X-позицию
    $x = $i === 0 ? 0 : $xw * $i;

    // Рисуем символ с наклоном и случайной высотой
    imagettftext(
        $image,
        $font_size,
        rand(-20, 20),           // случайный наклон
        $x + $font_gap,          // X
        rand(22, $height - 5),   // Y
        $font_color,
        $font,
        $letter
    );
}

// ==============================
// Сохранение кода в сессии
// ==============================

/**
 * Идентификатор формы из GET-параметра
 * @var string
 */
$form_id = $_GET['form'] ?? 'default';

// Очищаем от потенциально опасных символов (только буквы, цифры, подчёркивание)
$form_id = preg_replace('/[^a-zA-Z0-9_]/', '', $form_id);
if (empty($form_id)) {
    $form_id = 'default';
}

/**
 * Сгенерированный код (в нижнем регистре)
 * @var string
 */
$captcha_text = strtolower($digit);

// Сохраняем код в сессии:
// - Общий ключ (для совместимости)
// - Привязанный к форме ключ (основной)
$_SESSION['fw_captcha_text'] = $captcha_text;
$_SESSION['fw_captcha_text_' . $form_id] = $captcha_text;

// --- НАЧАЛО БЛОКА ОТЛАДКИ ---
error_log("Generated CAPTCHA Text: " . $captcha_text);
error_log("Form ID: " . $form_id);
error_log("Captcha Session Data AFTER generation: " . print_r($_SESSION, true));
error_log("--- КОНЕЦ /captcha/image.php ---");
// --- КОНЕЦ БЛОКА ОТЛАДКИ ---

// ==============================
// Вывод изображения
// ==============================

// Устанавливаем заголовок для изображения PNG
header('Content-Type: image/png');

// Отправляем изображение в браузер
imagepng($image);

// Освобождаем память
imagedestroy($image);
