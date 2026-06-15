<?php
header('Content-type: application/xml; charset=utf-8');

include('config.php');

$home_id = isset($_GET['home_id']) ? (int)$_GET['home_id'] : null;

// Получаем ВСЕ необходимые данные за один запрос, только для домов с show=1 и нужным статусом квартир
$data_apartments = $mysql->get_arr('
    SELECT apartaments.*, 
           homes.title as hcaption, 
           homes.built_year, 
           homes.ready_quarter, 
           homes.adress as full_address,
           homes.complite,
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
');

// Максимальные этажи по домам
$data_floors = $mysql->get_arr('
    SELECT home_id, MAX(`floor`) as max_floor 
    FROM apartaments 
    GROUP BY `home_id`
');
$floors = [];
foreach ($data_floors as $v) {
    $floors[$v['home_id']] = (int)$v['max_floor'];
}

// Функция экранирования
function xml_escape($str) {
    return htmlspecialchars((string)$str, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

// Определение типа дома по материалу
function get_house_type($material) {
    if (!$material) return 'Панельный';
    
    $material = strtolower(trim($material));
    
    if (strpos($material, 'кирпич') !== false) {
        return 'Кирпичный';
    } elseif (strpos($material, 'панель') !== false) {
        return 'Панельный';
    } elseif (strpos($material, 'блоч') !== false || strpos($material, 'блок') !== false) {
        return 'Блочный';
    } elseif (strpos($material, 'монолит') !== false && strpos($material, 'кирпич') !== false) {
        return 'Монолитно-кирпичный';
    } elseif (strpos($material, 'монолит') !== false) {
        return 'Монолитный';
    } elseif (strpos($material, 'дерев') !== false) {
        return 'Деревянный';
    }
    
    return 'Панельный';
}

// Определение этажности из описания
function parse_floors($floor_desc) {
    if (!$floor_desc) return 15;
    
    preg_match_all('/\d+/', $floor_desc, $matches);
    
    if (!empty($matches[0])) {
        return max(array_map('intval', $matches[0]));
    }
    
    return 15;
}

// Определение типа квартиры
function get_room_type($rooms) {
    $rooms = (int)$rooms;
    if ($rooms <= 0) return 'Студия';
    if ($rooms == 1) return '1';
    if ($rooms == 2) return '2';
    if ($rooms == 3) return '3';
    if ($rooms == 4) return '4';
    if ($rooms >= 10) return '10 и более';
    return (string)$rooms;
}

// Определение отделки
function get_decoration($renovation) {
    if (!$renovation) return 'Чистовая'; // по умолчанию чистовая отделка
    
    $renovation = strtolower(trim($renovation));
    if (strpos($renovation, 'чистовая') !== false || strpos($renovation, 'под ключ') !== false) {
        return 'Чистовая';
    } elseif (strpos($renovation, 'предчистовая') !== false) {
        return 'Предчистовая';
    }
    return 'Чистовая'; // по умолчанию
}

print '<?xml version="1.0" encoding="utf-8"?>';
?>
<Ads formatVersion="3" target="Avito.ru">
<?php
foreach ($data_apartments as $result) {
    if ($home_id && $result['home_id'] != $home_id) {
        continue;
    }

    // 1. КОЛИЧЕСТВО КОМНАТ
    $rooms = preg_replace('/[^0-9]/', '', $result['rooms']);
    $room_type = get_room_type($rooms);

    // 2. ЭТАЖИ
    $floor = (int)max(1, $result['floor'] ?: 1);
    $floor_total_db = $floors[$result['home_id']] ?? 0;
    $floor_total_desc = parse_floors($result['home_floor_desc'] ?? '');
    $floors_total = max(1, $floor_total_db, $floor_total_desc);
    $floors_total = min(99, max(1, $floors_total)); // Гарантируем диапазон 1-99

    // 3. ПЛОЩАДЬ И ЦЕНА
    $area = (float)$result['area'];
    if ($area < 10 || $area > 5000) continue;
    
    $price = (int)$result['price'];
    if ($price <= 0) continue;

    // 4. СТАТУС ДОМА
    $is_new_building = $result['complite'] ? 'no' : 'yes'; // 'no' для сданных, 'yes' для строящихся
    $is_completed = $result['complite'] ? true : false;    // true если дом сдан

    // 5. ТИП ДОМА
    $house_type = get_house_type($result['wallmaterial'] ?? '');

    // 6. ID КОРПУСА В АВИТО
    $building_avito_id = trim($result['building_avito_id'] ?? '');
    if (empty($building_avito_id)) {
        continue; // Пропускаем, если нет ID корпуса
    }

    // 7. АДРЕС
    $address = trim($result['full_address'] ?? '');
    if (empty($address)) {
        $address = 'Россия, Новосибирская область, г. Новосибирск';
    }

    // 8. ОПИСАНИЕ
    $desc = "Продается {$room_type}-комнатная квартира от застройщика";
    $desc .= $is_completed ? " в сданном доме" : " в строящемся доме";
    $desc .= ". Этаж {$floor} из {$floors_total}. Общая площадь: " . number_format($area, 1) . " м²";
    
    if (!empty($result['hcaption'])) {
        //$desc .= ". Корпус: " . $result['hcaption'];
    }
    
    // Срок сдачи только для строящихся домов
    if (!$is_completed) {
        $built_year = $result['built_year'] ?: date('Y');
        $ready_quarter = $result['ready_quarter'] ?: 1;
        $quarter_text = ['1 квартал', '2 квартал', '3 квартал', '4 квартал'][$ready_quarter-1] ?? '4 квартал';
        // $desc .= ". Срок сдачи: {$quarter_text} {$built_year} года.";
    } else {
        $built_year = $result['built_year'] ?: date('Y');
        //$desc .= ". Дом сдан в {$built_year} году.";
    }
    
    $desc .= " Застройщик: ООО \"Энергомонтаж\".";
    
    // Обрезаем до 7500 символов
    $desc = mb_substr($desc, 0, 7500);

    // 9. ИЗОБРАЖЕНИЕ - выводим оригинальный путь без изменений
    //$image_url = $result['image_pb'] ?? 'https://em-nsk.ru/ic/logo.png';
	
// Оригинальный путь к изображению
$image_url = $result['image_pb'] ?? '';

// Пропускаем объявление, если нет изображения
if (empty($image_url)) {
    continue;
}

$base_url = 'https://em.m2profi.pro/sahmatka/';
$base_path = __DIR__ . '/'; // Убедитесь, что это правильный путь к корню сайта

// Приводим URL к относительному пути
if (strpos($image_url, $base_url) === 0) {
    $relative_path = substr($image_url, strlen($base_url));
} elseif (strpos($image_url, 'http') === 0) {
    // Внешний URL — не можем проверить файл на сервере
    continue;
} else {
    $relative_path = ltrim($image_url, '/');
}
// print $relative_path;
// Работаем только с .svg
if ( pathinfo($relative_path, PATHINFO_EXTENSION) === 'svg') {
    // Заменяем директорию: pbplans → pbplans_jpg
    $jpg_relative_path = str_replace('pbplans/', 'pbplans_jpg/', $relative_path);
    
    // Заменяем .svg на ..jpg (две точки!)
    $jpg_relative_path = preg_replace('/\.svg$/i', '..jpg', $jpg_relative_path);
    
    $jpg_local_path = $base_path . $jpg_relative_path;

    if (file_exists($jpg_local_path)) {
        $image_url = $base_url . ltrim($jpg_relative_path, '/');
    } else {
		  $image_url = $base_url . ltrim($jpg_relative_path, '/');
        // JPG-файл не найден → пропускаем объявление
          continue;
    }
} else {
    // Не SVG — используем как есть, но делаем абсолютным
    if (strpos($image_url, 'http') !== 0) {
        $image_url = $base_url . ltrim($relative_path, '/');
    }
}


	 
	
	
	
	
    // Не преобразуем .svg в .jpg, выводим оригинальный путь
    if (strpos($image_url, 'http') === false && strpos($image_url, '//') !== 0) {
        // Если путь относительный - добавляем базовый URL
        $image_url = 'https://em.m2profi.pro/' . ltrim($image_url, '/');
    }
    
    // 10. ОТДЕЛКА - по умолчанию "Чистовая"
    $decoration = get_decoration($result['renovation_type'] ?? '');
	
	
 

?>
  <Ad>
    <Id><?= (int)$result['apartament_id'] ?></Id>
    <OperationType>Продам</OperationType>
    <ObjectType>Квартира</ObjectType>
    <Category>Квартиры</Category>
    <Price><?= $price ?></Price>
    <ContactPhone>+7 (383) 347-47-00</ContactPhone>
    <CompanyName>ООО "Энергомонтаж"</CompanyName>
    <Description><?= xml_escape($desc) ?></Description>
    <Url>https://em-nsk.ru/</Url>

     <Rooms><?= xml_escape($room_type) ?></Rooms>
    <Floor><?= $floor ?></Floor>
    <Floors><?= $floors_total ?></Floors>
    <Square><?= number_format($area, 1, '.', '') ?></Square>
    <Status>Квартира</Status>

     <MarketType>Новостройка</MarketType>
    <HouseType><?= xml_escape($house_type) ?></HouseType>
    <NewBuilding><?= $is_new_building ?></NewBuilding>
    <NewDevelopmentId><?= xml_escape($building_avito_id) ?></NewDevelopmentId>
    <PropertyRights>Застройщик</PropertyRights>
    <Decoration><?= xml_escape($decoration) ?></Decoration>

     <Images>
      <Image url="<?= xml_escape($image_url) ?>" />
    </Images>
  </Ad>
<?php
}
?>
</Ads>