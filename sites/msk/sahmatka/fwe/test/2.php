<?php
header('Content-Type: text/html; charset=utf-8')
  wer
  werwwer
  print all asdasdqwe
  wer
  wr;
asdd
  ini_set('max_execution_time', 10800); // 3 часа
ini_set('upload_max_size', '800M');
ini_set('post_max_size', '800M');
ini_set('memory_limit', '2000M');
ignore_user_abort(true);
set_time_limit(0);
// Отключаем буферизацию вывода
ini_set('output_buffering', 'Off');
ini_set('zlib.output_compression', 'Off');
ini_set('proxy_buffering', 'Off');aasdad
  ini_set('gzip', 'Off');
$GLOBALS['starttime'] = microtime(true);
$hd = getallheaders();
@ob_flush();
@flush();
// Создаем очищаем лог
$x='';
$fp = fopen($_SERVER['DOCUMENT_ROOT'] . '/upload/exchange/exportlog.html', "w");
fwrite($fp, "" . $x);
fclose($fp);
function logmessage($txt)
{
  global $starttime;
  $filename = $_SERVER['DOCUMENT_ROOT'] . '/upload/exchange/exportlog.html';
  $data = 'test string';
  $x = '';
  $x .= date('[Y-m-d H:i:s]') . ' : ';
  // $x.= ' : work :'.microtime(true) - $GLOBALS['starttime'].' сек : ';
  $x .= $txt;
  $x .= '<br/>';
  if ($_GET['dev']) {
	print $x;
  }
  $fp = fopen($filename, "a");
  fwrite($fp, "\r\n" . $x);
  fclose($fp);
  @flush();
}
//var_dump($hd);
$p_key = '';
$uploaddir = $_SERVER['DOCUMENT_ROOT'] . '/upload/exchange/';
if (!isset($_GET['dev'])) {
  $_GET['dev'] = false;
}
if (!$_GET['dev']) {
  if (!isset($_POST['json']) && !isset($_GET['json']) || isset($_POST['zip'])) {
	header("HTTP/1.0 400 Bad Request");
	logmessage('ОШИБКА - Нет POST[jsoon] или GET[jsoon] или POST[zip]');
	exit();
  }
  if (!isset($_POST['key']) || $_POST['key'] != 'lXMNqT9PSP8mupyP101alZrl2') {
	logmessage('ОШИБКА - не верный  rest api key - "'.$_POST['key'].'"' );
	#header("HTTP/1.0 404 Not Found");
	#echo '{"result":false, "error": "Не указан \'key\'"}';
	#exit();
  }else{
	logmessage(' верный  rest api key - "'.$_POST['key'].'"' );
  }
  $o = null;
  try {
	if ($_POST['json'] == 'file' || isset($_GET['json'])) {
	  $lle = file_get_contents($uploaddir. 'json.txt');
	  $o = json_decode($lle);
	} else {
	  $o = json_decode($_POST['json']); //parse json file
	}
  } catch (Exception $e) {
	logmessage('ОШИБКА загрузки POST[jsoon]  ');
	header("HTTP/1.0 401 Bad Request");
	echo "fas2";
	exit();
  }
}
logmessage('Начата выгрузка сессия '.time());
// ОБработка изображений
if (!$_GET['dev'] || $_GET['dev'] == 2) {
  logmessage('<h1>Обработка изображений</h1>');
  // Очищаем папку img
  logmessage('<h2>Очищаем папку /img/</h2>');
  if (file_exists($uploaddir . '/img/')) {
	foreach (glob($uploaddir . '/img/*') as $file) {
	  @unlink($file);
	  //logmessage('Удаление файла '.$file);
	}
  }
  logmessage('<h2>Создаем "' . $uploaddir . '" если нет</h2>');
  // Создаем $uploaddir если нет
  if (!is_dir($uploaddir)) {
	mkdir($uploaddir, 0777, true);
  }
  $uploadfile = $uploaddir . "Picture.zip";
  logmessage('<h2>Распаковка архива изображений</h2>');
  // Разархивация
  $zip = new ZipArchive;
  if ($zip->open($uploadfile) === TRUE) {
	$zip->extractTo($uploaddir . '/img/');
	$zip->close();
	//unlink ($uploadfile);
	logmessage('<h2>Архив разпакован</h2>');
  } else {
	echo 'Архив поврежден';
	exit();
  }
  if ($_GET['dev']) {
	logmessage('<h2>Архив распакован</h2>');
  }
}
logmessage('<h1>Работа с jsoon</h1>');
$lle = file_get_contents($uploaddir . 'json.txt');
logmessage('<h2>Файл прочитан</h2>');
logmessage('<h2>Декодировка jsoon в объект</h2>');
$o = json_decode($lle);
if ($o == null) {
  header("HTTP/1.0 402 Bad Request");
  echo "fas3";
  exit();
} else {
  logmessage('<h2>Файл декодирован</h2>');
}
if (isset($o->remove)) {
  for ($q = 0; $q < count($o->remove); $q++) {
	$rs = Core_DataBase::instance()->setQueryType(3)->query(
	  "DELETE FROM `search_pages` WHERE `module_value_id` IN (SELECT `id` FROM `shop_groups` WHERE `guid` = '" . $o->remove[$q] . "'); DELETE FROM `shop_groups` WHERE `guid` = '" . $o->remove[$q] . "'"
	);
	//$rs = Core_DataBase::instance()->setQueryType(3)->query("DELETE FROM `shop_price_entries`, `shop_warehouse_entries`, `shop_warehouse_items`, `shop_warehouse_inventory_items`, `shop_warehouse_entry_accumulates`, `shop_price_setting_items`  WHERE `shop_item_id` IN (SELECT `id` FROM `shop_items` WHERE `guid` = '".$o->remove[$q]."')");			
	$rs = Core_DataBase::instance()->setQueryType(3)->query("DELETE FROM `shop_items` WHERE `guid` = '" . $o->remove[$q] . "'");
	$rs = Core_DataBase::instance()->setQueryType(3)->query("DELETE FROM `shop_groups` WHERE `guid` = '" . $o->remove[$q] . "'");
	$rs = Core_DataBase::instance()->setQueryType(3)->query("DELETE FROM `shop_producers` WHERE `guid` = '" . $o->remove[$q] . "'");
  }
}
if (isset($o->groups)) {
  $rs = Core_DataBase::instance()->setQueryType(0)->query('SELECT `id`, `guid` FROM `shop_groups`');
  $ls = ['' => ''];
  while ($r = $rs->asAssoc()->current()) {
	$ls[$r['guid']] = $r['id'];
  }
  $stage = 0;
  logmessage('<h1>Обработка групп (рубрик) - ' . count($o->groups) . '</h1> ');
  for ($q = 0; $q < count($o->groups); $q++) {
	$active_d7 = 1;
	$par_to = 0;
	$e = $o->groups[$q];
	logmessage('<hr/><h2>Обработка группы #' . $q . ' - ' . $e->name . '</h2>');
	if (!isset($e->parent) || $e->parent == "" || $e->parent == "dd46f2ab-30ca-11e9-aa5a-e0d55ebd205d") {
	  $par_to = 1;
	  if (!isset($e->ico) || $e->ico == false) {
		$active_d7 = 1;
	  }
	}
	if (isset($e->less) && $e->less) {
	  $active_d7 = 0;
	} else {
	  $active_d7 = 1;
	}
	$img = '';
	$img2 = "''";
	if ($e->img != null && $e->img != false) {
	  $fl_p = get_putch($ls[$e->id], 'group') . $e->img;
	  move_file($e->img, $fl_p);
	  $img = "`image_large` = '" . $e->img . "', `image_small` = '" . $e->img . "',";
	  $img2 = "'" . $e->img . "'";
	}
	if ($stage == 0 && !isset($ls[$e->id])) {
	  logmessage('<h2>нет группы с id ' . $e->id . ' добавляем</h2>');
	  $sql = "INSERT INTO `shop_groups`(`shop_id`, `name`, `image_large`, `image_small`, `siteuser_group_id`, `path`, `user_id`, `guid`, `active`, `sorting`) VALUES (1, '" . $e->name . "', " . $img2 . ", " . $img2 . ", '-1', '" . translit_sef($e->name) . "', '20', '" . $e->id . "', '" . $active_d7 . "', '" . $q . "')";
	  logmessage($sql . '!');
	  $rs = Core_DataBase::instance()->setQueryType(1)->query($sql);
	} elseif ($stage == 0) {
	  logmessage('<h2>Группа есть на сайте - обновление ' . $e->id . '   ЭТАП 0</h2>');
	  $sql = " UPDATE `shop_groups` SET `name` = '" . $e->name . "', " . $img . " `path` = '" . translit_sef($e->name) . "', `active` = '" . $active_d7 . "', `sorting` = '" . $q . "' WHERE `guid` = '" . $e->id . "' ";
	  logmessage($sql);
	  $rs = Core_DataBase::instance()->setQueryType(2)->query($sql);
	}
	if ($stage == 1) {
	  logmessage(
		'<h2>  ' . $e->id . ' + ЭТАП 1 (расстановка родителей делается после добавления и редактирвоания всех групп)</h2>'
	  );
	  if (isset($ls[$e->parent])) {
		$sql = " UPDATE `shop_groups` SET `parent_id` = '" . (($par_to == 1) ? '0' : $ls[$e->parent]) . "' WHERE `guid` = '" . $e->id . "' ";
		logmessage($sql);
		$rs = Core_DataBase::instance()->setQueryType(2)->query($sql);
	  } else {}
	}
	if ($stage == 0 && $q == count($o->groups) - 1) {
	  //if($_GET['dev']){print '<h2>Последняя группа  '.$e->id.' обновляем массив существующий групп в памяти  ЭТАП 0</h2>';}
	  $q = 0;
	  $stage = 1;
	  $rs = Core_DataBase::instance()->setQueryType(0)->query('SELECT `id`, `guid` FROM `shop_groups`');
	  $ls = ['' => ''];
	  while ($r = $rs->asAssoc()->current()) {
		$ls[$r['guid']] = $r['id'];
	  }
	}
	//Иконки групп
	if (isset($e->ico) && $e->ico != false) {
	  if (!$_GET['dev'] || $_GET['dev'] == 2) // только если разархивируется архив 
	  {
		logmessage('Обработка иконок групп');
		move_file($e->ico, get_putch($ls[$e->id], 'group') . 'ico_' . $e->ico);
		Core_DataBase::instance()->setQueryType(3)->query("DELETE FROM `property_value_files` WHERE `entity_id` = '" . $ls[$e->id] . "'");
		$rs = Core_DataBase::instance()->setQueryType(1)->query(
		  "INSERT INTO `property_value_files`(`property_id`, `entity_id`, `file`, `file_name`) VALUES ('338', '" . $ls[$e->id] . "', 'ico_" . $e->ico . "', '" . $e->ico . "')"
		);
	  }
	}
  }//for
  logmessage('<h2>Группы обработанны</h2>');
}// КОнец обработки групп
if (isset($o->brands)) {
  for ($q = 0; $q < count($o->brands); $q++) {
	$rs = Core_DataBase::instance()->setQueryType(0)->query('SELECT `id`, `guid` FROM `shop_producers`');
	$ls = [];
	while ($r = $rs->asAssoc()->current()) {
	  $ls[$r['guid']] = $r['id'];
	}
	for ($q = 0; $q < count($o->brands); $q++) {
	  $e = $o->brands[$q];
	  if ($e->img != false) {
		move_file($e->img, "/upload/shop_1/producers/" . $e->img);
	  }
	  if (isset($ls[$e->id])) {
		$rs = Core_DataBase::instance()->setQueryType(2)->query(
		  "UPDATE `shop_producers` SET `name` = '" . $e->name . "', `path` = '" . translit_sef($e->name) . "', `image_large` = '" . $e->img . "', `image_small` = '" . $e->img . "' WHERE `guid` = '" . $e->id . "'"
		);
	  } else {
		$rs = Core_DataBase::instance()->setQueryType(1)->query(
		  "INSERT INTO `shop_producers` (`name`, `path`, `image_large`, `image_small`, `guid`, `shop_id`, `user_id`) VALUES ('" . $e->name . "', '" . translit_sef($e->name) . "',  '" . $e->img . "', '" . $e->img . "', '" . $e->id . "', '1', '19')"
		);
	  }
	}
  }
}
if (isset($o->products)) {
  logmessage('<h2>Обработка товаров  - ' . count($o->products) . '</h2>');
  $rs = Core_DataBase::instance()->setQueryType(0)->query('SELECT `id`, `guid` FROM `shop_items`');
  $ls = [];
  if (isset($rs) && $rs != null && $rs != false) {
	while ($r = $rs->asAssoc()->current()) {
	  $ls[$r['guid']] = $r['id'];
	}
  }
  $rs = Core_DataBase::instance()->setQueryType(0)->query('SELECT `id`, `guid` FROM `shop_producers`');
  $br = [];
  while ($r = $rs->asAssoc()->current()) {
	$br[$r['guid']] = $r['id'];
  }	
  //var_dump(count ($o->products));
  for ($q = 0; $q < count($o->products); $q++) {
	$e = $o->products[$q];
	logmessage('<hr/><h1>Обработка товара #' . $q . ' - ' . $e->name . '</h1>');
	if ($e->name == "") {
	  continue;
	}
	if (gettype($e->unit) != 'object') {
	  logmessage(
		"Ошибка проверки целостности данных, значение не является объектом 'products[" . $q . "].unit'"
	  );
	  error(
		"Ошибка проверки целостности данных, значение не является объектом 'products[" . $q . "].unit'"
	  );
	}
	$id_1 = Core_DataBase::instance()->setQueryType(0)->query("SELECT `id` FROM `shop_measures` WHERE `okei` = '" . $e->unit->code . "'");
	if ($id_1 == null) {
	  $id_1 = 0;
	} else {
	  $id_1 = $id_1->asObject()->current()->id;
	}
	$bgr = Core_DataBase::instance()->setQueryType(0)->query("SELECT `id` FROM `shop_groups` WHERE `guid` = '" . $e->group . "'");
	$bgr = (isset($bgr) && $bgr != false) ? $bgr->asObject() : false;
	$z = [
	  "shop_id" => 1,
	  "marking" => $e->vendor,
	  "vendorcode" => $e->code,
	  "shop_producer_id" => ($e->brand != "") ? $br[$e->brand] : "",
	  "name" => str_replace(["\r", "\n", "'"], "", $e->name),
	  "shop_measure_id" => $id_1,
	  "shop_group_id" => (isset($bgr) && $bgr != false) ? $bgr->current()->id : 0,
	  "image_large" => $e->img,
	  "image_small" => $e->img,
	  "text" => str_replace(["\r", "\n", "'"], '', str_replace("\n", "<br>", $e->description)),
	  "max_quantity" => $e->instock,
	  "price" => 0,
	  "guid" => $e->id,
	  "path" => translit_sef($e->code . '-' . $e->name)
	];
	if (isset($ls[$e->id])) {
	  $sql = "UPDATE `shop_items` SET " . simple_sql(0, $z) . " WHERE `guid` = '" . $e->id . "'";
	  logmessage($sql);
	  $rs = Core_DataBase::instance()->setQueryType(2)->query($sql);
	  if ($e->img != false) {
		logmessage(
		  'ДОБАВЛЯЕМ ИЗОБРАЖЕНИЕ ДЛЯ СУЩЕСТВУЮЩЕГО ТОВАРА ' . $e->img . ' - guid=' . $e->id
		);
		move_file($e->img, get_putch($ls[$e->id], 'item') . $e->img);
	  }
	} else {
	  $sql = "INSERT INTO `shop_items` " . simple_sql(1, $z);
	  logmessage($sql);
	  $rs = Core_DataBase::instance()->setQueryType(1)->query($sql);
	  if ($e->img != false) {
		logmessage('ДОБАВЛЯЕМ ИЗОБРАЖЕНИЕ ДЛЯ НОВОГО ТОВАРА ' . $e->img . ' - ' . $rs->getInsertId());
		move_file($e->img, get_putch($rs->getInsertId(), 'item') . $e->img);
	  }
	  $ls[$e->id] = $rs->getInsertId();
	}
	logmessage('Цены');
	for ($aq = 0; $aq < count($e->price); $aq++) {
	  $sql="INSERT INTO `shop_item_prices_n` (`id`, `type`, `value`) VALUES ('" . $ls[$e->id] . "', '" . $e->price[$aq]->key . "', '" . $e->price[$aq]->value . "') ON DUPLICATE KEY UPDATE `value` = '" . $e->price[$aq]->value . "'";
	  logmessage($sql);
	  Core_DataBase::instance()->setQueryType(1)->query($sql);
	}
	$isnot_eny = false;
	if (isset($e->properties)) {
	  logmessage('Обрабатываем properties ');
	  $rs = Core_DataBase::instance()->setQueryType(3)->query("DELETE FROM `property_value_ints` WHERE `entity_id` = '" . $ls[$e->id] . "'");
	  for ($w = 0; $w < count($e->properties); $w++) {
		$r = $e->properties[$w];
		if ($r->name == 'Не выгружать на сайт') {
		  logmessage('Не выгружать не сайт');
		  if ($r->value == 'Да' || $r->value == 'да' || $r->value == 'true') {
			$isnot_eny = true;
		  }
		  continue;
		}
		$ids = [];
		$rs = Core_DataBase::instance()->setQueryType(0)->query("SELECT `id` FROM `lists` WHERE `name` = '" . $r->name . "'")->asObject()->current();
		if (isset($rs) && $rs !== false) {
		  $rs = $rs->id;
		  //var_dump($rs);
		  $ids[0] = $rs;
		} else {
		  $rs = Core_DataBase::instance()->setQueryType(1)->query("INSERT INTO `lists` (`name`, `user_id`, `site_id`) VALUES ('" . $r->name . "', '19', '1')");
		  $ids[0] = $rs->getInsertId();
		}
		$rs = Core_DataBase::instance()->setQueryType(0)->query("SELECT `id` FROM `list_items` WHERE `value` = '" . $r->value . "'")->asObject()->current();
		if (isset($rs) && $rs !== false) {
		  $rs = $rs->id;
		  //var_dump($rs);
		  $ids[1] = $rs;
		} else {
		  $rs = Core_DataBase::instance()->setQueryType(1)->query("INSERT INTO `list_items` (`value`, `user_id`, `list_id`) VALUES ('" . $r->value . "', '19', '" . $ids[0] . "')");
		  $ids[1] = $rs->getInsertId();
		}
		$rs = Core_DataBase::instance()->setQueryType(1)->query(
		  "INSERT INTO `property_value_ints` (`property_id`, `entity_id`, `value`) VALUES ('" . $ids[0] . "', '" . $ls[$e->id] . "', '" . $ids[1] . "')"
		);
	  }
	}
	if ($isnot_eny) {
	  logmessage('НЕ АКТИВЕН');
	  $sql = "UPDATE `shop_items` SET `active` = '0' WHERE `guid` = '" . $e->id . "'";
	  logmessage($sql);
	  Core_DataBase::instance()->setQueryType(2)->query($sql);
	} else {
	  logmessage('АКТИВЕН');
	  $sql = "UPDATE `shop_items` SET `active` = '1' WHERE `guid` = '" . $e->id . "'";
	  logmessage($sql);
	  Core_DataBase::instance()->setQueryType(2)->query($sql);
	}
  }
}
echo '{"finish": true}';
exit();
function simple_sql($type, $arr)
{
  $key = array_keys($arr);
  $s = '';
  if ($type == 0) { //update
	for ($q = 0; $q < count($key); $q++) {
	  $s .= " `" . $key[$q] . "` = '" . $arr[$key[$q]] . "'";
	  if ($q < count($key) - 1) {
		$s .= ",";
	  }
	}
  } else {	//insert
	$s .= " (";
	$b = '';
	for ($q = 0; $q < count($key); $q++) {
	  $s .= " `" . $key[$q] . "`";
	  $b .= "'" . $arr[$key[$q]] . "'";
	  if ($q < count($key) - 1) {
		$s .= ",";
		$b .= ",";
	  }
	}
	$s .= " ) VALUES (" . $b . ")";
  }
  return $s;
}
function translit_sef($value)
{
  $converter = array(
	'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
	'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
	'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
	'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
	'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
	'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
	'э' => 'e',    'ю' => 'yu',   'я' => 'ya',
  );
  $value = mb_strtolower($value);
  $value = strtr($value, $converter);
  $value = mb_ereg_replace('[^-0-9a-z]', '-', $value);
  $value = mb_ereg_replace('[-]+', '-', $value);
  $value = trim($value, '-');
  return $value;
}
function get_putch($id, $type)
{
  $id = $id . "";
  $ida = $id;
  $s = "/upload/shop_1/";
  $id = str_split($id);
  for ($q = 0; $q < 3; $q++) {
	$s .= $id[$q] . "/";
  }
  $s .= $type . "_" . $ida . "/";
  if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $s)) {
	mkdir($_SERVER['DOCUMENT_ROOT'] . $s, 0777, true);
  }
  return $s;
}
function move_file($file, $putch)
{
  $updir = $_SERVER['DOCUMENT_ROOT'] . '/upload/exchange/';
  //var_dump([$_SERVER['DOCUMENT_ROOT'] ."". $putch, $updir."img/".$file]);
  if (file_exists($updir . "img/" . $file)) {
	if (file_exists($_SERVER['DOCUMENT_ROOT'] . "" . $putch)) {
	  logmessage('Файл существует - перезаписсываем! "' . $_SERVER['DOCUMENT_ROOT'] . "" . $putch . '" ');
	  @unlink($_SERVER['DOCUMENT_ROOT'] . $putch); // Удаляем файл и перезаписываем
	}
	if (copy($updir . "img/" . $file, $_SERVER['DOCUMENT_ROOT'] . "" . $putch)) {
	  // logmessage('Файл скопирован "'.$updir."img/".$file.'" в дирректорию "'.$_SERVER['DOCUMENT_ROOT'] ."". $putch.'" '); 
	} else {
	  logmessage(
		'ОШИБКА - Не удалось скопировать файл "' . $updir . "img/" . $file . '" в дирректорию "' . $_SERVER['DOCUMENT_ROOT'] . "" . $putch . '" '
	  );
	}
  } else {
	logmessage('ОШИБКА Файл НЕ НАЙДЕН "' . $updir . "img/" . $file . '" ');
  }
}
function error($e)
{
  echo '{"result": false, "error": "' . $e . '"}';
  exit();
}
