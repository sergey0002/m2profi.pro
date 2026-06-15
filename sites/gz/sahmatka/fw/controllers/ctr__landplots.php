<?
/*
1. КЛИКАБЕЛЬНЫМИ только участки в продаже!
(остальные серым цветом)

2. При закрытии фенсибукс обновлять JSOON? или одну квартиру

3. Перетаскивание не клике
4. Запоминание зума и позицию скрола?!

5. Миниатюра карты при клике на учаток
- Грзим карту с центром на участке + толко он выделен

*/
class ctr__landplots extends ctr__
{
	var $table = 'landplots'; //Главная таблица
	var $key_filed = 'lp_id'; // Ключевое поле главной таблицы
	var $ctr = 'landplots';
    var $title = 'Участки';
 
  
 
 
 
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
		
		
		if($_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem')
		{
			$titles['checkrow'] = '1';
		}
		// $titles[$this->key_filed] = 'id'; 1 019 118 000
		$titles['lp_id'] = 'ID'; 
		 
		 
	 
		$titles['num'] = 'Номер'; 
		$titles['area'] = 'Площадь';
		
		
		//$titles['htype'] = 'htype';
		//$titles['project_id'] = 'project_id';
		
	//	$titles['raion'] = 'Район';
		
		
		$titles['area_caption'] = 'Поселок';
		$titles['map_caption'] = 'Карта';
		//$titles['street'] = 'street';
			
		
		$titles['price'] = 'Цена';
		$titles['status'] = 'Статус';
		// 			raion
		
		$this->display_table_exrow=0; // раскрывать строки
		 
		
		$this->ajcrud_table_titles=$titles;
		
		// Не переносить по словам
		$nowrap=array();
		$nowrap['date']=1;
		$nowrap['num'] = 1;
		$this->ajcrud_table_nowrap=$nowrap;
		
		// Сортировать по столбцам
		$order=array();
		$order['num']='num';
	 
		$this->ajcrud_table_order=$order; 
 
	}
	
	
	
	
  
 // БАзовый запрос  menu
	function get_base_sql($filtr_data='')
	{
		// ПРиоритетно ставим гет переменные
		foreach($_GET as $k=>$v)
		{
			// $filtr_data[$k]=$v;
		}
		if(!$filtr_data){$filtr_data =  $_REQUEST;}
		
		global $mysql;
		$q = 'SELECT '.$this->table.'.*   , 
		landplots_maps.caption as map_caption,
		landplots_area.caption as area_caption 
		FROM  '.$this->table.'  
		
		
		LEFT JOIN landplots_maps ON landplots.map_id = landplots_maps.landplots_map_id 
		
		LEFT JOIN landplots_area ON  landplots_area.area_id = landplots_maps.area_id 
		';
		 
		 
		$q.='  WHERE 1=1 AND num >0';
		
		if(!$_GET['show_dell']){	$q.=' AND `'.$this->table.'`.`del`="0" ';	}
		else{$q.=' AND `'.$this->table.'`.`del`="1" ';}
		
		if($filtr_data['id']){	$q.=' AND `'.$this->table.'`.`'.$this->key_filed.'`="'.$filtr_data['id'].'" ';	}
		
		if( $filtr_data['price'] )
		{
			$q.=' AND landplots.price = "'.$filtr_data['price'].'" ';  
		}
		
		if( $filtr_data['area_id'] )
		{
			$q.=' AND landplots_area.area_id = "'.$filtr_data['area_id'].'" ';  
		}
		
		if( $filtr_data['map_id'] )
		{
			$q.=' AND landplots.map_id = "'.$filtr_data['map_id'].'" ';  
		}
		
		
		 
		
		
		
		if( $filtr_data['raion'] )
		{
			$q.=' AND landplots.raion = "'.$filtr_data['raion'].'" ';  
		}
		
		
		
		if( $filtr_data['numbers'] )
		{
			
			$buf = explode(',',$filtr_data['numbers']);
			$str_in = '';
			
			foreach($buf as $k=>$v)
			{
				if(!trim($v)){unset($buf[$k]);}
			}
			foreach($buf as $k=>$v)
			{
				
				$str_in.='"'.$v.'"';
				if($k<count($buf)-1){$str_in.=',';}
				
			}
			$q.=' AND landplots.num IN('.$str_in.') ';  
		}
		
		  
		
		if( $filtr_data['status'] )
		{
			if($filtr_data['status'] == 0 || $filtr_data['status'] == 2 )
			{
				$q.=' AND ( landplots.status = "0" OR   landplots.status = "2" ) ';  
			}
			else
			{
					$q.=' AND   landplots.status = "'.$filtr_data['status'].'" ';  
			}
		}
		
		
		
		if( $filtr_data['min_num'] )
		{  
			$q.=' AND CAST( landplots.num as UNSIGNED) > "'.$filtr_data['min_num'].'" ';  	 
		}
		
		if( $filtr_data['max_num'] )
		{
			$q.=' AND CAST( landplots.num as UNSIGNED) > "'.$filtr_data['max_num'].'" ';  
		}
		
		if( $filtr_data['order_filed'] )
		{
			$q.=' ORDER BY '.$filtr_data['order_filed']; // Только актуальные брони без истории
			if( $filtr_data['order_asc'] ) { $q.=' ASC '; } else { $q.=' DESC '; }
		}
		else
		{
				$q.=' ORDER BY CAST( landplots.num as SIGNED INTEGER) '; 
				$q.='  ASC ';  
			
		}
		
		
		
		// if($_GET['id']){$q.=''}
		 // print $q;
		return $q;
	}
	
	
	function ajcrud_filtr()
	{
		global $gl_raion;
		global $filed;
		global $mysql;
	
		$gl_area = $mysql->get_select_data('SELECT * FROM landplots_area','area_id','caption','все');
		$gl_map = $mysql->get_select_data('SELECT * FROM landplots_maps WHERE `landplots_maps`.`show`>"0" ','landplots_map_id','caption','все');
		?>
			 
		
		
		
		<?
		if($_SESSION['sh_login'] == 'admin' && 1==2 )  
		{
			?>
			<div class="filter-item"  > 
			<?	
			$this->filtr_select('Агентство','agency_id','caption');	
			?>
			</div>	
			<? 
		}
		?>
		
			<div class="filter-item" style="display:none;"> 
				 <?	 $filed->select('area_id', 'Поселок', $gl_area,   '', $style = 'text-transform:none; height:auto;'); ?>
			</div> 
			<div class="filter-item"  > 
				 <?	 $filed->select('map_id', 'Карта', $gl_map,   '', $style = 'text-transform:none; height:auto;'); ?>
			</div>
	
			<div class="filter-item"  style="display:none;"> 
				 <?	 $filed->select('raion', 'район', $gl_raion,   '', $style = 'text-transform:none; height:auto;'); ?>
			</div> 
			
			
			
			
			<?
		if($_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem'  )  
		{
			?>
			<div class="filter-item"  > 
			<span class="input_title">Номера через запятую</span>
			<input type="text"   name="numbers" class="input_edit" value="" placeholder="">	
			</div>		
			
			 
			<?
				}
		?>
		
		
		
			
		<?
		if($_SESSION['sh_login'] == 'admin' && 1==2 )  
		{
			?>
			<div class="filter-item"  > 
			<span class="input_title">Мин №</span>
			<input type="text"   name="min_num" class="input_edit" value="" placeholder="">	
			</div>		
			
			<div class="filter-item"  > 
			<span class="input_title">Max №  </span>
			<input type="text"   name="max_num" class="input_edit" value="" placeholder="">	
			</div>
			<?
				}
		?>
		
		
		
			<?
			
			if( $_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem')
			{
			?>
			<div class="filter-item"> 
				<span class="input_title">Статус</span>
				<select name="status" class="input_edit" style="text-transform:none; height:auto;">
					<option value="0" selected="selected">Не задан </option>
					<option value="2">Свободен</option>			
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
	
	
	
	// JSOON данные всех участков 
	function act__jsoondata()
	{
		global $mysql;
		$map_id = (int) $_GET['map_id'];
		
		if( $map_id ){$where = ' WHERE map_id="'.$map_id.'" ';}
		else{$where = '';}
		
		$arr = $mysql->get_arr('SELECT * FROM landplots '.$where.' ',1,'polygon_id');
		foreach($arr as $k=>$v) 
		{
			
			//$price = ($v['area']/100)*600000;
			
			
			$price = $v['price'];
			$price = number_format($price, 0, ' ', ' ');
			
			$v['adress'] = 'ул.Центральная д.28';
			$img_home = 'img/projects/1.png';
			
			$v['text'] = '';
			if($v['area']){ $v['text'].='Площадь участка <br/><b>'.$v['area'].'м<sup>2</sup></b>';}
			if($price){ $v['text'].='<br/> Стоимость участка <br/><b>'.$price.' </b>';}
			
			
			
			
			if($_SESSION['sh_login'] != 'admin' &&  $_SESSION['sh_login'] != 'goodzem' && $_SESSION['agency_id'] != "92")
			{
				if($v['status']==5 || $v['status']==6)
				{
					$arr[$k]['status']='3';
				}
				
			}
			if($v['status']==0  )
			{
				$arr[$k]['status']='2';
			}
				
			$broni_status_arr=array();
			$broni_status_arr[0]='Не задан';
			$broni_status_arr[2]='Свободен';
			$broni_status_arr[3]='Продан';
			$broni_status_arr[4]='Забронирован';
			$broni_status_arr[5]='Бронь Гудзем';
			$broni_status_arr[6]='Участок подрядчика';					
 
			$broni_status_color_arr=array();
			$broni_status_color_arr[0]='#8DFFA9';
			$broni_status_color_arr[2]='#8DFFA9';
			$broni_status_color_arr[3]='#FF8A90';
			$broni_status_color_arr[4]='#FEFF52';
			$broni_status_color_arr[5]='#87cefa';
			$broni_status_color_arr[6]='#991DFB';		
 
 //<br/> Площадь участка<br/><b>'.$v['area'].'м<sup>2</sup></b>
 //<span class="tt_text">'.$v['adress'].'</span>
 
 
			$arr[$k]['map_id']=$v['map_id']; // ИД ОБЕКТА (КАРТЫ)
 
 
			$arr[$k]['status_text']=$broni_status_arr[$arr[$k]['status']];
			$arr[$k]['status_color']=$broni_status_color_arr[$arr[$k]['status']];
			$arr[$k]['tooltip']='
			<span class="tt_title" style="display:block;"> <b>Участок № '.$v['num'].'</b></span>
			
			 
			<span class="tt_text" style="line-height:1.5em;"> '.$v['text'].'</span>
			';
			
			if($_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem')
			{
				$arr[$k]['class']='insale'; // В продаже! (для не даминов только участки свободные)
			}
			else
			{
				if($arr[$k]['status'] == 2)
				{
				$arr[$k]['class']='insale'; // В продаже! (для не даминов только участки свободные)
				}
				else
				{
					$arr[$k]['class']='noinsale'; // В продаже! (для не даминов только участки свободные)
				}
			}
		}
		print json_encode($arr);
	}
	
	### ДЕЙСТВИЯ КОНТРОЛЛЕРА
	
	
 
	
	

	function act__broni()
	{
		global $mysql;
		$id = $_GET['id'];
		if($id)
		{
			$filtr = array();
			$filtr['id'] = $id;
			$data = $mysql->get_arr($this->get_base_sql($filtr));
			$data=$data[0];
			 
		}
		else
		{
			print 'Не указан объект';
			return;
		}
		$this->tpl($data,'parking_spaces','form_broni_pub'); // ШАБЛОН ЗАПИСИ / БРОНИ для ПУБЛИЧНОГО ДОСТУПА БЕЗ ОБРАБОТКИ ЗАЯВОК!!!
		
		if($_POST)
		{
			 
			// Обработка формы
		}
  
	}
	
	
	function objects_menu()
	{
			 return;
		?>
 
 
			 <div style="width:100%; margin-bottom:10px;">
				<a href="user.php?action=<?=$action?>&sdan=0" class="mdef <? if(!$_GET['sdan']){ print 'mdefth';} ?>" style=" display:inline-block; padding-left: 12px; font-weight:bold; ">СТРОЯЩИЕСЯ</a> 
				<a href="user.php?action=<?=$action?>&sdan=1" class="mdef <? if($_GET['sdan']){ print 'mdefth';} ?>" style=" display:inline-block; padding-left: 10px; font-weight:bold; ">СДАННЫЕ</a>
			 </div>
			 
			 <div style="padding-right:0; padding-left:0; min-height:auto; margin-bottom: 0;" class="page-header"    >
			
			 <br/>
			 <ul class="mmenu">
		<?
		
		//object_menux($action);
		$class=' class="mdef" ';
		if( $_GET['action'] == 'objects2' )
		{
			$class='  class="mdef mdefth " ';  
		}
		?>
		
		<?
		if(($_SESSION['agency_id'] == "92" || $_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem' || $_SESSION['sh_login'] == 'fd'  ) ||1==1)
		{
			if($_GET['sdan'])
			{
				
				foreach($GLOBALS['custom_apparts_all'] as $k=>$v)
				{
					?>
					<li style="padding:0; "><a href="/sahmatka/form_order_custom.php?custom_home_id=<?=$v['home']?>&custom_appart_id=<?=$v['custom_appart_id']?>" class="mdef m2catalog_item_order iframe"><?=$v['homecaption']?></a></li>
					<?
				}
				 
			?>
			 <li style="display:none; padding:0; "><a href="user.php?action=objects2&sdan=<?=$_GET['sdan']?>" <?=$class?>>Другие</a></li>
			
			<?
			}
		}
		?>
			</ul>
		 
 
		 
		 	          <form id="obj_nav_form" method="GET" action="user.php" class="mobilenav" name="autosubmit_select"  >
						<div class="objects-head-nav__select"  >
						 
							<select  name="url" onChange="document.autosubmit_select.submit();" style="width:100%;  text-align: left; border-radius:0; ">
							<?
								?><option>Выбрать дом</option><?
							foreach($h_arr as $k=>$v)
							{
								if(isset($_GET['sdan']))
								{
									if($_GET['sdan']){if($v['complite']=="0"){continue;}}
									else{if($v['complite']=="1"){continue;}}
								}
								?><option value="/sahmatka/user.php?action=objects&home=<?=$v['home_id']?>&sdan=<?=$_GET['sdan']?>" <? if($v['home_id']==$_GET['home']){ print ' selected="selected" ';}?>><?=$v['long_title']?></option><?
							}
							?>
							<?
							if($_SESSION['agency_id'] == "92" || $_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem')
							{
								if(isset($_GET['sdan']))
								{
								?>
							 
								<option value="/sahmatka/form_order_custom.php?custom_home_id=101&custom_appart_id=1">Свечникова, 4/1</option>
								<?
								}
							}
							?>
							</select>
							
						</div>
					 	</form>
 		
		</div>			
		
		<hr style="margin-top: 12px; " class="nomobile"/>
		
	
						
						
						
		<?
	}
	
	 
	
	
	
	### ФОрма брони для админов и агентств
	function act__order()
	{
		
		global $mysql;
		global $t;
		
		$map_id = (int) $_GET['map_id'];
		if( !$map_id ){ $map_id =1; }
		if( $map_id ){$where = ' AND map_id="'.$map_id.'" ';}
		else{$where = '';}
		
		
		$t['h1']='Бронирование участка';
		
		$pid = (int) $_GET['polygon_id'];
		$data = $mysql->get_arr('SELECT * FROM `landplots` WHERE `polygon_id`="'.$pid.'" '.$where.' ',1);
		
		
		// ОБНОВЛЯТЬ ТОЛЬКО ПО НЕМУ!!!!!!!
 
		$idx = $data['lp_id']; 
		
		print '<pre>';
		//print_r($_GET);
		
		if(!$data)
		{
			//	print 'Новый участок'; 
		
		}
		else
		{
			//print 'Редактирование участка';
			// print_r($data);
		}
		print '</pre>';
		 
		 
		 
		 
		 
		
		if($_POST)
		{
			if($_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] =='em_nsv' ||  $_SESSION['sh_login'] == 'goodzem' ) // Админ агентства или агент
			{
 
				// print 'Обработка админской формы';

				$datax = array();
				
				 $datax['map_id']  = $map_id ;
				
				
				if( $_POST['price'] ) { $datax['price'] = $_POST['price']; }
				if( $_POST['area'] ) { $datax['area'] = $_POST['area']; }
				if( $_POST['street'] ) { $datax['street'] = $_POST['street']; }
				if( $_POST['raion'] ) { $datax['raion'] = $_POST['raion']; }
				if( $_POST['htype'] ) { $datax['htype'] = $_POST['htype']; }
				
				if( $_POST['kadastrnum'] ) { $datax['kadastrnum'] = $_POST['kadastrnum']; }
				
				
				// $datax['status'] = $_POST['status']; // Статус меняется спец методом add_broni()
				$datax['num'] = $_POST['num'];
				
				//$datax['del'] = $_POST['del'];
				//if(!$datax['del']){$datax['del']=0;}
				 
				//print_r($datax);
				if($data)
				{
					$datax['polygon_id'] = $pid;
					$mysql->update_for_key('landplots','lp_id',$idx ,$datax,0);
				 	$idx = $data['lp_id']; 
					print 'Данные обновлены';
				}
				else
				{
					$datax['polygon_id'] = $pid;
					$idx = $mysql->insert('landplots',$datax);
					print 'Данные добавленны';
				}
				
				 
				if( $_POST['status'] ) 
				{
				
					$broni_idx = $this->add_broni($idx,$_POST['status']);
					$datax['status_broni_id'] = $broni_idx; 
				}
				
				// Повторно получаем обновленные данные
				$data = $mysql->get_arr('SELECT * FROM `landplots` WHERE `polygon_id`="'.$pid.'" '.$where.' ',1);
				
				
				// 
				//$data = $this->get_id_arr($_GET['id']);
				$this->tpl($data,'landplots','form_broni_ag'); // ШАБЛОН ЗАПИСИ / БРОНИ для ПУБЛИЧНОГО ДОСТУПА БЕЗ ОБРАБОТКИ ЗАЯВОК!!!
			}
			elseif( $_SESSION['sh_login'] )
			{
				// Обработка формы бронирования
				//print '<pre>';
				//print_r($_GET);
				//print_r($_POST);
				//print_r($data);
				//print '</pre>';
				 
				$this->add_broni_pf($data);
			}
			else
			{
				print 'ДОступ запрещен';
				return;
			}
		}
		else
		{
			//$data = $this->get_id_arr($_GET['id']);
			// print_r($_GET);
			if( $_SESSION['sh_login'] && $_GET['a'] == '/sites/gl/sahmatka/ctrind.php' ) 
			{
				$this->tpl($data,'landplots','form_broni_ag'); // ШАБЛОН ЗАПИСИ / БРОНИ для ПУБЛИЧНОГО ДОСТУПА БЕЗ ОБРАБОТКИ ЗАЯВОК!!!
			}
			else
			{
				$this->tpl($data,'landplots','form_broni_pub'); // ШАБЛОН ЗАПИСИ / БРОНИ для ПУБЛИЧНОГО ДОСТУПА БЕЗ ОБРАБОТКИ ЗАЯВОК!!!
			}
		}
		
		// История броней
		if( $idx && ($_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem') && $_GET['a'] == '/sites/gl/sahmatka/ctrind.php' )
		{ 
			$this->act__broni_history($idx);
		}
		
	}
	
	
	
	// Пост обработка публичной формы
	function act__order_pub()
	{
		global $mysql;
		global $t;
		$pid = (int) $_GET['polygon_id'];
		$data = $mysql->get_arr('SELECT * FROM `landplots` WHERE `polygon_id`="'.$pid.'" ',1);
		
		$idx = $data['lp_id']; 
		$this->tpl($data,'landplots','form_broni_pub'); // ШАБЛОН ЗАПИСИ / БРОНИ для ПУБЛИЧНОГО ДОСТУПА БЕЗ ОБРАБОТКИ ЗАЯВОК!!!
		
		print '<pre>';
			//print_r($data);
			//print_r($_POST);
			//print_r($_GET);
		print '</pre>';
	}
	
	
	
	// Добавление брони в базу
	function add_broni($space_id,$status)
	{ 
		global $mysql;
		
		$data_space = $mysql->get_for_key('landplots','lp_id',$space_id,1);
		 
		// print_r($data_space);
		
		// Проверяем если не изменился пользователь и статус
		if($data_space['status'] != $status)
		{
			// Записываем бронь
			$data = array();
			$data['lp_id'] = $space_id;
			$data['status'] = $status;
			$data['date'] = date('Y-m-d H:i:s',time()); // текущая дата
			$data['date_first'] = date('Y-m-d H:i:s',time()); // текущая дата
			$data['date_fu'] = date('Y-m-d H:i:s',time()); // текущая дата
			$data['broni_up_counter'] = 0; // текущая дата
			$data['comment'] = ''; // текущая дата
			$data['user_id'] = $_SESSION['sh_id'];
			$broni_id = $mysql->insert('landplots_broni',$data);
			
			// Обновляем статус в основной таблице
			$data = array();
			$data['status'] = $status;
			$data['status_broni_id'] = $broni_id;
	 
			$mysql -> update_for_key( 'landplots', 'lp_id', $space_id , $data );
		}
		else{$broni_id = $data_space['status_broni_id'];}
		return $broni_id;
	}
	
	
	
	
	
	function act__broni_history($space_id='',$ttitle=false)
	{
		if(!$space_id){$space_id = $_GET['id'];}
		if($_GET['nott']){$ttitle =false;}
		
		global $mysql;
		global $status_arr;
		global $status_color_arr;
	   
		$q = 'SELECT landplots.*,users.*,agency.caption as agcaption , landplots_broni.status as b_status , landplots_broni.date  , landplots_broni.lp_broni_id 
		FROM landplots  
		LEFT JOIN landplots_broni  ON landplots_broni.lp_id =landplots.lp_id
		 
		LEFT JOIN users ON users.id =landplots_broni.user_id
		LEFT JOIN agency ON agency.agency_id = users.agency_id
		';
		if($space_id){ $q.=' WHERE landplots.lp_id = "'.$space_id.'" '; }
		
		$q.=' ORDER BY landplots.lp_id, landplots_broni.date DESC';
		
		$space_data = $mysql->get_arr($q );
		if(!$space_data[0]['lp_broni_id']){return;}
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
			if(!$v['lp_broni_id']){continue;}
						 
			if($lp_id!=$v['lp_id'] && !$space_id)
			{
				?>
				<tr><td colspan="10" align="center">
				<?
				  if($v['lp_broni_id'] != $v['status_broni_id'] && $i_ps==0 )
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
				$lp_id = $v['lp_id'];
			}
			else // То же место следующая бронь
			{
				$i_ps++;
			}
			
			if($v['lp_broni_id'] == $v['status_broni_id']) {	 $style="font-weight:bold;";	$tb_text='(Текущий статус)'; } else{$style=""; $tb_text='';}
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
	
	
	
 
	
	
	
	// Обработка формы
	function add_broni_pf($data='')
	{
		global $mysql;
 
		// Получаем данные места
		$space_data = $data;
		  
		$space_id = $data['lp_id'];
		if(!$space_id){print 'Не указан id '; return;}
		
		
		// print_r($space_data);
		
		// Проверяем текущий статус помещения  
		if( $space_data['status']  && $space_data['status']!='2' && 1==2) 
		{
			//	if($stat!='' && $stat!='2' && $stat!='5' && $stat!='0' ){print '<h2 style="color:red">Ошибка бронирования квартира уже забронирована другим пользователем</h2>';  $err_m[]='Квартира уже забронирована другим пользователем';}
		
			print 'Ошибка - конфликт статуса бронирования - вероятно место было забронированно другим пользователем, пока вы заполняли форму';
			return;
		}
		elseif($space_data['lp_id'])
		{
			//
			
			if( !$_FILES['passport_scan']['type'] || !$_FILES['passport_scan2']['type'] || !$_FILES['anket']['type'] )
			{
				?><h2 style="color:red">Для бронирования необходимо загрузить указанные файлы</h2><?
			}
			else
			{
				//print 'Применение брони успешно';
				//print ' ID Брони: '; 
				$new_broni_id =  $this->add_broni($space_id,4);
				
				$dir = "uploads_landplots/$new_broni_id/";
				mkdir($dir, 0777);
				 
				 
				if($_FILES['passport_scan']['type'])
				{
						$ext =  substr(strrchr(basename($_FILES['passport_scan']['name']), '.'), 1);
						$uploadfile = $dir . basename('passport_scan'.'.'.$ext);
						$files[0] = 	$uploadfile; // для письма
						if (move_uploaded_file($_FILES['passport_scan']['tmp_name'], $uploadfile))
						{
							// echo "Скан паспорта 1 - Файл был успешно загружен.\n <br>";
						} 
						else 
						{
							echo "Ошибка!\n";
							$err_m[]='Скан паспорта не был загружен';
						}
				}

				if($_FILES['passport_scan2']['type'])
				{
						$ext =  substr(strrchr(basename($_FILES['passport_scan2']['name']), '.'), 1);
						$uploadfile = $dir . basename('passport_scan2'.'.'.$ext);
						$files[1] = 	$uploadfile; // для письма
						if (move_uploaded_file($_FILES['passport_scan2']['tmp_name'], $uploadfile))
						{
							// echo "Скан паспорта 2 - Файл был успешно загружен.\n<br>";
						} 
						else 
						{
							echo "Ошибка!\n";
							$err_m[]='Скан паспорта 2 не был загружен';
						}
				}

				if($_FILES['anket']['type'])
				{
						$ext =  substr(strrchr(basename($_FILES['anket']['name']), '.'), 1);
						$uploadfile = $dir . basename('anket'.'.'.$ext);
						 $files[2] = 	$uploadfile; // для письма
						if (move_uploaded_file($_FILES['anket']['tmp_name'], $uploadfile))
						{
							echo "Анкета- Файл был успешно загружен.\n<br>";
						} 
						else 
						{
							echo "Ошибка!\n";
							$err_m[]='Анкета-  не был загружен';
						}
				}
				
				if($_POST && !$err_m)
				{
					?>
					<h2 style="color:#000; text-align:center;">Участок забронирован</h2>
					<p style="color:#00CDAD; font-weight:bold;; font-size:20px; text-align:center;">Срок действия брони - 10 календарных дней, по прошествии 10 дней бронь будет анулирована автоматически</h2>
					<hr/>
					<?
					
					
						$message = "Бронирование участка №".$data['num']."\r\n <br/>";
						$message .= "Заявка поступила от пользователя - <b>".$_SESSION['sh_name'].'</b> Представителя агентства - <b>'.$_SESSION['ucaption']."</b>\r\n </b><br/> ";
					 		
						//  XMail('89236470002@mail.ru', 'Бронирование квартиры '.$homes[$home_id]['caption']. '/сек-'.$section_id.'/этаж-'.$floor.'/кв'.$_GET['num'], $message, $files);
						# XMail( 'site@em-nsk.ru', 'em-opd@mail.ru', 'Бронирование квартиры '.$homes[$home_id]['caption']. '/сек-'.$section_id.'/этаж-'.$floor.'/кв'.$_GET['num'], $message, $files);

				 
						 
				 
						include_once('SendMailSmtpClass11.php');
 	
						 $mailSMTP = new SendMailSmtpClass('gl_order@mail.ru', 'T2jpnmqSyxbpb8C7MtiU', 'ssl://smtp.mail.ru',465,"UTF-8"); // создаем экземпляр класса
						// от кого
						$from = array(
							"M2 Green Launge", // Имя отправителя
							"gl_order@mail.ru" // почта отправителя
						);

						// кому отправка. Можно указывать несколько получателей через запятую
						$to = 'info@g-lounge.ru,   89236470002@mail.ru';
						// $to = '89236470002@mail.ru';
						// добавляем файлы
						$mailSMTP->addFile($files[0]);
						$mailSMTP->addFile($files[1]);
						$mailSMTP->addFile($files[2]);
						
						// отправляем письмо
						  $result =  $mailSMTP->send($to,  'Бронирование участка - №'.$data['num'], $message, $from); 
						 if($result === true){	echo "Done";	}
						 else{echo "Error: " . $result;	}
					 
				}
				
			}
			
			
			//
		}
		else
		{
			print 'Не корректный id';
			return ;
		}
		
		return;
		
  
	
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	/*
	1. Выгрузить карту в отдельные файлы
	2. Режим редактора
	- при клике на участке ajax грузится данные в окошко 
	поля номер, площадь, цена, проект дома (выбрать)
	статус - впродаже/не в продаже и как в эм, тек которые не в продаже серым закрывать 
	Окошко позиционируется абсолютно внутри карты 

	+ Редактор проектов домов


+ если не режим редактирования форма брони аякс в таком же окошке 
+ всплывающие подсказки !!! к участкам как к домам

+ копировать базу ЭМ
- подключить статистику , заявки с сайта, 
	
	*/
	
	function act__index()
	{
		
		
		$this->act__area();
		return;
		
		
		global $t;
		$t['h1'] = 'Участки';
		$this->tpl('','landplots','map_css'); // Легенда со статусами
		?>
		 
		<script src="https://gl.m2profi.pro/e-smart-zoom-jquery.js"  ></script>
		<div id="zoomcontainer" class="noselect dragscroll" style="position:relative; max-width:100%; overflow:scroll; "  >
			<div id="slide">			  
				<div class="scheme" style="width:100%;" >
					<img src="https://gl.m2profi.pro/sahmatka/landplots/mbg.svg" width="100%" alt=""> 
					<? print file_get_contents('https://gl.m2profi.pro/sahmatka/landplots/polygons2.svg'); ?>
				</div>
			</div>
		</div>

		<div>
		<ul class="objects-head-status-list" style="text-align: right; margin-bottom:30px; margin-top:30px;">
			<li class="objects-head-status__green" style="display:inline-block; margin-right: 20px;">Свободен</li>
			<li class="objects-head-status__yellow" style="display:inline-block; margin-right: 20px;">Забронирован</li>
			
			
			<li class="objects-head-status__red" style="display:inline-block; margin-right: 20px; ">Продан</li>
			<?
			//print_r($_SESSION);
			if($_SESSION['sh_login']=='admin' ||  $_SESSION['sh_login'] == 'goodzem')
			{
				?>
					<li class="objects-head-status__grey" style="display:inline-block; margin-right: 20px;">Бронь Гудзем</li>
					<li class="objects-head-status__blue" style="display:inline-block; margin-right: 20px;">Забронирован подрядчиком</li>	
				<?
			}
			?>
		</ul>
		</div>
		<?
		 
		$this->tpl('','landplots','map_js');  
	 
	}
	
	
	
	
	
	
	
	function act__index_pub()
	{
		global $t;
		$t['h1'] = 'Участки';
		//$this->tpl('','landplots','map_css'); // Легенда со статусами
		?>
	
		<div id="zoomcontainer" class="noselect dragscroll" style="position:relative; max-width:100%; overflow:hidden; "  >
			<div id="slide">			  
				<div class="scheme" style="width:100%; " >
					<img src="https://gl.m2profi.pro/sahmatka/landplots/bgx2.png" width="100%" alt=""> 
					<? print file_get_contents('https://gl.m2profi.pro/sahmatka/landplots/polygons2.svg'); ?>
				</div>
			</div>
		</div>
		<?
		//$this->tpl('','landplots','map_js');  
		?>
		 
		
		<?
	}
	
	
	
 
 function object_menu()
 {
	 global $mysql;
	 
	 $landplots_area = $mysql->get_arr('SELECT * FROM landplots_area ');
	 ?>
	 

<style>
.mdef{ padding:5px; padding-left:5px; padding-right:5px; font-weight:bold; font-size:18px; font-weight:bold; font-size:18px;}	


.objmenua .mdef{color:#000;  }
  .mdefa{color:#FFA500;} /* ТОлько админам */
.mdefaop{color:#999999;} /*  Админам и отделу продаж */


.mdefth{color:#FFF; background-color:#00CDAD;  }			 
.mdef:hover{color:#FFF; background-color:#00CDAD;}					
						
 
@media screen and (min-width: 1000px) {
  .mmenu{ display:block;	padding-right:0;  margin-top:15px;    display: flex;    flex-direction: row;    justify-content: space-between;		width: 100%;}
  .mobilenav{display:none;}
}
@media screen and (max-width: 1000px) {
  .mmenu{	display:none;		}
  .mobilenav{display:block; width:100%;}
  .nomobile{display:none;}
}
</style>


	 <div style="padding-right:0; padding-left:0; min-height:auto; margin-bottom: 0;" class="page-header">
			
			 <br>
			 <ul class="mmenu">
					<?
					foreach($landplots_area as $k=>$v)
					{
						$class ='';
						if($_GET['area_id']==$v['area_id']){ $class = 'mdefth'; }
						
						if($v['show']==1)
						{
							?>
								<li style="padding:0;"><a href="/sahmatka/ctrind.php?ctr=landplots&act=area&area_id=<?=$v['area_id']?>" class="mdef <?=$class?>"><?=$v['caption']?></a> </li>
							<?
						}
						elseif( ( $v['show']==3 || $v['show']==2 ) && ($_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem') )
						{					
							?>
								<li style="padding:0;"><a href="/sahmatka/ctrind.php?ctr=landplots&act=area&area_id=<?=$v['area_id']?>" class="mdef mdefa <?=$class?>"><?=$v['caption']?></a> </li>
							<?
						}
					}
					?>
				</ul>
	 
			  <form id="obj_nav_form" method="GET" action="user.php" class="mobilenav" name="autosubmit_select">
				<div class="objects-head-nav__select">
					<select name="url" onchange="document.autosubmit_select.submit();" style="width:100%;  text-align: left; border-radius:0; ">
					<option>Выбрать поселок</option>
					<?
					foreach($landplots_area as $k=>$v)
					{
						if($v['show']==1)
						{
							?>
								<option value="/sahmatka/ctrind.php?ctr=landplots&act=area&area_id=<?=$v['area_id']?>"><?=$v['caption']?></option>
							<?
						}
						elseif( ( $v['show']==3 || $v['show']==2 ) && ($_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem') )
						{					
							?>
								<option value="/sahmatka/ctrind.php?ctr=landplots&act=area&area_id=<?=$v['area_id']?>"><?=$v['caption']?></option>
							<?
						}
					}
					?>
				</select>	
				</div>
			</form>
 		
		</div>
		<hr style="margin-top: 12px; " class="nomobile">
		<?
 }
 
 
 
 
 
 
 function act__jqsvg()
 {
	 $map_id = $_GET['map_id'];
	 $svg_path =  'https://gl.m2profi.pro/maps/'.$map_id.'/map.svg';
	 print file_get_contents( $svg_path  );
 }
	
	function act__map($map_id=false)
	{
		
		 //$this->objects_menu();
		global $t;
		global $mysql;
		
		if(!$map_id){ $map_id = (int) $_GET['map_id']; }
		$arr = $mysql->get_arr('SELECT * FROM  landplots_maps WHERE  landplots_map_id="'.$map_id.'"');
		$map_id = $arr[0]['landplots_map_id'];
		
 		//print_r($arr);
		$t['h1'] = 'Участки';
	   
	  $svg_path =  'https://gl.m2profi.pro/maps/'.$map_id.'/map.svg';
	  $bg_path =  'https://gl.m2profi.pro/maps/'.$map_id.'/map.png';
		?>
		 
 
		 
<div id="map__<?=$map_id?>" class="noselect" style="position:relative">
	<div style="position: absolute;    left: 0;    top: 0;    z-index: 3; display:none;">
		<div class=" ">
			<button class="zmb" data-zoom-down  style="width: auto;">-</button> 
			 
			<button class="zmb" data-zoom-up  style="width: auto;" >+</button>
		</div>
	</div>
	<div class="ratio ratio-4x3 " style="overflow:hidden">
		<div id="myViewport" class="myViewport">
			<div class="myContent" id="mapcontent__<?=$map_id?>">
				<div class="scheme"  style="position:absolute;  width:100%; left:0; ">
				<? print file_get_contents( $svg_path ); ?>
				</div>
				<img class="mapbg"   src="<?=$bg_path?>"   alt="">
			</div>
		</div>		
	</div>
</div>

 

		<div>
		<ul class="objects-head-status-list" style="text-align: right; margin-bottom:30px; margin-top:30px;">
			<li class="objects-head-status__green" style="display:inline-block; margin-right: 20px;">Свободен</li>
			<li class="objects-head-status__yellow" style="display:inline-block; margin-right: 20px;">Забронирован</li>
			<li class="objects-head-status__red" style="display:inline-block; margin-right: 20px; ">Продан</li>
			<?
			//print_r($_SESSION);
			if($_SESSION['sh_login']=='admin' ||  $_SESSION['sh_login'] == 'goodzem')
			{
				?>
					<li class="objects-head-status__grey" style="display:inline-block; margin-right: 20px;">Бронь Гудзем</li>
					<li class="objects-head-status__blue" style="display:inline-block; margin-right: 20px;">Забронирован подрядчиком</li>	
				<?
			}
			?>
		</ul>
		</div>
		<script src="/maps/frontend/wheel-zoom.min.js" type="text/javascript"></script>
 
	
		<script>
		$( document ).ready(function() {
			 updatejsoon(<?=$map_id?>,<?=$arr[0]['numbers']?>);
		});
		</script>
		<?
		 
		
	 
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
 // Все карты поселка
 function act__area()
 {
	 
	 global $t;
	 $t['h1'] = 'Участки';
		
	 global $mysql;
	 $area_id = (int) $_GET['area_id'];
 
	  $this->tpl('','landplots','map_css_new'); // Легенда со статусами
	  $this->tpl('','landplots','map_js_new');  
	 
		?>
		
	  <STYLE>
		  	.ratio {
  position: relative;
  width: 100%;

  &::before {
    display: block;
    padding-top: 75%;
    content: "";
  }

  > * {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
  }
}
 
		  @media (max-width: 750px) 
		  { 
			.ratio {
			  position: relative;
			  width: 100%;

			  &::before {
				display: block;
				padding-top: 150%;
				content: "";
			  }

			  > * {
				position: absolute;
				top: 0;
				left: 0;
				width: 100%;
				height: 100%;
			  }
			}
		  }
		  
		  
		  .path_area:hover{display:none; }
		  

		  </STYLE>
		  
		  <?
		  
	//  if(!$area_id){$area_id=1;}
		  
	 if($area_id)
	 { 
		 $arr = $mysql->get_arr('SELECT * FROM landplots_maps WHERE area_id="'.$area_id.'" ');
		 $this->object_menu();
		 
		 foreach( $arr as $k=>$v )
		 {
			  $this->act__map( $v['landplots_map_id'] );
		 }
	 }
	 else
	 {
		 
		$arr = $mysql->get_arr('SELECT * FROM landplots_area  ');
		?>
		<div class="objects">
			<div class="row">
		<?
		foreach($arr as $k=>$v)
		{
			
		$status = 'в продаже';
			
		// доступ
		if($_SESSION['sh_login'] === 'admin' ||  $_SESSION['sh_login'] == 'goodzem')
		{
			if( $v['show']!=1 && $v['show']!=2 && $v['show']!=3){continue;}
		}
		elseif(  $_SESSION['agency_id'] == "92")
		{
			if( $v['show']!=1 && $v['show']!=3){continue;}
		}
		else 
		{
			if( $v['show']!=1  ){continue;}
 
		}
		
		
		if( $v['show']!=1  ){$status = 'скрыт';}
		
			
		?>		
			<div class="col-sm-6 col-md-6 col-lg-4 col-xl-3" <?if( $v['show']!=1 ){?> style="opacity:0.6" <?}?>>
				<div class="object">
					<div class="object__title"><?=$v['caption']?></div>
					<div class="object__pict">
						<img src="/area_render/<?=$v['area_id']?>.jpg" alt="">
						<div class="object__info">
						<div class="object__status object__status_sale"><?=$status?></div>
						</div>
					</div>
					<a href="ctrind.php?ctr=landplots&act=area&area_id=<?=$v['area_id']?>" class="object__btn btn btn_arrow">К объекту<i></i></a>
				</div>
			</div>
		<?
		}
		?>
		</div>
		</div>
		
		<?
	 }
 
 }
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 

function act__parsexml()
{
	global $mysql;
	$map_id = 57;
 
	$s=file_get_contents('../maps/'.$map_id.'/data.php');
	if(!$s){print 'Нет файла'; return;}
	
	$s = preg_replace_callback('/"([^"]+)"/', function ($matches) 
	{
	 return htmlspecialchars($matches[0],ENT_NOQUOTES);
	}, $s);
 
	libxml_use_internal_errors(true);
	$doc = simplexml_load_string($s);
	  
	if (!$doc) 
	{
		$errors = libxml_get_errors();
		foreach ($errors as $error) 
		{
			print_r($error);
		}
		libxml_clear_errors();
	}

	$nodes = $doc->children();
	
	print '<pre>';
//print_r($nodes);
	
	$outstr = '';
	foreach( $nodes as $k => $v )
	{
		$attr = current($v->attributes());   
		
		/*
		[class] => area area-status-free
		[data-status] => free
		[data-area-number] => 2 НОМЕР УЧАСТКА
		[data-area-id] => 10
		[data-tippy-content] => <h4>Участок №2</h4><p>Площадь 8.59 сот.</p><p class='rouble-ico'><strong>1 580 560</strong></p>
		[d] => M941.5 242L942 179.5H971.5V242.5L941.5 242Z
		*/
	 
		$data = array();
		$data['map_id'] = $map_id;
		$data['polygon_id'] = $attr['data-area-id'];
		$data['num'] =$attr['data-area-number'] ;
		if($attr['data-status'] =='reserved'){ $data['status'] = '4';}
		if($attr['data-status'] =='free'){ $data['status'] = '2';} // Свободен
		if($attr['data-status'] =='sold'){ $data['status'] = '3';} // продан
		
		if($attr['data-status'] =='stock is-disable'){ $data['status'] = '6';} // не в продаже
	
	 
	 
		preg_match('~<strong>(.*?)</strong>~is', $attr['data-tippy-content'], $p ); // ценаf 
		$data['price'] = str_replace(' ','',$p[1]);
		 
		preg_match('~Площадь (.*?) сот.~is', $attr['data-tippy-content'], $m );  
		$data['area'] = $m[1]*100;
		
		$svg.='<path data-id="'.$attr['data-area-id'].'" d="'.$attr['d'].'"></path>
		';
		
		// print '<br>';
		// print_r($data); 
	
		if(!$data['polygon_id']){$data['polygon_id']=0;}
		if(!$data['num']){$data['num']=0;}
		if(!$data['price']){$data['price']=0;}
		if(!$data['num']){$data['num']=0;}
		if(!$data['status']){$data['status']=2;}
		if(!$data['area']){$data['area']=0;}
		
		print_r($data);
		$arr = $mysql->get_arr('SELECT * FROM landplots WHERE map_id="'.$map_id.'" AND polygon_id="'.$attr['data-area-id'].'" ',1);
		if($arr) // Обновление существующих записей
		{
			//РАЗБОКИРОВАТЬ
			//print $mysql->update_for_key('landplots','lp_id',$arr['lp_id'],$data,1);
			print '</br>';
		}
		else
		{
			//$idx = $mysql->insert('landplots',$data);
		}
		//print_r($arr);
	}

		print '</pre>';
		
			print '<textarea>';
		print $svg;
		print '</textarea>';
}
 
	
	function act__import()
	{
		global $mysql;
		
		$file =file('import.csv');
		// print_r($file);
		
		foreach($file as $k=>$v)
		{
			if($v)
			{
				$str_arr = explode(';',$v);
				$new_arr[$str_arr[0]] = $str_arr;
			}
		}
 
		$arr = $mysql->get_arr('SELECT * FROM landplots WHERE status!=3');
		
		print '<pre>';
		// print_r($arr);
		print '</pre>';
		
		foreach($arr as $k=>$v)
		{
			$new_arr_sq[$v['num']]=$v;
			//print $v['num']; 
			//if($new_arr[$v['num']]){print '<b>Есть</b>';}
			//else{print '<b>Нет!!!!!</b>';}
			//print '<br/>';
		}
		
		
		foreach($new_arr as $k=>$v)
		{
			$data_array = array();
			$data_array['area']=trim($v[2]);
			# $mysql->update_for_key('landplots','num',$k,$data_array,1);
			print '<br/>';
			
			//print $k; 
			//if($new_arr_sq[$k]){print '<b>Есть</b>';}
			//else{print '<b>Нет!!!!!</b>';}
			//print '<br/>';
		}
		
		
			
	}
	
	function act__editor()
	{
		global $t;

		$this->act__index();
		$t['h1'] = 'Редактор карты';
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
	
	
	
	function display_table__raion($row)
	{
		global $gl_raion;
		return $gl_raion[$row['raion']]; 
	}

	function display_table__price($row)
	{
		//return $row['area']/100*600000; 
		return $row['price'];
	}
 

	function ajcrud_checkform()
	{
		global $filed;
		if( $_SESSION['sh_login'] == 'admin' ||  $_SESSION['sh_login'] == 'goodzem')
		{
		?>
		<div style="margin-top: 20px;   border-top: 1px solid #000;    padding-top: 20px;">
		<div>Массовая смена статуса (для отмеченных строк)</div>
		<select name="status" class="input_edit" style="text-transform:none; height:auto; border: solid #00CDAD 2px;">
			<option value="0">Не задан</option>			
			<option value="2">Свободен</option>			
			<option value="3">Продан</option>			
			<option value="4">Забронирован</option>			
			<option value="5">Забронирован застройщиком</option>			
			<option value="6">Участок подрядчика</option>		
		</select>
		
		
		 
		<?
		$filed->text('new_area_price','Пересчитать цену за сотку','');
		?>
		
		
		<input class="stat-top-btn btn btn_arrow-long" type="submit" value="Сохранить" style="padding: 10px; height:35px; width:auto; "/>
	 
		</div>
		
		
		 
		
		
		
		
		
		
		<?
		}
		
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
			//$data = $mysql->get_for_key( $this->table , $this->key_filed , $k); 
			//print_r($data['lp_id'] );
   
			// $landplots = $r->get_object('landplots'); // контроллер лендплотс
			
			   $broni_idx = $this->add_broni( $k , $_POST['status'] );
			  
			  $datax = array();
			  $datax['status_broni_id'] = $broni_idx; 
			  $datax['status'] = $_POST['status']; 
			  $mysql->update_for_key('landplots','lp_id', $k , $datax , 0);
  
		}
	}


	// Метод генерирует ссылку аякс для контента раскрывающейся строки
	function display_hr_ajax($v)
	{
		return '/sahmatka/ajax_router.php?ctr=landplots&act=broni_history&id='.$v['lp_id'];
	}
	
	function act__price()
	{
		global $t;
		$t['h1'] = 'Участки';
 
		$this->display_ajax_crud();
	}
}