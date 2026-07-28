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
			'summary' => ['homes' => 0, 'apartments' => 0, 'sold' => 0, 'free' => 0],
		];
	}

	$idsSql = implode(',', $homeIds);
	$sqlStats = "
		SELECT
			a.home_id,
			TRIM(a.rooms) AS rooms,
			COUNT(*) AS total_count,
			SUM(CASE WHEN a.status2 = 3 THEN 1 ELSE 0 END) AS sold_count,
			SUM(CASE WHEN (a.status2 IS NULL OR a.status2 IN (0, 2)) THEN 1 ELSE 0 END) AS free_count
		FROM apartaments a
		WHERE a.home_id IN ({$idsSql})
		  AND TRIM(a.rooms) <> ''
		GROUP BY a.home_id, TRIM(a.rooms)
	";
	$statRows = $mysql->get_arr($sqlStats);
	$byHome = [];
	if (is_array($statRows)) {
		foreach ($statRows as $sr) {
			$hid = (int)$sr['home_id'];
			$rooms = trim((string)$sr['rooms']);
			if ($hid <= 0 || $rooms === '') {
				continue;
			}
			$byHome[$hid][$rooms] = [
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

	foreach ($homeRows as $hr) {
		$hid = (int)$hr['home_id'];
		$long = trim((string)($hr['long_title'] ?? ''));
		$title = trim((string)($hr['title'] ?? ''));
		$caption = $long !== '' ? $long : $title;

		$roomMap = $byHome[$hid] ?? [];
		if ($roomMap) {
			ksort($roomMap, SORT_NATURAL);
		}

		$homeTotal = 0;
		foreach ($roomMap as $rm) {
			$homeTotal += (int)$rm['total'];
		}

		$roomsOut = [];
		foreach ($roomMap as $rooms => $rm) {
			$total = (int)$rm['total'];
			$sold = (int)$rm['sold'];
			$free = (int)$rm['free'];
			$widthPct = $homeTotal > 0 ? round($total / $homeTotal * 100, 1) : 0.0;
			$soldFillPct = $total > 0 ? round($sold / $total * 100, 1) : 0.0;
			$soldLabelPct = $homeTotal > 0 ? round($sold / $homeTotal * 100, 1) : 0.0;
			$roomsOut[] = [
				'rooms' => $rooms,
				'total' => $total,
				'sold' => $sold,
				'free' => $free,
				'width_pct' => $widthPct,
				'sold_fill_pct' => $soldFillPct,
				'label_type_pct' => $widthPct,
				'label_sold_pct' => $soldLabelPct,
			];
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
			'rooms' => $roomsOut,
		];
	}

	return [
		'homes' => $homesOut,
		'summary' => [
			'homes' => count($homesOut),
			'apartments' => $sumApt,
			'sold' => $sumSold,
			'free' => $sumFree,
		],
	];
}

} // function_exists
