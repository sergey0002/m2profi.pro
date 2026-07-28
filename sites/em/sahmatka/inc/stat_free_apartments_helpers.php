<?php
/**
 * Helpers: отчёт «Свободные квартиры» (задача 8).
 */

if (!function_exists('stat_free_can_access')) {

function stat_free_can_access()
{
	$login = (string)($_SESSION['sh_login'] ?? '');
	return in_array($login, ['admin', 'director', 'fd', 'demo_admin'], true);
}

function stat_free_roman_quarter($q)
{
	$map = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
	$q = (int)$q;
	return $map[$q] ?? '';
}

/**
 * Формат «III 2026». Приоритет: delivery_date → ready_quarter+built_year → «—».
 */
function stat_free_format_delivery_quarter($deliveryDate, $readyQuarter = null, $builtYear = null)
{
	$deliveryDate = trim((string)$deliveryDate);
	if ($deliveryDate !== '' && $deliveryDate !== '0000-00-00') {
		$ts = strtotime($deliveryDate);
		if ($ts) {
			$month = (int)date('n', $ts);
			$year = (int)date('Y', $ts);
			$q = (int)ceil($month / 3);
			$roman = stat_free_roman_quarter($q);
			if ($roman && $year > 0) {
				return $roman . ' ' . $year;
			}
		}
	}
	$rq = (int)$readyQuarter;
	$by = (int)$builtYear;
	$roman = stat_free_roman_quarter($rq);
	if ($roman && $by > 0) {
		return $roman . ' ' . $by;
	}
	return '—';
}

/**
 * Базовый цвет типа квартир (тёмные тона на светлом фоне).
 * [светлый, тёмный] — большой разброс, чтобы градиент был заметнее.
 * @return array{0:array{0:int,1:int,2:int},1:array{0:int,1:int,2:int}}
 */
function stat_free_type_base_rgb($roomsKey)
{
	$n = (int)preg_replace('/\D+/', '', (string)$roomsKey);
	$map = [
		1 => [[102, 187, 106], [27, 94, 32]],     // зелёный
		2 => [[255, 167, 38], [191, 54, 12]],      // оранжевый
		3 => [[41, 182, 246], [1, 87, 155]],       // синий
		4 => [[149, 117, 205], [69, 39, 160]],     // фиолетовый
		5 => [[236, 64, 122], [136, 14, 79]],      // малиновый
		6 => [[38, 166, 154], [0, 77, 64]],        // бирюзовый
		7 => [[141, 110, 99], [62, 39, 35]],       // коричневый
		8 => [[120, 144, 156], [38, 50, 56]],      // серо-синий
	];
	if (isset($map[$n])) {
		return $map[$n];
	}
	return [[144, 164, 174], [55, 71, 79]];
}

/**
 * Оттенок типа: чем больше свободных %, тем темнее.
 * @return array{r:int,g:int,b:int,css:string}
 */
function stat_free_type_shade_rgb($roomsKey, $freePct)
{
	$pair = stat_free_type_base_rgb($roomsKey);
	$light = $pair[0];
	$dark = $pair[1];
	$t = max(0.0, min(100.0, (float)$freePct)) / 100.0;
	$r = (int)round($light[0] + ($dark[0] - $light[0]) * $t);
	$g = (int)round($light[1] + ($dark[1] - $light[1]) * $t);
	$b = (int)round($light[2] + ($dark[2] - $light[2]) * $t);
	return [
		'r' => $r,
		'g' => $g,
		'b' => $b,
		'css' => 'rgb(' . $r . ',' . $g . ',' . $b . ')',
	];
}

/**
 * Градиент: от светлого (мало свободных) до оттенка реального free%.
 * Конечная точка у каждой полоски своя — по факту свободных, не «на 100%».
 */
function stat_free_type_gradient_css($roomsKey, $freePct)
{
	$freePct = max(0.0, min(100.0, (float)$freePct));
	$start = stat_free_type_shade_rgb($roomsKey, 0);
	$end = stat_free_type_shade_rgb($roomsKey, $freePct);
	return 'linear-gradient(90deg, ' . $start['css'] . ' 0%, ' . $end['css'] . ' 100%)';
}

/** @deprecated */
function stat_free_heat_rgb($freePct)
{
	return stat_free_type_shade_rgb('1к', $freePct);
}

/** @deprecated */
function stat_free_heat_gradient_css($freePct)
{
	return stat_free_type_gradient_css('1к', $freePct);
}

function stat_free_escape($s)
{
	global $connection;
	$s = (string)$s;
	if (isset($connection) && $connection) {
		return mysqli_real_escape_string($connection, $s);
	}
	return addslashes($s);
}

/**
 * @return array{sdan:string,sort:string}
 */
function stat_free_filters_from_request()
{
	$sdan = (string)($_GET['sdan'] ?? 'all');
	if (!in_array($sdan, ['all', '0', '1'], true)) {
		$sdan = 'all';
	}
	$sort = (string)($_GET['sort'] ?? 'delivery_asc');
	if (!in_array($sort, ['delivery_asc', 'delivery_desc'], true)) {
		$sort = 'delivery_asc';
	}
	return ['sdan' => $sdan, 'sort' => $sort];
}

/**
 * Список домов + агрегация по типам.
 *
 * @return array{homes:array,summary:array}
 */
function stat_free_load_report(array $filters)
{
	global $mysql;

	$where = [
		'(h.del = 0 OR h.del IS NULL)',
		'h.`show` IN (1, 2, 3)',
	];
	if ($filters['sdan'] === '0') {
		$where[] = 'h.complite = 0';
	} elseif ($filters['sdan'] === '1') {
		$where[] = 'h.complite = 1';
	}
	$whereSql = implode(' AND ', $where);

	$dir = ($filters['sort'] === 'delivery_desc') ? 'DESC' : 'ASC';
	$sqlHomes = "
		SELECT
			h.home_id,
			h.title,
			h.long_title,
			h.complite,
			h.delivery_date,
			h.ready_quarter,
			h.built_year
		FROM homes h
		WHERE {$whereSql}
		ORDER BY
			(h.delivery_date IS NULL OR h.delivery_date = '0000-00-00') ASC,
			h.delivery_date {$dir},
			h.title ASC
	";
	$homeRows = $mysql->get_arr($sqlHomes);
	if (!is_array($homeRows) || !$homeRows) {
		return [
			'homes' => [],
			'room_columns' => [],
			'summary' => ['homes' => 0, 'apartments' => 0, 'sold' => 0, 'free' => 0],
		];
	}

	$homeIds = [];
	foreach ($homeRows as $hr) {
		$hid = (int)($hr['home_id'] ?? 0);
		if ($hid > 0) {
			$homeIds[] = $hid;
		}
	}
	$homeIds = array_values(array_unique($homeIds));
	if (!$homeIds) {
		return [
			'homes' => [],
			'room_columns' => [],
			'summary' => ['homes' => 0, 'apartments' => 0, 'sold' => 0, 'free' => 0],
		];
	}

	$idsSql = implode(',', $homeIds);
	$sqlStats = "
		SELECT
			a.home_id,
			CAST(TRIM(a.rooms) AS UNSIGNED) AS rooms_n,
			COUNT(*) AS total_count,
			SUM(CASE WHEN a.status2 = 3 THEN 1 ELSE 0 END) AS sold_count,
			SUM(CASE WHEN (a.status2 IS NULL OR a.status2 IN (0, 2)) THEN 1 ELSE 0 END) AS free_count
		FROM apartaments a
		WHERE a.home_id IN ({$idsSql})
		  AND TRIM(a.rooms) <> ''
		  AND CAST(TRIM(a.rooms) AS UNSIGNED) > 0
		GROUP BY a.home_id, CAST(TRIM(a.rooms) AS UNSIGNED)
	";
	$statRows = $mysql->get_arr($sqlStats);
	$byHome = [];
	if (is_array($statRows)) {
		foreach ($statRows as $sr) {
			$hid = (int)$sr['home_id'];
			$roomsN = (int)($sr['rooms_n'] ?? 0);
			if ($hid <= 0 || $roomsN <= 0) {
				continue;
			}
			$roomsKey = (string)$roomsN;
			$byHome[$hid][$roomsKey] = [
				'total' => (int)$sr['total_count'],
				'sold' => (int)$sr['sold_count'],
				'free' => (int)$sr['free_count'],
			];
		}
	}

	$homesOut = [];
	$sumApt = 0;
	$sumSold = 0;
	$sumFree = 0;
	$roomKeys = [];

	foreach ($homeRows as $hr) {
		$hid = (int)$hr['home_id'];
		$long = trim((string)($hr['long_title'] ?? ''));
		$title = trim((string)($hr['title'] ?? ''));
		$caption = $long !== '' ? $long : $title;

		$roomMap = $byHome[$hid] ?? [];
		if ($roomMap) {
			ksort($roomMap, SORT_NUMERIC);
		}

		$homeTotal = 0;
		foreach ($roomMap as $rm) {
			$homeTotal += (int)$rm['total'];
		}

		$roomsByType = [];
		foreach ($roomMap as $rooms => $rm) {
			$total = (int)$rm['total'];
			$sold = (int)$rm['sold'];
			$free = (int)$rm['free'];
			$widthPct = $homeTotal > 0 ? round($total / $homeTotal * 100, 1) : 0.0;
			$soldFillPct = $total > 0 ? round($sold / $total * 100, 1) : 0.0;
			$freeFillPct = $total > 0 ? round($free / $total * 100, 1) : 0.0;
			// «Всего» — доля типа в доме; свободно/продано — от числа квартир этого типа
			$soldLabelPct = $soldFillPct;
			$freeLabelPct = $freeFillPct;
			$col = (string)$rooms . 'к';
			$roomsByType[$col] = [
				'rooms' => $col,
				'total' => $total,
				'sold' => $sold,
				'free' => $free,
				'width_pct' => $widthPct,
				'sold_fill_pct' => $soldFillPct,
				'free_fill_pct' => $freeFillPct,
				'label_type_pct' => $widthPct,
				'label_sold_pct' => $soldLabelPct,
				'label_free_pct' => $freeLabelPct,
			];
			$roomKeys[(int)$rooms] = true;
			$sumSold += $sold;
			$sumFree += $free;
		}
		$sumApt += $homeTotal;

		$homesOut[] = [
			'home_id' => $hid,
			'caption' => $caption,
			'title' => $title,
			'complite' => (int)($hr['complite'] ?? 0),
			'delivery_date' => $hr['delivery_date'] ?? null,
			'delivery_label' => stat_free_format_delivery_quarter(
				$hr['delivery_date'] ?? null,
				$hr['ready_quarter'] ?? null,
				$hr['built_year'] ?? null
			),
			'home_total' => $homeTotal,
			'rooms_by_type' => $roomsByType,
		];
	}

	$roomColumns = array_keys($roomKeys);
	sort($roomColumns, SORT_NUMERIC);
	$roomColumns = array_map(static function ($n) {
		return (string)$n . 'к';
	}, $roomColumns);

	// Макс. всего квартир типа среди домов → 100% ширины столбца (серый трек)
	$maxTotalByType = [];
	foreach ($homesOut as $h) {
		foreach ($h['rooms_by_type'] as $col => $rm) {
			$t = (int)$rm['total'];
			if (!isset($maxTotalByType[$col]) || $t > $maxTotalByType[$col]) {
				$maxTotalByType[$col] = $t;
			}
		}
	}
	foreach ($homesOut as &$h) {
		foreach ($h['rooms_by_type'] as $col => &$rm) {
			$maxT = (int)($maxTotalByType[$col] ?? 0);
			$rm['bar_scale_pct'] = ($maxT > 0)
				? round(((int)$rm['total'] / $maxT) * 100, 1)
				: 0.0;
		}
		unset($rm);
	}
	unset($h);

	return [
		'homes' => $homesOut,
		'room_columns' => $roomColumns,
		'max_total_by_type' => $maxTotalByType,
		'summary' => [
			'homes' => count($homesOut),
			'apartments' => $sumApt,
			'sold' => $sumSold,
			'free' => $sumFree,
		],
	];
}

} // function_exists
