<?php
/*
CREATE TABLE IF NOT EXISTS form_submissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source      VARCHAR(50)  NOT NULL,
    source_url  VARCHAR(512) NOT NULL DEFAULT '',
    ip_address  VARCHAR(45)  NOT NULL,
    submission_hash CHAR(64) NOT NULL DEFAULT '',
    user_agent  VARCHAR(512) DEFAULT NULL,
    name        VARCHAR(255) DEFAULT NULL,
    phone       VARCHAR(50)  DEFAULT NULL,
    email       VARCHAR(255) DEFAULT NULL,
    user_id     BIGINT UNSIGNED DEFAULT NULL,
    agency_id   BIGINT UNSIGNED DEFAULT NULL,
    fields      JSON NOT NULL,
    files       JSON DEFAULT NULL,
    created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_source (source),
    INDEX idx_created (created_at),
    INDEX idx_email (email),
    INDEX idx_user_id (user_id),
    INDEX idx_agency_id (agency_id),
    INDEX idx_hash_window (submission_hash, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

*/



// FormReceiver.php
declare(strict_types=1);

class FormReceiver
{
    private array $config;
    private ?PDO $pdo = null;
    private string $requestId;
    private string $source = '';
    private string $sourceUrl = '';
    private array $fields = [];
    private array $files = [];
    private array $quickFields = [];

    /**
     * @param array $config [
     *   'db' => ['dsn' => '...', 'user' => '...', 'pass' => '...'],
     *   'sources' => ['site_main' => 'secret1', 'landing' => 'secret2'],
     *   'duplicate_window' => 300, // секунд
     *   'cors_origin' => '*',
     *   'table' => 'form_submissions'
     * ]
     */
    public function __construct(array $config)
    {
        $this->config = array_merge([
            'db' => [
                'dsn'  => 'mysql:host=127.0.0.1;dbname=saas_db;charset=utf8mb4',
                'user' => 'root',
                'pass' => ''
            ],
            'sources'          => [],
            'duplicate_window' => 300,
            'cors_origin'      => '*',
            'table'            => 'form_submissions'
        ], $config);

        if (empty($this->config['sources'])) {
            throw new \InvalidArgumentException('Config error: at least one source with secret code must be defined.');
        }

        $this->requestId = $_SERVER['HTTP_X_REQUEST_ID'] ?? bin2hex(random_bytes(8));
    }

    /** 🔹 Главный метод: вызывается один раз для обработки запроса */
    public function handle(): void
    {
        $this->setHeaders();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->respond(405, false, [], 'Method Not Allowed');
            return;
        }

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            $this->respond(400, false, [], 'Invalid JSON payload');
            return;
        }

        try {
            $this->authenticate($data);
            $this->preparePayload($data);

            $ip = $this->getClientIp();
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

            $hash = $this->generateSubmissionHash($ip, $ua);
            $this->checkDuplicate($hash);

            $id = $this->saveToDb($ip, $ua, $hash);
            $this->respond(201, true, ['id' => $id]);

        } catch (\DomainException $e) {
            // Ожидаемые ошибки валидации / авторизации / дубли
            $this->respond($e->getCode() ?: 400, false, [], $e->getMessage());
        } catch (\Throwable $e) {
            error_log("[FormReceiver] Critical: {$e->getMessage()} | req: {$this->requestId}");
            $this->respond(500, false, [], 'Internal Server Error');
        }
    }

    /* ========================================
       ВНУТРЕННИЕ МЕТОДЫ
    ======================================== */

    private function setHeaders(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header("Access-Control-Allow-Origin: {$this->config['cors_origin']}");
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Headers: Content-Type, Accept, X-Request-Id');
        header("X-Request-Id: {$this->requestId}");
    }

    private function authenticate(array $data): void
    {
        $this->source = (string)($data['source'] ?? '');
        $code = (string)($data['code'] ?? '');

        if (!isset($this->config['sources'][$this->source]) || $this->config['sources'][$this->source] !== $code) {
            throw new \DomainException('Forbidden: invalid source or secret code', 403);
        }
    }

	private function preparePayload(array $data): void
	{
		$this->sourceUrl = (string)($data['source_url'] ?? '');
		$this->files = is_array($data['files'] ?? null) ? $data['files'] : [];
		$this->fields = is_array($data['fields'] ?? null) ? $data['fields'] : [];

		if (!is_array($this->fields) || !is_array($this->files)) {
			throw new \DomainException('fields and files must be arrays', 400);
		}

		// 🔹 Извлечение и санитизация быстрых полей
		$this->quickFields = [
			'name'      => isset($this->fields['name']) ? mb_substr(trim((string)$this->fields['name']), 0, 255) : null,
			'phone'     => isset($this->fields['phone']) ? preg_replace('/[^\d\+\-\(\) ]/', '', trim((string)$this->fields['phone'])) : null,
			'email'     => isset($this->fields['email']) ? (filter_var(trim((string)$this->fields['email']), FILTER_VALIDATE_EMAIL) ?: null) : null,
			
			// 🔹 NPS: извлекаем в отдельную колонку (число 0-10)
			'nps_score' => isset($this->fields['nps_score']) && is_numeric($this->fields['nps_score']) 
				? max(0, min(10, (int)$this->fields['nps_score'])) 
				: null,
			
			// ❌ user_id и agency_id УБРАНЫ отсюда → останутся в JSON
		];

		// 🗑️ Убираем только те поля, которые извлекли в quickFields, чтобы не дублировать в JSON
		foreach ($this->quickFields as $k => $_) {
			unset($this->fields[$k]);
		}
		$this->fields = empty($this->fields) ? [] : $this->fields;
	} 
    private function generateSubmissionHash(string $ip, string $ua): string
    {
        ksort($this->fields); // Детерминированная сортировка ключей
        $payload = json_encode([
            'ip'     => $ip,
            'source' => $this->source,
            'fields' => $this->fields,
            'ua'     => mb_substr($ua, 0, 200)
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return hash('sha256', $payload);
    }

    private function checkDuplicate(string $hash): void
    {
        $pdo = $this->getDb();
        $cutoff = date('Y-m-d H:i:s', time() - $this->config['duplicate_window']);

        $stmt = $pdo->prepare("
            SELECT 1 FROM {$this->config['table']} 
            WHERE submission_hash = :hash AND created_at >= :cutoff LIMIT 1
        ");
        $stmt->execute(['hash' => $hash, 'cutoff' => $cutoff]);

        if ($stmt->fetch()) {
            throw new \DomainException('Duplicate submission detected (F5 protected)', 409);
        }
    }

    private function saveToDb(string $ip, string $ua, string $hash): int
    {
        $pdo = $this->getDb();
        $table = $this->config['table'];

        $stmt = $pdo->prepare("
            INSERT INTO $table 
            (source, source_url, ip_address, submission_hash, user_agent, name, phone, email, user_id, agency_id, fields, files)
            VALUES 
            (:source, :source_url, :ip, :hash, :ua, :name, :phone, :email, :user_id, :agency_id, :fields, :files)
        ");

        $stmt->execute([
            ':source'     => $this->source,
            ':source_url' => $this->sourceUrl,
            ':ip'         => $ip,
            ':hash'       => $hash,
            ':ua'         => $ua,
            ':name'       => $this->quickFields['name'],
            ':phone'      => $this->quickFields['phone'],
            ':email'      => $this->quickFields['email'],
            ':user_id'    => $this->quickFields['user_id'],
            ':agency_id'  => $this->quickFields['agency_id'],
            ':fields'     => json_encode($this->fields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ':files'      => !empty($this->files) ? json_encode($this->files, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null
        ]);

        return (int)$pdo->lastInsertId();
    }

    private function getClientIp(): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        }
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '0.0.0.0';
    }

    private function getDb(): PDO
    {
        if ($this->pdo === null) {
            $this->pdo = new PDO(
                $this->config['db']['dsn'],
                $this->config['db']['user'],
                $this->config['db']['pass'],
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_PERSISTENT         => true // Опционально: ускоряет повторные запросы
                ]
            );
        }
        return $this->pdo;
    }

    private function respond(int $httpCode, bool $success, array $data = [], ?string $error = null): void
    {
        http_response_code($httpCode);
        echo json_encode([
            'success'    => $success,
            'data'       => $success ? $data : null,
            'error'      => $error,
            'request_id' => $this->requestId
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}







 

// ⚙️ НАСТРОЙКИ В НАЧАЛЕ ФАЙЛА
$receiver = new FormReceiver([
    'db' => [
        'dsn'  => 'mysql:host=127.0.0.1;dbname=m2profi_doc_m2profi;charset=utf8mb4',
        'user' => 'm2profi_doc_m2profi',
        'pass' => 'u/H<9oDP)Gf|tzQR'
    ],
    'sources' => [
        'em'     => '657443564563456',
        'noff' => '467845687680668'
    ],
    'duplicate_window' => 300, // 5 минут защита от F5/двойного клика
    'cors_origin'      => '*', // или '*'
    'table'            => 'form_submissions'
]);

// 🚀 Запуск обработчика
$receiver->handle();