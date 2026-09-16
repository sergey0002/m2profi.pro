<?php
/**
 * Каталог и валидация полей фидов новостроек.
 * Родитель + Яндекс / Авито. Без глобальных функций.
 *
 * Яндекс: https://yandex.ru/support/realty-partner/ru/requirements-sale-new
 * Авито XML 67066: https://www.avito.ru/autoload/documentation/templates/67066?fileFormat=xml
 */

abstract class em_feed_fields
{
	/** @return array<int,array> */
	abstract public function catalog();

	/**
	 * @param array<string,string> $fields
	 * @return string[]
	 */
	protected function extra_validate(array $fields)
	{
		return [];
	}

	/** Пустое обязательное поле допустимо (специфика площадки). */
	protected function allow_empty_required($key, array $fields)
	{
		return false;
	}

	/** @return array<string,array> */
	public function index()
	{
		$out = [];
		foreach ($this->catalog() as $row) {
			$row['tooltip'] = $this->tooltip($row);
			$out[$row['key']] = $row;
		}
		return $out;
	}

	public function tooltip(array $meta)
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

	/** @return array<string,string> */
	public function empty_map()
	{
		$out = [];
		foreach ($this->catalog() as $row) {
			$out[$row['key']] = '';
		}
		return $out;
	}

	/**
	 * @param array<string,mixed> $values
	 * @return array<string,string>
	 */
	public function apply(array $values)
	{
		$out = $this->empty_map();
		foreach ($values as $k => $v) {
			if (array_key_exists($k, $out)) {
				$out[$k] = is_scalar($v) || $v === null ? trim((string)$v) : '';
			}
		}
		return $out;
	}

	/**
	 * @param array<string,string> $fields
	 * @return string[]
	 */
	public function validate(array $fields)
	{
		$reasons = [];
		foreach ($this->catalog() as $meta) {
			$key = $meta['key'];
			$val = isset($fields[$key]) ? trim((string)$fields[$key]) : '';
			if (!empty($meta['required']) && $val === '') {
				if ($this->allow_empty_required($key, $fields)) {
					continue;
				}
				$reasons[] = 'обязательно пусто: ' . $key;
				continue;
			}
			if ($val === '') {
				continue;
			}
			if (!empty($meta['enum']) && is_array($meta['enum'])) {
				$parts = !empty($meta['multi'])
					? preg_split('/\s*\|\s*/u', $val, -1, PREG_SPLIT_NO_EMPTY)
					: [$val];
				foreach ($parts as $part) {
					$ok = false;
					$valNorm = mb_strtolower(trim((string)$part));
					foreach ($meta['enum'] as $ev) {
						if ($part === $ev || $valNorm === mb_strtolower((string)$ev)) {
							$ok = true;
							break;
						}
					}
					if (!$ok) {
						$reasons[] = $key . ': значение не из формата («' . $part . '»)';
					}
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
				if (isset($meta['min']) && $n < (float)$meta['min']) {
					$reasons[] = $key . ': меньше ' . $meta['min'];
				}
				if (isset($meta['max']) && $n > (float)$meta['max']) {
					$reasons[] = $key . ': больше ' . $meta['max'];
				}
			}
			if (!empty($meta['maxlen']) && mb_strlen($val) > (int)$meta['maxlen']) {
				$reasons[] = $key . ': длиннее ' . (int)$meta['maxlen'] . ' знаков';
			}
		}
		$floor = (int)($fields['floor'] ?? $fields['Floor'] ?? 0);
		$floors = (int)($fields['floors-total'] ?? $fields['Floors'] ?? 0);
		if ($floor > 0 && $floors > 0 && $floor > $floors) {
			$reasons[] = 'этаж больше этажности дома';
		}
		return array_merge($reasons, $this->extra_validate($fields));
	}

	/** @return array<string,true> */
	public static function reason_keys(array $reasons)
	{
		$keys = [];
		foreach ($reasons as $r) {
			$r = (string)$r;
			if (preg_match('/обязательно пусто:\s*(.+)$/u', $r, $m)) {
				$keys[trim($m[1])] = true;
			} elseif (preg_match('/^([^\s:]+):/u', $r, $m)) {
				$keys[trim($m[1])] = true;
			}
		}
		return $keys;
	}

	/**
	 * Карточка: ошибки сверху, затем заполненные, пустые внизу.
	 *
	 * @return array{0: array<string,string>, 1: array<string,true>}
	 */
	public static function card_order(array $fields, array $reasons)
	{
		$err = self::reason_keys($reasons);
		$bad = [];
		$filled = [];
		$empty = [];
		foreach ($fields as $k => $v) {
			$v = is_scalar($v) || $v === null ? (string)$v : '';
			if (isset($err[$k])) {
				$bad[$k] = $v;
			} elseif (trim($v) !== '') {
				$filled[$k] = $v;
			} else {
				$empty[$k] = $v;
			}
		}
		return [$bad + $filled + $empty, $err];
	}

	public static function xml_escape($s)
	{
		return htmlspecialchars((string)$s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
	}

	public static function xml_el($name, $value, $indent = "\t")
	{
		echo $indent . '<' . $name . '>' . self::xml_escape($value) . '</' . $name . ">\n";
	}

	public static function xml_options($name, $value, $indent = '    ')
	{
		$parts = preg_split('/\s*\|\s*/u', trim((string)$value), -1, PREG_SPLIT_NO_EMPTY);
		if (!$parts) {
			return;
		}
		echo $indent . '<' . $name . ">\n";
		foreach ($parts as $p) {
			echo $indent . '  <Option>' . self::xml_escape($p) . "</Option>\n";
		}
		echo $indent . '</' . $name . ">\n";
	}
}

class em_yandex_feed_fields extends em_feed_fields
{
	protected function allow_empty_required($key, array $fields)
	{
		if ($key !== 'rooms') {
			return false;
		}
		$studio = mb_strtolower(trim((string)($fields['studio'] ?? '')));
		return in_array($studio, ['1', 'да', 'true', '+'], true);
	}

	public function map_building_type($material)
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

	public function map_renovation($renovation)
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

	public function catalog()
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
			['key' => 'living-space/value', 'title' => 'living-space/value', 'required' => 0, 'help' => 'Жилая площадь. Официальный валидатор Яндекса принимает фид без неё.'],
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
			['key' => 'rooms', 'title' => 'rooms', 'required' => 1, 'help' => 'Число комнат. Для студии не требуется.'],
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
}

class em_avito_feed_fields extends em_feed_fields
{
	public function map_decoration($renovation)
	{
		$r = mb_strtolower(trim((string)$renovation));
		if ($r === '') {
			return '';
		}
		if ($r === 'нет' || strpos($r, 'без') !== false || strpos($r, 'черн') !== false) {
			return 'Без отделки';
		}
		if (strpos($r, 'предчист') !== false) {
			return 'Предчистовая';
		}
		if (strpos($r, 'чист') !== false || strpos($r, 'ключ') !== false) {
			return 'Чистовая';
		}
		return '';
	}

	public function map_house_type($material)
	{
		if (!$material) {
			return '';
		}
		$material = mb_strtolower(trim((string)$material));
		if (strpos($material, 'монолит') !== false && strpos($material, 'кирпич') !== false) {
			return 'Монолитно-кирпичный';
		}
		if (strpos($material, 'кирпич') !== false) {
			return 'Кирпичный';
		}
		if (strpos($material, 'панель') !== false) {
			return 'Панельный';
		}
		if (strpos($material, 'блоч') !== false || strpos($material, 'блок') !== false) {
			return 'Блочный';
		}
		if (strpos($material, 'монолит') !== false) {
			return 'Монолитный';
		}
		return '';
	}

	public function write_ad_xml(array $f)
	{
		echo "  <Ad>\n";
		foreach ($this->catalog() as $meta) {
			$key = $meta['key'];
			$val = trim((string)($f[$key] ?? ''));
			if ($val === '') {
				continue;
			}
			if ($key === 'Images') {
				echo "    <Images>\n";
				echo '      <Image url="' . self::xml_escape($val) . "\" />\n";
				echo "    </Images>\n";
				continue;
			}
			if ($key === 'DduLink') {
				echo '    <DduLink><![CDATA[' . $val . "]]></DduLink>\n";
				continue;
			}
			if (!empty($meta['multi'])) {
				self::xml_options($key, $val, '    ');
				continue;
			}
			self::xml_el($key, $val, '    ');
		}
		echo "  </Ad>\n";
	}

	public function catalog()
	{
		$rooms = ['Студия', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10 и более', 'Своб. планировка'];
		$lift = ['Нет', '1', '2', '3', '4'];
		return [
			['key' => 'Id', 'title' => 'Id', 'required' => 1, 'help' => 'Уникальный id объявления. Не более 100 знаков.', 'maxlen' => 100],
			['key' => 'DateBegin', 'title' => 'DateBegin', 'required' => 0, 'help' => 'Дата начала размещения (dd.MM.yyyy, yyyy-MM-dd или ISO 8601).'],
			['key' => 'DateEnd', 'title' => 'DateEnd', 'required' => 0, 'help' => 'Дата окончания размещения.'],
			['key' => 'ListingFee', 'title' => 'ListingFee', 'required' => 0, 'help' => 'Вариант платного размещения. По умолчанию Package.', 'enum' => ['Package', 'PackageBBL', 'BBL']],
			['key' => 'AdStatus', 'title' => 'AdStatus', 'required' => 0, 'help' => 'Услуга продвижения. По умолчанию Free.', 'enum' => ['Free', 'Highlight', 'XL', 'x2_1', 'x2_7', 'x5_1', 'x5_7', 'x10_1', 'x10_7', 'x15_1', 'x15_7', 'x20_1', 'x20_7']],
			['key' => 'AvitoId', 'title' => 'AvitoId', 'required' => 0, 'help' => 'Номер уже размещённого объявления на Авито. Для нового не заполнять.'],
			['key' => 'ManagerName', 'title' => 'ManagerName', 'required' => 0, 'help' => 'Имя менеджера, до 40 символов.', 'maxlen' => 40],
			['key' => 'ContactPhone', 'title' => 'ContactPhone', 'required' => 0, 'help' => 'Один российский телефон. Необязательно.'],
			['key' => 'Description', 'title' => 'Description', 'required' => 1, 'help' => 'Текст объявления, до 7500 символов.', 'maxlen' => 7500],
			['key' => 'Images', 'title' => 'Images', 'required' => 0, 'help' => 'Фото JPEG/PNG, до 40 шт. XML: <Image url="…"/>. Необязательно.'],
			['key' => 'VideoURL', 'title' => 'VideoURL', 'required' => 0, 'help' => 'Только VK Видео (vkvideo.ru) или Rutube.'],
			['key' => 'Category', 'title' => 'Category', 'required' => 1, 'help' => 'Категория.', 'enum' => ['Квартиры']],
			['key' => 'Promo', 'title' => 'Promo', 'required' => 0, 'help' => 'Продвижение за комиссию (блок «Сдам»). Для продажи обычно пусто.', 'enum' => ['Commission']],
			['key' => 'PromoPeriod', 'title' => 'PromoPeriod', 'required' => 0, 'help' => 'Период Promo.'],
			['key' => 'PromoBid', 'title' => 'PromoBid', 'required' => 0, 'help' => 'Ставка Promo 100–1500 шаг 100.'],
			['key' => 'InternetCalls', 'title' => 'InternetCalls', 'required' => 0, 'help' => 'Интернет-звонки Авито.', 'enum' => ['Да', 'Нет']],
			['key' => 'CallsDevices', 'title' => 'CallsDevices', 'required' => 0, 'help' => 'ID устройств. XML: <Option>.', 'multi' => 1],
			['key' => 'ContactMethod', 'title' => 'ContactMethod', 'required' => 0, 'help' => 'Способ связи.', 'enum' => ['По телефону и в сообщениях', 'По телефону']],
			['key' => 'OperationType', 'title' => 'OperationType', 'required' => 1, 'help' => 'Тип объявления.', 'enum' => ['Продам']],
			['key' => 'BalconyOrLoggiaMulti', 'title' => 'BalconyOrLoggiaMulti', 'required' => 0, 'help' => 'Балкон/лоджия. Несколько через | .', 'enum' => ['Балкон', 'Лоджия'], 'multi' => 1],
			['key' => 'Price', 'title' => 'Price', 'required' => 1, 'help' => 'Цена в рублях, целое число.', 'numeric' => 1],
			['key' => 'MarketType', 'title' => 'MarketType', 'required' => 1, 'help' => 'Шаблон 67066 — только Новостройка.', 'enum' => ['Новостройка']],
			['key' => 'HouseType', 'title' => 'HouseType', 'required' => 1, 'help' => 'Тип дома. Деревянный неприменим к Новостройка.', 'enum' => ['Кирпичный', 'Панельный', 'Блочный', 'Монолитный', 'Монолитно-кирпичный']],
			['key' => 'Floor', 'title' => 'Floor', 'required' => 1, 'help' => 'Этаж объекта, целое.', 'numeric' => 1],
			['key' => 'Floors', 'title' => 'Floors', 'required' => 1, 'help' => 'Этажей в доме.', 'numeric' => 1],
			['key' => 'Rooms', 'title' => 'Rooms', 'required' => 1, 'help' => 'Количество комнат.', 'enum' => $rooms],
			['key' => 'Square', 'title' => 'Square', 'required' => 1, 'help' => 'Общая площадь 10–5000 м².', 'numeric' => 1, 'min' => 10, 'max' => 5000],
			['key' => 'KitchenSpace', 'title' => 'KitchenSpace', 'required' => 0, 'help' => 'Кухня 2–100 м². Не для Студия / своб. планировки.', 'numeric' => 1, 'min' => 2, 'max' => 100],
			['key' => 'LivingSpace', 'title' => 'LivingSpace', 'required' => 0, 'help' => 'Жилая площадь 5–5000 м². Необязательно.', 'numeric' => 1, 'min' => 5, 'max' => 5000],
			['key' => 'ApartmentNumber', 'title' => 'ApartmentNumber', 'required' => 0, 'help' => 'Номер квартиры.'],
			['key' => 'Status', 'title' => 'Status', 'required' => 1, 'help' => 'Статус недвижимости.', 'enum' => ['Квартира', 'Апартаменты']],
			['key' => 'ViewFromWindows', 'title' => 'ViewFromWindows', 'required' => 0, 'help' => 'Вид из окон. Несколько через | .', 'enum' => ['Во двор', 'На улицу', 'На солнечную сторону'], 'multi' => 1],
			['key' => 'PassengerElevator', 'title' => 'PassengerElevator', 'required' => 0, 'help' => 'Пассажирский лифт.', 'enum' => $lift],
			['key' => 'FreightElevator', 'title' => 'FreightElevator', 'required' => 0, 'help' => 'Грузовой лифт.', 'enum' => $lift],
			['key' => 'Courtyard', 'title' => 'Courtyard', 'required' => 0, 'help' => 'Двор. Несколько через | .', 'enum' => ['Закрытая территория', 'Детская площадка', 'Спортивная площадка'], 'multi' => 1],
			['key' => 'Parking', 'title' => 'Parking', 'required' => 0, 'help' => 'Парковка. Несколько через | .', 'enum' => ['Подземная', 'Наземная многоуровневая', 'Открытая во дворе', 'За шлагбаумом во дворе', 'Гостевая'], 'multi' => 1],
			['key' => 'RoomType', 'title' => 'RoomType', 'required' => 0, 'help' => 'Тип комнат, если Rooms ≥ 2.', 'enum' => ['Изолированные', 'Смежные'], 'multi' => 1],
			['key' => 'BathroomMulti', 'title' => 'BathroomMulti', 'required' => 0, 'help' => 'Санузел. Несколько через | .', 'enum' => ['Совмещённый', 'Раздельный'], 'multi' => 1],
			['key' => 'SaleOptions', 'title' => 'SaleOptions', 'required' => 0, 'help' => 'Доп. способ продажи. Для новостроек по умолчанию «Можно в ипотеку».', 'enum' => ['Можно в ипотеку', 'Продажа доли', 'Аукцион'], 'multi' => 1],
			['key' => 'CeilingHeight', 'title' => 'CeilingHeight', 'required' => 0, 'help' => 'Высота потолков, м, до 50.', 'numeric' => 1, 'max' => 50],
			['key' => 'NDAdditionally', 'title' => 'NDAdditionally', 'required' => 0, 'help' => 'Дополнительно.', 'enum' => ['Гардеробная', 'Панорамные окна'], 'multi' => 1],
			['key' => 'NewDevelopmentId', 'title' => 'NewDevelopmentId', 'required' => 1, 'help' => 'ID корпуса из справочника Авито (Housing).'],
			['key' => 'DevelopmentsBuildingName', 'title' => 'DevelopmentsBuildingName', 'required' => 0, 'help' => 'Название корпуса в справочнике Авито.'],
			['key' => 'PropertyRights', 'title' => 'PropertyRights', 'required' => 1, 'help' => 'Право собственности.', 'enum' => ['Собственник', 'Посредник', 'Застройщик']],
			['key' => 'Decoration', 'title' => 'Decoration', 'required' => 1, 'help' => 'Отделка. В 67066 нет «Черновая».', 'enum' => ['Без отделки', 'Предчистовая', 'Чистовая']],
			['key' => 'SaleMethod', 'title' => 'SaleMethod', 'required' => 0, 'help' => 'Способ продажи.', 'enum' => ['Договор долевого участия', 'Договор уступки права требования', 'Договор ЖСК', 'Договор купли-продажи']],
			['key' => 'ShareholderFirstName', 'title' => 'ShareholderFirstName', 'required' => 0, 'help' => 'Имя дольщика (переуступка).'],
			['key' => 'ShareholderLastName', 'title' => 'ShareholderLastName', 'required' => 0, 'help' => 'Фамилия дольщика.'],
			['key' => 'ShareholderPatronymic', 'title' => 'ShareholderPatronymic', 'required' => 0, 'help' => 'Отчество дольщика.'],
			['key' => 'ShareholderINN', 'title' => 'ShareholderINN', 'required' => 0, 'help' => 'ИНН дольщика (юрлицо).'],
			['key' => 'DduLink', 'title' => 'DduLink', 'required' => 0, 'help' => 'Архив ДДУ zip, HTTPS, XML CDATA.'],
			['key' => 'BasePriceND', 'title' => 'BasePriceND', 'required' => 0, 'help' => 'Цена без скидок.', 'numeric' => 1],
			['key' => 'DiscountConditionText', 'title' => 'DiscountConditionText', 'required' => 0, 'help' => 'Условия скидки.'],
		];
	}
}
