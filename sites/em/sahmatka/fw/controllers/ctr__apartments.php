<?


$_GET['rent_home_id'] = $_GET['bid']; // для аякс запросов карты 

class ctr__apartments extends ctr__
{  
 
	var $table = 'apartments'; //Главная таблица
	var $key_filed = 'apartments_id'; // Ключевое поле главной таблицы
	var $ctr = 'apartments';
    var $title = 'Квартиры';
   
	function __construct()
	{
		global $mysql;
		$this->data_arr = $this->get_data_arr(); // Получаем данные для отображения 
		$this->data_arr_ns = $this->get_data_arr(1); // Получаем данные не учитывая условий поиска (для форм фильтров)
		
		 
		if($_GET['dev'])
		{
			//print '<pre>';
			// print_r($this->data_arr_ns);
			//print '</pre>'; 
		}
		$this->sql = $mysql;
 	}
	
	function get_data_arr($no_search=0)
	{
		
		if($_REQUEST['home_id2'] && !$_REQUEST['home'])
		{
			$_REQUEST['home'] = $_REQUEST['home_id2'];
		}
		// ФУНКЦИИ МОДЕЛИ и контроллера
		
		//print_r($_POST);
		//print_r($_GET);
 
		 global $mysql;
		 $sql = ' SELECT ';
		 $_REQUEST['load'] = $_REQUEST['act'];
		 
		 if($_REQUEST['load']=='sel_home' || $_REQUEST['load']=='sel_rooms_k' || $_REQUEST['load']=='sel_min_price' ||   $_REQUEST['load']=='sel_max_price' || $_REQUEST['load']=='sel_floor' ||  $_REQUEST['load']=='sel_sdan')
		 {
			 $sql.=' count(apartaments.apartament_id) as c, ';
		 }
		 /*
		 Получение b_status - из броней
		  LEFT JOIN (
			 SELECT home_id as b_hid, apartments_num as b_anum, status as b_status FROM `broni`
				 where date=(SELECT max(date) FROM broni as b WHERE broni.home_id = b.home_id AND `b`.`apartments_num`=`broni`.`apartments_num`)
		 ) 
		 as br on (br.b_hid=home_id  AND apartaments.apartment_num = b_anum)
		 
		 */ 
		$sql .='
		  apartaments.* , CAST(apartaments.rooms AS UNSIGNED) as rooms_int, group_concat(DISTINCT apartaments.floor ORDER BY apartaments.floor ASC SEPARATOR ", ") as floors,
		  group_concat(DISTINCT apartaments.apartment_num ORDER BY apartaments.apartment_num ASC SEPARATOR ", ") as anums,
		  homes.* FROM `apartaments` 
		  LEFT JOIN homes on (apartaments.home_id = homes.home_id)
		  WHERE  image_pb !="" AND (status2 is null or status2 = 2 or  status2 = "0" ) AND (homes.show=1  )
		';
// ДЛЯ АДМИНА ДОБАВИТЬ or homes.show=3 - оп or homes.show=2 - админ 

		if(!$no_search) // не испольовать условия поиска
		{
		if( $_REQUEST['min_price'] && $_REQUEST['load']!='sel_min_price' ){ $sql .=' AND price>="'.$_REQUEST['min_price'].'" '; }
		if( $_REQUEST['max_price'] && $_REQUEST['load']!='sel_max_price' ){ $sql .=' AND price<="'.$_REQUEST['max_price'].'" '; }


		//if( $_REQUEST['load']!='sel_home' && $_REQUEST['home'] )
		//{
		if($_REQUEST['home']=='001'){$_REQUEST['home']=''; $_REQUEST['object']=1;}
		if($_REQUEST['home']=='002'){$_REQUEST['home']=''; $_REQUEST['object']=2;}
		if($_REQUEST['home']=='003'){$_REQUEST['home']=''; $_REQUEST['object']=3;}
		if($_REQUEST['home']=='004'){$_REQUEST['home']=''; $_REQUEST['object']=4;}
		if($_REQUEST['home']=='005'){$_REQUEST['home']=''; $_REQUEST['object']=5;}
		if($_REQUEST['home']=='006'){$_REQUEST['home']=''; $_REQUEST['object']=6;}
		if($_REQUEST['home']=='007'){$_REQUEST['home']=''; $_REQUEST['object']=7;}
		if($_REQUEST['home'] && $_REQUEST['load']!='sel_home'){ $sql .=' AND apartaments.home_id="'.$_REQUEST['home'].'" '; }
	 
		//}
		
		
		
		
		if($_REQUEST['floor'] && $_REQUEST['load']!='sel_floor'){ $sql .=' AND apartaments.floor="'.$_REQUEST['floor'].'" '; }

		// КОмнат
		if( $_REQUEST['rooms'] && $_REQUEST['load']!='sel_rooms' ){ $sql .=' AND  CAST(rooms AS UNSIGNED)="'.$_REQUEST['rooms'].'" '; }
		if( $_REQUEST['rooms_k'] && $_REQUEST['load']!='sel_rooms_k' ){ $sql .=' AND rooms ="'.$_REQUEST['rooms_k'].'" '; }

		
		if( $_REQUEST['rooms_min'] && $_REQUEST['load']!='sel_rooms_min'){ $sql .=' AND  CAST(rooms AS UNSIGNED)>="'.$_REQUEST['rooms_min'].'" '; }
		if( $_REQUEST['rooms_max'] && $_REQUEST['load']!='sel_rooms_max' ){ $sql .=' AND  CAST(rooms AS UNSIGNED)<="'.$_REQUEST['rooms_max'].'" '; }
		
		// родники / приозерный
		//Брать из таблицы домов		
		if( $_REQUEST['raion'] && $_REQUEST['raion']==1 ){ $sql .=' AND apartaments.home_id IN(5,8) '; }
		if( $_REQUEST['raion'] && $_REQUEST['raion']==2 ){ $sql .=' AND apartaments.home_id IN(3,7,6,9,10,12,15) '; }
		
		// Серии домов смарт грин итпz
		// БРАТЬ ИЗ ТАБЛИЦЫ ДОМОВ!
 
		if( $_REQUEST['object'] && $_REQUEST['object']==3 ){ $sql .=' AND homes.kvartal = "3"'; } // RED
		if( $_REQUEST['object'] && $_REQUEST['object']==4 ){ $sql .=' AND homes.kvartal = "1"'; } // Infinity
		if( $_REQUEST['object'] && $_REQUEST['object']==2 ){ $sql .=' AND homes.kvartal = "2"'; } // Приозерный 
		if( $_REQUEST['object'] && $_REQUEST['object']==6 ){ $sql .=' AND homes.kvartal = "6"'; } // ДИНАСТИЯ 
		if( $_REQUEST['object'] && $_REQUEST['object']==7 ){ $sql .=' AND homes.kvartal = "7"'; } // АТОМ 

/*
$kvartal[6] = 'ДИНАСТИЯ';
$kvartal[1] = 'Квартал INFINITY';
$kvartal[2] = 'Микрорайон Приозерный';
$kvartal[3] = 'Серия RED';
*/



		// Сданные дома + ФИльтр не применяется при загрузке этого поля!
		if( $_REQUEST['load']!='sel_sdan' &&  $_REQUEST['sdan'] && $_REQUEST['sdan']==1 ){ $sql .=' AND ( homes.complite = "1"  )'; }
		elseif( $_REQUEST['load']!='sel_sdan' && $_REQUEST['sdan'] && $_REQUEST['sdan']==2 ){ $sql .=' AND ( homes.complite = "0"   ) '; }
		
		
		}
		
		
 
			// Группировка 
			if( $_REQUEST['load']=='sel_home' ){ $sql .=' GROUP BY apartaments.home_id ORDER BY kvartal, homes.title';}
			
			elseif( $_REQUEST['load']=='sel_sdan' ){ $sql .=' GROUP BY homes.complite ORDER BY price ';}
			
			elseif( $_REQUEST['load']=='sel_rooms_k' ){ $sql .=' GROUP BY rooms ORDER BY rooms ';}
			
			elseif( $_REQUEST['load']=='sel_rooms_min' ||  $_REQUEST['load']=='sel_rooms_max'){ $sql .=' GROUP BY rooms ORDER BY rooms  ';}
			
			
			elseif( $_REQUEST['load']=='sel_min_price' || $_REQUEST['load']=='sel_max_price' ){ $sql .=' GROUP BY price ORDER BY price';}
			elseif( $_REQUEST['load']=='sel_floor' ){ $sql .=' GROUP BY apartaments.floor ORDER BY apartaments.floor ASC';}
			
			elseif( $_REQUEST['load']=='data' ){ $sql .=' group by section_id, area, image_pb order by complex_domclick , apartaments.home_id, price	'; }
			
			
			// ЛИМИТЫ ПО УМПОЛЧАНИЮ (lazy_load)
			if($this->lazy_load)
			{
				if($_REQUEST['limit_start']){$lstart=$_REQUEST['limit_start'];}
				else{$lstart=0;}
				
				if($_REQUEST['limit_stop']){$lstop=$_REQUEST['limit_stop'];}
				else{$lstop=10;}
				$sql .=' LIMIT '.$lstart.','.$lstop;
			}
			
			if($_GET['dev'])
			{
				//print '<br><br/>';
			    //print $sql;
			}
			if(!$no_search) // не испольовать условия поиска
			{
				 $this->q = $sql;
			}
			
			if($_GET['dev'])
			{
				print $sql;
				$arr = $mysql->get_arr($sql);
				print '<pre>';
				print_r($arr);
				print '</pre>';
				}
			
			
			//
			return $mysql->get_arr($sql);
			
			 
	
			 //print_R($this->data_arr);
	}


 
	// функции вида
	
	
	# Метод вывода опшен для селектов с учетом неактивных
	// noactval - показывать не активные или удалять ?
	function select_items($filed_k,$filed_v,$filed_req='',$filed_c='',$noactval=1)
	{
		 
		// Приводим ключи к одному виду (значение ключевого поля)
		foreach($this->data_arr_ns as $k => $v)	{	$data_arr_ns2[ $v[$filed_k] ] = $v;	}
		foreach($this->data_arr as $k => $v){	$data_arr2[ $v[$filed_k] ] = $v;	}		
		$this->data_arr_ns = $data_arr_ns2;
		$this->data_arr = $data_arr2;
		 
		 
		# цены меньше больше не корректно считает? (заменить ползунком)	удалять недоступные в больше меньше?
		 # Комнат множественный выбор !
		 
		 
		# Стили очистить и подготовить стили для диваид изолированные
		# Лези лоад ? - показать еще кнопка и лимит 50 записей 
		#- скрытое поле старт в форме , берем и пишем туда старт при нажатии еще переписывается поле или просто номер порции  
	 
		
		//print_R($this->data_arr);
		 
		 
		// print_r($this->data_arr_ns);
		foreach($this->data_arr_ns as $k => $v)
		{
			// форматирование для некоторых полей
			if($filed_v == 'price')
			{
				$value = number_format($v[$filed_v], 0, '.', ' ');
			}
			elseif($filed_v == 'complite')
			{
				$v[$filed_v];
				if(!$v[$filed_v]  || $v[$filed_v]=='0' || $v[$filed_v]=='2' ){$title='Строящиеся'; $value=2;}
				else{$title='Сданные'; $value=1;  } 
			}
			else{ $value = $v[$filed_v]; $title=$value; }
			
			if( $this -> data_arr[$k] ||1==1 )
			{
				if($this->data_arr[$k]){$style=' style="color:#000; "';}else{$style=' style="color:#cdcdcd; "';}
				?>
				<option value="<?=$value?>" <?if($_REQUEST[$filed_req]==$value){?>selected="selected"<?}?> <?=$style?>><?=$title?>  
				<?
				if( $filed_c && $this->data_arr[$k][$filed_c] ){	?>(<?=$this->data_arr[$k][$filed_c]?>)<?	}
				?>
				</option>
				<?
			}
			elseif($noactval)
			{
				?>
				<optgroup label="<?=$title?>" style="font: 400 16px/49px Montserrat, sans-serif; font-weight:100; color:#BDBDBD;"></optgroup>
				<?
			}
		}
	}




	
	function act__sel_home()
	{	 
	
		$filed_k='home_id';
		$filed_v='long_title';
		$filed_req='home';
		$filed_c='c';
	 
		// Приводим ключи к одному виду (значение ключевого поля)
		foreach($this->data_arr_ns as $k => $v)	{	$data_arr_ns2[ $v[$filed_k] ] = $v;	}
		foreach($this->data_arr as $k => $v){	$data_arr2[ $v[$filed_k] ] = $v;	}		
		$this->data_arr_ns = $data_arr_ns2;
		$this->data_arr = $data_arr2;
	 		
		$kvartal_buf=0;
			
		foreach($this->data_arr_ns as $k => $v)
		{
			
			
			if($kvartal_buf!=$v['kvartal'])
			{
				?>
				<option 
				value="<?=$GLOBALS['kvartal_code'][$v['kvartal']]?>" 
				style="<?=$GLOBALS['kvartal_style'][$v['kvartal']]?>" 
				
				<?  if( $_REQUEST['object']==$GLOBALS['kvartal_code'][$v['kvartal']]){?> selected="selected" <?}?>>
				<?=$GLOBALS['kvartal'][$v['kvartal']]?> 
				</option>
				<?
				$kvartal_buf=$v['kvartal'];
			}
			if($v[$filed_k]==47){continue;} // НЕ ВЫВОДИМ ДОМ АТОМ! только квартал
			// форматирование для некоторых полей
			if($filed_v == 'price')
			{
				$value=number_format($v[$filed_v], 0, '.', ' ');
			}
			else{ $value = $v[$filed_k]; $title=$v[$filed_v]; }
			
			if($this->data_arr[$k] ||1==1)
			{
				
				if($this->data_arr[$k]){$style=' style="color:#000; "';}else{$style=' style="color:#cdcdcd; "';}
				?>
				
			 
				<option value="<?=$value?>" <?if($_REQUEST[$filed_req]==$value){?>selected="selected"<?}?> <?=$style?>><?=$title?>  
				<?
				if( $filed_c && $this->data_arr[$k][$filed_c] ){	?>(<?=$this->data_arr[$k][$filed_c]?>)<?	}
				?>
				</option>
				<?
			}
			else
			{
				?>
				<optgroup label="<?=$v[$filed_v]?>"></optgroup>
				<?
			}
		}
		
	}

	
	
	
	function act__sel_sdan()
	{		
	
	                           
		$this->select_items('complite','complite','sdan','c'); 
	}
	
	
	function act__sel_rooms_min()
	{		
		$this->select_items('rooms_int','rooms_int','rooms_min','c'); 
	}
	
	
	function act__sel_rooms_max()
	{		
		$this->select_items('rooms_int','rooms_int','rooms_max','c'); 
	}
	
	
	
	
	function act__sel_rooms_k()
	{		
		$this->select_items('rooms','rooms','rooms_k','c'); 
	}
	
	function act__sel_min_price()
	{
		$this->select_items('price','price','min_price',0,0); 
	}
 
	function act__sel_max_price()
	{
		$this->select_items('price','price','max_price',0,0); 
	}
 
	function act__sel_floor()
	{
		$this->select_items('floor','floor','floor','c'); 
	}
	
	 
	 
	 
	 
	 
	function act__data()
	{
		if(count($this->data_arr))
		{
			print '<div style="padding:5px;">Найдено: '.count($this->data_arr) .' вариантов планировок</div>  ';
		}
		
		// print $this->q;
		// print '<pre>';
		// print_r($this->data_arr);
		// print '</pre>';
		
		if(!$this->data_arr)
		{
			?><div style="width:100%; text-align:center;">Квартир соответствующих условиям поиска не найдено</div><?
		}
		// print_R( $this->data_arr);
		foreach( $this->data_arr as $k => $result )
		{
			print '<pre>';
			//print_r( $result);
			print '</pre>';
			
			
			
		#$result['price'] = number_format($result['price'], 0, '.', ' ');  
		#$result['price'] = ' '.$result['price'].' руб.';
		
		# Скрываем цены от посетителей сайта !!!
		if($result['show']=="3" || 1==1)
		{
			$result['price'] ='';
		}
		
		 
	?>
				<div class="m2catalog_item">
				 <?
				 # print_r($result);
				 ?>
				<div class="m2catalog_item_img_frame">
					<img src="<?=$result['image_pb']?>"  />
				</div>
				 
				<div class="m2catalog_item_content_frame">
						<span class="h_seriya">  <b style="<?=$GLOBALS['kvartal_style'][$result['kvartal']]?>"><?=$GLOBALS['kvartal'][$result['kvartal']]?></b>  </span>
						<span><b>Дом:</b> <?=$result['long_title']?> </span> 
						<hr/>
						<span><b>Секция:</b> <?=$result['section_id']?> </span>
						<span><b>Этаж:</b> <?=$result['floors']?> </span> 
						<span style="    font-size: 12px; color: #EEE;"><b>Номера квартир:</b> <?=$result['anums']?>   </span> <br/>
						
						 
						
						<span><b>Комнат:</b> <?=$result['rooms']?> </span>
						<span><b>Площадь:</b> <?=$result['area']?> м<sup>2</sup>  </span>
				</div>
				<div class="m2catalog_item_order_frame">
					<div class="m2catalog_item_price"><?=$result['price']?></div>
<a class="m2catalog_item_order iframe" href="https://em.m2profi.pro/sahmatka/iframe_router.php?ctr=apartments&act=card&home_id=<?=$result['home_id']?>&apartment_num=<?=$result['apartment_num']?>&apartments=<?=$result['apartments']?>">Забронировать</a> 
				</div>
				<div class="m2_catalog_both"></div>
				</div>			 	
	<?
	
	 
		}
	}
	
	public function act__history()
	{
		$apartament_id = (int)$_GET['apartament_id'];
		if (!$apartament_id) {
			echo "<div class='warn'>Не указан ID квартиры</div>";
			return;
		}

		// Получаем все брони по данной квартире, сортировка по дате убыванию
		$rows = $this->sql->query("SELECT * FROM broni WHERE apartament_id = ? ORDER BY date DESC", [$apartament_id]);

		if (!$rows || !count($rows)) {
			echo "<div class='info'>Броней по этой квартире пока не было</div>";
			return;
		}

		echo "<div class='broni-history'>";
		echo "<h3>История броней по квартире #{$apartament_id}</h3>";
		echo "<table class='table' border='1' cellspacing='0' cellpadding='4'>";
		echo "<tr>
			<th>ID</th>
			<th>Дата</th>
			<th>Статус</th>
			<th>Пользователь</th>
			<th>Комментарий</th>
			<th>Цена</th>
		</tr>";

		foreach ($rows as $r) {
			$user = $this->sql->get("SELECT login FROM users WHERE id = ?", [$r['user_id']]);
			$user_login = $user ? $user['login'] : '—';

			$status = $r['status'];
			$status_text = $this->status_name($status);

			echo "<tr>";
			echo "<td>{$r['broni_id']}</td>";
			echo "<td>{$r['date']}</td>";
			echo "<td>{$status_text}</td>";
			echo "<td>{$user_login}</td>";
			echo "<td>{$r['comment']}</td>";
			echo "<td>{$r['price']}</td>";
			echo "</tr>";
		}

		echo "</table>";
		echo "</div>";
	}

	 // ========================
    // Метод act__order — карточка квартиры и форма бронирования
    // ========================
  
  
  
  
  
   


 













  
  
  
   
   
public function get_apartment_by_id(int $apartment_id): ?array
{
    global $mysql;

    $sql = "
        SELECT 
		
			booking.broni_id AS b_id,
            booking.status AS b_status,
            booking.date AS b_date,
			home.title,
            
            user.*,
            agency.*,
            apartment.*,
            
            
            -- Поля с конфликтами
            apartment.home_id AS apartment_home_id,
            home.home_id AS home_home_id,

            apartment.status AS apartment_status,
            booking.status AS booking_status,

            booking.broni_id AS booking_id,
             booking.price AS b_price,

            user.id AS user_id,
            user.login AS user_login,
            user.name AS user_name,
            user.agency_id AS user_agency_id,

            agency.agency_id AS agency_id,
            agency.type AS agency_type,
            agency.add_datetime AS agency_add_datetime,
            agency.del AS agency_deleted
            
        FROM apartaments AS apartment
        LEFT JOIN homes AS home ON apartment.home_id = home.home_id
        LEFT JOIN broni AS booking ON apartment.status_broni_id = booking.broni_id
        LEFT JOIN users AS user ON booking.user_id = user.id
        LEFT JOIN agency AS agency ON user.agency_id = agency.agency_id
        WHERE apartment.apartament_id = {$apartment_id}
        LIMIT 1
    ";

    $data = $mysql->get_arr($sql, 1);



 if($_GET['dev'])
{
	print 'get_apartment_by_id';
	print '<pre>';
	print_r($data);
	print '</pre>';
}



    return $data ?: null;
}


public function get_apartment(int $home_id, int $apartment_num): ?array
{
 global $mysql;
 // Из вывода убрал брони так как они затерали таблицу если не 
//print '<br><br>';
      $sql = "
        SELECT 
		
			booking.broni_id AS b_id,
            booking.status AS b_status,
            booking.date AS b_date,
			
			home.title,
            homes_kvartal.title as kvartal_title,
			homes_sections.caption as section_caption,
            user.*,
            agency.*,
            apartment.*,
           
            
      
            apartment.home_id AS apartment_home_id,
            home.home_id AS home_home_id,

            apartment.status AS apartment_status,
            booking.status AS booking_status,

            booking.broni_id AS booking_id,
             booking.price AS b_price,

            user.id AS user_id,
            user.login AS user_login,
            user.name AS user_name,
            user.agency_id AS user_agency_id, 

            agency.agency_id AS agency_id,
            agency.type AS agency_type,
            agency.add_datetime AS agency_add_datetime,
            agency.del AS agency_deleted
            
        FROM apartaments AS apartment
		
		
		
		
		
        LEFT JOIN homes AS home ON apartment.home_id = home.home_id
		LEFT JOIN homes_sections AS homes_sections ON homes_sections.homes_sections_id = apartment.section_id

		LEFT JOIN homes_kvartal AS homes_kvartal ON homes_kvartal.homes_kvartal_id = home.homes_kvartal_id
        LEFT JOIN broni AS booking ON apartment.status_broni_id = booking.broni_id
        LEFT JOIN users AS user ON booking.user_id = user.id
        LEFT JOIN agency AS agency ON user.agency_id = agency.agency_id
        WHERE apartment.home_id = {$home_id} AND apartment.apartment_num = {$apartment_num}
       
    ";
//print '<br><br>';
    $data = $mysql->get_arr($sql, 1);
 //print_r($data);
 
 //print '<br><br>';
 
	if($_GET['dev'])
	{
		print 'get_apartment';
		print '<pre>';
		print_r($data);
		print '</pre>';
	}


    return $data ?: null;
}


   
   
   
 
  
















function act__order()
{
	$days_left=14; // Срок брони 
	
	if($_SESSION['agency_id']=='958') // ООО НОВЫЕ ТЕХНОЛОГИИ 5 дней на бронь 
	{
		$days_left=5;
	}
		
		
		
    global $mysql, $homes, $filed, $sa;

    $home_id       = (int)$_GET['home_id'];
    $apartment_num = (int)$_GET['apartment_num'];
    $subact        = $_GET['subact'] ?? '';
    $broni_id      = $_GET['broni_id'] ?? 0;

    // Получаем все данные по квартире, дому, брони, пользователю и агентству
    $data = $this->get_apartment($home_id, $apartment_num);



    if (!$data) {
        echo '<h2>Квартира не найдена</h2>';
        return;
    }

    // --- compred: блок «Добавить к предложению» ---
    $compred_list = [];
    $compred_msg = '';
    $compred_err = '';
    $compred_selected_id = 0;
    if (!empty($_SESSION['sh_id'])) {
        if (!empty($_GET['compred_ok'])) {
            $compred_msg = 'Квартира добавлена в предложение';
        }
        $compred_selected_id = (int)($_GET['compred_id'] ?? 0);
        if (!empty($_GET['compred_err'])) {
            $compred_err = urldecode((string)$_GET['compred_err']);
        }
        $compred_list = $mysql->get_arr(
            'SELECT compred_id, caption FROM compred
             WHERE user_id = ' . (int)$_SESSION['sh_id'] . ' AND del = 0
             ORDER BY updated_at DESC LIMIT 100'
        );
    }
    $compred_apartament_id = (int)($data['apartament_id'] ?? 0);
    $compred_return_url = 'iframe_router.php?ctr=apartments&act=order'
        . '&home_id=' . (int)$home_id
        . '&apartment_num=' . (int)$apartment_num
        . '&apartments=' . (int)($_GET['apartments'] ?? 0);

    // Извлекаем нужные части
    $apartment = array_filter($data, fn($k) => strpos($k, 'apartment_') === 0 || in_array($k, [
        'apartament_id', 'section_id', 'floor', 'price', 'price_m', 'area', 'rooms',
        'kitchen_area', 'text', 'adress', 'plan_code', 'status', 'status2',
        'status_broni_id', 'date', 'image_pb', 'plan_type', 'image', 'area2', 'area_t',
        'window_orient_1', 'window_orient_2'
    ]), ARRAY_FILTER_USE_KEY);

    $home = array_filter($data, fn($k) => strpos($k, 'home_') === 0, ARRAY_FILTER_USE_KEY);

    // Статус квартиры — приоритет: status2 квартиры
    $stat = $apartment['status2'] ?? $data['apartment_status'] ?? '';

    // Бронь — проверяем по псевдонимам
    $broni = array_filter($data, fn($k) => strpos($k, 'booking_') === 0 || in_array($k, ['b_id', 'b_status', 'b_date','b_price']), ARRAY_FILTER_USE_KEY);

    $err_m = [];
    $show_form = true;
    $success = '';
    $show_done_template = false;
    $done_message = '';

    // === 0. Обработка subact (если пришел из GET)
    if ($subact && !empty($broni) && !empty($broni['b_id'])) {
        if ($subact == 'upbroni') {
            $sa->up_broni($broni['b_id'], 4, 'Продление пользователем');
            $date_start = time();
            $date_end = $date_start + $days_left*24*3600;
           
            $done_message = "Бронь продлена!<br>Ваша бронь активна ещё <b>$days_left</b> дней — до <b>".date('d.m.Y', $date_end)."</b>.";
        } elseif ($subact == 'unsetbroni') {
            $sa->up_broni($broni['b_id'], 2, 'Отмена пользователем');
            $done_message = "Бронь отменена. Квартира снова доступна для бронирования.";
        } else {
            $done_message = "Неизвестное действие!";
        }

        $tpl_data = [
            'data' => $data,
            'apartment' => $apartment,
            'broni' => $broni,
            'stat' => $stat,
            'home_id' => $home_id,
            'apartment_num' => $apartment_num,
            'done_message' => $done_message
        ];

        $this->tpl($tpl_data, 'apartments', 'form_broni_done');
        return;
    }

    // --- 1. Проверка повторной брони (только для пользователей, не для админа) ---
    $is_user = !in_array($_SESSION['sh_login'], ['admin','em_nsv','demo_admin']);

    if (
        $broni
        && in_array($stat, ['4', '5'])
        && $data['user_id'] == $_SESSION['sh_id']
        && $is_user
    ) {
        $date_start = strtotime($data['b_date'] ?? '');
        $date_end = $date_start + $days_left*24*3600;
        $days_left = ceil(($date_end - time()) / (24*3600));

        if ($days_left > 0) {
            $done_message = "У вас уже есть действующая бронь на эту квартиру. <br> Цена брони - ".$data['b_price']." <br>
                Она будет действовать ещё <b>$days_left</b> дней — до <b>".date('d.m.Y', $date_end)."</b>.";
            $show_done_template = true;
            $show_form = false;
        } else {
            $success = "Ваша бронь на эту квартиру истекла.";
            $show_form = true;
        }
    }

    // --- 2. POST: обработка смены/создания брони ---
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$show_done_template) {
        // 1. Админ или агент: меняет статус через up_broni, если история уже есть
        if (in_array($_SESSION['sh_login'], ['admin','em_nsv','demo_admin'])) {
            if (isset($_POST['status'])) {
                $status = (int)$_POST['status'];
                $comment = 'Изменение статуса админом';
                $status_broni_id = isset($apartment['status_broni_id']) ? (int)$apartment['status_broni_id'] : 0;

                $valid_bron = 0;
                if ($status_broni_id > 0) {
                    $brn = $mysql->get_arr("SELECT broni_id FROM broni WHERE broni_id={$status_broni_id}", 1);
                    if ($brn && $brn['broni_id'] == $status_broni_id) $valid_bron = 1;
                }

                if ($valid_bron) {
                    $sa->up_broni($status_broni_id, $status, $comment);
                } else {
                    $sa->new_broni($home_id, $apartment_num, $status);
                }

                add_log('Статус квартиры изменён администратором');
                $success = "Статус квартиры изменён!";
            }

            if (isset($_POST['window_orient_1'])) {
                list($o1, $o2) = window_orient_normalize(
                    (int)($_POST['window_orient_1'] ?? 0),
                    0
                );
                $check = window_orient_validate($o1, null);
                if ($check['ok']) {
                    $apartament_id = (int)($apartment['apartament_id'] ?? $data['apartament_id'] ?? 0);
                    if ($apartament_id > 0) {
                        $mysql->update_for_key('apartaments', 'apartament_id', $apartament_id, [
                            'window_orient_1' => $o1,
                        ]);
                        add_log('Ориентация окон изменена администратором');
                        $success = ($success ? $success . '<br>' : '') . 'Ориентация окон сохранена.';
                    }
                } else {
                    $err_m[] = $check['error'];
                }
            }

            // Обновляем данные после изменений
            $data = $this->get_apartment($home_id, $apartment_num);
            $apartment = array_filter($data, fn($k) => strpos($k, 'apartment_') === 0 || in_array($k, [
                'apartament_id', 'section_id', 'floor', 'price', 'price_m', 'area', 'rooms',
                'kitchen_area', 'text', 'adress', 'plan_code', 'status', 'status2',
                'status_broni_id', 'date', 'image_pb', 'plan_type', 'image', 'area2', 'area_t',
                'window_orient_1', 'window_orient_2'
            ]), ARRAY_FILTER_USE_KEY);

            $broni = array_filter($data, fn($k) => strpos($k, 'booking_') === 0 || in_array($k, ['b_id', 'b_status', 'b_date']), ARRAY_FILTER_USE_KEY);
            $stat = $apartment['status2'] ?? $data['apartment_status'] ?? '';
        }

        // 2. Пользователь-агент: только если квартира свободна
        elseif ($show_form && $_SESSION['sh_id']) {
            if ($stat != '' && $stat != '2' && $stat != '5' && $stat != '0') {
                $err_m[] = 'Квартира уже забронирована другим пользователем';
            } else {
                $need_files = ['passport_scan','passport_scan2','anket'];
                foreach ($need_files as $f) {
                    if (empty($_FILES[$f]['type'])) $err_m[] = 'Необходимо загрузить '.$f;
                }

                if (!$err_m) {
                    $bron_id = $sa->new_broni($home_id, $apartment_num, 4);
                    add_log('Забронировано помещение');
                    $dir = "uploads/$bron_id/";
                    if (!file_exists($dir)) mkdir($dir, 0777, true);

                    $files = [];
                    foreach ($need_files as $idx => $f) {
                        if (!empty($_FILES[$f]['type'])) {
                            $ext = pathinfo($_FILES[$f]['name'], PATHINFO_EXTENSION);
                            $uploadfile = $dir . $f.'.'.$ext;
                            $files[] = $uploadfile;
                            if (move_uploaded_file($_FILES[$f]['tmp_name'], $uploadfile)) {
                                // ok
                            } else {
                                $err_m[] = $f.' не был загружен';
                            }
                        }
                    }

                    if (!$err_m) {
                        $date_start = time();
                        $date_end = $date_start + $days_left*24*3600;
                         
                        $done_message = "Квартира успешно забронирована!<br>Ваша бронь активна ещё <b>$days_left</b> дней — до <b>".date('d.m.Y', $date_end)."</b>.";

                        $tpl_data = [
                            'data' => $data,
                            'apartment' => $apartment,
                            'broni' => $broni,
                            'stat' => $stat,
                            'home_id' => $home_id,
                            'apartment_num' => $apartment_num,
                            'done_message' => $done_message
                        ];
                        $this->tpl($tpl_data, 'apartments', 'form_broni_done');
                        return;
                    }
                }
            }
        }
    }

    // --- Вывод шаблона ---
    $tpl_data = [
        'data' => $data,
        'apartment' => $apartment,
        'broni' => $broni,
        'stat' => $stat,
        'home_id' => $home_id,
        'apartment_num' => $apartment_num,
        'success' => $success,
        'err_m' => $err_m,
        'show_form' => $show_form,
        'done_message' => $done_message,
        'compred_list' => $compred_list,
        'compred_msg' => $compred_msg,
        'compred_err' => $compred_err,
        'compred_selected_id' => $compred_selected_id,
        'apartament_id' => $compred_apartament_id,
        'return_url' => $compred_return_url,
    ];

    if ($show_done_template) {
        $this->tpl($tpl_data, 'apartments', 'form_broni_done');
        return;
    }

    if (in_array($_SESSION['sh_login'], ['admin','em_nsv','demo_admin'])) {
        $this->tpl($tpl_data, 'apartments', 'form_broni_ag');
    } else {
        $this->tpl($tpl_data, 'apartments', 'form_broni_pub');
    }

    if ($_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'director') {
        $this->act__broni_history($home_id, $apartment_num);
    }
}



 

function act__broni_history($home_id, $apartment_num)
{
    global $mysql;

    // Получаем актуальные данные квартиры (цена и статус)
    $apt = $mysql->get_arr('SELECT * FROM apartaments WHERE home_id="'.intval($home_id).'" AND apartment_num="'.intval($apartment_num).'"', 1);

    // Получаем историю броней
      $q = '
        SELECT 
            broni.*, 
            broni.date as fdate, 
            broni.status as bstatus, 
            users.login, users.name, users.phone, users.e_mail, 
            agency.caption, 
            apartaments.price as curr_price, apartaments.status as curr_status, apartaments.rooms
        FROM `broni`
        LEFT JOIN users ON broni.user_id = users.id
        LEFT JOIN agency ON agency.agency_id = users.agency_id
        LEFT JOIN apartaments ON broni.home_id = apartaments.home_id 
                               AND broni.apartments_num = apartaments.apartment_num
        WHERE broni.home_id="'.intval($home_id).'" 
          AND broni.apartments_num="'.intval($apartment_num).'"
        ORDER BY broni.date DESC
    ';
	  
    $rows = [];
    $res = $mysql->c->query($q);
    if ($res) while ($row = $res->fetch_assoc()) $rows[] = $row;

    // Для статуса (общая карта)
    $status_map = [0=>'Свободна',2=>'Свободна',3=>'Продана',4=>'Забронирована',5=>'Бронь застройщика',6=>'Квартира подрядчика'];

    $data = [
        'history' => $rows,
        'apt' => $apt,
        'status_map' => $status_map
    ];
    $this->tpl($data, 'apartments', 'broni_history');
}













function act__card_ajaxform()
{ 
	global $formProtect;
	global $fw_mailer;
	header('Content-Type: application/json; charset=utf-8');
	try {
		$formProtect = new FormProtect();

		// 1. Валидация и все защиты одним методом
		$rules = [
			'home'    => 'required|string|min:3|max:64',
			'section_caption'    => 'required|string|min:3|max:64',
			'apartment_num'    => 'required|string|min:1|max:4',
			
			'fio'    => 'required|string|min:3|max:64',
			'phone'   => 'required|validPhone',
			'message' => 'string|max:300|noHtml|noLinks',
	 
		];
		$data = $formProtect->validateForm($rules); 

 
		$titles=[];
		$titles['home']='Дом';
		$titles['section_caption']='Секция';
		$titles['apartment_num']='Квартира';
		$titles['fio']='ФИО';
		$titles['phone']='Телефон';
		$titles['message']='Сообщение';
		 
				 
		/*		 
		
		ПРИЛОЖИТЬ ПЛАНИРОВКУ И ССЫЛКУ НА ШАХМАТКУ ! 
		 
		ЧИНИМ ПОЛНОЦЕННО ЛОАДМОД ИЗ КОРНЯ CORE ГРУЗИМ МЕССАДЖЕС И СОБИРАЕМ СООБЩЕНИЕ НАСТРАИВАЕМ ВАЛИДАЦИЮЮ ДЛЯ ВСЕХ ПОЛЕЙ ПОЛСТ !!!
		 ЮЗЕЕМ РОУТЕР НОВЫЙ елси не указан модуль старый если указан новый ! вначале в core потом в base проверяем слегка модифицированная загрузка 
		 
		*/
		// Собираем сообщение 
		 $message = fw_messages::build_message($data, $titles);
		 
		
		
		// Отправка письма нескольким получателям (указываем через запятую)
		$recipients = '89236470002@mail.ru,op@em-nsk.group'; //, ' 
		
	 
		if (!$fw_mailer->send($recipients, 'Заявка EM-NSK.RU - публичная карточка квартиры ', $message)) {
			// Если send_to_multiple() вернул false, сообщаем об ошибке
			$formProtect->fail('Не удалось отправить заявку. Попробуйте позднее.');
			exit; 
		}
	  
		// Если мы дошли до этой точки, значит send_to_multiple() вернул true
		$formProtect->ok('Ваша заявка успешно отправлена');

	} catch (Throwable $e) {
		// Этот блок сработает при исключениях
		error_log('[FormProtect Backend Error] ' . $e->getMessage());
		$formProtect->fail('Ошибка сервера. Попробуйте позднее.');
	}
}




 




function act__card()
{
    global $mysql, $homes, $filed, $sa;

    $home_id       = (int)$_GET['home_id'];
    $apartment_num = (int)$_GET['apartment_num'];
    $subact        = $_GET['subact'] ?? '';
    $broni_id      = $_GET['broni_id'] ?? 0;

    // Получаем все данные по квартире, дому, брони, пользователю и агентству
    $data = $this->get_apartment($home_id, $apartment_num);
	
	//print_r($data);
	
 


    if (!$data) {
        echo '<h2>Квартира не найдена</h2>';
        return;
    }

    // Извлекаем нужные части
    $apartment = array_filter($data, fn($k) => strpos($k, 'apartment_') === 0 || in_array($k, [
        'apartament_id', 'section_id', 'floor', 'price', 'price_m', 'area', 'rooms',
        'kitchen_area', 'text', 'adress', 'plan_code', 'status', 'status2',
        'status_broni_id', 'date', 'image_pb', 'plan_type', 'image', 'area2', 'area_t',
        'window_orient_1', 'window_orient_2'
    ]), ARRAY_FILTER_USE_KEY);

    $home = array_filter($data, fn($k) => strpos($k, 'home_') === 0, ARRAY_FILTER_USE_KEY);

    // Статус квартиры — приоритет: status2 квартиры
    $stat = $apartment['status2'] ?? $data['apartment_status'] ?? '';

    // Бронь — проверяем по псевдонимам
    $broni = array_filter($data, fn($k) => strpos($k, 'booking_') === 0 || in_array($k, ['b_id', 'b_status', 'b_date','b_price']), ARRAY_FILTER_USE_KEY);

    $err_m = [];
    $show_form = true;
    $success = '';
 
    $done_message = '';

   
 
 

    // --- Вывод шаблона ---
    $tpl_data = [
        'data' => $data,
        'apartment' => $apartment,
        'broni' => $broni,
        'stat' => $stat,
        'home_id' => $home_id,
        'apartment_num' => $apartment_num,
        'success' => $success,
        'err_m' => $err_m,
        'show_form' => $show_form,
        'done_message' => $done_message
    ];

 
	$this->tpl($tpl_data, 'apartments', 'public_card');
       
}


 





 
	
	
	function act__index()
	{
		global $t;
		$t['h1'] = 'Квартиры';
		
		global $filed;
		global $mysql;
		global $r;
		global $filed_errors;
	}
	
	
	
}