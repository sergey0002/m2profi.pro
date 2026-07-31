<?
session_start();
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
header('Access-Control-Allow-Origin: *'); 
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0,pre-check=0", false);
header("Cache-Control: max-age=0", false);
header("Pragma: no-cache");


error_reporting( E_ERROR ); 
include('config.php');


// Гет переменные перезаписывают пустые $_REQUEST - для наследования гет в каталоге
 foreach($_REQUEST as $k=>$v)
 {
	 if(!$_REQUEST[$k] && $_GET[$k]) { $_REQUEST[$k] = $_GET[$k]; }
 }

			 
class ctr__zapiskeys
{
	
	 
	function __construct()
	{
		$this->get_data_arr(); // Получаем данные для отображения 
	}
	
	function get_data_arr()
	{
		// ФУНКЦИИ МОДЕЛИ и контроллера
		
		//print_r($_POST);
		//print_r($_GET);
 
		  global $mysql;
		  $q = ' SELECT ';
		  
		 if($_GET['load']=='sel_home' || $_GET['load']=='sel_section' || $_GET['load']=='sel_apartment_num' || $_GET['load']=='sel_date')
		 {
			 $q.=' count(*) as c, ';
		 }
		 
		 $q.=' z2.date as z2date, z2.time as z2time, zapis.* ,apartaments.floor, apartaments.rooms, apartaments.area ,homes.long_title
		 
			FROM `zapis` 
			LEFT JOIN  apartaments ON apartaments.home_id = zapis.home_id AND zapis.apartment_num = apartaments.apartment_num
			LEFT JOIN  homes ON homes.home_id = apartaments.home_id


LEFT JOIN  zapis as z2 ON z2.home_id = zapis.home_id AND zapis.apartment_num = z2.apartment_num AND z2.del="0"
 
			where  1=1 
			
			AND zapis.section!="0" 
			AND zapis.section!="" 
			AND long_title!="" 
			AND show_keys=1
			';
			if( !$_POST['show_dell'] && !$_GET['show_dell'] ){ $q.=' AND zapis.del="0"  ';}
			if( $_POST['pom'] || $_GET['pom'] ){ $q.=' AND zapis.pom="1"  ';}
			 
			
			
			if( $_POST['home'] ){ $q.=' AND apartaments.home_id = "'. $_POST['home'].'" ';}
			if( $_POST['section'] ){ $q.=' AND apartaments.section_id = "'. $_POST['section'].'" ';}
			if( $_POST['apartment_num'] ){ $q.=' AND apartaments.apartment_num = "'. $_POST['apartment_num'].'" ';}
			if( $_POST['date'] ){ $q.=' AND zapis.date = "'.$_POST['date'].'" ';}
			if( !$_POST['arhiv'] ){ $q.=' AND `zapis`.`date` >= CURDATE() ';}
			//else{}

			// Группировка 
			if( $_GET['load']=='sel_home' ){ $q.='GROUP BY home_id ORDER BY home_id';}
			elseif( $_GET['load']=='sel_section' ){ $q.='GROUP BY section ORDER BY section';}
			elseif( $_GET['load']=='sel_apartment_num' ){ $q.='GROUP BY apartment_num ORDER BY apartment_num';}
			elseif( $_GET['load']=='sel_date' ){ $q.='GROUP BY zapis.date ORDER BY zapis.date DESC';}
			elseif( $_GET['load']=='data' ){ $q.='  ORDER BY zapis.date DESC , zapis.time ASC';}
			
			  //print $q;
			
			$this->data_arr =$mysql->get_arr($q);
			
			//print_R($this->data_arr);
	}


 
	// функции вида
	
	
	function ajax__sel_home()
	{	
		foreach($this->data_arr as $k => $v)
		{
			?>
			<option value="<?=$v['home_id']?>"><?=$v['long_title']?> (<?=$v['c']?>)</option>
			<?
		}
	}
	
	
	
	function ajax__sel_section()
	{		
		foreach($this->data_arr as $k => $v)
		{
			?>
			<option value="<?=$v['section']?>"><?=$v['section']?> (<?=$v['c']?>)</option>
			<?
		}
	}
	
	function ajax__sel_apartment_num()
	{
		foreach($this->data_arr as $k => $v)
		{
			?>
			<option value="<?=$v['apartment_num']?>"><?=$v['apartment_num']?>  </option>
			<?
		}
	}
	
	function ajax__sel_date()
	{
		foreach($this->data_arr as $k => $v)
		{
			?>
			<option value="<?=$v['date']?>"><?=date('d.m.Y',strtotime( $v['date'])) ?> (<?=$v['c']?>)</option>
			<?
		}
	}
	
	
	function ajax__data()
	{
		// print_R( $this->data_arr);
		
		 
		foreach( $this->data_arr as $k => $result )
		{
			
			
			if(!$result['pom']){$pom='<span style="color:green;">нет</span>';}
			else{$pom='<span style="color:red;">да</span>';}
			
			
			 echo    '<tr'; if($result['del']){ print ' class="del" ' ;} print '>';
			 echo '
					  <td><span>'.$result['zapis_id'].'</span></td>'.
					 '<td style="white-space: nowrap;"><span>'
					 
					 .date('d.m.Y',strtotime( $result['date']));
					 
					 echo '</span>';
					 if($result['del'] && $result['z2date'] ){ print '<br/><span style="color:#EEE; text-decoration:none;">'. date('d.m.Y',strtotime( $result['z2date'])).'</span>'  ; }
					 echo '</td>';
					 
					 echo '<td><span>'.$result['time'];
					  echo '</span>';
					 if($result['del'] &&  $result['z2time'] ){ print '<br/><span style="color:#EEE; text-decoration:none;">'.  $result['z2time'].'</span>'; }
					 
					 echo ' </td>'.
					 '<td><span>'.$result['long_title'].'</span></td>'.
					 '<td><span>'.$result['section'].'</span></td>'.
					 '<td><span>№'.$result['apartment_num'].' ('.$result['floor'].'эт, '.$result['rooms'].'к, '.$result['area'].'м<sup>2</sup>)</span></td>' .
					 '<td><span>'.$result['phone'].'</span></td>' .
					 '<td><span>'.$pom.'</span></td>' .
					 '<td><span>'.$result['fio'].'</span></td>' ;
					
					print '<td style="wordwrap:nowrap;">';
					 
					   print '<a href="iframe_router.php?ctr=zapiskeys&act=card&id='.$result['zapis_id'].'" style="color:#0000ff; font-size: 18px; " class="iframe_rajax ">i</a>&nbsp;&nbsp;';
					  
					if($_SESSION['sh_login']=='admin' || $_SESSION['sh_login']=='op15')
					{
						 print '<a href="iframe_router.php?ctr=zapiskeys&act=edit&id='.$result['zapis_id'].'" style="color:green; " class="iframe_rajax table-edit"></a>&nbsp;&nbsp;';	
						 print '<a href="ctrind.php?ctr=zapiskeys&act=del&id='.$result['zapis_id'].'" style="color:red; font-size: 18px; " onclick="return confirm(\'Вы действительно хотите удалить запись.\');">X</a> ';	
					}
					print '</td>';
		}
	}
	
	
	
}
















class ctr__catalog
{
	
	
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
		// ФУНКЦИИ МОДЕЛИ и контроллера
		
		//print_r($_POST);
		//print_r($_GET);
 
		  global $mysql;
		  $sql = ' SELECT ';
		  
		 if($_REQUEST['load']=='sel_home' || $_REQUEST['load']=='sel_rooms_k' || $_REQUEST['load']=='sel_min_price' ||   $_REQUEST['load']=='sel_max_price' || $_REQUEST['load']=='sel_floor')
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
		  apartaments.* , CAST(apartaments.rooms AS UNSIGNED) as rooms_int, group_concat(DISTINCT apartaments.floor ORDER BY apartaments.floor ASC SEPARATOR ", ") as floors, homes.* FROM `apartaments` 
		  LEFT JOIN homes on (apartaments.home_id = homes.home_id)
		  WHERE  image_pb !="" AND (status2 is null or status2 = 2 or  status2 = "0" ) AND (homes.show=1  )
		';
// ДЛЯ АДМИНА ДОБАВИТЬ or homes.show=3 - оп or homes.show=2 - админ 

		if(!$no_search) // не испольовать условия поиска
		{
		if( $_REQUEST['min_price'] && $_REQUEST['load']!='sel_min_price' ){ $sql .=' AND price>="'.$_REQUEST['min_price'].'" '; }
		if( $_REQUEST['max_price'] && $_REQUEST['load']!='sel_max_price' ){ $sql .=' AND price<="'.$_REQUEST['max_price'].'" '; }

		if($_REQUEST['home']=='001'){$_REQUEST['home']=''; $_REQUEST['object']=1;}
		if($_REQUEST['home']=='002'){$_REQUEST['home']=''; $_REQUEST['object']=2;}
		if($_REQUEST['home']=='003'){$_REQUEST['home']=''; $_REQUEST['object']=3;}
		if($_REQUEST['home']=='004'){$_REQUEST['home']=''; $_REQUEST['object']=4;}
		if($_REQUEST['home']=='005'){$_REQUEST['home']=''; $_REQUEST['object']=5;}
		if($_REQUEST['home']=='006'){$_REQUEST['home']=''; $_REQUEST['object']=6;}
		
		if($_REQUEST['home'] && $_REQUEST['load']!='sel_home'){ $sql .=' AND apartaments.home_id="'.$_REQUEST['home'].'" '; }
		
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
		
		// Серии домов смарт грин итп
		// БРАТЬ ИЗ ТАБЛИЦЫ ДОМОВ!
		if( $_REQUEST['object'] && $_REQUEST['object']==1 ){ $sql .=' AND apartaments.home_id IN(3,6,9,10) '; } // смарт
		if( $_REQUEST['object'] && $_REQUEST['object']==2 ){ $sql .=' AND apartaments.home_id IN(17) '; } // лайф
		if( $_REQUEST['object'] && $_REQUEST['object']==3 ){ $sql .=' AND apartaments.home_id IN(12,15,16) '; } // ГРИН
		if( $_REQUEST['object'] && $_REQUEST['object']==4 ){ $sql .=' AND apartaments.home_id IN(20,21,23,25,26,27,28,29,30,31,32,34) '; } // Infinity
		if( $_REQUEST['object'] && $_REQUEST['object']==5 ){ $sql .=' AND apartaments.home_id IN(22,24) '; } // RED 

		// Сданные дома
		if( $_REQUEST['sdan'] && $_REQUEST['sdan']==1 ){ $sql .=' AND ( homes.complite = "1"  )'; }
		elseif( $_REQUEST['sdan'] && $_REQUEST['sdan']==2 ){ $sql .=' AND ( homes.complite = "0" and homes.show_keys="0" ) '; }
		
		
		}
		
		
 
			// Группировка 
			if( $_REQUEST['load']=='sel_home' ){ $sql .=' GROUP BY apartaments.home_id ORDER BY kvartal, homes.title';}
			
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
		 
		foreach($this->data_arr_ns as $k => $v)
		{
			// форматирование для некоторых полей
			if($filed_v == 'price')
			{
				$value=number_format($v[$filed_v], 0, '.', ' ');
			}
			else{$value=$v[$filed_v];}
			
			if($this->data_arr[$k])
			{
				
				?>
				<option value="<?=$v[$filed_k]?>" <?if($_REQUEST[$filed_req]==$v[$filed_k]){?>selected="selected"<?}?>><?=$value?>  
				<?
				if( $filed_c && $this->data_arr[$k][$filed_c] ){	?>(<?=$this->data_arr[$k][$filed_c]?>)<?	}
				?>
				</option>
				<?
			}
			elseif($noactval)
			{
				?>
				<optgroup label="<?=$value?>" style="font: 400 16px/49px Montserrat, sans-serif; font-weight:100; color:#BDBDBD;"></optgroup>
				<?
			}
		}
	}




	
	function ajax__sel_home()
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
			
			// форматирование для некоторых полей
			if($filed_v == 'price')
			{
				$value=number_format($v[$filed_v], 0, '.', ' ');
			}
			else{$value=$v[$filed_v];}
			
			if($this->data_arr[$k])
			{
				?>
				<option value="<?=$v[$filed_k]?>" <?if($_REQUEST[$filed_req]==$v[$filed_k]){?>selected="selected"<?}?>><?=$value?>  
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
	
	
	
	
	function ajax__sel_rooms_min()
	{		
		$this->select_items('rooms_int','rooms_int','rooms_min','c'); 
		 
	}
	
	
	function ajax__sel_rooms_max()
	{		
		$this->select_items('rooms_int','rooms_int','rooms_max','c'); 
		 
	}
	
	
	
	
	function ajax__sel_rooms_k()
	{		
		$this->select_items('rooms','rooms','rooms_k','c'); 
		 
	}
	
	function ajax__sel_min_price()
	{
		$this->select_items('price','price','min_price',0,0); 
	}
 
	function ajax__sel_max_price()
	{
		$this->select_items('price','price','max_price',0,0); 
	}
 
	function ajax__sel_floor()
	{
		$this->select_items('floor','floor','floor','c'); 
	}
	
	 
	 
	 
	 
	 
	function ajax__data()
	{
		print '<div style="padding:5px;">Найдено: '.count($this->data_arr) .' вариантов планировок</div>  ';

		 // print $this->q;
		print '<pre>';
		// print_r($this->data_arr);
		print '</pre>';
		
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
						<span><b>Дом:</b> №<?=$result['long_title']?> </span> 
						<hr/>
						<span><b>Секция:</b> <?=$result['section_id']?> </span>
						<span><b>Этаж:</b> <?=$result['floors']?>   </span> <br/>
						<span><b>Комнат:</b> <?=$result['rooms']?> </span>
						<span><b>Площадь:</b> <?=$result['area']?> м<sup>2</sup>  </span>
				 </div>
				 <div class="m2catalog_item_order_frame">
					<div class="m2catalog_item_price"><?=$result['price']?></div>
					<a class="m2catalog_item_order iframe" href="<?=$GLOBALS['config']['domains']['em']?>/sahmatka/form_order.php?home_id=<?=$result['home_id']?>&apartment_num=<?=$result['apartment_num']?>&apartments=<?=$result['apartments']?>">Забронировать</a> 
				 </div>
				 <div class="m2_catalog_both"></div>
				 </div>			 	
	<?
	
	 
		}
	}
	
	
	
}































#################### РОУТЕР ##########################
if($_GET['controller'])
{
	$class = 'ctr__'.$_GET['controller'];
	if( class_exists( $class ) )
	{
		$controller = new $class();
	}
}
 

	
if($_GET['load'] && $controller )
{
	$method='ajax__' . $_GET['load'];
	
	if( method_exists( $controller , $method ) )
	{
		$controller->$method();
	}
	
}
######################################################


if($_GET['action']=='user')
{
	?>
	<div id="edit-users" class="callback-form">
		<div class="callback-form__title">Редактирование пользователя</div>
		<form action="#">
			<select data-placeholder="Агенство">
				<option></option>
				<option>Русбизнес</option>
				<option>ЦАН</option>
				<option>Формула Недвижимости</option>
				<option>Русбизнес</option>
				<option>ЦАН</option>
				<option>Формула Недвижимости</option>
			</select>
			<input type="text" placeholder="Логин">
			<input type="text" placeholder="Пароль">
			<input type="text" placeholder="ФИО">
			<input type="text" placeholder="E-mail">
			<input class="phone-in" type="tel" placeholder="Телефон">
			<button class="btn btn_notbdrs callback-form__btn">Сохранить измененения<i></i></button>
		</form>
	</div>
	
	<?
	
	
}
elseif($_GET['action']=='agency')
{
	?>
		<div id="edit-agency" class="callback-form">
		<div class="callback-form__title">Редактирование агентства</div>
		<form action="#">
			<input type="text" placeholder="Название агенства">
			<input type="text" placeholder="Логин">
			<input type="text" placeholder="Пароль">
			<input type="text" placeholder="Фио">
			<input type="text" placeholder="E-mail">
			<input class="phone-in" type="tel" placeholder="Телефон">
			<button class="btn btn_notbdrs callback-form__btn">Сохранить измененения<i></i></button>
		</form>
		</div>
	<?
	
}
elseif($_GET['action']=='exc')
{
	?>
		<div id="edit-tour" class="callback-form">
		<div class="callback-form__title">Редактирование экскурсии</div>
		<form action="#">
			<input type="text" data-date-format="d.m.yyyy" class="datepicker-here" placeholder="Дата экскурсии">
			<input type="text" class="timepicker-here" placeholder="Время экскурсии">
			<input type="text" data-date-format="d.m.yyyy" class="datepicker-here" placeholder="Дата записи">
			<input type="text" placeholder="ФИО">
			<input class="phone-in" type="tel" placeholder="Телефон">
			<input type="text" placeholder="Объект">
			<button class="btn btn_notbdrs callback-form__btn">Сохранить измененения<i></i></button>
		</form>
	</div>
	<?
	
}
elseif($_GET['action']=='keys')
{
	?>
		<div id="edit-key" class="callback-form">
		<div class="callback-form__title">Редактирование выдачи ключей</div>
		<form action="#">
			<input type="text" data-date-format="d.m.yyyy" class="datepicker-here" placeholder="Дата">
			<input type="text" class="timepicker-here" placeholder="Время">
			<input type="text" placeholder="Дом">
			<input type="text" placeholder="Секция">
			<input type="text" placeholder="Квартира">
			<input class="phone-in" type="tel" placeholder="Телефон">
			<input type="text" placeholder="ФИО">
			<button class="btn btn_notbdrs callback-form__btn">Сохранить измененения<i></i></button>
		</form>
	</div>
	<?
	
}
elseif($_GET['action']=='agency_add')
{
	?>
		<div id="add-agency" class="callback-form">
		<div class="callback-form__title">Добавить агенство</div>
		<form action="#">
			<input type="text" placeholder="Название агентства">
			<input type="text" placeholder="Логин администратора">
			<input type="text" placeholder="Пароль">
			<input type="text" placeholder="ФИО">
			<input class="phone-in" type="tel" placeholder="Телефон">
			<input type="text" placeholder="E-mail">
			<button class="btn btn_notbdrs callback-form__btn">Сохранить измененения<i></i></button>
		</form>
	</div>
	<?
	
}
	
	
	
	
elseif($_GET['action']=='sel_appartment_num')
{
	?>
		 
			<input type="text" placeholder="Название агентства">
			<input type="text" placeholder="Логин администратора">
			<input type="text" placeholder="Пароль">
			<input type="text" placeholder="ФИО">
			<input class="phone-in" type="tel" placeholder="Телефон">
			<input type="text" placeholder="E-mail">
			 
	<?
	
}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	