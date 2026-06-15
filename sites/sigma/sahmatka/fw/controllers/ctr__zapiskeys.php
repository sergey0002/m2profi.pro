<?
$GLOBALS['t']['title']='Редактор домов';

class ctr__zapiskeys extends ctr__
{ 
	var $table = 'zapis'; //Главная таблица
	var $key_filed = 'zapis_id'; // Ключевое поле главной таблицы
	var $ctr = 'zapiskeys';
	
	function __construct()
	{
		global $mysql;
		
		
		$this->homes_arr = $mysql -> get_arr(' SELECT * FROM `homes` ORDER BY `title` ',1,'home_id');
		 
		// Для селектов 
		// $this->select_homes[]='Выбрать дом';
		foreach($this->homes_arr as $k=>$v)
		{
			if(!$v['show_keys']){continue;}
			$this->select_homes[$k] = $v['title'];
		}
 
		$this->get_data_arr(); // Получаем данные для отображения 
		$w_rus[0]='ВС';
		$w_rus[1]='ПН';
		$w_rus[2]='ВТ';
		$w_rus[3]='СР';
		$w_rus[4]='ЧТ';
		$w_rus[5]='ПТ';
		$w_rus[6]='СБ';
		$w_rus[7]='ВС';
		
		$this->w_rus = $w_rus;
		  
		$this->get_grafic();
	}
	 
	  
	  
	
	function get_data_arr()
	{
		// ФУНКЦИИ МОДЕЛИ и контроллера
		
		//print_r($_POST);
		//print_r($_GET);
 
		  global $mysql;
		  $q = ' SELECT ';
		  
		 if($_GET['act']=='sel_home' || $_GET['act']=='sel_section' || $_GET['act']=='sel_apartment_num' || $_GET['act']=='sel_date')
		 {
			 $q.=' count(*) as c, ';
		 }
		 
		 $q.=' z2.date as z2date, z2.time as z2time, zapis.* ,apartaments.floor, apartaments.rooms, apartaments.area ,homes.long_title
			FROM `zapis` 
			LEFT JOIN  apartaments ON apartaments.home_id = zapis.home_id AND zapis.apartment_num = apartaments.apartment_num
			LEFT JOIN  homes ON homes.home_id = apartaments.home_id
			LEFT JOIN  zapis as z2 ON z2.home_id = zapis.home_id AND zapis.apartment_num = z2.apartment_num AND z2.del="0"
			where  1=1 
			AND zapis.section!="0" 
			AND zapis.section!="" 
			AND long_title!="" 
			
			';
			
			// AND show_keys=1
			
			
			if( !$_POST['show_dell'] && !$_GET['show_dell'] ){ $q.=' AND zapis.del="0"  ';}
			 
			if( $_POST['home'] ){ $q.=' AND apartaments.home_id = "'. $_POST['home'].'" ';}
			if( $_POST['home_id'] ){ $q.=' AND apartaments.home_id = "'. $_POST['home_id'].'" ';}
			
			if( $_POST['section'] ){ $q.=' AND apartaments.section_id = "'. $_POST['section'].'" ';}
			if( $_POST['section_id'] ){ $q.=' AND apartaments.section_id = "'. $_POST['section_id'].'" ';}
			
			
			if( $_POST['apartment_num'] ){ $q.=' AND apartaments.apartment_num = "'. $_POST['apartment_num'].'" ';}
			if( $_POST['date'] ){ $q.=' AND zapis.date = "'.$_POST['date'].'" ';}
			
			if( !$_POST['arhiv'] ){ $q.=' AND `zapis`.`date` >= CURDATE() ';}
			
			if( $_POST['pom'] || $_GET['pom'] ){ $q.=' AND zapis.pom="1"  ';}
			//else{}

			// Группировка 
			if( $_GET['act']=='sel_home' ){ $q.='GROUP BY home_id ORDER BY home_id';}
			elseif( $_GET['act']=='sel_section' ){ $q.='GROUP BY section ORDER BY section';}
			elseif( $_GET['act']=='sel_apartment_num' ){ $q.='GROUP BY apartment_num ORDER BY apartment_num';}
			elseif( $_GET['act']=='sel_date' ){ $q.='GROUP BY zapis.date ORDER BY zapis.date DESC';}
			else { $q.='  ORDER BY zapis.date DESC , zapis.time ASC';}
			
			 //print $q;
			$this->data_arr = $mysql->get_arr($q);
			//print_R($this->data_arr);
	}
	
	
	
	 


 


 
	
	
	

 
 
 








################# Редактирование записи
 

	function act__edit()
	{
		global $filed;
		global $mysql;
		global $r;
		
		
		# Данные редактирования
		$id = $_GET['id'];
		if( $id )
		{
			$data = $mysql->get_for_key('zapis','zapis_id',$_GET['id']);
			print '<br><br> <h2>Редактирование записи</h2>';
			
			if(!$data){ print 'Ошибка: не удалось получить данные записи' ; return; }
		}
		else
		{
			print '<h2>Новая запись</h2>';
			print 'Добавление записей не возможно';
			return;
		}
 
	 
		if($_POST) ############# Обработка данных пост
		{
				$data_insert = array();
				//print '<pre>';
				// print_r($_POST);
				//print '</pre>';
				 
			if($id) // Редактирваоние существующей записи
			{
				 
				$data_insert = array();
				//$data_insert['time'] = $_POST['time'];
				//$data_insert['date'] = $_POST['date'];
				$data_insert['phone'] = $_POST['phone'];
				$data_insert['fio'] = $_POST['fio'];
				if( $_POST['pom'] ){ $data_insert['pom'] =1; } else{ $data_insert['pom'] = 0; }
 			
				//print_r($data_insert);
				print 'Изменения сохранены!';//
				$mysql -> update_for_key( 'zapis' , 'zapis_id' , $id , $data_insert );
			}
			
			// Повторно получаем данные измененные
			$data = $mysql->get_for_key('zapis','zapis_id',$_GET['id']);
		}
		
		if(!$_POST || 1 == 1 ) ############# ФОРМА
		{
		?>
		
	<form action="<?=$r->acturl('zapiskeys','edit','iframe_router.php');?>&id=<?=$id?>" method="POST"  data-controller="zapiskeys" id="ajaxform">
 
	<div style="text-align:left; width:100%;">
	<?
	 
  	
	print '<h2>ДОМ: '.$this->homes[$data['home_id']].'</h2>';	
	?><div style="display:none;"><?=$filed->text('home_id','',$data['home_id'])?></div><?

	print '<h2>Секция: '. $data['section'].'</h2>';
	?><div style="display:none;"><?=$filed->text('section','',$data['section'])?></div><?
 
	print '<h2>' . unit_label_cap('nom') . ': '. $data['apartment_num'].'</h2>';
	?><div style="display:none;"><?=$filed->text('apartment_num','',$data['apartment_num'])?></div><?
 
	?>	
	<hr/>
	<?
	$date_value = date('d.m.Y',strtotime( $data['date']));
	?>
	<input type="hidden" name="sel_date_zapis__original" id="sel_date_zapis__original" value="<?=$date_value?>" />
	<h2 style="font-size:14px;">Дата: <?=$date_value?></h2>
	
	
	<select id="sel_date_zapis" name="date" style="max-width:100%;  display:none;">
		<option value="">Дата</option>
		<option selected="selected" value="<?=$date_value?>"><?=$date_value?></option>
	</select>
	 
	<br/>
	 
	
	<?
	$time_value = date('H:i',strtotime( $data['time']));
	?> 
	<input type="hidden" name="sel_time_zapis__original" id="sel_time_zapis__original" value="<?=$time_value?>" />
	<h2 style="font-size:14px;">Время: <?=$time_value?></h2>
	
	
	<select id="sel_time_zapis" name="time" style="max-width:100%; display:none;">
		<option value="">Время</option>
		<option selected="selected" value="<?=$time_value?>"><?=$time_value?></option>
	</select>
    
	<br/>
	
	<?=$filed->text('phone','Телефон',$data['phone']);?><br/>
	<?=$filed->text('fio','ФИО',$data['fio']);?><br/>
	<?=$filed->checkbox('pom','С помогающей',$data['pom']);?><br/><br/>
 
	<?=$filed->submit();?><br/>
	</div>
			
	</form>
		<?
	}
 
		
		//$this->act__index();
		?>
		<script>
		 //relate_ajax_select('','sel_date_zapis,sel_time_zapis'); // Грузим дату данными из формы
		
		$('#sel_date_zapis').on('change', function() {
		  // relate_ajax_select(this,'sel_time_zapis');
		});
		</script>
		<?
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	// Перечень квартир в доме с пометкой о записи % прошедших записей % квартир без записи % Предстоящих записей график выдачи ключей %
	function act__showhome_ind()
	{
		$q = 'SELECT * FROM appartaments LEFT JOIN zapis ON home_id = zapis.home_id';
	}
	
	
	
	
	#### ИНТЕРФЕЙС БЕКЕНД
	
	
	
	
	
	
		
	
	
	
	
	
	
	
	
	
	
	
	############################################################################################
	############################### новая форма от 18.11.22
	
 
	
	
	
	# ПОДСЧЕТ КОЛИЧЕСТВА ЗАПИСЕЙ НА ДЕНЬ ,ВРЕМЯ В ДОМЕ
	function check_count_zapisx($home='',$data,$time='')
	{
		global $mysql;
	 
		$tsql = 'SELECT count(*) as c FROM `zapis` WHERE "'.$data.'" = DATE_FORMAT(date,"%d.%m.%Y") AND zapis.del="0" ';
		
		if($home){ $tsql.= ' AND `home_id` = "'.$home.'"  ';	}
		if($time){ $tsql .= ' AND `time` = "'.$time.'" ';}
		
		//	print $tsql;
		$arr = $mysql->get_arr( $tsql , 1 );
		 
		//  print_r($arr);
		return $arr['c'];
	}
	
	
	
	
	
	// ПОЛУЧИТЬ ГРАФИК в виде 2х массивов доступное количество записей [дом][дата][время]="записей";
	function get_grafic()
	{
	
		global $mysql;
		
		// Группы обектов 
		$place_group = $this->place_group;
	   
		// Кстыль приводим даты в базе  
		$arr = $mysql->get_arr('SELECT * FROM `keys_grafic`');
		foreach( $arr as $k=>$v)
		{
			$data= array(); 
			$data['time_sql'] = $v['time'].':00';
			$data['date_sql'] = date('Y-m-d',strtotime($v['date'])); // Текущая дата
			
			$group_id = (int) $v['place_id'];
			if( $group_id )
			{
				// $data['place_group_id'] = $group_id; // Текущая дата
			}
			$mysql->update_for_key('keys_grafic','keys_grafic_id',$v['keys_grafic_id'],$data);
		}
		
		 
		
		### данные записей #############################################
		$this->count_zapis_date = array();
		$this->count_zapis_date_time = array();
		$this->count_zapis_home_date_time = array();
		$this->count_zapis_home = array();
		
		$q = 'SELECT count(*) as c, home_id, DATE_FORMAT(date,"%d.%m.%Y") as date, time  FROM `zapis` WHERE `zapis`.`date`>=NOW() AND  zapis.del="0" GROUP BY home_id,date,time';
 
		$arr = $mysql->get_arr( $q   );
	 
		foreach($arr as $k => $v )
		{ 
			$this->count_zapis_date[$v['date']] = $this->count_zapis_date[$v['date']]+$v['c'];	
			$this->count_zapis_date_time[$v['date']][$v['time']] = $this->count_zapis_date[$v['date']][$v['time']]+$v['c'];
			$this->count_zapis_home[$v['home_id']] = $this->count_zapis_home[$v['home_id']]+$v['c'];
			
			// Только это используется для пределения свободных дней записи?!
			$this->count_zapis_home_date_time[$v['home_id']][$v['date']][$v['time']] = $this->count_zapis_home_date_time[$v['home_id']][$v['date']][$v['time'].':00']+$v['c'];
		}
		
		// Коррекция по группам 
		
  
		### -------------- #############################################
		
		
		
		 
		### Данные Графика #############################################
		
		$this -> settings_arr_date_time = array();
		$this -> settings_arr_date = array();
		
		$this -> grafic_arr_date_time  = array();
		$this -> grafic_arr_date  = array();
		$this -> grafic_arr_home  = array();
		$this -> grafic_arr_date_time_free = array();
	 
		
		# ТОЛЬКО ДАТЫ НЕ ПРОШЕДШИЕ И ТОЛЬКО ТЕ В КОТОРЫХ ЕСТЬ ВРЕМЯ!

		############## СОБИРАЕМ ГРАФИК - ДОСТУПНОЕ ВРЕМЯ И ЛИМИТЫ ЗАПИСЕЙ
		$data_settings = $mysql->get_arr(' SELECT * FROM keys_grafic WHERE `date_sql`>=NOW() ');
		 
		
		// Формируем массив id[дата][время]='вся инфа'
		foreach( $data_settings as $k => $v )
		{
			if( $v )
			{
				$this -> settings_arr_date_time [$v['place_id']][$v['date']][$v['time']] = $v['сapacity'];// Количество записей на время
				$this -> settings_arr_date[$v['place_id']][$v['date']] = $this -> settings_arr_date[$v['place_id']][$v['date']] + $v['сapacity']; // Количество записей на дату
				
				$this -> grafic_arr_date_time [$v['place_id']][$v['date']][$v['time_sql']] = $v['сapacity'];// Количество записей на время
				$this -> grafic_arr_date[$v['place_id']][$v['date']] = $this -> grafic_arr_date[$v['place_id']][$v['date']] + $v['сapacity']; // Количество записей на дату

				$this -> grafic_arr_home[$v['place_id']]  = $this -> grafic_arr_home[$v['place_id']]  + $v['сapacity']; // Количество записей на дом
				
				// $place_group['01.12.2022'][]=27;
				if( in_array( $v['place_id'] , $place_group[$v['date']] ) )
				{
					  // if( $_GET['dev'] ){print '<h2>ДОМ - '.$v['place_id'].' на дату '.$v['date'].' состоит в группе</h1>'; }
					  // if( $_GET['dev'] ){print_r( $place_group[$v['date']] ); }
					  // if( $_GET['dev'] ){print $v['time_sql'];}
					  
					  // ОБЩЕЕ КОЛИЧЕСТВО МЕСТ ДЛЯ ГРУППЫ
				}
				
				// Свободно не учитывая группы
				$this -> settings_arr_date_time_free[$v['place_id']][$v['date']][$v['time_sql']] = $v['сapacity']  - $this->count_zapis_home_date_time[$v['place_id']][$v['date']][$v['time_sql']];
				
				
			}
		}
		
	
		
		
		
		
		// КОРРЕКТИРУЕМ МАССИВ СВОБОДНОГО ВРЕМЕНИ НА ОСНОВЕ ГРУПП (ГДЕ БОЛЬШЕ СВОБОДНО ТАМ И У ВСЕХ СВОБОДНО В ГРУППЕ ) чето не работает походуф
		// Суммируем количество занятого времени  отнимаем дружно от свободного 
	
 
	
	
	
		foreach( $this -> grafic_arr_date_time as $k=>$v ) // $k - обьект
		{ 
			foreach( $v as $k1=>$v1 ) // k1 - дата
			{
				if( in_array( $k , $place_group[$k1] )  ) // Если обект в группе сс каким то на заданную дату
				{ 
					if($_GET['dev']){print '<h1>'. $k.'-'.$k1.'</h1>';}
					foreach ( $place_group[$k1] as $kkk => $vvv ) // $vvv другой обект из группы  (ид обекта)
					{
						foreach( $v1 as $k2 => $v2 ) // k2 - время икл по времени  ОРИГИНАЛЬНОГО обекта в графике 
						{
						 
								
							//	if($_GET['dev']){print '<b>Группы - '.$k1.'-'.$vvv.'</b><br/>';}
							$ccc_1 = (int) $this -> settings_arr_date_time_free[$vvv][$k1][$k2];
							$ccc_2 = (int) $this -> settings_arr_date_time_free[$k][$k1][$k2];
							 
							if( $ccc_1 < $ccc_2 )  
							{
								 // print  $this -> settings_arr_date_time_free[$vvv][$k1][$k2] . ' - ' .$this -> settings_arr_date_time_free[$k][$k1][$k2]. '<br/>';
								 $this -> settings_arr_date_time_free_g[$vvv][$k1][$k2] = $ccc_2;	
							}
							
							if( ( $vvv.$k1.k2!=$k.$k1.k2 )  && ($this->count_zapis_home_date_time[$k][$k1][$k2] || $this->count_zapis_home_date_time[$vvv][$k1][$k2]) ) // не сложить количество записей одного обекта
							{
								// Суммируем количество записей 
								 			
								if($_GET['dev'])
								{ 
									print '<b>['.$vvv.']['.$k1.']['.$k2.']='.$this->count_zapis_home_date_time[$vvv][$k1][$k2].' ++++ ['.$k.']['.$k1.']['.$k2.']='.$this->count_zapis_home_date_time[$k][$k1][$k2].' </b><br/>';
								}			 
								// (int) $this->count_zapis_home_date_time[$k][$k1][$k2] = (int) $this->count_zapis_home_date_time[$k][$k1][$k2] + (int) $this->count_zapis_home_date_time[$vvv][$k1][$k2];
							}
							 
							 
						}
					}
				}
			}
		}
		
		
		
		
		
		
		 
		
		
		
		 
		
		// ПЕРЕСОБИРАЕМ МАССИВ СВОБОДНОГО ВРЕМЕНИ - ПО ДАТАМ И ДОМАМ
		foreach( $this -> settings_arr_date_time_free as $k=>$v ) // $k - обьект
		{
			foreach( $v as $k1=>$v1 ) // k1 - дата
			{
				foreach( $v1 as $k2 => $v2 ) // k2 - время
				{
					$this->settings_arr_free[$k] = $this->settings_arr_free[$k] + $v2;
					$this->settings_arr_date_free[$k][$k1] = $this->settings_arr_date_free[$k][$k1] + $v2;
				}
			}
		}
		 
 
		  
	}

 

	 
	// Дата из в mysql
	function datefrommysql($mydate, $dtformat='Y-m-d')
	{
		$dt = new DateTime();
		$date = $dt->createFromFormat($dtformat, $mydate);
		$convertdt = $date->format('d.m.Y');
		return $convertdt;
	}
	
	function date2mysql($mydate, $dtformat='d.m.Y')
	{
		$dt = new DateTime();
		$date = $dt->createFromFormat($dtformat, $mydate);
		$convertdt = $date->format('Y-m-d');
		return $convertdt;
	}



	############## НОВЫЙ ГРАФИК
	function  get_graficx()
	{
		global $mysql;
		$arr = $mysql -> get_arr(' SELECT * FROM keys_graficx LEFT JOIN keys_graficx_gr ON keys_graficx.keys_graficx_id = keys_graficx_gr.keys_graficx_id ');
		
		foreach($arr as  $k=>$v )
		{
			// [дом][дата][время]=количество
			$this->graficx_data[$v['place_id']][$this->datefrommysql($v['date'])][$v['time']]=$v['сapacity'];
			
			# для вывода графика
			// [дата][группа][дом][время] = записей
			if($v['place_id'])
			{
				// вывод времени для группы 
				$this->graficx_data_pv[$this->datefrommysql($v['date'])][$v['keys_graficx_id']][$v['time']]=$v['сapacity'];
				
				$this->graficx_data_p[$this->datefrommysql($v['date'])][$v['keys_graficx_id']][$v['place_id']][$v['time']]=$v['сapacity'];
			}
		}
		 
	}
	 


	// Получение данных графика для отрисовки и занятости
	function get_graficxdata()
	{
		global $mysql;
		
		$mysql -> get_array(' SELECT * FROM keys_graficx WHERE keys_graficx.date>=curdate() ');
	}

  
 
 
	 
	 
	 

 

	
	# ПОИСК НАЗВАНИЕ ДОМА ФОРМА ЗАПИСИ
	function act__v_home_name()
	{
		global $mysql;
  
		$q = ' SELECT * FROM homes WHERE show_keys="1" AND home_id="'.$_GET['home_id'].'" ';
		$arr=$mysql->get_arr($q,1);
		print 'Дом № '.$arr['long_title'].' по генплану';
	}
	
	
	
	
	
	# ПОИСК НАЗВАНИЕ ДОМА ФОРМА ЗАПИСИ
	function act__v_adresskeys()
	{
		global $mysql;
   
		$q = ' SELECT * FROM homes WHERE show_keys="1" AND home_id="'.$_GET['home_id'].'" ';
		$arr = $mysql->get_arr($q,1);
		print ' <b>'.$arr['keys_adress'].'</b> ';
	}
	
	
	
	
	# ПОИСК ФОРМЫ ЗАПИСИ
	function act__sel_home_zapis()
	{
		global $mysql;
 
		 
		$arr=$mysql->get_arr(' SELECT * FROM homes WHERE show_keys="1" ');
		?><option value="">Выбрать дом</option><?
		foreach($arr as $k=>$v)
		{
			// $free = $this -> grafic_arr_home[$v['home_id']] - $this -> count_zapis_home[$v['home_id']];
			$free = $this -> settings_arr_free[$v['home_id']];
			 
			if( $free > 0 || $v['home_id'] == '29' ){$opt = ' style="font-weight:bold;" '; $text = '';}	else{$opt = ' disabled ';  $text = '  ';}
			
			?><option value="<?=$v['home_id']?>" <?=$opt?>>Дом №<?=$v['long_title']?> <?= $text?>  </option><?
		}
	}
	 # ПОИСК ФОРМЫ ЗАПИСИ
	function act__sel_section_zapis()
	{
		global $mysql;
		$q = ' SELECT section_id FROM apartaments WHERE `apartaments`.`home_id`="'.$_GET['home_id'].'" GROUP BY `section_id` ';
		$arr = $mysql->get_arr($q);
		?><option value="">Выбрать секцию</option><?
		foreach($arr as $k=>$v)
		{
			if($_POST['home_id']=='49' && $v['section_id']=="3"){continue;}
			?><option value="<?=$v['section_id']?>">Секция №<?=$v['section_id']?> </option><?
		}
	}
	 
	# ПОИСК ФОРМЫ ЗАПИСИ
	function act__sel_apartament_zapis()
	{
		global $mysql;
		$q = ' SELECT apartment_num FROM apartaments WHERE `apartaments`.`home_id`="'.$_GET['home_id'].'" AND `apartaments`.`section_id`="'.$_GET['section_id'].'" GROUP BY `apartment_num` ';
		$arr = $mysql->get_arr($q);
		?><option value=""><?= unit_phrase('select') ?></option><?
		foreach($arr as $k=>$v)
		{
			?><option value="<?=$v['apartment_num']?>"><?=$v['apartment_num']?> </option><?
		}
	}
	
	
	 
	
	
	
	
	 
	
	############################### новая форма от 18.11.22 конец
    ############################################################################################
	
	
	
	
	
	
	 
	 
	 
	 
	 
	 
	 
	 
	 
	 
	#################################################
	#################################################
	  
	##### ФОРМА ПОИСКА 
	function act__sel_home()
	{	
		foreach($this->data_arr as $k => $v)
		{
			?>
			<option value="<?=$v['home_id']?>"><?=$v['long_title']?> (<?=$v['c']?>)</option>
			<?
		}
	}
	
	
	
	function act__sel_section()
	{		
		foreach($this->data_arr as $k => $v)
		{
			?>
			<option value="<?=$v['section']?>"><?=$v['section']?> (<?=$v['c']?>)</option>
			<?
		}
	}
	
	function act__sel_apartment_num()
	{
		foreach($this->data_arr as $k => $v)
		{
			?>
			<option value="<?=$v['apartment_num']?>"><?=$v['apartment_num']?>  </option>
			<?
		}
	}
	
	function act__sel_date()
	{
		foreach($this->data_arr as $k => $v)
		{
			?>
			<option value="<?=$v['date']?>"><?=date('d.m.Y',strtotime( $v['date'])) ?> (<?=$v['c']?>)</option>
			<?
		}
	}
	##### ФОРМА ПОИСКА  конец
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	//Аякс загрузка тела тблицы
	function act__ajax_data()
	{
		// print_R( $this->data_arr); 
		if($this->data_arr)
		{
			foreach( $this->data_arr as $k => $result )
			{
				$this->tpl($result,'zapiskeys','index_ajaxrow'); 
			}
		}
		else{
			?><tr><td colspan="1000"><center><br><br>Подходящих данных не найдено</center></td></tr><?
			
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
			$mysql -> update_for_key( 'zapis' , 'zapis_id' , $id , $data );
		}
		$this->act__index();
	}
	
	
	
	function act__archive()
	{
		
		global $mysql;
		$sql = 'SELECT
			z.zapis_id,
			z.home_id,
			h.title AS home_title,
			z.section,
			z.apartment_num,
			z.date,
			z.time,
			z.phone,
			z.email,
			z.fio
		FROM
			zapis z
		INNER JOIN
			homes h ON z.home_id = h.home_id
		WHERE
		 
			  z.del = 0
		ORDER BY
			z.date DESC, z.time DESC;';
		
		$data = $mysql->get_arr($sql);
		
		//print '<pre>';
		//print_r($data);
		//print '</pre>';
		 
		  
		$titles['date'] ='Дата';
		$titles['time'] ='Время';
		$titles['home_title'] ='Дом';
		$titles['apartment_num'] = unit_label_cap('nom');
		$titles['fio'] ='ФИО';
		 
		$titles['phone'] ='Телефон';
		$titles['email'] ='email';
	 
		 
		 
		 
		 
		// Генерируем файл и получаем публичную ссылку
		$link = $this->exportArrayToCsvFile($data, $titles, 'arh.csv');

		// Убеждаемся, что скрипт не отправил ничего до этого (иначе header() не сработает)
		if (headers_sent($file, $line)) {
			die("Headers already sent at $file:$line");
		}

		// Делаем редирект на файл
		header('Location: ' . $link);

 
	}
	
	function act__index()
	{
		global $r;
		global $mysql;
		global $t;
		$t['h1'] = 'Запись на выдачу';
		
		?>
		<a href="/sahmatka/ajax_router.php?ctr=zapiskeys&act=archive">Скачать архив</a>
		<div style="text-align:right; width:100%; padding:20px; padding-left:0; padding-right:0;">
			<a style="display:none;" href="<?=$r->acturl('zapiskeys','backendform','iframe_router.php');?>" class="btn_2  iframe_r">Запись </a>
			<a href="form_zapis_editor.php" style="display:none;"  class="btn_2  iframe_r">Запись </a>
		</div>
 
			<?	
			$this->get_data_arr();
			$arr = $this->data_arr;
			?>
			<div class="stat">
			<?
				$this->tpl('','zapiskeys','index_table');	
			?>	
			</div>			
								
<script>
$( document ).ready(function() {
 
/*
resultto - ид тега для загрузки результата
formid - ид формы 
url - 
append - добавлять к содержимому ajax
*/
function sendAjaxForm(resultto, formid, url,append=1,progressid='progressbar') {
 
 
 if(!append){$('#'+resultto).html('');	}
 $('#'+progressid).show();
 	
			
    $.ajax({
        url:     url, //url страницы (action_ajax_form.php)
        type:     "POST", //метод отправки
        dataType: "html", //формат данных
        data: $("#"+formid).serialize(),  // Сеарилизуем объект
        success: function(response) { //Данные отправлены успешно
		 
        	// result = $.parseJSON(response);
        	//$('#'+resultto).html('Имя: '+result.name+'<br>Телефон: '+result.phonenumber);
			if(append)
			{
				$('#'+resultto).append(response);
			}
			else
			{
				$('#'+resultto).html(response);
			}
			$('#'+progressid).hide();
			
				// Перезагржаем фенси 
				 $('.iframe_rajax').magnificPopup({type:'iframe',
				  removalDelay: 100,
				  fixedContentPos: true, 
				  disableOn:1,
				   tLoading: 'Загрузка #%curr%...',
					callbacks: {
					open: function() {
					  // Will fire when this exact popup is opened
					  // this - is Magnific Popup object
					},
					close: function() {
						// Перезагрузить отображение!
						sendAjaxForm( 'zapisdata' , 'filtrform' , 'https://xdemo.m2profi.pro/sahmatka/ajax_router.php?ctr=zapiskeys&act=ajax_data',0); // Грузим содержимое селек
					},
					open: function() {
						  location.href = location.href.split('#')[0] + "#pop";
						} 
					 
					// e.t.c.
				  }
				   
				  });
				//////////////////////////////////
  
  
  
    	},
    	error: function(response) { // Данные не отправлены
            // $('#'+resultto).html('Ошибка. Данные не отправлены.');
			 alert('ajax error');
			$('#'+progressid).hide();
    	}
 	});  
}
	
	
/*
select_id_list = 'идселекта1,ид селекта2' - селекты которые необходимо перезагрузить 
*/
function relate_ajax_select(th,select_id_list,formid='filtrform')
{
	// Получаем массив селектов которые затрагивает данный 
	var array = select_id_list.split(",");
 
	// Цикл по массиву селектов
	array.forEach(function(item, i, arr) {
		// alert( i + ": " + item + " (массив:" + arr + ")" );
		 $("#"+item).prop('disabled', 'disabled'); // Блокируем селекты в которые предстоит загрузка  
		 $('#'+item).find('option[value!=""]').remove(); // Удаляем все НЕПУСТЫЕ опшены
	});
	//
	var controller= $('#'+formid).attr('data-controller');
	// Цикл по массиву селектов
	array.forEach(function(item, i, arr) {		 
		// alert(item);
		 sendAjaxForm( item , formid , '/sahmatka/ajax_router.php?ctr='+controller+'&act='+item+''); // Грузим содержимое селек
		// РАЗБлокируем селекты в которые предстоит загрузка   
		  $("#"+item).prop('disabled', '');
	});
	//
}
 
 

// Начальная загрузка данных 
relate_ajax_select('','sel_home,sel_section,sel_apartment_num,sel_date');
  sendAjaxForm( 'fw_data_tbody' , 'filtrform' , '/sahmatka/ajax_router.php?ctr=zapiskeys&act=ajax_data',0); // Грузим содержимое селек


$('#arhiv').change(function() {
 relate_ajax_select(this,'sel_home,sel_section,sel_apartment_num,sel_date');
});


$('#pom').change(function() {
 //relate_ajax_select(this,'sel_home,sel_section,sel_apartment_num,sel_date');
});


$('#sel_home').on('change', function() {
 // alert( this.value );
 relate_ajax_select(this,'sel_section,sel_apartment_num,sel_date');
});


$('#sel_section').on('change', function() {
 // alert( this.value );
 relate_ajax_select(this,'sel_apartment_num,sel_date');
});

 
 
$('#sel_apartment_num').on('change', function() {
 // alert( this.value );
 relate_ajax_select(this,'sel_date');
});

  
// ЗАГРУЗКА ДАННЫХ ПРИЛЮБОЙ ОБРАБОТКЕ ФОРМЫ!
$( "#filtrform input,#filtrform select" ).change(function() {
  sendAjaxForm( 'fw_data_tbody' , 'filtrform' , '/sahmatka/ajax_router.php?ctr=zapiskeys&act=ajax_data',0); // Грузим содержимое селек
});


});
</script>



				</script>
		<?
		//print_R($mysql);
	}
	
	
	  
	
	
	 
	
	
	// Публичная карточка выдачи ключей
	function act__card()
	{
		// РАскодируем закодированный id (защита от перебора с целью сбора данных записавшихся)
		if( $_GET['idx'] ){ $idx = (int) $_GET['idx']; $id=$idx;}
		
		if( !$id ){ $id = $_GET['id']; }
		
		// print dechex($id); // Перевести в шестнацатеричную систему id
		// hexdec(); Перевести из 16ти в десятиричную 
		
		$this->card($id);
	}
	
	
	
	
	// карточка записей 
	function card($id='',$data='', $h1='')
	{
		//if(!$id){}
		global $mysql;
		
		 
		if( !$h1 ){ $h1='Запись на выдачу ключей'; }
		
		if(!$data) // переданны данные
		{
			$q = 'SELECT zapis.* ,homes.title, homes.keys_adress FROM zapis LEFT JOIN homes ON homes.home_id = zapis.home_id WHERE 1=1 ';
			$q.= 'AND zapis.zapis_id="'.$id.'" ';
			
			// print $q;
			$v = $mysql->get_arr($q,1);
		}
		else{ $v = $data; }
		
		if(!$v){ die('Ошибка доступа'); }
		
		//print '<pre>';
		//print_r($v);
		$v['h1'] = $h1;
		$v['date'] = date("d.m.Y",strtotime($v['date']));
		$this->tpl($v,'zapiskeys','zapis_card'); 
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	// Публичная карточка выдачи ключей
	function act__card2()
	{
		// РАскодируем закодированный id (защита от перебора с целью сбора данных записавшихся)
		if( $_GET['idx'] ){ $idx = (int) $_GET['idx']; $id=$idx;}
		
		if( !$id ){ $id = $_GET['id']; }
		
		// print dechex($id); // Перевести в шестнацатеричную систему id
		// hexdec(); Перевести из 16ти в десятиричную 
		
		$this->card2($id);
	}
	
	
	
	
	// карточка записей 
	function card2($id='',$data='', $h1='')
	{
		//if(!$id){}
		global $mysql;
		
		 
		if( !$h1 ){ $h1='Запись на выдачу ключей'; }
		
		if(!$data) // переданны данные
		{
			$q = 'SELECT zapis.* ,homes.title, homes.keys_adress FROM zapis LEFT JOIN homes ON homes.home_id = zapis.home_id WHERE 1=1 ';
			$q.= 'AND zapis.zapis_id="'.$id.'" ';
			
			// print $q;
			$v = $mysql->get_arr($q,1);
		}
		else{ $v = $data; }
		
		if(!$v){ die('Ошибка доступа'); }
		
		//print '<pre>';
		//print_r($v);
		$v['h1'] = $h1;
		
		$v['date'] = date("d.m.Y",strtotime($v['date']));
		$this->tpl($v,'zapiskeys','zapis_card2'); 
	}
	
	
 
}