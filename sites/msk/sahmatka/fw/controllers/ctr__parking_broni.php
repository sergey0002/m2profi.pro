<?
// КНопка восстановить статус от текущей даты! - удалить запись из истории броней
// снятие броней
// Грузить продажника предыдущую бронь 
// счетчик обновления броней
	

class ctr__parking_broni extends ctr__
{  

	var $table = 'parking_broni'; //Главная таблица
	var $key_filed = 'parking_broni_id'; // Ключевое поле главной таблицы
	var $ctr = 'parking_broni';
    var $title = 'Брони парковок';
   
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
		$titles['date'] = 'Дата';
		$titles['caption'] = 'Агентство'; 
		$titles['login'] = 'Логин';
		$titles['adress_disp'] = 'Адрес';
		$titles['floor'] = 'Этаж';
		$titles['num'] = 'Место'; 
		$titles['price'] = 'Цена';
		$titles['status'] = 'Статус';
		$titles['exrow'] = ''; // плюсик
		$this->ajcrud_table_titles=$titles;
		
		// Не переносить по словам
		$nowrap=array();
		$nowrap['date']=1;
		$nowrap['adress_disp']=1;
		$nowrap['floor'] = 1;
		$nowrap['num'] = 1;
		$this->ajcrud_table_nowrap=$nowrap;
		
		// Сортировать по столбцам
		$order=array();
		$order[$this->key_filed]=$this->key_filed;
		$order['date']='date';
		$this->ajcrud_table_order=$order; 
		
		$this->display_table_exrow=1; // раскрывать строки
	}
	
	
	
	
 
 	// БАзовый запрос  menu
	function get_base_sql($filtr_data='')
	{
		
		if(!$filtr_data){$filtr_data =  $_REQUEST;}
		
		global $mysql;
		$q = 'SELECT  parking_spaces.*,parking_broni.*,parking_floors.*,parking_buildings.*,users.*,agency.*,count(parking_broni.parking_broni_id)as bc , parking_spaces.status as status FROM  parking_spaces    
		LEFT JOIN parking_broni ON parking_broni.parking_broni_id = parking_spaces.status_broni_id /* АКТУАЛЬНЫЕ БРОНИ */
		/* Грузить еще предидущую бронь чтобы узнать кто бронировал перед продажей! */
		LEFT JOIN parking_floors ON parking_floors.parking_floor_id = parking_spaces.parking_floor_id
		LEFT JOIN parking_buildings ON parking_buildings.parking_building_id = parking_floors.parking_building_id
 
		LEFT JOIN users ON parking_broni.user_id = users.id
		LEFT JOIN agency ON agency.agency_id = users.agency_id
		
		WHERE 1=1 
 
		';
		  $q.=' AND  parking_broni.parking_broni_id>0';  
		  
		  $q.=' AND  parking_spaces.status!="0" AND  parking_spaces.status!="2" '; // Только НЕ СВОБОДНЫЕ КВАРТИРЫ
		  
		  
		  
		
		// $q.=' AND parking_spaces.status_broni_id = parking_broni.parking_broni_id '; // Только актуальные брони без истории
		
		//if( !$_GET['showdel'] ){	$q.=' AND `'.$this->table.'`.`del`="0" ';	}
 
		if( $filtr_data['parking_building_id'] )
		{
			$q.=' AND parking_buildings.parking_building_id = "'.$filtr_data['parking_building_id'].'" '; // Только актуальные брони без истории
		}
		
		
		if( $filtr_data['status'] )
		{
			$q.=' AND parking_spaces.status = "'.$filtr_data['status'].'" '; // Только актуальные брони без истории
		}
		
		
		if( $filtr_data['agency_id'] )
		{
			$q.=' AND agency.agency_id = "'.$filtr_data['agency_id'].'" '; 
		}
		if( $filtr_data['user_id'] )
		{
			$q.=' AND users.id = "'.$filtr_data['user_id'].'" ';  
		}
		
		$q.=' GROUP BY parking_spaces.parking_space_id ';
		
		if( $filtr_data['order_filed'] )
		{
			$q.=' ORDER BY '.$filtr_data['order_filed']; // Только актуальные брони без истории
			if( $filtr_data['order_asc'] ) { $q.=' ASC '; } else { $q.=' DESC '; }
		}
		else
		{
				$q.=' ORDER BY parking_broni.date'; 
				$q.='  DESC ';  
			
		}
   
		 // if($_GET['id']){$q.=''}
		 //  print $q;
		return $q;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function ajcrud_filtr()
	{
		?>
		<div class="filter-item"> 
			<? $this->filtr_select('Здание','parking_building_id','adress_disp');	?>
			</div>	

			<div class="filter-item" style="display:none;"> 
			<?	
			 $this->filtr_select('Статус','status','status');	
			?>
			</div>	

			<div class="filter-item"  > 
			<?	
			$this->filtr_select('Агентство','agency_id','caption');	
			?>
			</div>	
			 <div class="filter-item"  > 
			<?	
			$this->filtr_select('Логин','user_id','login');	
			?>
			</div>	
			
			<div class="filter-item"> 
				<span class="input_title">Статус</span>
				<select name="status" class="input_edit" style="text-transform:none; height:auto;">
					<option value="0" selected="selected">Не задан </option>
			 
					<option value="3">Продана </option>
					<option value="4">Забронирована </option>
					<option value="5">Забронирована застройщиком </option>
					<option value="6">Парковка подрядчика </option>
				</select>
			</div> 
			<div class="filter-item filter-item-checkbox" style="display:none;"> 
				<input type="checkbox"    id="show_dell" name="show_dell" value="1" <?=$this->get_form_check($this->formid('indexsearch'),'show_dell','request')?>> <label for="show_dell">Удаленные</label><br/>
			</div>
		<?
	}
	
	
	
	 
 
	
	
	### ПОДГОТОВКА К ВЫВОДУ КОНТЕНТА СТОЛБЦОВ
	function display_table__login($v)
	{
		return '<b>'.$v['login'].'</b>' . ' ('.$v['name'].')';
	}
	function display_table__date($v)
	{
		return fromsql_date($v['date']);
	}
	function display_table__status($v)
	{
		global $status_arr;
		global $status_color_arr;
		return  '<span style="background-color:'. $status_color_arr[$v['status']].';  "><b>'. $status_arr[$v['status']].'</b></span>';
	}
	
	function display_table__edit($row)
	{
		return $link = '
		<a href="?ctr='.$this->ctr.'&act=edit&id='.$row[$this->key_filed].'" class="table-edit"> </a> 
		&nbsp;
		&nbsp;
		<a href="?ctr='.$this->ctr.'&act=del&id='.$row[$this->key_filed].'" style="color:red;  font-size: 21px;"> X </a>
		';
	}


	// Метод генерирует ссылку аякс для контента раскрывающейся строки
	function display_hr_ajax($v)
	{
		return '/sahmatka/ajax_router.php?ctr=parking_spaces&act=broni_history&id='.$v['parking_space_id'];
	}

	function act__index()
	{
		global $t;
		$t['h1'] = 'Брони парковок';
 
		$this->display_ajax_crud();
	}
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	
	
	
	
		
	
	
	
}