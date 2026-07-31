<?
class urlc
{
	 public $scheme = 'http'; // Схема сайта которую применять
	 public $no_www = 1; // 1 отрезать www  
	
	
	 public $url_data; // данные url 
	 public $q_data; // данные url get
	 
	 
	// Нормализация URL (упорядочиваем параметры гет по алфавиту, отрезаем www, приводим схему к http) build - собирать url и вернуть в виде строки
	public function url($url='',$build=1)
	{
		if(!$url){ $url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; } // текущий URL
		/*
			[scheme] => http
			[host] => localhost
			[path] => /2gis/nsdstr.php
			[query] => x=1
		*/
		$url_data = parse_url($url);
		//print_r($url_data);
		
		// Схема по умолчанию
		if($this->scheme){	$url_data['scheme'] = $this->scheme; }
				
				
		// Повторяющиеся слеши на один
		$url_data['host'] = str_ireplace('www.','',$url_data['host']);
		
		if($this->no_www)
		{	 
		// убираем www
		$url_data['path'] = preg_replace('|([/]+)|s', '/', $url_data['path']);
		}
		
		 // Массив GET параметров
		parse_str($url_data['query'],$q_data);
		ksort($q_data); // сортируем гет переменные по ключу по алфавиту
		//print_r($q_data);
		
		$this->url_data = $url_data;
		$this->q_data = $q_data;
		
		
		
		if($build)
		{
			return $this->build_url(); // Собираем url и возвращаем
		}
	}
	
	
	
	// Собрать url из параметров  спаршенных методом url
	/*
	q_nasled - приоритет $query_arr над текущими заданными при парсинге в методе url параметрами 
	*/
	function build_url()
	{
		
		//print_r($this->url_data);
		//print_r($this->q_data);
		
		$str='';
		if($this->url_data)
		{
			$str.=$this->url_data['scheme'];
			$str.='://';
			$str.=$this->url_data['host'];
			$str.=$this->url_data['path'];
		}
		if($this->q_data)
		{
			$str.='?';
			$i=0;
			foreach($this->q_data as $k=>$v)
			{
				if($v) // Пустые переменные игнорим
				{
				$i++;
				if($i>1){$str.='&';}
				$str.=$k.'='.$v;	
				}				
			}
		}
		return $str;
	}
	 
 
	// ДОбавляем к url гет параметры  унаследовав текущие 
	public function urlq($arr='',$url='')
	{
		$this->url($url,0); // парсим и нормализуем URL (указанный или текущий)
		
		if($arr)
		{
			foreach($arr as $k=>$v)
			{
				$this->q_data[$k]=$v;
			}
		}
		return $this->build_url();
	}
}


 
$urlc = new urlc();
//$u['x']='';
//$u['dvhj123dv']='';
//array( "x" => "",)
//print  $urlc->urlq($u);













# Работа с одной страницей из паблика и бекенда
class seo_page
{
	 public $page_data = ''; // массив с данными текущщей страницы
	 public $url = ''; // нормализованный url
	 
	//  
	function __construct()
	{
		 
	}
	 
	//  добавить страницу, или редактирует по url 
	public function add_page($data2='',$url='',$id='')
	{
		global  $mysql;
		global  $urlc;
			
		if(!$url)
		{
			$url = $urlc->url();
		}
		
		$path = $urlc-> url_data['path'];
		$query = $urlc-> url_data['query'];
		
		if(!$id)
		{
		$arrx=array();
		$arrx['url'] = $url;
		}
		else
		{
			$arrx=array();
			$arrx['id'] = $id;
		}
		 
		$data['url'] = $url;
		$data['path'] = $path;
		$data['query'] = $query;
		if($data2)
		{
			foreach($data2 as $k=>$v)
			{
				$data[$k] = $v;
			}
		}
		
		//print '<h2>';
		 return $mysql->insert_or_not('seo_url',$arrx,$data,'url','uid');
	}
	
	
	//Получить данные страницы
	function get_page($url='',$uid='')
	{
		global  $mysql;
		global  $urlc;

		if($uid)
		{
			$this->page_data = $mysql->get_for_key('seo_url','uid',$uid,1);
			return $this->page_data;
		}
		else 
		{
			if(!$url){ $url = $urlc->url(); }
			$this->page_data = $mysql->get_for_key('seo_url','url',$url,1);
			return $this->page_data;
		}
	}
	
	// Получить атрибут страницы
	function get_attr($name)
	{
		return $this->page_data[$name];
	}
	
	
	
	//форма редактирвоания страницы
	function editorpage()
	{
		
	}
	// обработчик формы редактирования 
	function editorpage_post()
	{
		
	}
 
 
}
		
		
 //$x= new seo_page();
 // $x->add_page();
 //$x->get_page( )

//$arr = $x->get_page( );
//print_r($arr);
		















# Работа текстами страницы (проверка на уник, схожесть)
class seo_unitext
{
	
	/*
	Задачи:
	1.Подсчитать релевантность текста относительно эталонных 
	2. Сравнить ассортимент слов (какие слова добавить свойственные выдаче или пары )
	3. Работа с предложениями разбить текст на предложениями
	4. работа с подзаголовками 
	5. работа с абзадцами 
	
	
	загрузка эталонных текстов из поисковой выдачи  (загрузка выдачи из выгрузок коллектора)
	парсинг html выдерание текстов и тп 
	
	 
	*/
	
	
	
	 public $page_data = ''; // массив с данными текущщей страницы
	 public $url = ''; // нормализованный url
	 
	// вернёт массив слов.
	function get_minification_array($text)
	{
		// Удаление экранированных спецсимволов
		$text = stripslashes($text);	
		
		// Преобразование мнемоник 
		$text = html_entity_decode($text);
		$text = htmlspecialchars_decode($text, ENT_QUOTES);	
		
		// Удаление html тегов
		$text = strip_tags($text);
		
		// Все в нижний регистр 
		$text = mb_strtolower($text);	
		
		// Удаление лишних символов
		$text = str_ireplace('ё', 'е', $text);
		$text = mb_eregi_replace("[^a-zа-яй0-9 ]", ' ', $text);
		
		// Удаление двойных пробелов
		$text = mb_ereg_replace('[ ]+', ' ', $text);
		
		// Преобразование текста в массив
		$words = explode(' ', $text);
		
		// Удаление дубликатов
		$words = array_unique($words);
	 
		// Удаление предлогов и союзов
		$array =  file('stopwords.txt');
 
		$words = array_diff($words, $array);
	 
		// Удаление пустых значений в массиве
		$words = array_diff($words, array(''));	
	 
		return $words;
	}
	
	
	
	// Проверка текста на униклаьность для поля таблицы - если текст полностью входит в текст поля то 100% показывает???
	function test_text($text,$filed='text_1',$key_filed='url',$table='seo_url')
	{
		// Сверять пары слов ?! 
		// ПОДУЛЮЧИТЬ МОРФИ!
		// Привести в нормальную форму слова
		// Кеш таблица 
		/*
		ид текста
		ид текста2
		% схожесть


		ид текста 
		ид слова
		сколько раз употребляется
		
		
		ид текста
		ид слова 1
		ид слова 2
		Сколько раз употребляется пара? (шингл 2 слова)
		
		
		*/
		global  $mysql;
		global  $urlc;
		
		$text = $this->get_minification_array($text); // массив слов текста
		
		$count = count($text);	
		$out = array();
		$sql = 'SELECT `'.$filed.'`,`'.$key_filed.'` FROM `'.$table.'` limit 10000';
		
		$list = $mysql->get_arr($sql,$first=false,$key=false);
		// Проход по всем статьям в таблице
	 
		foreach($list as $row) 
		{
			$verifiable = $this->get_minification_array( $row[$filed]);
		 
			$similar_counter = 0;
			foreach ($text as $text_row) 
			{
				foreach ($verifiable as $verifiable_row)
				{
					if($text_row == $verifiable_row) 
					{
						$similar_counter++;
						break;
					}
				}
			}
			$out[$row[$key_filed]] = $similar_counter * 100 / $count;
		}
		
		// Сортировка результатов и ограничение до 15шт
		arsort($out);
		$out = array_slice($out, 0, 15, true);
		 
		print_r($out);
	}
}	
		
	





	//print '<pre>';
	
 //$x = new seo_unitext();
 
 //$text = 'Ремонт инвентаря';
 //$x->test_text($text,'name','name','2gis_rubrics');

 
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		# работа с поисковыми запросами 
		class seo_kk
		{
			
			/*
			Задачи 
			
			
			1. Найти самые видимые страницы с сайтов которым /меньше года/+число беклинков <200/, которые светятся по большему числу запросов с реальным вордстатом (максимальный суммарный реальный вордстат)
			2. 
			
			*/
			
			// грузим данные из файла кей коллектора о выдаче
			function load_search_results()
			{
				global $mysql;
				
			}
			
			// грузим данные из файла кей коллектора о поисковых запросах 
			function load_qdata()
			{
				global $mysql;
				
			}
			
			// получитьпараметры домена
			function get_domain_params()
			{
				
			}
			
			
			
			
		}
		
		
		
		
		
		
		
		
		
		
		// Лог визитов пользователей и поисковых машин Храним за 30 дней к примеру все обращения и расшифровываем их 
		class logvisit
		{
			//Пишем визит  
			function write()
			{
				// Сессию пишем - поддержка куков
				// кук на js поддержка js
				// img кук - поддержка картинок 
				
				/*
				page_id
				datetime
				ueragent
				ip
				referer				
				*/
			}
			
			
			
			function display()
			{
				//фильтр юзерагент, с дата по дата 
				// группировка в дерево 
			}
		}
		
		
		
		
		// Рдактор в виде таблицы аякс?!		
		class table_editor
		{
			// Поле- таблица ключ поле 
			
		}
