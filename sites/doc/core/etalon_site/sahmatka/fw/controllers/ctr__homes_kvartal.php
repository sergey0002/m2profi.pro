<?
$GLOBALS['t']['title']='Редактор ЖК';

class ctr__homes_kvartal extends ctr__
{ 

 
	var $table = 'homes_kvartal'; //Главная таблица
	var $key_filed = 'homes_kvartal_id'; // Ключевое поле главной таблицы
	var $ctr = 'homes_kvartal';
	var $title = 'Проекты (Комплексы)';
	var $title_act1 = 'Проекты';
	var $title_act2 = 'Проекты';
	
	function __construct()
	{
		$this->mysql=$GLOBALS['mysql'];
	 
		$data=$this->getfiltr(); // Получаем данные для вывода
		$this->data=$data; // Сохраняем данные
		 	
		$data_nofiltr=$this->getfiltr([1]); // Данные без фильров для селектов?! очень ресуроемко ! по сути все брони перебирает циклом , но с другой стороны у нас все записи выводятся и ничего 
		$this->data_nofiltr=$data_nofiltr; // Сохраняем данные
	  
		// Выводимые столбцы
		$titles = array();
		$titles['homes_kvartal_id'] = 'ID';
		$titles['show'] = 'Отображение';
		$titles['title'] = 'Название';
		$titles['order'] = 'Сортировка'; 
	    $titles['edit'] = 'Редактирование'; 
		$this->ajcrud_table_titles=$titles;
		
		// Не переносить по словам
		$nowrap=array();
		$nowrap['edit'] = 1;
		$this->table_nowrap=$nowrap;
		
		// Сортировать по столбцам
		//$order=array();
		//$order[$this->key_filed]=$this->key_filed;
		//$order['adress']='adress';
		//$order['show']='`show`';
		 
		//$this->ajcrud_table_order=$order; 
		$this->aj_crud_addbutton=1;		
	}
	 
	
	// БАзовый запрос 
	function get_base_sql( )
	{
		foreach($_GET as $k=>$v){  $filtr_data[$k]=$v;	}
		$q = 'SELECT * FROM `'.$this->table.'` WHERE 1=1 ';
		//if(!$filtr_data['showhide']){$q .= 'AND (`show`="1" or `show`="2" or `show`="3") '; }
		 
		//if($filtr_data['show_keys']){$q .= 'AND show_keys = "'.$filtr_data['show_keys'].'" '; }
		return $q.'  ORDER BY homes_kvartal.order    ';
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
			$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
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
		global $filed;
		global $mysql;
		global $r;
		global $t;
	 	
		$t['h1'] = 'Редактирование ЖК';
		# Данные редактирования
		$id = $_GET['id'];
		if($id)
		{
			$data = $mysql->get_for_key($this->table,$this->key_filed,$_GET['id']);
	 
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
				  
				  <?=$filed->text('title','Заголовок',$data['title']);?><br/>
				  <?=$filed->text('order','Порядок сортировки',$data['order']);?><br/>
				  <?
						$data_sel=array();
						$data_sel[1] = 'Всем';
						$data_sel[2] = 'Админам';
						$data_sel[3] = 'Админам и ОП';
						$data_sel[0] = 'НЕТ';
					?>
					<?=$filed->select('show','Показывать',$data_sel,$data['show']);?><br/>
				</div>
				<div class="col-md-6">
					<?=$filed->textarea('description','Описание',$data['description']);?><br/> 
				</div>
			</div>
			
			<h2>Объект на карте</h2>
			 <?
				// Карта (координаты и адресс)
				$data_map = array();
				$data_map['lat'] = $data['latitude'];
				$data_map['lon'] = $data['longitude'];
				$data_map['adress'] = $data['adress'];
				$filed->map('map',$data_map);  
			?>
			<hr/>
				<h2>Домклик</h2>
				<?=$filed->text('complex_domclick','complex (Домлик)',$data['complex_domclick']);?><br/>
				
				<?=$filed->checkbox('infrastructure_parking', 'Парковка', $data['infrastructure_parking'])?>
				<?=$filed->checkbox('infrastructure_security', 'Охрана', $data['infrastructure_security'])?>
				<?=$filed->checkbox('infrastructure_fenced_area', 'Огороженная территория', $data['infrastructure_fenced_area'])?>
				<?=$filed->checkbox('infrastructure_sports_ground', 'Спортивная площадка', $data['infrastructure_sports_ground'])?>
				<?=$filed->checkbox('infrastructure_playground', 'Детская площадка', $data['infrastructure_playground'])?>
				<?=$filed->checkbox('infrastructure_school', 'Школа', $data['infrastructure_school'])?>
				<?=$filed->checkbox('infrastructure_kindergarten', 'Детский сад', $data['infrastructure_kindergarten'])?>
	  
			</form>
			<a href="https://domclick.ru/complexes/123__<?=$data['complex_domclick']?>" target="_blank">Страница комплекса (ЖК)</a>
		<?
		}
 
		if($_POST) ############# Обработка данных пост
		{
 
			 //print '<pre>';
			 // print_R($_POST);
			// print '</pre>';
 
			$data = array();
			   
			$data['title'] =  $this->data_value($_POST['title']);
			$data['order'] = $this->data_value($_POST['order'],0);
			$data['show'] = $this->data_value($_POST['show'],0);
			$data['description'] = $this->data_value($_POST['description']);
			$data['latitude'] = $this->data_value($_POST['map_lat']);
			$data['longitude'] = $this->data_value($_POST['map_lon']);
			$data['adress'] = $this->data_value($_POST['map_adress']);
			
			$data['infrastructure_parking'] = $this->data_value($_POST['infrastructure_parking'],0);
			$data['complex_domclick'] = $this->data_value($_POST['complex_domclick'],0);
			$data['infrastructure_security'] = $this->data_value($_POST['infrastructure_security'],0);
			$data['infrastructure_fenced_area'] = $this->data_value($_POST['infrastructure_fenced_area'],0);
			$data['infrastructure_sports_ground'] = $this->data_value($_POST['infrastructure_sports_ground'],0);
			$data['infrastructure_playground'] = $this->data_value($_POST['infrastructure_playground'],0);
			$data['infrastructure_school'] = $this->data_value($_POST['infrastructure_school'],0);
			$data['infrastructure_kindergarten'] = $this->data_value($_POST['infrastructure_kindergarten'],0);
			  
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
		return;
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
		$t['h1'] = 'Настройки ЖК';
	 
		$this->display_ajax_crud();
	}
	
	
	
}