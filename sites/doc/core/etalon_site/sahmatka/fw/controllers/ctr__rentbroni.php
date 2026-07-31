<?
// КНопка восстановить статус от текущей даты! - удалить запись из истории броней
// снятие броней
// Грузить продажника предыдущую бронь 
// счетчик обновления броней
	

class ctr__rentbroni extends ctr__ 
{  

	var $table = 'rentbroni'; //Главная таблица
	var $key_filed = 'rent_broni_id'; // Ключевое поле главной таблицы
	var $ctr = 'rentbroni';
    var $title = 'Брони на аренду';
   
	function __construct()
	{
		 
		$data=$this->getfiltr($filtr);
		$this->data=$data; // Сохраняем данные	
		  
		$data_nofiltr=$this->getfiltr([1]); // Данные без фильров для селектов?! очень ресуроемко ! по сути все брони перебирает циклом , но с другой стороны у нас все записи выводятся и ничего 
		$this->data_nofiltr=$data_nofiltr; // Сохраняем данные
		 
		 
		$titles[$this->key_filed] = 'id';
		$titles['date'] = 'Дата';
		$titles['caption'] = 'Агентство'; 
		$titles['login'] = 'Логин';
		$titles['adress'] = 'Адрес';
		$titles['area'] = 'Площадь';
		$titles['floor'] = 'Этаж';
		$titles['appart_num'] = 'Помещение'; 
		 
		$titles['status'] = 'Статус';
		$titles['bc'] = 'Записей';
		$this->ajcrud_table_titles=$titles;
		 
	 
		// Сортировать по столбцам
		$order=array();
		$order[$this->key_filed]=$this->key_filed;
		$this->aj_crud_table_order=$order; 
		 
		
		// Не переносить по словам
		$nowrap=array();
		$nowrap['edit'] = 1;
		$this->table_nowrap=$nowrap;
 
		// $this->aj_crud_addbutton=0;
		$this->display_table_exrow=0; // раскрывать строки
		
	}
	
 // Получить двумрный массив по фильтру
	function getfiltr($filtr)
	{
		global $mysql;
		$sql = $this->get_base_sql( $filtr );
		$data = $mysql->get_arr($sql);
		return $data;	
	}
	
	
 
 	// БАзовый запрос  menu
	function get_base_sql($where='')
	{
		
		$filtr_data =  $_REQUEST;
		
		global $mysql;
		$q = 'SELECT rent_objects.*,rent_broni.*, rent_homes.*,users.*,agency.*,count(rent_broni.rent_broni_id)as bc , rent_objects.status as status 

FROM rent_objects LEFT JOIN rent_broni ON rent_broni.rent_broni_id = rent_objects.status_broni_id /* АКТУАЛЬНЫЕ БРОНИ */ /* Грузить еще предидущую бронь чтобы узнать кто бронировал перед продажей! */ 
 
LEFT JOIN rent_homes ON rent_homes.rent_home_id= rent_objects.rent_home_id 

LEFT JOIN users ON rent_broni.user_id = users.id 
LEFT JOIN agency ON agency.agency_id = users.agency_id 
WHERE 1=1   


 
		';
		  $q.=' AND  rent_broni.rent_broni_id>0';  
		  
		  $q.=' AND  rent_objects.status!="0" AND  rent_objects.status!="2" '; // Только НЕ СВОБОДНЫЕ КВАРТИРЫ
		  
		  
		  
		
		// $q.=' AND rent_objects.status_broni_id = rent_broni.rent_broni_id '; // Только актуальные брони без истории
		
		//if( !$_GET['showdel'] ){	$q.=' AND `'.$this->table.'`.`del`="0" ';	}
 
		if( $filtr_data['parking_building_id'] )
		{
			$q.=' AND parking_buildings.parking_building_id = "'.$filtr_data['parking_building_id'].'" '; // Только актуальные брони без истории
		}
		
		
		if( $filtr_data['status'] )
		{
			$q.=' AND rent_objects.status = "'.$filtr_data['status'].'" '; // Только актуальные брони без истории
		}
		
		
		if( $filtr_data['agency_id'] )
		{
			$q.=' AND agency.agency_id = "'.$filtr_data['agency_id'].'" '; 
		}
		if( $filtr_data['user_id'] )
		{
			$q.=' AND users.id = "'.$filtr_data['user_id'].'" ';  
		}
		
		$q.=' GROUP BY rent_objects.rent_objects_id   ';
		
		if( $filtr_data['order_filed'] )
		{
			$q.=' ORDER BY '.$filtr_data['order_filed']; // Только актуальные брони без истории
			if( $filtr_data['order_asc'] ) { $q.=' ASC '; } else { $q.=' DESC '; }
		}
		else
		{
				$q.=' ORDER BY rent_broni.date  '; 
				$q.='  DESC ';  
			
		}
 


		
		// if($_GET['id']){$q.=''}
		 //   print $q;
		return $q;
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	 
		
		
		
	

	
	
	
	
	// Панель редактирования
	function actions_panel($result)
	{
		if($result['del']){$del_class = 'dtable_del_class';}
		else{ $del_class = ''; }
			
		if(!$result['del'])
		{
			print '<td class="dtable '.$del_class.'">'.$result['order'].'&nbsp; &nbsp;  
			
			<a href="ajax_router.php?ctr='.$this->ctr.'&act=orderup&id='.$result[$this->key_filed].'" class="fw_ajaxlink" data-id="'.$result[$this->key_filed].'" style="font-size: 18px;">&#8593; </a> 
			&nbsp; 
			<a href="ajax_router.php?ctr='.$this->ctr.'&act=orderdown&id='.$result[$this->key_filed].'" class="fw_ajaxlink" data-id="'.$result[$this->key_filed].'" style="font-size: 18px;">&#8595;	</a> 
			</td>';

			print '<td class="dtable" style="">';
			print '<a href="index.php?ctr='.$this->ctr.'&act=editpage&id='.$result[$this->key_filed].'" class="table-edit"  ></a>
			&nbsp;
			<a href="ajax_router.php?ctr='.$this->ctr.'&act=del&id='.$result[$this->key_filed].'" class="fw_ajaxlink" data-actionhide="1" data-reloadall="1" data-id="'.$result[$this->key_filed].'" data-confirm="Вы действительно хотите удалить элемент?" style="color:red; font-size: 18px;">X</a>';
			 print '</td>';
		}
		else
		{
			print '<td></td>';
			 print '
			 <td>
			 <a href="ajax_router.php?ctr='.$this->ctr.'&act=del2&id='.$result[$this->key_filed].'" class="fw_ajaxlink" data-actionhide="1" data-reloadall="1"  data-id="'.$result[$this->key_filed].'" data-confirm="Вы действительно хотите удалить элемент без возможности восстановления?" style="color:red; font-size: 18px;">X</a> 
			 &nbsp;
			 <a href="ajax_router.php?ctr='.$this->ctr.'&act=recovery&id='.$result[$this->key_filed].'" class="fw_ajaxlink" data-reloadall="1" data-id="'.$result[$this->key_filed].'" data-confirm="Вы действительно хотите восстановить элемент?" style="color:GREEN; font-size: 18px;">R</a> 
			 </td>';
		}
	}
	
	
	
	
	
	
	
	
 
 



 
	 
	
	
	// Отображение столбов
	
 
	function display_table__edit($row)
	{
		return $link = '
		<a href="?ctr='.$this->ctr.'&act=edit&id='.$row[$this->key_filed].'" class="table-edit"> </a> 
		&nbsp;
		&nbsp;
		<a href="?ctr='.$this->ctr.'&act=del&id='.$row[$this->key_filed].'" style="color:red;  font-size: 21px;"> X </a>
		';
	}
 
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
	
	// Контент раскрывающейся строки
	function display_hr_content($v)
	{
		print '<div style="width:100%; max-width:100vw; text-align:center; padding:10px; " class="loader"  ><img src="loader.gif" height=50/></div>';
	}
	
	// Метод генерирует ссылку аякс для контента раскрывающейся строки
	function display_hr_ajax($v)
	{
		return '/sahmatka/ajax_router.php?ctr=rentbroni&act=broni_history&id='.$v['rent_objects_id'];
	}
	
	
	
	
	 
	function ajcrud_filtr()
	{
		?>
		<div class="filter-item"> 
		<?	$this->filtr_select('Здание','rent_home_id','adress');	?>
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
	
	
	
	function act__index()
	{
		global $r;
		global $mysql;
		
		global $t;
		$t['h1'] = 'Брони аренды';;
		$this->display_ajax_crud();
	}
	
	
	
	
	
	
	
	
	 
	
	
	
	
	######################################################################### ЗАпоминание заполнения форм 
	function session_form_save()
	{
		 // Если передан ид формы пишем значения в сессию
		 if($_REQUEST['formid'])
		 {
			 $formid = $_REQUEST['formid'];
			 if(!$r){ if( $_POST ){ $r='post'; } else{ $r='get'; }	}
			 unset($_SESSION['sforms'][$r][$formid]);
			 
			 if(!$_SESSION['sforms'][$r][$formid]['generation_time']){ $_SESSION['sforms'][$r][$formid]['generation_time'] = time();  } // Время генерации формы
				 
				 
			 // print_r($_REQUEST);
			 foreach($_GET as $k=>$v )
			 {
				// $_SESSION['sforms']['get'][$formid][$k]=$v;
			 }
			 foreach($_POST as $k=>$v )
			 {
				 if($v )
				 { 
					$_SESSION['sforms']['post'][$formid][$k]=$v;
				 }
				//  else{unset($_SESSION['sforms']['post'][$formid][$k]);}
				//$_SESSION['sforms']['post'][$formid]['dir']='2';
				
				
			 }
			 foreach($_REQUEST as $k=>$v )
			 {
				 if($v)
				 { 
					$_SESSION['sforms']['request'][$formid][$k]=$v;
				 }
			 }
			 
		 }
		 
	}
		// Генерируем ид формы
	function formid($id)
	{
		// Директория Ctr и act остальные гет переменые убрать
		// $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
		return   md5($_GET['ctr'].'-'.$_GET['act']  .'-'.$id);
	}
	
	// ПИшем значение формы в сесссию
	function form_sval($form_id,$name,$value,$r='')
	{
	}
	
	// Получаем значение поля формы из сессии
	function get_form_sval( $form_id  ,$name  , $r='' )
	{	
		if(!$r)	{ if( $_POST ){ $r='post'; } else{ $r='get'; }	}
 		return $_SESSION['sforms'][$r][$form_id][$name];  
	}
	
	
	// чекбукс
	function get_form_check( $form_id  ,$name  )
	{	
		if( $this->get_form_sval( $form_id  ,$name,'post' ) ){ return ' checked="checked" '; }
		else{print $form_id.'-'.$name;}
	}
	
	// select option
	function aj_select( $name, $val  )
	{	
	    $form_id = $_POST['formid'];
		if($val == $this->get_form_sval( $form_id  ,$name ) )
		{
			// print   $form_id.'-'. $this->get_form_sval( $form_id  ,$name );
			return ' selected="selected" ';
		}  
	}
	###########################################################################
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	function act__broni_history($space_id='',$ttitle=false)
	{
		if(!$space_id){$space_id = $_GET['id'];}
		if($_GET['nott']){$ttitle =false;}
		
		global $mysql;
		global $status_arr;
		global $status_color_arr;
	 
		$q = 'SELECT rent_objects.*,users.*,agency.caption as agcaption , rent_broni.status as b_status , rent_broni.date  , rent_broni.rent_broni_id ,rent_homes.*
		FROM rent_objects  
		LEFT JOIN rent_broni  ON rent_broni.rent_objects_id = rent_objects.rent_objects_id
		
		LEFT JOIN rent_homes  ON rent_homes.rent_home_id =rent_objects.rent_home_id
		 
		
		LEFT JOIN users ON users.id =rent_broni.user_id
		LEFT JOIN agency ON agency.agency_id = users.agency_id
		';
		if($space_id){ $q.=' WHERE rent_objects.rent_objects_id = "'.$space_id.'" '; }
		
		$q.=' ORDER BY rent_objects.rent_objects_id, rent_broni.date DESC';
	 
		$space_data = $mysql->get_arr($q );
		if(!$space_data[0]['rent_broni_id']){return;}
		// print '<pre>'; 
		// print_r($space_data );
		// print '</pre>';
		
		
		?>
		<table class="bronihtable">
		
		<?
		if($ttitle)
		{
		?>
		<tr>
			<td><b>Дата</b></td>
			<td><b>Агентство</b></td>
			<td><b>Пользовтель</b></td>
			<td><b>Статус</b></td>
		</tr>
		<?
		}
		?>
		<tbody>
		<?
		$i=0;
		foreach($space_data as $k => $v )
		{
			$i_ps=0;
			if(!$v['rent_broni_id']){continue;}
						 
			if($parking_space_id!=$v['parking_space_id'] && !$space_id)
			{
				?>
				<tr><td colspan="10" align="center">
				<?
				  if($v['rent_broni_id'] != $v['status_broni_id'] && $i_ps==0 )
				  {
					  print $v['adress_disp'].' - '.$v['num'];
					  print '<h1>Ошибка</h1>';
					  
					  if($v['b_status'] == $v['status'])
					  {
						 print '';
					  }
					  else
					  {
						   print 'Нельзя исправить   ';
					  }
				  }
				 else
				 {
					 print $v['adress_disp'].' - '.$v['num'];
					// print $v['status_broni_id'];
				 }
				
				?>
				</td></tr><?
				$parking_space_id = $v['parking_space_id'];
			}
			else // То же место следующая бронь
			{
				$i_ps++;
			}
			
			if($v['rent_broni_id'] == $v['status_broni_id']) {	 $style="font-weight:bold;";	$tb_text='(Текущий статус)'; } else{$style=""; $tb_text='';}
			?>
			<tr>
			<td style="<?=$style?>"><?=fromsql_date($v['date'])?></td>
			<td style="<?=$style?>"><?=$v['agcaption']?></td>
			<td style="<?=$style?>"><?=$v['login']?> (<?=$v['name']?>)</td>
			<td  style="<?=$style?>"><span style="background-color:<?=$status_color_arr[$v['b_status']]?>;" > <?=$status_arr[$v['b_status']]?> <?=$tb_text?></span></td>
			</tr>
			<?
			$i++;
		}
		?>
		</tbody>
		</table>
	 
		<?
		
	}
	
}