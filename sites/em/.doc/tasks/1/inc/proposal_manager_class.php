<?php
/**
 * Класс управления предложениями (избранное)
 */
class ProposalManager {
    
    private static $session_key = 'selected_objects';
    
    /**
     * Добавление объекта к предложению
     */
    public static function addObject($type, $id) {
        $proposal_data = self::getProposalData();
        
        if (!$proposal_data) {
            $proposal_data = [
                'objects' => [],
                'created_at' => time()
            ];
        }
        
        // Проверяем, не добавлен ли уже объект
        $exists = false;
        foreach ($proposal_data['objects'] as $obj) {
            if ($obj['type'] == $type && $obj['id'] == $id) {
                $exists = true;
                break;
            }
        }
        
        if (!$exists) {
            $proposal_data['objects'][] = [
                'type' => $type,
                'id' => $id,
                'added_at' => time(),
                'note' => '' // Поле для текстовой подписи
            ];
            
            self::setProposalData($proposal_data);
            return true;
        }
        
        return false;
    }
    
    /**
     * Удаление объекта из предложения
     */
    public static function removeObject($type, $id) {
        $proposal_data = self::getProposalData();
        
        if ($proposal_data && isset($proposal_data['objects'])) {
            $proposal_data['objects'] = array_filter(
                $proposal_data['objects'], 
                function($obj) use ($type, $id) {
                    return !($obj['type'] == $type && $obj['id'] == $id);
                }
            );
            
            self::setProposalData($proposal_data);
            return true;
        }
        
        return false;
    }
    
    /**
     * Получение данных предложения
     */
    public static function getProposalData() {
        if (isset($_SESSION[self::$session_key])) {
            return json_decode($_SESSION[self::$session_key], true);
        }
        return null;
    }
    
    /**
     * Установка данных предложения
     */
    public static function setProposalData($data) {
        $_SESSION[self::$session_key] = json_encode($data);
    }
    
    /**
     * Очистка предложения
     */
    public static function clearProposal() {
        unset($_SESSION[self::$session_key]);
    }
    
    /**
     * Получение количества объектов в предложении
     */
    public static function getCount() {
        $data = self::getProposalData();
        if ($data && isset($data['objects'])) {
            return count($data['objects']);
        }
        return 0;
    }
    
    /**
     * Обновление примечания для объекта
     */
    public static function updateNote($type, $id, $note) {
        $proposal_data = self::getProposalData();
        
        if ($proposal_data && isset($proposal_data['objects'])) {
            foreach ($proposal_data['objects'] as &$obj) {
                if ($obj['type'] == $type && $obj['id'] == $id) {
                    $obj['note'] = $note;
                    self::setProposalData($proposal_data);
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * Генерация уникального хеша для постоянной ссылки
     */
    public static function generateHash($name) {
        return md5($name . time() . rand(1000, 9999));
    }
    
    /**
     * Сохранение предложения как постоянного
     */
    public static function savePermanentProposal($name) {
        $proposal_data = self::getProposalData();
        
        if (!$proposal_data) {
            return false;
        }
        
        $proposal_data['name'] = $name;
        $proposal_data['hash'] = self::generateHash($name);
        $proposal_data['created_at'] = time();
        
        // Создаем папку, если не существует
        $data_dir = 'personaloffer/data/';
        if (!is_dir($data_dir)) {
            mkdir($data_dir, 0755, true);
        }
        
        // Сохраняем в JSON файл
        $file_path = $data_dir . $proposal_data['hash'] . '.json';
        file_put_contents($file_path, json_encode($proposal_data, JSON_UNESCAPED_UNICODE));
        
        return $proposal_data['hash'];
    }
    
    /**
     * Загрузка постоянного предложения по хешу
     */
    public static function loadPermanentProposal($hash) {
        $file_path = 'personaloffer/data/' . $hash . '.json';
        
        if (file_exists($file_path)) {
            $content = file_get_contents($file_path);
            return json_decode($content, true);
        }
        
        return null;
    }
    
    /**
     * Получение метки типа объекта
     */
    public static function getObjectTypeLabel($type) {
        $labels = [
            'house' => 'Дом',
            'land' => 'Участок',
            'project' => 'Проект',
            'settlement' => 'Поселок'
        ];
        
        return $labels[$type] ?? $type;
    }
}
?>