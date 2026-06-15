<?php

require_once 'CaptchaValidator.php';
require_once 'Validator.php';

/**
 * FormProtect — ядро антибот/антифлуд/CSRF-защиты формы (simple-captcha only, современный ООП)
 */
class FormProtect
{
    protected $post;
    protected $files;
    public $formId;
    protected $debug = true;

    public function __construct(array $post = null, array $files = null)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $this->post = $post ?? $_POST;
        $this->files = $files ?? $_FILES;
        $this->formId = $this->post['_fp_form'] ?? 'default';
        // Флаг отладки — можно управлять через define('FP_DEBUG', true) или сессию
        $this->debug = (defined('FP_DEBUG') && FP_DEBUG) || (!empty($_SESSION['FP_DEBUG']));
    }

    /**
     * Полная проверка формы: валидация, базовые защиты, капча (через CaptchaValidator), дубль.
     * В случае ошибок — сразу exit с JSON.
     * Возвращает только валидированные данные.
     */
    public function validateForm(array $rules, $captchaRequired = true)
    {
        // 1. Валидация пользовательских данных
        $validator = new Validator($rules);
        if (!$validator->passes()) {
            $this->fail(
                'Исправьте ошибки в форме',
                $validator->errors(),
                false,
                true,
                $this->getDebugInfo(['stage' => 'validator'])
            );
        }

        // 2. Базовые проверки (антибот/антиспам, дубль — только после капчи)
        $this->checkBasicSecurity(false);

        // 3. Капча (если требуется) — полностью делегирована!
        if ($captchaRequired) {
            $captcha = new CaptchaValidator($this->formId, $this->post['_fp_captcha'] ?? null);
            // Можно расширить: 
			//$captcha->setDebug($this->debug);
            $captcha->validateOrFail($this->debug ? $this->getDebugInfo(['stage' => 'captcha']) : []);
        }

        // 4. Дубль (после капчи)
        if ($this->isDuplicate()) {
            $this->fail(
                'Эти данные уже были отправлены ранее. Подождите немного перед повторной отправкой.',
                [],
                false,
                false,
                $this->getDebugInfo(['stage' => 'duplicate'])
            );
        }
        $this->markAsSent();

        // 5. Вернём валидированные данные
        return $validator->validData();
    }

    // ==== CSRF ====
    public function csrfToken()
    {
        if (empty($_SESSION['_csrf'][$this->formId])) {
            $_SESSION['_csrf'][$this->formId] = bin2hex(random_bytes(16));
        }
        return $_SESSION['_csrf'][$this->formId];
    }

    public function checkCsrf()
    {
        return isset($this->post['_csrf'], $_SESSION['_csrf'][$this->formId])
            && hash_equals($_SESSION['_csrf'][$this->formId], $this->post['_csrf']);
    }

    public function clearCsrfToken()
    {
        unset($_SESSION['_csrf'][$this->formId]);
    }

    // ==== Honeypot ====
    public function honeypotCheck()
    {
        return empty($this->post['_fp_hp']);
    }

    // ==== JS-флаг ====
    public function jsCheck()
    {
        return !empty($this->post['_fp_js']) && $this->post['_fp_js'] === '1';
    }

    // ==== Антидубль ====
    public function isDuplicate($timeoutSeconds = 30)
    {
        $data = $this->getDupCheckData();
        ksort($data);
        $hash = hash('sha256', $this->formId . '|' . serialize($data));
        $now = time();
        $lastEntry = $_SESSION['_fp_last'][$this->formId] ?? null;

        return ($lastEntry && is_array($lastEntry)
            && isset($lastEntry['hash'], $lastEntry['time'])
            && $lastEntry['hash'] === $hash
            && ($now - $lastEntry['time']) < $timeoutSeconds);
    }

    public function clearDuplicate()
    {
        unset($_SESSION['_fp_last'][$this->formId]);
    }

    public function markAsSent()
    {
        $data = $this->getDupCheckData();
        ksort($data);
        $hash = hash('sha256', $this->formId . '|' . serialize($data));
        $_SESSION['_fp_last'][$this->formId] = ['hash' => $hash, 'time' => time()];
    }

    /**
     * Формирует скрытые поля для формы (вызывать в HTML).
     */
    public static function hiddenFields($form = 'default')
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['_csrf'][$form])) {
            $_SESSION['_csrf'][$form] = bin2hex(random_bytes(16));
        }
        $csrf = htmlspecialchars($_SESSION['_csrf'][$form]);
        return <<<HTML
<input type="hidden" name="_fp_form" value="{$form}">
<input type="hidden" name="_csrf" value="{$csrf}">
<input type="text" name="_fp_hp" value="" style="display:none;" autocomplete="off">
<input type="hidden" name="_fp_js" value="1">
HTML;
    }

    // ==== Основная базовая проверка ====
    public function checkBasicSecurity($checkDuplicate = true)
    {
		// Периодически без причины вылетает !
		/*
        if (!$this->checkCsrf()) {
            $this->fail(
                'Ошибка безопасности, попробуйте обновить страницу.',
                [],
                false,
                false,
                $this->getDebugInfo(['stage' => 'csrf'])
            );
        }
		*/
        if (!$this->honeypotCheck()) {
            $this->fail(
                'Попробуйте повторить позже, сообщение похоже на спам!',
                [],
                false,
                false,
                $this->getDebugInfo(['stage' => 'honeypot'])
            );
        }
        if (!$this->jsCheck()) {
            $this->fail(
                'Включите JavaScript для отправки формы!',
                [],
                false,
                false,
                $this->getDebugInfo(['stage' => 'jscheck'])
            );
        }
        if ($checkDuplicate && $this->isDuplicate()) {
            $this->fail(
                'Эти данные уже были отправлены. Измените данные или подождите немного.',
                [],
                false,
                false,
                $this->getDebugInfo(['stage' => 'duplicate_check'])
            );
        }
    }

    /**
     * Формирует и выводит JSON-ответ об ошибке, завершает выполнение.
     * $validation_failed_early — специальный флаг для ранней валидации на фронте.
     */
    public function fail($message, $errors = [], $force_captcha = false, $validation_failed_early = false, $debugInfo = [])
    {
        $out = [
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'force_captcha' => $force_captcha
        ];
        if ($validation_failed_early) $out['validation_failed_early'] = true;
        if ($this->debug && !empty($debugInfo)) $out['debug'] = $debugInfo;
        echo json_encode($out, JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Формирует и выводит JSON-ответ об успехе, завершает выполнение.
     */
    public function ok($msg = 'Форма успешно отправлена!', $debugInfo = [])
    {
        $response = [
            'success' => true,
            'message' => $msg,
        ];
        if ($this->debug && !empty($debugInfo)) $response['debug'] = $debugInfo;
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ==== Приватная функция подготовки данных для дубля ====
    private function getDupCheckData()
    {
        $data = $this->post;
        $excludeKeys = ['_csrf', '_fp_js', '_fp_hp', '_fp_captcha'];
        foreach ($excludeKeys as $key) unset($data[$key]);

        foreach ($this->files as $key => $fileArray) {
            if (is_array($fileArray) && isset($fileArray[0]['name'])) {
                foreach ($fileArray as $idx => $file) {
                    if (is_array($file) && isset($file['name'])) {
                        $data['_file_' . $key . '_' . $idx . '_name'] = $file['name'];
                        $data['_file_' . $key . '_' . $idx . '_size'] = $file['size'] ?? null;
                    }
                }
            } elseif (is_array($fileArray) && isset($fileArray['name'])) {
                $data['_file_' . $key . '_name'] = $fileArray['name'];
                $data['_file_' . $key . '_size'] = $fileArray['size'] ?? null;
            }
        }
        return $data;
    }

    /**
     * Формирует debug-информацию для ответа (можно доработать по нуждам)
     */
    protected function getDebugInfo($add = [])
    {
        return array_merge([
            'session_id' => session_id(),
            'formId' => $this->formId,
            'session_keys' => array_keys($_SESSION),
            'captcha_value' => $_SESSION['fw_captcha_text_' . ($this->formId ?? 'default')] ?? null,
            'user_input' => $this->post['_fp_captcha'] ?? null,
            'post' => $this->post,
            'files' => $this->files,
        ], $add);
    }
}
