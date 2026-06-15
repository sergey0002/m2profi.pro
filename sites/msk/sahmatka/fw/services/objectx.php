<?php

/*
 
 
  1 в конфиге поля без перфикса!!!
  2. Если нет поля в конфиге оно игнорируется!!!! не запишется ничего 
  
  отключаем проверку конфига!  тип полей определяется по сеодержимому строка или число или все в варчар!
  
  Бекап таблицы?! главной - все поля закатываем в версию


========
если поля не изененнны всставляется все равно поле!

+ возможность сделать бекап одного свойства и восстановить одно свойство обекта из бекапа (цены, статусы )




 */

/**
 * Класс для управления произвольными свойствами объектов
 */
class fw_dataflex
{
    public $db; // Объект базы данных PDO
    public $obj_type; // Тип объекта (название основной таблицы)
    public $obj_type_key = "fw_node_id"; // Поле с первичным ключом основной таблицы
    public $fields_config = []; // Конфигурация полей
    public $where = []; // Условия WHERE
    public $order = []; // Условия ORDER BY
    public $limit = []; // Условия LIMIT
    public $errors = []; // Массив ошибок
    public $cache = []; // Кэш
    public $cache_enabled = true; // Флаг включения кэширования
    public $sql = []; // Логи SQL-запросов
    public $cf_table = "fwx_object_fields"; // Таблица для хранения произвольных полей
    public $mtable_cache = false; // Флаг использования кэширования
    public $engine_cache_table = "InnoDB"; // Движок таблицы для кэша
	public $log = [];

    /**
     * Конструктор класса
     *
     * @param PDO $db Объект базы данных
     * @param string $obj_type Тип объекта
     * @param string $cf_table Таблица конфигурации полей
     * @param string $obj_type_key Поле с первичным ключом основной таблицы
     */
    public function __construct(
        $db,
        $obj_type = "fw_nodes",
		$obj_type_key = "fw_node_id",
        $cf_table = "fwx_object_fields"
      
    ) {
        $this->db = $db;
        $this->obj_type = $obj_type;
        $this->cf_table = $cf_table;
        $this->obj_type_key = $obj_type_key;
        $this->cache_table = $cf_table . "__mcache";
        $this->cache_table_ids = $cf_table . "__mcache_ids";
		$this->log[]= 'Конструктор fw_dataflex таблица - '.$this->obj_type. ' Ключ - '.$this->obj_type_key. ' Свойства хранятся в '. $this->cf_table;	
        if ($this->mtable_cache) {
			$this->log[]= 'Кеш включен создаем таблицы кеша, если нету';
            $this->create_cache_tables(); // Создание таблиц кэша, если они не существуют
        } else {
			$this->log[]= 'Кеш отключен ';
            //$this->delete_cache_tables();
        }
    }

    /**
     * Выполняет SQL-запрос с подготовкой, выполнением и логированием
     *
     * @param string $sql SQL-запрос
     * @param array $params Параметры для подготовленного запроса
     * @param string $method Название метода, выполняющего запрос
     * @return mixed Возвращает результат выполнения запроса или false в случае ошибки
     */
    private function executeSql($sql, $params = [], $method = "")
    {
        try {
            // Запись времени начала выполнения запроса
            $start_time = microtime(true);
            // Подготовка запроса
            $stmt = $this->db->prepare($sql);
            // Выполнение запроса
            $result = $stmt->execute($params);
            // Запись времени окончания выполнения запроса
            $end_time = microtime(true);

            // Логирование выполнения SQL-запроса
            $this->sql[] = [
                "sql" => $sql,
                "params" => json_encode($params),
                "time" => round($end_time - $start_time, 3),
                "method" => $method,
                "rowCount" => $stmt->rowCount(),
                "ok" => 1,
            ];
            return $stmt;
        } catch (Exception $e) {
            // Логирование ошибки выполнения SQL-запроса
            $error = $e->getMessage();
            $this->sql[] = [
                "sql" => $sql,
                "params" => json_encode($params),
                "method" => $method,
                "error" => $error,
                "ok" => 0,
            ];
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Устанавливает произвольное поле (КОНФИГ ПОЛЕЙ в памяти)
     *
     * @param string $field_name Название поля
     * @param string $data_type Тип данных
	   @param boolean $many может ли поле иметь множество значений
     * @return bool Возвращает true в случае успешного выполнения, иначе false
     */
    public function setUnified($field_name, $data_type, $many = false)
    {
		
		$this->log[]= 'setUnified установлено поле   '.$field_name. ' Тип - '.$data_type. ' Множественное '. $many;
        if (empty($field_name) || empty($data_type)) {
            $this->errors[] = "Invalid arguments for setUnified()";
            return false;
        }

        $field_column = $this->getColumnByDataType($data_type);
        if (!$field_column) {
            $this->errors[] = "Invalid data type for setUnified()";
            return false;
        }

        $this->fields_config[$field_name] = [
            "data_type" => $data_type,
            "field_column" => $field_column,
            "many" => $many,
        ];

        return true;
    }

    ####################### КЕШИРОВАНИЕ В MYSQL ТАБЛИЦЕ

    /**
     * Создает таблицы для кеширования результатов, если они не существуют
     *
     * @return bool Возвращает true, если таблицы успешно созданы или уже существуют, иначе false
     */
    private function create_cache_tables()
    {
        $sql1 = "
            CREATE TABLE IF NOT EXISTS {$this->cache_table} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                hash VARCHAR(255),
                result LONGTEXT
            ) ENGINE={$this->engine_cache_table};
        ";

        $sql2 = "
            CREATE TABLE IF NOT EXISTS {$this->cache_table_ids} (
                id INT AUTO_INCREMENT PRIMARY KEY,
                cache_id INT,
                obj_type VARCHAR(255),
                obj_key VARCHAR(255),
                FOREIGN KEY (cache_id) REFERENCES {$this->cache_table}(id) ON DELETE CASCADE
            ) ENGINE={$this->engine_cache_table};
        ";

        return $this->executeSql($sql1, [], __METHOD__) &&
            $this->executeSql($sql2, [], __METHOD__);
    }

    /**
     * Сохраняет результат запроса в таблицу кеша
     *
     * @param string $hash Хеш запроса
     * @param array $result Результат запроса
     * @param array $obj_keys Ключи объектов для идентификации
     * @return bool Возвращает true в случае успешного кеширования, иначе false
     */
    private function set_cache_result($hash, $result, $obj_keys)
    {
        if (!$this->mtable_cache) {
            return false;
        }
        try {
            // Преобразование результата запроса в JSON
            $json_result = json_encode($result);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(
                    "JSON encoding error: " . json_last_error_msg()
                );
            }

            // Вставка или обновление результата в таблице кеша
            $sql = "REPLACE INTO {$this->cache_table} (hash, result) VALUES (?, ?)";
            $stmt = $this->executeSql($sql, [$hash, $json_result], __METHOD__);
            if ($stmt) {
                $cache_id = $this->db->lastInsertId();
                // Связывание результата кеша с ключами объектов
                foreach ($obj_keys as $obj_key) {
                    $sql = "INSERT INTO {$this->cache_table_ids} (cache_id, obj_type, obj_key) VALUES (?, ?, ?)";
                    $this->executeSql(
                        $sql,
                        [$cache_id, $this->obj_type, $obj_key],
                        __METHOD__
                    );
                }
                return true;
            }
            return false;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Получает результат запроса из таблицы кеша
     *
     * @param string $hash Хеш запроса
     * @return mixed Возвращает результат запроса или false в случае ошибки
     */
    private function get_cache_result($hash)
    {
        if (!$this->mtable_cache) {
            return false;
        }
        try {
            // Получение результата из таблицы кеша по хешу
            $sql = "SELECT result FROM {$this->cache_table} WHERE hash = ?";
            $stmt = $this->executeSql($sql, [$hash], __METHOD__);
            if ($stmt && ($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
                return json_decode($row["result"], true);
            }
            return false;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    ####################### ФИНАЛ - КЕШИРОВАНИЕ В MYSQL ТАБЛИЦЕ

    ###################### УСЛОВИЯ ЗАПРОСА

    /**
     * Сбрасывает условия SQL запроса
     */
    function reset()
    {
        $this->limit = [];
        $this->order = [];
        $this->where = [];
    }

    /**
     * Добавляет условие WHERE
     *
     * @param string $field Название поля
     * @param string $op Оператор сравнения
     * @param mixed $value Значение для сравнения
     * @param bool $nosql Флаг, указывающий на SQL выражение
     * @return bool Возвращает true в случае успешного выполнения, иначе false
     */
    public function where($field, $op, $value, $nosql = false)
    {
        if (empty($field) || empty($op) || $value === null) {
            $this->errors[] = "Invalid arguments for where()";
            return false;
        }

        $this->where[] = [
            "fieldname" => $field,
            "op" => $op,
            "value" => $value,
            "sqvalue" => $nosql,
        ];

        return true;
    }

    /**
     * Добавляет условие ORDER BY
     *
     * @param string $field Название поля
     * @param string $direction Направление сортировки (asc|desc)
     * @return bool Возвращает true в случае успешного выполнения, иначе false
     */
    public function order($field, $direction = "asc")
    {
        if (
            empty($field) ||
            !in_array(strtolower($direction), ["asc", "desc"])
        ) {
            $this->errors[] = "Invalid arguments for order()";
            return false;
        }

        $this->order[] = [
            "field" => $field,
            "direction" => $direction,
        ];

        return true;
    }

    /**
     * Добавляет ограничение LIMIT
     *
     * @param int $start Начальное значение
     * @param int $end Конечное значение
     * @return bool Возвращает true в случае успешного выполнения, иначе false
     */
    public function limit($start, $end)
    {
        if (!is_int($start) || !is_int($end)) {
            $this->errors[] = "Invalid arguments for limit()";
            return false;
        }

        $this->limit = [
            "start" => $start,
            "end" => $end,
        ];

        return true;
    }

    /**
     * Получает данные из базы данных с использованием кеширования
     *
     * @param array $where Массив условий WHERE
     * @param array $order Массив условий ORDER BY
     * @param array $limit Массив условий LIMIT
     * @param bool $no_main_table Флаг для исключения основной таблицы
     * @return array|false Массив данных или false в случае ошибки
     */
    public function get(
        $where = [],
        $order = [],
        $limit = [],
        $no_main_table = false
    ) {
        $where_clause_main = []; // Массив условий WHERE для основной таблицы
        $params = []; // Массив параметров для подготовленного SQL-запроса

        // Формирование условий WHERE из переданных параметров или из состояния объекта
        $where_conditions = !empty($where) ? $where : $this->where; // Используем переданные условия или условия из состояния объекта
        foreach ($where_conditions as $condition) {
            $field = $condition["fieldname"]; // Название поля
            $op = isset($condition["op"]) ? $condition["op"] : "="; // Оператор сравнения (по умолчанию '=')
            $value = $condition["value"]; // Значение для сравнения
            $sqvalue = isset($condition["sqvalue"])
                ? $condition["sqvalue"]
                : false; // Флаг, указывающий на SQL выражение

            if (strpos($field, "cmf__") === 0) {
                // Условие для таблицы кастомных полей
                $field_name = substr($field, 5); // Убираем префикс 'cmf__'
                if (isset($this->fields_config[$field_name])) {
                    $field_column =
                        $this->fields_config[$field_name]["field_column"];
                    if ($sqvalue) {
                        $where_clause_main[] = "(f.field_name = ? AND fv.$field_column $op $value)";
                        $params[] = $field_name;
                    } else {
                        $where_clause_main[] = "(f.field_name = ? AND fv.$field_column $op ?)";
                        $params[] = $field_name;
                        $params[] = $value;
                    }
                }
            } else {
                // Условие для основной таблицы
                if ($sqvalue) {
                    $where_clause_main[] = "m.$field $op $value";
                } else {
                    $where_clause_main[] = "m.$field $op ?";
                    $params[] = $value;
                }
            }
        }

        // Формирование условий ORDER BY из переданных параметров или из состояния объекта
        $order_conditions = !empty($order) ? $order : $this->order; // Используем переданные условия сортировки или условия из состояния объекта
        $order_clause = []; // Массив условий ORDER BY
        foreach ($order_conditions as $ord) {
            $field = $ord["field"]; // Название поля
            $direction = isset($ord["direction"])
                ? strtoupper($ord["direction"])
                : "ASC"; // Направление сортировки (по умолчанию 'ASC')
            if (strpos($field, "cmf__") === 0) {
                $field_name = substr($field, 5); // Убираем префикс 'cmf__' для кастомных полей
                if (isset($this->fields_config[$field_name])) {
                    $field_column =
                        $this->fields_config[$field_name]["field_column"];
                    $order_clause[] = "fv.$field_column $direction"; // Добавляем условие сортировки для таблицы кастомных полей
                }
            } else {
                $order_clause[] = "m.$field $direction"; // Добавляем условие сортировки для основной таблицы
            }
        }

        // Формирование условия LIMIT из переданных параметров или из состояния объекта
        $limit_clause = ""; // Условие LIMIT
        $limit_conditions = !empty($limit) ? $limit : $this->limit; // Используем переданные ограничения или ограничения из состояния объекта
        if (
            isset($limit_conditions["start"]) &&
            isset($limit_conditions["end"])
        ) {
            $limit_clause = "LIMIT {$limit_conditions["start"]}, {$limit_conditions["end"]}"; // Формируем условие LIMIT
        }

        // Получение сущностей, удовлетворяющих условиям поиска
        $entity_sql = "SELECT m.*
            FROM {$this->obj_type} m
            LEFT JOIN {$this->cf_table} f ON m.{$this->obj_type_key} = f.obj_key
            LEFT JOIN {$this->cf_table} fv ON f.ofid = fv.ofid
            WHERE f.obj_type = '{$this->obj_type}' AND f.actual_value = TRUE";

        if (!empty($where_clause_main)) {
            $entity_sql .= " AND " . implode(" AND ", $where_clause_main);
        }

        $entity_sql .= " GROUP BY m.{$this->obj_type_key}";

        if (!empty($order_clause)) {
            $entity_sql .= " ORDER BY " . implode(", ", $order_clause);
        }

        if ($limit_clause) {
            $entity_sql .= " $limit_clause";
        }

        // Выполнение запроса для получения сущностей
        try {
            $entity_stmt = $this->executeSql($entity_sql, $params, __METHOD__);
            if ($entity_stmt) {
                $entities = $entity_stmt->fetchAll(PDO::FETCH_ASSOC); // Получаем сущности
            } else {
                return false;
            }
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }

        // Получение всех кастомных полей для найденных сущностей
        if (!empty($entities)) {
            $entity_ids = array_column($entities, $this->obj_type_key);
            $placeholders = implode(
                ",",
                array_fill(0, count($entity_ids), "?")
            );

            $fields_sql = "SELECT f.obj_key, f.field_name, fv.*
                       FROM {$this->cf_table} f
                       LEFT JOIN {$this->cf_table} fv ON f.ofid = fv.ofid
                       WHERE f.obj_type = '{$this->obj_type}' AND f.actual_value = TRUE
                       AND f.obj_key IN ($placeholders)";

            try {
                $fields_stmt = $this->executeSql(
                    $fields_sql,
                    $entity_ids,
                    __METHOD__
                );
                if ($fields_stmt) {
                    $fields = $fields_stmt->fetchAll(PDO::FETCH_ASSOC); // Получаем кастомные поля
                } else {
                    return false;
                }
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
                return false;
            }

            // Обработка результатов
            $processed_results = [];
            foreach ($entities as $entity) {
                $obj_key = $entity[$this->obj_type_key];
                $processed_results[$obj_key] = $entity;
                $processed_results[$obj_key]["custom_fields"] = [];
            }

            foreach ($fields as $field) {
                $obj_key = $field["obj_key"];
                $field_name = "cmf__" . $field["field_name"];
                $field_column =
                    $this->fields_config[$field["field_name"]]["field_column"];
                $processed_results[$obj_key]["custom_fields"][$field_name][] =
                    $field[$field_column];
            }

            return array_values($processed_results);
        }

        return false;
    }

    /**
     * Обрабатывает результаты запроса, разбивая множественные значения полей на массивы
     *
     * @param array $results Результаты запроса
     * @return array Обработанные результаты
     */
    private function processResults($results)
    {
        //print '<pre>';

        // print_r($results);
        /// print '</pre>';
        $processed = [];
        foreach ($results as $row) {
            $processed_row = [];

            // Перебираем все поля строки результата
            foreach ($row as $field_name => $value) {
                if (strpos($field_name, "cmf__") === 0) {
                    // Если поле имеет префикс cmf__, это кастомное поле
                    $clean_field_name = substr($field_name, 5);
                    if ($value) {
                        $values = explode(",", $value);
                        $processed_row[$field_name] = [];
                        foreach ($values as $val) {
                            list($num, $actual_value) = explode("|", $val);
                            $processed_row[$field_name][$num] = [
                                "value" => $actual_value,
                                "user_editor_id" => isset(
                                    $row["cdf__user_editor_id"]
                                )
                                    ? $row["cdf__user_editor_id"]
                                    : null,
                                "edit_date_time" => isset(
                                    $row["cdf__edit_date_time"]
                                )
                                    ? $row["cdf__edit_date_time"]
                                    : null,
                                "edit_object_session" => isset(
                                    $row["cdf__edit_object_session"]
                                )
                                    ? $row["cdf__edit_object_session"]
                                    : null,
                                "mass_object_session" => isset(
                                    $row["cdf__mass_object_session"]
                                )
                                    ? $row["cdf__mass_object_session"]
                                    : null,
                                "num" => $num,
                                "actual_value" => isset(
                                    $row["cdf__actual_value"]
                                )
                                    ? $row["cdf__actual_value"]
                                    : null,
                            ];
                        }
                    } else {
                        $processed_row[$field_name] = [];
                    }
                } else {
                    // Если поле не имеет префикса cmf__, это поле основной таблицы
                    $processed_row[$field_name] = $value;
                }
            }
            $processed[] = $processed_row;
        }
        return $processed;
    }

    /**
     * Денормализует данные, создавая таблицу с плоской структурой для ускорения запросов
     *
     * @return bool Возвращает true в случае успешного выполнения, иначе false
     */
    public function denormalization()
    {
        try {
            // Проверка на активную транзакцию
            if (!$this->db->inTransaction()) {
                $this->db->beginTransaction();
            }

            // Получение всех уникальных полей для данного obj_type
            $sql = "SELECT DISTINCT field_name, 
                        CASE 
                            WHEN field_value_varchar IS NOT NULL THEN 'varchar'
                            WHEN field_value_int IS NOT NULL THEN 'int'
                            WHEN field_value_date IS NOT NULL THEN 'date'
                            WHEN field_value_float IS NOT NULL THEN 'float'
                        END AS data_type
                    FROM {$this->cf_table}
                    WHERE obj_type = ?";
            $stmt = $this->executeSql($sql, [$this->obj_type], __METHOD__);
            $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Уникальные имена столбцов
            $unique_fields = [];
            foreach ($fields as $field) {
                if (!isset($unique_fields[$field["field_name"]])) {
                    $unique_fields[$field["field_name"]] = $field["data_type"];
                }
            }

            // Создание денормализованной таблицы
            $field_defs = [];
            foreach ($unique_fields as $field_name => $data_type) {
                $field_column = $this->getColumnByDataType($data_type);
                $field_defs[] = "`$field_name` {$this->getSQLDataType(
                    $data_type
                )} DEFAULT NULL";
            }

            $denormalized_table = "{$this->obj_type}_denormalized";

            $sql = "DROP TABLE IF EXISTS $denormalized_table";
            $this->executeSql($sql, [], __METHOD__);

            $sql =
                "
                CREATE TABLE $denormalized_table (
                    obj_key VARCHAR(255) NOT NULL,
                    " .
                implode(", ", $field_defs) .
                ",
                    PRIMARY KEY (obj_key)
                ) ENGINE=InnoDB
            ";
            $this->executeSql($sql, [], __METHOD__);

            // Заполнение денормализованной таблицы
            $insert_fields = array_merge(
                ["obj_key"],
                array_keys($unique_fields)
            );
            $insert_placeholders = array_fill(0, count($insert_fields), "?");

            $subquery_parts = [];
            foreach ($unique_fields as $field_name => $data_type) {
                $field_column = $this->getColumnByDataType($data_type);
                $subquery_parts[] = "MAX(CASE WHEN field_name = '$field_name' THEN $field_column ELSE NULL END) AS `$field_name`";
            }

            $subquery = implode(", ", $subquery_parts);

            $sql = "
                SELECT obj_key, $subquery
                FROM {$this->cf_table}
                WHERE obj_type = ? AND actual_value = TRUE
                GROUP BY obj_key
            ";

            $stmt = $this->executeSql($sql, [$this->obj_type], __METHOD__);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results as $row) {
                $values = [];
                foreach ($insert_fields as $field) {
                    $values[] = $row[$field] ?? null;
                }
                $sql =
                    "INSERT INTO $denormalized_table (" .
                    implode(", ", $insert_fields) .
                    ") VALUES (" .
                    implode(", ", $insert_placeholders) .
                    ")";
                $this->executeSql($sql, $values, __METHOD__);
            }

            // Фиксация транзакции
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            // Откат транзакции в случае ошибки
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Возвращает соответствующий тип данных SQL для указанного типа данных
     *
     * @param string $data_type Тип данных
     * @return string SQL тип данных
     * @throws Exception Исключение в случае неподдерживаемого типа данных
     */
    private function getSQLDataType($data_type)
    {
        switch ($data_type) {
            case "varchar":
                return "VARCHAR(255)";
            case "int":
                return "INT";
            case "float":
                return "FLOAT";
            case "date":
                return "DATE";
            default:
                throw new Exception("Unsupported data type: $data_type");
        }
    }

    /**
     * Вставляет или обновляет данные
     *
     * @param array $data Данные для вставки или обновления
     * @param int|null $id Идентификатор записи
     * @param int|null $edit_object_session Сессия редактирования объекта
     * @param int|null $mass_object_session Сессия массового редактирования
     * @param bool $no_main_table Флаг для обработки только произвольных полей
     * @param bool $reset_many_num Флаг для сброса предыдущих значений множественных полей  $this->
     * @return bool Возвращает true в случае успешного выполнения, иначе false
     */
    public function up(
        $data,
        $id = null,
        $edit_object_session = null,
        $mass_object_session = null,
        $reset_many_num = false,
        $no_main_table = false
    ) {
        // Проверка на наличие данных и корректность их формата
        if (empty($data) || !is_array($data)) {
            $this->errors[] = "Invalid data for up()";
            return false;
        }
		else
		{
			$this->log[]='UP Обновление данных - обект id:'.$id; 
			$this->log[]=$data;
		}

        $main_data = []; // Массив для хранения основных данных
        $custom_data = []; // Массив для хранения данных произвольных полей

        // Разделение данных на основные и произвольные поля
        foreach ($data as $field => $value) {
            if (strpos($field, "cmf__") === 0) {
                // Если поле является кастомным, убираем префикс и обрабатываем его отдельно
                $field_name = substr($field, 5);
                if (is_array($value)) {
                    foreach ($value as $num => $sub_value) {
                        if ($sub_value === false) {
                            continue;
                        }
                        $custom_data[$field_name][$num] = $sub_value;
                    }
                } else {
                    if ($value === false) {
                        continue;
                    }
                    $custom_data[$field_name][1] = $value;
                }
            } elseif (!$no_main_table) {
                // Если поле является основным, добавляем его в основной массив данных
                $main_data[$field] = $value;
            }
        }

        // Генерация случайного значения для сессии редактирования объекта, если не указано
        if ($edit_object_session === null) {
            $edit_object_session = rand(1, 2000000000);
        }

        // Генерация случайного значения для сессии массового редактирования, если не указано
        if ($mass_object_session === null) {
            $mass_object_session = rand(1, 2000000000);
        }

        try {
            // Начало транзакции
            $this->db->beginTransaction();

            if ($id) {
                // Обновление существующей записи, если указан идентификатор
                if (!empty($main_data)) {
                    $set_clause = [];
                    $params = [];
                    foreach ($main_data as $field => $value) {
                        $set_clause[] = "$field = ?";
                        $params[] = $value;
                    }
                    $params[] = $id;
                    $sql =
                        "UPDATE {$this->obj_type} SET " .
                        implode(", ", $set_clause) .
                        " WHERE {$this->obj_type_key} = ?";
                    $this->executeSql($sql, $params, __METHOD__);
                }
            } else {
                // Вставка новой записи, если идентификатор не указан
                if (!empty($main_data)) {
                    $fields = array_keys($main_data);
                    $placeholders = array_fill(0, count($fields), "?");
                    $params = array_values($main_data);
                    $sql =
                        "INSERT INTO {$this->obj_type} (" .
                        implode(", ", $fields) .
                        ") VALUES (" .
                        implode(", ", $placeholders) .
                        ")";
                    $this->executeSql($sql, $params, __METHOD__);
                    $id = $this->db->lastInsertId();
                }
            }

            // Удаление связанных с объектом записей из кеша, если используется кеширование
            if ($this->mtable_cache) {
                $sql = "DELETE c FROM {$this->cache_table} c
                    JOIN {$this->cache_table_ids} ci ON c.id = ci.cache_id
                    WHERE ci.obj_type = ? AND ci.obj_key = ?";
                $this->executeSql($sql, [$this->obj_type, $id], __METHOD__);
            }

            // Получаем текущие значения полей для сравнения
            $current_values = $this->get([
                [
                    "fieldname" => $this->obj_type_key,
                    "op" => "=",
                    "value" => $id,
                ],
            ]);
            if ($current_values) {
                $current_values = $current_values[0]; // Предполагается, что результат выборки будет в виде массива массивов
            } else {
                $current_values = [];
            }

            foreach ($custom_data as $field_name => $values) {
                if ($reset_many_num) {
                    $this->resetFieldValues($id, $field_name);
                }
                foreach ($values as $num => $value) {
                    $config = $this->fields_config[$field_name];

                    // Проверяем, изменилось ли значение поля
                    if (
                        $this->isFieldChanged(
                            $current_values,
                            "cmf__" . $field_name,
                            $value,
                            $num
                        )
                    ) {
                        $this->insertOrUpdateField(
                            $id,
                            $field_name,
                            $value,
                            $config["field_column"],
                            $num,
                            $edit_object_session,
                            $mass_object_session
                        );
                    }
                }
            }

            // Фиксация транзакции
            $this->db->commit();
        } catch (Exception $e) {
            // Откат транзакции в случае ошибки
            $this->db->rollBack();
            $this->errors[] = $e->getMessage();
            return false;
        }

        return true;
    }

    /**
     * Проверяет, изменилось ли значение поля
     *
     * @param array $current_values Текущие значения полей
     * @param string $field_name Название поля
     * @param mixed $new_value Новое значение поля
     * @param int $num Номер значения для множественных полей
     * @return bool Возвращает true, если значение изменилось, иначе false
     */
    private function isFieldChanged(
        $current_values,
        $field_name,
        $new_value,
        $num
    ) {
        if (isset($current_values[$field_name][$num])) {
            // Сравниваем текущее значение с новым
            return $current_values[$field_name][$num]["value"] !== $new_value;
        }
        return true; // Если текущее значение отсутствует, считаем, что значение изменилось
    }

    /**
     * Вставляет или обновляет значение поля
     *
     * @param int $id Идентификатор записи
     * @param string $field_name Название поля
     * @param mixed $value Значение поля
     * @param string $field_column Имя столбца для значения
     * @param int $num Номер значения для множественных полей
     * @param int $edit_object_session Сессия редактирования объекта
     * @param int $mass_object_session Сессия массового редактирования actual_value
     */
    private function insertOrUpdateField(
        $id,
        $field_name,
        $value,
        $field_column,
        $num,
        $edit_object_session,
        $mass_object_session
    ) {
        // Обновление предыдущих значений поля, если они существуют
        $sql = "UPDATE {$this->cf_table} SET actual_value = FALSE WHERE obj_type = ? AND obj_key = ? AND field_name = ? AND num = ?";
        $this->executeSql(
            $sql,
            [$this->obj_type, $id, $field_name, $num],
            __METHOD__
        );

        // Вставка нового значения поля
        $sql = "
        INSERT INTO {$this->cf_table} (obj_type, obj_key, field_name, $field_column, user_editor_id, edit_date_time, edit_object_session, mass_object_session, num, actual_value)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE)
    ";
        $user_editor_id = 1; // Предполагается, что ID пользователя равен 1
        $current_time = date("Y-m-d H:i:s"); // Текущее время
        $params = [
            $this->obj_type,
            $id,
            $field_name,
            $value,
            $user_editor_id,
            $current_time,
            $edit_object_session,
            $mass_object_session,
            $num,
        ];
        $this->executeSql($sql, $params, __METHOD__);
    }

    /**
     * Помечает значения произвольного поля для объекта как неактуальные
     *
     * @param int $obj_key Ключ объекта
     * @param string $field_name Имя поля
     * @return bool Возвращает true в случае успешного выполнения, иначе false
     */
    private function resetFieldValues($obj_key, $field_name)
    {
        $sql = "UPDATE {$this->cf_table} SET actual_value = FALSE WHERE obj_type = ? AND obj_key = ? AND field_name = ?";
        return $this->executeSql(
            $sql,
            [$this->obj_type, $obj_key, $field_name],
            __METHOD__
        );
    }

    /**
     * Возвращает имя колонки для данного типа данных
     *
     * @param string $data_type Тип данных
     * @return string Имя колонки для данного типа данных
     * @throws Exception Исключение в случае неподдерживаемого типа данных
     */
    private function getColumnByDataType($data_type)
    {
        switch ($data_type) {
            case "varchar":
                return "field_value_varchar";
            case "int":
                return "field_value_int";
            case "float":
                return "field_value_float";
            case "date":
                return "field_value_date";
            default:
                throw new Exception("Unsupported data type: $data_type");
        }
    }

    /**
     * Возвращает массив ошибок
     *
     * @return array Массив ошибок
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Включает кеширование
     */
    public function enableCache()
    {
        $this->cache_enabled = true;
    }

    /**
     * Отключает кеширование
     */
    public function disableCache()
    {
        $this->cache_enabled = false;
    }

    /**
     * Очищает кеш
     */
    public function clearCache()
    {
        $this->cache = [];
    }

    /**
     * Удаляет таблицу, если она существует, и создает таблицу $cf_table
     * с индексами для оптимальной работы и типом таблицы InnoDB
     *
     * @return bool Возвращает true в случае успешного выполнения, иначе false
     */
    public function install()
    {
        try {
            $sql = "DROP TABLE IF EXISTS {$this->cf_table}";
            $this->executeSql($sql, [], __METHOD__);

            $sql = "
                CREATE TABLE IF NOT EXISTS {$this->cf_table} (
                    ofid INT AUTO_INCREMENT PRIMARY KEY,
                    obj_type VARCHAR(255) NOT NULL,
                    obj_key VARCHAR(255) NOT NULL,
                    field_name VARCHAR(255) NOT NULL,
                    field_value_varchar VARCHAR(255) DEFAULT NULL,
                    field_value_int INT DEFAULT NULL,
                    field_value_date DATE DEFAULT NULL,
                    field_value_float FLOAT DEFAULT NULL,
                    field_value_text TEXT DEFAULT NULL,
                    user_editor_id INT NOT NULL,
                    edit_date_time DATETIME NOT NULL,
                    edit_object_session INT NOT NULL,
                    mass_object_session INT DEFAULT NULL,
                    num INT NOT NULL,
                    actual_value BOOLEAN NOT NULL DEFAULT TRUE,
                    INDEX (obj_type),
                    INDEX (obj_key),
                    INDEX (field_name),
                    INDEX (field_value_varchar),
                    INDEX (field_value_int),
                    INDEX (field_value_date),
                    INDEX (field_value_float),
                    INDEX (edit_date_time),
                    INDEX (edit_object_session),
                    INDEX (mass_object_session),
                    INDEX (num),
                    INDEX (actual_value)
                ) ENGINE=InnoDB
            ";

            return $this->executeSql($sql, [], __METHOD__);
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Обновляет множество свойств объектов, соответствующих условию this->where
     *
     * @param array $data Ассоциативный массив свойств и их значений для обновления
     * @param int|null $edit_object_session Сессия редактирования объекта
     * @param int|null $mass_object_session Сессия массового редактирования
     * @param bool $reset_many_num Флаг для сброса предыдущих значений множественных полей
     * @return bool Возвращает true в случае успешного выполнения, иначе false
     */
    public function up_many(
        $data,
        $edit_object_session = null,
        $mass_object_session = null,
        $reset_many_num = true
    ) {
        if (empty($data) || !is_array($data)) {
            $this->errors[] = "Invalid data for up_many()";
            return false;
        }

        if ($edit_object_session === null) {
            $edit_object_session = rand(1, PHP_INT_MAX);
        }

        if ($mass_object_session === null) {
            $mass_object_session = rand(1, PHP_INT_MAX);
        }

        $where_clause = [];
        $params = [];
        foreach ($this->where as $condition) {
            $field = $condition["fieldname"];
            $op = $condition["op"] ?? "=";
            $value = $condition["value"];
            $sqvalue = $condition["sqvalue"] ?? false;

            if ($sqvalue) {
                $where_clause[] = "$field $op $value";
            } else {
                $where_clause[] = "$field $op ?";
                $params[] = $value;
            }
        }

        if (empty($where_clause)) {
            $this->errors[] = "No conditions specified for up_many()";
            return false;
        }

        try {
            $this->db->beginTransaction();

            $sql =
                "SELECT DISTINCT obj_key FROM {$this->cf_table} WHERE " .
                implode(" AND ", $where_clause);
            $stmt = $this->executeSql($sql, $params, __METHOD__);
            if ($stmt) {
                $obj_keys = $stmt->fetchAll(PDO::FETCH_COLUMN);
            } else {
                $obj_keys = false;
            }

            if (!$obj_keys) {
                $this->errors[] =
                    "No records found for the specified conditions";
                $this->db->rollBack();
                return false;
            }

            if ($this->mtable_cache) {
                $placeholders = str_repeat("?,", count($obj_keys) - 1) . "?";
                $sql = "DELETE c FROM {$this->cache_table} c
                        JOIN {$this->cache_table_ids} ci ON c.id = ci.cache_id
                        WHERE ci.obj_type = ? AND ci.obj_key IN ($placeholders)";
                $this->executeSql(
                    $sql,
                    array_merge([$this->obj_type], $obj_keys),
                    __METHOD__
                );
            }

            foreach ($obj_keys as $obj_key) {
                foreach ($data as $field_name => $values) {
                    if (isset($this->fields_config[$field_name])) {
                        if ($reset_many_num) {
                            $this->resetFieldValues($obj_key, $field_name);
                        }
                        if (is_array($values)) {
                            foreach ($values as $num => $value) {
                                $config = $this->fields_config[$field_name];
                                $this->insertOrUpdateField(
                                    $obj_key,
                                    $field_name,
                                    $value,
                                    $config["field_column"],
                                    $num,
                                    $edit_object_session,
                                    $mass_object_session
                                );
                            }
                        } else {
                            $config = $this->fields_config[$field_name];
                            $this->insertOrUpdateField(
                                $obj_key,
                                $field_name,
                                $values,
                                $config["field_column"],
                                1,
                                $edit_object_session,
                                $mass_object_session
                            );
                        }
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Получает все доступные поля для данного obj_type из таблицы $cf_table
     * и записывает их в fields_config
     *
     * @param string $obj_type Ключ таблицы
     * @return bool Возвращает true в случае успешного выполнения, иначе false
     */
    public function get_all_custom_fields()
    {
        $obj_type = $this->obj_type;
        try {
            $sql = "SELECT DISTINCT field_name, 
                    CASE 
                        WHEN field_value_varchar IS NOT NULL THEN 'varchar'
                        WHEN field_value_int IS NOT NULL THEN 'int'
                        WHEN field_value_date IS NOT NULL THEN 'date'
                        WHEN field_value_float IS NOT NULL THEN 'float'
                        WHEN field_value_text IS NOT NULL THEN 'text'
                    END AS data_type
                    FROM {$this->cf_table} 
                    WHERE obj_type = ?";

            $stmt = $this->executeSql($sql, [$obj_type], __METHOD__);
            if ($stmt) {
                $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $fields = false;
            }

            if (!$fields) {
                $this->errors[] = "No custom fields found for obj_type {$obj_type}";
                return false;
            }

            foreach ($fields as $field) {
                $this->fields_config[$field["field_name"]] = [
                    "data_type" => $field["data_type"],
                    "field_column" => $this->getColumnByDataType(
                        $field["data_type"]
                    ),
                ];
            }

            return true;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Выполняет get_all_custom_fields и формирует $x запросов с случайными выборками $where.
     * Возвращает лог, содержащий выборки, время выборки, сколько запросов входит в выборку,
     * время выполнения каждого запроса и выводит в формате HTML.
     *
     * @param string $obj_type Ключ таблицы.
     * @param int $x Количество запросов.
     * @return string HTML-лог с результатами выборок.
     */
    public function test_get($obj_type, $x)
    {
        try {
            // Получаем все доступные поля для данного obj_type.
            $this->get_all_custom_fields();

            // Инициализация HTML-лога.
            $log = "<h1>Test Get Log</h1>";
            $log .= "<style>
                .log-table, .log-table th, .log-table td {
                    border: 1px solid black;
                    border-collapse: collapse;
                    padding: 8px;
                }
                .log-table {
                    width: 100%;
                    margin-bottom: 20px;
                }
            </style>";
            $log .= "<table class='log-table'>";
            $log .=
                "<tr><th>Query</th><th>Where Clause</th><th>Time (seconds)</th><th>Rows Returned</th></tr>";

            // Генерация и выполнение $x случайных запросов.
            for ($i = 0; $i < $x; $i++) {
                // Формирование случайного условия WHERE.
                $where = [];
                foreach (array_keys($this->fields_config) as $field) {
                    if (rand(0, 1)) {
                        // 50% шанс включения поля в WHERE.
                        $operators = ["=", ">", "<", ">=", "<=", "LIKE"];
                        $operator = $operators[array_rand($operators)];
                        $value =
                            $operator === "LIKE"
                                ? "'%" . rand(1, 100) . "%'"
                                : rand(1, 100);

                        // Добавляем только существующие поля в условие WHERE.
                        if (isset($this->fields_config[$field])) {
                            $where[] = [
                                "fieldname" => $field,
                                "op" => $operator,
                                "value" => $value,
                                "sqvalue" => false,
                            ];
                        }
                    }
                }

                // Выполнение запроса.
                $start_time = microtime(true);
                $results = $this->get($where);
                $end_time = microtime(true);
                $time_taken = $end_time - $start_time;

                // Формирование строки лога.
                $where_clause = implode(
                    " AND ",
                    array_map(function ($w) {
                        return "{$w["fieldname"]} {$w["op"]} {$w["value"]}";
                    }, $where)
                );
                $num_rows = is_array($results) ? count($results) : 0;

                $log .= "<tr>";
                $log .= "<td>Query {$i}</td>";
                $log .= "<td>{$where_clause}</td>";
                $log .= "<td>{$time_taken}</td>";
                $log .= "<td>{$num_rows}</td>";
                $log .= "</tr>";
            }

            $log .= "</table>";
            return $log;
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Удаляет все кастомные свойства для объекта с указанным идентификатором
     *
     * @param int $obj_key Идентификатор объекта
     * @return bool Возвращает true в случае успешного выполнения, иначе false
     */
    public function deleteAllCustomProperties($obj_key)
    {
        try {
            // Начало транзакции
            $this->db->beginTransaction();

            // Удаление всех кастомных свойств для указанного объекта
            $sql = "DELETE FROM {$this->cf_table} WHERE obj_key = ? AND obj_type = ?";
            $result = $this->executeSql(
                $sql,
                [$obj_key, $this->obj_type],
                __METHOD__
            );

            // Проверка на успешное выполнение запроса
            if ($result) {
                // Фиксация транзакции
                $this->db->commit();
                return true;
            } else {
                // Откат транзакции в случае ошибки
                $this->db->rollBack();
                return false;
            }
        } catch (Exception $e) {
            // Откат транзакции в случае ошибки
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            $this->errors[] = $e->getMessage();
            return false;
        }
    }

    /**
     * Анализирует данные в таблице $cf_table и выводит статистику в формате HTML
     *
     * @return string HTML-вывод с результатами анализа
     */
   /**
 * Анализирует данные в таблице $cf_table и выводит статистику в формате дерева <ul><li>
 *
 * @return string HTML-вывод с результатами анализа
 */
/**
 * Анализирует данные в таблице $cf_table и выводит статистику в формате дерева <ul><li>
 *
 * @return string HTML-вывод с результатами анализа
 */
public function analyze()
{
    try {
        $sql = "
            SELECT 
                obj_type,
                COUNT(DISTINCT obj_key) AS num_objects,
                COUNT(*) AS total_field_values
            FROM {$this->cf_table}
            GROUP BY obj_type
        ";
        $stmt = $this->executeSql($sql, [], __METHOD__);
        if ($stmt) {
            $summary_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $summary_stats = false;
        }

        $sql = "
            SELECT 
                obj_type,
                field_name,
                data_type,
                COUNT(DISTINCT obj_key) AS num_objects,
                COUNT(*) AS num_fields,
                SUM(actual_value) AS num_actual_fields,
                AVG(num) AS avg_values_per_field,
                MIN(num) AS min_values_per_field,
                MAX(num) AS max_values_per_field,
                MAX(num) > 1 AS is_multiple
            FROM (
                SELECT 
                    obj_type,
                    obj_key,
                    field_name,
                    actual_value,
                    CASE 
                        WHEN field_value_varchar IS NOT NULL THEN 'varchar'
                        WHEN field_value_int IS NOT NULL THEN 'int'
                        WHEN field_value_date IS NOT NULL THEN 'date'
                        WHEN field_value_float IS NOT NULL THEN 'float'
                        WHEN field_value_text IS NOT NULL THEN 'text'
                    END AS data_type,
                    num
                FROM {$this->cf_table}
                WHERE actual_value = TRUE OR actual_value = FALSE
            ) AS subquery
            GROUP BY obj_type, field_name, data_type
        ";
        $stmt = $this->executeSql($sql, [], __METHOD__);
        if ($stmt) {
            $field_stats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $field_stats = false;
        }

        $data = [];
        foreach ($summary_stats as $row) {
            $obj_type = $row["obj_type"];
            $data[$obj_type]["num_objects"] = $row["num_objects"];
            $data[$obj_type]["total_field_values"] = $row["total_field_values"];
            $data[$obj_type]["fields"] = [];
        }

        foreach ($field_stats as $row) {
            $obj_type = $row["obj_type"];
            $field_name = $row["field_name"];
            $data[$obj_type]["fields"][$field_name] = [
                "data_type" => $row["data_type"],
                "is_multiple" => $row["is_multiple"] ? "ДА" : "НЕТ",
                "avg_values_per_field" => round($row["avg_values_per_field"], 2),
                "min_values_per_field" => $row["min_values_per_field"],
                "max_values_per_field" => $row["max_values_per_field"],
                "num_fields" => $row["num_fields"],
                "num_actual_fields" => $row["num_actual_fields"]
            ];
        }

        $html = '<style>
            .tree, .tree ul {
                list-style-type: none;
                padding-left: 20px;
            }
            .tree ul {
                margin-left: 20px;
                padding-left: 20px;
                border-left: 1px dashed #ccc;
            }
            .tree li {
                margin: 5px 0;
            }
            .tree li::before {
                content: "•";
                color: #888;
                display: inline-block;
                width: 1em;
                margin-left: -1em;
            }
            .tree li strong {
                color: #333;
            }
        </style>';
        $html .= '<ul class="tree">';

        foreach ($data as $obj_type => $info) {
            $html .= "<li><strong>Тип объекта: {$obj_type}</strong><ul>";
            $html .= "<li>Количество объектов: {$info["num_objects"]}</li>";
            $html .= "<li>Сумарное количество значений свойств: {$info["total_field_values"]}</li>";
            $html .= "<li><strong>Зарегистрированные типы свойств:</strong><ul>";
            foreach ($info["fields"] as $field_name => $field_info) {
                $html .= "<li><strong>Свойство: {$field_name}</strong><ul>";
                $html .= "<li>Тип: {$field_info["data_type"]}</li>";
                $html .= "<li>Множественное: {$field_info["is_multiple"]}</li>";
                $html .= "<li>Среднее количество значений: {$field_info["avg_values_per_field"]}</li>";
                $html .= "<li>Минимальное количество значений: {$field_info["min_values_per_field"]}</li>";
                $html .= "<li>Максимальное количество значений: {$field_info["max_values_per_field"]}</li>";
                $html .= "<li>Всего значений: {$field_info["num_fields"]}</li>";
                $html .= "<li>Актуальных значений: {$field_info["num_actual_fields"]}</li>";
                $html .= "</ul></li>";
            }
            $html .= "</ul></li>";
            $html .= "</ul></li>";
        }

        $html .= "</ul>";

        return $html;
    } catch (Exception $e) {
        $this->errors[] = $e->getMessage();
        return false;
    }
}





/**
 * Анализирует объекты и выводит информацию в формате дерева <ul><li>
 *
 * @param string|null $obj_type Тип объекта (название главной таблицы)
 * @param string|null $title_field Поле главной таблицы с заголовком объекта
 * @param int|null $obj_id Идентификатор объекта
 * @return string HTML-вывод с результатами анализа
 
 
 
 */
 
/**
 * Анализирует объекты и выводит информацию в формате дерева <ul><li>
 *
 * @param string|null $obj_type Тип объекта (название главной таблицы)
 * @param string|null $title_field Поле главной таблицы с заголовком объекта
 * @param int|null $obj_id Идентификатор объекта
 * @return string HTML-вывод с результатами анализа
 */
/**
 * Анализирует объекты и выводит информацию в формате дерева <ul><li>
 *
 * @param string|null $obj_type Тип объекта (название главной таблицы)
 * @param string|null $title_field Поле главной таблицы с заголовком объекта
 * @param int|null $obj_id Идентификатор объекта
 * @return string HTML-вывод с результатами  анализа
 */
 
 // 2024-07-25 15:12:07 упорядочить сессии по дате ! самые новые сверху 
 // в ветке Кастомные свойства , для каждой переменной добавить подветку "старые значения" в которой указывать сессию , пользователя дату и время , и старое значение свойства
public function analitycs_objets($obj_type = null, $title_field = null, $obj_id = null)
{
    // Устанавливаем тип объекта, если не задан
    $obj_type = $obj_type ?: $this->obj_type;

    try {
        // Если указан obj_id, добавляем условие для where
        if ($obj_id) {
            $this->where[] = [
                "fieldname" => $this->obj_type_key,
                "op" => "=",
                "value" => $obj_id
            ];
        }

        // Формирование SQL-запроса для получения данных объектов
        $obj_sql = "SELECT * FROM {$obj_type}";
        if (!empty($this->where)) {
            $where_clauses = [];
            $params = [];
            foreach ($this->where as $condition) {
                $where_clauses[] = "{$condition['fieldname']} {$condition['op']} ?";
                $params[] = $condition['value'];
            }
            $obj_sql .= " WHERE " . implode(" AND ", $where_clauses);
        }

        // Выполнение SQL-запроса для получения данных объектов
        $obj_stmt = $this->executeSql($obj_sql, $params, __METHOD__);
        if ($obj_stmt) {
            // Получение всех объектов в виде ассоциативного массива
            $objects = $obj_stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return false;
        }

        // Подготовка данных для каждого объекта
        $data = [];
        foreach ($objects as $object) {
            $obj_key = $object[$this->obj_type_key];
            $data[$obj_key] = [
                "main" => $object, // Основные данные объекта
                "custom_fields" => [], // Кастомные поля (будут заполнены позже)
                "history" => $this->get_historyid($obj_key) // Получение истории изменений для каждого объекта
            ];
        }

        // Начало формирования HTML-вывода
        $html = '<ul class="fwtree">';

        // Проход по каждому объекту для формирования данных
        foreach ($data as $obj_key => $info) {
            // Заголовок объекта
            $title = $title_field ? $info["main"][$title_field] : "ID {$obj_key}";
            $html .= "<li data-id='obj_{$obj_key}'><strong>Объект: {$title} (ID: {$obj_key})</strong><ul>";
            // Количество сессий редактирования
            $html .= "<li data-id='obj_{$obj_key}_sessions'>Количество сессий редактирования: " . count($info["history"]) . "</li>";
            // Свойства главной таблицы
            $html .= "<li data-id='obj_{$obj_key}_main'><strong>Свойства главной таблицы:</strong><ul>";
            foreach ($info["main"] as $field => $value) {
                $html .= "<li data-id='obj_{$obj_key}_main_{$field}'><strong>{$field}</strong>: {$value}</li>";
            }
            $html .= "</ul></li>";

            // Кастомные свойства
            $html .= "<li data-id='obj_{$obj_key}_custom'><strong>Кастомные свойства:</strong><ul>";
            foreach ($info["history"] as $session_id => $session_info) {
                foreach ($session_info["up_varibles"] as $field_name => $values) {
                    foreach ($values as $num => $value) {
                        $html .= "<li data-id='obj_{$obj_key}_custom_{$field_name}_{$num}'><strong>{$field_name}</strong><ul>";
                        $html .= "<li>Значение: {$value}</li>";
                        $html .= "<li>Последнее редактирование: {$session_info["date"]}</li>";
                        $html .= "<li>Отредактировал пользователь: {$session_info["user_id"]}</li>";
                        $html .= "<li>Номер значения: {$num}</li>";

                        // Добавление старых значений
                        $html .= "<li data-id='obj_{$obj_key}_custom_{$field_name}_old'><strong>Старые значения:</strong><ul>";
                        foreach ($info["history"] as $hist_session_id => $hist_session_info) {
                            // Проверка наличия старых значений для данного свойства
                            foreach ($hist_session_info["old_values"]["cmf__" . $field_name] ?? [] as $old_num => $old_value) {
                                $html .= "<li>Старое значение: {$old_value}, Сессия: {$hist_session_id}, Пользователь: {$hist_session_info["user_id"]}, Дата: {$hist_session_info["date"]}</li>";
                            }
                        }
                        $html .= "</ul></li>";

                        $html .= "</ul></li>";
                    }
                }
            }
            $html .= "</ul></li>";

            // История изменений
            $html .= "<li data-id='obj_{$obj_key}_history'><strong>История изменений (количество сессий изменений: " . count($info["history"]) . "):</strong><ul>";

            // Упорядочивание сессий по дате, новее сверху
            $sorted_sessions = array_keys($info["history"]);
            usort($sorted_sessions, function ($a, $b) use ($info) {
                return strtotime($info["history"][$b]["date"]) - strtotime($info["history"][$a]["date"]);
            });

            // Формирование данных для каждой сессии изменений
            foreach ($sorted_sessions as $session_id) {
                $session_info = $info["history"][$session_id];
                $updated_properties_count = count($session_info["up_varibles"]);
                $html .= "<li data-id='obj_{$obj_key}_history_{$session_id}'>№ сессии: {$session_id} (Дата и время: {$session_info["date"]}, Пользователь: {$session_info["user_id"]}, Изменено свойств: {$updated_properties_count})<ul>";
                $html .= "<li><strong>Обновленные свойства:</strong><ul>";

                // Формирование данных для каждого обновленного свойства в истории изменений
                foreach ($session_info["up_varibles"] as $field_name => $values) {
                    foreach ($values as $num => $new_value) {
                        $old_value = $session_info["old_values"]["cmf__" . $field_name][$num] ?? 'N/A';
                        $html .= "<li data-id='obj_{$obj_key}_history_{$session_id}_{$field_name}_{$num}'><strong>{$field_name}</strong><ul>";
                        $html .= "<li>Номер значения: {$num} : {$new_value}</li>";
                        $html .= "<li>Старое значение: {$old_value}</li>";
                        $html .= "<li>Новое значение: {$new_value}</li>";
                        $html .= "</ul></li>";
                    }
                }

                $html .= "</ul></li>";
                $html .= "</ul></li>";
            }
            $html .= "</ul></li>";
            $html .= "</ul></li>";
        }

        $html .= "</ul>";

        // Возвращение сформированного HTML
        return $html;
    } catch (Exception $e) {
        // Запись ошибок в случае исключений
        $this->errors[] = $e->getMessage();
        return false;
    }
}


// UP должны записываться свойства только если их значения были изменены











/**
 * Удаляет сессии без измененных значений
 *
 * @return bool Возвращает true в случае успешного выполнения, иначе false
 */
public function delete_empty_sessions()
{
    try {
        // Начало транзакции
        $this->db->beginTransaction();

        // SQL-запрос для получения всех сессий, где все записи имеют actual_value = FALSE
        $sql = "SELECT edit_object_session 
                FROM {$this->cf_table} 
                GROUP BY edit_object_session 
                HAVING SUM(CASE WHEN actual_value = TRUE THEN 1 ELSE 0 END) = 0";

        $stmt = $this->executeSql($sql, [], __METHOD__);
        if ($stmt) {
            $empty_sessions = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            return false;
        }

        if (empty($empty_sessions)) {
            // Если пустых сессий нет, возвращаем true
            $this->db->commit();
            return true;
        }

        // Удаление записей сессий без измененных значений
        $placeholders = implode(',', array_fill(0, count($empty_sessions), '?'));
        $sql_delete = "DELETE FROM {$this->cf_table} WHERE edit_object_session IN ($placeholders)";
        $result = $this->executeSql($sql_delete, $empty_sessions, __METHOD__);

        if ($result) {
            // Фиксация транзакции
            $this->db->commit();
            return true;
        } else {
            // Откат транзакции в случае ошибки
            $this->db->rollBack();
            return false;
        }
    } catch (Exception $e) {
        // Откат транзакции в случае ошибки
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        $this->errors[] = $e->getMessage();
        return false;
    }
}









/**
 * Удаляет все неактуальные значения свойств в базе
 *
 * @param int|null $user_id Идентификатор пользователя
 * @param string|null $obj_type Тип объекта
 * @param int|null $object_id Идентификатор объекта
 * @return bool Возвращает true в случае успешного выполнения, иначе false
 */
/**
 * Удаляет все неактуальные значения свойств в базе
 *
 * @param int|null $user_id Идентификатор пользователя
 * @param string|null $obj_type Тип объекта
 * @param int|null $object_id Идентификатор объекта
 * @return bool Возвращает true в случае успешного выполнения, иначе false
 */
public function delete_non_actual_values($user_id = null, $obj_type = null, $object_id = null)
{
    try {
        // Начало транзакции
        $this->db->beginTransaction();

        // Построение условий WHERE на основе переданных параметров
        $where_clauses = ["actual_value = FALSE"];
        $params = [];

        if ($user_id !== null) {
            $where_clauses[] = "user_editor_id = ?";
            $params[] = $user_id;
        }

        if ($obj_type !== null) {
            $where_clauses[] = "obj_type = ?";
            $params[] = $obj_type;
        }

        if ($object_id !== null) {
            $where_clauses[] = "obj_key = ?";
            $params[] = $object_id;
        }

        $where_sql = implode(" AND ", $where_clauses);

        // SQL-запрос для удаления неактуальных значений свойств
        $sql_delete = "DELETE FROM {$this->cf_table} WHERE $where_sql";
        
        // Логирование SQL-запроса и параметров
        $this->sql[] = [
            "sql" => $sql_delete,
            "params" => json_encode($params),
            "method" => __METHOD__
        ];

        $stmt = $this->db->prepare($sql_delete);
        $result = $stmt->execute($params);

        if ($result) {
            // Фиксация транзакции
            $this->db->commit();
            return true;
        } else {
            // Логирование ошибки выполнения SQL-запроса
            $this->sql[] = [
                "sql" => $sql_delete,
                "params" => json_encode($params),
                "method" => __METHOD__,
                "error" => $stmt->errorInfo()
            ];
            // Откат транзакции в случае ошибки
            $this->db->rollBack();
            return false;
        }
    } catch (Exception $e) {
        // Логирование ошибки выполнения SQL-запроса
        $this->sql[] = [
            "sql" => $sql_delete,
            "params" => json_encode($params),
            "method" => __METHOD__,
            "error" => $e->getMessage()
        ];
        // Откат транзакции в случае ошибки
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        $this->errors[] = $e->getMessage();
        return false;
    }
}



/**
 * Восстанавливает сессию изменения объекта, делая перепометки на актуальности свойств
 *
 * @param int $session_id Идентификатор сессии редактирования
 * @param int|null $object_id Идентификатор объекта (необязательный параметр)
 * @return bool Возвращает true в случае успешного выполнения, иначе false
 */
public function repair_session($session_id, $object_id = null)
{
    try {
        // Начало транзакции
        $this->db->beginTransaction();

        // Построение условий WHERE
        $where_clauses = ["edit_object_session = ?"];
        $params = [$session_id];

        if ($object_id !== null) {
            $where_clauses[] = "obj_key = ?";
            $params[] = $object_id;
        }

        $where_sql = implode(" AND ", $where_clauses);

        // Получение всех значений для сессии редактирования
        $sql = "SELECT obj_key, field_name, num, field_value_varchar, field_value_int, field_value_float, field_value_date
                FROM {$this->cf_table}
                WHERE $where_sql";
        $stmt = $this->executeSql($sql, $params, __METHOD__);
        if (!$stmt) {
            $this->db->rollBack();
            return false;
        }

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Обновление актуальности свойств
        foreach ($results as $row) {
            $obj_key = $row['obj_key'];
            $field_name = $row['field_name'];
            $num = $row['num'];
            $field_value_varchar = $row['field_value_varchar'];
            $field_value_int = $row['field_value_int'];
            $field_value_float = $row['field_value_float'];
            $field_value_date = $row['field_value_date'];

            // Помечаем все старые значения как неактуальные
            $update_sql = "UPDATE {$this->cf_table} 
                           SET actual_value = FALSE 
                           WHERE obj_type = ? AND obj_key = ? AND field_name = ? AND num = ?";
            $this->executeSql($update_sql, [$this->obj_type, $obj_key, $field_name, $num], __METHOD__);

            // Вставляем новое значение как актуальное
            $insert_sql = "INSERT INTO {$this->cf_table} 
                           (obj_type, obj_key, field_name, field_value_varchar, field_value_int, field_value_float, field_value_date, user_editor_id, edit_date_time, edit_object_session, num, actual_value)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, TRUE)";
            $params = [
                $this->obj_type, 
                $obj_key, 
                $field_name, 
                $field_value_varchar, 
                $field_value_int, 
                $field_value_float, 
                $field_value_date, 
                1, // Идентификатор пользователя, можно заменить на актуальный
                $session_id, 
                $num
            ];
            $this->executeSql($insert_sql, $params, __METHOD__);
        }

        // Фиксация транзакции
        $this->db->commit();
        return true;
    } catch (Exception $e) {
        // Откат транзакции в случае ошибки
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
        $this->errors[] = $e->getMessage();
        return false;
    }
}





/**
 * Возвращает стилизацию и JS для дерева
 *
 * @return string HTML-вывод со стилями и скриптом
 */
public function getTreeStylesAndScript()
{
    return '
    <style>
        .fwtree, .fwtree ul {
            list-style-type: none;
            padding-left: 20px;
        }
        .fwtree ul {
            margin-left: 20px;
            padding-left: 20px;
            border-left: 1px dashed #ccc;
            display: none;
        }
        .fwtree li {
            margin: 5px 0;
            cursor: pointer;
            position: relative;
            padding-left: 20px;
        }
        .fwtree li::before {
            content: "►";
            position: absolute;
            left: 0;
            color: #888;
            transition: transform 0.3s ease;
        }
        .fwtree .expanded > ul {
            display: block;
        }
        .fwtree .expanded > li::before {
            transform: rotate(90deg);
        }
        .fwtree li strong {
            color: #333;
            font-weight: bold;
        }
        .fwtree li:hover {
            background-color: #f0f0f0;
        }
        .fwtree li::marker {
            font-size: 1.2em;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const toggleElements = document.querySelectorAll(".fwtree li");
            
            // Восстановление состояния
            toggleElements.forEach(function(element) {
                const id = element.dataset.id;
                if (localStorage.getItem("fwtree_" + id) === "expanded") {
                    element.classList.add("expanded");
                }
            });

            toggleElements.forEach(function(element) {
                element.addEventListener("click", function(e) {
                    e.stopPropagation();
                    this.classList.toggle("expanded");

                    const id = this.dataset.id;
                    if (this.classList.contains("expanded")) {
                        localStorage.setItem("fwtree_" + id, "expanded");
                    } else {
                        localStorage.removeItem("fwtree_" + id);
                    }
                });
            });
        });
    </script>';
}









 
public function geth()
{
    $history = []; // Массив для хранения истории изменений

    try {
        // Построение условий WHERE из $this->where
        $where_clauses = [];
        $params = [];
        foreach ($this->where as $condition) {
            $where_clauses[] = "{$condition['fieldname']} {$condition['op']} ?";
            $params[] = $condition['value'];
        }

        // SQL-запрос для получения основной информации объектов
        $main_sql = "SELECT * FROM {$this->obj_type}";
        if (!empty($where_clauses)) {
            $main_sql .= " WHERE " . implode(" AND ", $where_clauses);
        }
        $main_stmt = $this->executeSql($main_sql, $params, __METHOD__);
        if ($main_stmt) {
            $main_data = $main_stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            return false;
        }

        // Получение всех obj_key для истории
        $obj_keys = array_column($main_data, $this->obj_type_key);

        if (empty($obj_keys)) {
            return false;
        }

        $placeholders = implode(',', array_fill(0, count($obj_keys), '?'));

        // SQL-запрос для получения истории изменений свойств объектов
        $history_sql = "SELECT edit_object_session, edit_date_time, user_editor_id, field_name, field_value_varchar, field_value_int, field_value_float, field_value_date, num, actual_value, obj_key
                        FROM {$this->cf_table}
                        WHERE obj_key IN ($placeholders) AND obj_type = ?
                        ORDER BY edit_object_session, edit_date_time";

        // Выполнение запроса
        $history_stmt = $this->executeSql($history_sql, array_merge($obj_keys, [$this->obj_type]), __METHOD__);
        if ($history_stmt) {
            $results = $history_stmt->fetchAll(PDO::FETCH_ASSOC);

            // Обработка результатов
            foreach ($results as $row) {
                $obj_key = $row["obj_key"];
                $session_id = $row["edit_object_session"];
                $date = $row["edit_date_time"];
                $user_id = $row["user_editor_id"];
                $field_name = $row["field_name"];
                $num = $row["num"];
                $value = $row["field_value_varchar"] ?? ($row["field_value_int"] ?? ($row["field_value_float"] ?? $row["field_value_date"]));

                if (!isset($history[$obj_key])) {
                    $history[$obj_key] = [];
                }

                if (!isset($history[$obj_key][$session_id])) {
                    $history[$obj_key][$session_id] = [
                        "date" => $date,
                        "user_id" => $user_id,
                        "old_values" => [],
                        "up_varibles" => [],
                        "allvaribles" => array_filter($main_data, function ($item) use ($obj_key) {
                            return $item[$this->obj_type_key] == $obj_key;
                        })[0] ?? [] // Заполнение allvaribles основными полями
                    ];
                }

                // Заполняем allvaribles кастомными полями
                if (!isset($history[$obj_key][$session_id]["allvaribles"]["cmf__" . $field_name])) {
                    $history[$obj_key][$session_id]["allvaribles"]["cmf__" . $field_name] = [];
                }
                $history[$obj_key][$session_id]["allvaribles"]["cmf__" . $field_name][$num] = $value;

                // Заполняем old_values и up_varibles кастомными полями
                if ($row["actual_value"]) {
                    if (!isset($history[$obj_key][$session_id]["up_varibles"]["cmf__" . $field_name])) {
                        $history[$obj_key][$session_id]["up_varibles"]["cmf__" . $field_name] = [];
                    }
                    $history[$obj_key][$session_id]["up_varibles"]["cmf__" . $field_name][$num] = $value;
                } else {
                    if (!isset($history[$obj_key][$session_id]["old_values"]["cmf__" . $field_name])) {
                        $history[$obj_key][$session_id]["old_values"]["cmf__" . $field_name] = [];
                    }
                    $history[$obj_key][$session_id]["old_values"]["cmf__" . $field_name][$num] = $value;
                }
            }
        }
    } catch (Exception $e) {
        $this->errors[] = $e->getMessage();
        return false;
    }

    return $history;
}


    /**
     * Получает историю изменений свойств объекта
     *
     * @param int $obj_type_key Ключ объекта
     * @return array История изменений свойств объекта
     */
    public function get_historyid($obj_type_key)
    {
        $history = []; // Массив для хранения истории изменений

        try {
            // SQL-запрос для получения основной информации объекта
            $main_sql = "SELECT * FROM {$this->obj_type} WHERE {$this->obj_type_key} = ?";
            $main_stmt = $this->executeSql(
                $main_sql,
                [$obj_type_key],
                __METHOD__
            );
            if ($main_stmt) {
                $main_data = $main_stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                return false;
            }

            // SQL-запрос для получения истории изменений свойств объекта
            $history_sql = "SELECT edit_object_session, edit_date_time, user_editor_id, field_name, field_value_varchar, field_value_int, field_value_float, field_value_date, num, actual_value
                        FROM {$this->cf_table}
                        WHERE obj_key = ? AND obj_type = ?
                        ORDER BY edit_object_session, edit_date_time";

            // Выполнение запроса
            $history_stmt = $this->executeSql(
                $history_sql,
                [$obj_type_key, $this->obj_type],
                __METHOD__
            );
            if ($history_stmt) {
                $results = $history_stmt->fetchAll(PDO::FETCH_ASSOC);

                // Обработка результатов
                foreach ($results as $row) {
                    $session_id = $row["edit_object_session"];
                    $date = $row["edit_date_time"];
                    $user_id = $row["user_editor_id"];
                    $field_name = $row["field_name"];
                    $num = $row["num"];
                    $value =
                        $row["field_value_varchar"] ??
                        ($row["field_value_int"] ??
                            ($row["field_value_float"] ??
                                $row["field_value_date"]));

                    if (!isset($history[$session_id])) {
                        $history[$session_id] = [
                            "date" => $date,
                            "user_id" => $user_id,
                            "old_values" => [],
                            "up_varibles" => [],
                            "allvaribles" => $main_data, // Заполнение allvaribles основными полями
                        ];
                    }

                    // Заполняем allvaribles кастомными полями
                    if (
                        !isset(
                            $history[$session_id]["allvaribles"][
                                "cmf__" . $field_name
                            ]
                        )
                    ) {
                        $history[$session_id]["allvaribles"][
                            "cmf__" . $field_name
                        ] = [];
                    }
                    $history[$session_id]["allvaribles"]["cmf__" . $field_name][
                        $num
                    ] = $value;

                    // Заполняем old_values и up_varibles кастомными полями
                    if ($row["actual_value"]) {
                        if (
                            !isset(
                                $history[$session_id]["up_varibles"][
                                    "cmf__" . $field_name
                                ]
                            )
                        ) {
                            $history[$session_id]["up_varibles"][
                                "cmf__" . $field_name
                            ] = [];
                        }
                        $history[$session_id]["up_varibles"][
                            "cmf__" . $field_name
                        ][$num] = $value;
                    } else {
                        if (
                            !isset(
                                $history[$session_id]["old_values"][
                                    "cmf__" . $field_name
                                ]
                            )
                        ) {
                            $history[$session_id]["old_values"][
                                "cmf__" . $field_name
                            ] = [];
                        }
                        $history[$session_id]["old_values"][
                            "cmf__" . $field_name
                        ][$num] = $value;
                    }
                }
            }
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
            return false;
        }

        return $history;
    }
}

 