<?php
// FormDuplicationSender.php
declare(strict_types=1);

/**
 * Класс для асинхронной отправки данных форм на удалённый сервер-приёмник.
 * 
 * Особенности:
 * - Чистый PHP, без зависимостей (cURL)
 * - Стандартизированный ответ: {success, data, error, request_id}
 * - Поддержка трейсинга через X-Request-Id
 * - Таймауты и обработка сетевых ошибок
 * - Не блокирует основной поток при использовании с fastcgi_finish_request()
 * 
 * @package FormDuplication
 * @version 2.1
 */
class FormDuplicationSender
{
    private string $endpoint;
    private string $source;
    private string $code;
    private int $timeout;
    private int $connectTimeout;
    private bool $verifySsl;
    private string $userAgent;

    /**
     * Конструктор
     * 
     * @param string $endpoint URL приёмника (например, 'https://doc.m2profi.pro/api.php')
     * @param string $source Идентификатор источника (должен быть в ALLOWED_SOURCES на приёмнике)
     * @param string $code Секретный код для аутентификации источника
     * @param array $options Дополнительные настройки:
     *   - timeout: int (по умолчанию 5) — общий таймаут запроса в секундах
     *   - connect_timeout: int (по умолчанию 3) — таймаут подключения
     *   - verify_ssl: bool (по умолчанию true) — проверять SSL-сертификат
     *   - user_agent: string (по умолчанию 'FormDuplicationSender/2.1')
     * 
     * @throws InvalidArgumentException если источник или код пустые
     */
    public function __construct(string $endpoint, string $source, string $code, array $options = [])
    {
        if (empty($source) || empty($code)) {
            throw new InvalidArgumentException('Source and code cannot be empty.');
        }

        $defaults = [
            'timeout'        => 5,
            'connect_timeout'=> 3,
            'verify_ssl'     => true,
            'user_agent'     => 'FormDuplicationSender/2.1'
        ];
        $opts = array_merge($defaults, $options);

        $this->endpoint       = rtrim($endpoint, '/');
        $this->source         = $source;
        $this->code           = $code;
        $this->timeout        = (int)$opts['timeout'];
        $this->connectTimeout = (int)$opts['connect_timeout'];
        $this->verifySsl      = (bool)$opts['verify_ssl'];
        $this->userAgent      = (string)$opts['user_agent'];
    }

    /**
     * Отправка данных на удалённый сервер
     * 
     * @param array<string, mixed> $fields Произвольные поля формы (ассоциативный массив)
     * @param string[] $files Массив ссылок на файлы (опционально)
     * @param string $sourceUrl URL страницы-источника (опционально)
     * 
     * @return array{
     *   success: bool,
     *   data?: array{id: int},
     *   error?: string,
     *   request_id?: string,
     *   http_code?: int,
     *   raw_response?: string
     * }
     */
    public function send(array $fields, array $files = [], string $sourceUrl = ''): array
    {
        $requestId = bin2hex(random_bytes(8));
        
        $payload = [
            'source'     => $this->source,
            'code'       => $this->code,
            'source_url' => $sourceUrl,
            'fields'     => $fields,
            'files'      => $files
        ];

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($jsonPayload === false) {
            return [
                'success'    => false,
                'error'      => 'JSON encoding error: ' . json_last_error_msg(),
                'request_id' => $requestId
            ];
        }

        $ch = curl_init();
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
            'Content-Length: ' . strlen($jsonPayload),
            'X-Request-Id: ' . $requestId
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL            => $this->endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $jsonPayload,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_TIMEOUT        => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_USERAGENT      => $this->userAgent,
            CURLOPT_SSL_VERIFYPEER => $this->verifySsl,
            CURLOPT_SSL_VERIFYHOST => $this->verifySsl ? 2 : 0,
            CURLOPT_ENCODING       => '', // Поддержка gzip/deflate
            CURLOPT_FOLLOWLOCATION => true, // Следовать за редиректами
            CURLOPT_MAXREDIRS      => 3,    // Макс. 3 редиректа
            CURLOPT_IPRESOLVE      => CURL_IPRESOLVE_V4 // Предпочитать IPv4 (опционально, для стабильности)
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErrno = curl_errno($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Обработка сетевых ошибок
        if ($curlErrno !== CURLE_OK) {
            return [
                'success'    => false,
                'error'      => 'Network error (' . $curlErrno . '): ' . $curlError,
                'request_id' => $requestId
            ];
        }

        // Попытка декодировать ответ
        $decoded = null;
        if (!empty($response)) {
            $decoded = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Сервер вернул не-JSON (например, HTML-ошибку 500)
                return [
                    'success'      => false,
                    'error'        => 'Invalid response format (HTTP ' . $httpCode . ')',
                    'http_code'    => $httpCode,
                    'raw_response' => substr($response, 0, 500), // Обрезаем для лога
                    'request_id'   => $requestId
                ];
            }
        }

        // Успешный ответ от приёмника
        if ($httpCode >= 200 && $httpCode < 300 && isset($decoded['success']) && $decoded['success'] === true) {
            return [
                'success'    => true,
                'data'       => $decoded['data'] ?? [],
                'request_id' => $decoded['request_id'] ?? $requestId
            ];
        }

        // Ошибка на стороне сервера (4xx / 5xx)
        return [
            'success'      => false,
            'error'        => $decoded['error'] ?? ('Server error (HTTP ' . $httpCode . ')'),
            'http_code'    => $httpCode,
            'raw_response' => $response,
            'request_id'   => $decoded['request_id'] ?? $requestId
        ];
    }

    /**
     * Геттеры для отладки / тестов
     */
    public function getEndpoint(): string { return $this->endpoint; }
    public function getSource(): string { return $this->source; }
    public function getTimeout(): int { return $this->timeout; }
    
    /**
     * Установка таймаутов "на лету" (если нужно переиспользовать экземпляр)
     */
    public function setTimeouts(int $timeout, int $connectTimeout): self
    {
        $this->timeout = $timeout;
        $this->connectTimeout = $connectTimeout;
        return $this;
    }
}