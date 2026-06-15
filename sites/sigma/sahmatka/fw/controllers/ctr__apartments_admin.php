<?
class ctr__apartments_admin extends ctr__
{  
 
	var $table = 'apartments'; //Главная таблица
	var $key_filed = 'apartament_id'; // Ключевое поле главной таблицы
	var $ctr = 'apartments_admin';
    var $title;
   
	function __construct()
	{
		$this->title = unit_label_cap('pl_nom');
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
		
		
		
		$titles['htitle'] = 'Дом';
		$titles['apartment_num'] = unit_label_cap('nom'); 
		
		$titles['date'] = 'Дата';
		$titles['ag_caption'] = 'Агентство';
		$titles['login'] = 'Логин';
		
		$titles['price'] = 'Цена  ';
		$titles['bprice'] = 'Цена брони';
		
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
		
		
		
		//$filtr_data['home_id'] = 47;
		//$filtr_data['apartment_num'] = 1;
		
		
		global $mysql;
		$q = '
 
		SELECT `apartaments`.* , `broni`.date, `broni`.`price` as `bprice`,`broni`.`broni_id`  , homes.title as htitle, users.login, users.name, agency.caption as ag_caption   , apartaments.apartament_id
		
		
		FROM `apartaments`
		LEFT JOIN `broni` ON `broni`.`broni_id` = `apartaments`.`status_broni_id` 
		LEFT JOIN `homes` ON `homes`.`home_id` = `apartaments`.`home_id` 
		LEFT JOIN `users` ON `users`.`id` =`broni`.`user_id`
		LEFT JOIN `agency` ON `agency`.`agency_id` = `users`.`agency_id`

		WHERE 1=1
		AND homes.title IS NOT NULL
		AND ( homes.show ="1" OR  homes.show ="2" OR homes.show ="3" )
		';
		    
		  
		if( $filtr_data['home_id'] )
		{
			$q.=' AND apartaments.home_id = "'.$filtr_data['home_id'].'" '; // Только актуальные брони без истории
		}
		
		if( $filtr_data['apartment_num'] )
		{
		 	$q.=' AND apartaments.apartment_num = "'.$filtr_data['apartment_num'].'" '; // Только актуальные брони без истории
		}
		
		
		
		// $q.=' AND parking_spaces.status_broni_id = parking_broni.parking_broni_id '; // Только актуальные брони без истории
		
		//if( !$_GET['showdel'] ){	$q.=' AND `'.$this->table.'`.`del`="0" ';	}
 
		if( $filtr_data['parking_building_id'] )
		{
			//$q.=' AND parking_buildings.parking_building_id = "'.$filtr_data['parking_building_id'].'" '; // Только актуальные брони без истории
		}
		
		
		if( $filtr_data['status'] )
		{
			if($filtr_data['status']=="2")
			{
				$q.=' AND ( apartaments.status = "2" OR  apartaments.status = "")'; // Только актуальные брони без истории
			}
			else
			{
				$q.=' AND apartaments.status = "'.$filtr_data['status'].'" '; // Только актуальные брони без истории
			}
		}
		
		
		if( $filtr_data['agency_id'] )
		{
			//$q.=' AND agency.agency_id = "'.$filtr_data['agency_id'].'" '; 
		}
		if( $filtr_data['user_id'] )
		{
			//$q.=' AND users.id = "'.$filtr_data['user_id'].'" ';  
		}
		
		 $q.=' GROUP BY apartaments.apartament_id ';
		
		if( $filtr_data['order_filed'] )
		{
			//$q.=' ORDER BY '.$filtr_data['order_filed']; // Только актуальные брони без истории
			//if( $filtr_data['order_asc'] ) { $q.=' ASC '; } else { $q.=' DESC '; }
		}
		else
		{
			$q.=' ORDER BY homes.home_id  , apartaments.apartment_num, broni.broni_id '; 
		}
   
		 // if($_GET['id']){$q.=''}
		  //   print $q; print '<br><br>';
		return $q;
	}
	
	 
	
	
	
	
	
	
	function ajcrud_filtr()
	{
		?>
		<div class="filter-item"> 
			<? $this->filtr_select('Объект','home_id','htitle');	?>
			</div>	

			<div class="filter-item"  > 
			<?	
			 $this->filtr_select(unit_label_cap('nom'),'apartment_num','apartment_num');	
			?>
			</div>	
			
			 
			
			

			<div class="filter-item"  > 
			<?	
			//$this->filtr_select('Агентство','agency_id','caption');	
			?>
			</div>	
			 <div class="filter-item"  > 
			<?	
			//$this->filtr_select('Логин','user_id','login');	
			?>
			</div>	
			
			<div class="filter-item"> 
				<span class="input_title">Статус</span>
				<select name="status" class="input_edit" style="text-transform:none; height:auto;">
					<option value="" selected="selected">Все</option>
					 
					<option value="2">Свободна </option>
					<option value="3">Продана </option>
					<option value="4">Забронирована </option>
					<option value="5">Забронирована застройщиком </option>
					<option value="6"><?= unit_phrase('contractor') ?> </option>
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
		if( $v['login'] )
		{
			return '<b>'.$v['login'].'</b>' . ' ('.$v['name'].')';
		}
		else
		{
			return ' - ';
		}
		
	}
	function display_table__date($v)
	{
		if($v['date'])
		{
			return fromsql_date($v['date']);
		}
		else
		{
			return '-';
		}
	}
	function display_table__status($v)
	{
		global $status_arr;
		global $status_color_arr;
		
		if( !$v['status']  ){$v['status']=2;}
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

 
 
	function display_table__price($row)
	{
		 global $mysql;
		 
		 
		// $arr = $mysql->get_arr('SELECT * FROM apartaments WHERE apartaments.apartament_id="'.$row['apartament_id'].'" ',1);
		 
		 
		 /*
		 ob_start();
		 if( $row['broni_id'] )
		 {
			 print  $row['broni_id'];
			 print ' - ';
			 print $row['price'];
			 $datax=array();
			 $datax['price']=$row['price'];
			  //$mysql->update_for_key('broni','broni_id',$row['broni_id'],$datax);
		 }
		 return ob_get_clean();
		 */
		 return $row['price'];
	}
	
	
 
 
	function act__exrow_broni()
	{
		print '!!! - !!! - !!! - !!!';
	}
 
 
 
	// Метод генерирует ссылку аякс для контента раскрывающейся строки
	function display_hr_ajax($v)
	{
		 return '/sahmatka/ajax_router.php?ctr=apartments_broni&act=exrow_broni&id='.$v['apartament_id'];
	}

	function act__index()
	{
		global $t;
		$t['h1'] = unit_label_cap('pl_nom');
 
		$this->display_ajax_crud();
	}
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	function act__broni_history($space_id='',$ttitle=false)
	{
		if(!$space_id){$space_id = $_GET['id'];}
		if($_GET['nott']){$ttitle =false;}
		
		global $mysql;
		global $status_arr;
		global $status_color_arr;
		
		$q = 'SELECT parking_spaces.*,users.*,agency.caption as agcaption , parking_broni.status as b_status , parking_broni.date  , parking_broni.parking_broni_id ,parking_buildings.*
		FROM parking_spaces  
		LEFT JOIN parking_broni  ON parking_broni.parking_space_id =parking_spaces.parking_space_id
		
		LEFT JOIN parking_buildings  ON parking_buildings.parking_building_id =parking_spaces.parking_building_id
		 
		
		LEFT JOIN users ON users.id =parking_broni.user_id
		LEFT JOIN agency ON agency.agency_id = users.agency_id
		';
		if($space_id){ $q.=' WHERE parking_spaces.parking_space_id = "'.$space_id.'" '; }
		
		$q.=' ORDER BY parking_spaces.parking_space_id, parking_broni.date DESC';
		
		$space_data = $mysql->get_arr($q );
		if(!$space_data[0]['parking_broni_id']){return;}
		// print '<pre>'; 
		//  print_r($space_data );
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
			if(!$v['parking_broni_id']){continue;}
						 
			if($parking_space_id!=$v['parking_space_id'] && !$space_id)
			{
				?>
				<tr><td colspan="10" align="center">
				<?
				  if($v['parking_broni_id'] != $v['status_broni_id'] && $i_ps==0 )
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
			
			if($v['parking_broni_id'] == $v['status_broni_id']) {	 $style="font-weight:bold;";	$tb_text='(Текущий статус)'; } else{$style=""; $tb_text='';}
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