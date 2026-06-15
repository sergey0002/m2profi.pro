<?
class ctr__parking_buildings extends ctr__
{  

	var $table = 'parking_buildings'; //Главная таблица
	var $key_filed = 'parking_building_id'; // Ключевое поле главной таблицы
	var $ctr = 'parking_buildings';
    var $title = 'Парковки - здания';
   
 
	function __construct()
	{
		
		$data=$this->getfiltr(); // Получаем данные для вывода
		$this->data=$data; // Сохраняем данные
			
			
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
		$titles['order'] = 'Сортировка';
		$titles['adress'] = 'Адрес';
		$titles['show'] = 'Показывать';
		$titles['complite'] = 'Сдан';
		$titles['edit'] = 'Действия'; 
		$this->ajcrud_table_titles=$titles;
		
		// Не переносить по словам
		$nowrap=array();
		$nowrap['edit'] = 1;
		$this->table_nowrap=$nowrap;
		
		// Сортировать по столбцам
		$order=array();
		$order[$this->key_filed]=$this->key_filed;
		$order['adress']='adress';
		$order['show']='`show`';
		 
		$this->ajcrud_table_order=$order; 
		$this->aj_crud_addbutton=1;
		$this->aj_crud_addbutton_iframe=0; // Новый обект в ифрейме
	}
 
 	// БАзовый запрос  menu
	function get_base_sql($filtr_data='')
	{
		
		if(!$filtr_data){$filtr_data =  $_REQUEST;}
		
		global $mysql;
		$q = 'SELECT '.$this->table.'.* FROM  '.$this->table.'    WHERE 1=1 ';
		
		if(!$_GET['show_dell']){	$q.=' AND `'.$this->table.'`.`del`="0" ';	}
		else{$q.=' AND `'.$this->table.'`.`del`="1" ';}
		
		
		if( $filtr_data['parking_building_id'] )
		{
			$q.=' AND parking_buildings.parking_building_id = "'.$filtr_data['parking_building_id'].'" '; // Только актуальные брони без истории
		}
		
		if( $filtr_data['order_filed'] )
		{
			//$q.=' ORDER BY '.$filtr_data['order_filed']; // Только актуальные брони без истории
			//if( $filtr_data['order_asc'] ) { $q.=' ASC '; } else { $q.=' DESC '; }
		}
		else
		{
				//$q.=' ORDER BY parking_broni.date'; 
				//$q.='  DESC ';  
			
		}
		// if($_GET['id']){$q.=''}
		 //  print $q;
		return $q;
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
	
	
	

	
	 
	 
	 
	 
	function act__edit()
	{
		global $t;
		$t['h1'] = 'Редактирование здания';
		
		global $filed;
		global $mysql;
		global $r;
		 
		# Данные редактирования
		$id = $_GET['id'];
		if($id)
		{
			$data = $mysql->get_for_key($this->table,$this->key_filed,$_GET['id']);
			//print '<h2>Редактирование объекта </h2>';
		}
		else
		{
			//print '<h2>Добавление объекта</h2>';
		}
		
		
		if(!$_POST) ############# ФОРМА
		{
		?>
		
		<style>
		.input_edit {width:100%; max-width:100%;}
		</style>
					
			<form action="<?=$r->acturl($this->ctr,'edit');?>&id=<?=$_GET['id']?>" method="POST" id="editform"  >
		 
			<div id="tree_check"></div>

			<br/><br/>
			<?=$this->formpanel($r->acturl($this->ctr,'index'));?>
			<div class="row">
				<div class="col-md-6">
				<?
					// $filed->text('caption','Заголовок',$data['caption']); print '<br/>';
					// $filed->text('build_type','Тип здания',$data['build_type']); print '<br/>';
					
					$filed->text('street','Улица (для поиска)',$data['street']); print '<br/>';
					$filed->text('adress_disp','Адрес (для поиска)',$data['adress_disp']); print '<br/>';
					?>
					  
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
					
					
					<?
					
					
					//$filed->textarea('descr','Описание',$data['descr'],' rows="2" style="height: 100px;" '); print '<br/>';
				?>
				</div>
				<div class="col-md-6">
				<?
					//$filed->image('photo','Фото ',$data['image'],'34256'); print '<br/>';
				?>
				</div>
			</div>
			<?
				// Карта (координаты и адресс)
				$data_map = array();
				$data_map['lat'] = $data['lat'];
				$data_map['lon'] = $data['lon'];
				$data_map['adress'] = $data['adress'];
				
				
				$filed->map('map',$data_map);  
			?>
			</form>
		
 		
			</div>
	
		<?
		}
 
		if($_POST) ############# Обработка данных пост
		{
			if(!$id){$id=$_GET['id'];}
			
			//print '<pre>';
			//print_r($_POST);
			//print '</pre>';

			$data = array();
			$data['caption'] = $_POST['caption'];
			// $data['build_type'] = $_POST['build_type'];
			//$data['descr'] = $_POST['descr'];
			$data['adress'] = $_POST['map_adress'];
			$data['lat'] = $_POST['map_lat'];
			$data['lon'] = $_POST['map_lon'];
			$data['street'] = $_POST['street'];
			
			$data['adress_disp'] = $_POST['adress_disp'];
			$data['order'] = $_POST['order'];
			$data['show'] = $_POST['show'];
			$data['delivery_date'] = $_POST['delivery_date'];
			$data['complite_text'] = $_POST['complite_text'];
			$data['complite'] = $_POST['complite'];
				
				
				
			if($id) // Редактирваоние существующей записи
			{
				//	print 'Изменения сохранены!';
				$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
				
			}
			else // Добавление новой записи
			{
				//	print 'Запись добавлена!';
				$mysql -> insert( $this->table , $data );
			}
			
			 $this->act__index();
		}
		 
	}
	
	

	function display_table__show($data)
	{
		$show=array();
		$show[0] = 'НЕТ';
		$show[1] = 'Всем';
		$show[2] = 'Админу';
		$show[3] = 'Админу и ОП';
		
		return $show[$data['show']];
	}
	function display_table__complite($data)
	{
		$show=array();
		$show[0] = 'НЕТ';
		$show[1] = 'ДА';
 
		return $show[$data['complite']];
	}
	
	
	
	
	
	
    
	
	function act__index()
	{
		global $t;
		$t['h1'] = 'Парковки - здания';
  
		$this->display_ajax_crud();
	}
	 
	
	
	
}