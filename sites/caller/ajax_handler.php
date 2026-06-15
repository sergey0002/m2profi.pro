<?php
// Единая точка входа для всех AJAX-запросов

session_start();
require_once 'config.php'; // Это инициализирует $pdo и $config

class AjaxHandler {
    private $pdo;
    private $config;

    public function __construct($pdo, $config) {
        $this->pdo = $pdo;
        $this->config = $config;
    }

    public function handleRequest($action) {
        if (method_exists($this, $action)) {
            $this->{$action}();
        } else {
            $this->sendError('Invalid action');
        }
    }

    private function sendSuccess($data = []) {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    private function sendError($message, $statusCode = 400) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }

    /**
     * Форматирует телефонный номер к единому виду +7 (XXX) XXX-XX-XX.
     * Обрабатывает несколько номеров, разделенных разделителями.
     *
     * @param string|null $phone_string Строка с одним или несколькими телефонными номерами.
     * @return string Отформатированная строка с телефонными номерами или "Нет данных".
     */
    private function formatPhoneNumber(?string $phone_string): string {
        if (empty($phone_string)) {
            return "Нет данных";
        }

        // Используем логику из 1.php для нормализации каждого номера
        $parts = preg_split('/[,;]/', $phone_string, -1, PREG_SPLIT_NO_EMPTY);
        $formatted_numbers = [];

        foreach ($parts as $part) {
            $normalized_phone = $this->normalizeSinglePhone($part);
            if ($normalized_phone !== null) {
                // Теперь форматируем в +7 (XXX) XXX-XX-XX, если это российский номер
                $digits_only = preg_replace('/\D/', '', $normalized_phone);
                if (strlen($digits_only) === 11 && $digits_only[0] === '7') {
                    $formatted_numbers[] = '+7 (' . substr($digits_only, 1, 3) . ') ' .
                                           substr($digits_only, 4, 3) . '-' .
                                           substr($digits_only, 7, 2) . '-' .
                                           substr($digits_only, 9, 2);
                } else {
                    // Если не российский номер или не 11 цифр, оставляем нормализованный вид
                    $formatted_numbers[] = $normalized_phone;
                }
            } else {
                // Если номер совсем некорректный, оставляем оригинальную часть (с trim)
                $formatted_numbers[] = trim($part);
            }
        }
        // Удаляем дубликаты и объединяем через запятую с пробелом
        $formatted_numbers = array_unique($formatted_numbers);
        return empty($formatted_numbers) ? "Нет данных" : implode(', ', $formatted_numbers);
    }

    /**
     * Вспомогательная функция для нормализации одного телефонного номера (аналог normalizePhone из 1.php).
     * @param string $phone
     * @return string|null
     */
    private function normalizeSinglePhone(string $phone): ?string {
        // Оставляем только цифры
        $digits = preg_replace('/[^0-9]/', '', trim($phone));
        $len = strlen($digits);

        if ($len === 0) {
            return null;
        }

        // Стандартные российские номера
        if ($len === 10 && !in_array($digits[0], ['7', '8'])) { // например 923...
            return '+7' . $digits;
        }
        if ($len === 11 && $digits[0] === '8') { // 8923...
            return '+7' . substr($digits, 1);
        }
        if ($len === 11 && $digits[0] === '7') { // 7923...
            return '+' . $digits;
        }

        // Если номер не стандартный, просто возвращаем цифры.
        // Это позволит отображать городские или иностранные номера.
        if (strpos(trim($phone), '+') === 0) {
            return '+' . $digits;
        }

        return $digits;
    }

    public function get_phones() {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $this->sendError('Ошибка: неверный ID.');
        }

        $id = (int)$_GET['id'];

        try {
            $stmt = $this->pdo->prepare("SELECT work_phone_contact, mobile_phone_contact FROM caller WHERE id = ?");
            $stmt->execute([$id]);
            $phones = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($phones) {
                $output = [];
                $output['work_phone'] = $this->formatPhoneNumber($phones['work_phone_contact']);
                $output['mobile_phone'] = $this->formatPhoneNumber($phones['mobile_phone_contact']);

                if (empty($output)) {
                    $this->sendSuccess(['message' => 'Нет номеров']);
                } else {
                    $this->sendSuccess($output);
                }
            } else {
                $this->sendSuccess(['message' => 'Нет данных']);
            }

        } catch (\PDOException $e) {
            // В реальном приложении здесь должно быть логирование
            // error_log($e->getMessage());
            $this->sendError('Ошибка базы данных.', 500);
        }
    }

    public function add_comment() {
        if (!isset($_SESSION['user_id'])) {
            $this->sendError('Unauthorized', 403);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id']) || !isset($data['comment'])) {
            $this->sendError('Missing required fields');
        }

        global $config; // Доступ к глобальной переменной $config
        try {
            $this->pdo->beginTransaction();

            // Добавляем комментарий в события
            $stmt = $this->pdo->prepare("INSERT INTO events (datetime, caller_id, event_type, comment, user_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                time(),
                $data['id'],
                $config['event_type_ids']['Добавление комментария'],
                $data['comment'],
                $_SESSION['user_id']
            ]);

            // Если передан цвет, обновляем его
            if (isset($data['color'])) {
                $stmt = $this->pdo->prepare("UPDATE caller SET color = ?, last_user_id = ?, last_update = ? WHERE id = ?");
                $stmt->execute([$data['color'], $_SESSION['user_id'], time(), $data['id']]);
            } else {
                // Обновляем только last_user_id если цвет не меняется
                $stmt = $this->pdo->prepare("UPDATE caller SET last_user_id = ?, last_update = ? WHERE id = ?");
                $stmt->execute([$_SESSION['user_id'], time(), $data['id']]);
            }

            $this->pdo->commit();
            $this->sendSuccess();

        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            // error_log($e->getMessage());
            $this->sendError('Database error: ' . $e->getMessage(), 500);
        }
    }

    public function update_color() {
        if (!isset($_SESSION['user_id'])) {
            $this->sendError('Unauthorized', 403);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id']) || !isset($data['color'])) {
            $this->sendError('Missing required fields');
        }

        global $config; // Доступ к глобальной переменной $config
        try {
            $this->pdo->beginTransaction();

            // Обновляем цвет и last_user_id в таблице caller
            $stmt = $this->pdo->prepare("UPDATE caller SET color = ?, last_user_id = ?, last_update = ? WHERE id = ?");
            $stmt->execute([$data['color'], $_SESSION['user_id'], time(), $data['id']]);

            // Добавляем событие об изменении цвета
            $stmt = $this->pdo->prepare("INSERT INTO events (datetime, caller_id, event_type, comment, user_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                time(),
                $data['id'],
                $config['event_type_ids']['Изменение цвета'],
                'Изменен цвет строки на ' . $config['rowcolors'][$data['color']],
                $_SESSION['user_id']
            ]);

            $this->pdo->commit();
            $this->sendSuccess();

        } catch (\PDOException $e) {
            $this->pdo->rollBack();
            // error_log($e->getMessage());
            $this->sendError('Database error: ' . $e->getMessage(), 500);
        }
    }

    public function log_phone_view() {
        if (!isset($_SESSION['user_id'])) {
            $this->sendError('Unauthorized', 403);
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id'])) {
            $this->sendError('Missing required fields');
        }

        global $config; // Доступ к глобальной переменной $config
        try {
            // Проверяем последнее событие просмотра для этой записи
            $stmt = $this->pdo->prepare("SELECT user_id
                                  FROM events
                                  WHERE caller_id = ? AND event_type = ?
                                  ORDER BY datetime DESC
                                  LIMIT 1");
            $stmt->execute([
                $data['id'],
                $config['event_type_ids']['Просмотр телефона']
            ]);
            $lastView = $stmt->fetch(PDO::FETCH_ASSOC);

            // Добавляем событие только если последний просмотр был другим пользователем или событий просмотра еще не было
            if (!$lastView || $lastView['user_id'] != $_SESSION['user_id']) {
                $this->pdo->beginTransaction();

                // Добавляем событие просмотра телефона
                $stmt = $this->pdo->prepare("INSERT INTO events (datetime, caller_id, event_type, comment, user_id) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    time(),
                    $data['id'],
                    $config['event_type_ids']['Просмотр телефона'],
                    'Просмотр контактных данных',
                    $_SESSION['user_id']
                ]);

                // Обновляем last_user_id
                $stmt = $this->pdo->prepare("UPDATE caller SET last_user_id = ?, last_update = ? WHERE id = ?");
                $stmt->execute([$_SESSION['user_id'], time(), $data['id']]);

                $this->pdo->commit();
            }

            $this->sendSuccess();

        } catch (\PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            // error_log($e->getMessage());
            $this->sendError('Database error: ' . $e->getMessage(), 500);
        }
    }

    public function get_caller_data() {
        try {
            $stmt = $this->pdo->query("
                SELECT
                    c.*,
                    c.work_phone_contact,
                    c.mobile_phone_contact,
                    (SELECT comment FROM events WHERE caller_id = c.id AND event_type = 1 ORDER BY datetime DESC LIMIT 1) as last_comment
                FROM
                    caller c
            ");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->sendSuccess($data);
        } catch (\PDOException $e) {
            $this->sendError('Database error: ' . $e->getMessage(), 500);
        }
    }

    public function get_events() {
        if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
            $this->sendError('Invalid ID');
        }

        $id = (int)$_GET['id'];
        global $config; // Доступ к глобальной переменной $config

        try {
            $stmt = $this->pdo->prepare("SELECT e.id, FROM_UNIXTIME(e.datetime) as datetime, e.comment, e.event_type, u.full_name AS user
                                  FROM events e
                                  LEFT JOIN users u ON e.user_id = u.id
                                  WHERE e.caller_id = ? AND e.event_type IN (1, 2)
                                  ORDER BY e.datetime DESC");
            $stmt->execute([$id]);
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Преобразуем типы событий в текстовые описания
            foreach ($events as &$event) {
                if (isset($event['event_type']) && isset($config['event_types'][$event['event_type']])) {
                    $event['event_type_text'] = $config['event_types'][$event['event_type']];
                } else {
                    $event['event_type_text'] = 'Неизвестное событие (#' . $event['event_type'] . ')';
                }
            }

            $this->sendSuccess(['events' => $events]);
        } catch (PDOException $e) {
            // error_log($e->getMessage());
            $this->sendError('Database error', 500);
        }
    }
}

// --- Router ---
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$requestData = [];

// Если action не найден в $_POST или $_GET, пытаемся получить его из JSON-тела
if (empty($action) && $_SERVER['REQUEST_METHOD'] === 'POST' && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $input = file_get_contents('php://input');
    $requestData = json_decode($input, true);
    if (isset($requestData['action'])) {
        $action = $requestData['action'];
    }
    // Логируем сырой ввод и декодированные данные для отладки
    error_log("AJAX Handler: Raw JSON input: " . $input);
    error_log("AJAX Handler: Decoded JSON data: " . print_r($requestData, true));
}

if (empty($action)) {
    error_log("AJAX Handler: Action not specified. POST: " . print_r($_POST, true) . " GET: " . print_r($_GET, true) . " JSON Data: " . print_r($requestData, true));
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Action not specified']);
    exit;
}
error_log("AJAX Handler: Action received: " . $action);

// Используем глобальные $pdo и $config
global $pdo, $config;

try {
    $handler = new AjaxHandler($pdo, $config);
    $handler->handleRequest($action);

} catch (PDOException $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    // В реальном приложении здесь должно быть логирование, а не вывод ошибки
    error_log("AJAX Handler: Database connection error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database connection error: ' . $e->getMessage()]);
    exit;
}