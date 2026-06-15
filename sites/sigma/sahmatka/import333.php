<?php
// header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
// header("Last-Modified: " . gmdate("D, d M Y H:i:s")." GMT");
// header("Cache-Control: no-cache, must-revalidate");
// header("Cache-Control: post-check=0,pre-check=0", false);
// header("Cache-Control: max-age=0", false);
// header("Pragma: no-cache");

include('config.php');
function import333_get_base_url()
{
	$is_https = (
		(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
		|| (isset($_SERVER['SERVER_PORT']) && (string) $_SERVER['SERVER_PORT'] === '443')
		|| (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
	);

	$scheme = $is_https ? 'https' : 'http';
	$host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'hotel.m2profi.pro';

	return $scheme . '://' . $host;
}

function import333_get_mode()
{
	$mode = isset($_GET['mode']) ? trim((string) $_GET['mode']) : '';

	if ($mode === '') {
		return 'apartaments';
	}

	return $mode;
}

function import333_is_broni_sync_mode()
{
	return in_array(import333_get_mode(), array('sync_broni', 'broni_only'), true);
}

function import333_normalize_broni_status($status)
{
	$status = (int) $status;

	if ($status === 0) {
		return 2;
	}

	return $status;
}

function import333_get_apartment_row($home_id, $apartment_num)
{
	global $connection;

	$home_id = (int) $home_id;
	$apartment_num = (int) $apartment_num;

	$query = '
		SELECT apartament_id, home_id, apartment_num, status2, status_broni_id
		FROM apartaments
		WHERE home_id = "' . $home_id . '" AND apartment_num = "' . $apartment_num . '"
		LIMIT 1
	';

	$result = mysqli_query($connection, $query);
	if (!$result) {
		return null;
	}

	$row = mysqli_fetch_assoc($result);
	mysqli_free_result($result);

	return $row ?: null;
}

function import333_get_latest_broni_row($home_id, $apartment_num)
{
	global $connection;

	$home_id = (int) $home_id;
	$apartment_num = (int) $apartment_num;

	$query = '
		SELECT broni_id, status, date
		FROM broni
		WHERE home_id = "' . $home_id . '" AND apartments_num = "' . $apartment_num . '"
		ORDER BY date DESC, broni_id DESC
		LIMIT 1
	';

	$result = mysqli_query($connection, $query);
	if (!$result) {
		return null;
	}

	$row = mysqli_fetch_assoc($result);
	mysqli_free_result($result);

	return $row ?: null;
}

function import333_sync_broni_state($home_id)
{
	global $connection;

	$home_id = (int) $home_id;

	$queries = array(
		'
		UPDATE `broni` AS t1
		INNER JOIN (
			SELECT apartament_id, home_id, apartment_num
			FROM apartaments
			WHERE home_id = "' . $home_id . '"
		) AS t2 ON t1.home_id = t2.home_id AND t1.apartments_num = t2.apartment_num
		SET t1.apartament_id = t2.apartament_id
		WHERE t1.home_id = "' . $home_id . '";
		',
		'
		UPDATE apartaments AS t1
		INNER JOIN (
			SELECT broni.broni_id, broni.status, broni.date, broni.home_id, broni.apartments_num
			FROM broni
			INNER JOIN (
				SELECT home_id, apartments_num, MAX(date) AS max_date
				FROM broni
				WHERE home_id = "' . $home_id . '"
				GROUP BY home_id, apartments_num
			) AS latest ON latest.home_id = broni.home_id
				AND latest.apartments_num = broni.apartments_num
				AND latest.max_date = broni.date
			WHERE broni.home_id = "' . $home_id . '"
		) AS t2 ON t1.home_id = t2.home_id AND t1.apartment_num = t2.apartments_num
		SET
			t1.status_broni_id = t2.broni_id,
			t1.status_broni_date = t2.date,
			t1.status = t2.status,
			t1.status2 = t2.status
		WHERE t1.home_id = "' . $home_id . '";
		',
	);

	foreach ($queries as $query) {
		mysqli_query($connection, $query);
	}
}

$array = file(__DIR__ . '/4.txt');
$i = 0;
$mode = import333_get_mode();
$is_broni_sync_mode = import333_is_broni_sync_mode();
$sync_stats = array(
	'processed' => 0,
	'applied' => 0,
	'skipped_missing_apartment' => 0,
	'skipped_same_status' => 0,
);
$sa = null;

if ($is_broni_sync_mode) {
	$sa = new sahmatka($_SESSION, $connection);
}

foreach ($array as $k => $v) {
	$str_arr = explode("\t", $v);

	print_r($str_arr);
	// Предварительная обработка значений
	foreach ($str_arr as $k2 => $v2) {
		$v2 = str_replace('"', '', $v2);
		$v2 = str_replace("\t", '', $v2);
		$v2 = preg_replace("/(^\s+)|(\s+$)/us", "", $v2);
		$str_arr[$k2] = $v2;
	}

	$str_arr3['home_id'] = 60;
	$str_arr3['section_id'] = $str_arr[2];
	$str_arr3['apartment_num'] = (int) $str_arr[3];
	$str_arr3['floor'] = $str_arr[0];
	$str_arr3['price'] = $str_arr[6];
	$str_arr3['area'] = $str_arr[4];
	$str_arr3['rooms'] = $str_arr[1];
	$str_arr3['status2'] = isset($str_arr[7]) ? (int) $str_arr[7] : 2;
	$allowed_statuses = array(0, 2, 3, 4, 5, 6);
	if (!in_array($str_arr3['status2'], $allowed_statuses, true)) {
		$str_arr3['status2'] = 2;
	}
	$str_arr3['status'] = $str_arr3['status2'];
	$a = str_replace(',', '.', $str_arr3['area']);

	if ($str_arr[0] == 1) {
		$floor_imgdir = '1';
	} elseif ($str_arr[0] == 2) {
		$floor_imgdir = '2';
	} elseif ($str_arr[0] > 2) {
		if ($str_arr3['section_id'] == 1) {
			$floor_imgdir = '3-17';
		} elseif ($str_arr3['section_id'] == 2 || $str_arr3['section_id'] == 3) {
			$floor_imgdir = '3-10';
		}
	}
	if ($str_arr[0] > 10) {
		if ($str_arr3['section_id'] == 2 || $str_arr3['section_id'] == 3) {
			$floor_imgdir = '11-17';
		}
	} else {
		$floor_imgdir = $str_arr[0];
	}

	$a = str_ireplace('.', '.', $a);

	$str_arr3['image_pb'] = import333_get_base_url() . '/sahmatka/pbplans/' . $str_arr3['home_id'] . '/' . $str_arr[2] . '/' . $floor_imgdir . '/' . $a . '.svg';

	if ($is_broni_sync_mode) {
		$sync_stats['processed']++;
		$apartment_row = import333_get_apartment_row($str_arr3['home_id'], $str_arr3['apartment_num']);

		if (!$apartment_row) {
			$sync_stats['skipped_missing_apartment']++;
			print 'SKIP apartment not found: home_id=' . $str_arr3['home_id'] . ', apartment_num=' . $str_arr3['apartment_num'] . '<br>';
			continue;
		}

		$latest_broni = import333_get_latest_broni_row($str_arr3['home_id'], $str_arr3['apartment_num']);
		$target_status = import333_normalize_broni_status($str_arr3['status2']);
		$current_status = $latest_broni
			? import333_normalize_broni_status($latest_broni['status'])
			: import333_normalize_broni_status($apartment_row['status2'] ?? 2);

		$need_new_broni = false;

		if ($latest_broni) {
			$need_new_broni = ($current_status !== $target_status);
		} elseif ($target_status !== 2 || $current_status !== 2) {
			$need_new_broni = true;
		}

		if (!$need_new_broni) {
			$sync_stats['skipped_same_status']++;
			print 'OK status unchanged: home_id=' . $str_arr3['home_id'] . ', apartment_num=' . $str_arr3['apartment_num'] . ', status=' . $target_status . '<br>';
			continue;
		}

		$sa->new_broni($str_arr3['home_id'], $str_arr3['apartment_num'], $target_status, 0);
		$sync_stats['applied']++;

		print 'SYNC broni: home_id=' . $str_arr3['home_id'] . ', apartment_num=' . $str_arr3['apartment_num'] . ', from=' . $current_status . ', to=' . $target_status . '<br>';
		continue;
	}

	// /home/m2profi/web/m2profi.pro/public_html/sites/em/sahmatka/pbplans/34
	// https://em.m2profi.pro/sahmatka/pbplans/34/3/

	#3 для ООО

	$sql = 'INSERT INTO `apartaments` (`home_id`, `section_id`, `apartment_num`, `floor`, `price`, `area`, `rooms`, `kitchen_area`, `text`, `house_adress`, `adress`, `image_pb`, `status`, `status2`)
VALUES (
"' . $str_arr3['home_id'] . '",
"' . $str_arr3['section_id'] . '",
"' . $str_arr3['apartment_num'] . '",
"' . preg_replace('~\D+~', '', $str_arr3['floor']) . '",
"' . $str_arr3['price'] . '",
"' . $str_arr3['area'] . '",
"' . $str_arr3['rooms'] . '",
"0",
"' . ($str_arr3['text'] ?? '') . '",
"' . ($str_arr3['house_adress'] ?? '') . '",
"' . ($str_arr3['adress'] ?? '') . '",
 "' . $str_arr3['image_pb'] . '",
 "' . $str_arr3['status'] . '",
 "' . $str_arr3['status2'] . '")
ON DUPLICATE KEY UPDATE
`section_id` = VALUES(`section_id`),
`floor` = VALUES(`floor`),
`price` = VALUES(`price`),
`area` = VALUES(`area`),
`rooms` = VALUES(`rooms`),
`image_pb` = VALUES(`image_pb`),
`status` = VALUES(`status`),
`status2` = VALUES(`status2`); ';

	# $mysql->sql( $sql); 
	/*
	// Обновление / клонирование строки
	INSERT INTO `apartaments` (`home_id`, `section_id`, `apartment_num`, `apartments`, `floor`, `price`, `price_m`, `area`, `rooms`, `kitchen_area`, `text`, `house_adress`, `adress`, `plan_code`, `status`, `status2`, `status_broni_id`, `status_broni_date`, `date`, `image_pb`, `plan_type`, `image`, `area2`, `area_t`)
	SELECT '34', '2', '110', '0', '1', '5200000', NULL, '64.60', '3с', '0', '', '', '', NULL, '3', '3', '30086', '2021-10-21 10:01:25', NULL, 'https://example.test/sahmatka/pbplans/32/2/64.6.svg', NULL, NULL, NULL, NULL
	FROM `apartaments`
	WHERE ((`apartament_id` = '20870'));
	(0.015 s)
	*/
	print $sql;

	print '<br><br>';

	/*
	print  $sql ="
UPDATE `apartaments` SET

`price` = '".$str_arr3['price']."',
`section_id` = '".$str_arr3['section_id']."',


 `image_pb` = '".$str_arr3['image_pb']."',
`plan_code` = '".$str_arr3['area']."'
WHERE `home_id` = '".$str_arr3['home_id']."' AND apartment_num='".$str_arr3['apartment_num']."'
 ;";

	*/

	/*

	1 Секция: нет в нарезке 3к квартир 64,70 метров (4-17 этаж)
	*/

	// UPDATE apartaments SET image_pb = REPLACE(image_pb, 'https://em-nsk.ru/sahmatka/pbplans/', 'https://example.test/sahmatka/pbplans/') where apartaments.home_id=33

	// Добавляем запись!
	// $query = mysqli_query($connection, $sql);

	// $mysql->sql( 'DELETE FROM `apartaments` WHERE `home_id` = "'.$str_arr3['home_id'].'";');
	// DELETE FROM `apartaments` WHERE ((`home_id` = '34'));

	$str_arr = $str_arr2 ?? array();

	print '<pre>';
	print_r($str_arr3);
	print '<pre>';

	if ($i > 10000) {
		break;
	}
	$i++;
}

if ($is_broni_sync_mode) {
	import333_sync_broni_state(60);

	print '<hr>';
	print 'Mode: ' . htmlspecialchars($mode, ENT_QUOTES, 'UTF-8') . '<br>';
	print 'Processed: ' . (int) $sync_stats['processed'] . '<br>';
	print 'Applied: ' . (int) $sync_stats['applied'] . '<br>';
	print 'Skipped missing apartment: ' . (int) $sync_stats['skipped_missing_apartment'] . '<br>';
	print 'Skipped same status: ' . (int) $sync_stats['skipped_same_status'] . '<br>';
}

// print '<h1>M</h1>';
// print $xx;
