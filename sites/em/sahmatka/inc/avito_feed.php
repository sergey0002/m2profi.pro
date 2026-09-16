<?php
/**
 * Сборка фида Avito из БД. Тот же набор объявлений для XML и админ-ленты.
 * Задача #23.
 */
require_once __DIR__ . '/pbplans_raster.php';
require_once __DIR__ . '/feed_fields.php';

class em_avito_feed
{
	private $mysql;
	private $sahmatkaDir;
	private $baseUrl = 'https://em.m2profi.pro/sahmatka/';

	public function __construct($mysql, $sahmatkaDir)
	{
		$this->mysql = $mysql;
		$this->sahmatkaDir = rtrim(str_replace('\\', '/', (string)$sahmatkaDir), '/') . '/';
	}

	public static function xml_escape($str)
	{
		return htmlspecialchars((string)$str, ENT_XML1 | ENT_QUOTES, 'UTF-8');
	}

	public static function get_house_type($material)
	{
		if (!$material) {
			return '';
		}
		$material = strtolower(trim((string)$material));
		if (strpos($material, 'кирпич') !== false) {
			return 'Кирпичный';
		}
		if (strpos($material, 'панель') !== false) {
			return 'Панельный';
		}
		if (strpos($material, 'блоч') !== false || strpos($material, 'блок') !== false) {
			return 'Блочный';
		}
		if (strpos($material, 'монолит') !== false && strpos($material, 'кирпич') !== false) {
			return 'Монолитно-кирпичный';
		}
		if (strpos($material, 'монолит') !== false) {
			return 'Монолитный';
		}
		if (strpos($material, 'дерев') !== false) {
			return 'Деревянный';
		}
		return '';
	}

	public static function parse_floors($floor_desc)
	{
		if (!$floor_desc) {
			return 15;
		}
		preg_match_all('/\d+/', (string)$floor_desc, $matches);
		if (!empty($matches[0])) {
			return max(array_map('intval', $matches[0]));
		}
		return 15;
	}

	public static function get_room_type($rooms)
	{
		$rooms = (int)$rooms;
		if ($rooms <= 0) {
			return 'Студия';
		}
		if ($rooms == 1) {
			return '1';
		}
		if ($rooms == 2) {
			return '2';
		}
		if ($rooms == 3) {
			return '3';
		}
		if ($rooms == 4) {
			return '4';
		}
		if ($rooms >= 10) {
			return '10 и более';
		}
		return (string)$rooms;
	}

	public static function get_decoration($renovation)
	{
		return feed_map_avito_decoration($renovation);
	}

	public function floors_map()
	{
		$rows = $this->mysql->get_arr('
			SELECT home_id, MAX(`floor`) as max_floor
			FROM apartaments
			GROUP BY `home_id`
		');
		$floors = [];
		if (is_array($rows)) {
			foreach ($rows as $v) {
				$floors[(int)$v['home_id']] = (int)$v['max_floor'];
			}
		}
		return $floors;
	}

	private function apartments_sql($homeId = 0)
	{
		$sql = '
			SELECT apartaments.*,
				   homes.title as hcaption,
				   homes.built_year,
				   homes.ready_quarter,
				   homes.adress as full_address,
				   homes.complite,
				   homes.lat,
				   homes.lon,
				   homes.delivery_date,
				   homes.wallmaterial,
				   homes.floor as home_floor_desc,
				   homes_kvartal.title as kvartal_title,
				   homes_kvartal.avito_complex_id as kvartal_avito_id,
				   homes.avito_id as building_avito_id,
				   homes.renovation as renovation_type
			FROM apartaments
			LEFT JOIN `homes` ON `homes`.`home_id` = `apartaments`.`home_id`
			LEFT JOIN `homes_kvartal` ON `homes_kvartal`.`homes_kvartal_id` = `homes`.`kvartal`
			WHERE (`apartaments`.`status` = "2" OR `apartaments`.`status` = "0" OR `apartaments`.`status` IS NULL)
			  AND `homes`.`show` = "1"
		';
		$homeId = (int)$homeId;
		if ($homeId > 0) {
			$sql .= ' AND apartaments.home_id = ' . $homeId;
		}
		return $sql;
	}

	/**
	 * Дома для селекта: название + avito_id.
	 *
	 * @return array<int,array{value:string,label:string}>
	 */
	public function homes_options()
	{
		$rows = $this->mysql->get_arr('
			SELECT DISTINCT homes.home_id, homes.title, homes.avito_id
			FROM homes
			INNER JOIN apartaments ON apartaments.home_id = homes.home_id
			WHERE homes.show = "1"
			  AND (apartaments.status = "2" OR apartaments.status = "0" OR apartaments.status IS NULL)
			ORDER BY homes.title, homes.home_id
		');
		$out = [];
		if (!is_array($rows)) {
			return $out;
		}
		$seen = [];
		foreach ($rows as $r) {
			$id = (int)$r['home_id'];
			if (isset($seen[$id])) {
				continue;
			}
			$seen[$id] = true;
			$title = trim((string)($r['title'] ?? ''));
			if ($title === '') {
				$title = 'Дом ' . $id;
			}
			$avito = trim((string)($r['avito_id'] ?? ''));
			$label = $title;
			if ($avito !== '') {
				$label .= ' — ' . $avito;
			} else {
				$label .= ' (нет avito_id)';
			}
			$out[] = ['value' => (string)$id, 'label' => $label];
		}
		return $out;
	}

	/**
	 * Маппинг строки БД → поля объявления + skip (как в XML-цикле).
	 *
	 * @param array $result
	 * @param array $floors
	 * @return array
	 */
	public function map_row(array $result, array $floors)
	{
		$skip = [];
		$roomsRaw = preg_replace('/[^0-9]/', '', (string)($result['rooms'] ?? ''));
		$room_type = self::get_room_type($roomsRaw);

		$floor = (int)max(1, $result['floor'] ?: 1);
		$floor_total_db = $floors[(int)$result['home_id']] ?? 0;
		$floor_total_desc = self::parse_floors($result['home_floor_desc'] ?? '');
		$floors_total = max(1, $floor_total_db, $floor_total_desc);
		$floors_total = min(99, max(1, $floors_total));

		$area = (float)$result['area'];
		if ($area < 10 || $area > 5000) {
			$skip[] = 'площадь вне 10–5000 (не попадает в фид)';
		}
		$price = (int)$result['price'];
		if ($price <= 0) {
			$skip[] = 'цена ≤ 0 (не попадает в фид)';
		}

		$is_new_building = $result['complite'] ? 'no' : 'yes';
		$is_completed = (bool)$result['complite'];
		$house_type = self::get_house_type($result['wallmaterial'] ?? '');
		$building_avito_id = trim((string)($result['building_avito_id'] ?? ''));
		if ($building_avito_id === '') {
			$skip[] = 'нет avito_id корпуса (не попадает в фид)';
		}

		$address = trim((string)($result['full_address'] ?? ''));

		$desc = 'Продается ' . $room_type . '-комнатная квартира от застройщика';
		$desc .= $is_completed ? ' в сданном доме' : ' в строящемся доме';
		$desc .= '. Этаж ' . $floor . ' из ' . $floors_total . '. Общая площадь: ' . number_format($area, 1) . ' м²';
		$desc .= ' Застройщик: ООО "Энергомонтаж".';
		$desc = mb_substr($desc, 0, 7500);

		$image_url = trim((string)($result['image_pb'] ?? ''));
		if ($image_url === '') {
			$skip[] = 'нет image_pb (не попадает в фид)';
		} else {
			$image_url = em_pbplans_prefer_png($image_url, rtrim($this->sahmatkaDir, '/'), $this->baseUrl);
			if (strpos($image_url, 'http') === false && strpos($image_url, '//') !== 0) {
				$image_url = 'https://em.m2profi.pro/' . ltrim($image_url, '/');
			}
		}

		$decoration = self::get_decoration($result['renovation_type'] ?? '');
		$hcaption = trim((string)($result['hcaption'] ?? ''));
		$id = (int)($result['apartament_id'] ?? 0);

		$kitchen = trim((string)($result['kitchen_area'] ?? ''));
		if ($kitchen === '0') {
			$kitchen = '';
		}
		$aptNum = trim((string)($result['apartment_num'] ?? ''));
		$builtYear = !empty($result['built_year']) ? (string)(int)$result['built_year'] : '';
		$lat = trim((string)($result['lat'] ?? ''));
		$lon = trim((string)($result['lon'] ?? ''));

		$catalog = feed_fields_avito_new();
		$fields = feed_fields_apply($catalog, [
			'Id' => $id ? (string)$id : '',
			'Category' => 'Квартиры',
			'OperationType' => 'Продам',
			'ContactPhone' => '+7 (383) 347-47-00',
			'CompanyName' => 'ООО "Энергомонтаж"',
			'Address' => $address,
			'Latitude' => $lat,
			'Longitude' => $lon,
			'Title' => $hcaption,
			'Description' => $desc,
			'Price' => $price > 0 ? (string)$price : '',
			'Url' => 'https://em-nsk.ru/',
			'Rooms' => $room_type,
			'ApartmentNumber' => $aptNum,
			'Square' => $area > 0 ? number_format($area, 1, '.', '') : '',
			'KitchenSpace' => $kitchen,
			'Floor' => (string)$floor,
			'Floors' => (string)$floors_total,
			'HouseType' => $house_type,
			'MarketType' => 'Новостройка',
			'NewDevelopmentId' => $building_avito_id,
			'NewBuilding' => $is_new_building,
			'PropertyRights' => 'Застройщик',
			'Decoration' => $decoration,
			'Status' => 'Квартира',
			'BuiltYear' => $builtYear,
			'Images/Image@url' => $image_url,
		]);

		$reasons = $skip;
		$reasons = array_merge($reasons, feed_fields_validate($fields, $catalog));
		$reasons = array_values(array_unique($reasons));

		$titleParts = array_filter([
			$hcaption !== '' ? $hcaption : '',
			$id ? 'Id ' . $id : '',
			$room_type !== '' ? 'Rooms ' . $room_type : '',
		]);

		return [
			'ok' => count($reasons) === 0,
			'in_feed' => count($skip) === 0,
			'reasons' => $reasons,
			'fields' => $fields,
			'id' => (string)$id,
			'title' => implode(' · ', $titleParts),
			'image' => $image_url,
			'building' => (string)(int)$result['home_id'],
			'home_id' => (int)$result['home_id'],
		];
	}

	/**
	 * @return array<int,array>
	 */
	public function collect($homeId = 0, $includeSkipped = false)
	{
		$floors = $this->floors_map();
		$rows = $this->mysql->get_arr($this->apartments_sql($homeId));
		if (!is_array($rows)) {
			return [];
		}
		$out = [];
		foreach ($rows as $result) {
			$item = $this->map_row($result, $floors);
			if (!$includeSkipped && !$item['in_feed']) {
				continue;
			}
			$out[] = $item;
		}
		return $out;
	}

	public function output_xml($homeId = 0)
	{
		$items = $this->collect($homeId, false);
		$catalog = feed_fields_avito_new();
		echo '<?xml version="1.0" encoding="utf-8"?>';
		echo "\n<Ads formatVersion=\"3\" target=\"Avito.ru\">\n";
		foreach ($items as $item) {
			$f = $item['fields'];
			echo "  <Ad>\n";
			foreach ($catalog as $meta) {
				$key = $meta['key'];
				$val = $f[$key] ?? '';
				if ($key === 'Images/Image@url') {
					echo "    <Images>\n";
					echo '      <Image url="' . self::xml_escape($val) . "\" />\n";
					echo "    </Images>\n";
					continue;
				}
				feed_xml_el($key, $val, '    ');
			}
			echo "  </Ad>\n";
		}
		echo "</Ads>";
	}
}
