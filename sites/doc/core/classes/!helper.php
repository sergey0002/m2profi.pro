<?

function translit($value)
{
	$converter = array(
		'А' => 'A',    'Б' => 'B',    'В' => 'V',    'Г' => 'G',    'Д' => 'D',
		'Е' => 'E',    'Ё' => 'Yo',   'Ж' => 'Zh',   'З' => 'Z',    'И' => 'I',
		'Й' => 'J',    'К' => 'K',    'Л' => 'L',    'М' => 'M',    'Н' => 'N',
		'О' => 'O',    'П' => 'P',    'Р' => 'R',    'С' => 'S',    'Т' => 'T',
		'У' => 'U',    'Ф' => 'F',    'Х' => 'X',    'Ц' => 'Cz',   'Ч' => 'Ch',
		'Ш' => 'Sh',   'Щ' => 'Shh',  'Ъ' => '``',   'Ы' => 'Y`',   'Ь' => '`',
		'Э' => 'E`',   'Ю' => 'Yu',   'Я' => 'Ya',   
 
		'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
		'е' => 'e',    'ё' => 'yo',   'ж' => 'zh',   'з' => 'z',    'и' => 'i',
		'й' => 'j',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
		'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
		'у' => 'u',    'ф' => 'f',    'х' => 'x',    'ц' => 'cz',   'ч' => 'ch',
		'ш' => 'sh',   'щ' => 'shh',  'ъ' => '``',   'ы' => 'y`',   'ь' => '`',
		'э' => 'e`',   'ю' => 'yu',   'я' => 'ya'
	);
 
	$value = strtr($value, $converter);
	return $value;
}









/**
 * Выделение select option.
 * 
 * @param mixed $var
 * @param string $value
 * @return string
 
 <?php echo selected($field, 1); ?>
 */
function selected($var, $value) 
{
	if (!is_array($var)) {
		$var = explode(',', $var);
	}
 
	return (in_array($value, $var)) ? ' selected' : '';
}











//Вывод даты с русскими месяцами

function date_ru($timestamp, $show_time = false)
{
	if (empty($timestamp)) {
		return '-';
	} else {
		$now   = explode(' ', date('Y n j H i'));
		$value = explode(' ', date('Y n j H i', $timestamp));
 
		if ($now[0] == $value[0] && $now[1] == $value[1] && $now[2] == $value[2]) {
			return 'Сегодня в ' . $value[3] . ':' . $value[4];
		} else {
			$month = array(
				'', 'января', 'февраля', 'марта', 'апреля', 'мая', 'июня', 
				'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря'
			);
			$out = $value[2] . ' ' . $month[$value[1]] . ' ' . $value[0];
			if ($show_time) {
				$out .= ' в ' . $value[3] . ':' . $value[4];
			}
			return $out;
		}
	}
}











// обрезка текств 
function preview_text($value, $limit = 300)
{
	$value = stripslashes($value);		
	$value = htmlspecialchars_decode($value, ENT_QUOTES);
	$value = str_ireplace(array('<br>', '<br />', '<br/>'), ' ', $value);
	$value = strip_tags($value);
	$value = trim($value);
 
	if (mb_strlen($value) < $limit) {
		return $value;
	} else {
		$value   = mb_substr($value, 0, $limit);
		$length  = mb_strripos($value, ' ');
		$end     = mb_substr($value, $length - 1, 1);
 
		if (empty($length)) {
			return $value;
		} elseif (in_array($end, array('.', '!', '?'))) {
			return mb_substr($value, 0, $length);
		} elseif (in_array($end, array(',', ':', ';', '«', '»', '…', '(', ')', '—', '–', '-'))) {
			return trim(mb_substr($value, 0, $length - 1)) . '...';
		} else {
			return trim(mb_substr($value, 0, $length)) . '...';
		}
		
		return trim();
	}
}











/**
 * Склонение существительных после числительных.
 * 
 * @param string $value Значение
 * @param array $words Массив вариантов, например: array('товар', 'товара', 'товаров')
 * @param bool $show Включает значение $value в результирующею строку
 * @return string
 
 echo num_word(1, array('рубль', 'рубля', 'рублей')) . '<br>';
 */
function num_word($value, $words, $show = true) 
{
	$num = $value % 100;
	if ($num > 19) { 
		$num = $num % 10; 
	}
	
	$out = ($show) ?  $value . ' ' : '';
	switch ($num) {
		case 1:  $out .= $words[0]; break;
		case 2: 
		case 3: 
		case 4:  $out .= $words[1]; break;
		default: $out .= $words[2]; break;
	}
	
	return $out;
}












// Разница в процентах! 
// echo sale_percent(1000, 800);
function sale_percent($price, $sale) {
	return round((($price - $sale) * 100) / $price, 2);
}














// Собираем url
function reverse_parse_url(array $parts)
{
	$url = '';
	if (!empty($parts['scheme'])) {
		$url .= $parts['scheme'] . ':';
	}
	if (!empty($parts['user']) || !empty($parts['host'])) {
		$url .= '//';
	}	
	if (!empty($parts['user'])) {
		$url .= $parts['user'];
	}	
	if (!empty($parts['pass'])) {
		$url .= ':' . $parts['pass'];
	}
	if (!empty($parts['user'])) {
		$url .= '@';
	}	
	if (!empty($parts['host'])) {
		$url .= $parts['host'];
	}
	if (!empty($parts['port'])) {
		$url .= ':' . $parts['port'];
	}	
	if (!empty($parts['path'])) {
		$url .= $parts['path'];
	}	
	if (!empty($parts['query'])) {
		if (is_array($parts['query'])) {
			$url .= '?' . http_build_query($parts['query']);
		} else {
			$url .= '?' . $parts['query'];
		}
	}	
	if (!empty($parts['fragment'])) {
		$url .= '#' . $parts['fragment'];
	}
	
	return $url;
}









// форматирование цен
function format_price($value, $unit = 'руб.')
{
	if ($value > 0) {
		$value = number_format($value, 2, ',', ' ');
		$value = str_replace(',00', '', $value);
 
		if (!empty($unit)) {
			$value .= ' ' . $unit;
		}
	} else {
		$value = 'Нет в наличии';
	}
 
	return $value;
}

// Приводим цену к единому виду 
function clean_price($value, $default = 0, $decimal = false)
{
	$value = mb_ereg_replace('[^0-9.,]', '', $value);
	$value = mb_ereg_replace('[,]+', ',', $value);
	$value = mb_ereg_replace('[.]+', '.', $value);
 
	$pos_1 = mb_strpos($value, '.');
	$pos_2 = mb_strpos($value, ',');
 
	if ($decimal) {
		if ($pos_1 && $pos_2) {
			// 1,000,000.00
			$value = mb_substr($value . '00', 0, $pos_1 + 3);
			$value = str_replace(',', '', $value);
		} elseif ($pos_1) {
			// 1000000.00
			$value = mb_substr($value . '00', 0, $pos_1 + 3);
		} elseif ($pos_2) {
			if ((mb_strlen($value) - $pos_2) == 3) {
				// 10,00
				$value = str_replace(',', '.', $value);
			} else {
				// 100,000,000
				$value = str_replace(',', '', $value) . '.00';
			}
		} elseif (mb_strlen($value) == 0) {
			return $default;
		} else {
			$value = $value . '.00';
		}
 
		return ($value == '0.00') ? 0 : $value;
	} else {
		if ($pos_1 && $pos_2) {
			// 1,000,000.00
			$value = mb_substr($value, 0, $pos_1);
			$value = str_replace(',', '', $value);
		} elseif ($pos_1) {
			// 1000000.00
			$value = mb_substr($value, 0, $pos_1);
		} elseif ($pos_2) {
			// 100,000,000
			$value = str_replace(',', '', $value);
		}
 
		return (mb_strlen($value) == 0) ? $default : intval($value);
	}
}