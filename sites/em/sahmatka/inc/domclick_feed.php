<?php
/**
 * Сборка квартир фида Домклик из БД (как domclick_feedx.php) для XML-логики и админ-ленты.
 * Публичный URL на дом: /sahmatka/domclick-{home_id}.xml
 * Задача #23.
 */
require_once __DIR__ . '/pbplans_raster.php';
require_once __DIR__ . '/feed_fields.php';
require_once __DIR__ . '/feed_preview.php';

class em_domclick_feed
{
	private $mysql;
	private $sahmatkaDir;
	private $baseUrl = 'https://em.m2profi.pro/sahmatka/';
	/** @var em_domclick_feed_fields */
	private $fieldsSpec;

	public function __construct($mysql, $sahmatkaDir)
	{
		$this->mysql = $mysql;
		$this->sahmatkaDir = rtrim(str_replace('\\', '/', (string)$sahmatkaDir), '/') . '/';
		$this->fieldsSpec = new em_domclick_feed_fields();
	}

	public function fields_spec()
	{
		return $this->fieldsSpec;
	}

	public function floors_map()
	{
		$rows = $this->mysql->get_arr('
			SELECT home_id, MAX(`floor`) as max_floor
			FROM apartaments
			GROUP BY home_id
		');
		$floors = [];
		if (is_array($rows)) {
			foreach ($rows as $v) {
				$floors[(int)$v['home_id']] = (int)$v['max_floor'];
			}
		}
		return $floors;
	}

	private function apartments_sql(array $filters = [])
	{
		$sql = '
			SELECT apartaments.*,
				homes.title as hcaption,
				homes.long_title,
				homes.built_year,
				homes.ready_quarter,
				homes.complite,
				homes.show as home_show,
				homes.lat,
				homes.lon,
				homes.map_mapkeys_adress,
				homes.renovation as renovation_type,
				homes.kvartal as kvartal_id,
				homes.complex_domclick,
				homes.corpus_code_domclick,
				homes_kvartal.title as kvartal_title
			FROM apartaments
			LEFT JOIN homes ON homes.home_id = apartaments.home_id
			LEFT JOIN homes_kvartal ON homes_kvartal.homes_kvartal_id = homes.kvartal
			WHERE (apartaments.status = "2" OR apartaments.status = "0")
			  AND homes.show = "1"
		';
		$sql .= feed_preview_sql_and_filters($filters);
		return $sql;
	}

	/**
	 * @return array<int,array{value:string,label:string}>
	 */
	public function homes_options()
	{
		$rows = $this->mysql->get_arr('
			SELECT DISTINCT homes.home_id, homes.title,
				homes.complex_domclick, homes.corpus_code_domclick
			FROM homes
			INNER JOIN apartaments ON apartaments.home_id = homes.home_id
			WHERE homes.show = "1"
			  AND (apartaments.status = "2" OR apartaments.status = "0")
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
			$cx = trim((string)($r['complex_domclick'] ?? ''));
			$cr = trim((string)($r['corpus_code_domclick'] ?? ''));
			$label = $title;
			$bits = [];
			if ($cx !== '') {
				$bits[] = 'c:' . $cx;
			}
			if ($cr !== '') {
				$bits[] = 'b:' . $cr;
			}
			if ($bits) {
				$label .= ' — ' . implode(', ', $bits);
			} else {
				$label .= ' (нет domclick id)';
			}
			$out[] = ['value' => (string)$id, 'label' => $label];
		}
		return $out;
	}

	public function map_row(array $result, array $floors)
	{
		$skip = [];
		$homeId = (int)($result['home_id'] ?? 0);
		$id = (int)($result['apartament_id'] ?? 0);
		$complexId = trim((string)($result['complex_domclick'] ?? ''));
		$corpusId = trim((string)($result['corpus_code_domclick'] ?? ''));
		$lat = trim((string)($result['lat'] ?? ''));
		$lon = trim((string)($result['lon'] ?? ''));
		$aptNum = trim((string)($result['apartment_num'] ?? ''));
		$hcaption = trim((string)($result['hcaption'] ?? ''));
		$longTitle = trim((string)($result['long_title'] ?? ''));
		if ($longTitle === '') {
			$longTitle = $hcaption;
		}

		if ($complexId === '') {
			$skip[] = 'нет complex_domclick (не попадает в фид)';
		}
		if ($corpusId === '') {
			$skip[] = 'нет corpus_code_domclick (не попадает в фид)';
		}
		if ($lat === '') {
			$skip[] = 'нет lat (не попадает в фид)';
		}
		if ($lon === '') {
			$skip[] = 'нет lon (не попадает в фид)';
		}
		if ($aptNum === '') {
			$skip[] = 'нет apartment_num (flat не пишется в XML)';
		}

		$roomsRaw = preg_replace('/[^0-9]/', '', (string)($result['rooms'] ?? ''));
		$roomsN = (int)$roomsRaw;
		$floor = (int)($result['floor'] ?? 0);
		$floorsTotal = isset($floors[$homeId]) ? (int)$floors[$homeId] : 0;
		$area = (float)str_replace(',', '.', (string)($result['area'] ?? 0));
		$price = (int)($result['price'] ?? 0);
		if ($price <= 0) {
			$skip[] = 'цена ≤ 0';
		}

		$plan = trim((string)($result['image_pb_png'] ?? ''));
		if ($plan === '') {
			$plan = trim((string)($result['image_pb'] ?? ''));
		}
		if ($plan === '') {
			$skip[] = 'нет plan/image_pb';
		} else {
			$plan = em_pbplans_prefer_png($plan, rtrim($this->sahmatkaDir, '/'), $this->baseUrl);
			if (strpos($plan, 'http') === false && strpos($plan, '//') !== 0) {
				$plan = 'https://em.m2profi.pro/' . ltrim($plan, '/');
			}
		}

		$buildingState = !empty($result['complite']) ? 'built' : 'unfinished';
		$builtYear = !empty($result['built_year']) ? (string)(int)$result['built_year'] : '';
		$readyQuarter = !empty($result['ready_quarter']) ? (string)(int)$result['ready_quarter'] : '';
		$kitchen = trim((string)($result['kitchen_area'] ?? ''));
		if ($kitchen === '' || $kitchen === '0') {
			$kitchen = '0';
		}
		$renovation = trim((string)($result['renovation_type'] ?? ''));
		$feedUrl = feed_preview_origin() . '/sahmatka/domclick-' . $homeId . '.xml';

		$fields = $this->fieldsSpec->apply([
			'complex/id' => $complexId,
			'complex/name' => $longTitle,
			'complex/latitude' => $lat,
			'complex/longitude' => $lon,
			'complex/address' => trim((string)($result['map_mapkeys_adress'] ?? '')),
			'building/id' => $corpusId,
			'building/name' => $longTitle,
			'building/floors' => $floorsTotal > 0 ? (string)$floorsTotal : '',
			'building/building_state' => $buildingState,
			'building/built_year' => $builtYear,
			'building/ready_quarter' => $readyQuarter,
			'building/building_type' => 'панельный',
			'flat/flat_id' => $id ? (string)$id : '',
			'flat/apartment' => $aptNum,
			'flat/floor' => $floor > 0 ? (string)$floor : '',
			'flat/room' => (string)$roomsN,
			'flat/plan' => $plan,
			'flat/balcony' => 'Нет',
			'flat/renovation' => $renovation,
			'flat/price' => $price > 0 ? (string)$price : '',
			'flat/area' => $area > 0 ? number_format($area, 1, '.', '') : '',
			'flat/decoration' => '1',
			'flat/ready_housing' => '0',
			'flat/kitchen_area' => $kitchen,
			'flat/living_area' => $area > 0 ? number_format($area, 1, '.', '') : '',
			'flat/window_view' => 'На улицу',
			'flat/bathroom' => 'Раздельный',
			'feed_url' => $feedUrl,
		]);

		$reasons = $skip;
		$reasons = array_merge($reasons, $this->fieldsSpec->validate($fields));
		$reasons = array_values(array_unique($reasons));

		$titleParts = array_filter([
			$hcaption !== '' ? $hcaption : '',
			$aptNum !== '' ? 'кв. ' . $aptNum : '',
			$id ? 'id ' . $id : '',
		]);

		return [
			'ok' => count($reasons) === 0,
			'in_feed' => count($skip) === 0,
			'reasons' => $reasons,
			'fields' => $fields,
			'id' => (string)$id,
			'title' => implode(' · ', $titleParts),
			'image' => $plan,
			'building' => (string)$homeId,
			'home_id' => $homeId,
			'kvartal_id' => (int)($result['kvartal_id'] ?? 0),
			'rooms_n' => $roomsN,
			'area_n' => $area,
			'feed_url' => $feedUrl,
		];
	}

	/**
	 * @return array<int,array>
	 */
	public function collect($homeId = 0, $includeSkipped = false, array $filters = [])
	{
		if ($homeId > 0) {
			$filters['building'] = (int)$homeId;
		}
		$floors = $this->floors_map();
		$rows = $this->mysql->get_arr($this->apartments_sql($filters));
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
}
