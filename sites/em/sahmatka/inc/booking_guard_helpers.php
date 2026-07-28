<?php
/**
 * Booking guard: кэш режима ручного бронирования по (home_id, rooms).
 * Задача 7 — sites/em/.doc/tasks/7/
 */

if (!function_exists('booking_guard_config')) {

function booking_guard_config()
{
	$cfg = (isset($GLOBALS['booking_guard']) && is_array($GLOBALS['booking_guard']))
		? $GLOBALS['booking_guard']
		: [];
	$free = array_map('intval', (array)($cfg['free_statuses'] ?? [0, 2]));
	$free = array_values(array_unique($free));
	if (!$free) {
		$free = [0, 2];
	}
	return [
		'enabled' => !empty($cfg['enabled']),
		'free_statuses' => $free,
		'free_percent_threshold' => (float)($cfg['free_percent_threshold'] ?? 10.0),
		'admin_service_panel_enabled' => !empty($cfg['admin_service_panel_enabled']),
		'admin_service_panel_logins' => (array)($cfg['admin_service_panel_logins'] ?? ['admin']),
	];
}

function booking_guard_message()
{
	return "Обратитесь в отдел продаж.\nБронирование данного типа квартир в этом доме временно производится в ручном режиме.";
}

function booking_guard_message_html()
{
	return 'Обратитесь в отдел продаж.<br>Бронирование данного типа квартир в этом доме временно производится в ручном режиме.';
}

function booking_guard_can_manage_panel()
{
	$cfg = booking_guard_config();
	if (empty($cfg['admin_service_panel_enabled'])) {
		return false;
	}
	$login = (string)($_SESSION['sh_login'] ?? '');
	return in_array($login, $cfg['admin_service_panel_logins'], true);
}

function booking_guard_can_clear_broni()
{
	$login = (string)($_SESSION['sh_login'] ?? '');
	return $login === 'admin' || $login === 'demo_admin';
}

function booking_guard_escape($s)
{
	global $connection;
	$s = (string)$s;
	if (isset($connection) && $connection) {
		return mysqli_real_escape_string($connection, $s);
	}
	return addslashes($s);
}

function booking_guard_table_exists()
{
	static $exists = null;
	if ($exists !== null) {
		return $exists;
	}
	global $mysql;
	try {
		$row = $mysql->get_arr("SHOW TABLES LIKE 'booking_guard_room'", 1);
		$exists = !empty($row);
	} catch (Throwable $e) {
		$exists = false;
	}
	return $exists;
}

/**
 * @param int[] $homeIds
 * @return array [home_id => ['rooms' => [rooms => stats], 'total'=>, 'free'=>, ...]]
 */
function booking_guard_calc_room_stats(array $homeIds)
{
	global $mysql;
	$cfg = booking_guard_config();
	$homeIds = array_values(array_unique(array_filter(array_map('intval', $homeIds))));
	$result = [];
	if (!$homeIds) {
		return $result;
	}
	$bgIn = implode(',', $cfg['free_statuses']);
	$threshold = (float)$cfg['free_percent_threshold'];
	$homeIdsSql = implode(',', $homeIds);

	$q = "
		SELECT
			r.home_id,
			r.rooms_value,
			r.total_count,
			r.free_count,
			r.booked_count,
			ROUND((r.free_count / NULLIF(r.total_count, 0)) * 100, 2) AS room_free_percent,
			ROUND((r.booked_count / NULLIF(r.total_count, 0)) * 100, 2) AS room_booked_percent,
			t.home_total_count,
			t.home_free_count,
			t.home_booked_count,
			t.home_sold_count,
			ROUND((t.home_free_count / NULLIF(t.home_total_count, 0)) * 100, 2) AS home_free_percent,
			ROUND((t.home_booked_count / NULLIF(t.home_total_count, 0)) * 100, 2) AS home_booked_percent,
			ROUND((t.home_sold_count / NULLIF(t.home_total_count, 0)) * 100, 2) AS home_sold_percent
		FROM (
			SELECT
				home_id,
				TRIM(rooms) AS rooms_value,
				COUNT(*) AS total_count,
				SUM(CASE WHEN (status2 IS NULL OR status2 IN ($bgIn)) THEN 1 ELSE 0 END) AS free_count,
				SUM(CASE WHEN (status2 IS NOT NULL AND status2 NOT IN ($bgIn) AND status2 <> 3) THEN 1 ELSE 0 END) AS booked_count
			FROM apartaments
			WHERE home_id IN ($homeIdsSql)
			GROUP BY home_id, TRIM(rooms)
			HAVING rooms_value <> ''
		) r
		INNER JOIN (
			SELECT
				home_id,
				COUNT(*) AS home_total_count,
				SUM(CASE WHEN (status2 IS NULL OR status2 IN ($bgIn)) THEN 1 ELSE 0 END) AS home_free_count,
				SUM(CASE WHEN (status2 IS NOT NULL AND status2 NOT IN ($bgIn) AND status2 <> 3) THEN 1 ELSE 0 END) AS home_booked_count,
				SUM(CASE WHEN status2 = 3 THEN 1 ELSE 0 END) AS home_sold_count
			FROM apartaments
			WHERE home_id IN ($homeIdsSql)
			GROUP BY home_id
		) t ON t.home_id = r.home_id
	";
	$rows = $mysql->get_arr($q);
	if (!is_array($rows)) {
		return $result;
	}
	foreach ($rows as $row) {
		$homeId = (int)($row['home_id'] ?? 0);
		$rooms = trim((string)($row['rooms_value'] ?? ''));
		if ($homeId <= 0 || $rooms === '') {
			continue;
		}
		if (!isset($result[$homeId])) {
			$result[$homeId] = [
				'rooms' => [],
				'total' => 0,
				'free' => 0,
				'booked' => 0,
				'sold' => 0,
				'percent' => 0.0,
				'booked_percent' => 0.0,
				'sold_percent' => 0.0,
			];
		}
		$roomFreePercent = (float)($row['room_free_percent'] ?? 0);
		$result[$homeId]['rooms'][$rooms] = [
			'total' => (int)($row['total_count'] ?? 0),
			'free' => (int)($row['free_count'] ?? 0),
			'booked' => (int)($row['booked_count'] ?? 0),
			'percent' => $roomFreePercent,
			'booked_percent' => (float)($row['room_booked_percent'] ?? 0),
			'is_manual_mode' => ($cfg['enabled'] && $roomFreePercent < $threshold),
		];
		$result[$homeId]['total'] = (int)($row['home_total_count'] ?? 0);
		$result[$homeId]['free'] = (int)($row['home_free_count'] ?? 0);
		$result[$homeId]['booked'] = (int)($row['home_booked_count'] ?? 0);
		$result[$homeId]['sold'] = (int)($row['home_sold_count'] ?? 0);
		$result[$homeId]['percent'] = (float)($row['home_free_percent'] ?? 0);
		$result[$homeId]['booked_percent'] = (float)($row['home_booked_percent'] ?? 0);
		$result[$homeId]['sold_percent'] = (float)($row['home_sold_percent'] ?? 0);
	}
	foreach ($result as $hid => $_) {
		ksort($result[$hid]['rooms'], SORT_NATURAL);
	}
	return $result;
}

/**
 * Upsert auto rows; does not overwrite is_manual_mode when source=manual.
 */
function booking_guard_sync_auto(array $stats)
{
	global $mysql;
	if (!booking_guard_table_exists()) {
		return false;
	}
	$cfg = booking_guard_config();
	$threshold = (float)$cfg['free_percent_threshold'];
	$enabled = !empty($cfg['enabled']);

	foreach ($stats as $homeId => $homeStat) {
		$homeId = (int)$homeId;
		if ($homeId <= 0 || empty($homeStat['rooms']) || !is_array($homeStat['rooms'])) {
			continue;
		}
		foreach ($homeStat['rooms'] as $rooms => $roomStat) {
			$rooms = trim((string)$rooms);
			if ($rooms === '') {
				continue;
			}
			$total = (int)($roomStat['total'] ?? 0);
			$free = (int)($roomStat['free'] ?? 0);
			$percent = (float)($roomStat['percent'] ?? 0);
			$wantManual = ($enabled && $total > 0 && $percent < $threshold) ? 1 : 0;
			$roomsEsc = booking_guard_escape($rooms);

			$existing = $mysql->get_arr(
				"SELECT booking_guard_room_id, source, is_manual_mode
				 FROM booking_guard_room
				 WHERE home_id = {$homeId} AND rooms = '{$roomsEsc}'",
				1
			);

			if ($existing && ($existing['source'] ?? '') === 'manual') {
				$mysql->get_arr(
					"UPDATE booking_guard_room SET
						free_percent = " . number_format($percent, 2, '.', '') . ",
						free_count = {$free},
						total_count = {$total},
						threshold_percent = " . number_format($threshold, 2, '.', '') . ",
						updated_at = NOW()
					 WHERE booking_guard_room_id = " . (int)$existing['booking_guard_room_id']
				);
				continue;
			}

			if ($existing) {
				$mysql->get_arr(
					"UPDATE booking_guard_room SET
						is_manual_mode = {$wantManual},
						source = 'auto',
						free_percent = " . number_format($percent, 2, '.', '') . ",
						free_count = {$free},
						total_count = {$total},
						threshold_percent = " . number_format($threshold, 2, '.', '') . ",
						set_by_user_id = NULL,
						updated_at = NOW()
					 WHERE booking_guard_room_id = " . (int)$existing['booking_guard_room_id']
				);
			} else {
				$mysql->get_arr(
					"INSERT INTO booking_guard_room
						(home_id, rooms, is_manual_mode, source, free_percent, free_count, total_count, threshold_percent, created_at, updated_at)
					 VALUES
						({$homeId}, '{$roomsEsc}', {$wantManual}, 'auto',
						 " . number_format($percent, 2, '.', '') . ", {$free}, {$total},
						 " . number_format($threshold, 2, '.', '') . ", NOW(), NOW())"
				);
			}
		}
	}
	return true;
}

/**
 * @return array [home_id][rooms] => ['is_manual_mode'=>bool, 'source'=>string]
 */
function booking_guard_load_modes(array $homeIds)
{
	global $mysql;
	$out = [];
	$homeIds = array_values(array_unique(array_filter(array_map('intval', $homeIds))));
	if (!$homeIds || !booking_guard_table_exists()) {
		return $out;
	}
	$sql = 'SELECT home_id, rooms, is_manual_mode, source FROM booking_guard_room WHERE home_id IN (' . implode(',', $homeIds) . ')';
	$rows = $mysql->get_arr($sql);
	if (!is_array($rows)) {
		return $out;
	}
	foreach ($rows as $row) {
		$hid = (int)$row['home_id'];
		$rooms = trim((string)$row['rooms']);
		$out[$hid][$rooms] = [
			'is_manual_mode' => !empty($row['is_manual_mode']),
			'source' => (string)($row['source'] ?? 'auto'),
		];
	}
	return $out;
}

function booking_guard_is_manual_mode($homeId, $rooms)
{
	global $mysql;
	$cfg = booking_guard_config();
	if (empty($cfg['enabled'])) {
		return false;
	}
	$homeId = (int)$homeId;
	$rooms = trim((string)$rooms);
	if ($homeId <= 0 || $rooms === '') {
		return false;
	}
	if (!booking_guard_table_exists()) {
		// fallback live calc for one home
		$stats = booking_guard_calc_room_stats([$homeId]);
		return !empty($stats[$homeId]['rooms'][$rooms]['is_manual_mode']);
	}
	$roomsEsc = booking_guard_escape($rooms);
	$row = $mysql->get_arr(
		"SELECT is_manual_mode FROM booking_guard_room WHERE home_id = {$homeId} AND rooms = '{$roomsEsc}'",
		1
	);
	if ($row) {
		return !empty($row['is_manual_mode']);
	}
	// no cache row — compute and sync one home
	$stats = booking_guard_calc_room_stats([$homeId]);
	booking_guard_sync_auto($stats);
	return !empty($stats[$homeId]['rooms'][$rooms]['is_manual_mode']);
}

function booking_guard_set_manual($homeId, $rooms, $enabled, $userId = null, $note = null)
{
	global $mysql;
	if (!booking_guard_table_exists()) {
		return false;
	}
	$homeId = (int)$homeId;
	$rooms = trim((string)$rooms);
	if ($homeId <= 0 || $rooms === '') {
		return false;
	}
	$enabled = $enabled ? 1 : 0;
	$userId = $userId !== null ? (int)$userId : (int)($_SESSION['sh_id'] ?? 0);
	$roomsEsc = booking_guard_escape($rooms);
	$noteEsc = $note !== null && $note !== '' ? "'" . booking_guard_escape($note) . "'" : 'NULL';
	$userSql = $userId > 0 ? (string)$userId : 'NULL';

	$existing = $mysql->get_arr(
		"SELECT booking_guard_room_id FROM booking_guard_room WHERE home_id = {$homeId} AND rooms = '{$roomsEsc}'",
		1
	);
	if ($existing) {
		$mysql->get_arr(
			"UPDATE booking_guard_room SET
				is_manual_mode = {$enabled},
				source = 'manual',
				set_by_user_id = {$userSql},
				note = {$noteEsc},
				updated_at = NOW()
			 WHERE booking_guard_room_id = " . (int)$existing['booking_guard_room_id']
		);
	} else {
		$mysql->get_arr(
			"INSERT INTO booking_guard_room
				(home_id, rooms, is_manual_mode, source, set_by_user_id, note, created_at, updated_at)
			 VALUES
				({$homeId}, '{$roomsEsc}', {$enabled}, 'manual', {$userSql}, {$noteEsc}, NOW(), NOW())"
		);
	}
	return true;
}

function booking_guard_set_auto($homeId, $rooms)
{
	global $mysql;
	if (!booking_guard_table_exists()) {
		return false;
	}
	$homeId = (int)$homeId;
	$rooms = trim((string)$rooms);
	if ($homeId <= 0 || $rooms === '') {
		return false;
	}
	$roomsEsc = booking_guard_escape($rooms);
	$mysql->get_arr(
		"UPDATE booking_guard_room SET source = 'auto', set_by_user_id = NULL, note = NULL, updated_at = NOW()
		 WHERE home_id = {$homeId} AND rooms = '{$roomsEsc}'"
	);
	$stats = booking_guard_calc_room_stats([$homeId]);
	booking_guard_sync_auto($stats);
	return true;
}

/**
 * Merge DB modes into calc stats for UI.
 */
function booking_guard_apply_modes_to_stats(array &$stats, array $modes)
{
	$cfg = booking_guard_config();
	foreach ($stats as $homeId => &$homeStat) {
		foreach ($homeStat['rooms'] as $rooms => &$roomStat) {
			if (isset($modes[$homeId][$rooms])) {
				$roomStat['is_manual_mode'] = !empty($modes[$homeId][$rooms]['is_manual_mode']);
				$roomStat['source'] = $modes[$homeId][$rooms]['source'] ?? 'auto';
			} else {
				$roomStat['is_manual_mode'] = !empty($cfg['enabled']) && !empty($roomStat['is_manual_mode']);
				$roomStat['source'] = 'auto';
			}
			if (empty($cfg['enabled'])) {
				$roomStat['is_manual_mode'] = false;
			}
		}
		unset($roomStat);
	}
	unset($homeStat);
}

} // function_exists
