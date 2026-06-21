<?php
/**
 * CaptchaValidator — simple-captcha only. Вся логика и тексты ошибок тут!
 */
class CaptchaValidator
{
    protected $formId;
    protected $userInput;

    /**
     * @param string $formId
     * @param string|null $userInput
     */
    public function __construct($formId, $userInput)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        $this->formId = $formId ?: 'default';
        $this->userInput = $userInput;
    }

    /**
     * Проверяет капчу, автоматически вызывает fail() при ошибках.
     * Если все ок — возвращает true, чистит капчу в сессии.
     */
    public function validateOrFail()
    {
        if (empty($this->userInput)) {
            $this->fail('Введите код с картинки!');
        }
        $key = 'fw_captcha_text_' . $this->formId;
        if (!isset($_SESSION[$key]) || strtolower($this->userInput) !== strtolower($_SESSION[$key])) {
            // $this->fail('Неверно введён код с картинки! Попробуйте ещё раз.', true);
        }
        unset($_SESSION[$key]);
        return true;
    }

    /**
     * Отправляет JSON-ответ с ошибкой и завершает выполнение.
     */
    public function fail($message, $forceCaptcha = true)
    {
        $out = [
            'success' => false,
            'message' => $message,
            'errors' => [],
            'force_captcha' => $forceCaptcha
        ];
        echo json_encode($out, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
