<?
class ctr__apartments extends ctr__
{  
 
	var $table = 'apartments'; //Главная таблица
	var $key_filed = 'apartments_id'; // Ключевое поле главной таблицы
	var $ctr = 'apartments';
    var $title = 'Квартиры';
   
	function __construct()
	{
		$this->data_arr = $this->get_data_arr(); // Получаем данные для отображения 
		$this->data_arr_ns = $this->get_data_arr(1); // Получаем данные не учитывая условий поиска (для форм фильтров)
		
		if($_GET['dev'])
		{
			//print '<pre>';
			// print_r($this->data_arr_ns);
			//print '</pre>';
		}
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
			
			
			
		$result['price'] = number_format($result['price'], 0, '.', ' ');  
		$result['price'] = ' '.$result['price'].' руб.';
	?>
				<div class="m2catalog_item">
				 
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
					<a class="m2catalog_item_order iframe" href="https://em.m2profi.pro/sahmatka/form_order.php?home_id=<?=$result['home_id']?>&apartment_num=<?=$result['apartment_num']?>&apartments=<?=$result['apartments']?>">Забронировать</a> 
				</div>
				<div class="m2_catalog_both"></div>
				</div>			 	
	<?
	
	 
		}
	}
	
	
	
	 
	
	
	
}