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
		$titles['kvartal'] = 'Квартал'; 
		$titles['complite_text'] = 'Статус';
	//	$titles['description'] = 'Описание'; 
		$titles['order'] = 'Сортировка'; 
	    $titles['show_keys'] = 'Выдача ключей'; 
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
		$q = 'SELECT * FROM `'.$this->table.'` WHERE 1=1 ';
		if(!$filtr_data['showhide']){$q .= 'AND (`show`="1" or `show`="2" or `show`="3") '; }
		 
		if($filtr_data['show_keys']){$q .= 'AND show_keys = "'.$filtr_data['show_keys'].'" '; }
		return $q.'  ORDER BY homes.order    ';
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
			if(!$data['img'] ||1==1){ $data['img'] = 'http://' . $GLOBALS['config']['domain'] . '/'render/'.$data['home_id'].'.jpg'; }
			
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
				 <?=$filed->text('wallmaterial','Материал / технология',$data['wallmaterial']);?><br/>
				 <?=$filed->text('floor','Этажей',$data['floor']);?><br/>
				 <?=$filed->select('kvartal','Квартал',$kvartal,$data['kvartal']);?><br/>
				<?=$filed->text('img','Изображение',$data['img']);?><br/>
				</div>
				
				
				
				
				<div class="col-md-6">
				<?=$filed->text('order','Порядок сортировки',$data['order']);?><br/>
					<?
						$data_sel=array();
						$data_sel[1] = 'Всем';
						$data_sel[2] = 'Админам';
						$data_sel[3] = 'Админам и ОП';
						$data_sel[0] = 'НЕТ';
					?>
					<?=$filed->select('show','Показывать',$data_sel,$data['show']);?><br/>
					
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
				<b>Фид домклик:</b>  https://em.m2profi.pro/sahmatka/domclick-<?=$data['home_id']?>.xml
			</form>
			
		<?
		}
 
		if($_POST) ############# Обработка данных пост
		{
	 
			//print '<pre>';
			//print_R($_POST);
			//print '</pre>';

			$data = array();
			 
			$data['home_id'] = $_POST['home_id'];
			if($_POST['complex_domclick']) { $data['complex_domclick'] = $_POST['complex_domclick']; }
			if($_POST['corpus_code_domclick'])  { $data['corpus_code_domclick'] = $_POST['corpus_code_domclick']; }
			$data['title'] = $_POST['title'];
			$data['long_title'] = $_POST['long_title'];
			$data['show'] = $_POST['show'];
			$data['complite_text'] = $_POST['complite_text'];
			$data['img'] = $_POST['img'];
			$data['description'] = $_POST['description'];
			$data['order'] = $_POST['order'];
			$data['keys_message'] = $_POST['keys_message'];
			$data['lat'] = $_POST['map_lat'];
			$data['lon'] = $_POST['map_lon'];
			$data['kvartal'] = $_POST['kvartal'];
			$data['adress'] = $_POST['map_adress'];
			$data['keys_adress'] = $_POST['keys_adress'];
			$data['wallmaterial'] = $_POST['wallmaterial'];
			$data['floor'] = $_POST['floor'];
			
			if($_POST['show_keys']){$data['show_keys'] = $_POST['show_keys'];}
			else{$data['show_keys']=0;}
			
			
			$data['map_mapkeys_adress'] = $_POST['map_mapkeys_adress'];
			$data['map_mapkeys_lat'] = $_POST['map_mapkeys_lat'];
			$data['map_mapkeys_lon'] = $_POST['map_mapkeys_lon'];
			
	 
			
			
			if($_POST['delivery_date'])
			{
				$delivery_date = date('Y-m-d' , strtotime( $_POST['delivery_date'] ) );
				if( $delivery_date )
				{
					$data['delivery_date'] = $delivery_date;
				}
			}
			
			// $data['delivery_date'] = $_POST['delivery_date'];
			  
			  print '<pre>';
			//  print_r($_POST);
			 // print '</pre>';
			 // print '<pre>';
			//  print_r($data);
			  print '</pre>';
			  
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