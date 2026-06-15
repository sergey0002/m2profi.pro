<?php

include('config.php');

 
// Получение данных о домах
$data_homes = $mysql->get_arr('
    SELECT `homes`.*, homes_kvartal.title as k_title 
    FROM `homes` 
    LEFT JOIN `homes_kvartal` ON `homes_kvartal`.`homes_kvartal_id` = `homes`.`kvartal`
    WHERE `homes`.`show` = "1" AND homes.`yandex-building-id` > 0 AND `homes`.`yandex-house-id` > 0 
    ORDER BY `homes`.`home_id`
');
$homes = array_column($data_homes, null, 'home_id');

// Получение данных о квартирах
$data_apartaments = $mysql->get_arr('
    SELECT apartaments.* , homes.title as hcaption , homes.built_year, homes.ready_quarter, homes.adress, `homes`.`yandex-house-id` , `homes`.`yandex-building-id` ,  `homes`.`complite`  
    FROM apartaments 
    LEFT JOIN `homes` ON `homes`.`home_id` = `apartaments`.`home_id`
    LEFT JOIN `homes_kvartal` ON `homes_kvartal`.`homes_kvartal_id` = `homes`.`kvartal`
    WHERE (`apartaments`.`status2`="2" OR `apartaments`.`status2`="0" OR `apartaments`.`status2`="4" OR `apartaments`.`status2`="5" OR `apartaments`.`status2`="6" OR `apartaments`.`status2`="3"  ) 
	AND `homes`.`show` = "1" AND homes.`yandex-building-id` >0 AND `homes`.`yandex-house-id` >0 
');

// Получение данных о количестве этажей
$data_floors = $mysql->get_arr('
    SELECT home_id, max(`floor`) as floor 
    FROM apartaments 
    GROUP by `apartaments`.`home_id`
');
$floors = array_column($data_floors, null, 'home_id');

  
 // Создание CSV-файла
$csv_file_path = 'realty_feed.csv';
$fp = fopen($csv_file_path, 'w');
 
  
// Заголовки CSV
$headers = [
    'Уникальный ID дома',
    'Уникальный ID квартиры',
    'Дата создания фида',
    'Адрес дома',
    'Номер квартиры',
    'Статус квартиры',
    'Название дома',
    'Секция дома',
    'Цена',
    'Количество комнат',
     'Этаж',
    'Статус строительства',
    'Планировка',
    'Площадь'
];



$headers_win1251 = array_map('iconv', array_fill(0, count($headers), 'UTF-8'), array_fill(0, count($headers), 'CP1251//TRANSLIT'), $headers);

// Запись заголовков
fputcsv($fp, $headers_win1251, ';', '"');





// Формирование данных для каждой квартиры
foreach ($data_apartaments as $result) {
    

    $result['rooms'] = preg_replace('/[^0-9]/', '', $result['rooms']);
 
    $date = date('c');

    // Формирование строки данных
    $row = [
	
		'home_id' => $result['home_id'],
        'apartament_id' => $result['apartament_id'],
		 
        'creation-date' => $date,
        'address' => $result['adress'],
		'apartment_num' => $result['apartment_num'],
		 
		'status' => $result['status'],
			 
        'building-name' => $result['hcaption'],
        'building-section' => $result['section_id'],
		'price' => $result['price'],
        'rooms' => $result['rooms'],
        'floor' => $result['floor'],
 
        'building-state' => $result['complite'],
        'image-plan' => $result['image_pb'],
		'area' => $result['area'],
    ];



	// Преобразование строки данных в кодировку Windows-1251
    $row_win1251 = array_map('iconv', array_fill(0, count($row), 'UTF-8'), array_fill(0, count($row), 'CP1251//TRANSLIT'), $row);
	
	
    fputcsv($fp, $row_win1251, ';', '"');

    # fputcsv($fp, $row);
}

fclose($fp);


header('Content-type: text/csv');
# header('Content-Disposition: attachment; filename="realty_feed.csv"');



// Читаем содержимое файла для отправки пользователю
readfile($csv_file_path);
$files[] = $csv_file_path;

multi_attach_mail('on_potapenko@bk.ru', 'Фид квартир '. $date, 'Файл с фидом во вложении', 'test@m2profi.pro',  'em-nsk.ru', $files  );


multi_attach_mail('89236470002@mail.ru', 'Фид квартир '. $date, 'Файл с фидом во вложении', 'test@m2profi.pro',  'em-nsk.ru', $files  );




/*
https://em.m2profi.pro/sahmatka/csv_feed.php - Фид CSV все дома в продаже 
 
Фид содержит данные по всем квартирам в домах "в продаже" с актуальными ценами 

Статусы квартир: 
0,2 - Свободна 
4 - Забронирована
3 - Продана 
5 - Забронирована застройщиком 
6 - Забронированна подрядчиком 	
 
+ При каждом обращении к этому URL файл отправляется на почту в виде вложенного файла 

		
*/