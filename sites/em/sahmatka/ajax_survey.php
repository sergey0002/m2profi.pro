<?php
// 🔹 Буферизация всего вывода — гарантия чистого JSON
ob_start();
 
// Класс уже подключен в config.php
include_once('config.php');

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    http_response_code(405);
    echo '{"status":"error"}';
    exit;
}

// === Данные формы ===
$nps = isset($_POST['nps_score']) ? (int) $_POST['nps_score'] : null;
$likes = isset($_POST['likes']) ? trim(strip_tags($_POST['likes'])) : '';
$improvements = isset($_POST['improvements']) ? trim(strip_tags($_POST['improvements'])) : '';
$missing = isset($_POST['missing_features']) ? trim(strip_tags($_POST['missing_features'])) : '';
$short = isset($_POST['short_desc']) ? trim(strip_tags($_POST['short_desc'])) : '';

// Данные из сессии (резерв)
$login = $_SESSION['sh_login'] ?? null;
$name = $_SESSION['sh_name'] ?? null;
$agency = $_SESSION['ucaption'] ?? null;

// === 🔹 ОБОГАЩЕНИЕ: берём полные данные из локальной БД ===
$user_full = null;
$agency_full = null;

// Если пользователь авторизован и $mysql доступен — тянем полные данные
if (!empty($_SESSION['sh_id']) && isset($mysql) && method_exists($mysql, 'get_for_key')) {
    // Полные данные пользователя из таблицы users
    $user_full = $mysql->get_for_key('users', 'id', (int)$_SESSION['sh_id']);
    
    // Полные данные агентства из таблицы agency (по agency_id пользователя)
    if ($user_full && !empty($user_full['agency_id'])) {
        $agency_full = $mysql->get_for_key('agency', 'agency_id', (int)$user_full['agency_id']);
    }
}

// === Письмо (ваша вёрстка) ===
$subject = "Результаты опроса (" . ($_SERVER['HTTP_HOST'] ?? 'M2 Profi') . ")";

$message = "
<html>
<body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; background-color: #f4f7f9; padding: 20px;'>
    <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border: 1px solid #e1e8ed;'>
        <div style='background-color: #1a2a40; color: #ffffff; padding: 25px; text-align: center;'>
            <h2 style='margin: 0; font-size: 22px; font-weight: 300;'>Результаты опроса</h2>
            <p style='margin: 5px 0 0; opacity: 0.8; font-size: 14px;'>Проект: " . ($_SERVER['HTTP_HOST'] ?? 'M2 Profi') . "</p>
        </div>
        <div style='padding: 30px;'>
            <div style='margin-bottom: 25px; border-bottom: 2px solid #f0f4f8; padding-bottom: 15px;'>
                <p style='margin: 0; font-size: 13px; color: #888; text-transform: uppercase; letter-spacing: 1px;'>Отправитель</p>
                <p style='margin: 5px 0; font-size: 16px; font-weight: bold; color: #1a2a40;'>" . htmlspecialchars($name ?? '-') . " <span style='font-weight: normal; color: #555;'>(" . htmlspecialchars($login ?? 'Гость') . ")</span></p>
                <p style='margin: 0; font-size: 14px; color: #555;'>Агентство: <strong>" . htmlspecialchars($agency ?? '-') . "</strong></p>
            </div>
            <div style='background-color: #f8faff; border-left: 4px solid #1a2a40; padding: 15px; margin-bottom: 25px;'>
                <p style='margin: 0; font-size: 14px; color: #1a2a40;'><strong>Оценка (NPS):</strong></p>
                <p style='margin: 5px 0 0; font-size: 24px; font-weight: bold; color: #1a2a40;'>" . ($nps ?? 'Не указано') . " / 10</p>
            </div>
            <div style='margin-bottom: 20px;'>
                <p style='margin: 0 0 8px; font-size: 14px; font-weight: bold; color: #555;'>Что больше всего нравится:</p>
                <div style='background: #fff; border: 1px solid #eee; padding: 12px; border-radius: 4px; font-size: 14px; white-space: pre-wrap;'>" . ($likes ? htmlspecialchars($likes) : '<span style="color:#bbb;">Не указано</span>') . "</div>
            </div>
            <div style='margin-bottom: 20px;'>
                <p style='margin: 0 0 8px; font-size: 14px; font-weight: bold; color: #555;'>Что можно было бы улучшить:</p>
                <div style='background: #fff; border: 1px solid #eee; padding: 12px; border-radius: 4px; font-size: 14px; white-space: pre-wrap;'>" . ($improvements ? htmlspecialchars($improvements) : '<span style="color:#bbb;">Не указано</span>') . "</div>
            </div>
            <div style='margin-bottom: 20px;'>
                <p style='margin: 0 0 8px; font-size: 14px; font-weight: bold; color: #555;'>Каких функций не хватает:</p>
                <div style='background: #fff; border: 1px solid #eee; padding: 12px; border-radius: 4px; font-size: 14px; white-space: pre-wrap;'>" . ($missing ? htmlspecialchars($missing) : '<span style="color:#bbb;">Не указано</span>') . "</div>
            </div>
            <div style='margin-bottom: 20px;'>
                <p style='margin: 0 0 8px; font-size: 14px; font-weight: bold; color: #555;'>Продукт одним словом/фразой:</p>
                <div style='background: #fdfdfd; border: 1px dashed #ccc; padding: 12px; border-radius: 4px; font-size: 15px; font-style: italic; color: #333;'>" . ($short ? htmlspecialchars($short) : '<span style="color:#bbb;">Не указано</span>') . "</div>
            </div>
        </div>
        <div style='background-color: #f4f7f9; padding: 15px; text-align: center; font-size: 12px; color: #999; border-top: 1px solid #e1e8ed;'>
            Письмо создано автоматически системой обратной связи " . ($_SERVER['HTTP_HOST'] ?? 'M2 Profi') . "
        </div>
    </div>
</body>
</html>";

$to = getenv('SUPPORT_EMAIL') ?: "89236470002@mail.ru";
$from = getenv('NOREPLY_EMAIL') ?: "no-reply@" . ($_SERVER['HTTP_HOST'] ?? 'm2profi.pro');
$senderName = $_SERVER['HTTP_HOST'] ?? 'M2 Profi';

// === ОТПРАВКА ПИСЬМА ===
$mailSent = false;
if (function_exists('multi_attach_mail')) {
    $mailSent = @multi_attach_mail($to, $subject, $message, $from, $senderName);
}

// === ДУБЛИКАЦИЯ НА УДАЛЁННЫЙ СЕРВЕР ===
// 🔹 Класс уже подключен в config.php — просто используем его
if ($mailSent && class_exists('FormDuplicationSender')) {
    try {
        $sender = new FormDuplicationSender(
            'https://doc.m2profi.pro/api.php',  // 🔹 HTTPS!
            'em',                                // 🔹 Источник
            '657443564563456',                   // 🔹 Код
            ['timeout' => 5]
        );
        
        // 🔹 Формируем массив с обогащёнными данными
        $remoteFields = [
            // Быстрые поля (попадут в отдельные колонки БД приёмника)
            'name'      => $user_full['name'] ?? $name ?? '',
            'phone'     => $user_full['phone'] ?? '',
            'email'     => $user_full['e_mail'] ?? '',
            'nps_score' => $nps,
            
            // Полные данные для отображения в раскрывающейся строке (останутся в JSON)
            'user_fio'      => $user_full['name'] ?? '',
            'user_phone'    => $user_full['phone'] ?? '',
            'user_email'    => $user_full['e_mail'] ?? '',
            'agency_name'   => $agency_full['caption'] ?? $agency ?? '',
            
            // Резервные данные из сессии
            'session_login'   => $login ?? '',
            'session_ucaption'=> $agency ?? '',
            
            // Поля опроса
            'likes'            => $likes,
            'improvements'     => $improvements,
            'missing_features' => $missing,
            'short_desc'       => $short,
        ];
        
        // 🔹 Отправляем
        $result = $sender->send($remoteFields, [], $_SERVER['HTTP_REFERER'] ?? '');
        
        // 🔹 Отладка только при ?dev=1 в URL
        if (isset($_GET['dev']) && !$result['success']) {
            echo "<pre>Debug: " . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
        }
    } catch (Throwable $e) {
        if (isset($_GET['dev'])) { echo "<pre>Exception: " . $e->getMessage() . "</pre>"; }
    }
}

// === ОТВЕТ ПОЛЬЗОВАТЕЛЮ ===
// 🔹 Очищаем буфер и отдаём чистый JSON
ob_end_clean();
if (!headers_sent()) { header('Content-Type: application/json; charset=utf-8'); }
http_response_code(200);

// Всегда возвращаем успех, если письмо ушло (дубликация — вторична)
echo $mailSent ? '{"status":"success"}' : '{"status":"error"}';
 