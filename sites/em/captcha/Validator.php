<?php
/**
 * Универсальный валидатор для форм (вдохновлён Laravel)
 * - Корректно обрабатывает одиночные и множественные upload-ы: files, files[], files.0
 * - Поддержка вложенных и массивных полей (в т.ч. files.*)
 * - Проверка обязательности, email, строки, числа, min/max, размер файлов, mime, кастомные правила
 * - Добавлена строгая проверка существования методов валидации
 * - Добавлена отладочная информация в режиме debug
 * - Улучшена валидация телефонных номеров по международным стандартам
 */
class Validator
{
    protected $data = [];
    protected $files = [];
    protected $rules = [];
    protected $errors = [];
    protected $debugInfo = [];
    protected $debugMode = false;

    /**
     * Конструктор
     * 
     * @param array $rules Правила валидации
     * @param bool $debugMode Режим отладки
     */
    public function __construct(array $rules, $debugMode = false)
    { 
        $this->debugMode = $debugMode;
        $this->data = $_POST;
        $this->files = $this->normalizeFiles($_FILES);
        $this->rules = $rules;
        $this->errors = [];
        $this->debugInfo = [];
    }

    /**
     * Проверить все поля по правилам (true — если ошибок нет)
     */
    public function passes()
    {
        foreach ($this->rules as $field => $rules) {
            $rulesArr = is_array($rules) ? $rules : explode('|', $rules);
            
            // --- Обработка fields.* (например, files.*) ---
            if (strpos($field, '.*') !== false) {
                $base = substr($field, 0, -2);
                $isFile = isset($this->files[$base]);
                $items = $isFile ? $this->files[$base] : ($this->getFieldValue($base) ?? []);
                if (is_array($items)) {
                    foreach ($items as $i => $item) {
                        $this->validateField($base . '.' . $i, $item, $rulesArr, $isFile);
                    }
                }
                continue;
            }
            
            // --- Обычные поля (файлы или текстовые) ---
            $isFile = isset($this->files[$field]);
            $value = $isFile ? $this->files[$field] : $this->getFieldValue($field);
            
            // Если файловое поле, всегда приводим к массиву (даже один файл)
            if ($isFile && !is_array($value)) {
                $value = [$value];
            }
            
            // Множественный upload (files[]), валидируем каждый файл
            if ($isFile && is_array($value)) {
                foreach ($value as $i => $v) {
                    $this->validateField($field . '.' . $i, $v, $rulesArr, true);
                }
            }
            // Массив текстовых значений (например, foo[])
            elseif (is_array($value) && !$isFile) {
                foreach ($value as $i => $v) {
                    $this->validateField($field . '.' . $i, $v, $rulesArr, false);
                }
            }
            // Одиночное значение/файл
            else {
                $this->validateField($field, $value, $rulesArr, $isFile);
            }
        }
        
        return empty($this->errors);
    }

    /**
     * Массив ошибок (поле => текст ошибки)
     */
    public function errors()
    {
        return $this->errors;
    }

    /**
     * Валидные данные (с учётом нормализации)
     */
    public function validData()
    {
        $data = [];
        foreach ($this->rules as $field => $rules) {
            $isFile = isset($this->files[$field]);
            if ($isFile) {
                $data[$field] = $this->files[$field];
            } else {
                $data[$field] = $this->getFieldValue($field);
            }
        }
        return $data;
    }

    /**
     * Получить информацию для отладки
     */
    public function getDebugInfo()
    {
        return $this->debugInfo;
    }

    // ================= ВНУТРЕННЯЯ ЛОГИКА ===================
    protected function validateField($field, $value, $rulesArr, $isFile = false)
	{
		$fieldDebug = [
			'field' => $field,
			'value' => $isFile ? '[FILE]' : $value,
			'rules' => [],
			'passed' => true
		];

		foreach ($rulesArr as $rule) {
			$params = [];
			$ruleName = $rule;
			$ruleDebug = [
				'rule' => $ruleName,
				'params' => [],
				'passed' => false,
				'error' => null
			];
			
			// min:3, mimes:jpg,png
			if (is_string($rule) && strpos($rule, ':') !== false) {
				[$ruleName, $paramStr] = explode(':', $rule, 2);
				$params = explode(',', $paramStr);
				$ruleDebug['params'] = $params;
			}
			
			// === НОВАЯ РЕГИСТРОНЕЗАВИСИМАЯ ЛОГИКА ===
			// Нормализуем имя правила: убираем пробелы, приводим к нижнему регистру
			$normalizedRuleName = strtolower(trim($ruleName));
			// Преобразуем в camelCase (первая буква в верхнем регистре)
			$camelCaseRuleName = ucfirst($normalizedRuleName);
			// Формируем имя метода
			$method = 'rule' . $camelCaseRuleName;
			// === КОНЕЦ ИЗМЕНЕНИЙ ===
			
			if (method_exists($this, $method)) {
				$result = $this->$method($value, $params, $field, $isFile);
				
				if ($result !== true) {
					$this->errors[$field] = $result;
					$ruleDebug['passed'] = false;
					$ruleDebug['error'] = $result;
					$fieldDebug['passed'] = false;
					$fieldDebug['rules'][] = $ruleDebug;
					$this->debugInfo[] = $fieldDebug;
					return false;
				}
				
				$ruleDebug['passed'] = true;
			}
			// Кастомные callable-правила
			elseif (is_callable($rule)) {
				$msg = $rule($value, $this->data, $this->files);
				if ($msg !== true) {
					$this->errors[$field] = $msg;
					$ruleDebug['passed'] = false;
					$ruleDebug['error'] = $msg;
					$fieldDebug['passed'] = false;
					$fieldDebug['rules'][] = $ruleDebug;
					$this->debugInfo[] = $fieldDebug;
					return false;
				}
				$ruleDebug['passed'] = true;
			}
			// Правило не найдено
			else {
				$errorMessage = "Ошибка валидации: не найден метод для правила '{$ruleName}'";
				$this->errors[$field] = $errorMessage;
				$ruleDebug['passed'] = false;
				$ruleDebug['error'] = $errorMessage;
				$fieldDebug['passed'] = false;
				$fieldDebug['rules'][] = $ruleDebug;
				$this->debugInfo[] = $fieldDebug;
				throw new RuntimeException($errorMessage);
			}
			
			$fieldDebug['rules'][] = $ruleDebug;
		}
		
		if ($this->debugMode) {
			$this->debugInfo[] = $fieldDebug;
		}
		
		return true;
	}

    /**
     * Получить значение поля (в т.ч. вложенное: field.0, field[0])
     */
    protected function getFieldValue($field)
    {
        if (isset($this->data[$field])) return $this->data[$field];
        // Вложенное — типа "photos.0"
        $parts = explode('.', $field);
        $v = $this->data;
        foreach ($parts as $p) {
            if (!is_array($v) || !array_key_exists($p, $v)) return null;
            $v = $v[$p];
        }
        return $v;
    }

    // ================= ПРАВИЛА ======================
    protected function ruleRequired($value, $params, $field, $isFile)
    {
        if ($isFile) {
            // Для файлов: должен быть загружен без ошибок
            if (empty($value) || !isset($value['error']) || $value['error'] !== UPLOAD_ERR_OK) {
                return 'Файл обязателен';
            }
        } else {
            if ($value === null || $value === '' || (is_array($value) && empty($value))) {
                return 'Обязательное поле';
            }
        }
        return true;
    }

    protected function ruleEmail($value)
    {
        if ($value && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return 'Некорректный e-mail';
        }
        return true;
    }

    protected function ruleString($value)
    {
        if ($value !== null && !is_string($value)) {
            return 'Значение должно быть строкой';
        }
        return true;
    }

    protected function ruleNumeric($value)
    {
        if ($value !== null && !is_numeric($value)) {
            return 'Значение должно быть числом';
        }
        return true;
    }

    protected function ruleMin($value, $params, $field, $isFile)
    {
        $min = isset($params[0]) ? (float)$params[0] : 0;
        if ($isFile) {
            if (!empty($value['size']) && $value['size'] < $min * 1024) {
                return "Размер файла не менее {$min} KB";
            }
        } elseif (is_string($value)) {
            if (mb_strlen($value) < $min) return "Минимальная длина — {$min} символов";
        } elseif (is_numeric($value)) {
            if ($value < $min) return "Минимальное значение — {$min}";
        }
        return true;
    }

    protected function ruleMax($value, $params, $field, $isFile)
    {
        $max = isset($params[0]) ? (float)$params[0] : 0;
        if ($isFile) {
            if (!empty($value['size']) && $value['size'] > $max * 1024) {
                return "Размер файла не более {$max} KB";
            }
        } elseif (is_string($value)) {
            if (mb_strlen($value) > $max) return "Максимальная длина — {$max} символов";
        } elseif (is_numeric($value)) {
            if ($value > $max) return "Максимальное значение — {$max}";
        }
        return true;
    }

    protected function ruleSize($value, $params, $field, $isFile)
    {
        $size = isset($params[0]) ? (float)$params[0] : 0;
        if ($isFile) {
            if (empty($value['size']) || $value['size'] != $size * 1024) {
                return "Размер файла должен быть ровно {$size} KB";
            }
        } elseif (is_string($value)) {
            if (mb_strlen($value) != $size) return "Длина должна быть ровно {$size} символов";
        }
        return true;
    }

    protected function ruleMimes($value, $params, $field, $isFile)
    {
        if (!$isFile || empty($params)) return true;
        if (empty($value['name'])) return 'Не выбран файл';
        $allowed = array_map('strtolower', $params);
        $ext = strtolower(pathinfo($value['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed)) {
            return 'Недопустимый формат файла: ' . implode(', ', $allowed);
        }
        return true;
    }

    protected function ruleArray($value)
    {
        if (!is_array($value)) return 'Должно быть массивом';
        return true;
    }

    /**
     * Проверяет, что значение не содержит HTML-тегов.
     * @param mixed $value Значение для проверки.
     * @return bool|string True если прошло, строка с ошибкой если нет.
     */
    protected function ruleNoHtml($value)
    {
        // Если значение null или пустая строка, считаем его валидным
        if ($value === null || $value === '') {
            return true;
        }
        if (!is_string($value)) {
            return true; // Пропускаем не-строки
        }
        // Сравниваем исходную строку с версией без тегов
        if ($value !== strip_tags($value)) {
            return 'Поле не должно содержать HTML-тегов.';
        }
        return true;
    }

    /**
     * Проверяет, что значение не содержит ссылок (http://, https://, www.).
     * @param mixed $value Значение для проверки.
     * @return bool|string True если прошло, строка с ошибкой если нет.
     */
    protected function ruleNoLinks($value)
    {
        // Если значение null или пустая строка, считаем его валидным
        if ($value === null || $value === '') {
            return true;
        }
        if (!is_string($value)) {
            return true; // Пропускаем не-строки
        }
        // Простая проверка на наличие типичных частей URL
        if (preg_match('/https?:\/\/|www\./i', $value)) {
            return 'Поле не должно содержать ссылок.';
        }
        return true;
    }

    /**
     * Расширенная проверка телефонного номера по международным стандартам
     * Поддерживает российские номера, международные форматы и другие стандарты
     * 
     * @param mixed $value Значение для проверки
     * @return bool|string True если прошло, строка с ошибкой если нет
     */
	protected function ruleValidPhone($value)
	{
		if ($value === null || $value === '') {
			return true;
		}
		
		if (!is_string($value)) {
			return 'Номер телефона должен быть строкой';
		}
		
		// Проверяем наличие недопустимых символов
		if (preg_match('/[^+\d\s\-\(\)]/', $value) || preg_match('/[_*#]/', $value)) {
			return 'Номер телефона не соответствует формату';
		}
		
		// Российские номера в допустимых форматах
		$russianFormats = [
			'/^\+7\s?\(?\d{3}\)?[-\s]?\d{3}[-\s]?\d{2}[-\s]?\d{2}$/',  // +7 (999) 123-45-67
			'/^8\s?\(?\d{3}\)?[-\s]?\d{3}[-\s]?\d{2}[-\s]?\d{2}$/',   // 8 (999) 123-45-67
			'/^\+7\d{10}$/',                                          // +79991234567
			'/^8\d{10}$/'                                             // 89991234567
		];
		
		// Международные номера в формате E.164
		$internationalFormat = '/^\+\d{7,15}$/';
		
		// Проверяем соответствие российским форматам
		foreach ($russianFormats as $format) {
			if (preg_match($format, $value)) {
				// Дополнительная проверка на полноту номера
				$digits = preg_replace('/\D/', '', $value);
				if (strlen($digits) === 11 && (strpos($digits, '7') === 0 || strpos($digits, '8') === 0)) {
					return true;
				}
			}
		}
		
		// Проверяем соответствие международному формату
		if (preg_match($internationalFormat, $value)) {
			return true;
		}
		
		return 'Некорректный формат номера телефона';
	}

    /**
     * Преобразует $_FILES к единому формату (всегда массивы файлов)
     */
    protected function normalizeFiles($files)
    {
        $output = [];
        foreach ($files as $field => $fileArr) {
            if (is_array($fileArr['name'])) {
                $count = count($fileArr['name']);
                $items = [];
                for ($i = 0; $i < $count; $i++) {
                    // Пропускаем незагруженные
                    if (empty($fileArr['name'][$i]) && $fileArr['error'][$i] == UPLOAD_ERR_NO_FILE) {
                        continue;
                    }
                    $items[] = [
                        'name'     => $fileArr['name'][$i],
                        'type'     => $fileArr['type'][$i],
                        'tmp_name' => $fileArr['tmp_name'][$i],
                        'error'    => $fileArr['error'][$i],
                        'size'     => $fileArr['size'][$i],
                    ];
                }
                // Даже если один файл — всё равно массив!
                if (!empty($items)) $output[$field] = $items;
            } else {
                if (empty($fileArr['name']) && $fileArr['error'] == UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                $output[$field] = [ $fileArr ];
            }
        }
        return $output;
    }
}

/**
 * Пример использования:
 * 
 * $debugMode = true; // Включить режим отладки
 * $rules = [
 *     'name' => 'required|string|min:3|max:64',
 *     'phone' => 'required|validPhone',
 *     'message' => 'string|max:300|no_html|no_links',
 * ];
 * 
 * $validator = new Validator($rules, $debugMode);
 * 
 * if (!$validator->passes()) {
 *     $response = [
 *         'success' => false,
 *         'message' => 'Исправьте ошибки в форме',
 *         'errors' => $validator->errors(),
 *         'validation_failed_early' => true
 *     ];
 *     
 *     // Добавляем отладочную информацию, если включен режим отладки
 *     if ($debugMode) {
 *         $response['debug'] = $validator->getDebugInfo();
 *     }
 *     
 *     echo json_encode($response, JSON_UNESCAPED_UNICODE);
 *     exit;
 * }
 * 
 * // Если дошли до этой точки - валидация прошла успешно
 * $response = [
 *     'success' => true,
 *     'message' => 'Форма успешно отправлена!',
 * ];
 * 
 * // Добавляем отладочную информацию, если включен режим отладки
 * if ($debugMode) {
 *     $response['debug'] = $validator->getDebugInfo();
 * }
 * 
 * echo json_encode($response, JSON_UNESCAPED_UNICODE);
 * exit;
 */