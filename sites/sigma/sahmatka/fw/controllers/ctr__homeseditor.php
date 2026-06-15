<?
$GLOBALS['t']['title']='Редактор домов';

class ctr__homeseditor extends ctr__
{ 

 
	var $table = 'homes'; //Главная таблица
	var $key_filed = 'homes_id'; // Ключевое поле главной таблицы
	var $ctr = 'homeseditor';
	var $title = 'Объекты';
	var $title_act1 = 'Объект';
	var $title_act2 = 'Объекта';
	
	function __construct()
	{
		
		$this->show_array=array();
		$this->show_array[1] = 'Всем';
		$this->show_array[2] = 'Админам';
		$this->show_array[3] = 'Админам и ОП';
		$this->show_array[0] = 'НЕТ';
						
						
						
		$this->mysql=$GLOBALS['mysql'];
	 
		$data=$this->getfiltr(); // Получаем данные для вывода
		$this->data=$data; // Сохраняем данные
		 	
		$data_nofiltr=$this->getfiltr([1]); // Данные без фильров для селектов?! очень ресуроемко ! по сути все брони перебирает циклом , но с другой стороны у нас все записи выводятся и ничего 
		$this->data_nofiltr=$data_nofiltr; // Сохраняем данные
	  
		// Выводимые столбцы
		$titles = array();
		$titles['home_id'] = 'ID';
		$titles['show'] = 'Отображение';
		$titles['title'] = 'Название';
		$titles['kvartal'] = 'Комплекс'; 
		$titles['complite'] = 'Статус';
	//	$titles['description'] = 'Описание'; 
		$titles['order'] = 'Сортировка'; 
	    $titles['show_keys'] = 'Выдача ключей'; 
		
		 $titles['domclick'] = 'Домклик'; 
		
		
		
	    $titles['edit'] = 'Редактирование'; 
		$this->ajcrud_table_titles=$titles;
		
		// Не переносить по словам
		$nowrap=array();
		$nowrap['edit'] = 1;
		$this->table_nowrap=$nowrap;
		
		// Сортировать по столбцам
		$order=array();
		$order[$this->key_filed]=$this->key_filed;
		//$order['adress']='adress';
		//$order['show']='`show`';
		 
		$this->ajcrud_table_order=$order; 
		$this->aj_crud_addbutton=1;		
	}
	 
	
	// БАзовый запрос 
	function get_base_sql( )
	{
		foreach($_GET as $k=>$v){  $filtr_data[$k]=$v;	}
		$q = 'SELECT `homes`.* FROM `'.$this->table.'` ';
		
		$q.='  LEFT JOIN `homes_kvartal` ON `homes_kvartal`.`homes_kvartal_id` = `homes`.`kvartal` ';
		//$q.='  LEFT JOIN `apartaments` as ap_sale ON `apartaments`.`homes_id` = `homes`.`homes_id` AND  ';
		
		
		
		$q.='   WHERE 1=1 ';
		if(!$filtr_data['showhide']){$q .= ' AND (`homes`.`show`="1" or `homes`.`show`="2" or `homes`.`show`="3") '; }
		 
		if($filtr_data['show_keys']){$q .= 'AND show_keys = "'.$filtr_data['show_keys'].'" '; }
		return $q.'      ';
	}
	
	
	
	// Метод содержимого столбца
	function display_table__edit($row)
	{
		global $t;
		return $link = '
		<a href="?ctr='.$this->ctr.'&act=edit&id='.$row[$this->key_filed].'" class="iframe_rajax table-edit"> </a> 
		 
		';
	}
	
	// Метод содержимого столбца
	function display_table__kvartal($row)
	{
		global $kvartal;
		return $kvartal[$row['kvartal']];
	}
	
	
	
	
	// Метод содержимого столбца
	function display_table__show_keys($row)
	{
		if($row['show_keys']){return '<b>ДА</b>';}
		else return 'НЕТ';
		 
	}
	
	
	function display_table__complite($row)
	{
		if($row['complite']){return '<b>сдан</b>';}
		else{return 'строится';}
	}
	function display_table__show($row)
	{
		 return $this->show_array[$row['show']]; 
	}
	// Метод содержимого столбца
	function display_table__domclick($row)
	{
		$dk = true;
		$dk_t = array();
		$dk_t2 = '';
	 $dk_t[] = $row['show'];
		if( !$row['title'] ){ $dk = false;  $dk_t[] = 'title';}
		if( !$row['floor'] ){ $dk = false;  $dk_t[] = 'floor';}
		if( $row['show']!="1" ){ $dk = false;  $dk_t[] = 'show';}
		if( !$row['kvartal'] ){ $dk = false;  $dk_t[] = 'kvartal';}
		if( !$row['wallmaterial'] ){ $dk = false;  $dk_t[] = 'wallmaterial';}	
		if( !$row['complex_domclick'] ){ $dk = false;  $dk_t[] = 'complex_domclick';}
		if( !$row['corpus_code_domclick'] ){ $dk = false;  $dk_t[] = 'corpus_code_domclick';}
		if( !$row['built_year'] ){ $dk = false;  $dk_t[] = 'built_year';}
		if( !$row['ready_quarter'] ){ $dk = false;  $dk_t[] = 'ready_quarter';}
		if( !$row['renovation'] ){ $dk = false;  $dk_t[] = 'renovation';}
		if( !$row['lat'] ){ $dk = false;  $dk_t[] = 'lat';}
		if( !$row['lon'] ){ $dk = false;  $dk_t[] = 'lon';}
		if( !$row['adress'] ){ $dk = false;  $dk_t[] = 'adress';}
		
		if($dk)
		{
			return '<b>ЕСТЬ</b>';
		}
		else
		{
			
			foreach($dk_t as $k=>$v)
			{
				$dk_t2.=$v.'<br/>';
			}
			return 'НЕТ ДАННЫХ<br>'.$dk_t2;
		}
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
		//	$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
		}
		$this->act__index();
	}
	
	
	
	
	
	function formpanel($backlink='')
	{
		
		if(!$backlink){$backlink = $this->backlink;}
		?>
		<div class="row">
			<div class="col-md-3"  style="text-align:left;"><a href="<?=$backlink?>" class="forminformpanel_link">Назад</a> </div>
			<div class="col-md-6"  style="text-align:center;" class="forminform"><?=$this->forminform?> </div>
			<div class="col-md-3"  style="text-align:right;"><a href="#" onclick="document.getElementById('editform').submit(); return false;" class="forminformpanel_link" style="text-align:right;">Сохранить</a></div>
		 </div>
		<br/>
		<hr/>
		<?
	}
	
	
	
	
	function act__edit()
	{
		global $kvartal;
	
		global $filed;
		global $mysql;
		global $r;
		global $t;
	 	
		$t['h1'] = 'Редактирование объекта';
		# Данные редактирования
		$id = $_GET['id'];
		if($id)
		{
			$data = $mysql->get_for_key($this->table,$this->key_filed,$_GET['id']);
			
			if($data['delivery_date'])
			{
				/// $data['delivery_date'] = date('d.m.Y' , strtotime( $data['delivery_date'] ) );
			}
			if(!$data['img'] ||1==1){ $data['img'] = 'http://xdemo.m2profi.pro/render/'.$data['home_id'].'.jpg'; }
			
			//print '<h2>Редактирование '.$this->title_act2.'</h2>';
			
			//print '<pre>';
			//print_r($data);
			//print '</pre>';
		}
		else
		{
			//print '<h2>Добавление '.$this->title_act2.'</h2>';
		}
		
 
		if(!$_POST) ############# ФОРМА
		{
			
		?>		
		<style>
		.input_edit {width:100%; max-width:100%;}
		</style>		
			<form action="<?=$r->acturl( $this->ctr , 'edit' );?>&id=<?=$id?>" method="POST" id="editform"  >
			<br/><br/>
			<?=$this->formpanel($r->acturl($this->ctr,'index'));?>
			
			<div class="row">
				<div class="col-md-6">
				 <?=$filed->text('home_id','home_id',$data['home_id']);?><br/>
				 <?=$filed->text('title','Краткий заголовок',$data['title']);?><br/>
				 <?=$filed->text('long_title','Полный заголовок',$data['long_title']);?><br/>
				 <?=$filed->select('wallmaterial','Материал / технология',array(''=>'не указано','панельный'=>'панельный','монолитный'=>'монолитный','кирпичный'=>'кирпичный','кирпично-монолитный'=>'кирпично-монолитный'),$data['wallmaterial']);?><br/>
				 <?=$filed->text('floor','Этажей',$data['floor']);?><br/>
				 <?=$filed->select('kvartal','Комплекс',$mysql->get_select_data(' SELECT * FROM `homes_kvartal` ','homes_kvartal_id','title'),$data['kvartal']);?><br/>
				<?=$filed->text('img','Изображение',$data['img']);?><br/>
				</div>
				 
				
				<div class="col-md-6">
				<?=$filed->text('order','Порядок сортировки',$data['order']);?><br/>
					<?
						
					?>
					<?=$filed->select('show','Показывать',$this->show_array,$data['show']);?><br/>
					
					<?
					$filed->date('delivery_date','Дата сдачи',$data['delivery_date']);
					?><br/>
					<?=$filed->text('complite_text','Состояние готовности',$data['complite_text']);?><br/>
					<?=$filed->checkbox('complite','Дом сдан',$data['complite']);?><br/>
					<?=$filed->textarea('description','Описание',$data['description']);?><br/> 
				</div>
			</div>
			
			<h2>Объект на карте</h2>
			 <?
				// Карта (координаты и адресс)
				$data_map = array();
				$data_map['lat'] = $data['lat'];
				$data_map['lon'] = $data['lon'];
				$data_map['adress'] = $data['adress'];
				$filed->map('map',$data_map);  
			?>

			<hr/>
				<h2>Выдача ключей</h2>
			
				<?=$filed->checkbox('show_keys','Выдача ключей',$data['show_keys']);?><br/>
				<?=$filed->text('keys_message','Сообщение при успешной записи на выдачу ключей',$data['keys_message']);?><br/>
				<?=$filed->text('keys_adress','Адрес выдачи ключей',$data['keys_adress']);?><br/>
			<?
				// Карта (координаты и адресс)
				$data_map = array();
				$data_map['lat'] = $data['map_mapkeys_lat'];
				$data_map['lon'] = $data['map_mapkeys_lon'];
				$data_map['adress'] = $data['map_mapkeys_adress'];
				$filed->map('map_mapkeys',$data_map);  
			?>	
			<hr/>
				<h2>Домклик</h2>
				<?=$filed->text('complex_domclick','complex (Домлик)',$data['complex_domclick']);?><br/>
				<?=$filed->text('corpus_code_domclick','corpus_code (Домлик)',$data['corpus_code_domclick']);?><br/>
				<?=$filed->text('built_year','Год сдачи (Домлик)',$data['built_year']);?><br/>
				  
				 
				 
				
				 
				 
				<?=$filed->select('ready_quarter','квартал сдачи (Домлик)',array('1'=>'1','2'=>'2','3'=>'3','4'=>'4'),$data['ready_quarter']);?><br/>
  
				<?=$filed->select('renovation','Отделка',array(''=>'не указано','чистовая'=>'чистовая','черновая'=>'черновая','нет'=>'нет','предчистовая'=>'предчистовая'),$data['renovation']);?><br/>
				<br/><br/>
				<a href="https://domclick.ru/validation/validator" target="_blank">Валидатор домклик</a>  
					<a href="https://domclick.ru/validation/requirements" target="_blank">Поля фида</a> 
					<a href="https://domclick.ru/complexes/123__<?=$data['complex_domclick']?>" target="_blank">Страница комплекса (ЖК)</a> 
					
					<br/><br/>
				
				
				
				
				
				<b>Фид домклик:</b>  https://xdemo.m2profi.pro/sahmatka/domclick-<?=$data['home_id']?>.xml
				
 
 
				
				<h2>Яндекс недвижимость</h2>
				
				  <?=$filed->text('yandex-building-id','yandex-building-id',$data['yandex-building-id']);?><br/>
				
				  <?=$filed->text('yandex-house-id','yandex-house-id',$data['yandex-house-id']);?><br/>
				 https://xdemo.m2profi.pro/sahmatka/yandex_feedx.php
				 <h2>Авито</h2>
				  
				 
				     <?=$filed->text('avito_id','avito_id',$data['avito_id']);?><br/>
 				 https://xdemo.m2profi.pro/sahmatka/avito_feedx.php 
				 https://autoload.avito.ru/format/xmlcheck/ 
			</form>
			
		<?
		}
 
		if($_POST) ############# Обработка данных пост
		{
	 
			//print '<pre>';
			//print_R($_POST);
			//print '</pre>';

			$data = array();
			 
			$data['home_id'] = $this->data_value( $_POST['home_id'] );
			$data['complex_domclick'] =   $this->data_value($_POST['complex_domclick'] ); 
			$data['corpus_code_domclick'] = $this->data_value($_POST['corpus_code_domclick'] ); 
			$data['yandex-house-id'] = $this->data_value($_POST['yandex-house-id'] ); 
			$data['yandex-building-id'] = $this->data_value($_POST['yandex-building-id'] ); 
			
			 
				 
				 
			$data['title'] = $this->data_value($_POST['title'] );
			$data['long_title'] = $this->data_value($_POST['long_title'] );
			$data['show'] = $this->data_value($_POST['show'] );
			$data['complite_text'] = $this->data_value($_POST['complite_text'] );
			
			
			if(!$_POST['complite'] ){$_POST['complite'] =0;}
			$data['complite'] = $this->data_value($_POST['complite'] );
			
			$data['img'] = $this->data_value($_POST['img'] );
			$data['description'] = $this->data_value($_POST['description'] );
			$data['order'] = $this->data_value($_POST['order'] );
			$data['keys_message'] = $this->data_value($_POST['keys_message'] );
			$data['lat'] = $this->data_value($_POST['map_lat'] );
			$data['lon'] = $this->data_value($_POST['map_lon'] );
			$data['kvartal'] = $this->data_value($_POST['kvartal'] );
			$data['adress'] = $this->data_value($_POST['map_adress'] );
			$data['keys_adress'] = $this->data_value($_POST['keys_adress'] );
			$data['wallmaterial'] = $this->data_value($_POST['wallmaterial'] );
			$data['floor'] = $this->data_value($_POST['floor'] );
			$data['show_keys'] = $this->data_value( $_POST['show_keys'] , 0 );
			$data['map_mapkeys_adress'] = $this->data_value( $_POST['map_mapkeys_adress'] ) ;
			$data['map_mapkeys_lat'] = $this->data_value( $_POST['map_mapkeys_lat'] );
			$data['map_mapkeys_lon'] = $this->data_value( $_POST['map_mapkeys_lon'] );
			$data['built_year'] = $this->data_value( $_POST['built_year'],false);
			$data['ready_quarter'] = $this->data_value( $_POST['ready_quarter'],1);
			$data['renovation'] = $this->data_value( $_POST['renovation'] ,false);
			$data['avito_id'] = $this->data_value( $_POST['avito_id'] ,false);

			if($_POST['delivery_date'])
			{
				$delivery_date = date('Y-m-d' , strtotime( $_POST['delivery_date'] ) );
				if( $delivery_date )
				{
					$data['delivery_date'] = $this->data_value( $delivery_date );
				}
			}
			
			// $data['delivery_date'] = $_POST['delivery_date'];
			  
			  //print '<pre>';
			//  print_r($_POST);
			 // print '</pre>';
			 // print '<pre>';
			//  print_r($data);
			  //print '</pre>';
			  
			if($id) // Редактирваоние существующей записи
			{
				print 'Изменения сохранены!';
				print   $mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
			}
			else // Добавление новой записи
			{
				print 'Запись добавлена!';
				$mysql -> insert( $this->table, $data );
			}
			$this->act__index();
		}
		 
	 
	}
	
	
	/*
	Адреса 800 серия 
	
	
	Даты выдачи ключей от сюда
	https://em-nsk.ru/projects/ 
	*/
	
	
	
	function ajcrud_filtr()
	{
		?>
		
		
		<div class="filter-item filter-item-checkbox"  > 
			<input type="checkbox" id="showhide" name="showhide" value="1" <? if($_GET['showhide']){print ' checked="checked" ';} ?>> <label for="showhide">Скрытые</label><br/>
		</div>
		
		<div class="filter-item filter-item-checkbox"  > 
			<input type="checkbox" id="show_keys" name="show_keys" value="1" <? if($_GET['show_keys']){print ' checked="checked" ';} ?>> <label for="show_keys">Выдача ключей</label><br/>
		</div>
 
		<?
	}
	
	
	
	
	
	function act__index()
	{
  
		global $t;
		$t['h1'] = 'Настройки объектов';
	 
		$this->display_ajax_crud();
	}
	
	
	
}