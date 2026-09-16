<?php
/**
 * Сборка фида Яндекс.Недвижимость из БД. XML и админ-лента — один код.
 * Задача #23.
 */
require_once __DIR__ . '/pbplans_raster.php';
require_once __DIR__ . '/feed_fields.php';
require_once __DIR__ . '/feed_preview.php';

class em_yandex_feed
{
	private $mysql;
	private $sahmatkaDir;
	private $baseUrl = 'https://em.m2profi.pro/sahmatka/';
	/** @var em_yandex_feed_fields */
	private $fieldsSpec;

	public function __construct($mysql, $sahmatkaDir)
	{
		$this->mysql = $mysql;
		$this->sahmatkaDir = rtrim(str_replace('\\', '/', (string)$sahmatkaDir), '/') . '/';
		$this->fieldsSpec = new em_yandex_feed_fields();
	}

	public function fields_spec()
	{
		return $this->fieldsSpec;
	}

	public static function xml_escape($str)
	{
		return htmlspecialchars((string)$str, ENT_XML1 | ENT_QUOTES, 'UTF-8');
	}

	public function floors_map()
	{
		$rows = $this->mysql->get_arr('
			SELECT home_id, MAX(`floor`) as floor
			FROM apartaments
			GROUP BY home_id
		');
		$floors = [];
		if (is_array($rows)) {
			foreach ($rows as $v) {
				$floors[(int)$v['home_id']] = (int)$v['floor'];
			}
		}
		return $floors;
	}

	private function apartments_sql(array $filters = [])
	{
		$sql = 'SELECT apartaments.*,
			homes.title as hcaption,
			homes.built_year,
			homes.ready_quarter,
			homes.adress,
			homes.lat,
			homes.lon,
			homes.wallmaterial,
			homes.renovation,
			homes.delivery_date,
			homes.kvartal as kvartal_id,
			homes_kvartal.title as kvartal_title,
			`homes`.`yandex-house-id`,
			`homes`.`yandex-building-id`,
			`homes`.`complite`
		FROM apartaments
		LEFT JOIN `homes` ON `homes`.`home_id` = `apartaments`.`home_id`
		LEFT JOIN `homes_kvartal` ON `homes_kvartal`.`homes_kvartal_id` = `homes`.`kvartal`
		WHERE (`apartaments`.`status2`="2" OR `apartaments`.`status2`="0")
		  AND `homes`.`show` = "1"';
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
				`homes`.`yandex-building-id` as yb,
				`homes`.`yandex-house-id` as yh
			FROM homes
			INNER JOIN apartaments ON apartaments.home_id = homes.home_id
			WHERE homes.show = "1"
			  AND (apartaments.status2 = "2" OR apartaments.status2 = "0")
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
			$yb = trim((string)($r['yb'] ?? ''));
			$yh = trim((string)($r['yh'] ?? ''));
			$label = $title;
			$bits = [];
			if ($yb !== '') {
				$bits[] = 'b:' . $yb;
			}
			if ($yh !== '') {
				$bits[] = 'h:' . $yh;
			}
			if ($bits) {
				$label .= ' — ' . implode(', ', $bits);
			} else {
				$label .= ' (нет yandex id)';
			}
			$out[] = ['value' => (string)$id, 'label' => $label];
		}
		return $out;
	}

	public function map_row(array $result, array $floors, $date)
	{
		$homeId = (int)($result['home_id'] ?? 0);
		$id = (int)($result['apartament_id'] ?? 0);
		$buildingState = '';
		if (isset($result['complite']) && $result['complite'] !== '' && $result['complite'] !== null) {
			$buildingState = !empty($result['complite']) ? 'hand-over' : 'unfinished';
		}
		$roomsRaw = preg_replace('/[^0-9]/', '', (string)($result['rooms'] ?? ''));
		$studio = ($roomsRaw === '' || (int)$roomsRaw === 0) ? '1' : '';
		$readyQuarter = $result['ready_quarter'] ? (string)(int)$result['ready_quarter'] : '';
		$builtYear = $result['built_year'] ? (string)(int)$result['built_year'] : '';
		$floorsTotal = isset($floors[$homeId]) ? (string)(int)$floors[$homeId] : '';
		$planImg = em_pbplans_prefer_png($result['image_pb'] ?? '', rtrim($this->sahmatkaDir, '/'), $this->baseUrl);
		$hcaption = trim((string)($result['hcaption'] ?? ''));
		$address = trim((string)($result['adress'] ?? ''));
		$apartment = trim((string)($result['apartment_num'] ?? ''));
		$yb = trim((string)($result['yandex-building-id'] ?? ''));
		$yh = trim((string)($result['yandex-house-id'] ?? ''));
		$floor = (string)(int)($result['floor'] ?? 0);
		if ($floor === '0') {
			$floor = '';
		}
		$area = trim((string)($result['area'] ?? ''));
		$price = trim((string)($result['price'] ?? ''));
		$section = trim((string)($result['section_id'] ?? ''));
		$kitchen = trim((string)($result['kitchen_area'] ?? ''));
		if ($kitchen === '0') {
			$kitchen = '';
		}
		$lat = trim((string)($result['lat'] ?? ''));
		$lon = trim((string)($result['lon'] ?? ''));
		$handover = trim((string)($result['delivery_date'] ?? ''));
		if ($handover !== '' && preg_match('/^\d{4}-\d{2}-\d{2}/', $handover, $m)) {
			$handover = substr($handover, 0, 10);
		} else {
			$handover = '';
		}

		$values = [
			'@internal-id' => $id ? (string)$id : '',
			'type' => 'продажа',
			'property-type' => 'жилая',
			'category' => 'квартира',
			'url' => '',
			'creation-date' => $date,
			'last-update-date' => $date,
			'location/country' => 'Россия',
			'location/region' => 'Новосибирская область',
			'location/locality-name' => 'Новосибирск',
			'location/address' => $address,
			'location/apartment' => $apartment,
			'location/latitude' => $lat,
			'location/longitude' => $lon,
			'sales-agent/phone' => '+73833474700',
			'sales-agent/organization' => 'ООО "Энергомонтаж"',
			'sales-agent/url' => 'http://em-nsk.ru/',
			'sales-agent/category' => 'developer',
			'sales-agent/photo' => 'http://em-nsk.ru/ic/logo.png',
			'deal-status' => 'первичная продажа',
			'price/value' => $price,
			'price/currency' => 'RUR',
			'area/value' => $area,
			'area/unit' => $area !== '' ? 'кв. м' : '',
			'living-space/value' => '',
			'living-space/unit' => '',
			'kitchen-space/value' => $kitchen,
			'kitchen-space/unit' => $kitchen !== '' ? 'кв. м' : '',
			'image' => $planImg,
			'image@tag' => $planImg !== '' ? 'plan' : '',
			'renovation' => $this->fieldsSpec->map_renovation($result['renovation'] ?? ''),
			'description' => ($roomsRaw !== '' || $floor !== '')
				? ('Продается ' . $roomsRaw . ' к. кв., ' . $floor . ' этаж.')
				: '',
			'new-flat' => '1',
			'floor' => $floor,
			'rooms' => (string)$roomsRaw,
			'studio' => $studio,
			'floors-total' => $floorsTotal,
			'building-name' => $hcaption,
			'yandex-building-id' => $yb,
			'yandex-house-id' => $yh,
			'built-year' => $builtYear,
			'ready-quarter' => $readyQuarter,
			'key-handover-date' => $handover,
			'building-state' => $buildingState,
			'building-type' => $this->fieldsSpec->map_building_type($result['wallmaterial'] ?? ''),
			'building-section' => $section,
		];

		$fields = $this->fieldsSpec->apply($values);
		$reasons = $this->fieldsSpec->validate($fields);

		$titleParts = array_filter([
			$hcaption,
			$apartment !== '' ? 'кв. ' . $apartment : '',
			$id ? 'id ' . $id : '',
		]);

		return [
			'ok' => count($reasons) === 0,
			'reasons' => $reasons,
			'fields' => $fields,
			'id' => (string)$id,
			'title' => implode(' · ', $titleParts),
			'image' => $planImg,
			'building' => (string)$homeId,
			'home_id' => $homeId,
			'kvartal_id' => (int)($result['kvartal_id'] ?? 0),
			'rooms_n' => (int)$roomsRaw,
			'area_n' => (float)str_replace(',', '.', (string)($result['area'] ?? 0)),
		];
	}

	public function collect($homeId = 0, array $filters = [])
	{
		if ($homeId > 0) {
			$filters['building'] = (int)$homeId;
		}
		$date = (new DateTime())->format('c');
		$floors = $this->floors_map();
		$rows = $this->mysql->get_arr($this->apartments_sql($filters));
		if (!is_array($rows)) {
			return ['date' => $date, 'items' => []];
		}
		$items = [];
		foreach ($rows as $result) {
			$items[] = $this->map_row($result, $floors, $date);
		}
		return ['date' => $date, 'items' => $items];
	}

	public function output_xml($homeId = 0)
	{
		$pack = $this->collect($homeId);
		echo '<?xml version="1.0" encoding="utf-8"?>';
		echo "\n<realty-feed xmlns=\"http://webmaster.yandex.ru/schemas/feed/realty/2010-06\">\n";
		echo '<generation-date>' . self::xml_escape($pack['date']) . "</generation-date>\n";
		foreach ($pack['items'] as $item) {
			$this->write_offer($item['fields']);
		}
		echo "</realty-feed>\n";
	}

	private function write_offer(array $f)
	{
		$el = function ($name, $key, $indent = "\t") use ($f) {
			em_feed_fields::xml_el($name, $f[$key] ?? '', $indent);
		};
		echo '<offer internal-id="' . self::xml_escape($f['@internal-id'] ?? '') . "\">\n";
		$el('type', 'type');
		$el('property-type', 'property-type');
		$el('category', 'category');
		$el('url', 'url');
		$el('creation-date', 'creation-date');
		$el('last-update-date', 'last-update-date');
		$el('vas', 'vas');
		echo "\t<location>\n";
		$el('country', 'location/country', "\t\t");
		$el('region', 'location/region', "\t\t");
		$el('district', 'location/district', "\t\t");
		$el('locality-name', 'location/locality-name', "\t\t");
		$el('sub-locality-name', 'location/sub-locality-name', "\t\t");
		$el('address', 'location/address', "\t\t");
		$el('apartment', 'location/apartment', "\t\t");
		$el('direction', 'location/direction', "\t\t");
		$el('distance', 'location/distance', "\t\t");
		$el('latitude', 'location/latitude', "\t\t");
		$el('longitude', 'location/longitude', "\t\t");
		echo "\t\t<metro>\n";
		$el('name', 'location/metro/name', "\t\t\t");
		$el('time-on-foot', 'location/metro/time-on-foot', "\t\t\t");
		$el('time-on-transport', 'location/metro/time-on-transport', "\t\t\t");
		echo "\t\t</metro>\n";
		echo "\t</location>\n";
		echo "\t<sales-agent>\n";
		$el('name', 'sales-agent/name', "\t\t");
		$el('phone', 'sales-agent/phone', "\t\t");
		$el('category', 'sales-agent/category', "\t\t");
		$el('organization', 'sales-agent/organization', "\t\t");
		$el('url', 'sales-agent/url', "\t\t");
		$el('email', 'sales-agent/email', "\t\t");
		$el('photo', 'sales-agent/photo', "\t\t");
		echo "\t</sales-agent>\n";
		$el('deal-status', 'deal-status');
		echo "\t<price>\n";
		$el('value', 'price/value', "\t\t");
		$el('currency', 'price/currency', "\t\t");
		$el('unit', 'price/unit', "\t\t");
		echo "\t</price>\n";
		echo "\t<discount>\n";
		$el('final-price', 'discount/final-price', "\t\t");
		$el('end-date', 'discount/end-date', "\t\t");
		echo "\t</discount>\n";
		echo "\t<area>\n";
		$el('value', 'area/value', "\t\t");
		$el('unit', 'area/unit', "\t\t");
		echo "\t</area>\n";
		$living = trim((string)($f['living-space/value'] ?? ''));
		$livingUnit = trim((string)($f['living-space/unit'] ?? ''));
		if ($living !== '' || $livingUnit !== '') {
			echo "\t<living-space>\n";
			$el('value', 'living-space/value', "\t\t");
			$el('unit', 'living-space/unit', "\t\t");
			echo "\t</living-space>\n";
		}
		$kitchen = trim((string)($f['kitchen-space/value'] ?? ''));
		$kitchenUnit = trim((string)($f['kitchen-space/unit'] ?? ''));
		if ($kitchen !== '' || $kitchenUnit !== '') {
			echo "\t<kitchen-space>\n";
			$el('value', 'kitchen-space/value', "\t\t");
			$el('unit', 'kitchen-space/unit', "\t\t");
			echo "\t</kitchen-space>\n";
		}
		$tag = $f['image@tag'] ?? '';
		$tagAttr = $tag !== '' ? ' tag="' . self::xml_escape($tag) . '"' : '';
		echo "\t<image{$tagAttr}>" . self::xml_escape($f['image'] ?? '') . "</image>\n";
		$el('is-image-order-change-allowed', 'is-image-order-change-allowed');
		$el('renovation', 'renovation');
		$el('description', 'description');
		echo "\t<video-review>\n";
		$el('youtube-video-review-url', 'video-review/youtube-video-review-url', "\t\t");
		$el('rutube-video-review-url', 'video-review/rutube-video-review-url', "\t\t");
		echo "\t</video-review>\n";
		$el('online-show', 'online-show');
		echo "\t<virtual-tour>\n";
		$el('model-url', 'virtual-tour/model-url', "\t\t");
		$el('provider', 'virtual-tour/provider', "\t\t");
		$el('preview-url', 'virtual-tour/preview-url', "\t\t");
		echo "\t</virtual-tour>\n";
		$el('new-flat', 'new-flat');
		$el('floor', 'floor');
		$el('rooms', 'rooms');
		$el('rooms-type', 'rooms-type');
		$el('apartments', 'apartments');
		$el('studio', 'studio');
		$el('open-plan', 'open-plan');
		$el('balcony', 'balcony');
		$el('window-view', 'window-view');
		$el('floor-covering', 'floor-covering');
		$el('bathroom-unit', 'bathroom-unit');
		$el('floors-total', 'floors-total');
		$el('building-name', 'building-name');
		$el('yandex-building-id', 'yandex-building-id');
		$el('yandex-house-id', 'yandex-house-id');
		$el('built-year', 'built-year');
		$el('ready-quarter', 'ready-quarter');
		$el('key-handover-date', 'key-handover-date');
		$el('building-state', 'building-state');
		$el('building-phase', 'building-phase');
		$el('building-type', 'building-type');
		$el('building-series', 'building-series');
		$el('building-section', 'building-section');
		$el('ceiling-height', 'ceiling-height');
		$el('lift', 'lift');
		$el('rubbish-chute', 'rubbish-chute');
		$el('guarded-building', 'guarded-building');
		$el('parking', 'parking');
		$el('is-elite', 'is-elite');
		echo "</offer>\n";
	}
}
