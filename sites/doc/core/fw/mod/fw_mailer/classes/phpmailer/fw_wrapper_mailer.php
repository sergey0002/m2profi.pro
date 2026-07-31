<?php
//https://codernotes.ru/articles/php/otpravka-pisem-s-pomoshhyu-phpmailer.html  

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class fw_wrapper_mailer
{
    private $cnf;
    private $phpmailer;
    private $from;
    private $from_name;

    /**
     * Конструктор класса
     * @param array $cnf Конфигурационный массив с настройками почты
     */
    function __construct($cnf)
    { 
        $this->cnf = $cnf;
        $this->clean();
    }
    
    /**
     * Инициализация PHPMailer и настройка параметров подключения
     * @return bool Результат инициализации
     */
    function clean()
    {
        $cnf = $this->cnf;
        
        // Проверка обязательных параметров
        if (empty($cnf['from'])) {
            fw_log('$cnf[from] not found', 'fw_mailer::__construct');
            return false;
        }
        
        if (empty($cnf['from_name'])) {
            fw_log('$cnf[from_name] not found', 'fw_mailer::__construct');
            return false;
        }
        
        // Создаем новый экземпляр PHPMailer с включенной обработкой исключений
        $this->phpmailer = new PHPMailer(true);
        
        $this->phpmailer->CharSet = 'UTF-8';
         
        $this->from = $cnf['from'];
        $this->from_name = $cnf['from_name'];
        
        // Устанавливаем отправителя
        $this->phpmailer->setFrom($this->from, $this->from_name);        
        
        // Настройка метода отправки
        if ($cnf['method'] == 'smtp') {
            // Настройки SMTP
            $this->phpmailer->isSMTP();
            $this->phpmailer->SMTPAuth = true;
            $this->phpmailer->SMTPDebug = 0; // Отключаем отладку в production
            $this->phpmailer->isHTML(true); 
            $this->phpmailer->Host = $cnf['server'];
            $this->phpmailer->Port = $cnf['port'];
            $this->phpmailer->Username = $cnf['login'];
            $this->phpmailer->Password = $cnf['password'];
            
            // Автоматическое определение типа шифрования
            if ($cnf['port'] == 465) {
                $this->phpmailer->SMTPSecure = 'ssl';
            } elseif ($cnf['port'] == 587) {
                $this->phpmailer->SMTPSecure = 'tls';
            }
            
            fw_log('Подключение по SMTP', 'fw_mailer::__construct');
        } else {
            fw_log('Отправка через php mail()', 'fw_mailer::__construct');
        }
        
        return true;
    }

    /**
     * Сбрасывает все данные письма, оставляя только конфигурацию и данные отправителя
     * Готовит объект для отправки нового письма
     * @return bool Результат сброса
     */
    function reset()
    {
        if (!$this->phpmailer) {
            return false;
        }
        
        try {
            // Сбрасываем все получателей
            $this->phpmailer->clearAddresses();
            $this->phpmailer->clearCCs();
            $this->phpmailer->clearBCCs();
            
            // Сбрасываем вложения
            $this->phpmailer->clearAttachments();
            
            // Сбрасываем встроенные изображения
            $this->phpmailer->clearCustomHeaders();
            
            // Сбрасываем тему письма
            $this->phpmailer->Subject = '';
            
            // Сбрасываем тело письма
            $this->phpmailer->Body = '';
            $this->phpmailer->AltBody = '';
            
            // Сбрасываем ответ на адрес
            $this->phpmailer->clearReplyTos();
            
            // Убедимся, что отправитель установлен (на случай, если был сброшен)
            $this->phpmailer->setFrom($this->from, $this->from_name);
            
            fw_log('fw_mailer::reset - объект сброшен и готов к новой отправке', 'fw_mailer::reset');
            return true;
            
        } catch (Exception $e) {
            fw_log('Ошибка при сбросе объекта: ' . $e->getMessage(), 'fw_mailer::reset');
            return false;
        }
    }
    
    /**
     * Добавляет адрес получателя
     * @param string $to Email получателя
     * @param string $name Имя получателя (опционально)
     * @return bool Результат добавления
     */
    function add_addr($to, $name = '')
    {
        try {
            $this->phpmailer->addAddress($to, $name);
            return true;
        } catch (Exception $e) {
            fw_log('fw_mailer - Ошибка добавления адресата: ' . $e->getMessage(), 'fw_mailer::add_addr');
            return false;
        }
    }
    
    /**
     * Добавляет вложение из файла на сервере
     * @param string $filepath Путь к файлу
     * @return bool Результат добавления
     */
    function add_file($filepath)
    {
        if (!file_exists($filepath)) {
            fw_log('fw mailer - ATTACH FILE NOT FOUND: ' . $filepath, 'fw_mailer::add_file');
            return false;
        }
        
        try {
            $this->phpmailer->addAttachment($filepath);
            return true;
        } catch (Exception $e) {
            fw_log('fw_mailer - Ошибка добавления вложения: ' . $e->getMessage(), 'fw_mailer::add_file');
            return false;
        }
    }
    
    /**
     * Прикрепляет изображение для использования в HTML (<img src="cid:name">)
     * @param string $filepath Путь к файлу изображения
     * @return bool Результат добавления
     */
    function add_image($filepath)
    {
        if (!file_exists($filepath)) {
            fw_log('fw mailer - IMAGE FILE NOT FOUND: ' . $filepath, 'fw_mailer::add_image');
            return false;
        }
        
        $name = basename($filepath);
        try {
            $this->phpmailer->addEmbeddedImage($filepath, $name);
            return true;
        } catch (Exception $e) {
            fw_log('fw_mailer - Ошибка добавления изображения: ' . $e->getMessage(), 'fw_mailer::add_image');
            return false;
        }
    }
    
    /**
     * Прикрепляет массив файлов по URL
     * @param array $urls Массив URL файлов для прикрепления
     * @return array Ассоциативный массив с результатами обработки каждого файла
     */
    function add_files_from_urls($urls)
    {
        $results = [];
        
        // Проверяем, что передан массив
        if (!is_array($urls)) {
            fw_log('fw mailer - add_files_from_urls: передан не массив', 'fw_mailer::add_files_from_urls');
            return $results;
        }
        
        foreach ($urls as $index => $url) {
            try {
                // Проверяем, что URL не пустой
                if (empty($url)) {
                    $results[$index] = [
                        'success' => false,
                        'error' => 'Пустой URL',
                        'url' => $url
                    ];
                    fw_log('fw mailer - add_files_from_urls: пустой URL на позиции ' . $index, 'fw_mailer::add_files_from_urls');
                    continue;
                }
                
                // Проверяем корректность URL
                if (!filter_var($url, FILTER_VALIDATE_URL)) {
                    $results[$index] = [
                        'success' => false,
                        'error' => 'Некорректный URL',
                        'url' => $url
                    ];
                    fw_log('fw mailer - add_files_from_urls: некорректный URL ' . $url, 'fw_mailer::add_files_from_urls');
                    continue;
                }
                
                // Скачиваем файл во временную директорию
                $temp_file = $this->download_file_to_temp($url);
                
                if ($temp_file === false) {
                    $results[$index] = [
                        'success' => false,
                        'error' => 'Ошибка загрузки файла',
                        'url' => $url
                    ];
                    fw_log('fw mailer - add_files_from_urls: ошибка загрузки файла ' . $url, 'fw_mailer::add_files_from_urls');
                    continue;
                }
                
                // Получаем имя файла из URL
                $filename = basename(parse_url($url, PHP_URL_PATH));
                if (empty($filename)) {
                    $filename = 'file_' . $index;
                }
                
                // Добавляем файл как вложение
                $this->phpmailer->addAttachment($temp_file, $filename);
                
                $results[$index] = [
                    'success' => true,
                    'filename' => $filename,
                    'temp_path' => $temp_file,
                    'url' => $url
                ];
                
                fw_log('fw mailer - add_files_from_urls: файл добавлен ' . $url, 'fw_mailer::add_files_from_urls');
                
            } catch (Exception $e) {
                $results[$index] = [
                    'success' => false,
                    'error' => $e->getMessage(),
                    'url' => $url
                ];
                fw_log('fw mailer - add_files_from_urls: исключение ' . $e->getMessage() . ' для URL ' . $url, 'fw_mailer::add_files_from_urls');
            }
        }
        
        return $results;
    }
    
    /**
     * Скачивает файл по URL во временную директорию
     * @param string $url URL файла для скачивания
     * @return string|false Путь к временному файлу или false в случае ошибки
     */
    private function download_file_to_temp($url)
    {
        // Создаем временный файл
        $temp_file = tempnam(sys_get_temp_dir(), 'mailer_');
        
        if ($temp_file === false) {
            return false;
        }
        
        // Используем cURL для скачивания файла
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Отключаем проверку SSL для простоты
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Таймаут 30 секунд
        curl_setopt($ch, CURLOPT_USERAGENT, 'PHP Mailer Wrapper'); // Устанавливаем User-Agent
        
        $file_data = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Проверяем результат
        if ($file_data === false || !empty($error)) {
            unlink($temp_file); // Удаляем временный файл при ошибке
            fw_log('fw mailer - download_file_to_temp: ошибка cURL ' . $error, 'fw_mailer::download_file_to_temp');
            return false;
        }
        
        // Проверяем HTTP код ответа
        if ($http_code !== 200) {
            unlink($temp_file); // Удаляем временный файл при ошибке
            fw_log('fw mailer - download_file_to_temp: HTTP ошибка ' . $http_code, 'fw_mailer::download_file_to_temp');
            return false;
        }
        
        // Записываем данные в временный файл
        if (file_put_contents($temp_file, $file_data) === false) {
            unlink($temp_file); // Удаляем временный файл при ошибке
            fw_log('fw mailer - download_file_to_temp: ошибка записи файла', 'fw_mailer::download_file_to_temp');
            return false;
        }
        
        return $temp_file;
    }
    
    /**
     * Отправляет письмо одному или нескольким получателям, указанным в строке через запятую
     * @param string $recipients_string Email получателя(ей) - один адрес или несколько, разделенных запятыми
     * @param string $subject Тема письма
     * @param string $message Тело письма (HTML)
     * @return bool Результат отправки
     */
    function send($recipients_string, $subject, $message)
    {
        fw_log('ОТПРАВКА ПИСЬМА! to:' . $recipients_string . ' subject:' . $subject, 'fw_mailer::send');

        if (empty($recipients_string)) {
            fw_log('fw mailer - $recipients_string not found', 'fw_mailer::send');
            return false;
        }

        if (empty($subject)) {
            fw_log('fw mailer - $subject not found', 'fw_mailer::send');
            return false;
        }

        if (empty($message)) {
            fw_log('fw mailer - $message not found', 'fw_mailer::send');
            return false;
        }

        try {
            // Разбиваем строку на массив адресов
            // Используем explode с запятой и trim для каждого элемента
            $raw_recipients = array_map('trim', explode(',', $recipients_string));
            
            // Сбрасываем предыдущих получателей
            $this->phpmailer->clearAddresses();

            // Добавляем всех получателей
            $added_count = 0;
            foreach ($raw_recipients as $email) {
                // Пропускаем пустые значения (например, если в строке было две запятые подряд ",,")
                if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->phpmailer->addAddress($email);
                    $added_count++;
                    fw_log('fw mailer - добавлен получатель: ' . $email, 'fw_mailer::send');
                } else {
                    if (!empty($email)) { // Логируем только непустые некорректные адреса
                        fw_log('fw mailer - пропущен некорректный email: ' . $email, 'fw_mailer::send');
                    }
                }
            }

            if ($added_count === 0) {
                fw_log('fw mailer - ни один корректный email не найден', 'fw_mailer::send');
                return false;
            }

            // Тема письма
            $this->phpmailer->Subject = $subject;

            // Тело письма
            $this->phpmailer->msgHTML($message);

            // Отправляем письмо
            if ($this->phpmailer->send()) {
                fw_log('Письмо успешно отправлено ' . $added_count . ' получателям', 'fw_mailer::send');
                return true;
            } else {
                $error = $this->phpmailer->ErrorInfo;
                fw_log('Ошибка отправки письма: ' . $error, 'fw_mailer::send');
                return false;
            }

        } catch (Exception $e) {
            fw_log('Исключение при отправке письма: ' . $e->getMessage(), 'fw_mailer::send');
            return false;
        } finally {
            // Всегда сбрасываем объект после отправки
            $this->reset();
        }
    }
}
 