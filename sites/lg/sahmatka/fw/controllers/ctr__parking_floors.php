<?
if($_GET['bid'])
{
	$_GET['parking_building_id'] = $_GET['bid']; // для аякс запросов карты 
}


class ctr__parking_floors extends ctr__
{  

	var $table = 'parking_floors'; //Главная таблица
	var $key_filed = 'parking_floor_id'; // Ключевое поле главной таблицы
	var $ctr = 'parking_floors';
    var $title = 'Поэтажные планы парковок';
 
 
 
	function __construct()
	{
		
		$data=$this->getfiltr(); // Получаем данные для вывода
		$this->data = $data; // Сохраняем данные
			 
		$data_nofiltr=$this->getfiltr([1]); // Данные без фильров для селектов?! очень ресуроемко ! по сути все брони перебирает циклом , но с другой стороны у нас все записи выводятся и ничего 
		$this->data_nofiltr=$data_nofiltr; // Сохраняем данные
		
		/*
		Перезагружать содержимое селектов при каждой выборке по хорошему? только те которые не указаны в гет запросе?
		+ в гет запросе указывать только не нулевые!
		
		+ как то псевдонимы прикрутить к гет запросам?!
		*/
		
		// Выводимые столбцы
		$titles = array();
		$titles[$this->key_filed] = 'id';
		$titles['adress_disp'] = 'Адрес';
		$titles['floor'] = 'Этаж';
		$titles['plan_file'] = 'План'; 
		$titles['c'] = 'Мест'; 
		$titles['edit'] = 'Действия'; 
		$this->ajcrud_table_titles=$titles;
		
		// Не переносить по словам
		$nowrap=array();
		// $nowrap['date']=1;
		
		$this->ajcrud_table_nowrap=$nowrap;
		
		// Сортировать по столбцам
		$order=array();
		$order[$this->key_filed]=$this->key_filed;
		$order['adress_disp']='adress_disp';
		$this->ajcrud_table_order=$order; 
		
		$this->aj_crud_addbutton=1;
		// $this->display_table_exrow=1; // раскрывать строки
	}
	
	
	
	
 	// БАзовый запрос  menu
	function get_base_sql($filtr_data='')
	{
		//if(!$filtr_data){$filtr_data =  $_REQUEST;}
		
		// ПРиоритетно ставим гет переменные
		foreach($_GET as $k=>$v){  $filtr_data[$k]=$v;	}
 
		$q = 'SELECT '.$this->table.'.* ,
		count(`parking_spaces`.`parking_space_id` ) as c ,
		parking_buildings.adress,parking_buildings.adress_disp, parking_buildings.street
		FROM  '.$this->table.'   ';
		$q.=' LEFT JOIN parking_buildings ON parking_buildings.parking_building_id = parking_floors.parking_building_id ';
		$q.=' LEFT JOIN parking_spaces ON parking_spaces.parking_floor_id = parking_floors.parking_floor_id ';
		$q.='  WHERE 1=1 ';
		
		// $q.=' AND `parking_spaces`.`del`="0" '; // Перестает отображаться в списках выбора места так как нет парковок в нем!)
		$q.=' AND `parking_buildings`.`del`="0" ';
		$q.=' AND `parking_floors`.`del`="0" ';
		
		
		if(!$filtr_data['showdel']){	$q.=' AND `'.$this->table.'`.`del`="0" ';	}
		if($filtr_data['id']){	$q.=' AND `'.$this->table.'`.`'.$this->key_filed.'`="'.$filtr_data['id'].'" ';	}
		
		if($filtr_data['parking_building_id']){	$q.=' AND `parking_buildings`.`parking_building_id`="'.$filtr_data['parking_building_id'].'" ';	}
		
		
		
		
		
		$q.=' GROUP BY `'.$this->table.'`.`'.$this->key_filed.'` ';
		// if($_GET['id']){$q.=''}
		// print $q;
		return $q;
	}
	
	
	 
	 
	 
	 
	#######################
	####################### ОТОБРАЖЕНИЕ В КАБИНЕТЕ И НА ПУБЛИЧНОМ ПЛАНЕ 
	#######################
	
	
	
	// ОТОБРАЖЕНИЕ МЕСТА В КАБИНЕТАХ И ПУБЛИЧНОМ САЙТЕ
	function printspace_disp($data='')
	{
		global $status_arr;
		$price =  number_format($data['price'], 0, '.', ' ');
		 
		if(!$data['status']){$data['status']=2; }
		
		if($_SESSION['sh_login'] == 'admin' || $_SESSION['agency_id'] == 92 )
		{
			if($data['status']==2){$status_class='car_g'; }
			elseif($data['status']==3){$status_class='car_r'; $href ='#';}
			elseif($data['status']==4){$status_class='car_y'; $href ='#';}
			elseif($data['status']==5){$status_class='car_b'; $href ='#';}
			else{$data['status']==6; $status_class='car_f'; $href ='#';}
		}
		else
		{
			if($data['status']==2){$status_class='car_g'; }
			elseif($data['status']==3){$status_class='car_r'; $href ='#';}
			elseif($data['status']==4){$status_class='car_y'; $href ='#';}
			elseif($data['status']==5){$status_class='car_r'; $href ='#';}
			else{$data['status']==6; $status_class='car_r'; $href ='#';}
		}
		if($_SESSION['sh_login']=='admin')
		{
			$href   = 'https://' . $GLOBALS['config']['domain'] . '/'sahmatka/iframe_router.php?ctr=parking_spaces&act=order&id='.$data['parking_space_id'].'';
	 
			if($data['status_broni_id'])
			{
				if($data['login']=='admin'){  $data['caption']='';}
				$br_info='<br/><br/><b>'.$status_arr[$data['status']].'</b> <br/>'.fromsql_date($data['date']).'<br/>'.$data['login'].' - '.$data['name'].' <br> <b>'.$data['caption'].'</b><br/>';
			}
 
			?>
			<a  href="<?=$href?>" rel="tooltip"  title="Место №<?=$data['num']?><br/><b><?=$price?></b><br/><?=$data['area']?>м<sup>2</sup> <?=$br_info?>" data-id="<?=$data['parking_space_id']?>" class="iframe_r car <?=$status_class?>" style="left:<?=$data['x']?>px; top:<?=$data['y']?>px; transform:rotate(<?=$data['rotate']?>rad);"> 
				<span class="pk_num"><?=$data['num']?></span> 
				<span class="pk_price"><?=$price?></span> 
				<span class="pk_area"><?=$data['area']?></span> 
			</a>
			<input class="place_ch" style="display:none;" type="checkbox" name="places[]" id="place__<?=$data['parking_space_id']?>" value="<?=$data['parking_space_id']?>" />
			<?
		}
		elseif( $_SESSION['sh_login'] &&  $_SESSION['agency_id'] == 92 ) // Все залогиненые - кликть можно на все квартиры карточка должна меняться в зависимости от прав
		{
			if($data['status_broni_id'])
			{
				if($data['login']=='admin'){   $data['caption']='';}
				$br_info='<br/><br/><b>'.$status_arr[$data['status']].'</b> <br/>'.fromsql_date($data['date']).'<br/>'.$data['login'].' - '.$data['name'].' <br> <b>'.$data['caption'].'</b><br/>';
			}			
			
			if($data['status']!=2){$price='';}else{$price.='<br/>';}
			$href   = 'https://' . $GLOBALS['config']['domain'] . '/'sahmatka/iframe_router.php?ctr=parking_spaces&act=order&id='.$data['parking_space_id'].'';
			?>
			<a  href="<?=$href?>" rel="tooltip"  title="Место №<?=$data['num']?><br/><?=$price?><br/><?=$data['area']?>м<sup>2</sup> <?=$br_info?>" data-id="<?=$data['parking_space_id']?>" class="iframe_r car <?=$status_class?>" style="left:<?=$data['x']?>px; top:<?=$data['y']?>px; transform:rotate(<?=$data['rotate']?>rad);"> 
				<span class="pk_num"><?=$data['num']?></span> 
				<span class="pk_price"><?=$price?></span> 
				<span class="pk_area"><?=$data['area']?></span> 
			</a>
			<?
		}
		elseif( $_SESSION['sh_login'] ) // Все залогиненые - кликть можно на все квартиры карточка должна меняться в зависимости от прав
		{
			if($data['status']!=2){$price='';}else{$price.='<br/>';}
			$href   = 'https://' . $GLOBALS['config']['domain'] . '/'sahmatka/iframe_router.php?ctr=parking_spaces&act=order&id='.$data['parking_space_id'].'';
			?>
			<a  href="<?=$href?>" rel="tooltip"  title="Место №<?=$data['num']?><br/><?=$price?> <?=$data['area']?>м<sup>2</sup>" data-id="<?=$data['parking_space_id']?>" class="iframe_r car <?=$status_class?>" style="left:<?=$data['x']?>px; top:<?=$data['y']?>px; transform:rotate(<?=$data['rotate']?>rad);"> 
				<span class="pk_num"><?=$data['num']?></span> 
				<span class="pk_price"><?=$price?></span> 
				<span class="pk_area"><?=$data['area']?></span> 
			</a>
			<?
		}
		else
		{
			if($data['status']!=2){$price='';}else{$price.='<br/>';}
			$href   = 'https://' . $GLOBALS['config']['domain'] . '/'sahmatka/iframe_router.php?ctr=parking_spaces&act=broni&id='.$data['parking_space_id'].'';
			?>
			<a  href="<?=$href?>" rel="tooltip"  title="Место № <?=$data['num']?><br/><?=$price?>  <?=$data['area']?>м<sup>2</sup>" data-id="<?=$data['parking_space_id']?>" class="iframe  car <?=$status_class?>" style="left:<?=$data['x']?>px; top:<?=$data['y']?>px; transform:rotate(<?=$data['rotate']?>rad);"> 
				<span class="pk_num"><?=$data['num']?></span> 
				<span class="pk_price"><?=$price?></span> 
				<span class="pk_area"><?=$data['area']?></span> 
			</a>
			<?
		}
	}
	
 
 
 
 
 
 
 function act__bronin()
 {
	 
	 global $mysql;
	 
	 $id = (int) $_REQUEST['id'];
	 $q = 'SELECT parking_spaces.* , 
		parking_floors.floor as floor, parking_buildings.adress, parking_buildings.street,  parking_buildings.adress_disp  

		FROM  parking_spaces   ';
		 
		$q.=' LEFT JOIN parking_buildings ON parking_buildings.parking_building_id = parking_spaces.parking_building_id ';
		
		$q.=' LEFT JOIN parking_floors ON parking_floors.parking_floor_id = parking_spaces.parking_floor_id ';
		
		$q.='  WHERE 1=1 AND parking_space_id = "'.$id.'"';
		
		$data = $mysql->get_arr($q,1);
		
	  
	 
	 $this->tpl($data,'parking_floors','bronin');
 
	 
 }
 
 
 
 
	// ВЫВОД ЭТАЖА 
	function disp_floor($id='',$space_tpl='')
	{
		global $t;
		global $filed;
		global $mysql;
		global $r;

		$filtr = array();
		$filtr['id'] = $id;
		$q = $this->get_base_sql($filtr);
		$data = $mysql->get_arr($q);
		$data=$data[0];
		//print '<h2>Редактирование объекта </h2>';
		?>
			<div style="text-align:center; font-weight:bold; font-size:20px; text-transform: uppercase; over"><?=$data['floor']?> этаж</div>
		 	<div class="row">
				<div class="col-md-12">
				<div id="save_text"><br/></div>	
				<div style="max-width:100%; overflow-x: scroll;">
				<?
				 $width = $data['plan_width'];
				 if($data['parking_floor_id'] == '10' || $data['parking_floor_id'] == '11')
				 {
					$bp = '  background-position: 0 30px; ';
				 }
				?>
					<div class="de_plan" style="width:<?=$width?>px; <?=$bp?>  min-height: 500px;  background-image:url('<?=$data['plan_file']?>'); background-size: contain; background-repeat: no-repeat; position:relative;">
					<img src="<?=$data['plan_file']?>" style="visibility:hidden; margin-bottom:50px; width:<?=$width?>px;" />
					<?
						// получаем и выводим места
						//$spaces_data_arr = $mysql->get_arr('SELECT * FROM parking_spaces WHERE parking_floor_id = "'.$id.'" AND `parking_spaces`.`del`="0" ');
						
						$spaces_data_arr = $mysql->get_arr('SELECT parking_spaces.* ,users.*, agency.caption,parking_broni.date FROM parking_spaces 
						
						LEFT JOIN parking_broni ON   parking_broni.parking_broni_id = parking_spaces.status_broni_id
						LEFT JOIN users ON users.id =parking_broni.user_id
						LEFT JOIN agency ON agency.agency_id = users.agency_id
		
						WHERE 
						
						parking_spaces.parking_floor_id = "'.$id.'" AND `parking_spaces`.`del`="0" ');
						
						
						
						foreach($spaces_data_arr as $k=>$v)
						{
							$this->printspace_disp( $v );
						}
					?> 
					</div> 
				</div> 
				</div>
			</div>
			 
		<?
		$this->tpl('','parking_floors','status_legend'); // Легенда со статусами
	}
	
	
	
	### ВИДЖЕТ ПОЭТАЖНЫХ ПЛАНОВ вывод дома
	function act__public_wiget_display($parking_building_id='',$parking_floor_id='')
	{
	 
		global $mysql;
		$data = $mysql ->get_arr(' SELECT * FROM parking_buildings WHERE parking_building_id = "'.$_GET['parking_building_id'].'" AND del=0 ');
		print '<h1>'.$data['0']['adress_disp'].'</h1>';
		
		$this->act__display_bf($parking_building_id='',$parking_floor_id='');
	}
	
	
	 
	
	// ОТОБРАЖАТЬ ВСЕ ЭТАЖИ ИЛИ УКАЗАННЫЙ ЭТАЖ ЗДАНИЯ  
	function act__display_bf($parking_building_id='',$parking_floor_id='')
	{
		global $mysql;
		
		if(!$parking_building_id) {  $parking_building_id = (int) $_GET['parking_building_id']; }
		if(!$parking_floor_id) {  $parking_floor_id = (int) $_GET['parking_floor_id']; }
		
		// Стили отображения этажа
		$this->tpl('','parking_floors','style_floor_display');
		
		if($parking_floor_id) // Один этаж
		{
			$this->disp_floor($parking_floor_id);
		}
		else
		{
			$floors = $mysql ->get_arr(' SELECT * FROM parking_floors WHERE parking_building_id = "'.$_GET['parking_building_id'].'" AND del=0 ORDER by parking_floors.floor');
			foreach($floors as $k=>$v)
			{
				$this->disp_floor($v['parking_floor_id']);
			}
		}
	}		 
			 
			 
			 
			 
			 
			 
			 
			 
			 
			 
			 
			 
			 
			 
			 
			 
			 
			 
			 
			 
			 
			 
			 

	////////////////////////////////////////////
	function act__catalog()
	{
		global $mysql;
		global $t;
		$t['h1'] = 'Парковки';
		
		$parking_building_id = (int) $_GET['parking_building_id'];
		 
		$adress_arr = $mysql ->get_arr('SELECT * FROM parking_buildings  WHERE parking_buildings.del="0" ORDER by parking_buildings.order ');
		foreach($adress_arr as $k=>$v)
		{
			$sreet_arr[$v['street']][] = $v;
		}
		?>
		<style>
			.mdef{ padding:5px; padding-left:7px; padding-right:7px; font-weight:bold; font-size:18px; font-weight:bold; font-size:18px;}	
			.objmenua .mdef{ color:#000;  }
			.mdefa{ color:#FFA500;} /* ТОлько админам */
			.mdefaop{ color:#999999;} /*  Админам и отделу продаж */
			.mdefth{ color:#FFF; background-color:#00CDAD;  }			 
			.mdef:hover{ color:#FFF; background-color:#00CDAD;}	
			@media screen and (min-width: 1000px) {
			  .mmenu{ display:block;	padding-right:0;  margin-top:15px;    display: flex;    flex-direction: row;    justify-content: space-between;		width: 100%;}
			  .mobilenav{display:none;}
			}
			@media screen and (max-width: 1000px) {
			  .mmenu{	display:none;		}
			  .mobilenav{display:block; width:100%;}
			  .nomobile{display:none;}
			}
			.mdef ul{display:none;}
		</style>
		<div style="padding-right:0; padding-left:0; min-height:auto;     margin-bottom: 0;    margin-top: 15px;" class="page-header">	 
			 <ul class="mmenu">		
			 <?
			 foreach($adress_arr as $k=>$v)
			 {
				if($v['show']==0){$opt = ' opacity: 0.4;  ';}else{$opt ='';}
				if($v['parking_building_id'] == $parking_building_id){$actclass=' mdefth '; }else{$actclass='';}
					if( ($v['show']==2  ) && $_SESSION['sh_login'] == 'admin' ) // Админу показывать все
					{
						?>
						<li style="padding:0; <?=$opt?>"><a href="ctrind.php?ctr=parking_floors&act=catalog&parking_building_id=<?=$v['parking_building_id']?>" style="color:#999;" class="mdef <?=$actclass?>"><?=$v['adress_disp']?></a> </li>	
						<? 
					}
					elseif(  $v['show']==3  &&  ( $_SESSION['agency_id'] == 92 || $_SESSION['sh_login'] == 'admin') ) // ТОЛЬКО ОТДЕЛУ ПРОДАЖ
					{
						?>
						<li style="padding:0; <?=$opt?>"><a href="ctrind.php?ctr=parking_floors&act=catalog&parking_building_id=<?=$v['parking_building_id']?>" class="mdef <?=$actclass?>"><?=$v['adress_disp']?></a> </li>	
						<? 
					}
					elseif( $v['show']==1  ) // ПОКАЗЫВАТЬ ВСЕМ!
					{
						?>
						<li style="padding:0; <?=$opt?>"><a href="ctrind.php?ctr=parking_floors&act=catalog&parking_building_id=<?=$v['parking_building_id']?>" class="mdef <?=$actclass?>"><?=$v['adress_disp']?></a> </li>	
						<? 
					}
			 }
			 ?>
			 </ul>
		</div>
		<hr style="margin-top: 12px; " class="nomobile">
		 <?
		 if(!$parking_building_id)
		 {
			 ?>
			<div class="objects">
			<div class="row">
				<?
				foreach($adress_arr as $k=>$v)
				{
					if( $v['show']==0 && $_SESSION['sh_login'] == 'admin' ){ $opt = 'opacity: 0.4; '; }else{ $opt =''; }
					$v['opt'] = $opt;
					
					if( $v['show']==2 && $_SESSION['sh_login'] == 'admin' ) // Админу показывать все
					{
						$this->tpl($v,'parking_floors','catalog_card');
					}
					elseif(  $v['show']==3  &&  ( $_SESSION['agency_id'] == 92 || $_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'em_nsv') ) // ТОЛЬКО ОТДЕЛУ ПРОДАЖ
					{
						$this->tpl($v,'parking_floors','catalog_card');
					}
					elseif( $v['show']==1  ) // ПОКАЗЫВАТЬ ВСЕМ!
					{
						$this->tpl($v,'parking_floors','catalog_card');
					}
				}
				?> 
 
			</div>
		</div>
		
		<?
		}
		else
		{ 	
			$this->act__display_bf($parking_building_id);
		}
	}


 
 
 
 
 
 
	// Ajax поиск улицы
	function act__sel_street()
	{
		global $mysql;
		$arr = $mysql ->get_arr(' SELECT   `parking_buildings`.`adress_disp` as `street` FROM `parking_buildings`  WHERE  parking_buildings.del="0" AND  parking_buildings.show="1" ');
		
		print '<option value=""> - </option>';
		foreach($arr as $k=>$v)
		{
			$buf = explode(',',$v['street']);
			$buf[0] = trim($buf[0]);
			
			if( $buf[0] && !in_array( $buf[0], $street_arr  ) ){ $street_arr[]= $buf[0]; }
		}
		
		foreach($street_arr as $k=>$v)
		{ 
			print '<option value="'.$v.'">'.$v.' </option>';
		}
			
	}
 
 
 
 
	 // JSOON ДЛЯ ФОРМИРОВАНИЯ КАРТЫ НА КЛИЕНТЕ!!!
	 
	 function act__mapitems($parking_building_id = '') 
	 {
		$parking_building_id = '';
		global $mysql;

		// SQL-запрос для получения данных о парковках
		$q = 'SELECT * FROM parking_buildings WHERE 1=1';
		if ($parking_building_id) {
			$q .= ' AND parking_building_id = "' . $mysql->escape_string($parking_building_id) . '" ';
		}

		$data = $mysql->get_arr($q);

		// Формируем массив с данными для карты
		$result = [];
		foreach ($data as $item) 
		{
			if ($item['lat'] && $item['lon'] && $item['adress']) {
				$result[] = [
					'id' => $item['parking_building_id'],
					'name' => $item['adress_disp'],
					'coordinates' => [$item['lat'], $item['lon']],
					'balloon_content' => '<a href="https://rent.d-at.ru/parking/?id=' . $item['parking_building_id'] . '" style="color:#56A4ED;">Перейти</a>'
				];
			}
		}

		// Возвращаем результат в формате JSON
		header('Content-Type: application/json');
		echo json_encode(['items' => $result]);
		exit;
	}

 
 
 
 
 
 
 	// Поиск по id обекта и ид дома добавить
	// Склонение смотреть обект обекты
	
	function act__map($parking_building_id='',$href_perfix='https://rent.d-at.ru/parking_one/?id=' ) 
	{
		//<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&amp;apikey=9998badd-d4f7-462f-b4a5-9c3aa51768c0" type="text/javascript"></script>
	
		global $mysql;
	   
		$q = 'SELECT * FROM  parking_buildings    WHERE 1=1 ';
		if($parking_building_id)
		{
			$q .= ' AND parking_buildings.parking_building_id = "'.$parking_building_id.'" ';
		}
		
		$data = $mysql->get_arr( $q ) ;
 
		  
		  /*
		    [parking_building_id] => 1
            [caption] => 
            [adress] => Новосибирск, Калининский район, микрорайон Родники, улица Свечникова, 4
            [adress_disp] => Свечникова, 4
            [lat] => 55.11238841356582
            [lon] => 82.94587539814762
            [street] => Свечникова
            [show] => 1
            [order] => 2
            [delivery_date] => 
            [complite] => 1
            [complite_text] => сдана
            [del] => 0
            [i] => 0
        )
		*/
		
		$i=0;
		foreach($data as $k=>$v)
		{
			if($v['lat'] && $v['lon'] && $v['adress'] )
			{
			$i++;
			 
			$name=$v['adress_disp'];
  
			$baloon_content = '<a href=\''.$href_perfix.$v['parking_building_id'].''.'\' style=\'color:#56A4ED;\'>Перейти</a>';
			 
			$items_text.='{center: ['.$v['lat'].', '.$v['lon'].'], name: "'.$name.'",htmlcontent: "'.$baloon_content.'"}';
			if($i<count($data)){$items_text.=',';}
			}
		}
		?> 
<script>
	// Группы объектов
	var groups = [
			{
				name: "Обьекты недвижимости",
				style: "islands#redIcon",
				items: [
					<?=$items_text?>
				]}    
		];
	 	
	ymaps.ready(init);
	function init() {

    // Создание экземпляра карты.
    var myMap = new ymaps.Map('map', {
            center: [55.11141538533996, 82.93576671412082],
            zoom: 16 ,
            minZoom: 16,
            maxZoom: 18,
			controls: ['geolocationControl','routeButtonControl','zoomControl']
        } ),
        // Контейнер для меню.
        menu = $('<ul class="menu"/>');
        
    for (var i = 0, l = groups.length; i < l; i++) {
        createMenuGroup(groups[i]);
    }

    function createMenuGroup (group) {
        // Пункт меню.
        var menuItem = $('<li><a href="#">' + group.name + '</a></li>'),
        // Коллекция для геообъектов группы.
            collection = new ymaps.GeoObjectCollection(null, { preset: group.style }),
        // Контейнер для подменю.
            submenu = $('<ul class="submenu"/>');

        // Добавляем коллекцию на карту.
        myMap.geoObjects.add(collection);
        // Добавляем подменю.
        menuItem
            .append(submenu)
            // Добавляем пункт в меню.
            .appendTo(menu)
            // По клику удаляем/добавляем коллекцию на карту и скрываем/отображаем подменю.
            .find('a')
            .bind('click', function () {
                if (collection.getParent()) {
                    myMap.geoObjects.remove(collection);
                   // submenu.hide();
                } else {
                    myMap.geoObjects.add(collection);
                    //submenu.show();
                }
            });
        for (var j = 0, m = group.items.length; j < m; j++) {
            createSubMenu(group.items[j], collection, submenu);
        }
    }

    function createSubMenu (item, collection, submenu) {
        // Пункт подменю.
        var submenuItem = $('<li><a href="#">' + item.name + '</a></li>'),
        // Создаем метку.
            placemark = new ymaps.Placemark(item.center, { balloonContentFooter: item.name,balloonContentBody: item.htmlcontent});
			
			// Произвольный HTML балуна
				placemark2 = new ymaps.Placemark(myMap.getCenter(), {
				// Зададим содержимое заголовка балуна.
				balloonContentHeader: '<a href = "#">Рога и копыта</a><br>' +
					'<span class="description">Сеть кинотеатров</span>',
				// Зададим содержимое основной части балуна.
				balloonContentBody: '<img src="img/cinema.jpg" height="150" width="200"> <br/> ' +
					'<a href="tel:+7-123-456-78-90">+7 (123) 456-78-90</a><br/>' +
					'<b>Ближайшие сеансы</b> <br/> Сеансов нет.',
				// Зададим содержимое нижней части балуна.
				balloonContentFooter: 'Информация предоставлена:<br/>OOO "Рога и копыта"',
				// Зададим содержимое всплывающей подсказки.
				hintContent: 'Рога и копыта'
			});
			
			

        // Добавляем метку в коллекцию.
        collection.add(placemark);
        // Добавляем пункт в подменю.
        submenuItem
            .appendTo(submenu)
            // При клике по пункту подменю открываем/закрываем баллун у метки.
            .find('a')
            .bind('click', function () {
                if (!placemark.balloon.isOpen()) {
                    placemark.balloon.open();
                } else {
                    placemark.balloon.close();
                }
                return false;
            });
    }

    // Добавляем меню в тэг BODY.
    menu.appendTo($('#map_menu'));
 
	myMap.setBounds(myMap.geoObjects.getBounds(), {checkZoomRange:true}).then(function(){ if(myMap.getZoom() > 17) myMap.setZoom(17);});
	//myMap.setZoom(17);
	
	
	//на мобильных устройствах... (проверяем по userAgent браузера)
	if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)){
		//... отключаем перетаскивание карты
		myMap.behaviors.disable('drag');
	}
	else
	{
		// myMap.behaviors.disable('scrollZoom'); // Запрет зума скролом
	}
	 

		
  myMap.geoObjects.events.add('balloonopen', function (e) {
            // Ссылку на объект, вызвавший событие,

            // можно получить из поля 'target'.

           // e.get('target').options.set('preset', 'islands#greenIcon');
		 
		   
		   
		   
		     $('.iframerent').magnificPopup({type:'iframe',
 // removalDelay: 100,
 // fixedContentPos: true, 
  //disableOn:1,
  mainClass: 'mfp-fade',
    mainClass: 'mfp-no-margins mfp-with-zoom', // class to remove default margin from left and right side
      removalDelay: 300,
   tLoading: 'Загрузка #%curr%...',
    callbacks: {
    open: function() {
      // Will fire when this exact popup is opened
      // this - is Magnific Popup object
    },
    close: function() {
        // parent.location.reload(true);  
    },
	open: function() {
          location.href = location.href.split('#')[0] + "#pop";
        } 
	 
    // e.t.c.
  }
   
  });
  
  
  

        })



  
  
  
}
</script>
    <style type="text/css">
        #map {
            width: 100%;
            height: 500px;
        }
         /* Оформление меню (начало)*/
        .menu {
            list-style: none;
            padding: 5px;

            margin: 0;
        }
        .submenu {
            list-style: none;

            margin: 0 0 0 20px;
            padding: 0;
        }
        .submenu li {
            font-size: 90%;
        }
            /* Оформление меню (конец)*/
    </style>
	
	
	<div id="map"></div>
	<div id="map_menu" style="display:none;"></div>
<?		
		
	}
	
	
	
	
	
	
	
	
	function act__display_map()
	{	
		 
		$this->act__map();
		 
	}
	
	
 
	
	
	
	function act__display_v3()
	{	
	}
	
 
  // Выввод на публичном сайта ajax
	function act__display_v2()
	{		
		global $mysql;
		global $t;
		$t['h1'] = 'Парковки';
		 
		$parking_building_id = (int) $_GET['street_pp'];
		 
		$adress_arr = $mysql ->get_arr('SELECT 
		parking_buildings.*, GROUP_CONCAT(parking_floors.plan_file SEPARATOR "|") AS plan_files,
		count(DISTINCT  parking_floors.parking_floor_id) as  floor_c,
		count(DISTINCT  parking_spaces.parking_space_id) as  space_c
		
		FROM parking_buildings  
		
		
		LEFT JOIN parking_floors ON parking_floors.parking_building_id = parking_buildings.parking_building_id
		LEFT JOIN parking_spaces ON parking_spaces.parking_floor_id = parking_floors.parking_floor_id
		
		WHERE parking_buildings.del="0" 
		
		
		GROUP BY parking_buildings.parking_building_id
		ORDER by parking_buildings.order,adress_disp
		');
		 
		 
		 
	 
		### ВЫВОДИМ ОДНУ ПАРКОВКУ ВСЕ ЭТАЖИ 
		if($_REQUEST['street_pp'])
		{
			$parking_building_id = (int) $_REQUEST['street_pp'];
			$_GET['parking_building_id'] = (int) $_REQUEST['street_pp'];
			
			 
			$this->act__public_wiget_display($parking_building_id ) ;
			
			return;
		}
		
		# Выводим все парковки 
		?>
		<div id="maprentobjects" class="rent_spoller" style="margin-top:20px;"> 
		 <?
		  if($adress_arr)
		  {
			$this->act__map();
		  }
		  ?>
		</div>

		<br/>
		<?
		
		?><div id="rentobjects"><?
		foreach($adress_arr as $k=>$v)
		{
			if( $v['show']==1  ) // ПОКАЗЫВАТЬ ВСЕМ!
			{
				$this->tpl($v,'parking_floors','cat_one_item_v2');
			}
		}
		?></div><?
 
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
 
 
 
 
	#######################
	####################### РЕДАКТОР  ПЛАНА ЭТАЖА
	#######################
 
	
	// ОТОБРАЖЕНИЕ МЕСТА  - В РЕДАКТОРЕ ПОЭТАЖНОГО ПЛАНА 
	function printspace_editor($data='')
	{
		$price =  number_format($data['price'], 0, '.', ' ');
		
		if(!$data['status']){$data['status']=2; }
		if($data['status']==2){$status_class='car_g';}
		if($data['status']==4){$status_class='car_y';}
		if($data['status']==3){$status_class='car_r';}
		if($data['status']==6){$status_class='car_b';}
		if($data['status']==5){$status_class='car_f';}
			 
		?>
		<div data-for="place__<?=$data['parking_space_id']?>"  data-id="<?=$data['parking_space_id']?>" class="car <?=$status_class?>"  data-deg="<?=$data['rotate']?>" style="left:<?=$data['x']?>px; top:<?=$data['y']?>px; transform:rotate(<?=$data['rotate']?>rad)"> 
			<span class="pk_num"><?=$data['num']?></span> 
			<span class="pk_price"><?=$price?></span> 
			<span class="pk_area"><?=$data['area']?></span> 
		</div>
		<?
		
		// НАследуем пост
		if(in_array($data['parking_space_id'],$_POST['places']))
		{
			$ch = ' checked="checked" ';
		}
		?>
		<input style="display:none;" <?=$ch?> class="place_ch" type="checkbox" name="places[]" id="place__<?=$data['parking_space_id']?>" value="<?=$data['parking_space_id']?>" />
		<?
	}
	
	
	// Быстрое добавление МЕСТ
	function act__fastadd_spaces()
	{
		$parking_floor_id = (int) $_GET['parking_floor_id'];
		if(!$parking_floor_id ){ print 'Не указан id этажа'; return;}
		
		global $filed;
		global $mysql;
 
	 ?>
	 <form method="POST">
		<?
		
		$start = (int)  $_POST['start'];
		$finish = (int) $_POST['finish'];
		
		if(!$start){$start=1;}
		if(!$finish){$finish=2;}
		
		//if(!$_POST['start']){$_POST['start'] = 1;}
		$filed->text('start','Стартовый номер',$start);
		$filed->text('finish','Конечный номер',$finish);
		$filed->text('nameperf','Перфикс названия (буква)',$_POST['nameperf']);
		$filed->text('size','Размер',$_POST['size']);
		$filed->text('area','Площадь',$_POST['area']);
		$filed->text('price','Цена',$_POST['price']);
		?>
		 <br/><br/>
		<?
		if($_POST)
		{
			 if(!$start || !$finish || $start > $finish )
			 {
				print 'не корректно указанны стартовый и финишный номера';
				return;
			 }
			if(!$_POST['confirm'])
			{
				for($i=$start; $i<=$finish; $i++)
				{
					print $i.' - ';
					print 'Номер:'.$_POST['nameperf'].$i.' /  ';
					print 'Размер:'.$_POST['size'].' /  ';
					print 'Площадь:'.$_POST['area'].' /  ';
					print 'Цена:'.$_POST['price'].' /  ';
					print '<br/>';
				}
				print '<br><br>';
				$filed->checkbox('confirm','Подтвердить',$_POST['confirm']);
			}
			else
			{
				$arr = $mysql->get_for_key('parking_floors','parking_floor_id',$parking_floor_id,1);
			 
				if(!$arr['parking_building_id'])
				{
					print 'Не удалось определить задение';
					return;
				}
				for($i=$start; $i<=$finish; $i++)
				{
					print $i.' - ';
					print 'Номер:'.$_POST['nameperf'].$i.' /  ';
					print 'Размер:'.$_POST['size'].' /  ';
					print 'Площадь:'.$_POST['area'].' /  ';
					print 'Цена:'.$_POST['price'].' /  ';
					print '<br/>';
					
					 $data = array();
					 $data['parking_building_id'] = $arr['parking_building_id'];
					 $data['parking_floor_id'] = $arr['parking_floor_id'];
					 if($_POST['size']){ $data['size'] = $_POST['size']; }
					 if($_POST['area']){ $data['area'] = $_POST['area']; }
					 if($_POST['price']){ $data['price'] = $_POST['price']; }
					 $data['num'] = $_POST['nameperf'].$i;
					 // print_r($data); print '<br/>';
					 $mysql->insert('parking_spaces',$data);
				}
				print '<b>Элементы добавлены, <br/>чтобы перейти к размещению закройте окно</b>';
				//$mysql->insert()
				// Добавление 
			}
		}
			$filed->submit();
			?>
			</form>
			<?
	}







	// РЕДАКТИРОВАНИЕ РАЗМЕТКИ ЭТАЖА (ДВИГАТЬ МЕСТА)
	function act__edit()
	{ 
		global $filed;
		global $mysql;
		global $r;
		global $t;
		$t['h1'] = 'План этажа';
	   
		# Данные редактирования
		$id = $_GET['id'];
		if($id)
		{
			$filtr = array();
			$filtr['id'] = $id;
			$data2 = $mysql->get_arr($this->get_base_sql($filtr));
			$data = $mysql->get_arr($this->get_base_sql($filtr));
			$data=$data[0];
			//print '<h2>Редактирование объекта </h2>'; 
		}
		else
		{
			 // print '<h2>Добавление объекта</h2>';
		}
		
		if($_POST) ############# Обработка данных пост
		{
			 //print '<pre>';
			 //print_R($_POST);
			 //print '</pre>';
			
			if($_POST['select_mode'] && $_POST['places'] && is_array($_POST['places']) )
			{
				foreach($_POST['places'] as $kk=>$vv)
				{
					$mass_date =array();
					if( $_POST['group_edit']['price'] ){ $_POST['group_edit']['price'] = (int) trim($_POST['group_edit']['price']); }
					if( $_POST['group_edit']['size'] ){ $_POST['group_edit']['size'] =  trim($_POST['group_edit']['size']); }
					if( $_POST['group_edit']['area'] ){ $_POST['group_edit']['area'] =  (float) trim($_POST['group_edit']['area']); }
		  
		  
					if( $_POST['group_edit']['price'] ){ $mass_date['price'] =  $_POST['group_edit']['price']; }
					if( $_POST['group_edit']['size'] ){ $mass_date['size'] =  $_POST['group_edit']['size']; }
					if( $_POST['group_edit']['area'] ){ $mass_date['area'] =  $_POST['group_edit']['area']; }
					
					#if( $_POST['group_edit']['y'] ){ $mass_date['y'] =  $_POST['group_edit']['y']; }
					#if( $_POST['group_edit']['x'] ){ $mass_date['x'] =  $_POST['group_edit']['x']; }
					#if( $_POST['group_edit']['rotate'] || $_POST['group_edit']['rotate'] === "0" ){ $mass_date['rotate'] =  $_POST['group_edit']['rotate']; }
					
					if( $_POST['group_edit']['status']  ){ $mass_date['status'] =  $_POST['group_edit']['status']; }
					
					if( $mass_date )
					{
						$mysql->update_for_key('parking_spaces','parking_space_id',$vv,$mass_date );
					}
				}
			}
			 
			if(!$id){$id=$_GET['id'];}
			$data = array();
			$data['floor'] = $_POST['floor'];
			$data['plan_file'] = $_POST['plan_file'];
			$data['plan_width'] = $_POST['plan_width'];
			$data['parking_building_id'] = $_POST['parking_building_id'];
		   
			if( $id ) // Редактирваоние существующей записи
			{
				//	print 'Изменения сохранены!';
				$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
			}
			else // Добавление новой записи
			{
				//	print 'Запись добавлена!';
				$mysql -> insert( $this->table , $data );
			}
			// $this->act__index();
		}
		
		
		if( !$_POST || 1==1 ) ############# ФОРМА РЕДАКТИРОВАНИЯ
		{
			 $this->tpl($data,'parking_floors','admin_editform_head'); // Шапка формы (стили скрипты)
		?>
		<?=$this->formpanel($r->acturl($this->ctr,'index'));?>
		
		 <form action="<?=$r->acturl($this->ctr,'edit');?>&id=<?=$_GET['id']?>" method="POST" id="editform"  >
 
		 <div class="row">
				<div class="col-md-12">
				<?
					$b = $mysql->get_arr('SELECT * FROM parking_buildings WHERE del="0" ');
					foreach($b as $k=>$v)
					{
						$bildings_arr[$v['parking_building_id']] = $v['adress_disp'];
					}
					$filed->select('parking_building_id','Здание',$bildings_arr,$data['parking_building_id']);
					$filed->text('floor','Этаж',$data['floor']); print '<br/>';
					$filed->text('plan_file','Ссылка на план этажа',$data['plan_file']); print '<br/>';
					$filed->text('plan_width','Ширина изображения плана (PX)',$data['plan_width']); print '<br/>';
					
					$width = $data2[0]['plan_width'];
					if($data2[0]['parking_floor_id'] == '10' || $data2[0]['parking_floor_id'] == '11')
					{
						$bp = '  background-position: 0 30px; ';
					}
				?>	
				
				
	
		
		
		<br/>
		<?
		if($id )  
		{
		?>
		<div style="padding:5px; background-color:#EEE;">
		<table width="100%" style="border-bottom:2px solid #3d535f;">
		<tr>
		<td align="left">
			<a href="iframe_router.php?ctr=parking_floors&act=fastadd_spaces&parking_floor_id=<?=$_GET['id']?>" class="iframe_r deplan_zoom"  >Быстрое добавление объектов</a>
		</td>
		<td align="center" style="font-size:10px;">Двигать карту можно правой кнопкой мыши<br> 
			<br/>
			<label class="deplan_zoom" for="disable_drag" ><input type="checkbox" id="disable_drag" name="disable_drag" <? if($_POST['disable_drag']){print 'checked="checked" ';}?>> Отключить перетаскивание </label>
			<label class="deplan_zoom"  for="disable_rotate" ><input type="checkbox" id="disable_rotate" name="disable_rotate" <? if($_POST['disable_rotate']){print 'checked="checked" ';}?>> Отключить вращение </label>
			<label class="deplan_zoom"  for="select_mode"><input type="checkbox" id="select_mode" name="select_mode" <? if($_POST['select_mode']){print 'checked="checked" ';}?>/> Режим выделения (массовые действия)</label>
			<label  class="deplan_zoom" for="border_mode" ><input type="checkbox" id="border_mode"> Границы элементов (упрощает выравнивание)</label>
		</td>
		<td align="right"  >
			<span class="deplan_zoom deplan_text"> zoom: 1</span>
			<span class="deplan_zoom deplan_minus">-</span>
			<span class="deplan_zoom deplan_0">0</span>
			<span class="deplan_zoom deplan_plus">+</span>
		</td>
		</tr>
		</table>
		</div>
			 <div class="car_massform" style="display:none;     background-color: #EEE; padding:3px;">
 
 
			<span id="select_all_places" class="deplan_zoom">Выделить все</span>		
			<span id="unselect_all_places" class="deplan_zoom">Снять выделение</span>
			<span id="clean_form_places" class="deplan_zoom">Очистить форму</span><br/>
 
		<div class="row">
			<div class="col-md-11">
			<?
				?><div style="display: inline-block;   width:120px; padding:5px;"><? $filed->text_num('group_edit[price]','Цена','',0,100000000,'1','width:100%;'); ?></div><?  
				?><div style="display: inline-block;   width:120px; padding:5px;"><? $filed->text_float('group_edit[area]','Площадь','',0,15000,'0.01','width:100%;'); ?></div><?  
				?><div style="display: inline-block;   width:120px; padding:5px;"><? $filed->text('group_edit[size]','Размер','',' style="width:100%;" '); ?></div><? 
				
				  $broni_status=array();
				  // $broni_status[0] = 'Не задан';
				  $broni_status[0] = ' ---- ';
				  $broni_status[2] = 'Свободна';
				  $broni_status[4] = 'Забронирована';
				  $broni_status[3] = 'Продано';	
				  $broni_status[5] = 'Забронировано застройщиком';
				  $broni_status[6] = 'Подрядчика';	
	 	  
				?><div style="display: inline-block;   width:150px; padding:5px;"><? $filed->select('group_edit[status]','Статус', $broni_status,'',' width:100%; text-transform:none; height:auto;');  ?></div><? 
 
			 
				?>
				<br/>
			<span style="font-size:10px; font-weight:bold">Свойства объекта при сохранении будут применены к выделенным</span>
			</div>
			<div class="col-md-1" style="text-align:right;">
			<?
			/*
				?><div style="display: inline-block; width:120px; padding:5px;"><?  $filed->text_num('group_edit[x]','Left (px)','','',5000,'1','width:100%'); ?></div><?  
				?><div style="display: inline-block; width:120px;; padding:5px;"><? $filed->text_num('group_edit[y]','Top (px)','','',5000,'1','width:100%'); ?></div><?  
				?><div style="display: inline-block; width:120px; padding:5px;"><? $filed->text_float('group_edit[rotate]','Угол (Rad)','',-2,2,'0.1','width:100%'); ?></div><? 
			<br/>
			<span style="font-size:10px; font-weight:bold">Координаты объекта на плане</span>
			<?
			*/
			?>
			</div>
		</div>
		 
		</div>
		<div id="save_text" style="font-size:10px; text-align:right; width:100%;"><br/></div>
		
		<div style="width:100%; overflow:scroll;" class="de_planf">
				<div class="de_plan" style="width:<?=$width?>px; min-height: 500px; <?=$bp?> background-image:url('<?=$data['plan_file']?>'); background-size: contain; background-repeat: no-repeat; position:relative;">
				<br/>
				<br/>
					<img src="<?=$data['plan_file']?>" style="visibility:hidden;   width:<?=$width?>px;"  />
					<?
						// получаем и выводим места
						$spaces_data_arr = $mysql->get_arr('SELECT * FROM parking_spaces WHERE parking_floor_id = "'.$id.'" AND del="0"');
						foreach($spaces_data_arr as $k=>$v)
						{
							$this->printspace_editor( $v );
						}
					?> 
					<div id="tmouse"></div>
					<div style="width:100%; height:100px; background-color:#EEE; text-align:center; padding:40px; margin-top:100px;" class="droppable">Удаление</div>
				</div> 
		</div>		 
			</div><!-- col !-->
				 
		</div><!-- row!-->
	 	
		
		</form>
		<?
		} // Если редактирование
		else
		{
			print 'Для редактирования плана, необходимо сохранить объект';
		}
		}
	}
	
	function act__save_drag()
	{
		global $mysql;
		
		$id = (int) $_POST['id'];
		$data = array();
		$data['x'] = $_POST['x'];
		$data['y'] = $_POST['y'];

		$mysql->update_for_key('parking_spaces','parking_space_id',$id,$data,0);
		print '  Сохранено  ';
		// print_r($_POST); 
	}
	
	function act__save_rotate()
	{
		global $mysql;
		
		$id = (int) $_POST['id'];
		$data = array();
		
		if(!$_POST['degress'] || $_POST['degress']=='NaN'){$_POST['degress']=0;}
		$data['rotate'] = $_POST['degress'];
 
		$mysql->update_for_key('parking_spaces','parking_space_id',$id,$data,0);
		print '  Сохранено  ';
		// print_r($_POST);
	}
	
	
	function act__deltedrag()
	{
		global $mysql;
		
		$id = (int) $_POST['id'];
		$data = array();
		$data['del'] = 1;
		$mysql->update_for_key('parking_spaces','parking_space_id',$id,$data,0);
		print '  Сохранено -   ';
		// print_r($_POST);
	}

	
	
	####################### 
	####################### 
	####################### 
	
	// Метод содержимого столбца
	function display_table__edit($row)
	{

		return $link = '
		<a href="?ctr='.$this->ctr.'&act=edit&id='.$row[$this->key_filed].'" class="table-edit"> </a> 
		&nbsp;
		&nbsp;
		<a href="?ctr='.$this->ctr.'&act=del&id='.$row[$this->key_filed].'" style="color:red;  font-size: 21px;"> X </a>
		';
	}
	 
	
	
	# Удаление записи (пометка)
	function act__del()
	{
		global $mysql;
		$id = $_GET['id'];
		if($id)
		{			
			$data = array();
			$data['del'] = 1;
			$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
		}
		$this->act__index();
	}
	
	
	
	
	function ajcrud_filtr()
	{
		?>
		<div class="filter-item"> 
			<? $this->filtr_select('Здание','parking_building_id','adress_disp');	?>
			</div>	

			 
			
			 
			 
		<?
	}
	
	
	
	
	function act__index()
	{
		global $t;
		$t['h1'] = 'Поэтажные планы';
		$this->display_ajax_crud();
	}
	
	
	  
	
	 
	 
	 
	 
	
	
	// jsoon  поиск улицы
	function act__jsoon_street()
	{
		global $mysql;

		// Выполняем SQL-запрос для получения списка улиц
		$arr = $mysql->get_arr('
			SELECT *
			FROM `parking_buildings`  
			WHERE `parking_buildings`.`show` = "1" AND `parking_buildings`.`del` = "0" 
			 
		');

		// Подготавливаем массив данных для отправки
				$result[] = [
					'id' => '', // Используем название улицы как ID
					'name' => 'Парковки', // Название улицы
					'count' => $item['c'] // Количество объектов на этой улице
				];
		foreach ($arr as $item) {
			if (!empty($item['adress_disp'])) {
				$result[] = [
					'id' => $item['parking_building_id'], // Используем название улицы как ID
					'name' => trim($item['adress_disp']), // Название улицы 
				];
			}
		}

		// Отправляем данные в формате JSON
		header('Content-Type: application/json');
		echo json_encode($result);
	}
	
	
	
	
	
}