<?
class fw_messages{
	
	
	
	function __construct() 
	{
        // Конструктор можно оставить пустым
    }
    
    /**
     * Формирует HTML-сообщение на основе данных формы
     * 
     * @param array $data Данные из POST (массив)
     * @param array $titles Массив заголовков полей в формате [поле] => заголовок (опционально)
     * @return string HTML-сообщение
     */
    function build_message($data, $titles = []) {
        $message = '';
        
        // Служебные поля, которые нужно пропустить
        $exclude = ['_fp_form', '_csrf', '_fp_hp', '_fp_js', '_fp_captcha', 'fwformid'];
        
        foreach ($data as $field => $value) {
            // Пропускаем служебные поля
            if (in_array($field, $exclude)) {
                continue;
            }
            
            // Определяем заголовок для поля
            $header = isset($titles[$field]) ? $titles[$field] : $field;
            
            // Форматируем значение (если это массив, преобразуем в строку)
            if (is_array($value)) {
                $formattedValue = implode(', ', $value);
            } else {
                $formattedValue = $value;
            }
            
            // Экранируем HTML-символы для безопасности
            $safeHeader = htmlspecialchars($header);
            $safeValue = htmlspecialchars($formattedValue);
            
            // Добавляем строку в сообщение
            $message .= '<b>' . $safeHeader . ':</b> ' . $safeValue . '<br/>';
        }
        
        return $message;
    }
	
	
	
}