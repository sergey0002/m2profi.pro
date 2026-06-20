<?php
/**
 * Файл: sites/xdemo2/sahmatka/inc/ProposalManager.php
 * Основной класс для управления предложением (избранным)
 */

require_once __DIR__ . '/objects_data.php';

class ProposalManager {
    private static $session_key = 'selected_objects';
    private static $storage_path = __DIR__ . '/../personaloffer/data/';

    /**
     * Инициализация сессии
     */
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION[self::$session_key])) {
            $_SESSION[self::$session_key] = [];
        }
    }

    /**
     * Получить список выбранных объектов из сессии
     */
    public static function getSelectedObjects() {
        self::init();
        return $_SESSION[self::$session_key];
    }

    /**
     * Проверить, добавлен ли объект в предложение
     */
    public static function isAdded($id) {
        self::init();
        return isset($_SESSION[self::$session_key][$id]);
    }

    /**
     * Получить количество объектов в предложении
     */
    public static function getCount() {
        self::init();
        return count($_SESSION[self::$session_key]);
    }

    /**
     * Добавить объект в предложение
     */
    public static function addObject($type, $id) {
        self::init();
        if (!isset($_SESSION[self::$session_key][$id])) {
            $_SESSION[self::$session_key][$id] = [
                'type' => $type,
                'id' => $id,
                'added_at' => date('Y-m-d H:i:s'),
                'note' => ''
            ];
            return true;
        }
        return false;
    }

    /**
     * Удалить объект из предложения
     */
    public static function removeObject($id) {
        self::init();
        if (isset($_SESSION[self::$session_key][$id])) {
            unset($_SESSION[self::$session_key][$id]);
            return true;
        }
        return false;
    }

    /**
     * Добавить заметку к объекту
     */
    public static function addNote($id, $note) {
        self::init();
        if (isset($_SESSION[self::$session_key][$id])) {
            $_SESSION[self::$session_key][$id]['note'] = $note;
            return true;
        }
        return false;
    }

    /**
     * Очистить предложение
     */
    public static function clear() {
        self::init();
        $_SESSION[self::$session_key] = [];
    }

    /**
     * Получить полные данные объекта по ID (используя реестр)
     */
    public static function getFullObjectData($id, $type = null) {
        if (!$type) {
            // Пытаемся определить тип по префиксу ID
            $parts = explode('_', $id);
            $type_prefix = $parts[0];
            $type_map = [
                'house' => 'house',
                'land' => 'land',
                'settlement' => 'settlement',
                'project' => 'project'
            ];
            $type = $type_map[$type_prefix] ?? null;
        }

        if (!$type) return null;

        return getObjectDataFromRegistry($type, $id);
    }

    /**
     * Получить все данные предложения (включая объекты с деталями)
     */
    public static function getProposalData() {
        $objects = self::getSelectedObjects();
        $full_data = [];
        
        foreach ($objects as $id => $item) {
            $details = self::getFullObjectData($id, $item['type']);
            if ($details) {
                $full_data[] = array_merge($details, [
                    'id' => $id,
                    'type' => $item['type'],
                    'note' => $item['note']
                ]);
            }
        }
        
        return [
            'objects' => $full_data
        ];
    }

    /**
     * Сохранить предложение в файл (генерация постоянной ссылки)
     */
    public static function savePermanentProposal($proposal_name = '', $manager_name = '') {
        $objects = self::getProposalData();
        if (empty($objects['objects'])) return false;

        $hash = md5(uniqid((string)rand(), true));
        $save_data = [
            'hash' => $hash,
            'proposal_name' => $proposal_name ?: ($_SESSION['proposal_name'] ?? 'Ваше предложение'),
            'created_at' => date('Y-m-d H:i:s'),
            'manager_name' => $manager_name,
            'objects' => $objects['objects']
        ];

        if (!is_dir(self::$storage_path)) {
            mkdir(self::$storage_path, 0777, true);
        }

        $file_path = self::$storage_path . $save_data['hash'] . '.json';
        if (file_put_contents($file_path, json_encode($save_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT))) {
            return $save_data['hash'];
        }

        return false;
    }

    /**
     * Загрузить данные предложения из файла по хешу
     */
    public static function loadPermanentProposal($hash) {
        $file_path = self::$storage_path . $hash . '.json';
        if (file_exists($file_path)) {
            return json_decode(file_get_contents($file_path), true);
        }
        return null;
    }
}
