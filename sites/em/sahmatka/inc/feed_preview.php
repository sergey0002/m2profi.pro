<?php
/**
 * Превью фидов Yandex / Avito — fetch XML, flat-поля, валидация, сводка.
 * Задача #23 — sites/em/.doc/tasks/23/
 */

if (!function_exists('feed_preview_can_access')) {

function feed_preview_can_access()
{
	$login = (string)($_SESSION['sh_login'] ?? '');
	return $login === 'admin' || $login === 'demo_admin';
}

function feed_preview_origin()
{
	$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
		|| (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
		|| (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
	$scheme = $https ? 'https' : 'http';
	$host = (string)($_SERVER['HTTP_HOST'] ?? 'localhost');
	return $scheme . '://' . $host;
}

function feed_preview_h($s)
{
	return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

/**
 * @return array{ok:bool,xml:string,error:string,http:int,url:string}
 */
function feed_preview_fetch($relativePath, $query = '')
{
	$relativePath = ltrim((string)$relativePath, '/');
	$url = feed_preview_origin() . '/sahmatka/' . $relativePath;
	if ($query !== '' && $query !== null) {
		$url .= (strpos($url, '?') === false ? '?' : '&') . ltrim((string)$query, '?&');
	}

	$result = [
		'ok' => false,
		'xml' => '',
		'error' => '',
		'http' => 0,
		'url' => $url,
	];

	if (!function_exists('curl_init')) {
		$result['error'] = 'curl не доступен';
		return $result;
	}

	$host = (string)($_SERVER['HTTP_HOST'] ?? '');
	$sslVerify = !(stripos($host, '.test') !== false || stripos($host, 'localhost') !== false);

	$ch = curl_init($url);
	curl_setopt_array($ch, [
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_MAXREDIRS => 3,
		CURLOPT_CONNECTTIMEOUT => 15,
		CURLOPT_TIMEOUT => 120,
		CURLOPT_SSL_VERIFYPEER => $sslVerify,
		CURLOPT_SSL_VERIFYHOST => $sslVerify ? 2 : 0,
		CURLOPT_USERAGENT => 'm2profi-feed-preview/23',
	]);
	$body = curl_exec($ch);
	$errno = curl_errno($ch);
	$err = curl_error($ch);
	$http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);

	$result['http'] = $http;

	if ($errno) {
		$result['error'] = 'curl #' . $errno . ': ' . $err;
		return $result;
	}
	if ($http !== 200) {
		$result['error'] = 'HTTP ' . $http;
		return $result;
	}
	if (!is_string($body) || $body === '') {
		$result['error'] = 'пустой ответ';
		return $result;
	}

	$trim = ltrim($body);
	if (strpos($trim, '<?xml') !== 0
		&& strpos($trim, '<realty-feed') !== 0
		&& strpos($trim, '<Ads') !== 0
	) {
		$result['error'] = 'ответ не похож на XML фид';
		return $result;
	}

	$result['ok'] = true;
	$result['xml'] = $body;
	return $result;
}

/**
 * @return SimpleXMLElement|false
 */
function feed_preview_load_xml($xmlString)
{
	$prev = libxml_use_internal_errors(true);
	$xml = simplexml_load_string((string)$xmlString, 'SimpleXMLElement', LIBXML_NONET);
	libxml_clear_errors();
	libxml_use_internal_errors($prev);
	return $xml ?: false;
}

function feed_preview_text($node)
{
	if ($node === null || $node === false) {
		return '';
	}
	if (is_string($node) || is_numeric($node)) {
		return trim((string)$node);
	}
	return trim((string)$node);
}

/**
 * Дети с учётом default xmlns (Яндекс realty-feed).
 *
 * @param SimpleXMLElement $element
 * @return SimpleXMLElement
 */
function feed_preview_xml_children($element)
{
	$nsList = $element->getDocNamespaces(true);
	if (!empty($nsList[''])) {
		$kids = $element->children($nsList['']);
		if ($kids !== null && count($kids) > 0) {
			return $kids;
		}
	}
	$kids = $element->children();
	return $kids !== null ? $kids : $element->children();
}

/**
 * Плоский список дочерних тегов: location/address, price/value, Images/Image@url
 *
 * @param SimpleXMLElement $element
 * @return array<string,string>
 */
function feed_preview_children_flat($element, $prefix = '')
{
	$out = [];
	if (!($element instanceof SimpleXMLElement)) {
		return $out;
	}

	foreach ($element->attributes() as $an => $av) {
		$key = ($prefix === '' ? '@' : $prefix . '@') . $an;
		$out[$key] = trim((string)$av);
	}

	$children = feed_preview_xml_children($element);
	if ($children === null || count($children) === 0) {
		return $out;
	}

	foreach ($children as $name => $child) {
		$path = $prefix === '' ? (string)$name : $prefix . '/' . $name;
		$grand = feed_preview_xml_children($child);
		$hasGrand = $grand !== null && count($grand) > 0;
		$attrs = $child->attributes();
		$hasAttrs = $attrs !== null && count($attrs) > 0;

		if ($hasGrand || ($hasAttrs && trim((string)$child) === '')) {
			$nested = feed_preview_children_flat($child, $path);
			foreach ($nested as $k => $v) {
				$out[$k] = $v;
			}
			$text = trim((string)$child);
			if ($text !== '' && !$hasGrand) {
				$out[$path] = $text;
			}
		} else {
			$out[$path] = trim((string)$child);
			if ($hasAttrs) {
				foreach ($attrs as $an => $av) {
					$out[$path . '@' . $an] = trim((string)$av);
				}
			}
		}
	}

	return $out;
}

function feed_preview_field(array $fields, $key)
{
	return isset($fields[$key]) ? trim((string)$fields[$key]) : '';
}

/**
 * @return array{ok:bool,reasons:string[],fields:array,id:string,title:string,image:string,building:string}
 */
function feed_preview_validate_yandex(SimpleXMLElement $offer)
{
	$fields = feed_preview_children_flat($offer);
	$id = feed_preview_field($fields, '@internal-id');
	if ($id === '') {
		$id = (string)($offer['internal-id'] ?? '');
	}

	$image = feed_preview_field($fields, 'image');
	if ($image === '') {
		$image = feed_preview_field($fields, 'image@url');
	}
	// первое <image> если несколько (с учётом xmlns)
	if ($image === '') {
		foreach (feed_preview_xml_children($offer) as $name => $child) {
			if ((string)$name !== 'image') {
				continue;
			}
			$t = trim((string)$child);
			if ($t !== '') {
				$image = $t;
				break;
			}
			$u = trim((string)($child['url'] ?? ''));
			if ($u !== '') {
				$image = $u;
				break;
			}
		}
	}

	$building = feed_preview_field($fields, 'building-name');
	$apt = feed_preview_field($fields, 'location/apartment');
	$titleParts = array_filter([$building, $apt !== '' ? 'кв. ' . $apt : '', $id !== '' ? 'id ' . $id : '']);
	$title = implode(' · ', $titleParts);

	$reasons = [];
	$req = [
		'type' => 'type',
		'property-type' => 'property-type',
		'category' => 'category',
		'location/country' => 'location/country',
		'location/locality-name' => 'location/locality-name',
		'location/address' => 'location/address',
		'price/currency' => 'price/currency',
		'rooms' => 'rooms',
		'building-name' => 'building-name',
		'sales-agent/phone' => 'sales-agent/phone',
		'yandex-building-id' => 'yandex-building-id',
		'yandex-house-id' => 'yandex-house-id',
	];
	foreach ($req as $label => $key) {
		if (feed_preview_field($fields, $key) === '') {
			$reasons[] = 'пусто: ' . $label;
		}
	}
	if ($id === '') {
		$reasons[] = 'пусто: @internal-id';
	}
	if ($image === '') {
		$reasons[] = 'пусто: image';
	}

	$price = (float)str_replace(',', '.', feed_preview_field($fields, 'price/value'));
	if ($price <= 0) {
		$reasons[] = 'price/value ≤ 0';
	}
	$area = (float)str_replace(',', '.', feed_preview_field($fields, 'area/value'));
	if ($area <= 0) {
		$reasons[] = 'area/value ≤ 0';
	}

	$floor = (int)feed_preview_field($fields, 'floor');
	$floorsTotal = (int)feed_preview_field($fields, 'floors-total');
	if ($floor < 1) {
		$reasons[] = 'floor < 1';
	}
	if ($floorsTotal < 1) {
		$reasons[] = 'floors-total < 1';
	}
	if ($floor >= 1 && $floorsTotal >= 1 && $floor > $floorsTotal) {
		$reasons[] = 'floor > floors-total';
	}

	$state = feed_preview_field($fields, 'building-state');
	if ($state === 'unfinished') {
		if (feed_preview_field($fields, 'built-year') === '') {
			$reasons[] = 'unfinished без built-year';
		}
		if (feed_preview_field($fields, 'ready-quarter') === '') {
			$reasons[] = 'unfinished без ready-quarter';
		}
	}

	return [
		'ok' => count($reasons) === 0,
		'reasons' => $reasons,
		'fields' => $fields,
		'id' => $id,
		'title' => $title !== '' ? $title : ('offer ' . $id),
		'image' => $image,
		'building' => $building,
	];
}

/**
 * @return array{ok:bool,reasons:string[],fields:array,id:string,title:string,image:string,building:string}
 */
function feed_preview_validate_avito(SimpleXMLElement $ad)
{
	$fields = feed_preview_children_flat($ad);
	$id = feed_preview_field($fields, 'Id');
	$devId = feed_preview_field($fields, 'NewDevelopmentId');
	$rooms = feed_preview_field($fields, 'Rooms');

	$image = feed_preview_field($fields, 'Images/Image@url');
	if ($image === '' && isset($ad->Images->Image)) {
		foreach ($ad->Images->Image as $img) {
			$u = trim((string)($img['url'] ?? ''));
			if ($u !== '') {
				$image = $u;
				break;
			}
		}
	}

	$titleParts = array_filter([
		$devId !== '' ? $devId : '',
		$id !== '' ? 'Id ' . $id : '',
		$rooms !== '' ? 'Rooms ' . $rooms : '',
	]);
	$title = implode(' · ', $titleParts);

	$reasons = [];
	if ($id === '') {
		$reasons[] = 'пусто: Id';
	}
	$category = feed_preview_field($fields, 'Category');
	if ($category === '') {
		$reasons[] = 'пусто: Category';
	} elseif ($category !== 'Квартиры') {
		$reasons[] = 'Category ≠ Квартиры';
	}
	foreach (['OperationType', 'Rooms', 'Floor', 'Floors', 'Square', 'NewDevelopmentId', 'MarketType', 'HouseType', 'ContactPhone', 'Description'] as $k) {
		if (feed_preview_field($fields, $k) === '') {
			$reasons[] = 'пусто: ' . $k;
		}
	}
	if ($image === '') {
		$reasons[] = 'пусто: Images/Image@url';
	}

	$price = (int)feed_preview_field($fields, 'Price');
	if ($price <= 0) {
		$reasons[] = 'Price ≤ 0';
	}
	$square = (float)str_replace(',', '.', feed_preview_field($fields, 'Square'));
	if ($square <= 0) {
		$reasons[] = 'Square ≤ 0';
	}
	$floor = (int)feed_preview_field($fields, 'Floor');
	$floors = (int)feed_preview_field($fields, 'Floors');
	if ($floor >= 1 && $floors >= 1 && $floor > $floors) {
		$reasons[] = 'Floor > Floors';
	}
	$desc = feed_preview_field($fields, 'Description');
	if ($desc !== '' && mb_strlen($desc) > 7500) {
		$reasons[] = 'Description > 7500';
	}

	return [
		'ok' => count($reasons) === 0,
		'reasons' => $reasons,
		'fields' => $fields,
		'id' => $id,
		'title' => $title !== '' ? $title : ('Ad ' . $id),
		'image' => $image,
		'building' => $devId,
	];
}

/**
 * @param array<int,array> $items validated cards
 * @return array<string,mixed>
 */
function feed_preview_stats_yandex(array $items, $feedUrl = '', $generationDate = '')
{
	$total = count($items);
	$valid = 0;
	$invalid = 0;
	$noImage = 0;
	$emptyYandexBuilding = 0;
	$buildings = [];
	$yBuildingIds = [];
	$yHouseIds = [];
	$addresses = [];
	$rooms = [];

	foreach ($items as $it) {
		if (!empty($it['ok'])) {
			$valid++;
		} else {
			$invalid++;
		}
		$f = $it['fields'] ?? [];
		$img = (string)($it['image'] ?? '');
		if ($img === '') {
			$noImage++;
		}
		$bn = feed_preview_field($f, 'building-name');
		if ($bn !== '') {
			$buildings[$bn] = true;
		}
		$yb = feed_preview_field($f, 'yandex-building-id');
		if ($yb === '') {
			$emptyYandexBuilding++;
		} else {
			$yBuildingIds[$yb] = true;
		}
		$yh = feed_preview_field($f, 'yandex-house-id');
		if ($yh !== '') {
			$yHouseIds[$yh] = true;
		}
		$addr = feed_preview_field($f, 'location/address');
		if ($addr !== '') {
			$addresses[$addr] = true;
		}
		$r = feed_preview_field($f, 'rooms');
		$key = $r === '' ? '(пусто)' : $r;
		if (!isset($rooms[$key])) {
			$rooms[$key] = 0;
		}
		$rooms[$key]++;
	}
	ksort($rooms, SORT_NATURAL);

	return [
		'url' => $feedUrl,
		'generation_date' => $generationDate,
		'total' => $total,
		'valid' => $valid,
		'invalid' => $invalid,
		'buildings' => count($buildings),
		'yandex_building_ids' => count($yBuildingIds),
		'empty_yandex_building_id' => $emptyYandexBuilding,
		'yandex_house_ids' => count($yHouseIds),
		'addresses' => count($addresses),
		'no_image' => $noImage,
		'rooms' => $rooms,
	];
}

/**
 * @param array<int,array> $items
 * @return array<string,mixed>
 */
function feed_preview_stats_avito(array $items, $feedUrl = '', $formatVersion = '', $target = '')
{
	$total = count($items);
	$valid = 0;
	$invalid = 0;
	$noImage = 0;
	$devIds = [];
	$houseTypes = [];
	$newYes = 0;
	$newNo = 0;
	$rooms = [];

	foreach ($items as $it) {
		if (!empty($it['ok'])) {
			$valid++;
		} else {
			$invalid++;
		}
		$f = $it['fields'] ?? [];
		if ((string)($it['image'] ?? '') === '') {
			$noImage++;
		}
		$dev = feed_preview_field($f, 'NewDevelopmentId');
		if ($dev !== '') {
			$devIds[$dev] = true;
		}
		$ht = feed_preview_field($f, 'HouseType');
		if ($ht !== '') {
			$houseTypes[$ht] = true;
		}
		$nb = feed_preview_field($f, 'NewBuilding');
		if ($nb === 'yes') {
			$newYes++;
		} elseif ($nb === 'no') {
			$newNo++;
		}
		$r = feed_preview_field($f, 'Rooms');
		$key = $r === '' ? '(пусто)' : $r;
		if (!isset($rooms[$key])) {
			$rooms[$key] = 0;
		}
		$rooms[$key]++;
	}
	ksort($rooms, SORT_NATURAL);

	return [
		'url' => $feedUrl,
		'format_version' => $formatVersion,
		'target' => $target,
		'total' => $total,
		'valid' => $valid,
		'invalid' => $invalid,
		'developments' => count($devIds),
		'house_types' => count($houseTypes),
		'new_building_yes' => $newYes,
		'new_building_no' => $newNo,
		'no_image' => $noImage,
		'rooms' => $rooms,
	];
}

/**
 * Distinct building keys from items for filter select.
 *
 * @param array<int,array> $items
 * @return array<int,string>
 */
function feed_preview_building_options(array $items)
{
	$opts = [];
	foreach ($items as $it) {
		$b = trim((string)($it['building'] ?? ''));
		if ($b !== '') {
			$opts[$b] = $b;
		}
	}
	ksort($opts, SORT_NATURAL);
	return array_values($opts);
}

/**
 * @param array<int,array> $items
 * @return array<int,array>
 */
function feed_preview_filter_items(array $items, $building = '', $onlyInvalid = false)
{
	$building = trim((string)$building);
	$out = [];
	foreach ($items as $it) {
		if ($building !== '' && trim((string)($it['building'] ?? '')) !== $building) {
			continue;
		}
		if ($onlyInvalid && !empty($it['ok'])) {
			continue;
		}
		$out[] = $it;
	}
	return $out;
}

} // function_exists guard
