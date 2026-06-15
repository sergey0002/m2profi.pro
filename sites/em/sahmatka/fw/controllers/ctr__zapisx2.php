<?
 
/*
Проверка при записи
+ переключить на синхронный аякс!
отключить стили в селектах и добавить прогрессбар
рефакторинг 
один дом в нескольких группах в пределах дня!
*/
#[AllowDynamicProperties]
class ctr__zapisx2 extends ctr__
{ 
	var $table = 'keys_graficx_date'; //Главная таблица
	var $key_filed = 'keys_graficx_date_id'; // Ключевое поле главной таблицы
	var $ctr = 'zapisx2';
	
	
	
	
	
	
	
	function __construct()
	{
		global $mysql;
		 
		$this->homes_arr = $mysql -> get_arr(' SELECT * FROM `homes` WHERE show_keys = 1 ORDER BY `title` ',1,'home_id');
	 
		// Для селектов 
		// $this->select_homes[]='Выбрать дом';
		foreach($this->homes_arr as $k=>$v)
		{
			$this->select_homes[$k] = $v['title'];
		}
		$w_rus[0]='ВС';
		$w_rus[1]='ПН';
		$w_rus[2]='ВТ';
		$w_rus[3]='СР';
		$w_rus[4]='ЧТ';
		$w_rus[5]='ПТ';
		$w_rus[6]='СБ';
		$w_rus[7]='ВС';
		$this->w_rus = $w_rus;
		 
	 

   

		$time_arr['09:00']='1';
		#$time_arr['09:30']='1';
		
		$time_arr['10:00']='1';
		#$time_arr['10:30']='1';
		
		$time_arr['11:00']='1';
		#$time_arr['11:30']='1';
		
		
		#$time_arr['12:00']='1';
		#$time_arr['12:30']='1';
		
		
		
		#$time_arr['13:00']='1';
		$time_arr['13:30']='1';
		
		#$time_arr['14:00']='1';
		$time_arr['14:30']='1';
		
		#$time_arr['15:00']='1';
		$time_arr['15:30']='1';
		
		#$time_arr['16:00']='1';
		#$time_arr['16:30']='1';
		$this->time_arr = $time_arr;
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


	# Удаление  
	function act__del()
	{
		global $mysql;
		$id = $_GET['id'];
		if($id)
		{			
			$data = array();
			$data['del'] = 1;
			$mysql -> update_for_key( 'keys_graficx_date' , 'keys_graficx_date_id' , $id , $data );
		}
		$this->act__index();
	}
	 
	 
	# Скрыть
	function act__s_daygroup()
	{
		global $mysql;
		$id = $_GET['id'];
		if($id)
		{			
			$data = array();
			$data['show'] = 1;
			$mysql -> update_for_key( 'keys_graficx_date' , 'keys_graficx_date_id' , $id , $data );
		}
		$this->act__index();
	}
	 
	# Показать
	function act__h_daygroup()
	{
		global $mysql;
		$id = $_GET['id'];
		if($id)
		{			
			$data = array();
			$data['show'] = 0;
			$mysql -> update_for_key( 'keys_graficx_date' , 'keys_graficx_date_id' , $id , $data );
		}
		$this->act__index();
	}
	
	
	
	 
	 
	 
	function get_graficx( $future = false , $pom = false , $dkp = false , $sh = 0 )
	{
		 
		 
		global $mysql;
		
		$q='SELECT keys_graficx_date.*,keys_graficx_objects.object_id,keys_graficx_time.time , keys_graficx_time.c, keys_graficx_time.pom,keys_graficx_time.dkp
		FROM keys_graficx_date 
		LEFT JOIN keys_graficx_time ON keys_graficx_date.keys_graficx_date_id = keys_graficx_time.keys_graficx_date_id
		LEFT JOIN keys_graficx_objects ON keys_graficx_date.keys_graficx_date_id = keys_graficx_objects.keys_graficx_date_id
		WHERE keys_graficx_date.del="0"
		';
		
		 
		// Фильтрация по dkp
		if ($dkp == 1) {
			$q .= ' AND keys_graficx_time.dkp = 0';
		} elseif ($dkp == 2) {
			$q .= ' AND keys_graficx_time.dkp > 0';
		}
 
		if ($pom == 1) {
			$q .= ' AND keys_graficx_time.pom = 0';
		} elseif ($pom == 2) {
			$q .= ' AND keys_graficx_time.pom > 0';
		}
		
		
		
		// Показывать скрытое! (только админам и Виталию)
		if(!$_GET['admin'] && $sh==0){ $q.=' AND  `keys_graficx_date`.`show` = "1" ';}
		
		
		
		if($future){$q.=' AND keys_graficx_date.date_mysql>= CURDATE() ';} // Только будущие даты (метод генерации селектов)
		$q.=' ORDER BY keys_graficx_date.date_mysql ';
	
		$data = $mysql->get_arr($q);
		#print '<pre>';
		 #  print_r($data);
		#print '</pre>';
		#$this->settings_arr_date_time_free[$data['home_id']][$data['date']][$data['time']]; // НУЖЕН!!!!!! для добавления!
		foreach($data as $l=>$v) # ЦИКЛ ПО ГРАФИКУ
		{ 
			// 2 - только помогайки, 1 только без помогаек
			if ( 
					( ( $pom == false ) || ( $pom == 2 && $v['pom'] ) || ( $pom==1 && !$v['pom'] ) )
					|| 	
					( ( $dkp == false ) || ( $dkp == 2 && $v['dkp']   ) || ( $dkp==1 && !$v['dkp']    ) )
			   )
			{
				
			$this->graficx_i_date_b[$v['keys_graficx_date_id']]=$v; // [дата][] = группа
			$this->graficx_i_date[$v['date']][$v['keys_graficx_date_id']]=1; // [дата][] = группа
			$this->graficx_i_homes[$v['keys_graficx_date_id']][$v['object_id']]=1; // [группа][]=дом
			
			// Выбираем мощность в зависимости от типа
			$cap = (int)$v['c'];
			if ($pom == 2) $cap = (int)$v['pom'];
			else if ($dkp == 2) $cap = (int)$v['dkp'];
			
			$this->graficx_i_times[$v['keys_graficx_date_id']][$v['time']] = $cap; // [группа][время]=количество записей
		   
			$this->graficx_i_hgr[$v['object_id']][$v['date']] = $v['keys_graficx_date_id']; // [дом][дата] = группа #1 дом одна дата одна группа!
			 
			$this->graficx_i_gr_c[$v['object_id']][$v['keys_graficx_date_id']][$v['time']] = $cap; // по графику [дом][дата группа][время] = мест всего  
			$this->graficx_i_gr_c2[$v['keys_graficx_date_id']][$v['time']] = $cap; // по графику [дата группа][время] = мест всего 
			 
			$this->graficx_i_grra[$v['object_id']][$v['date']] = $v['keys_graficx_date_id']; // расшифровка [дом][дата] = группа// ДОМ МОЖЕТ БЫТЬ ТОЛЬКО В ОДНОЙ ГРУППЕ НА ДАТУ!!!! при этом
			
			$this->graficx_i_grra2[$v['keys_graficx_date_id']] = $v['date']; // расшифровка2 [группа]=дата
			 
			$this->graficx_i_pom[$v['keys_graficx_date_id']][$v['time']] = $v['pom'];
			$this->graficx_i_dkp[$v['keys_graficx_date_id']][$v['time']] = $v['dkp'];
			}
		}
		
		// Загружаем только будущие записи (или последние 30 дней для статистики)
		$q = 'SELECT home_id, date, time, apartment_num, section, pom, dkp 
		      FROM zapis 
		      WHERE date >= "2024-01-01" AND del = "0" ';
		$data = $mysql->get_arr($q);
		
		//print '<pre>';
		// print_r($data);
		//print '</pre>';
		
		// МАССИВ СВОБОДНЫХ МЕСТ (приравниваем к графику)
		$this->graficx_i_za_gr_free = $this->graficx_i_gr_c; // [дом][группа][время] = мест всего 
		$this->graficx_i_za_gr_free2 = $this->graficx_i_gr_c2; // [дата группа][время] = мест всего 
		foreach($data as $l=>$v) # ЦИКЛ ПО ЗАПИСЯМ
		{
			$da = date('d.m.Y', strtotime( $v['date'] ) );
			$ti = preg_replace( "#(:\d+):\d+#", '$1', $v['time'] ) ;
			 
			$this->zapis_i_dth[$da][$ti][$v['home_id']]=$v; //[дата][время][дом]=записи
			
			if($this->graficx_i_grra[$v['home_id']][$da]) // Есть группа для записи на это время
			{
				// Определяем тип записи
				$match = false;
				if ($pom == 2 && $v['pom']) $match = true;
				else if ($dkp == 2 && $v['dkp']) $match = true;
				else if ($pom != 2 && $dkp != 2 && !$v['pom'] && !$v['dkp']) $match = true;
				
				if ($match) {
					$this->graficx_i_za_c[$v['home_id']][$this->graficx_i_grra[$v['home_id']][$da]][$ti]++; // по записям [дом][дата группа][время] = записей всего 
					// отнимаем от графика количество записей на этот день  [дом][группа][время] = мест всего 
					$this->graficx_i_za_gr_free[$v['home_id']][$this->graficx_i_grra[$v['home_id']][$da]][$ti]--;// 
					$this->graficx_i_za_gr_free2[$this->graficx_i_grra[$v['home_id']][$da]][$ti]--;//  [дата группа][время] = мест свободно 
				}				
				if(!$this->graficx_i_za_gr_free2[$this->graficx_i_grra[$v['home_id']][$da]][$ti])
				{
					unset($this->graficx_i_za_gr_free2[$this->graficx_i_grra[$v['home_id']][$da]][$ti]); // удаляем пустое время
				}
				
				if(!$this->graficx_i_za_gr_free2[$this->graficx_i_grra[$v['home_id']][$da]] )
				{
					unset($this->graficx_i_za_gr_free2[$this->graficx_i_grra[$v['home_id']][$da]] ); // удаляем пустое даты
				}
				
				
				
			} 
			
			// Если есть группа с таким домом и вней есть такое время на которое запись
			if( $gid = $this->graficx_i_hgr[$v['home_id']][$da] )
			{
				// Слот считается существующим, если по этому времени в группе есть
				// любая мощность (c / pom / dkp), а не только стандартная (c).
				// Раньше использовалось if($this->graficx_i_times[$gid][$ti]) — это
				// отсекало записи "С ПО" и "ДКП" на слотах с c=0.
				if (isset($this->graficx_i_times[$gid][$ti])
					|| !empty($this->graficx_i_pom[$gid][$ti])
					|| !empty($this->graficx_i_dkp[$gid][$ti])
				) {
					$this->zapis_i_dtx[$v['home_id']][$da][$ti][]=$v; //[дом][дата][время] =записи
					$this->zapis_i_dt[$da][$ti][]=$v; //[дата][время] =записи
					$this->zapis_i_pr[$da][$gid][$ti][]=$v; //[дата][группа][время] =записи
					
					// Подсчет по типам для админки
					$this->zapis_counts[$gid][$ti]['total']++;
					if ($v['pom']) {
						$this->zapis_counts[$gid][$ti]['pom']++;
					} elseif ($v['dkp']) {
						$this->zapis_counts[$gid][$ti]['dkp']++;
					} else {
						$this->zapis_counts[$gid][$ti]['std']++;
					}
				}
			}
			else
			{
				 $this->zapis_i_dtnr[$da][$ti][]=$v; //[дата][время] =записи ВНЕ РАСПИСАНИЯ
			}
		}
		
		// Загружаем фильтры
		$q_f = 'SELECT * FROM keys_graficx_filters';
		$filters_data = $mysql->get_arr($q_f);
		foreach($filters_data as $f) {
			$this->graficx_i_filters[$f['keys_graficx_date_id']][$f['home_id']][] = $f;
		}
	}
	 
  
############## ЗАПИСЬ
	// Дома с свободными датами
	function act__sel_home_zapis()
	{
		// 2 - только помогайки, 1 только без помогаек
		if($_REQUEST['pom']){$pom=2;}else{$pom=1;}
		if($_REQUEST['dkp']){$dkp=2;}else{$dkp=1;}
		
		
		$this->get_graficx( 1 , $pom , $dkp );
		print '<option value="">Выбрать дом</option>';
		foreach( $this->homes_arr as $k=>$v )// [дом][дата][время] = записей 
		{  
			if( $this->graficx_i_za_gr_free[$k] )
			{
				print '<option value="'.$k.'" style="font-weight:bold;">Дом №'.$this->select_homes[$k].'</option>'; 
			}
			else
			{
				print '<option  value="'.$k.'" disabled>Дом №'.$this->select_homes[$k].'</option>'; 
			}
		}
	}
 
	function act__sel_section_zapis()
	{
		// home_id: 26
		// pom: 0
		global $mysql;
		
		// 1. Получаем настройки фильтрации (ПО/ДКП)
		if($_REQUEST['pom']){$pom=2;}else{$pom=1;}
		if($_REQUEST['dkp']){$dkp=2;}else{$dkp=1;}
		
		// 2. Загружаем график и правила (Future only)
		$this->get_graficx(1, $pom, $dkp);
		$home_id = $_GET['home_id'];
		
		// 3. Собираем список активных групп (где есть свободные места)
		$active_groups = [];
		if (isset($this->graficx_i_hgr[$home_id])) {
			foreach($this->graficx_i_hgr[$home_id] as $date => $gid) {
				// Если в группе есть свободные слоты
				if (!empty($this->graficx_i_za_gr_free2[$gid])) {
					$active_groups[$gid] = true;
				}
			}
		}
		
		// 4. Загружаем ВСЕ квартиры дома для проверки доступности секций
		$q_apt = 'SELECT section_id, apartment_num FROM apartaments WHERE home_id="'.$home_id.'"';
		$all_apts = $mysql->get_arr($q_apt);
		
		// Группируем квартиры по секциям
		$sections_map = [];
		foreach($all_apts as $apt) {
			$sections_map[$apt['section_id']][] = $apt['apartment_num'];
		}
		ksort($sections_map); // Сортируем по номеру секции

		?><option value="">Выбрать секцию</option><?
		foreach($sections_map as $sec_id => $apts)
		{
			//if($_POST['home_id']=='49' && $sec_id=="3"){continue;}
			
			// Smart Filter: Секция видна, если ХОТЯ БЫ ОДНА квартира в ней разрешена в ХОТЯ БЫ ОДНОЙ активной группе
			$is_section_visible = false;
			
			if (!empty($active_groups)) {
				foreach($apts as $apt_num) {
					foreach(array_keys($active_groups) as $gid) {
						$filters = $this->graficx_i_filters[$gid][$home_id] ?? [];
						if ($this->isApartmentAllowed($home_id, $sec_id, $apt_num, $filters)) {
							$is_section_visible = true;
							break 2; // Нашли доступную квартиру -> секция доступна
						}
					}
				}
			}
			
			if (!$is_section_visible) continue;

			?><option value="<?= $sec_id ?>">Секция №<?= $sec_id ?> </option><?
		}
	}
	
  
	function act__sel_apartament_zapis()
	{
		global $mysql;
		
		// 1. Получаем настройки фильтрации (ПО/ДКП)
		if($_REQUEST['pom']){$pom=2;}else{$pom=1;}
		if($_REQUEST['dkp']){$dkp=2;}else{$dkp=1;}
		
		// 2. Загружаем график и правила
		$this->get_graficx(1, $pom, $dkp);
		$home_id = $_GET['home_id'];
		$section_id = $_GET['section_id'];

		$q = '
		SELECT apartaments.apartment_num,zapis.zapis_id FROM apartaments 
		LEFT JOIN `zapis` ON `zapis`.`home_id` = `apartaments`.`home_id` AND  `zapis`.`apartment_num` = `apartaments`.`apartment_num` AND `zapis`.`del`="0"
		WHERE `apartaments`.`home_id`="'.$home_id.'" AND `apartaments`.`section_id`="'.$section_id.'" ORDER BY `apartaments`.`apartment_num` 
		 ';
		$arr = $mysql->get_arr($q);
		
		// 3. Собираем список активных групп (где есть свободные места)
		$active_groups = [];
		if (isset($this->graficx_i_hgr[$home_id])) {
			foreach($this->graficx_i_hgr[$home_id] as $date => $gid) {
				if (!empty($this->graficx_i_za_gr_free2[$gid])) {
					$active_groups[$gid] = true;
				}
			}
		}
		 
		?><option value="">Выбрать квартиру</option><?
		foreach($arr as $k=>$v)
		{
			// Smart Filter: Квартира видна, если разрешена в ХОТЯ БЫ ОДНОЙ активной группе
			$is_allowed = false;
			
			if (!empty($active_groups)) {
				foreach(array_keys($active_groups) as $gid) {
					$filters = $this->graficx_i_filters[$gid][$home_id] ?? [];
					if ($this->isApartmentAllowed($home_id, $section_id, $v['apartment_num'], $filters)) {
						$is_allowed = true;
						break;
					}
				}
			}
			
			if (!$is_allowed) continue;

			if($v['zapis_id']){ $st = ' style="color:#333;" ';}
			else{  $st = ' style="color:#000; font-weight:bold;" '; }
			?><option  <?= $st ?> value="<?= $v['apartment_num'] ?>"><?= $v[
  'apartment_num'
] ?> </option><?
		}
	}
	
	
	 
	
	function act__sel_date_zapisx2()
	{
		// home_id: 26
		// section_id: 1
		// pom: 0
		if($_REQUEST['pom']){$pom=2;}else{$pom=1;}
		$this->get_graficx(1,$pom);
		//print_r($this->graficx_i_za_gr_free[$_GET['home_id']]);
		
		print '<option value="">Выбрать дату</option>';
		foreach( $this->graficx_i_za_gr_free[$_GET['home_id']] as $k=>$v )// [дом][группа][время] = записей 
		{  
			 if($this->graficx_i_za_gr_free2[$k])
			 { 
				// ЗАПРЕТЫ
				$fd = $this->graficx_i_grra2[$k]; // Рассматриваемая дата
				$h = date('H', time()); // текущий час!
				$date_t = date('d.m.Y',time()); // Текущая дата
	 
				$tom_date_ = new DateTime('+1 days'); 
				$tom_date = $tom_date_->format('d.m.Y');// Завтрашняя дата 
				
				$wn = date("w", strtotime($fd)); // номекр дня недели на который рассматриваем запись
				$wn_t = date("w", time()); // номер текущео дня недели

				$next_monday = new DateTime('next monday'); // ближайший понедельник
				$next_mondayt = $next_monday->format('d.m.Y');
				
				
				$iskl =true; // Исключения (true выводим)
				
				
				if($fd == '23.01.2024' && $_POST['home_id'] == "37" && $_POST['section_id']!="1" && $_POST['section_id']!="3" ){$iskl = false;}
				 
				
				
				// print 'h-'.$h.'k-'.$k.'date_t-'.$date_t.'tom_date-'.$tom_date.'<br/>';
				//h-16k-103date_t-28.02.2023tom_date-01.03.2023
				 
				if( !$iskl || ( $h>=16 && ( ( $fd==$date_t || $fd==$tom_date  ) || ($wn_t>4 && $k==$next_mondayt) ) ) ){ continue; } // в выходные после 16 блокируем запись на завтра и на ближайший понедельник
				if(  $fd==$date_t  ){ continue; }  // Запись на текущую дату закрыть 
				
				// Фильтрация квартир по правилам
				$filters = $this->graficx_i_filters[$k][$_REQUEST['home_id']] ?? [];
				if (!$this->isApartmentAllowed($_REQUEST['home_id'], $_REQUEST['section_id'] ?? 0, $_REQUEST['apartament_num'] ?? '', $filters)) {
					continue;
				}
				
				if($iskl)
				{
					print '<option value="'.$this->graficx_i_grra2[$k].'">'.$this->graficx_i_grra2[$k].'</option>';  //
				}				
			 }
		}
	}
	  
	  
	function act__sel_time_zapisx2()
	{
		if($_REQUEST['pom']){$pom=2;}else{$pom=1;}
		if($_REQUEST['dkp']){$dkp=2;}else{$dkp=1;}
		   
		$this->get_graficx(1,$pom,$dkp);
		// act: sel_time_zapisx
		// home_id: 26
		// apartament_num: 6
		// date: 28.02.2023 
		// pom: 0
		// print_r($_REQUEST);
		 
		print '<option value="">Выбрать время</option>';
	  
		// ДОМ ТОЛЬКО В ОДНОЙ ГРУППЕ МОЖЕТ БЫТЬ НА ДАТУ
		$group = $this->graficx_i_grra[$_REQUEST['home_id']][$_REQUEST['date']]; // расшифровка [дом][дата] = группа
	 
		// $group получен и =518
		// print_r($this->graficx_i_za_gr_free2[$group]); // тут Array ( [09:00] => 3 [10:00] => 3 [11:00] => 3 [13:30] => 3 [14:30] => 3 [15:30] => 3 ) ПОЧУМУ 15.30????????
		foreach( $this->graficx_i_za_gr_free2[$group] as $k=>$v )
		{
			 
			$check_date = DateTime::createFromFormat('d.m.Y', $_REQUEST['date']);
			$october_29 = DateTime::createFromFormat('d.m.Y', '29.10.2024'); // Дата для сравнения
			/*
			
			62,58,54,50,46,42,38,34,30,26,22,18,14,10,6,2,1,     
			56,61,57,53,49,45,41,37,33,29,25,21,17,13,9,5,    
			141,136,131,126,121,116,111,106,101,96,91,86,81,76,71,66,  
			142,137,132,127,122,117,112,107,102,97,92,87,82,77,72,67, 
			211,207,203,199,195,191,187,183,179,175,171,167,163,159,155,151,
			277,273,269,265,261,257,253,249,245,241,237,233,229,225,221,217,213
			
			*/
			//Большие квартиры
			$app_nums = [56];
			// Большие квартиры только после 29 октября могут записываться на 15:30 и 16:30
			if( ( $_REQUEST['home_id'] == "46" && in_array( trim($_REQUEST['apartament_num']), $app_nums) ) )
			{
				 
					continue;
				 
			}
			if($check_date < $october_29 ){$x=1;}
			else{$x=2;}
			
			// Фильтрация
			$filters = $this->graficx_i_filters[$group][$_REQUEST['home_id']] ?? [];
			if (!$this->isApartmentAllowed($_REQUEST['home_id'], $_REQUEST['section_id'] ?? 0, $_REQUEST['apartament_num'] ?? '', $filters)) {
				continue;
			}
			
			
			
			if($v)
			{ 
				print '<option value="'.$k.'">'.$k.'</option>';  // 
			}
		}
		 
	}
	 
	
	
	/**
	 * Проверка доступности квартиры по фильтрам
	 */
	function isApartmentAllowed($home_id, $section_id, $apt_num, $filters) {
		$section_id = (int)$section_id;
		$apt_num = trim((string)$apt_num);
		
		$sections_allowed = [];
		$apts_included = [];
		$apts_excluded = [];
		
		if (empty($filters)) return true;
		
		foreach ($filters as $f) {
			if ($f['home_id'] != $home_id) continue;
			
			if ($f['filter_type'] == 'section_include') {
				$sections_allowed[] = $f['filter_value'];
			} elseif ($f['filter_type'] == 'apartment_include') {
				$apts_included[] = $f['filter_value'];
			} elseif ($f['filter_type'] == 'apartment_exclude') {
				$apts_excluded[] = $f['filter_value'];
			}
		}
		
		if (!empty($sections_allowed) && !in_array($section_id, $sections_allowed)) return false;
		if (!empty($apts_included) && !in_array($apt_num, $apts_included)) return false;
		if (!empty($apts_excluded) && in_array($apt_num, $apts_excluded)) return false;
		
		return true;
	}

	function act__test() {
    global $mysql;

    // 1. Получаем все параметры из GET и POST запросов
$_GET['home_id'] = 45;
$_GET['section_id'] = 1;
$_GET['apartament_num'] =1 ;


$_GET['date'] = '29.07.2025';
    // Параметры GET
    $home_id = isset($_GET['home_id']) ? (int)$_GET['home_id'] : null;  // ID дома
    $section_id = isset($_GET['section_id']) ? (int)$_GET['section_id'] : null;  // ID секции
    $apartament_num = isset($_GET['apartament_num']) ? (int)$_GET['apartament_num'] : null;  // Номер квартиры
    $date = isset($_GET['date']) ? $_GET['date'] : null;  // Дата для проверки
    $pom = isset($_GET['pom']) ? (int)$_GET['pom'] : null;  // Помогающая компания (1 - да, 0 - нет)
    $dkp = isset($_GET['dkp']) ? (int)$_GET['dkp'] : null;  // ДКП (1 - да, 0 - нет)
    
    // Параметры POST
    if ($_POST) {
        // Пример получения данных из POST, если они передаются
        $home_id = isset($_POST['home_id']) ? (int)$_POST['home_id'] : $home_id;
        $section_id = isset($_POST['section_id']) ? (int)$_POST['section_id'] : $section_id;
        $apartament_num = isset($_POST['apartament_num']) ? (int)$_POST['apartament_num'] : $apartament_num;
        $date = isset($_POST['date']) ? $_POST['date'] : $date;
        $pom = isset($_POST['pom']) ? (int)$_POST['pom'] : $pom;
        $dkp = isset($_POST['dkp']) ? (int)$_POST['dkp'] : $dkp;
    }

    // Выводим полученные параметры для отладки
    echo "<pre>";
    echo "GET параметры:\n";
    print_r($_GET);
    echo "\nPOST параметры:\n";
    print_r($_POST);
    echo "\nИспользуемые параметры:\n";
    echo "home_id: $home_id\n";
    echo "section_id: $section_id\n";
    echo "apartament_num: $apartament_num\n";
    echo "date: $date\n";
    echo "pom: $pom\n";
    echo "dkp: $dkp\n";

    // 2. Проверяем корректность переданных параметров
    if (!$home_id || !$date) {
        echo "Не переданы обязательные параметры: home_id или date.";
        return;
    }

    // 3. Запрашиваем график для указанного дома, секции и даты
    $this->get_graficx(1, $pom, $dkp);  // Получаем график с фильтрацией по пом и дкп

    // 4. Выводим основные массивы с данными
    echo "\n\nОсновные данные графика:\n";

    // Проверяем, есть ли данные по домам для этой даты
    if (isset($this->graficx_i_gr_c[$home_id])) {
        echo "График для дома $home_id:\n";
        print_r($this->graficx_i_gr_c[$home_id]);  // Массив с количеством мест для дома
    } else {
        echo "Нет данных для дома $home_id на дату $date.\n";
    }

    // Проверяем массив с количеством свободных мест
    echo "\nКоличество свободных мест по группам и времени:\n";
    if (isset($this->graficx_i_gr_c2[$date])) {
        echo "Места для группы по дате $date:\n";
        print_r($this->graficx_i_gr_c2[$date]);  // Массив мест по группе и времени
    } else {
        echo "Нет данных для группы на дату $date.\n";
    }

    // 5. Проверка занятости по времени для указанной даты
    echo "\nПроверка занятости по времени:\n";
    if (isset($this->graficx_i_za_gr_free[$home_id][$date])) {
        echo "Свободные места для дома $home_id на дату $date:\n";
        print_r($this->graficx_i_za_gr_free[$home_id][$date]);  // Массив свободных мест для дома на указанную дату
    } else {
        echo "Нет свободных мест для дома $home_id на дату $date.\n";
    }

    // 6. Проверка наличия записей на указанную дату
    echo "\nЗаписи на указанную дату:\n";
    $q = 'SELECT zapis.* FROM zapis WHERE `date` = "' . $date . '" AND home_id = "' . $home_id . '" AND del = "0"';
    $data = $mysql->get_arr($q);  // Получаем записи на указанную дату для дома
    print_r($data);  // Выводим записи, если они есть

    // 7. Проверка занятости квартиры на указанную дату и время
    echo "\nПроверка занятости квартиры $apartament_num на указанную дату и время:\n";
    $ti = '10:00';  // Пример времени, можно заменить на динамическое
    $free = $this->act__testfree($home_id, $date, $ti);  // Проверка свободности времени для квартиры
    echo "Свободно: " . ($free ? "Да" : "Нет") . "\n";
    
    echo "</pre>";
}

 
 
############## /ЗАПИСЬ
  
 
 
 
 
 function mounthmenu()
 {
		if(!$_GET['month']){	$tm = date('m');	}	
		else{	$tm = $_GET['month'];	}	
		
		if(!$_GET['year']){	$ty = date('Y');	}
		else{	$ty = $_GET['year'];	}	
						
		$y_now = date('Y');

		print '<b>Год:</b> ';
		for( $y = $y_now - 2; $y <= $y_now; $y++ )
		{
			if($y == $ty){$st=' style="font-weight:bold; font-size: 16px;" ';}
			else{$st='';}
			print ' / <a href="?ctr=zapisx2&month='.$tm.'&year='.$y.'" '.$st.'>'.$y.'</a>  ';
		}
		
		print ' &nbsp;&nbsp;&nbsp;&nbsp; ';

		print '<b>Месяц:</b> ';
		for( $m=1; $m<=12; $m++ )
		{
			if(strlen($m)<2){$m = '0'.$m;} 
			if($m == $tm){$st=' style="font-weight:bold; font-size: 16px;" ';}
			else{$st='';}
			print ' / <a href="?ctr=zapisx2&month='.$m.'&year='.$ty.'" '.$st.'>'.$m.'</a>  ';
		}
		
		print '<br/><br/>';
 }
 
 
 
	function act__index()
	{
		global $t;
		$t['h1']='Редактор графика';
		$this->get_graficx(false,false,false,1); // 
		
		$this->mounthmenu();
		
		if($_GET['month']){$mounth=$_GET['month'];}
		else{ $mounth = date('m'); }
		
		if($_GET['year']){$year=$_GET['year'];}
		else{ $year = date('Y'); }
		 
		
		?>
		<style>
		.geditor_frame{  }
		.geditor_day{display: block; border:solid 1px #000; width:14%; min-width:100px; height:250px; overflow: hidden; position:relative; float:left; margin:1px; }
		.geditor_dayna{display:block; border:solid 1px #EEE; width:14%; min-width:100px; height:250px;  position:relative; float:left; margin:1px;}
		.geditor_daytop {background:#00CDAD;  color:#FFF; font-size:12px; padding:1px; text-align:right; padding-right:10px; z-index: 10;}
		.geditor_day_content { height: 210px; overflow-y: auto; overflow-x: hidden; padding-bottom: 20px; }
		.geditor_gr{border:solid 1px #000;  font-size:12px; margin:1px;}
		.geditor_gr_title{border:solid 1px #2F4049; padding:1px; font-size:12px; background:#2F4049; color:#00CDAD; }
		.geditor_gr_body{ padding: 3px;}
		.geditor_gr_editlink{background:#FFF; color:red; padding-left:3px; padding-right:3px;}
		.gret{border-bottom:solid 1px #EEE; }
		.gtt{width:40px; display:inline-block;}
		.ajax_status{font-size:12px;}
		.home_check{display: inline-block; border:1px solid #000; padding:3px;}
		.gr_homes{
			background:#CCC; color:#000; font-size: 12px; 
			border-radius: 10px 10px 0 0;
			padding-left: 10px;
			padding-right: 10px;
			margin: 1px; position:relative;
			}
		.gr_gr{border:solid 1px  #999; border-radius:10px; width:98%; margin:2px; }
		.gr_gr_hide{border:solid 1px  #999; border-radius:10px; width:98%; margin:2px; opacity:0.6; }
		.gr_times{font-size:12px; padding:3px; }
		.gr_time{border-bottom:1px solid #CCC; font-size:12px; cursor:default;}
		.gr_time:hover{background:#F0F0FF; }
		
		.gr_time_over{color:red; font-size:10px;}
		.gr_add_link{position:absolute; bottom:0; left:0; display:block; text-align:center; width:100%; border-top:solid 1px red; background:#000; color:#FFF; font-size:10px; z-index: 100; padding: 2px 0;}
		
		
		.gr_edit_link{  width:100%; border-radius:20px; color:#000;  }
		
		
		.del_link{     }
		
		.gr_realc{color:green; font-weight:bold;}
		
		.daypanellink{color:red;}
		.daypanellink2{color:#000;}
		
		.badge-filter { display: inline-block; width: 7px; height: 7px; border-radius: 50%; margin-left: 2px; border: 1px solid rgba(0,0,0,0.1); vertical-align: middle; }
		.badge-section { background: #3b82f6; }
		.badge-apartment { background: #ef4444; }
		.badge-vip { background: #fbbf24; }
		</style>
 
		<div class="geditor_frame">
		<?
		
		$start = strtotime('01.'.$mounth.'.'.$year);
		$finish = strtotime('+1 MONTH', $start );
		//$finish = strtotime(date('Y-m-t'));
 
		for($i = $start; $i < $finish; $i += 86400)// Цикл по суткам
		{ 
			$day_date = date('d.m.Y', $i); // Текущий день в виде даты
			$dn = date("w",$i); // Номер дня недели
			if($dn==0 || $dn==6 ){ $dcolor = 'red'; }else{ $dcolor = '#FFF;';}
		 
			// Пропускаем пустые дни с начала месяца 
			if($i==$start && $dn>1)
			{
				for($k=1; $k<$dn; $k++){ print ' <div class="geditor_dayna"> &nbsp; </div> '; }
			}
			?>
				<div class="geditor_day">
					<div class="geditor_daytop" style="color:<?= $dcolor ?>">
						<b>
						<?= $this->w_rus[$dn] ?> / 
						<?= $day_date ?>
						</b> 
					</div>
					<?= $this->disp_gr_day($day_date) ?>
				</div>
			<?
			//if( $dn == 0 ){ print ' &nbsp; '; }
		}
		?></div>
		
		<script>
		$('.delconfirm').click(function(){
			return confirm("Вы действительно хотите удалить элемент?");
		})
		</script>
		<?			
	}
	
	
	
 
	// ОТОБРАЖЕНИЕ ДНЯ ГРАФИКА
	function disp_gr_day( $date='' )
	{
		global $r;
		print '<div class="geditor_day_content">';
		// print $date;
		/*
			$this->graficx_i_date[$v['date']][$v['keys_graficx_date_id']]=1; // [дата][] = группа
			$this->graficx_i_homes[$v['keys_graficx_date_id']][$v['object_id']]=1; // [группа][]=дом
			$this->graficx_i_times[$v['keys_graficx_date_id']][$v['time']]=$v['c']; // [группа][время]=количество записей
			
			$this->zapis_i_dth[$v['date']][$ti][$v['home_id']]=$v; //[дата][время][дом]=записи
			$this->zapis_i_dt[$v['date']][$ti][]=$v; //[дата][время] =записи
		*/
		foreach($this->graficx_i_date[$date] as $k=>$v) // Ид групп полуачем
		{
			if($this->graficx_i_date_b[$k]['show']){ $grclassx='gr_gr';	}
			else{ $grclassx='gr_gr_hide'; }
			
			// выводим группу
			?><div class="<?= $grclassx ?>"><?
				// print 'Группа'.$k.'<br/>';
				?>
				<div class="gr_homes"><?
				//Выводим дома 
				foreach( $this->graficx_i_homes[$k] as $k1 => $v1 )
				{
					 print '<b>'.$this->select_homes[$k1].'</b>';
					 
					 // Индикаторы фильтров
					 if (!empty($this->graficx_i_filters[$k][$k1])) {
						 $tt_parts = [];
						 $has_sect = false; $has_excl = false; $has_incl = false;
						 
						 $sects = []; $excls = []; $incls = [];
						 foreach($this->graficx_i_filters[$k][$k1] as $f) {
							 if ($f['filter_type'] == 'section_include') { $has_sect = true; $sects[] = $f['filter_value']; }
							 if ($f['filter_type'] == 'apartment_exclude') { $has_excl = true; $excls[] = $f['filter_value']; }
							 if ($f['filter_type'] == 'apartment_include') { $has_incl = true; $incls[] = $f['filter_value']; }
						 }
						 
						 if ($has_sect) $tt_parts[] = "Подъезды: " . implode(', ', $sects);
						 if ($has_excl) $tt_parts[] = "Исключены: " . implode(', ', $excls);
						 if ($has_incl) $tt_parts[] = "Только VIP: " . implode(', ', $incls);
						 
						 $full_tt = implode(" | ", $tt_parts);
						 
						 if ($has_sect) echo '<span class="badge-filter badge-section" title="'.$full_tt.'"></span>';
						 if ($has_excl) echo '<span class="badge-filter badge-apartment" title="'.$full_tt.'"></span>';
						 if ($has_incl) echo '<span class="badge-filter badge-vip" title="'.$full_tt.'"></span>';
					 }
					 echo ' ';
				}
				?>
				
				</div>
				
				<div class="gr_times"><?
				// Собираем все слоты дня по времени: в одной строке БД может быть
				// микс ёмкостей (c + pom + dkp) — для каждой ненулевой выводим
				// отдельную линию, чтобы не терять «С ПО» / «ДКП».
				$slot_times = array_keys((array)($this->graficx_i_times[$k] ?? []));
				$slot_times = array_unique(array_merge(
					$slot_times,
					array_keys((array)($this->graficx_i_pom[$k] ?? [])),
					array_keys((array)($this->graficx_i_dkp[$k] ?? []))
				));
				sort($slot_times);

				$counts_all = $this->zapis_counts[$k] ?? [];
				$has_multi_groups = count((array)($this->graficx_i_date[$date] ?? [])) > 1;

				foreach($slot_times as $k2)
				{
					$std_cap = (int)($this->graficx_i_times[$k][$k2] ?? 0);
					$pom_cap = (int)($this->graficx_i_pom[$k][$k2] ?? 0);
					$dkp_cap = (int)($this->graficx_i_dkp[$k][$k2] ?? 0);

					$counts = $counts_all[$k2] ?? [];
					$total_global = count((array)($this->zapis_i_dt[$date][$k2] ?? []));

					// Список линий, которые надо отрисовать
					$rows = [];
					if ($std_cap > 0) {
						$rows[] = ['cap' => $std_cap, 'label' => '',                'cnt' => (int)($counts['std'] ?? 0)];
					}
					if ($pom_cap > 0) {
						$rows[] = ['cap' => $pom_cap, 'label' => ' <b> - П</b>',   'cnt' => (int)($counts['pom'] ?? 0)];
					}
					if ($dkp_cap > 0) {
						$rows[] = ['cap' => $dkp_cap, 'label' => ' <b> - ДКП</b>', 'cnt' => (int)($counts['dkp'] ?? 0)];
					}
					// Fallback: все ёмкости 0, но строка в графике существует — оставляем 1 линию для отрисовки
					if (empty($rows)) {
						$rows[] = ['cap' => 0, 'label' => '', 'cnt' => (int)($counts['std'] ?? 0)];
					}

					foreach($rows as $row) {
						print '<div class="gr_time">'.$k2.'-'.$row['cap'].$row['label'];
						print ' / <span class="gr_realc" style="color:green; font-weight:bold; font-size:13px;" title="Соответствует фильтрам этой группы">'.$row['cnt'].'</span>';
						if ($total_global > 0 && $has_multi_groups) {
							print ' <span class="gr_realc" style="color:#999; font-size:10px;" title="Общее количество записей на это время по всем группам">('.$total_global.')</span>';
						}
						print '</div>';
					}
				}
				?>
				</div>
				<div style="text-align:right; width:100%;"> 
				
				<?
				if($this->graficx_i_date_b[$k]['show'])
				{
				?>
				<a class="gr_edit_link daypanellink" href="<?= $r->acturl(
      'zapisx2',
      'h_daygroup'
    ) ?>&date=<?= $date ?>&id=<?= $k ?>&month=<?= $_GET[
  'month'
] ?>"><span class="mdi mdi-eye fs21"></span></a> 
				<?
				}
				elseif(!$this->graficx_i_date_b[$k]['show'])
				{ 
				?>
				<a class="gr_edit_link daypanellink2" href="<?= $r->acturl(
      'zapisx2',
      's_daygroup'
    ) ?>&date=<?= $date ?>&id=<?= $k ?>&month=<?= $_GET[
  'month'
] ?>"><span class="mdi mdi-eye-off fs21"></span></a> 
				<?
				}
				?>
				
				<a class="iframe_r gr_edit_link daypanellink" href="<?= $r->acturl(
      'zapisx2',
      'edit_daygroup',
      'iframe_router.php'
    ) ?>&date=<?= $date ?>&id=<?= $k ?>"><span class="mdi mdi-playlist-edit fs21"></span></a> 
				<a class="del_link delconfirm daypanellink" href="<?= $r->acturl(
      'zapisx2',
      'edit_daygroup'
    ) ?>&act=del&id=<?= $k ?>&month=<?= $_GET[
  'month'
] ?>"><span class="mdi mdi-delete-circle-outline fs21"></span></a> 
				</div>
				<?
			?></div><?
		}
		
		
		foreach($this->zapis_i_dtnr[$date]  as $kx =>$vx)
		{	
			$tt='';
			foreach($vx as $vx1=>$vx2)
			{
				$tt ='д.'.$this->select_homes[$vx2['home_id']].' '; 
				$tt.=' кв.'.$vx2['apartment_num'].' ';
				$tt.=$vx2['fio'];
			}
			 
			print '<div class="gr_time gr_time_over" rel="tooltip" title="'.$tt.'">';
			print $kx;
			print ' / <span class="gr_realc gr_time_over">'.count((array)($this->zapis_i_dtnr[$date][$kx] ?? [])).'</span>';
			print '</div>';
		}
		print '</div>';
		
		?>  
		<a class="iframe_r gr_add_link" href="<?= $r->acturl(
    'zapisx2',
    'edit_daygroup',
    'iframe_router.php'
  ) ?>&date=<?= $date ?>">добавить</a><?
	}
	
	
	
	
	
	   
	# РЕДАКТИРОВАНИЕ ДОБАВЛЕНИЕ ГРУППЫ ОБЕКТОВ В ДНЕ
	function act__edit_daygroup()
	{
		global $filed;
		global $mysql;
		
		$date = $_GET['date'];
		$id =  $_GET['id'];
		
		
		//print '<pre>';
		//print_r($_POST);
		//print '</pre>';
		
		
		$validation_error = '';
		if ($_POST) {
			// 1. Проверка наличия домов
			if (empty($_POST['objects']) || !array_filter($_POST['objects'])) {
				$validation_error = 'Выберите хотя бы один дом для создания правила';
			}
			
			// 2. Проверка наличия временных слотов с мощностью
			if (!$validation_error) {
				$has_capacity = false;
				foreach ($_POST['times'] ?? [] as $time => $cap) {
					if ((int)$cap > 0 || (int)($_POST['times_pom'][$time] ?? 0) > 0 || (int)($_POST['times_dkp'][$time] ?? 0) > 0) {
						$has_capacity = true;
						break;
					}
				}
				if (!$has_capacity) {
					$validation_error = 'Укажите хотя бы один временной слот с доступным количеством записей';
				}
			}

			// 3. Проверка конфликтов фильтров квартир
			if (!$validation_error) {
				foreach ($_POST['objects'] as $home_id => $checked) {
					if (!$checked) continue;
					
					$excl = $_POST['exclude_apartments'][$home_id] ?? [];
					$incl = $_POST['include_apartments'][$home_id] ?? [];
					
					if (!is_array($excl)) $excl = [];
					if (!is_array($incl)) $incl = [];
					
					// Ищем пересечения
					$conflicts_apts = array_intersect($excl, $incl);
					if (!empty($conflicts_apts)) {
						$home_name = $this->select_homes[$home_id] ?? "Дом #{$home_id}";
						$validation_error = "Конфликт фильтров для {$home_name}: квартиры " . implode(', ', $conflicts_apts) . " одновременно в списках 'Исключить' и 'Только VIP'. Уберите дубли.";
						break;
					}
				}
			}

			// 4. Проверка конфликтов (дублирующиеся правила)
			if (!$validation_error) {
				$conflicts = $this->checkRuleConflicts($_GET['date'], $_POST['objects'], $id);
				if (!empty($conflicts)) {
					$conflict_ids = array_unique(array_column($conflicts, 'group_id'));
					$validation_error = 'Обнаружены конфликты: этот дом уже используется в другом правиле на эту дату (#' . implode(', #', $conflict_ids) . ').';
				}
			}

			if (!$validation_error) {
				// Начинаем транзакцию
				$mysql->sql('START TRANSACTION');
				
				try {
				// Добавляем новую запись на дату (если новая группа)
				if (!$id) {
					$date_insert = array();
					$date_insert['date'] = $_GET['date'];
					$date_insert['show'] = "0";
					$date_insert['date_mysql'] = date('Y-m-d', strtotime($_GET['date']));
					$id = $mysql->insert('keys_graficx_date', $date_insert, false);
				}
				
				if (!$id) {
					throw new Exception('Ошибка добавления записи');
				}
				
				// Удаляем все записи домов для группы
				$mysql->sql(' DELETE FROM keys_graficx_objects WHERE keys_graficx_date_id="' . $id . '" ');
				
				// Добавляем записи домов для группы
				foreach ($_POST['objects'] as $k => $v) {
					if ($v) {
						$date_insert = array();
						$date_insert['keys_graficx_date_id'] = $id;
						$date_insert['object_id'] = $k;
						$mysql->insert('keys_graficx_objects', $date_insert, false);
					}
				}
				
				// Удаляем записи времени для группы
				$mysql->sql(' DELETE FROM keys_graficx_time WHERE keys_graficx_date_id="' . $id . '" ');
				
				// Добавляем записи времени для группы
				foreach ($_POST['times'] as $k => $v) {
					if ($v || ($_POST['times_pom'][$k] ?? 0) || ($_POST['times_dkp'][$k] ?? 0)) {
						$date_insert = array();
						$date_insert['keys_graficx_date_id'] = $id;
						$date_insert['time'] = $k;
						$date_insert['c'] = (int)$v;
						$date_insert['pom'] = (int)($_POST['times_pom'][$k] ?? 0);
						$date_insert['dkp'] = (int)($_POST['times_dkp'][$k] ?? 0);
						
						$mysql->insert('keys_graficx_time', $date_insert, false);
					}
				}

				// --- СОХРАНЕНИЕ ФИЛЬТРОВ ---
				$mysql->sql(' DELETE FROM keys_graficx_filters WHERE keys_graficx_date_id="' . $id . '" ');
				
				foreach ($_POST['objects'] as $home_id => $checked) {
					if (!$checked) continue;
					
					// 1. Фильтр секций
					if (isset($_POST['section_mode'][$home_id]) && $_POST['section_mode'][$home_id] === 'include') {
						if (!empty($_POST['sections'][$home_id])) {
							foreach ($_POST['sections'][$home_id] as $section_id) {
								$filter_data = [
									'keys_graficx_date_id' => $id,
									'home_id' => $home_id,
									'filter_type' => 'section_include',
									'filter_value' => $section_id
								];
								$mysql->insert('keys_graficx_filters', $filter_data, false);
							}
						}
					}
					
					
					// Предварительно получаем список доступных квартир для выбранных секций
					$available_apts = [];
					$q_avail = 'SELECT apartment_num FROM apartaments WHERE home_id = "'.(int)$home_id.'"';
					
					// Если включен фильтр по секциям
					if (isset($_POST['section_mode'][$home_id]) && $_POST['section_mode'][$home_id] === 'include' && !empty($_POST['sections'][$home_id])) {
						$sec_ids = array_map('intval', $_POST['sections'][$home_id]);
						$q_avail .= ' AND section_id IN (' . implode(',', $sec_ids) . ')';
					}
					
					$avail_res = $mysql->get_arr($q_avail);
					$available_apts = array_column($avail_res, 'apartment_num');
					
					// 2. Исключение квартир
					if (!empty($_POST['exclude_apartments'][$home_id])) {
						$apartments = $_POST['exclude_apartments'][$home_id];
						if (!is_array($apartments)) {
							$apartments = array_map('trim', explode(',', $apartments));
						}
						foreach ($apartments as $apt) {
							if ($apt === '') continue;
							if (!in_array($apt, $available_apts)) continue;
							
							$mysql->insert('keys_graficx_filters', [
								'keys_graficx_date_id' => $id,
								'home_id' => $home_id,
								'filter_type' => 'apartment_exclude',
								'filter_value' => $apt
							], false);
						}
					}
					
					// 3. Только квартиры (include)
					if (!empty($_POST['include_apartments'][$home_id])) {
						$apartments = $_POST['include_apartments'][$home_id];
						if (!is_array($apartments)) {
							$apartments = array_map('trim', explode(',', $apartments));
						}
						foreach ($apartments as $apt) {
							if ($apt === '') continue;
							if (!in_array($apt, $available_apts)) continue;

							$mysql->insert('keys_graficx_filters', [
								'keys_graficx_date_id' => $id,
								'home_id' => $home_id,
								'filter_type' => 'apartment_include',
								'filter_value' => $apt
							], false);
						}
					}
				}
				
				// Коммитим транзакцию
				$mysql->sql('COMMIT');
				
			} catch (Exception $e) {
				// Откатываем изменения при ошибке
				$mysql->sql('ROLLBACK');
				die('Ошибка сохранения правила: ' . $e->getMessage());
			}
		}
	}
	 
		
		// Ид Записи если ест редактирование 
		if( $id )
		{
		$data_objects = array(); // [ид]=1/0
		$data_times=array(); //[время] = записей
		
		// Если была ошибка валидации, используем данные из POST
		if (!empty($validation_error) && $_POST) {
			$data_objects = $_POST['objects'] ?? [];
			foreach ($_POST['times'] ?? [] as $time => $cap) {
				$data_times[$time] = (int)$cap;
				$data_times_pom[$time] = (int)($_POST['times_pom'][$time] ?? 0);
				$data_times_dkp[$time] = (int)($_POST['times_dkp'][$time] ?? 0);
			}
		} else {
			$q = ' SELECT * FROM keys_graficx_objects WHERE `keys_graficx_date_id`="'.$id.'" ' ;
			$arr = $mysql->get_arr($q);
			 
			foreach($arr as $k=>$v)
			{
				$data_objects[$v['object_id']]=1;
			}
		 
			$q = '  SELECT * FROM keys_graficx_time WHERE `keys_graficx_date_id`="'.$id.'"  ' ;
			$arr = $mysql->get_arr( $q );
			foreach($arr as $k=>$v)
			{
				$data_times[$v['time']]=$v['c'];
				$data_times_pom[$v['time']]=$v['pom'];
				$data_times_dkp[$v['time']]=$v['dkp'];
			}
		}

			// Загружаем фильтры
			$data_filters = [];
			
			// Если была ошибка валидации, используем данные из POST
			if (!empty($validation_error) && $_POST) {
				foreach ($_POST['objects'] ?? [] as $home_id => $checked) {
					if (!$checked) continue;
					
					if (!empty($_POST['sections'][$home_id])) {
						$data_filters[$home_id]['sections'] = $_POST['sections'][$home_id];
					}
					if (!empty($_POST['exclude_apartments'][$home_id])) {
						$data_filters[$home_id]['exclude_apartments'] = $_POST['exclude_apartments'][$home_id];
					}
					if (!empty($_POST['include_apartments'][$home_id])) {
						$data_filters[$home_id]['include_apartments'] = $_POST['include_apartments'][$home_id];
					}
				}
			} else {
				$q = 'SELECT * FROM keys_graficx_filters WHERE keys_graficx_date_id="' . $id . '"';
			$arr_f = $mysql->get_arr($q);
			foreach ($arr_f as $f) {
				$hid = $f['home_id'];
				$type = $f['filter_type'];
				$val = $f['filter_value'];
				if ($type == 'section_include') {
					$data_filters[$hid]['sections'][] = $val;
				} elseif ($type == 'apartment_exclude') {
					$data_filters[$hid]['exclude_apartments'][] = $val;
				} elseif ($type == 'apartment_include') {
					$data_filters[$hid]['include_apartments'][] = $val;
				}
				}
			}
		}
		
		print '<div class="rule-editor-container" style="padding:10px 20px;">';
		if (!empty($validation_error)) {
			echo '<div style="background:#fff5f5; color:#c53030; padding:15px; border-radius:8px; border:1px solid #feb2b2; margin-bottom:20px; font-weight:600;">⚠️ ' . $validation_error . '</div>';
		}
		?>
		<style>
		.rule-editor { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; max-width: 1000px; margin: 0 auto; color: #333; }
		.editor-header { background: #f4f7f6; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 5px solid #00cdad; }
		.section-title { font-weight: bold; font-size: 1.1em; color: #2f4049; margin: 20px 0 10px; display: block; text-transform: uppercase; border-bottom: 2px solid #eee; padding-bottom: 5px; }
		
		.homes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px; }
		.home-card { border: 1px solid #ddd; border-radius: 8px; padding: 12px; background: #fff; transition: all 0.2s; }
		.home-card.active { border-color: #00cdad; box-shadow: 0 2px 8px rgba(0,205,173,0.15); }
		.home-card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
		.home-card-header label { font-weight: bold; cursor: pointer; display: flex; align-items: center; gap: 8px; margin-bottom: 0; }
		
		.home-filters { border-top: 1px solid #eee; padding-top: 10px; display: none; margin-top: 10px; font-size: 0.9em; }
		.home-card.active .home-filters { display: block; }
		
		.filter-row { margin-bottom: 15px; }
		.filter-row label { display: block; font-size: 0.85em; color: #666; margin-bottom: 6px; }
		.section-list { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px; }
		.section-badge { background: #f8f9fa; border-radius: 4px; padding: 4px 10px; cursor: pointer; font-size: 0.85em; border: 1px solid #dee2e6; color: #495057; }
		.section-badge.selected { background: #00cdad; color: #fff; border-color: #00cdad; box-shadow: 0 2px 4px rgba(0,205,173,0.2); }
		
		.apt-selector { margin-top: 10px; border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; }
		.apt-selector-header { background: #f8f9fa; padding: 6px 10px; font-size: 0.8em; font-weight: 600; color: #4a5568; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
		.apt-list-container { max-height: 150px; overflow-y: auto; padding: 5px; display: grid; grid-template-columns: repeat(auto-fill, minmax(60px, 1fr)); gap: 4px; background: #fff; }
		.apt-item { font-size: 0.75em; display: flex; align-items: center; gap: 3px; border: 1px solid #edf2f7; padding: 2px 4px; border-radius: 3px; cursor: pointer; transition: background 0.1s; }
		.apt-item:hover { background: #f7fafc; }
		.apt-item.selected { background: #ebf8ff; border-color: #bee3f8; }
		.apt-item label { margin: 0; cursor: pointer; }
		
		.time-table { width: 100%; border-collapse: collapse; margin: 20px 0; background: #fff; border-radius: 12px; overflow: hidden; border: 1px solid #e9ecef; }
		.time-table th { background: #2f4049; color: #fff; text-align: left; padding: 12px 15px; font-size: 0.85em; font-weight: 500; }
		.time-table td { padding: 10px 15px; border-bottom: 1px solid #f1f3f5; vertical-align: middle; }
		.time-row:hover { background: #f8f9fa; }
		.cap-input { width: 60px; padding: 6px; border: 1px solid #ced4da; border-radius: 4px; text-align: center; font-weight: 500; }
		.row-total { font-weight: 600; color: #00cdad; font-size: 1.1em; }
		
		.conflict-box { margin-top: 30px; padding: 20px; border-radius: 10px; background: #fff5f5; border: 1px solid #feb2b2; }
		.conflict-title { color: #c53030; font-weight: 700; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; font-size: 1.1em; }
		.conflict-item { background: #fff; border: 1px solid #fed7d7; border-radius: 8px; padding: 15px; margin-bottom: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.03); }
		.conflict-header { border-bottom: 1px solid #f1f3f5; padding-bottom: 10px; margin-bottom: 10px; font-weight: 700; color: #2d3748; display: flex; justify-content: space-between; align-items: center; }
		.badge { padding: 3px 8px; border-radius: 12px; font-size: 0.75em; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
		.badge-info { background: #ebf8ff; color: #2b6cb0; border: 1px solid #bee3f8; }
		.badge-warning { background: #fffaf0; color: #9c4221; border: 1px solid #feebc8; }
		.conflict-actions { margin-top: 12px; display: flex; gap: 12px; }
		
		.btn-mini { padding: 6px 12px; font-size: 0.85em; border-radius: 6px; cursor: pointer; border: 1px solid #e2e8f0; background: #fff; text-decoration: none; color: #4a5568; font-weight: 500; display: inline-flex; align-items: center; transition: all 0.2s; }
		.btn-mini:hover { background: #edf2f7; border-color: #cbd5e0; }
		.btn-view { color: #2b6cb0; border-color: #bee3f8; }
		.btn-view:hover { background: #ebf8ff; }
		.btn-del { color: #c53030; border-color: #feb2b2; }
		.btn-del:hover { background: #fff5f5; }
		
		.submit-panel { position: sticky; bottom: 0; background: rgba(255,255,255,0.95); backdrop-filter: blur(5px); padding: 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: flex-end; gap: 15px; z-index: 1000; box-shadow: 0 -4px 15px rgba(0,0,0,0.05); }
		.btn-cancel { padding: 10px 25px; border: 1px solid #cbd5e0; background: #fff; color: #4a5568; border-radius: 8px; font-weight: 600; cursor: pointer; transition: all 0.2s; }
		.btn-cancel:hover { background: #f7fafc; }
		.btn-save { background: #00cdad; color: #fff; border: none; padding: 12px 40px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 6px rgba(0,205,173,0.2); }
		.btn-save:hover { background: #00b396; transform: translateY(-1px); box-shadow: 0 6px 12px rgba(0,205,173,0.25); }
		.btn-save:active { transform: translateY(0); }
		</style>
		
		<div class="rule-editor">
			<form action="" method="POST" id="rule-form">
				<div class="editor-header">
					<h3 style="margin:0; color:#2f4049;">Редактирование группы: <?= $_GET['date'] ?></h3>
					<?php if($id): ?><small style="color:#718096">ID правила: #<?= $id ?></small><?php endif; ?>
				</div>

				<span class="section-title">1. Объекты и ограничения</span>
				<div class="homes-grid">
				<?php foreach($this->homes_arr as $k => $v): 
					$isActive = !empty($data_objects[$k]);
					$q_s = 'SELECT * FROM homes_sections WHERE homes_id = "'.(int)$v['homes_id'].'" ORDER BY caption';
					$sections = $mysql->get_arr($q_s);
				?>
					<div class="home-card <?= $isActive ? 'active' : '' ?>" id="home-card-<?= $k ?>">
						<div class="home-card-header">
							<label>
								<input type="checkbox" name="objects[<?= $k ?>]" value="1" <?= $isActive ? 'checked' : '' ?> onclick="toggleHome(<?= $k ?>)">
								Дом №<?= $v['title'] ?>
							</label>
							<span style="color:#a0aec0; font-size: 0.8em; font-weight:500;">#<?= $v['home_id'] ?></span>
						</div>
						
						<div class="home-filters">
							<div class="filter-row">
								<label>Доступные подъезды:</label>
								<div style="display:flex; gap: 15px; margin-bottom: 8px;">
									<label style="font-size: 0.9em; font-weight:500; display:flex; align-items:center; gap:5px;">
										<input type="radio" name="section_mode[<?= $k ?>]" value="all" <?= empty($data_filters[$k]['sections']) ? 'checked' : '' ?> onclick="toggleSections(<?= $k ?>, false)"> Все
									</label>
									<label style="font-size: 0.9em; font-weight:500; display:flex; align-items:center; gap:5px;">
										<input type="radio" name="section_mode[<?= $k ?>]" value="include" <?= !empty($data_filters[$k]['sections']) ? 'checked' : '' ?> onclick="toggleSections(<?= $k ?>, true); updateApartments(<?= $k ?>)"> Выбранные
									</label>
								</div>
								<div class="section-list" id="section-list-<?= $k ?>" style="<?= empty($data_filters[$k]['sections']) ? 'display:none' : 'display:flex' ?>">
									<?php foreach($sections as $s): 
										$isSSelected = !empty($data_filters[$k]['sections']) && in_array($s['section_id'], $data_filters[$k]['sections']);
									?>
										<label class="section-badge <?= $isSSelected ? 'selected' : '' ?>">
											<input type="checkbox" name="sections[<?= $k ?>][]" value="<?= $s['section_id'] ?>" style="display:none" <?= $isSSelected ? 'checked' : '' ?> onchange="this.parentElement.classList.toggle('selected'); updateApartments(<?= $k ?>)">
											<?= $s['caption'] ?>
										</label>
									<?php endforeach; ?>
								</div>
							</div>
							
							<div class="filter-row">
								<label>Исключить квартиры:</label>
								<div class="apt-selector">
									<?php $has_excl_apts = !empty($data_filters[$k]['exclude_apartments']); ?>
									<div class="apt-selector-header">
										<span>Доступные квартиры</span>
										<a href="javascript:void(0)" onclick="toggleApts(this)" style="font-size:0.9em; text-decoration:none;"><?= $has_excl_apts ? 'свернуть' : 'развернуть' ?></a>
									</div>
									<div class="apt-list-container" id="exclude-list-<?= $k ?>" style="<?= $has_excl_apts ? 'display:grid' : 'display:none' ?>">
										<?php 
										$q_apts = 'SELECT apartment_num FROM apartaments WHERE home_id = "'.(int)$k.'"';
										if (!empty($data_filters[$k]['sections'])) {
											$q_apts .= ' AND section_id IN ('.implode(',', array_map('intval', $data_filters[$k]['sections'])).')';
										}
										$q_apts .= ' ORDER BY apartment_num';
										$apts = $mysql->get_arr($q_apts);
										
										foreach($apts as $apt): 
											$isExcl = !empty($data_filters[$k]['exclude_apartments']) && in_array($apt['apartment_num'], $data_filters[$k]['exclude_apartments']);
										?>
											<label class="apt-item <?= $isExcl ? 'selected' : '' ?>">
												<input type="checkbox" name="exclude_apartments[<?= $k ?>][]" value="<?= $apt['apartment_num'] ?>" <?= $isExcl ? 'checked' : '' ?> onchange="this.parentElement.classList.toggle('selected')">
												<?= $apt['apartment_num'] ?>
											</label>
										<?php endforeach; ?>
									</div>
								</div>
							</div>

							<div class="filter-row">
								<label>Только для номеров (V.I.P.):</label>
								<div class="apt-selector">
									<?php $has_incl_apts = !empty($data_filters[$k]['include_apartments']); ?>
									<div class="apt-selector-header">
										<span>Выбрать квартиры</span>
										<a href="javascript:void(0)" onclick="toggleApts(this)" style="font-size:0.9em; text-decoration:none;"><?= $has_incl_apts ? 'свернуть' : 'развернуть' ?></a>
									</div>
									<div class="apt-list-container" id="include-list-<?= $k ?>" style="<?= $has_incl_apts ? 'display:grid' : 'display:none' ?>">
										<?php 
										foreach($apts as $apt): 
											$isIncl = !empty($data_filters[$k]['include_apartments']) && in_array($apt['apartment_num'], $data_filters[$k]['include_apartments']);
										?>
											<label class="apt-item <?= $isIncl ? 'selected' : '' ?>">
												<input type="checkbox" name="include_apartments[<?= $k ?>][]" value="<?= $apt['apartment_num'] ?>" <?= $isIncl ? 'checked' : '' ?> onchange="this.parentElement.classList.toggle('selected')">
												<?= $apt['apartment_num'] ?>
											</label>
										<?php endforeach; ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
				</div>

				<script>
				function toggleHome(id) {
					const card = document.getElementById('home-card-' + id);
					if (!card) return;
					const checkbox = card.querySelector('input[type="checkbox"]');
					if (checkbox.checked) {
						card.classList.add('active');
					} else {
						card.classList.remove('active');
					}
					if (typeof checkConflicts === 'function') checkConflicts();
				}

				function toggleSections(homeId, show) {
					const sectionList = document.getElementById('section-list-' + homeId);
					if (sectionList) {
						sectionList.style.display = show ? 'flex' : 'none';
					}
					if (!show) {
						updateApartments(homeId); // Reset to all
					}
				}

				function updateApartments(homeId) {
					const modeInput = document.querySelector(`input[name="section_mode[${homeId}]"]:checked`);
					if (!modeInput) return;
					const mode = modeInput.value;
					const sections = [];
					if (mode === 'include') {
						document.querySelectorAll(`input[name="sections[${homeId}][]"]:checked`).forEach(cb => {
							sections.push(cb.value);
						});
					}

					// Сохраняем текущие выбранные квартиры
					const exclChecked = Array.from(document.querySelectorAll(`#exclude-list-${homeId} input:checked`)).map(cb => cb.value);
					const inclChecked = Array.from(document.querySelectorAll(`#include-list-${homeId} input:checked`)).map(cb => cb.value);

					const formData = new FormData();
					formData.append('home_id', homeId);
					sections.forEach(s => formData.append('sections[]', s));

					fetch('ajax_router.php?ctr=zapisx2&act=ajax_get_apts', {
						method: 'POST',
						body: formData
					})
					.then(response => response.json())
					.then(data => {
						const exclContainer = document.getElementById(`exclude-list-${homeId}`);
						const inclContainer = document.getElementById(`include-list-${homeId}`);
						if (!exclContainer || !inclContainer) return;

						let htmlExcl = '';
						let htmlIncl = '';
						const availableApts = data.apts.map(String);
						
						data.apts.forEach(num => {
							const isExclChecked = exclChecked.includes(num.toString()) && availableApts.includes(num.toString()) ? 'checked' : '';
							htmlExcl += `
								<label class="apt-item ${isExclChecked ? 'selected' : ''}">
									<input type="checkbox" name="exclude_apartments[${homeId}][]" value="${num}" ${isExclChecked} onchange="this.parentElement.classList.toggle('selected')">
									${num}
								</label>`;
								
							const isInclChecked = inclChecked.includes(num.toString()) && availableApts.includes(num.toString()) ? 'checked' : '';
							htmlIncl += `
								<label class="apt-item ${isInclChecked ? 'selected' : ''}">
									<input type="checkbox" name="include_apartments[${homeId}][]" value="${num}" ${isInclChecked} onchange="this.parentElement.classList.toggle('selected')">
									${num}
								</label>`;
						});
						
						exclContainer.innerHTML = htmlExcl;
						inclContainer.innerHTML = htmlIncl;
					});
				}
				
				function toggleApts(el) {
					const container = el.parentElement.nextElementSibling;
					if (container.style.display === 'none') {
						container.style.display = 'grid';
						el.innerText = 'свернуть';
					} else {
						container.style.display = 'none';
						el.innerText = 'развернуть';
					}
				}

				function sumRow(input) {
					const row = input.closest('tr');
					const inputs = row.querySelectorAll('.count-me');
					let total = 0;
					inputs.forEach(i => total += parseInt(i.value || 0));
					row.querySelector('.row-total').innerText = total;
				}

				// Проверка конфликтов
				let conflictCheckTimeout;
				function checkConflicts() {
					clearTimeout(conflictCheckTimeout);
					conflictCheckTimeout = setTimeout(() => {
						const selectedHomes = [];
						document.querySelectorAll('input[name^="objects"]:checked').forEach(cb => {
							const match = cb.name.match(/\[(\d+)\]/);
							if (match) selectedHomes.push(match[1]);
						});
						
						const box = document.getElementById('conflict-box');
						if (!box) return;
						if (selectedHomes.length === 0) {
							box.style.display = 'none';
							return;
						}
						
						const formData = new FormData();
						formData.append('date', '<?= $_GET['date'] ?>');
						formData.append('group_id', '<?= $id ?>');
						selectedHomes.forEach(id => formData.append('home_ids[]', id));
						
						fetch('ajax_router.php?ctr=zapisx2&act=check_conflicts', {
							method: 'POST',
							body: formData
						})
						.then(response => response.json())
						.then(data => {
							renderConflicts(data.conflicts || []);
						})
						.catch(err => console.error('Error checking conflicts:', err));
					}, 500);
				}

				function renderConflicts(conflicts) {
					const container = document.getElementById('conflicts-container');
					const box = document.getElementById('conflict-box');
					if (!container || !box) return;

					if (conflicts.length === 0) {
						box.style.display = 'none';
						return;
					}
					
					box.style.display = 'block';
					let html = '';
					conflicts.forEach(conflict => {
						const activeTimes = (conflict.times || []).filter(t => (parseInt(t.c) > 0 || parseInt(t.pom) > 0 || parseInt(t.dkp) > 0));
						const timesHtml = activeTimes.map(t => `<span style="background:#edf2f7; padding:2px 6px; border-radius:4px; margin-right:4px; font-size:0.85em; display:inline-block; margin-bottom:4px; font-weight:600; color:#4a5568; border:1px solid #e2e8f0;">${t.time}</span>`).join('');
						
						html += `
							<div class="conflict-item">
								<div class="conflict-header">
									<span>Дом №${conflict.home_title}</span>
									<span class="badge ${conflict.show == 1 ? 'badge-info' : 'badge-warning'}">${conflict.show == 1 ? 'Опубликовано' : 'Черновик'}</span>
								</div>
								<div style="font-size:0.9em; color:#4a5568; margin-bottom:8px; line-height:1.4;">
									Это здание уже используется в правиле <strong>#${conflict.group_id}</strong> на эту дату.
								</div>
								<div style="margin-bottom:12px;">
									<strong>Активные слоты:</strong><br/> ${timesHtml || 'нет'}
								</div>
								<div class="conflict-actions">
									<a href="?ctr=zapisx2&act=edit_daygroup&id=${conflict.group_id}&date=<?= $_GET['date'] ?>" target="_blank" class="btn-mini btn-view">Открыть</a>
									<button type="button" class="btn-mini btn-del" onclick="deleteConflictRule(${conflict.group_id})">Удалить</button>
								</div>
							</div>
						`;
					});
					container.innerHTML = html;
				}

				function deleteConflictRule(groupId) {
					if (!confirm('Вы уверены, что хотите удалить правило #' + groupId + '?')) return;
					const month = '<?= $_GET['month'] ?? "" ?>';
					window.location.href = 'ctrind.php?ctr=zapisx2&act=del&id=' + groupId + '&month=' + month + '&date=<?= $_GET['date'] ?>';
				}

				window.addEventListener('load', checkConflicts);
				</script>

				<span class="section-title">2. Количество записей</span>
				<table class="time-table">
					<thead>
						<tr>
							<th width="120">Время</th>
							<th>Стандарт (ДДУ)</th>
							<th>С ПО</th>
							<th>ДКП</th>
							<th width="100">Итого</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($this->time_arr as $k => $v): 
							$row_total = (int)($data_times[$k] ?? 0) + (int)($data_times_pom[$k] ?? 0) + (int)($data_times_dkp[$k] ?? 0);
						?>
						<tr class="time-row">
							<td style="font-weight:700; color:#2d3748;"><?= $k ?></td>
							<td><input type="number" name="times[<?= $k ?>]" class="cap-input count-me" min="0" max="50" value="<?= $data_times[$k] ?? 0 ?>" onchange="sumRow(this)"></td>
							<td><input type="number" name="times_pom[<?= $k ?>]" class="cap-input count-me" min="0" max="50" value="<?= $data_times_pom[$k] ?? 0 ?>" onchange="sumRow(this)"></td>
							<td><input type="number" name="times_dkp[<?= $k ?>]" class="cap-input count-me" min="0" max="50" value="<?= $data_times_dkp[$k] ?? 0 ?>" onchange="sumRow(this)"></td>
							<td class="row-total"><?= $row_total ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<div class="conflict-box" id="conflict-box" style="display:none">
					<div class="conflict-title">⚠️ Обнаружены конфликты в расписании</div>
					<div id="conflicts-container"></div>
				</div>

				<div class="submit-panel">
					<button type="button" class="btn-cancel" onclick="if(parent.jQuery.fancybox) { parent.jQuery.fancybox.close(); } else { window.close(); }">Отмена</button>
					<input type="submit" class="btn-save" value="Сохранить график">
				</div>
			</form>
		</div>


			</div>
			<?
 
	}
	  
	  
	  
	  
	  // Статистика дома
	  function act__stat_home()
	  {
		  // ВЫводим дом с кастомным методом как для печати!
	  }
	  
	 
	 
	 

	/**
	 * Проверка свободности слота с учетом типа записи
	 * 
	 * @param int $home ID дома
	 * @param string $date Дата в формате dd.mm.YYYY
	 * @param string $time Время в формате HH:MM
	 * @param int $pom 0 - без помогайки, 1 - с помогайкой
	 * @param int $dkp 0 - ДДУ, 1 - ДКП
	 * @return int 1 - свободно, 0 - занято
	 */
	function act__testfree($home = '24', $date = '14.03.2023', $time = '10:00', $pom = 0, $dkp = 0)
	{
		// Конвертация для get_graficx: 0 → 1 (без), 1 → 2 (с)
		$pom_filter = $pom ? 2 : 1;
		$dkp_filter = $dkp ? 2 : 1;
		
		// Загружаем график с учетом типа
		$this->get_graficx(1, $pom_filter, $dkp_filter);
		
		// ДОМ ТОЛЬКО В ОДНОЙ ГРУППЕ МОЖЕТ БЫТЬ НА ДАТУ
		$group = $this->graficx_i_grra[$home][$date];
		
		if (!$group) {
			return 0; // Нет группы для этого дома на эту дату
		}
		
		// Проверяем наличие свободных мест
		if ($this->graficx_i_za_gr_free2[$group][$time]) {
			return 1;
		} else {
			return 0;
		}
	}

	/**
	 * Атомарная проверка свободности и вставка записи
	 * Предотвращает race condition через транзакцию
	 * 
	 * @return array ['success' => bool, 'message' => string, 'id' => int|null]
	 */
	function atomic_insert_zapis($data)
	{
		global $mysql;
		
		// Начинаем транзакцию
		$mysql->sql('START TRANSACTION');
		
		try {
			// 1. Проверка: квартира уже записана?
			$q_check_apt = 'SELECT zapis_id FROM zapis 
							WHERE home_id = "' . (int)$data['home_id'] . '" 
							AND apartment_num = "' . (int)$data['apartment_num'] . '" 
							AND del = "0" 
							FOR UPDATE'; // Блокируем строку
			
			$existing = $mysql->get_arr($q_check_apt, 1);
			if ($existing) {
				$mysql->sql('ROLLBACK');
				return [
					'success' => false,
					'message' => 'Квартира уже записана',
					'id' => null
				];
			}
			
			// 2. Получаем группу для дома и даты
			$this->get_graficx(1, 
				$data['pom'] ? 2 : 1, 
				$data['dkp'] ? 2 : 1
			);
			
			$date_formatted = date('d.m.Y', strtotime($data['date']));
			$time_formatted = substr($data['time'], 0, 5); // HH:MM
			
			$group = $this->graficx_i_grra[$data['home_id']][$date_formatted];
			
			if (!$group) {
				$mysql->sql('ROLLBACK');
				return [
					'success' => false,
					'message' => 'Нет доступного графика для выбранного дома и даты',
					'id' => null
				];
			}
			
			// 3. Проверяем свободные места с блокировкой
			$q_count = 'SELECT COUNT(*) as cnt FROM zapis 
						WHERE date = "' . $data['date'] . '" 
						AND time = "' . $data['time'] . '" 
						AND home_id = "' . (int)$data['home_id'] . '" 
						AND del = "0" 
						FOR UPDATE'; // Блокируем подсчет
			
			$count_result = $mysql->get_arr($q_count, 1);
			$current_count = (int)$count_result['cnt'];
			
			// Получаем лимит из графика
			$capacity = (int)$this->graficx_i_gr_c2[$group][$time_formatted];
			
			if ($current_count >= $capacity) {
				$mysql->sql('ROLLBACK');
				return [
					'success' => false,
					'message' => 'Все места на это время заняты',
					'id' => null
				];
			}
			
			// 4. Вставляем запись
			$insert_id = $mysql->insert('zapis', $data);
			
			if (!$insert_id) {
				$mysql->sql('ROLLBACK');
				return [
					'success' => false,
					'message' => 'Ошибка вставки записи',
					'id' => null
				];
			}
			
			// 5. Коммитим транзакцию
			$mysql->sql('COMMIT');
			
			return [
				'success' => true,
				'message' => 'Запись успешно создана',
				'id' => $insert_id
			];
			
		} catch (Exception $e) {
			$mysql->sql('ROLLBACK');
			return [
				'success' => false,
				'message' => 'Ошибка: ' . $e->getMessage(),
				'id' => null
			];
		}
	}



	/**
	 * AJAX: Проверка конфликтов правил
	 */
	function act__check_conflicts()
	{
		global $mysql;
		
		$date = $_POST['date']; // dd.mm.YYYY
		$home_ids = isset($_POST['home_ids']) ? $_POST['home_ids'] : [];
		$current_group_id = isset($_POST['group_id']) ? (int)$_POST['group_id'] : 0;
		
		$conflicts = [];
		
		if (empty($home_ids)) {
			header('Content-Type: application/json');
			echo json_encode(['success' => true, 'conflicts' => []]);
			exit;
		}

		foreach ($home_ids as $home_id) {
			// Ищем существующие правила для этого дома на эту дату
			$q = 'SELECT DISTINCT d.keys_graficx_date_id, d.show
				  FROM keys_graficx_date d
				  JOIN keys_graficx_objects o ON d.keys_graficx_date_id = o.keys_graficx_date_id
				  WHERE d.date = "' . mysqli_real_escape_string($mysql->c, $date) . '"
				  AND o.object_id = "' . (int)$home_id . '"
				  AND d.del = 0';
			
			if ($current_group_id) {
				$q .= ' AND d.keys_graficx_date_id != ' . $current_group_id;
			}
			
			$existing = $mysql->get_arr($q);
			
			if (!empty($existing)) {
				foreach ($existing as $rule) {
					$group_id = $rule['keys_graficx_date_id'];
					
					// Времена
					$q_times = 'SELECT time, c, pom, dkp FROM keys_graficx_time 
								WHERE keys_graficx_date_id = ' . $group_id;
					$times = $mysql->get_arr($q_times);
					
					// Фильтры
					$q_filters = 'SELECT filter_type, filter_value 
								  FROM keys_graficx_filters 
								  WHERE keys_graficx_date_id = ' . $group_id . '
								  AND home_id = ' . (int)$home_id;
					$filters = $mysql->get_arr($q_filters);
					
					$conflicts[] = [
						'home_id' => $home_id,
						'home_title' => $this->select_homes[$home_id] ?? 'Неизвестно',
						'group_id' => $group_id,
						'show' => $rule['show'],
						'times' => $times,
						'filters' => $filters
					];
				}
			}
		}
		
		header('Content-Type: application/json');
		echo json_encode([
			'success' => true,
			'conflicts' => $conflicts
		]);
		exit;
	}
	 
	function act__zapisformx()
	{
		global $mysql;
		global $r;
		
		// === ВАЛИДАЦИЯ ВХОДНЫХ ДАННЫХ ===
		
		// 1. Проверка обязательных полей
		$required_fields = ['home_id', 'section_id', 'apartament_num', 'date', 'time', 'phone', 'fio', 'email'];
		foreach ($required_fields as $field) {
			if (empty($_POST[$field])) {
				die('<center><br/><br/><br/>Ошибка: не заполнено поле ' . $field . '</center>');
			}
		}
		
		// 2. Приведение типов
		$_POST['home_id'] = (int)$_POST['home_id'];
		$_POST['section_id'] = (int)$_POST['section_id'];
		$_POST['apartament_num'] = (int)$_POST['apartament_num'];
		$_POST['pom'] = !empty($_POST['pom']) ? 1 : 0;
		$_POST['dkp'] = !empty($_POST['dkp']) ? 1 : 0;
		
		// 3. Проверка существования квартиры
		$q_apt = 'SELECT apartment_num FROM apartaments 
				  WHERE home_id = "' . $_POST['home_id'] . '" 
				  AND section_id = "' . $_POST['section_id'] . '" 
				  AND apartment_num = "' . $_POST['apartament_num'] . '"';
		$apt_exists = $mysql->get_arr($q_apt, 1);
		
		if (!$apt_exists) {
			die('<center><br/><br/><br/>Ошибка: квартира не найдена в базе данных</center>');
		}
		
		// 4. Проверка формата даты
		$date_check = DateTime::createFromFormat('d.m.Y', $_POST['date']);
		if (!$date_check) {
			die('<center><br/><br/><br/>Ошибка: некорректный формат даты</center>');
		}
		
		// 5. Проверка формата времени
		if (!preg_match('/^\d{2}:\d{2}$/', $_POST['time'])) {
			die('<center><br/><br/><br/>Ошибка: некорректный формат времени</center>');
		}
		
		// === КОНЕЦ ВАЛИДАЦИИ ===

		$data = array();
		$data['home_id'] = $_POST['home_id'];
		$data['date'] =  $_POST['date'] ; 
		$data['section']= $_POST['section_id'];
		$data['apartment_num']= $_POST['apartament_num'];
		$data['time']= $_POST['time'].':00';
		$data['phone']= $_POST['phone'];
		$data['new_passport']= isset($_POST['passprot']) ? (int)$_POST['passprot'] : 0;
		$data['fio']= $_POST['fio'];
		$data['pom']= $_POST['pom'];
		$data['dkp']= $_POST['dkp'];
		$data['email']= $_POST['email'];
		$data['at']= time();
		
		// Конвертируем дату в MySQL формат ДО проверки
		$data['date'] = date("Y-m-d", strtotime($data['date']));

		// Используем атомарный метод
		$result = $this->atomic_insert_zapis($data);

		if ($result['success']) {
			$insid = $result['id'];
			
			// Получаем данные для письма
			ob_start();
			$this->card($insid, '', 'Ваша заявка принята');
			$con = ob_get_clean();
			
			// Отправка писем
			multi_attach_mail('89236470002@mail.ru', 
				'Запись на выдачу ключей - дом:' . $this->homes_arr[$data['home_id']]['title'] . 
				' кв.:' . $data['apartment_num'] . ' дата:' . $_POST['date'] . ' время:' . $data['time'], 
				$con, 'sdsasd@m2profi.pro', $GLOBALS['config']['domain']);
			
			multi_attach_mail($data['email'], 
				'Запись на выдачу ключей - дом:' . $this->homes_arr[$data['home_id']]['title'] . 
				' кв.:' . $data['apartment_num'] . ' дата:' . $_POST['date'] . ' время:' . $data['time'], 
				$con, 'admin@m2profi.pro', $GLOBALS['config']['domain']);
			
			multi_attach_mail('op15@em-nsk.group', 
				'Запись на выдачу ключей - дом:' . $this->homes_arr[$data['home_id']]['title'] . 
				' кв.:' . $data['apartment_num'] . ' дата:' . $_POST['date'] . ' время:' . $data['time'], 
				$con, 'admin@m2profi.pro', $GLOBALS['config']['domain']);
			
			print $con; // Показываем карточку
		} else {
			print '<center><br/><br/><br/>Произошла ошибка - ' . $result['message'] . 
				  '<br/>попробуйте записаться на другое время</center>';
		}
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
	 
	 
	 
	 function act__bedit()
	 {
		 global $filed;
		 
		 ?>
		 <style>
input{ padding:5px; }
.suk_form{color:#000;}
form{position:relative;}
#form_progressbar{  
	width:100%;  
	display:none;
    position: absolute;
    background: #FFF;
    height: 100%;
    z-index: 1000;
    opacity: 0.8;
    text-align: center;
    padding-top: 20%;
    font-size: 20px;
	}
</style>
		 <form method="post" action="/sahmatka/ajax_router.php?ctr=zapiskeys&act=zapisformx" id="zapisform">
		 <div id="form_progressbar">Загрузка...<br/><img src="/sahmatka/loader.gif"></div>
 
			<?= $filed->checkbox(
     'admin_mode',
     '<b>ЛЮБЫЕ ДОМА И ВРЕМЯ</b>',
     $_GET['admin_mode'],
     ' data-bl="-1" ',
     'admin_mode'
   ) ?><br/>
		
			<?= $filed->checkbox(
     'pom',
     'Помогающая компания',
     $_GET['pom'],
     ' data-bl="0" ',
     'pom'
   ) ?><br/>
		
			
			<select class="input_edit" name="home_id" id="home_id" class="suk_form" data-bl="1" required="required" disabled><option value="">Номер дома</option></select> <br/>
			 
			<select class="input_edit"  name="section_id" id="section_id"  class="suk_form" data-bl="2" required="required" disabled><option value="">Подъезд</option> </select> <br/>
			 
			<select class="input_edit" name="apartament_num" id="apartament_num"  class="suk_form" data-bl="3" required="required" disabled><option value="">Квартира</option> </select> <br/>
			 
			<select class="input_edit" name="date" id="date" class="suk_form" data-bl="4" required="required" disabled><option value="">Дата</option></select> <br/>
		 
			<select class="input_edit" name="time" id="time" class="suk_form" data-bl="5" required="required" disabled><option value="">Время</option> </select> <br/>
			<input class="input_edit" name="phone" id="phone" class="suk_form phone_mask"  type="tel" placeholder="Телефон" required="required">  <br/>
			<input class="input_edit" name="email" id="email" class="suk_form" type="email" placeholder="E-Mail" required="required">   <br/>
		    <input class="input_edit" name="fio" id="fio" class="suk_form" type="text" placeholder="ФИО" required="required">   <br/>
			<?= $filed->submit() ?>
		 </form>
		 <script type="text/javascript">
		function blocked_child_select(indexselect)
		{
			if(!indexselect){indexselect=1;}
			//alert(indexselect);
			$('*[data-bl]').each(function (index, element) 
			{
				 var ind = Number($(this).attr('data-bl'));
				 if(ind>indexselect)
				 {
					 $(this).fwreset_select_options();
					  console.log(indexselect);
				 }
			});
		}
// ПЛагин синхронной загрузки связных списков		
(function($) {
	 
	// Загрузка OPTIONS
    $.fn.fwloadx = function($url,$calback) {
        // при многократном вызове настройки будут сохраняться и замещаться при необходимости
         
        var $form = $(this).closest("form");
		var obj = this;
		
		
		$(obj).prop('disabled', 'disabled');
		$.ajax({
		  type: 'POST',
		  url: $url,
		  async: true,
		  cache: false,
		  data: $form.serialize(),
		  beforeSend: function() {
			// setting a timeout
			//alert(0);
			//$('#form_progressbar').show();
		  },
		  success: function(response) { //Данные отправлены успешно 
				$(obj).html(response);
				// console.log(response);
				blocked_child_select(obj.attr('data-bl'));
			
				//
			},
			error: function(response) { // Данные не отправлены
				//$(result_obj).html('Ошибка. Данные не отправленны.');
				blocked_child_select(obj.attr('data-bl'));
				//console.log(response);
			}
		}).done(function() {
			console.log('send data');
			$(obj).removeAttr("disabled");
			if($calback)
			{
				$calback();
			}
			$(obj).change();
			//	alert(1);
			$('#form_progressbar').hide();
			
			
		}).fail(function() {
			console.log('fail');
		});
		
        return this;
    };
	
	// Очистка options с значениями + блокировка select 
	$.fn.fwreset_select_options = function() { 
        var sel = this;
		sel.prop('selected', false);
		$('option[value=""]',sel).prop('selected', true);
		
		let option = $('option[value!=""]',sel);
		if (option) {option.remove();}
		$(sel).prop('disabled', 'disabled');
    };
	//
	
})(jQuery);




 
 
		 // A $( document ).ready() block.
		$(document).ready(function() {
		 
			$.ajaxSetup({
				cache: false,
				async: false
			});
			 
			// ДОМА
			var home_id = "22";
			
			
			$("#home_id").fwloadx("/sahmatka/ajax_router.php?ctr=zapisx&act=sel_home_zapis",function() {
				$('#home_id option[value='+home_id+']').prop('selected', true);
			});
			 
			 
			 
			$("#admin_mode").change(function() 
			{
				if ($(this).val()){
					$("#home_id").fwloadx("/sahmatka/ajax_router.php?ctr=zapisx&act=sel_home_zapis");
				}
				else{
					blocked_child_select($(this).attr('data-bl'));
				}
			});
			
			$("#pom").change(function() 
			{
				if ($(this).val()){
					$("#home_id").fwloadx("/sahmatka/ajax_router.php?ctr=zapisx&act=sel_home_zapis");
				}
				else{
					blocked_child_select($(this).attr('data-bl'));
				}
			});
			
			
			
			$("#home_id").change(function() 
			{
				if ($(this).val()){
					$("#section_id").fwloadx("/sahmatka/ajax_router.php?ctr=zapisx&act=sel_section_zapis");
				}
				else{
					blocked_child_select($(this).attr('data-bl'));
				}
			});
			
			$("#section_id").change(function() 
			{
				if ($(this).val()){
					$("#apartament_num").fwloadx("/sahmatka/ajax_router.php?ctr=zapisx&act=sel_apartament_zapis");
				}
				else{
					blocked_child_select($(this).attr('data-bl'));
				}
			});
			
			
			$("#apartament_num").change(function() 
			{
				if ($(this).val()){
					$("#date").fwloadx("/sahmatka/ajax_router.php?ctr=zapisx&act=sel_date_zapisx");
				}
				else{
					blocked_child_select($(this).attr('data-bl'));
				}
			});
			
			
			
			 
			$("#date").change(function() 
			{
				if ($(this).val()){
					$("#time").fwloadx("/sahmatka/ajax_router.php?ctr=zapisx&act=sel_time_zapisx");
				}
				else{
					blocked_child_select($(this).attr('data-bl'));
				}
			});
			
			 
			 
			 
		
		$( "#form_home_submit" ).click(function() {	$( "#zapisform" ).submit(); return false;	});
			
		 
		
		
		});
        </script>
		<?
	 }
	  
	  
	/**
	 * Проверка конфликтов правил
	 * @return array Массив конфликтующих правил с деталями
	 */
	function checkRuleConflicts($date, $selected_homes, $current_rule_id = null) {
		global $mysql;
		
		$conflicts = [];
		$date_mysql = $this->date2mysql($date);
		
		foreach ($selected_homes as $home_id => $checked) {
			if (!$checked) continue;
			
			// Ищем другие правила с этим домом на эту дату
			$q = "SELECT DISTINCT kgd.keys_graficx_date_id, kgd.show
				  FROM keys_graficx_date kgd
				  JOIN keys_graficx_objects kgo ON kgd.keys_graficx_date_id = kgo.keys_graficx_date_id
				  WHERE kgd.date = '" . mysqli_real_escape_string($mysql->c, $date) . "'
				  AND kgo.object_id = " . (int)$home_id . "
				  AND kgd.del = 0";
			
			if ($current_rule_id) {
				$q .= " AND kgd.keys_graficx_date_id != " . (int)$current_rule_id;
			}
			
			$existing = $mysql->get_arr($q);
			
			foreach ($existing as $rule) {
				$conflicts[] = [
					'group_id' => $rule['keys_graficx_date_id'],
					'home_id' => $home_id
				];
			}
		}
		
		return $conflicts;
	}

	function act__ajax_get_apts()
	{
		global $mysql;
		$home_id = (int)$_POST['home_id'];
		$sections = $_POST['sections'] ?? [];
		
		$q = 'SELECT apartment_num FROM apartaments WHERE home_id = "'.$home_id.'"';
		if (!empty($sections)) {
			// Очистка и фильтрация
			$sections = array_map('intval', $sections);
			$q .= ' AND section_id IN (' . implode(',', $sections) . ')';
		}
		$q .= ' ORDER BY apartment_num';
		
		$apts = $mysql->get_arr($q);
		
		header('Content-Type: application/json');
		echo json_encode(['apts' => array_column($apts, 'apartment_num')]);
		exit;
	}

}