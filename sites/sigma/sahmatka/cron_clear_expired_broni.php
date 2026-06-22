<?php
// php /home/m2profi/web/m2profi.pro/public_html/sites/em/sahmatka/cron_clear_expired_broni.php
// Определяем путь к файлу лога

 //wget -q -O /dev/null  "https://xdemo.m2profi.pro/sahmatka/cron_clear_expired_broni.php?x=1823751327542738787123"



$logFile = __DIR__ . '/cronlog.txt';

// Формируем строку для записи (дата, время и "ок")
$logMessage = date('Y-m-d H:i:s') . " ок\n";

// Записываем строку в файл
//file_put_contents($logFile, $logMessage, FILE_APPEND);

// Выводим сообщение в консоль (опционально)
echo "Запись добавлена в файл: $logFile\n";
 

print 'ВЫПОЛНЕНИЕ СКРИПТА !!!!';
 
require_once 'config.php'; // Подключение к БД

session_start();
$_SESSION = [
    'sh_login' => 'admin',
    'sh_id' => 1,
    'agency_id' => 0,
    'adm_caption' => ''
];

// ==================== НАСТРОЙКИ ====================
$DAYS_BEFORE_REMINDER   = 1;          // За сколько дней до снятия отправлять уведомление
$BRONI_AUTO_REMOVE_DAYS = 14;         // Через сколько дней бронь снимается автоматически
$AUTO_REMOVE            = true;       // true = снимать, false = только уведомления и вывод
$ONLY_USER_ID           = null;        // ID пользователя (null = не фильтровать)
$ONLY_AGENCY_ID         = null;       // ID агентства (null = не фильтровать)
// ===================================================

// Создаем объект sahmatka для работы с бронями
$sa = new sahmatka($_SESSION, $connection);
$sent_count = 0;



function isValidEmail($email) {
    // Регулярное выражение для проверки email
    $pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
    return preg_match($pattern, $email) === 1;
}



// === ФУНКЦИЯ ОТПРАВКИ УВЕДОМЛЕНИЙ ===
function sendReminderEmail($row, &$notifications) {
 
    $to         = $row['e_mail'];
    $userName   = htmlspecialchars( $row['user_info'] );
    $home       = htmlspecialchars($row['home_title']);
    $apartment  = htmlspecialchars($row['apartment_num']);
    $bronDate   = date('d.m.Y', strtotime($row['date']));
    $expireDate = date('d.m.Y', strtotime($row['date'] . " +{$GLOBALS['BRONI_AUTO_REMOVE_DAYS']} days"));
	$subject = "${'label'} истекает бронь д.{$home} кв.№{$apartment}";
	/*
	
	'broni_id' => $row['broni_id'],
                'date' => date('d.m.Y', strtotime($row['date'])),
                'user_info' => $row['user_info'],
                'agency_caption' => $row['agency_caption'] ?: '-',
                'home_title' => $row['home_title'],
                'apartment_num' => $row['apartment_num'],
                'days_late' => $row['days_late'],
				'email' => $row['e_mail']
				
				*/
		print '<h1>Формирование уведомления</h1>';		
				if(!isValidEmail($to)){ print '<h1>НЕ ВАЛИДНЫЕ EMAIL '.$to.'</h1>'; return;}
				else{ print '<h1>Валидный email '.$to.'</h1>'; }
    print $message = "
    <html>
    <head>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f6fdfd;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            background: #fff;
            border-radius: 12px;
            max-width: 600px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(to right, #d9f2f4, #eafafc);
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #1e8189;
        }
        .content {
            padding: 30px;
            font-size: 16px;
            line-height: 1.6;
        }
        .content strong {
            color: #000;
        }
        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #aaa;
            border-top: 1px solid #eee;
        }
    </style>
    </head>
    <body>
    <div class='container'>
        <div class='header'>
            <h1>Напоминание об окончании брони</h1>
        </div>
        <div class='content'>
            <p>Уважаемый(-ая) <strong>{$userName}</strong>, {$to}</p>
            <p>Вы бронировали квартиру <strong>№{$apartment}</strong> в доме <strong>«{$home}»</strong><br>Дата бронирования: <strong>{$bronDate}</strong>.</p>
            <p><strong>Внимание!</strong> Срок действия брони истекает <strong>{$GLOBALS['label']}</strong>.</p>
            <p>Если вы планируете продлить бронь — пожалуйста, сделайте это заранее, иначе квартира будет автоматически освобождена.</p>
        </div>
        <div class='footer'>
            Вы получили это письмо, так как зарегистрированы на платформе xdemo.m2profi.pro.
        </div>
    </div>
    </body>
    </html>
    ";

 	 
	// Для теста всегда отправляем на один адрес
	if (multi_attach_mail('89236470002@mail.ru', $subject, $message)) {
			 multi_attach_mail($to, $subject, $message); // Реальная рассылка закомментирована
		return true;
	} else {
		error_log("Ошибка отправки письма пользователю ID {$row['user_id']} ({$to})");
		return false;
	}
	 
    
 
}

// === ФУНКЦИЯ ОТПРАВКИ ТАБЛИЦЫ ПО EMAIL ===
function sendTableByEmail($results, $titles) {
    $to = '89236470002@mail.ru';
	
    $subject = 'СПИСОК УВЕДОМЛЕНИЙ ПО БРОНЯМ';

    ob_start();
    echo "<style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #e0e0e0;
        }
    </style>";

    echo "<table>";
    echo "<thead><tr>";
    foreach ($titles as $key => $title) {
        echo "<th>" . $title . "</th>";
    }
    echo "</tr></thead><tbody>";

    foreach ($results as $row) {
        echo "<tr>";
        foreach ($titles as $key => $title) {
            echo "<td>" . $row[$key] . "</td>";
        }
        echo "</tr>";
    }

    echo "</tbody></table>";
    $html_table = ob_get_clean();

    $message = "
    <html>
    <head>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f6fdfd;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            background: #fff;
            border-radius: 12px;
            max-width: 800px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(to right, #d9f2f4, #eafafc);
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid #ccc;
        }
        .email-content {
            padding: 20px;
        }
        .footer {
            padding: 15px;
            text-align: center;
            font-size: 12px;
            color: #aaa;
            border-top: 1px solid #eee;
        }
    </style>
    </head>
    <body>
    <div class='container'>
        <div class='email-header'>
            <h1>Список уведомлений по броням  </h1>
        </div>
        <div class='email-content'>
            $html_table
        </div>
        <div class='footer'>
            Это автоматическое уведомление от системы xdemo.m2profi.pro
        </div>
    </div>
    </body>
    </html>
    ";
 
    if (multi_attach_mail($to, $subject, $message)) {
        error_log("✅ Таблица с уведомлениями  отправлена на $to");
        return true;
    } else {
        error_log("❌ Ошибка отправки таблицы на $to");
        return false;
    }
	
	
	
    if (multi_attach_mail('bon053@yandex.ru', $subject, $message)) {
        error_log("✅ Таблица с уведомлениями бронями отправлена на $to");
        return true;
    } else {
        error_log("❌ Ошибка отправки таблицы на $to");
        return false;
    }
	 
 
}

// === ЛОГИКА ОТПРАВКИ УВЕДОМЛЕНИЙ ===
echo "<pre>";
echo "Начало выполнения скрипта: " . date('Y-m-d H:i:s') . "\n";
echo "Настройки:\n";
echo " - Уведомление за {$DAYS_BEFORE_REMINDER} дн.\n";
echo " - Автоматическое удаление через {$BRONI_AUTO_REMOVE_DAYS} дн.\n";
echo " - Снимать брони: " . ($AUTO_REMOVE ? 'ДА' : 'НЕТ') . "\n\n";
print '</pre>';
$notifications = [];

// Общая выборка броней для уведомлений
$expired_date_tomorrow = date('Y-m-d', strtotime("-" . ($BRONI_AUTO_REMOVE_DAYS - $DAYS_BEFORE_REMINDER) . " days"));
$expired_date_today = date('Y-m-d', strtotime("-{$BRONI_AUTO_REMOVE_DAYS} days"));

$reminder_labels = [
    $expired_date_tomorrow => 'Завтра',
    $expired_date_today   => 'Сегодня'
];

foreach (['Завтра' => $BRONI_AUTO_REMOVE_DAYS - $DAYS_BEFORE_REMINDER, 'Сегодня' => $BRONI_AUTO_REMOVE_DAYS] as $label => $days_ago) {
    $GLOBALS['label'] = $label;
    $date_check = date('Y-m-d', strtotime("-{$days_ago} days"));

    $where = "broni.status = 4 AND DATE(broni.date) = '$date_check' AND users.e_mail != ''";
    if (!is_null($ONLY_USER_ID)) {
        $where .= " AND broni.user_id = " . intval($ONLY_USER_ID);
    }
    if (!is_null($ONLY_AGENCY_ID)) {
        $where .= " AND users.agency_id = " . intval($ONLY_AGENCY_ID);
    }

    $q = "
    SELECT 
        broni.broni_id,
        broni.date,
        broni.home_id,
        broni.apartments_num AS apartment_num,
        CONCAT(users.name, ' / ', users.login) AS user_info,
        agency.caption AS agency_caption,
        homes.title AS home_title,
		users.e_mail,
        FLOOR((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(broni.date)) / 86400) AS days_late
    FROM broni
    LEFT JOIN users ON users.id = broni.user_id
    LEFT JOIN agency ON agency.agency_id = users.agency_id
    LEFT JOIN homes ON homes.home_id = broni.home_id
    INNER JOIN apartaments ON apartaments.apartament_id = broni.apartament_id
    WHERE $where
      AND apartaments.status_broni_id = broni.broni_id
      AND homes.show > 0
    ";

    $results_reminders = $sa->sql->get_arr($q);
	
	print '<h1>МАССИВ ДАННЫХ для рассылки  УВЕДОМЛЕНИЙ</h1>';
	print_r( $results_reminders);
	print '----<h1>МАССИВ УВЕДОМЛЕНИЙ</h1>---';
	
    foreach ($results_reminders as $row) 
	{
        if (sendReminderEmail($row, $label, $BRONI_AUTO_REMOVE_DAYS)) 
		{
            $notifications[] = [
                'broni_id' => $row['broni_id'],
                'date' => date('d.m.Y', strtotime($row['date'])),
                'user_info' => $row['user_info'],
                'agency_caption' => $row['agency_caption'] ?: '-',
                'home_title' => $row['home_title'],
                'apartment_num' => $row['apartment_num'],
                'days_late' => $row['days_late'],
				'email' => $row['e_mail']
            ];
            $sent_count++;
        }
    }
	
}

// === ТАБЛИЦА УВЕДОМЛЕНИЙ ===
echo "СПИСОК ОТПРАВЛЕННЫХ УВЕДОМЛЕНИЙ:\n";

if (!empty($notifications)) {
    $titles = [
        'broni_id' => 'ID',
        'date' => 'Дата брони',
        'user_info' => 'ФИО / Логин',
        'agency_caption' => 'Агентство',
        'home_title' => 'Дом',
        'apartment_num' => 'Квартира',
        'days_late' => 'Срок брони (дн.)'
    ];

    echo "<style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #e0e0e0;
        }
    </style>";

    echo "<div style='margin: 20px 0;'>";
    $sa->sql->display_table($notifications, $titles, false, 1);
    echo "</div>";

    // Отправляем таблицу уведомлений на email
    echo "Отправка таблицы уведомлений на email...\n";
    if (sendTableByEmail($notifications, $titles)) {
        echo "✅ Таблица уведомлений отправлена на 89236470002@mail.ru\n";
    } else {
        echo "❌ Не удалось отправить таблицу уведомлений\n";
    }
} else {
    echo "ℹ️ Нет уведомлений для отправки\n";
}

// === ЛОГИКА СНЯТИЯ ПРОСРОЧЕННЫХ БРОНЕЙ ===
echo "СПИСОК ПРОСРОЧЕННЫХ БРОНЕЙ:\n";

// Получаем только **актуальные** просроченные брони
$expired_date = date('Y-m-d', strtotime("-{$BRONI_AUTO_REMOVE_DAYS} days"));

$q = "
SELECT 
    broni.broni_id,
    broni.date,
    broni.home_id,
    broni.apartments_num AS apartment_num,
    CONCAT(users.name, ' / ', users.login) AS user_info,
    agency.caption AS agency_caption,
    homes.title AS home_title,
    FLOOR((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(broni.date)) / 86400) AS days_late
FROM broni
LEFT JOIN users ON users.id = broni.user_id
LEFT JOIN agency ON agency.agency_id = users.agency_id
LEFT JOIN homes ON homes.home_id = broni.home_id
INNER JOIN apartaments ON apartaments.apartament_id = broni.apartament_id
WHERE broni.status = 4
  AND DATE(broni.date) <= '$expired_date'
  AND apartaments.status_broni_id = broni.broni_id
  AND homes.show > 0
";

if (!is_null($ONLY_USER_ID)) {
    $q .= " AND broni.user_id = " . intval($ONLY_USER_ID);
}
if (!is_null($ONLY_AGENCY_ID)) {
    $q .= " AND users.agency_id = " . intval($ONLY_AGENCY_ID);
}

$results_broni = $sa->sql->get_arr($q);

if (!empty($results_broni)) {
    // Формируем заголовки для таблицы
    $titles = [
        'broni_id' => 'ID',
        'date' => 'Дата брони',
        'user_info' => 'ФИО / Логин',
        'agency_caption' => 'Агентство',
        'home_title' => 'Дом',
        'apartment_num' => 'Квартира',
        'days_late' => 'Просрочка (дн.)'
    ];

    // Преобразуем дату под формат отображения
    $display_data = array_map(function ($row) {
        return [
            'broni_id' => $row['broni_id'],
            'date' => date('d.m.Y', strtotime($row['date'])),
            'user_info' => $row['user_info'],
            'agency_caption' => $row['agency_caption'] ?: '-',
            'home_title' => $row['home_title'],
            'apartment_num' => $row['apartment_num'],
            'days_late' => $row['days_late']
        ];
    }, $results_broni);

    // Выводим таблицу на экран
    echo "<div style='margin: 20px 0;'>";
    $sa->sql->display_table($display_data, $titles, false, 1);
    echo "</div>";

    // Отправляем таблицу на email
    echo "Отправка таблицы просроченных броней на email...\n";
    if (sendTableByEmail($display_data, $titles)) {
        echo "✅ Таблица успешно отправлена на 89236470002@mail.ru\n";
    } else {
        echo "❌ Не удалось отправить таблицу\n";
    }

    // Если AUTO_REMOVE == true, вызываем up_broni()
    if ($AUTO_REMOVE) {
        foreach ($results_broni as $row) {
            $sa->up_broni($row['broni_id'], 2, 'Автоматическое снятие просроченной брони');
        }
        echo "✅ Применён метод up_broni() для всех просроченных броней. Статус изменён на 'Свободна'.\n";
    } else {
        echo "ℹ️ Режим просмотра: брони не были изменены. Для применения изменений установите \$AUTO_REMOVE = true.\n";
    }

} else {
    echo "ℹ️ Нет просроченных броней по текущему фильтру.\n";
}

echo "Завершение выполнения скрипта: " . date('Y-m-d H:i:s') . "\n";
echo "</pre>";


