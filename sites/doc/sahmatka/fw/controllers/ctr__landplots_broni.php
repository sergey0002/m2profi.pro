<?
// КНопка восстановить статус от текущей даты! - удалить запись из истории броней
// снятие броней
// Грузить продажника предыдущую бронь 
// счетчик обновления броней
	

class ctr__landplots_broni extends ctr__
{  

	var $table = 'landplots_broni'; //Главная таблица
	var $key_filed = 'lp_broni_id'; // Ключевое поле главной таблицы
	var $ctr = 'landplots_broni';
    var $title = 'Брони участков';
   
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
		
		
		if($_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem' )
		{
			// $titles['checkrow'] = '1';
		}
		$titles[$this->key_filed] = 'id';
		
		$titles['lpcaption'] = 'Поселок';
		$titles['date'] = 'Дата';
		 
		$titles['num'] = 'Участок'; 
		$titles['price'] = 'Цена';
		$titles['status'] = 'Статус';
		 
		if($_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem' )
		{
			$titles['caption'] = 'Агентство'; 
			$titles['login'] = 'Логин';
			$titles['exrow'] = ''; // плюсик
			$this->display_table_exrow=1; // раскрывать строки
		}
		else
		{
			$this->display_table_exrow=0; // раскрывать строки
		}
		
		$this->ajcrud_table_titles=$titles;
		
		// Не переносить по словам
		$nowrap=array();
		$nowrap['date']=1;
		$nowrap['num'] = 1;
		$this->ajcrud_table_nowrap=$nowrap;
		
		// Сортировать по столбцам
		$order=array();
		$order[$this->key_filed]=$this->key_filed;
		$order['date']='date';
		$this->ajcrud_table_order=$order; 
		
		
	}
	
	
	
	
 
 	// БАзовый запрос  menu
	function get_base_sql($filtr_data='')
	{
		
		if(!$filtr_data){$filtr_data =  $_REQUEST;}
		
		global $mysql;
		$q = 'SELECT  landplots.*,landplots_broni.*,users.*,agency.*,count(landplots_broni.lp_broni_id)as bc , landplots.status as status , landplots_area.caption as lpcaption
		
		FROM  landplots    
		LEFT JOIN landplots_broni ON landplots_broni.lp_broni_id = landplots.status_broni_id /* АКТУАЛЬНЫЕ БРОНИ */
		LEFT JOIN landplots_maps ON landplots.map_id = landplots_maps.landplots_map_id
		LEFT JOIN landplots_area ON landplots_area.area_id = landplots_maps.area_id
		
		/* Грузить еще предидущую бронь чтобы узнать кто бронировал перед продажей! */
	 
 
		LEFT JOIN users ON landplots_broni.user_id = users.id
		LEFT JOIN agency ON agency.agency_id = users.agency_id
		
		WHERE 1=1 
 
		';
		  $q.=' AND  landplots_broni.lp_broni_id>0';  
		  
		  $q.=' AND  landplots.status!="0" AND  landplots.status!="2" '; // Только НЕ СВОБОДНЫЕ КВАРТИРЫ
		  
		     
		
		// $q.=' AND parking_spaces.status_broni_id = parking_broni.parking_broni_id '; // Только актуальные брони без истории
		
		//if( !$_GET['showdel'] ){	$q.=' AND `'.$this->table.'`.`del`="0" ';	}
 
		if($_SESSION['sh_login'] != 'admin'  &&   $_SESSION['sh_login'] != 'goodzem' ) // только свое агентсво если не админ
		{
			$q.=' AND agency.agency_id = "'. $_SESSION['agency_id'].'" '; 
		}
		
		 
		if( $filtr_data['status'] )
		{
			$q.=' AND landplots.status = "'.$filtr_data['status'].'" '; // Только актуальные брони без истории
		}
		
		
		if( $filtr_data['agency_id'] )
		{
			$q.=' AND agency.agency_id = "'.$filtr_data['agency_id'].'" '; 
		}
		if( $filtr_data['user_id'] )
		{
			$q.=' AND users.id = "'.$filtr_data['user_id'].'" ';  
		}
		
		$q.=' GROUP BY landplots.lp_id ';
		
		if( $filtr_data['order_filed'] )
		{
			$q.=' ORDER BY '.$filtr_data['order_filed']; // Только актуальные брони без истории
			if( $filtr_data['order_asc'] ) { $q.=' ASC '; } else { $q.=' DESC '; }
		}
		else
		{
				$q.=' ORDER BY landplots_broni.date'; 
				$q.='  DESC ';  
			
		}
   
		 // if($_GET['id']){$q.=''}
		 //  print $q;
		return $q;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function ajcrud_filtr()
	{
		?>
			<div class="filter-item" style="display:none;"> 
			<?	
			 $this->filtr_select('Статус','status','status');	
			?>
			</div>	
		<?
		if($_SESSION['sh_login'] == 'admin'  ||  $_SESSION['sh_login'] == 'goodzem' )  
		{
			
			
			?>
			<div class="filter-item"  > 
			<?	
			$this->filtr_select('Поселок','agency_id','lpcaption');	
			?>
			</div>	
			<? 
			
			
			?>
			<div class="filter-item"  > 
			<?	
			$this->filtr_select('Агентство','agency_id','caption');	
			?>
			</div>	
			<? 
		}
		?>
			<div class="filter-item"  > 
			<?	
			$this->filtr_select('Логин','user_id','login');	
			?>
			</div>	
			
			
			<?
			
			if( $_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem' )
			{
			?>
			<div class="filter-item"> 
				<span class="input_title">Статус</span>
				<select name="status" class="input_edit" style="text-transform:none; height:auto;">
					<option value="0" selected="selected">Не задан </option>
			 
					<option value="3">Продан </option>
					<option value="4">Забронирован </option>
					<option value="5">Забронирован застройщиком </option>
					<option value="6">Участок подрядчика </option>
				</select>
			</div> 
			<?
			}
			?>
			
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


 
 


	function act__ajax_crud_check()
	{
		global $r;
		global $mysql;
		 print_r($_POST);
		 
	//	 if(!$_POST['checkrow'] || $_POST['status'] ){return;}
		foreach($_POST['checkrow'] as $k => $v )
		{
			// $k - id брони! нам нужно передавать ид участка и менять статус его с сохранением транзакции (добавление новой брони админа)
			$data = $mysql->get_for_key( $this->table , $this->key_filed , $k); 
			print_r($data['lp_id'] );
   
			$landplots = $r->get_object('landplots'); // контроллер лендплотс
			
			print  $broni_idx = $landplots->add_broni( $data['lp_id'] , $_POST['status'] );
			  
			  $datax = array();
			  $datax['status_broni_id'] = $broni_idx; 
			  $datax['status'] = $_POST['status']; 
			  $mysql->update_for_key('landplots','lp_id', $data['lp_id'] , $datax , 0);
  
		}
	}


	// Метод генерирует ссылку аякс для контента раскрывающейся строки
	function display_hr_ajax($v)
	{
		return '/sahmatka/ajax_router.php?ctr=landplots&act=broni_history&id='.$v['lp_id'];
	}

	function act__index()
	{
		global $t;
		$t['h1'] = 'Брони участков';
 
		$this->display_ajax_crud();
	}
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	
	
	
	
		
	
	
	
}