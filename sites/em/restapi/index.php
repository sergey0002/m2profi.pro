<?php
 

// Разрешаем запросы с любого домена.
header("Access-Control-Allow-Origin: *");

// Разрешаем браузеру использовать определенные HTTP-методы.
header("Access-Control-Allow-Methods: GET, OPTIONS");

// Разрешаем передавать в запросе нестандартные заголовки.
header("Access-Control-Allow-Headers: X-API-KEY, Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Обработка предварительных OPTIONS-запросов.
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
 
/**
 * REST API для базы данных застройщика недвижимости.
 * Версия 4.0 (Финальная)
 * - Добавлена сортировка по полю (sort_by) и направлению (sort_order).
 * - Добавлен фильтр по площади (area_min, area_max).
 * - Добавлена возможность фильтрации по нескольким ID домов (home_id=1,2,3).
 * - Добавлен эндпоинт /apartments/filters для "умных" фильтров.
 * - Эндпоинт /apartments возвращает общее количество найденных квартир.
 */

// Устанавливаем заголовок ответа в формат JSON с кодировкой UTF-8
header("Content-Type: application/json; charset=UTF-8");

class RealEstateAPI {
    private $pdo;
    private $apiKey = '23488918723681235767';

    /**
     * Конструктор класса.
     */
    public function __construct() {
        $dbHost = 'localhost';
        $dbName = 'm2profi_em';
        $dbUser = 'm2profi_em';
        $dbPass = 'tI9CBndTum14hShc';
 
        try {
            $this->pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8", $dbUser, $dbPass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $this->sendResponse(500, ["error" => "Ошибка подключения к базе данных: " . $e->getMessage()]);
            exit;
        }
    }

    /**
     * Главный метод для обработки входящих HTTP-запросов.
     */
    public function handleRequest() {
        if (!$this->authenticate()) {
            $this->sendResponse(401, ["error" => "Ошибка авторизации: неверный или отсутствует API-ключ."]);
            return;
        }

        $requestUri = strtok($_SERVER['REQUEST_URI'], '?');
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if ($basePath === '/' || $basePath === '\\') {
            $basePath = '';
        }
        $path = substr($requestUri, strlen($basePath));
        $requestParts = explode('/', trim($path, '/'));
        
        $resource = array_shift($requestParts) ?: null;
        $id = array_shift($requestParts) ?: null;

       switch ($resource) {
			case 'kvartals':
				$this->getKvartals($id);
				break;
			case 'homes':
				$this->getHomes($id);
				break;
			case 'apartments':
				if ($id === 'filters') {
					$this->getApartmentFilters();
				} elseif (is_numeric($id)) {
					// НОВОЕ: получение одной квартиры по ID
					$this->getApartmentById((int)$id);
				} else {
					$this->getApartments();
				}
				break;
			case '':
				$this->sendResponse(200, ["message" => "Real Estate API is running."]);
				break;
			default:
				$this->sendResponse(404, ["error" => "Эндпоинт не найден", "requested_resource" => $resource]);
				break;
		}
       
    }




	/**
	 * Получение полной информации о квартире по её ID.
	 *
	 * @param int $apartmentId ID квартиры из таблицы apartaments
	 */
	private function getApartmentById($apartmentId)
	{
		$sql = "
			SELECT
				-- Квартира (apartaments)
				a.apartament_id,
				a.home_id AS apartment_home_id,
				a.section_id,
				a.apartment_num,
				a.apartments,
				a.floor AS apartment_floor,
				a.price,
				a.price_m,
				a.area,
				a.rooms,
				a.kitchen_area,
				a.text AS apartment_text,
				a.house_adress,
				a.adress AS apartment_adress,
				a.plan_code,
				a.status,
				a.status2,
				a.status_broni_id,
				a.status_broni_date,
				a.date AS apartment_date,
				a.image_pb,
				a.image_pb_plan,
				a.image_pb_png,
				a.plan_type,
				a.image,
				a.area2,
				a.area_t,

				-- Дом (homes)
				h.homes_id,
				h.home_id AS home_internal_id,
				h.complex_domclick,
				h.corpus_code_domclick,
				h.title AS home_title,
				h.`yandex-building-id`,
				h.`yandex-house-id`,
				h.long_title,
				h.seo_title,
				h.seo_desctiption,
				h.show AS home_show,
				h.color_bg,
				h.seriya,
				h.complite_text,
				h.complite,
				h.built_year,
				h.ready_quarter,
				h.renovation,
				h.img AS home_img,
				h.description AS home_description,
				h.`order` AS home_order,
				h.broni_setup,
				h.show_keys,
				h.keys_adress,
				h.keys_message,
				h.map_mapkeys_lat,
				h.map_mapkeys_lon,
				h.map_mapkeys_adress,
				h.kvartal AS kvartal_id,
				h.homes_kvartal_id,
				h.delivery_date,
				h.lat AS home_lat,
				h.lon AS home_lon,
				h.adress AS home_adress,
				h.wallmaterial,
				h.floor AS home_floors_total,
				h.project_price,
				h.alias AS home_alias,
				h.del AS home_del,

				-- Жилой комплекс (homes_kvartal)
				hk.homes_kvartal_id AS kvartal_homes_kvartal_id,
				hk.title AS kvartal_title,
				hk.latitude AS kvartal_latitude,
				hk.longitude AS kvartal_longitude,
				hk.adress AS kvartal_adress,
				hk.description AS kvartal_description,
				hk.infrastructure_parking,
				hk.infrastructure_security,
				hk.infrastructure_fenced_area,
				hk.infrastructure_sports_ground,
				hk.infrastructure_playground,
				hk.infrastructure_school,
				hk.infrastructure_kindergarten,
				hk.complex_domclick AS kvartal_complex_domclick,
				hk.color AS kvartal_color,
				hk.bgcolor AS kvartal_bgcolor,
				hk.code AS kvartal_code,
				hk.show AS kvartal_show,
				hk.`order` AS kvartal_order,
				hk.del AS kvartal_del

			FROM apartaments a
			JOIN homes h ON a.home_id = h.home_id
			JOIN homes_kvartal hk ON h.kvartal = hk.homes_kvartal_id
			WHERE a.apartament_id = :apartment_id
			  AND (a.status = 2 OR a.status IS NULL OR a.status = 0)
			  AND h.show = 1 AND h.del = 0
			  AND hk.show = 1 AND hk.del = 0
			LIMIT 1
		";

		try {
			$stmt = $this->pdo->prepare($sql);
			$stmt->bindParam(':apartment_id', $apartmentId, PDO::PARAM_INT);
			$stmt->execute();
			$result = $stmt->fetch();

			if ($result) {
				$this->sendResponse(200, $result);
			} else {
				$this->sendResponse(404, ["error" => "Квартира не найдена или недоступна"]);
			}
		} catch (PDOException $e) {
			$this->sendResponse(500, ["error" => "Ошибка при получении квартиры: " . $e->getMessage()]);
		}
	}



    /**
     * Аутентификация запроса по заголовку 'X-API-KEY'.
     */
    private function authenticate() {
        $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? null;
        if (!$apiKey && function_exists('getallheaders')) {
            $headers = array_change_key_case(getallheaders(), CASE_UPPER);
            $apiKey = $headers['X-API-KEY'] ?? null;
        }
        return $apiKey !== null && $apiKey === $this->apiKey;
    }

    /**
     * Получение списка активных ЖК с полной агрегацией данных.
     */
    private function getKvartals($id = null) {
        $sql = "
            SELECT
                hk.*,
                COUNT(DISTINCT a.apartament_id) AS total_free_apartments
            FROM
                homes_kvartal hk
            LEFT JOIN
                homes h ON hk.homes_kvartal_id = h.kvartal AND h.show = 1 AND h.del = 0
            LEFT JOIN
                apartaments a ON h.home_id = a.home_id AND (a.status = 2 OR a.status IS NULL OR a.status = 0)
            WHERE
                hk.show = 1 AND hk.del = 0
        ";
        if ($id) {
            $sql .= " AND hk.homes_kvartal_id = :id";
        }
        $sql .= " GROUP BY hk.homes_kvartal_id ORDER BY hk.order ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            if ($id) {
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            }
            $stmt->execute();
            $result = $id ? $stmt->fetch() : $stmt->fetchAll();
            $this->sendResponse(200, $result ?: ($id ? null : []));
        } catch (PDOException $e) {
            $this->sendResponse(500, ["error" => "Ошибка при получении кварталов: " . $e->getMessage()]);
        }
    }

    /**
     * Получение списка домов или одного дома по ID/ALIAS с полной информацией.
     */
    private function getHomes($identifier = null) {
        $sql = "
            SELECT
                h.*,
                COUNT(DISTINCT a.apartament_id) as free_apartments_count
            FROM
                homes h
            LEFT JOIN
                apartaments a ON h.home_id = a.home_id AND (a.status = 2 OR a.status IS NULL OR a.status = 0)
            WHERE
                h.show = 1 AND h.del = 0
        ";
        if ($identifier !== null) {
            $sql .= " AND (h.alias = :identifier OR h.home_id = :identifier)";
        }
        $sql .= " GROUP BY h.homes_id";
        
        try {
            $stmt = $this->pdo->prepare($sql);
            if ($identifier !== null) {
                $stmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
            }
            $stmt->execute();
            $result = $identifier ? $stmt->fetch() : $stmt->fetchAll();
            $this->sendResponse(200, $result ?: ($identifier ? null : []));
        } catch (PDOException $e) {
            $this->sendResponse(500, ["error" => "Ошибка при получении домов: " . $e->getMessage()]);
        }
    }

    /**
     * Получение данных для "умных" фильтров на основе текущих выбранных параметров.
     */
    private function getApartmentFilters() {
        list($baseWhere, $params) = $this->buildFilterConditions();
        $baseFrom = $this->buildBaseFrom();
        $results = [];

        try {
            $homeSql = "SELECT h.home_id as id, COUNT(DISTINCT a.apartament_id) as count " . $baseFrom . $baseWhere . " GROUP BY h.home_id";
            $stmt = $this->pdo->prepare($homeSql);
            $stmt->execute($params);
            $results['homes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $kvartalSql = "SELECT CONCAT('kvartal_', h.kvartal) as id, COUNT(DISTINCT a.apartament_id) as count " . $baseFrom . $baseWhere . " GROUP BY h.kvartal";
            $stmt = $this->pdo->prepare($kvartalSql);
            $stmt->execute($params);
            $results['kvartals'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $roomsSql = "SELECT CAST(a.rooms AS UNSIGNED) as id, COUNT(DISTINCT a.apartament_id) as count " . $baseFrom . $baseWhere . " GROUP BY CAST(a.rooms AS UNSIGNED)";
            $stmt = $this->pdo->prepare($roomsSql);
            $stmt->execute($params);
            $results['rooms'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $sdanSql = "SELECT CASE WHEN h.complite = 1 THEN '1' ELSE '2' END as id, COUNT(DISTINCT a.apartament_id) as count " . $baseFrom . $baseWhere . " GROUP BY id";
            $stmt = $this->pdo->prepare($sdanSql);
            $stmt->execute($params);
            $results['sdan'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $this->sendResponse(200, $results);

        } catch (PDOException $e) {
            $this->sendResponse(500, ["error" => "Ошибка при получении данных для фильтров: " . $e->getMessage()]);
        }
    }

    /**
     * Получение списка квартир с пагинацией, сортировкой и общим количеством.
     */
    private function getApartments() {
        if (!defined('MAX_LIMIT')) define('MAX_LIMIT', 100);
        $limit = isset($_GET['limit']) ? abs((int)$_GET['limit']) : 15;
        if ($limit > MAX_LIMIT) $limit = MAX_LIMIT;
        if ($limit === 0) $limit = 15;
        $start = isset($_GET['start']) ? abs((int)$_GET['start']) : 0;

        list($whereClause, $params) = $this->buildFilterConditions();
        $baseFrom = $this->buildBaseFrom();

        try {
            $countSql = "SELECT COUNT(a.apartament_id) " . $baseFrom . $whereClause;
            $countStmt = $this->pdo->prepare($countSql);
            $countStmt->execute($params);
            $totalCount = (int) $countStmt->fetchColumn();

            // --- НОВАЯ ЛОГИКА: Продвинутая и безопасная сортировка ---
            $allowedSortBy = ['price', 'area', 'rooms', 'floor'];
            $sortBy = 'a.price'; // Сортировка по умолчанию
            if (!empty($_GET['sort_by']) && in_array($_GET['sort_by'], $allowedSortBy)) {
                if ($_GET['sort_by'] == 'rooms') {
                    $sortBy = 'CAST(a.rooms AS UNSIGNED)';
                } else {
                    $sortBy = 'a.' . $_GET['sort_by'];
                }
            }
            $sortOrder = 'ASC'; // Направление по умолчанию
            if (!empty($_GET['sort_order']) && strtoupper($_GET['sort_order']) === 'DESC') {
                $sortOrder = 'DESC';
            }
            
            $apartmentsSql = "SELECT a.* " . $baseFrom . $whereClause . " ORDER BY {$sortBy} {$sortOrder} LIMIT :limit OFFSET :start";
            
            $apartmentsStmt = $this->pdo->prepare($apartmentsSql);
            
            foreach ($params as $key => $val) {
                // Привязываем динамически, так как для IN(...) могут быть числовые ключи
                $apartmentsStmt->bindValue($key, $val);
            }
            $apartmentsStmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $apartmentsStmt->bindParam(':start', $start, PDO::PARAM_INT);
            
            $apartmentsStmt->execute();
            $apartments = $apartmentsStmt->fetchAll();

            $this->sendResponse(200, ['total_count' => $totalCount, 'apartments' => $apartments]);

        } catch (PDOException $e) {
            $this->sendResponse(500, ["error" => "Ошибка при получении квартир: " . $e->getMessage()]);
        }
    }

    /**
     * Вспомогательная функция для построения части FROM...JOIN.
     */
    private function buildBaseFrom() {
        return "FROM apartaments a JOIN homes h ON a.home_id = h.home_id JOIN homes_kvartal hk ON h.kvartal = hk.homes_kvartal_id";
    }

    /**
     * Вспомогательная функция для построения условий WHERE и массива параметров.
     */
    private function buildFilterConditions() {
        $conditions = [];
        $params = [];
        $conditions[] = '(a.status = 2 OR a.status IS NULL OR a.status = 0)';
        $conditions[] = 'h.show = 1 AND h.del = 0';
        $conditions[] = 'hk.show = 1 AND hk.del = 0';
        
        // --- НОВАЯ ЛОГИКА: Фильтр по кварталу, нескольким домам или одному дому ---
        if (!empty($_GET['home_id'])) {
            if (strpos($_GET['home_id'], 'kvartal_') === 0) {
                // Фильтр по кварталу
                $conditions[] = 'h.kvartal = :kvartal_id';
                $params[':kvartal_id'] = substr($_GET['home_id'], 8);
            } elseif (strpos($_GET['home_id'], ',') !== false) {
                // Фильтр по НЕСКОЛЬКИМ домам
                $home_ids = array_filter(array_map('intval', explode(',', $_GET['home_id'])));
                if (!empty($home_ids)) {
                    $placeholders = [];
                    foreach ($home_ids as $index => $id) {
                        $key = ':home_id_' . $index;
                        $placeholders[] = $key;
                        $params[$key] = $id;
                    }
                    $conditions[] = 'a.home_id IN (' . implode(',', $placeholders) . ')';
                }
            } else {
                // Фильтр по ОДНОМУ дому
                $conditions[] = 'a.home_id = :home_id';
                $params[':home_id'] = $_GET['home_id'];
            }
        }

        if (!empty($_GET['rooms_min'])) { $conditions[] = 'CAST(a.rooms AS UNSIGNED) >= :rooms_min'; $params[':rooms_min'] = $_GET['rooms_min']; }
        if (!empty($_GET['rooms_max'])) { $conditions[] = 'CAST(a.rooms AS UNSIGNED) <= :rooms_max'; $params[':rooms_max'] = $_GET['rooms_max']; }
        
        // --- НОВАЯ ЛОГИКА: Фильтр по площади ---
        if (!empty($_GET['area_min'])) { $conditions[] = 'a.area >= :area_min'; $params[':area_min'] = $_GET['area_min']; }
        if (!empty($_GET['area_max'])) { $conditions[] = 'a.area <= :area_max'; $params[':area_max'] = $_GET['area_max']; }

        if (!empty($_GET['sdan'])) {
            if ($_GET['sdan'] == '1') { $conditions[] = 'h.complite = 1'; } 
            elseif ($_GET['sdan'] == '2') { $conditions[] = '(h.complite IS NULL OR h.complite != 1)'; }
        }
        
        $whereClause = " WHERE " . implode(' AND ', $conditions);
        return [$whereClause, $params];
    }

    /**
     * Отправка ответа клиенту в формате JSON.
     */
    private function sendResponse($statusCode, $data) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}

// Создаем экземпляр API и обрабатываем запрос.
$api = new RealEstateAPI();
$api->handleRequest();