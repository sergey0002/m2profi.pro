<?php
/**
 * Каталог полей фидов новостроек + валидация по формату.
 * Яндекс: https://yandex.ru/support/realty/ru/feed/requirements-sale-new
 * Авито: автозагрузка категория «Квартиры» / новостройка (formatVersion 3).
 */

function feed_fields_tooltip(array $meta)
{
	$tip = (string)($meta['title'] ?? $meta['key']);
	if (!empty($meta['required'])) {
		$tip .= ' [обязательное]';
	}
	$tip .= '. ' . (string)($meta['help'] ?? '');
	if (!empty($meta['enum']) && is_array($meta['enum'])) {
		$tip .= ' Допустимо: ' . implode(', ', $meta['enum']) . '.';
	}
	if (!empty($meta['format'])) {
		$tip .= ' Формат: ' . $meta['format'] . '.';
	}
	return $tip;
}

function feed_fields_index(array $catalog)
{
	$out = [];
	foreach ($catalog as $row) {
		$row['tooltip'] = feed_fields_tooltip($row);
		$out[$row['key']] = $row;
	}
	return $out;
}

function feed_fields_empty_map(array $catalog)
{
	$out = [];
	foreach ($catalog as $row) {
		$out[$row['key']] = '';
	}
	return $out;
}

function feed_fields_apply(array $catalog, array $values)
{
	$out = feed_fields_empty_map($catalog);
	foreach ($values as $k => $v) {
		if (array_key_exists($k, $out)) {
			$out[$k] = is_scalar($v) || $v === null ? trim((string)$v) : '';
		}
	}
	return $out;
}

function feed_fields_validate(array $fields, array $catalog)
{
	$reasons = [];
	$idx = feed_fields_index($catalog);
	foreach ($catalog as $meta) {
		$key = $meta['key'];
		$val = isset($fields[$key]) ? trim((string)$fields[$key]) : '';
		if (!empty($meta['required']) && $val === '') {
			$reasons[] = 'обязательно пусто: ' . $key;
			continue;
		}
		if ($val === '') {
			continue;
		}
		if (!empty($meta['enum']) && is_array($meta['enum'])) {
			$ok = false;
			$valNorm = mb_strtolower($val);
			foreach ($meta['enum'] as $ev) {
				if ($val === $ev || $valNorm === mb_strtolower((string)$ev)) {
					$ok = true;
					break;
				}
			}
			if (!$ok) {
				$reasons[] = $key . ': значение не из формата («' . $val . '»)';
			}
		}
		if (!empty($meta['pattern']) && !preg_match($meta['pattern'], $val)) {
			$reasons[] = $key . ': неверный формат';
		}
		if (!empty($meta['numeric'])) {
			$n = (float)str_replace(',', '.', $val);
			if ($n <= 0) {
				$reasons[] = $key . ': должно быть > 0';
			}
		}
	}
	$floor = (int)($fields['floor'] ?? $fields['Floor'] ?? 0);
	$floors = (int)($fields['floors-total'] ?? $fields['Floors'] ?? 0);
	if ($floor > 0 && $floors > 0 && $floor > $floors) {
		$reasons[] = 'этаж больше этажности дома';
	}
	return $reasons;
}

function feed_xml_esc($s)
{
	return htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function feed_xml_el($name, $value, $indent = "\t")
{
	echo $indent . '<' . $name . '>' . feed_xml_esc($value) . '</' . $name . ">\n";
}

/** @return array<int,array> */
function feed_fields_yandex_new()
{
	$boolYn = ['да', 'нет', 'true', 'false', '1', '0', '+', '-'];
	$boolY = ['да', 'true', '1', '+'];
	return [
		['key' => '@internal-id', 'title' => 'internal-id', 'required' => 1, 'help' => 'Уникальный id оффера в вашей БД (атрибут offer).', 'format' => 'буквы/цифры'],
		['key' => 'type', 'title' => 'type', 'required' => 1, 'help' => 'Тип сделки.', 'enum' => ['продажа']],
		['key' => 'property-type', 'title' => 'property-type', 'required' => 1, 'help' => 'Тип недвижимости.', 'enum' => ['жилая']],
		['key' => 'category', 'title' => 'category', 'required' => 1, 'help' => 'Категория объекта.', 'enum' => ['квартира', 'flat', 'дом', 'house', 'таунхаус', 'townhouse']],
		['key' => 'url', 'title' => 'url', 'required' => 0, 'help' => 'URL страницы объявления.'],
		['key' => 'creation-date', 'title' => 'creation-date', 'required' => 1, 'help' => 'Дата создания объявления.', 'format' => 'YYYY-MM-DDTHH:mm:ss+00:00'],
		['key' => 'last-update-date', 'title' => 'last-update-date', 'required' => 0, 'help' => 'Дата обновления.', 'format' => 'ISO 8601'],
		['key' => 'vas', 'title' => 'vas', 'required' => 0, 'help' => 'Платное продвижение.', 'enum' => ['premium', 'raise', 'promotion']],
		['key' => 'location/country', 'title' => 'location/country', 'required' => 1, 'help' => 'Страна. Только Россия.', 'enum' => ['Россия']],
		['key' => 'location/region', 'title' => 'location/region', 'required' => 0, 'help' => 'Регион (старый формат адреса).'],
		['key' => 'location/district', 'title' => 'location/district', 'required' => 0, 'help' => 'Район.'],
		['key' => 'location/locality-name', 'title' => 'location/locality-name', 'required' => 1, 'help' => 'Населённый пункт.'],
		['key' => 'location/sub-locality-name', 'title' => 'location/sub-locality-name', 'required' => 0, 'help' => 'Район города.'],
		['key' => 'location/address', 'title' => 'location/address', 'required' => 1, 'help' => 'Улица и номер здания.'],
		['key' => 'location/apartment', 'title' => 'location/apartment', 'required' => 0, 'help' => 'Номер квартиры.'],
		['key' => 'location/direction', 'title' => 'location/direction', 'required' => 0, 'help' => 'Шоссе (загород).'],
		['key' => 'location/distance', 'title' => 'location/distance', 'required' => 0, 'help' => 'Расстояние до города, км.'],
		['key' => 'location/latitude', 'title' => 'location/latitude', 'required' => 0, 'help' => 'Широта (новый формат адреса).'],
		['key' => 'location/longitude', 'title' => 'location/longitude', 'required' => 0, 'help' => 'Долгота.'],
		['key' => 'location/metro/name', 'title' => 'metro/name', 'required' => 0, 'help' => 'Ближайшее метро.'],
		['key' => 'location/metro/time-on-foot', 'title' => 'metro/time-on-foot', 'required' => 0, 'help' => 'Минут пешком до метро.'],
		['key' => 'location/metro/time-on-transport', 'title' => 'metro/time-on-transport', 'required' => 0, 'help' => 'Минут на транспорте до метро.'],
		['key' => 'sales-agent/name', 'title' => 'sales-agent/name', 'required' => 0, 'help' => 'Имя продавца/агента.'],
		['key' => 'sales-agent/phone', 'title' => 'sales-agent/phone', 'required' => 1, 'help' => 'Телефон продавца.', 'format' => '+7XXXXXXXXXX', 'pattern' => '/^\+7\d{10}$/'],
		['key' => 'sales-agent/category', 'title' => 'sales-agent/category', 'required' => 1, 'help' => 'Тип продавца.', 'enum' => ['агентство', 'agency', 'застройщик', 'developer']],
		['key' => 'sales-agent/organization', 'title' => 'sales-agent/organization', 'required' => 0, 'help' => 'Название организации.'],
		['key' => 'sales-agent/url', 'title' => 'sales-agent/url', 'required' => 0, 'help' => 'Сайт застройщика/агентства.'],
		['key' => 'sales-agent/email', 'title' => 'sales-agent/email', 'required' => 0, 'help' => 'Email (покупателям не показывается).'],
		['key' => 'sales-agent/photo', 'title' => 'sales-agent/photo', 'required' => 0, 'help' => 'URL одного фото/логотипа, порты 80/443.'],
		['key' => 'deal-status', 'title' => 'deal-status', 'required' => 1, 'help' => 'Тип сделки.', 'enum' => ['первичная продажа', 'продажа от застройщика', 'прямая продажа', 'переуступка', 'reassignment', '214ФЗ', 'ФЗ 214', '214 ФЗ', 'ФЗ214', '214', 'по 214 фз']],
		['key' => 'price/value', 'title' => 'price/value', 'required' => 1, 'help' => 'Цена числом без пробелов, с НДС.', 'numeric' => 1],
		['key' => 'price/currency', 'title' => 'price/currency', 'required' => 1, 'help' => 'Валюта цены.', 'enum' => ['RUR', 'RUB', 'EUR', 'USD']],
		['key' => 'price/unit', 'title' => 'price/unit', 'required' => 0, 'help' => 'Единица, если цена за м².', 'enum' => ['кв. м', 'sq. m']],
		['key' => 'discount/final-price', 'title' => 'discount/final-price', 'required' => 0, 'help' => 'Цена со скидкой, не больше price/value.'],
		['key' => 'discount/end-date', 'title' => 'discount/end-date', 'required' => 0, 'help' => 'Окончание скидки.', 'format' => 'YYYY-MM-DDTHH:MM'],
		['key' => 'area/value', 'title' => 'area/value', 'required' => 1, 'help' => 'Общая площадь.', 'numeric' => 1],
		['key' => 'area/unit', 'title' => 'area/unit', 'required' => 1, 'help' => 'Единица площади.', 'enum' => ['кв. м', 'sq. m']],
		['key' => 'living-space/value', 'title' => 'living-space/value', 'required' => 1, 'help' => 'Жилая площадь (обязательна для новостроек).'],
		['key' => 'living-space/unit', 'title' => 'living-space/unit', 'required' => 0, 'help' => 'Единица жилой площади.', 'enum' => ['кв. м', 'sq. m']],
		['key' => 'kitchen-space/value', 'title' => 'kitchen-space/value', 'required' => 0, 'help' => 'Площадь кухни.'],
		['key' => 'kitchen-space/unit', 'title' => 'kitchen-space/unit', 'required' => 0, 'help' => 'Единица площади кухни.', 'enum' => ['кв. м', 'sq. m']],
		['key' => 'image', 'title' => 'image', 'required' => 1, 'help' => 'URL фото. tag=plan — планировка. Порты 80/443.'],
		['key' => 'image@tag', 'title' => 'image@tag', 'required' => 0, 'help' => 'Тип картинки.', 'enum' => ['plan', 'plan 3d', 'floor-plan', '3d plan']],
		['key' => 'is-image-order-change-allowed', 'title' => 'is-image-order-change-allowed', 'required' => 0, 'help' => 'Можно ли менять порядок фото.', 'enum' => $boolYn],
		['key' => 'renovation', 'title' => 'renovation', 'required' => 0, 'help' => 'Ремонт/отделка.', 'enum' => ['черновая', 'чистовая', 'под ключ', 'без отделки', 'предчистовая', 'whitebox']],
		['key' => 'description', 'title' => 'description', 'required' => 0, 'help' => 'Текст, до 10000 знаков.'],
		['key' => 'video-review/youtube-video-review-url', 'title' => 'youtube-video-review-url', 'required' => 0, 'help' => 'Прямая ссылка YouTube без таймкода.'],
		['key' => 'video-review/rutube-video-review-url', 'title' => 'rutube-video-review-url', 'required' => 0, 'help' => 'Ссылка RuTube.'],
		['key' => 'online-show', 'title' => 'online-show', 'required' => 0, 'help' => 'Онлайн-показ: 1 если есть.', 'enum' => ['1', '0']],
		['key' => 'virtual-tour/model-url', 'title' => 'virtual-tour/model-url', 'required' => 0, 'help' => 'Ссылка на 3D-тур.'],
		['key' => 'virtual-tour/provider', 'title' => 'virtual-tour/provider', 'required' => 0, 'help' => 'Провайдер тура.', 'enum' => ['matterport', 'vstour', 'iframe']],
		['key' => 'virtual-tour/preview-url', 'title' => 'virtual-tour/preview-url', 'required' => 0, 'help' => 'Превью 3D-тура.'],
		['key' => 'new-flat', 'title' => 'new-flat', 'required' => 1, 'help' => 'Признак новостройки.', 'enum' => $boolY],
		['key' => 'floor', 'title' => 'floor', 'required' => 1, 'help' => 'Этаж квартиры.', 'numeric' => 1],
		['key' => 'rooms', 'title' => 'rooms', 'required' => 1, 'help' => 'Число комнат (по паспорту при своб. планировке).'],
		['key' => 'rooms-type', 'title' => 'rooms-type', 'required' => 0, 'help' => 'Тип комнат.', 'enum' => ['смежные', 'раздельные']],
		['key' => 'apartments', 'title' => 'apartments', 'required' => 0, 'help' => 'Апартаменты.', 'enum' => $boolYn],
		['key' => 'studio', 'title' => 'studio', 'required' => 0, 'help' => 'Студия (не для своб. планировки).', 'enum' => $boolY],
		['key' => 'open-plan', 'title' => 'open-plan', 'required' => 0, 'help' => 'Свободная планировка (не для студий).', 'enum' => $boolY],
		['key' => 'balcony', 'title' => 'balcony', 'required' => 0, 'help' => 'Балкон/лоджия.', 'enum' => ['балкон', 'лоджия', '2 балкона', '2 лоджии']],
		['key' => 'window-view', 'title' => 'window-view', 'required' => 0, 'help' => 'Вид из окон.', 'enum' => ['во двор', 'на улицу', 'во двор и на улицу']],
		['key' => 'floor-covering', 'title' => 'floor-covering', 'required' => 0, 'help' => 'Покрытие пола.', 'enum' => ['ковролин', 'ламинат', 'линолеум', 'паркет']],
		['key' => 'bathroom-unit', 'title' => 'bathroom-unit', 'required' => 0, 'help' => 'Санузел: совмещенный, раздельный или число.'],
		['key' => 'floors-total', 'title' => 'floors-total', 'required' => 0, 'help' => 'Этажей в доме.'],
		['key' => 'building-name', 'title' => 'building-name', 'required' => 0, 'help' => 'Только название ЖК, без улицы.'],
		['key' => 'yandex-building-id', 'title' => 'yandex-building-id', 'required' => 1, 'help' => 'ID ЖК из realty.yandex.ru/newbuildings.tsv, 3-й столбец.'],
		['key' => 'yandex-house-id', 'title' => 'yandex-house-id', 'required' => 1, 'help' => 'ID корпуса из newbuildings.tsv, 7-й столбец.'],
		['key' => 'built-year', 'title' => 'built-year', 'required' => 1, 'help' => 'Год сдачи полностью, например 2026.', 'pattern' => '/^\d{4}$/'],
		['key' => 'ready-quarter', 'title' => 'ready-quarter', 'required' => 1, 'help' => 'Квартал сдачи.', 'enum' => ['1', '2', '3', '4']],
		['key' => 'key-handover-date', 'title' => 'key-handover-date', 'required' => 0, 'help' => 'Дата ключей.', 'format' => 'YYYY-MM-DD', 'pattern' => '/^\d{4}-\d{2}-\d{2}$/'],
		['key' => 'building-state', 'title' => 'building-state', 'required' => 1, 'help' => 'Стадия строительства.', 'enum' => ['built', 'hand-over', 'unfinished']],
		['key' => 'building-phase', 'title' => 'building-phase', 'required' => 0, 'help' => 'Очередь: «очередь 1», «3» и т.п.'],
		['key' => 'building-type', 'title' => 'building-type', 'required' => 0, 'help' => 'Тип дома.', 'enum' => ['кирпичный', 'монолит', 'панельный']],
		['key' => 'building-series', 'title' => 'building-series', 'required' => 0, 'help' => 'Серия дома.'],
		['key' => 'building-section', 'title' => 'building-section', 'required' => 0, 'help' => 'Корпус: «корпус 1», «дом 3».'],
		['key' => 'ceiling-height', 'title' => 'ceiling-height', 'required' => 0, 'help' => 'Высота потолков, метры.'],
		['key' => 'lift', 'title' => 'lift', 'required' => 0, 'help' => 'Лифт.', 'enum' => $boolYn],
		['key' => 'rubbish-chute', 'title' => 'rubbish-chute', 'required' => 0, 'help' => 'Мусоропровод.', 'enum' => $boolYn],
		['key' => 'guarded-building', 'title' => 'guarded-building', 'required' => 0, 'help' => 'Закрытая территория.', 'enum' => $boolYn],
		['key' => 'parking', 'title' => 'parking', 'required' => 0, 'help' => 'Охраняемая парковка.', 'enum' => $boolYn],
		['key' => 'is-elite', 'title' => 'is-elite', 'required' => 0, 'help' => 'Элитная недвижимость.', 'enum' => $boolYn],
	];
}

/** @return array<int,array> */
function feed_fields_avito_new()
{
	return [
		['key' => 'Id', 'title' => 'Id', 'required' => 1, 'help' => 'Стабильный id объявления в вашей системе.'],
		['key' => 'AvitoId', 'title' => 'AvitoId', 'required' => 0, 'help' => 'Id уже опубликованного объявления на Авито (чтобы не дублировать).'],
		['key' => 'Category', 'title' => 'Category', 'required' => 1, 'help' => 'Категория строго по классификатору.', 'enum' => ['Квартиры']],
		['key' => 'OperationType', 'title' => 'OperationType', 'required' => 1, 'help' => 'Тип операции.', 'enum' => ['Продам']],
		['key' => 'DateBegin', 'title' => 'DateBegin', 'required' => 0, 'help' => 'Дата начала размещения.', 'format' => 'YYYY-MM-DD'],
		['key' => 'DateEnd', 'title' => 'DateEnd', 'required' => 0, 'help' => 'Дата окончания размещения.', 'format' => 'YYYY-MM-DD'],
		['key' => 'ListingFee', 'title' => 'ListingFee', 'required' => 0, 'help' => 'Кто платит за размещение.', 'enum' => ['Package', 'PackageSingle', 'Single']],
		['key' => 'AdStatus', 'title' => 'AdStatus', 'required' => 0, 'help' => 'Статус объявления в фиде.', 'enum' => ['Free', 'Highlight', 'XL', 'x2_1', 'x2_7', 'x10_1', 'x10_7', 'x15_1', 'x15_7', 'x20_1', 'x20_7']],
		['key' => 'ContactPhone', 'title' => 'ContactPhone', 'required' => 1, 'help' => 'Контактный телефон.'],
		['key' => 'ContactMethod', 'title' => 'ContactMethod', 'required' => 0, 'help' => 'Способ связи.', 'enum' => ['По телефону', 'В сообщениях', 'По телефону и в сообщениях']],
		['key' => 'ManagerName', 'title' => 'ManagerName', 'required' => 0, 'help' => 'Имя менеджера в объявлении.'],
		['key' => 'EMail', 'title' => 'EMail', 'required' => 0, 'help' => 'Email контактного лица.'],
		['key' => 'CompanyName', 'title' => 'CompanyName', 'required' => 0, 'help' => 'Название компании.'],
		['key' => 'Address', 'title' => 'Address', 'required' => 0, 'help' => 'Адрес. Для новостройки можно заменить NewDevelopmentId.'],
		['key' => 'Latitude', 'title' => 'Latitude', 'required' => 0, 'help' => 'Широта, если нет точного адреса.'],
		['key' => 'Longitude', 'title' => 'Longitude', 'required' => 0, 'help' => 'Долгота.'],
		['key' => 'Title', 'title' => 'Title', 'required' => 0, 'help' => 'Заголовок объявления.'],
		['key' => 'Description', 'title' => 'Description', 'required' => 1, 'help' => 'Описание без HTML, до 7500 символов.'],
		['key' => 'Price', 'title' => 'Price', 'required' => 1, 'help' => 'Цена в рублях, целое число.', 'numeric' => 1],
		['key' => 'Url', 'title' => 'Url', 'required' => 0, 'help' => 'Ссылка на объект на сайте застройщика.'],
		['key' => 'VideoURL', 'title' => 'VideoURL', 'required' => 0, 'help' => 'Ссылка на видео (YouTube и т.п.).'],
		['key' => 'Rooms', 'title' => 'Rooms', 'required' => 1, 'help' => 'Комнаты.', 'enum' => ['Студия', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10 и более', 'Своб. планировка']],
		['key' => 'ApartmentNumber', 'title' => 'ApartmentNumber', 'required' => 0, 'help' => 'Номер квартиры.'],
		['key' => 'Square', 'title' => 'Square', 'required' => 1, 'help' => 'Общая площадь, м².', 'numeric' => 1],
		['key' => 'LivingSpace', 'title' => 'LivingSpace', 'required' => 0, 'help' => 'Жилая площадь, м².'],
		['key' => 'KitchenSpace', 'title' => 'KitchenSpace', 'required' => 0, 'help' => 'Площадь кухни, м².'],
		['key' => 'Floor', 'title' => 'Floor', 'required' => 1, 'help' => 'Этаж.', 'numeric' => 1],
		['key' => 'Floors', 'title' => 'Floors', 'required' => 1, 'help' => 'Этажей в доме.', 'numeric' => 1],
		['key' => 'HouseType', 'title' => 'HouseType', 'required' => 1, 'help' => 'Тип дома.', 'enum' => ['Кирпичный', 'Панельный', 'Блочный', 'Монолитный', 'Монолитно-кирпичный', 'Деревянный']],
		['key' => 'MarketType', 'title' => 'MarketType', 'required' => 1, 'help' => 'Рынок.', 'enum' => ['Вторичка', 'Новостройка']],
		['key' => 'NewDevelopmentId', 'title' => 'NewDevelopmentId', 'required' => 1, 'help' => 'ID корпуса/ЖК в Авито (новостройка).'],
		['key' => 'NewBuilding', 'title' => 'NewBuilding', 'required' => 0, 'help' => 'Строящийся дом: yes; сданный: no.', 'enum' => ['yes', 'no']],
		['key' => 'PropertyRights', 'title' => 'PropertyRights', 'required' => 1, 'help' => 'Правообладатель.', 'enum' => ['Собственник', 'Посредник', 'Застройщик']],
		['key' => 'Decoration', 'title' => 'Decoration', 'required' => 0, 'help' => 'Отделка.', 'enum' => ['Без отделки', 'Черновая', 'Предчистовая', 'Чистовая']],
		['key' => 'Status', 'title' => 'Status', 'required' => 0, 'help' => 'Тип помещения.', 'enum' => ['Квартира', 'Апартаменты']],
		['key' => 'CadastralNumber', 'title' => 'CadastralNumber', 'required' => 0, 'help' => 'Кадастровый номер.'],
		['key' => 'BuiltYear', 'title' => 'BuiltYear', 'required' => 0, 'help' => 'Год постройки/сдачи, 4 цифры.', 'pattern' => '/^\d{4}$/'],
		['key' => 'CeilingHeight', 'title' => 'CeilingHeight', 'required' => 0, 'help' => 'Высота потолков, м.'],
		['key' => 'RoomType', 'title' => 'RoomType', 'required' => 0, 'help' => 'Тип комнат.', 'enum' => ['Изолированные', 'Смежные']],
		['key' => 'BathroomMulti', 'title' => 'BathroomMulti', 'required' => 0, 'help' => 'Санузел. Несколько значений через | : Совмещённый, Раздельный.'],
		['key' => 'BalconyOrLoggiaMulti', 'title' => 'BalconyOrLoggiaMulti', 'required' => 0, 'help' => 'Балкон/лоджия. Несколько через | : Балкон, Лоджия.'],
		['key' => 'ViewFromWindows', 'title' => 'ViewFromWindows', 'required' => 0, 'help' => 'Вид из окон.', 'enum' => ['На улицу', 'Во двор', 'На улицу и двор']],
		['key' => 'PassengerElevator', 'title' => 'PassengerElevator', 'required' => 0, 'help' => 'Число пассажирских лифтов.'],
		['key' => 'FreightElevator', 'title' => 'FreightElevator', 'required' => 0, 'help' => 'Число грузовых лифтов.'],
		['key' => 'Courtyard', 'title' => 'Courtyard', 'required' => 0, 'help' => 'Двор. Несколько через | : Закрытая территория, Детская площадка, Спортивная площадка.'],
		['key' => 'ParkingType', 'title' => 'ParkingType', 'required' => 0, 'help' => 'Парковка. Несколько через | : Наземная, Подземная, Открытая, Многоуровневая.'],
		['key' => 'SaleOptions', 'title' => 'SaleOptions', 'required' => 0, 'help' => 'Условия продажи. Несколько через | : Возможна ипотека, Возможна продажа доли.'],
		['key' => 'Images/Image@url', 'title' => 'Images/Image@url', 'required' => 1, 'help' => 'URL фото планировки/объекта, без авторизации.'],
	];
}

function feed_map_yandex_building_type($material)
{
	$m = mb_strtolower(trim((string)$material));
	if ($m === '') {
		return '';
	}
	if (strpos($m, 'панел') !== false) {
		return 'панельный';
	}
	if (strpos($m, 'кирпич') !== false && strpos($m, 'монолит') === false) {
		return 'кирпичный';
	}
	if (strpos($m, 'монолит') !== false) {
		return 'монолит';
	}
	return '';
}

function feed_map_yandex_renovation($renovation)
{
	$r = mb_strtolower(trim((string)$renovation));
	if ($r === '') {
		return '';
	}
	if ($r === 'нет' || strpos($r, 'без') !== false) {
		return 'без отделки';
	}
	if (strpos($r, 'предчист') !== false) {
		return 'предчистовая';
	}
	if (strpos($r, 'черн') !== false) {
		return 'черновая';
	}
	if (strpos($r, 'ключ') !== false) {
		return 'под ключ';
	}
	if (strpos($r, 'чист') !== false) {
		return 'чистовая';
	}
	if (strpos($r, 'white') !== false) {
		return 'whitebox';
	}
	return '';
}

function feed_map_avito_decoration($renovation)
{
	$r = mb_strtolower(trim((string)$renovation));
	if ($r === '') {
		return '';
	}
	if ($r === 'нет' || strpos($r, 'без') !== false) {
		return 'Без отделки';
	}
	if (strpos($r, 'предчист') !== false) {
		return 'Предчистовая';
	}
	if (strpos($r, 'черн') !== false) {
		return 'Черновая';
	}
	if (strpos($r, 'чист') !== false || strpos($r, 'ключ') !== false) {
		return 'Чистовая';
	}
	return '';
}
