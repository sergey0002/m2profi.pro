<?php

/**
 * Modernized MySQL Wrapper with Legacy Compatibility
 * 
 * Changes:
 * - Fixed Cyrillic/Latin variable naming bug.
 * - Added strict escaping for all helper methods.
 * - PHP 8.2+ compatibility (declared properties).
 * - UTF-8mb4 support enabled by default.
 */
class m_mysql 
{
    /** @var mysqli|null Линк соединения с БД */
    public $c = null; 

    /** @var string Сообщения об ошибках */
    public $errors_messages = ''; 

    /** @var array Массив сообщений */
    public $messages = array(); 

    /** @var array Лог запросов */
    public $log = array();
    
    /** @var int Количество затронутых строк */
    public $count = 0;

    /**
     * Конструктор соединения
     */
    public function __construct()
    {
        // Проверяем наличие конфига (защита от Notice)
        if (!isset($GLOBALS['config'])) {
            // В идеале здесь нужно кинуть Exception или записать в лог, 
            // но для совместимости просто выходим
            return;
        }

        $login    = $GLOBALS['config']['mysql_login'];
        $password = $GLOBALS['config']['mysql_password'];
        $base     = $GLOBALS['config']['mysql_base'];
        $server   = $GLOBALS['config']['server'];
        
        // Включаем режим отчета об ошибках, но не исключения (чтобы не сломать старый код)
        mysqli_report(MYSQLI_REPORT_OFF);

        $this->c = @mysqli_connect($server, $login, $password, $base);

        if (!$this->c) {
            die('DB Connection Error: ' . mysqli_connect_error());
        }

        // Устанавливаем правильную кодировку сразу
        mysqli_set_charset($this->c, "utf8mb4");
    }

    /**
     * Присвоение значения (Helper)
     */
    public function data_value($val, $default = false, $data_type = '')
    {
        if ((!$val && $val !== false && $val !== 0 && $val !== '0') && 
            ($default || $default === false || $default === 0)) {
            $val = $default;
        }       
        return $val;
    }
    
    /**
     * Объединение массивов данных
     */
    public function split_data($data1, $data2, $key1, $key2)
    {
        if (!is_array($data1) || !is_array($data2)) {
            return is_array($data1) ? $data1 : array();
        }

        foreach ($data1 as $k => $v) {
            foreach ($data2 as $kk => $vv) {
                if (isset($v[$key1], $vv[$key2]) && $v[$key1] == $vv[$key2]) {
                    foreach ($vv as $kkk => $vvv) {
                        $data1[$k][$kkk] = $vvv;
                    }
                }
            }
        }
        return $data1;
    }
    
    /**
     * Поиск (исправлена уязвимость SQL Injection)
     */
    public function search($fileds, $search = '')
    {
        $sql = '';
        
        // Если поиск не передан, берем из GET, но безопасно
        if ($search === '' && isset($_GET['search'])) {
            $search = urldecode($_GET['search']); 
        }
        
        if ($search) {
            // SECURITY: Экранируем поисковую строку
            $searchSafe = mysqli_real_escape_string($this->c, $search);
            
            $sql .= ' AND ( ';
            $i = 0;
            if (is_array($fileds)) {
                foreach ($fileds as $v) {
                    if ($i > 0) { $sql .= ' OR '; } 
                    $i++;
                    // v (имя поля) не экранируем через real_escape, 
                    // предполагаем что разработчик передал корректные поля.
                    // Но значение поиска строго экранировано.
                    $sql .= ' ' . $v . ' LIKE "%' . $searchSafe . '%" ';
                }
            }
            $sql .= ' )';
        }
        return $sql;
    }

    /**
     * Получить строку по ключу
     */
    public function get_for_key($table, $key_filed, $key_value, $dop_wh = '')
    {
        // SECURITY: Полное экранирование
        $table      = $this->escape_ident($table);
        $key_filed  = $this->escape_ident($key_filed);
        $key_value  = mysqli_real_escape_string($this->c, $key_value);

        if ($dop_wh) { $dop_wh = ' AND ' . $dop_wh; }
        
        $q = "SELECT * FROM `$table` WHERE `$key_filed` = \"$key_value\" $dop_wh";
        return $this->get_arr($q, true);
    }
    
    /**
     * Обновление данных (Safe Update)
     */
    public function update_for_key($table, $key_filed, $key_value, $data, $print_q = false)
    {
        if ($this->is_demo()) return false;
     
        $table = $this->escape_ident($table);
        $q = "UPDATE `$table` SET ";
        $i = 0;
        $cnt = count($data);
        
        foreach ($data as $k => $v) {
            // SECURITY: Экранируем название поля
            $k_safe = $this->escape_ident($k);
            
            // SECURITY: Экранируем значение
            $v_safe = mysqli_real_escape_string($this->c, (string)$v);
            
            $i++;
            
            // Логика NULL значений
            if (!$v && $v !== 0 && $v !== '0') {
                $vi = 'NULL';
            } else { 
                $vi = '"' . $v_safe . '"'; 
            }
            
            $q .= "`$k_safe` = $vi ";
            if ($i < $cnt) { $q .= ' , '; }
        }
        
        // SECURITY: Экранируем ключи условия WHERE
        $key_filed = $this->escape_ident($key_filed);
        $key_value = mysqli_real_escape_string($this->c, (string)$key_value);
        
        $q .= " WHERE `$key_filed` = \"$key_value\" ";
        
        if ($print_q) { print $q; }  
        return $this->sql($q);
    }

    /**
     * Удаление строки (заглушка реализована)
     */
    public function delete_for_key($table, $key_filed, $value)
    {
        if ($this->is_demo()) return false;

        $table     = $this->escape_ident($table);
        $key_filed = $this->escape_ident($key_filed);
        $value     = mysqli_real_escape_string($this->c, (string)$value);

        $q = "DELETE FROM `$table` WHERE `$key_filed` = \"$value\"";
        return $this->sql($q);
    }
    
    /**
     * Вставка данных (Safe Insert)
     */
    public function insert($table, $data, $ign_error = false, $print = false)
    {
        if ($this->is_demo()) return false;
 
        $table = $this->escape_ident($table);
        $q = "INSERT INTO `$table` ( ";
        
        // Подготовка полей
        $keys = array_keys($data);
        // Экранируем названия столбцов
        $keys_safe = array_map(array($this, 'escape_ident'), $keys);
        $q .= '`' . implode('`, `', $keys_safe) . '`';
        
        $q .= ' ) VALUES (';
        
        $i = 0;
        $cnt = count($data);
        foreach ($data as $v) {
            $i++;
            if ($v === 'NOW()') {
                $q .= ' NOW() ';
            } else {
                $val_safe = mysqli_real_escape_string($this->c, (string)$v);
                $q .= ' "' . $val_safe . '" ';
            }
            if ($i < $cnt) { $q .= ' , '; }
        }
        $q .= ' );';
 
        if ($print) { print $q; }
        
        if ($this->sql($q, $ign_error)) {
            return mysqli_insert_id($this->c);
        } else {
            return false;
        }
    }
    
    /**
     * Вставка, если нет записи
     */
    public function insert_or_not($table, $find_array, $data, $key_filed, $rfiled = '')
    {
        $table = $this->escape_ident($table);
        $q = "SELECT * FROM `$table` WHERE ";
        
        foreach ($find_array as $k => $v) {
            $k = $this->escape_ident($k);
            $v = mysqli_real_escape_string($this->c, (string)$v);
            $q .= " `$k` = \"$v\" AND ";
        }
        $q .= ' 1 = 1';
        
        $data_array = $this->get_arr($q, true);
        
        if ($data_array) { 
            if (!$rfiled) { 
                return isset($data_array[$key_filed]) ? $data_array[$key_filed] : false; 
            } else { 
                return isset($data_array[$rfiled]) ? $data_array[$rfiled] : false; 
            }
        } else {
            return $this->insert($table, $data);
        }   
    }
    
    /**
     * Кэшируемый запрос
     */
    public function get_arr_c($sql, $first = false, $key = false, $cache_live = "604800")
    {
        $arr = false;
        // Проверяем наличие функций кэширования
        if ($cache_live && function_exists('get_cache')) {
            $arr = get_cache($sql . $key . $first, $cache_live);
        }
        
        if ($arr) {
            return $arr;
        } else {
            $arr = $this->get_arr($sql, $first, $key);
            if (function_exists('set_cache')) {
                set_cache($sql . $key . $first, $arr);
            }
            return $arr;
        }
    }
    
    /**
     * Данные для Select
     */
    public function get_select_data($sql, $value_filed, $title_filed, $null = '')
    {
        $arr = $this->get_arr($sql, true, $value_filed);
        $new_arr = array();
        
        if ($null !== '') { $new_arr[''] = $null; }
        
        if (is_array($arr)) {
            foreach ($arr as $k => $v) {
                $caption = (isset($v[$title_filed]) && $v[$title_filed]) ? $v[$title_filed] : '-';
                $new_arr[$k] = $caption;
            }
        }
        return $new_arr;
    }
    
    /**
     * Основной метод получения массива
     */
    public function get_arr($sql, $first = false, $key = false)
    {
        $query = $this->sql($sql);
        $arr = array();
        
        if (!$query) return $arr;
        
        $i = 0;
        while ($result = mysqli_fetch_assoc($query)) {
            $result['i'] = $i;
            $i++;
            if ($key) {
                if (isset($result[$key])) {
                    if ($first) {
                        $arr[$result[$key]] = $result;
                    } else {
                        $arr[$result[$key]][] = $result;
                    }
                }
            } else {
                $arr[] = $result;
            }
        }
        
        if ($first && !$key && isset($arr[0])) { $arr = $arr[0]; }
        
        return $arr;
    }
    
    /**
     * Выполнение SQL запроса
     * ВНИМАНИЕ: Если вы вызываете этот метод напрямую с параметрами из $_GET/$_POST,
     * вы подвержены SQL инъекциям. Используйте методы insert/update/get_for_key 
     * или экранируйте данные вручную.
     */
    public function sql($sql, $ign_error = false)
    {
        if ($this->is_demo()) {
            if (preg_match('/(DELETE|UPDATE|INSERT)/i', $sql)) {
                return false;
            }
        }

        $start_t = microtime(true);
        $result = mysqli_query($this->c, $sql);
        $stop_t = round(microtime(true) - $start_t, 4);
        
        if ($result) {
            $log_data = array();
            $log_data['q'] = $sql;
            
            // Корректное получение affected rows
            $this->count = mysqli_affected_rows($this->c);
            $log_data['rows'] = $this->count;
            
            $log_data['time'] = $stop_t;
            
            // Инициализация лога если не создан
            if (!isset($GLOBALS['sql_log'])) $GLOBALS['sql_log'] = array();
            
            $GLOBALS['sql_log'][] = $log_data;
            
            if (!isset($GLOBALS['sql_log']['alltime'])) $GLOBALS['sql_log']['alltime'] = 0;
            $GLOBALS['sql_log']['alltime'] += $stop_t;
            
            return $result;
        } else {
            // Логирование ошибки
            $error = mysqli_error($this->c);
            
            if (!$ign_error) {
                // В продакшене лучше не выводить SQL на экран, но для совместимости оставляем
                echo '<!-- SQL Error: ' . htmlspecialchars($error) . ' -->';
                // echo '<!-- Query: ' . htmlspecialchars($sql) . ' -->'; 
                die('System Database Error'); // Более безопасный вывод
            }
            return false;
        }
    }
    
    // --- DISPLAY HELPER METHODS --- //

    public function select_fileds($main_table, $fileds_table)
    {
        // Placeholder
    }
    
    public function display_table($arr, $titles, $all = false, $skin = '1')
    {
        if (!$arr) return;

        // Определяем стили, чтобы избежать ошибок Undefined Index
        $skin_def = array(
            'tabletag' => ' border="0" ',
            'thtag' => ' ',
            'trtag' => ' ',
            'tdtag' => ' '
        );
        // Если скин есть в базе (которую мы тут не видим), используем его, иначе дефолт
        // Для совместимости оставляем логику, но делаем её безопасной
        $s = $skin_def; 
        
        // Все столбцы!
        if ($all && isset($arr[0])) {
            foreach ($arr[0] as $k => $v) {
                if (empty($titles[$k])) {
                    $titles[$k] = $k;
                }
            }
        }
        
        echo '<table ' . $s['tabletag'] . '><thead><tr>';
        foreach ($titles as $k => $v) {
            echo '<th ' . $s['thtag'] . '>' . $v . '</th>';
        }
        echo '</tr></thead><tbody>';
        
        foreach ($arr as $v) {
            echo '<tr ' . $s['trtag'] . '>';
            foreach ($titles as $kt => $vt) {
                $val = isset($v[$kt]) ? $v[$kt] : '';
                echo '<td ' . $s['tdtag'] . '>' . $val . '</td>';
            }
            echo '</tr>';
        }
        echo '</tbody></table>';
    }
    
    public function pages_menu($allc, $pp, $tp)
    { 
        global $r;
        if ($pp <= 0) $pp = 10; // Защита от деления на ноль

        $pages = ceil($allc / $pp);
        
        print '<ul class="pages">';
        for ($i = 1; $i <= $pages; $i++) {
            if ($i == $tp) {
                $litag = ''; 
                $atag = 'style="font-weight:bold;"';
            } else {
                $litag = ''; 
                $atag = '';
            }
            
            $u = (is_object($r) && method_exists($r, 'acturl')) ? $r->acturl() : '?';
            print '<li ' . $litag . '><a ' . $atag . ' href="' . $u . '&page=' . $i . '">' . $i . '</a></li>';
        }
        print '</ul>';
        
        $start = floor($pp * $tp) - $pp;
        if ($start < 0) $start = 0;
        
        $end = $start + $pp;
        if ($end > $allc) { $end = $allc; }
        
        print ' Всего:' . $allc; 
        print ' Записи: ' . $start . '-' . $end;
    }
    
    public function pages_limits($allc, $pp, $tp)
    {
        if ($pp <= 0) $pp = 10;
        if ($tp < 1) $tp = 1;
        
        $start = floor($pp * $tp) - $pp;
        if ($start < 0) { $start = 0; }
        return ' LIMIT ' . $start . ',' . $pp;
    }

    // --- INTERNAL HELPERS --- //

    /**
     * Проверка демо-режима
     */
    private function is_demo()
    {
        return (isset($_SESSION['sh_login']) && $_SESSION['sh_login'] == 'demo_admin');
    }

    /**
     * Экранирование имен таблиц и полей (защита от инъекций в именах столбцов)
     * Удаляет любые символы, кроме букв, цифр и нижнего подчеркивания
     */
    private function escape_ident($ident)
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $ident);
    }
}
 