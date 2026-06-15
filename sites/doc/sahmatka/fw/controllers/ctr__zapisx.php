<?
  
/*
Проверка при записи
+ переключить на синхронный аякс!
отключить стили в селектах и добавить прогрессбар
рефакторинг 
один дом в нескольких группах в пределах дня!
*/
class ctr__zapisx extends ctr__
{ 
	var $table = 'keys_graficx_date'; //Главная таблица
	var $key_filed = 'keys_graficx_date_id'; // Ключевое поле главной таблицы
	var $ctr = 'zapisx';
	
	
	
	
	
	
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
		$time_arr['10:00']='1';
		$time_arr['11:00']='1';
		$time_arr['13:30']='1';
		$time_arr['14:30']='1';
		$time_arr['15:30']='1';
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
	
	
	
	
	 
	 
	 
	function get_graficx($future=false,$pom=false,$sh=0)
	{
	global $mysql;
		
		$q='SELECT keys_graficx_date.*,keys_graficx_objects.object_id,keys_graficx_time.time , keys_graficx_time.c, keys_graficx_time.pom
	FROM keys_graficx_date 
	LEFT JOIN keys_graficx_time ON keys_graficx_date.keys_graficx_date_id = keys_graficx_time.keys_graficx_date_id
		LEFT JOIN keys_graficx_objects ON keys_graficx_date.keys_graficx_date_id = keys_graficx_objects.keys_graficx_date_id
		WHERE keys_graficx_date.del="0"
		';
		
		// Показывать скрытое! (только админам и Виталию)
	if(!$_GET['admin'] && $sh==0){ $q.=' AND  `keys_graficx_date`.`show` = "1" ';}

		
		
		
		if($future){$q.=' AND keys_graficx_date.date_mysql>= CURDATE() ';} // Только будущие даты (метод генерации селектов)
		$q.=' ORDER BY keys_graficx_date.date_mysql ';
	
		$data = $mysql->get_arr($q);
		//print '<pre>';
		  // print_r($data);
		//print '</pre>';
		#$this->settings_arr_date_time_free[$data['home_id']][$data['date']][$data['time']]; // НУЖЕН!!!!!! для добавления!
		foreach($data as $l=>$v) # ЦИКЛ ПО ГРАФИКУ
	{ 
			// 2 - только помогайки, 1 только без помогаек
			if( ( $pom == false ) || ( $pom == 2 && $v['pom'] ) || ( $pom==1 && !$v['pom'] ) )
			{
				
			$this->graficx_i_date_b[$v['keys_graficx_date_id']]=$v; // [дата][] = группа
			$this->graficx_i_date[$v['date']][$v['keys_graficx_date_id']]=1; // [дата][] = группа
			$this->graficx_i_homes[$v['keys_graficx_date_id']][$v['object_id']]=1; // [группа][]=дом
			$this->graficx_i_times[$v['keys_graficx_date_id']][$v['time']]=$v['c']; // [группа][время]=количество записей
		   
			$this->graficx_i_hgr[$v['object_id']][$v['date']] = $v['keys_graficx_date_id']; // [дом][дата] = группа #1 дом одна дата одна группа!
			 
			$this->graficx_i_gr_c[$v['object_id']][$v['keys_graficx_date_id']][$v['time']] = $v['c']; // по графику [дом][дата группа][время] = мест всего  
			$this->graficx_i_gr_c2[$v['keys_graficx_date_id']][$v['time']] = $v['c']; // по графику [дата группа][время] = мест всего 
			 
			$this->graficx_i_grra[$v['object_id']][$v['date']] = $v['keys_graficx_date_id']; // расшифровка [дом][дата] = группа// ДОМ МОЖЕТ БЫТЬ ТОЛЬКО В ОДНОЙ ГРУППЕ НА ДАТУ!!!! при этом
			
			$this->graficx_i_grra2[$v['keys_graficx_date_id']] = $v['date']; // расшифровка2 [группа]=дата
			 
			$this->graficx_i_pom[$v['keys_graficx_date_id']][$v['time']] = $v['pom'];
			}
		}
		
		$q = 'SELECT zapis.* FROM zapis WHERE `date` > "2021-01-01" AND del="0" ';
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
				$this->graficx_i_za_c[$v['home_id']][$this->graficx_i_grra[$v['home_id']][$da]][$ti]++; // по записям [дом][дата группа][время] = записей всего 
				// отнимаем от графика количество записей на этот день  [дом][группа][время] = мест всего 
				$this->graficx_i_za_gr_free[$v['home_id']][$this->graficx_i_grra[$v['home_id']][$da][$ti]]--;// 
				
				
				// $this->graficx_i_za_gr_free_all[$v['home_id']][$this->graficx_i_grra[$v['home_id']][$da]][$ti]]--;// 
				
				$this->graficx_i_za_gr_free2[$this->graficx_i_grra[$v['home_id']][$da][$ti]]--;//  [дата группа][время] = мест свободно 
				
				if(!$this->graficx_i_za_gr_free2[$this->graficx_i_grra[$v['home_id']][$da]][$ti])
				{
					unset($this->graficx_i_za_gr_free2[$this->graficx_i_grra[$v['home_id']][$da]][$ti]); // удаляем пустое время
				}
				
				if(!$this->graficx_i_za_gr_free2[$this->graficx_i_grra[$v['home_id']][$da] ] )
				{
					unset($this->graficx_i_za_gr_free2[$this->graficx_i_grra[$v['home_id']][$da] ] ); // удаляем пустое даты
				}
				
				
				
			} 
			
			// Если есть группа с таким домом и вней есть такое время на которое запись
			if( $this->graficx_i_hgr[$v['home_id']][$da] && $this->graficx_i_times[$this->graficx_i_hgr[$v['home_id']][$da]][$ti] )
			{
				$this->zapis_i_dtx[$v['home_id']][$da][$ti][]=$v; //[дом][дата][время] =записи
				$this->zapis_i_dt[$da][$ti][]=$v; //[дата][время] =записи
				$this->zapis_i_pr[$da][$this->graficx_i_hgr[$v['home_id']][$da]][$ti]=$v; //[дата][группа][время] =записи
			}
			else
			{
				 $this->zapis_i_dtnr[$da][$ti][]=$v; //[дата][время] =записи ВНЕ РАСПИСАНИЯ
			}
		}
		 
	

		
	}
	
	function act__test()
	{
	
		 
		
		$this->get_graficx(1); // Только будующие даты 
		  
		print '<pre>';
		print '<h2>Дома</h2>';
		print_r($this->select_homes);
		print '<h2>Расшифровка групп в даты</h2>';
		print_r( $this->graficx_i_grra2 ); // РАсшифровка групп в даты [группа]=дата
		print_r($this->graficx_i_za_gr_free2); // свободно [группадата][время]=мест
	 	//print_r($this->graficx_i_gr_c); // // по графику [дом][группа][время] = мест всего  
		//print_r($this->graficx_i_za_c); // по записям [дом][группа][время] =  записей всего  
	   // print_r($this->graficx_i_za_gr_free); // по записям [дом][группа][время] =  мест - записей 	 НЕ ОТНЯЛАСЬ ЗАПИСЬ!!!!
			  
		    // print_r($this->graficx_i_grra); // расшифровка [дом][дата] = группа
		   
		// print_r($this->graficx_i_homef);
		// print_r($this->graficx_i_date);
		// print_r($this->graficx_i_homes);
		// print_r($this->graficx_i_times);
		// print_r($this->graficx_i_pom);
		// print_r($this->zapis_i_dtc);
		// print_r($this->zapis_i_dt);
		// print_r($this->zapis_i_dt);
		print '</pre>';
		 
		// ПОЛУЧАЕМ ЗАПИСИ 
		/*
		[дата][дом] - всего мест 
		[дом][дата] - мест занято
		
		*/
	}
	


############## ЗАПИСЬ
	// Дома с свободными датами
	function act__sel_home_zapis()
	{
		// 2 - только помогайки, 1 только без помогаек
		if($_REQUEST['pom']){$pom=2;}else{$pom=1;}
		$this->get_graficx( 1 , $pom );
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
		$q = ' SELECT section_id FROM apartaments WHERE `apartaments`.`home_id`="'.$_GET['home_id'].'" GROUP BY `section_id` ';
		$arr = $mysql->get_arr($q);
		?><option value="">Выбрать секцию</option><?
		foreach($arr as $k=>$v)
		{
			?><option value="<?=$v['section_id']?>">Секция №<?=$v['section_id']?> </option><?
	}
	}
	
   
	function act__sel_apartament_zapis()
	{
		global $mysql;
		  $q = '
		SELECT apartaments.apartment_num,zapis.zapis_id FROM apartaments 
		LEFT JOIN `zapis` ON `zapis`.`home_id` = `apartaments`.`home_id` AND  `zapis`.`apartment_num` = `apartaments`.`apartment_num` AND `zapis`.`del`="0"
		WHERE `apartaments`.`home_id`="'.$_GET['home_id'].'" AND `apartaments`.`section_id`="'.$_GET['section_id'].'" ORDER BY `apartaments`.`apartment_num` 
		 ';
		$arr = $mysql->get_arr($q);
		 
		?><option value="">Выбрать квартиру</option><?
		foreach($arr as $k=>$v)
		{
			if($v['zapis_id']){ $st = ' style="color:#333;" '; }
			else{  $st = ' style="color:#000; font-weight:bold;" '; }
			?><option  <?=$st?> value="<?=$v['apartment_num']?>"><?=$v['apartment_num']?> </option><?
	}
	}
	
	
	
	
	function block_datecheck($date,$time='' )
	{
		
	}
	
	function act__sel_date_zapisx()
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
				
				// print 'h-'.$h.'k-'.$k.'date_t-'.$date_t.'tom_date-'.$tom_date.'<br/>';
				//h-16k-103date_t-28.02.2023tom_date-01.03.2023
				 
				if( $h>=16 && ( $fd==$date_t || $fd==$tom_date  ) || ($wn_t>4 && $k==$next_mondayt) ) { continue; } // в выходные после 16 блокируем запись на завтра и на ближайший понедельник
				if(  $fd==$date_t  ){ continue; }  // Запись на текущую дату закрыть 
				
				print '<option value="'.$this->graficx_i_grra2[$k].'">'.$this->graficx_i_grra2[$k].'</option>';  // 
			 }
		}
	  
	 
	function act__sel_time_zapisx()
	{
		if($_REQUEST['pom']){$pom=2;}else{$pom=1;}
		$this->get_graficx(1,$pom);
		// act: sel_time_zapisx
		// home_id: 26
		// apartament_num: 6
		// date: 28.02.2023
	// pom: 0
		// print_r($_REQUEST);
		 
		print '<option value="">Выбрать время</option>';
	  
		// ДОМ ТОЛЬКО В ОДНОЙ ГРУППЕ МОЖЕТ БЫТЬ НА ДАТУ
	$group = $this->graficx_i_grra[$_REQUEST['home_id']][$_REQUEST['date']]; // расшифровка [дом][дата] = группа
	 
		foreach( $this->graficx_i_za_gr_free2[$group] as $k=>$v )
		{
			if($v)
			{ 
				print '<option value="'.$k.'">'.$k.'</option>';  // 
			}
		}
		 
	}
	 
	
	
	
	// ЗАтычка проверка СВОБОДНОСТИ ДАТЫ + ВРЕМЕНИ
	function act__testfree($home='24',$date='14.03.2023',$time='10:00')
	{
		
		//print $home.'<br/>';
		//print $date.'<br/>';
		//print $time.'<br/>';
	 
	 
		$this->get_graficx(1);
	  
		// ДОМ ТОЛЬКО В ОДНОЙ ГРУППЕ МОЖЕТ БЫТЬ НА ДАТУ
	$group = $this->graficx_i_grra[$home][$date]; // расшифровка [дом][дата] = группа
  
		// print_r($this->graficx_i_za_gr_free2[$group]);
  
		if( $this->graficx_i_za_gr_free2[$group][$time] )
		{
			return 1;	 
		}
	else
	{
			return 0;
	} 
	}
  
  
############## /ЗАПИСЬ
   
  
  
  
  
 function mounthmenu()
 {
		if(!$_GET['month']){	$tm = date('m');	}	
		else{	$tm = $_GET['month'];	}	
		
		if(!$_GET['year']){	$ty = date('Y');	}
		else{	$ty = $_GET['year'];	}	
						
		$y=date('Y');

		print '<b>Месяц:</b> ';
		for( $m=1; $m<=12; $m++ )
	{
			if($m==$tm && $y==$ty){$st=' style="font-weight:bold; font-size: 16px;" ';}
			else{$st='';}
			if(strlen($m)<2){$m = '0'.$m;} 
			print ' / <a href="?ctr=zapisx&month='.$m.'" '.$st.'>'.$m.'</a>  ';
		}
		
		$m='01';
		$y = date('Y')+1;
		if($m==$tm && $y==$ty){$st=' style="font-weight:bold; font-size: 16px;" ';  }
		else{$st='';}
		
		print ' / <a href="?ctr=zapisx&month=01&year='.$y.'" '.$st.'>01-'.$y.'</a>  ';
		print '<br/><br/>';
 }
  
  
  
	function act__index()
	{
		global $t;
		$t['h1']='Редактор графика';
		$this->get_graficx(false,false,1); // 
		
		$this->mounthmenu();
		
		if($_GET['month']){$mounth=$_GET['month'];}
		else{ $mounth = date('m'); }
		
		if($_GET['year']){$year=$_GET['year'];}
		else{ $year = date('Y'); }
		 
		
		?>
		<style>
		.geditor_frame{  }
	.geditor_day{display: block; border:solid 1px #000; width:14%; min-width:100px; min-height:210px; position:relative; float:left; margin:1px; padding-bottom: 10px;}
		.geditor_dayna{display:block; border:solid 1px #EEE; width:14%; min-width:100px; min-height:210px;  position:relative; float:left; margin:1px;}
		.geditor_daytop {background:#00CDAD;  color:#FFF; font-size:12px; padding:1px; text-align:right; padding-right:10px;}
		.geditor_gr{border:solid 1px #000;  font-size:12px; margin:1px;}
		.geditor_gr_title{border:solid 1px #2F4049; padding:1px; font-size:12px; background:#2F4049; color:#00CDAD; }
		.geditor_gr_body{ padding: 3px;}
		.geditor_gr_editlink{background:#FFF; color:red; padding-left:3px; padding-right:3px;}
		.gret{border-bottom:solid 1px #EEE; }
		.gtt{width:40px; display:inline-block;}
	.ajax_status{font-size:12px;}
		.home_check{display: inline-block; border:1px solid #00; padding:3px;}
		.gr_homes{
			background:#CCC; color:#000; font-size: 12px; 
			border-radius: 10px 0 0;
			padding-left: 10px;
			padding-right: 10px;
			margin: 1px; position:relative;
			}
		.gr_gr{border:solid 1px  #9; border-radius:10px; width:98%; margin:2px; }
	.gr_gr_hide{border:solid 1px #99; border-radius:10px; width:98%; margin:2px; opacity:0.6; }
	.gr_times{font-size:12px; padding:3px; }
	.gr_time{border-bottom:1px solid #CCC; font-size:12px; cursor:default;}
		.gr_time:hover{background:#F0F0FF; }
		
		.gr_time_over{color:red; font-size:10px;}
		.gr_add_link{position:absolute; bottom:0; left:0; text-align:center; width:100%; border:solid 1px red; background:#000; color:#FFF; font-size:10px;}
		
		
		.gr_edit_link{  width:100%; border-radius:20px; color:#000;  }
		
		
		.del_link{     }
		
		.gr_realc{color:green; font-weight:bold;}
		
		.daypanellink{color:red;}
		.daypanellink2{color:#000;}
		</style>

		<div class="geditor_frame">
		<?
		
		$start = strtotime('01.'.$mounth.'.'.$year);
		$finish = strtotime('+1 MONTH', $start );
		//$finish = strtotime(date('Y-m-t'));
  
		for($i = $start; $i < $finish; $i += 8640)// Цикл по суткам
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
					<div class="geditor_daytop" style="color:<?=$dcolor?>">
						<b>
						<?= $this->w_rus[$dn] ?> / 
						<?= $day_date ?>
						</b> 
					</div>
					<?=$this->disp_gr_day($day_date);?>
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
			?><div class="<?=$grclassx?>"><?
				// print 'Группа'.$k.'<br/>';
				?>
				<div class="gr_homes"><?
				//Выводим дома 
				foreach( $this->graficx_i_homes[$k] as $k1 => $v1 )
				{
					 print '<b>'.$this->select_homes[$k1].'</b> ';
				}
				?>
				
				</div>
				
				<div class="gr_times"><?
				//Выводим Время 
				foreach($this->graficx_i_times[$k] as $k2=>$v2)
				{
					 
					$tit =''; $tt ='';
					// Если есть записи на дату время выводим тт
					if($this->zapis_i_dt[$date][$k2] )
					{
						foreach($this->zapis_i_dt[$date][k2] as $vx1=>$vx2)
						{
							$tt =$vx2['home_id'];
							$tt.=$vx2['home_id'];
							$tt.=$vx2['apartment_num'];
							$tt.=$vx2['fio'];
						}
						//$tit =' rel="tooltip" title="'.$tt.'" ';
					}
					else{$tit =''; $tt ='';}
			
					print '<div class="gr_time" '.$tit.'>'.$k2.'-'.$v2;
					if( $this->graficx_i_pom[$k][$k2] ){ print   ' <b> - П</b>'; }
					
					// Реальные записи на это время всего 
					$count_realzapis = count($this->zapis_i_dt[$date][$k2]);
					if(!$count_realzapis){$count_realzapis='';}
					
					print ' / <span class="gr_realc">'.$count_realzapis.' </span>';
					
					print '</div>';
				}
				?>
				</div>
				<div style="text-align:right; width:100%;"> 
				
				<?
				if($this->graficx_i_date_b[$k]['show'])
				{
				?>
				<a class="gr_edit_link daypanellink" href="<?=$r->acturl('zapisx','h_daygroup');?>&date=<?=$date?>&id=<?=$k?>&month=<?=$_GET['month']?>"><span class="mdi mdi-eye fs21"></span></a> 
				<?
				}
				elseif(!$this->graficx_i_date_b[$k]['show'])
				{ 
				?>
				<a class="gr_edit_link daypanellink2" href="<?=$r->acturl('zapisx','s_daygroup');?>&date=<?=$date?>&id=<?=$k?>&month=<?=$_GET['month']?>"><span class="mdi mdi-eye-off fs21"></span></a> 
				<?
				}
				?>
				
				<a class="iframe_r gr_edit_link daypanellink" href="<?=$r->acturl('zapisx','edit_daygroup','iframe_router.php');?>&date=<?=$date?>&id=<?=$k?>"><span class="mdi mdi-playlist-edit fs21"></span></a> 
				<a class="del_link delconfirm daypanellink" href="<?=$r->acturl('zapisx','edit_daygroup');?>&act=del&id=<?=$k?>&month=<?=$_GET['month']?>"><span class="mdi mdi-delete-circle-outline fs21"></span></a> 
				</div>
				<?
			?></div><?
	}
		
		
		foreach($this->zapis_i_dtnr[$date] as $kx =>$vx)
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
			print ' / <span class="gr_realc gr_time_over">'.count($this->zapis_i_dtnr[$date][$kx]).'</span>';
			print '</div>';
		}
		
		?>  
		<a class="iframe_r gr_add_link" href="<?=$r->acturl('zapisx','edit_daygroup','iframe_router.php');?>&date=<?=$date?>">добавить</a><?
	}
	
	
	
	
	   
	# РЕДАКТИРОВАНИЕ ДОБАВЛЕНИЕ ГРУПЫ ОБЕКТОВ В ДНЕ
	function act__edit_daygroup()
	{
	global $filed;
	global $mysql;
		
		$date = $_GET['date'];
		$id =  $_GET['id'];
		
	if( $_POST )
		{
			// ДОбавляем новую запись на дату 
			if( !$id )
			{
				$date_insert = array();
				$date_insert['date'] = $_GET['date'];
				$date_insert['show'] = "0";
				$date_insert['date_mysql'] = date('Y-m-d', strtotime( $_GET['date'] ) );
				$id = $mysql->insert('keys_graficx_date',$date_insert,false);
			}
			if(!$id){die('Ошибка добавления записи');}
		 
			// Удаляем все записи домов для сессии
			 $mysql->sql(' DELETE FROM keys_graficx_objects WHERE keys_graficx_date_id="'.$id.'" ');
			// ДОбавляем записи домов для сессии
			foreach($_POST['objects'] as $k=>$v)
			{ 
				if($v)
				{
				$date_insert = array();
				$date_insert['keys_graficx_date_id'] = $id;
				$date_insert['object_id'] = $k;
				$mysql->insert('keys_graficx_objects',$date_insert,false);
				}
			}
		 
			// Удаляем записи времени для сессии
			//$mysql->
			$mysql->sql(' DELETE FROM keys_graficx_time WHERE keys_graficx_date_id="'.$id.'" ');
			// добавляем записи времени для сессии
			foreach($_POST['times'] as $k=>$v)
			{ 
				if($v)
				{
				$date_insert = array();
				$date_insert['keys_graficx_date_id'] = $id;
				$date_insert['time'] = $k;
				$date_insert['c'] = $v;
				if($_POST['times_pom'][$k]) { $date_insert['pom'] = $_POST['times_pom'][$k]; }else{ $date_insert['pom']=0;}
				$mysql->insert('keys_graficx_time',$date_insert,false );
				}
			}
			 
		}
	 
		
		// Ид Записи если ест редактирование 
		if( $id )
		{
			$data_objects = array(); // [ид]=1/0
			$data_times=array(); //[время] = записей
			
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
			}
		}
			print '<div style="padding:30px; margin:20px;">
			Редактирование группы объектов '.$_GET['date'].'
			print '<hr>';
		
			// Добавление записи в указанную дату 
			?>
			<style>
			.home_check{display:inline-block; padding:3px; border:solid 1px #EEE;     margin-right: 5px;}
			.home_check label{margin-bottom:0; cursor:default;}
			.time_filed{ display: inline-block; width:60px;}
			.time_filed .input_title{margin-bottom:2px;}
			</style>
			
			<form action="" method="POST">
			<?
				//$filed->text('date','Дата',$date);
				//$filed->text('id','id',$id);
				
				print ' <span class="input_title">ОБЪЕКТЫ</span>';
				foreach($this->homes_arr as $k=>$v)
				{
					?><div class="home_check"><?
					$filed->checkbox('objects['.$k.']',$v['title'],$data_objects[$k]);
					?></div><?				
				}
				
				print ' <span class="input_title">Время</span>';
				foreach($this->time_arr as $k=>$v)
				{
					?>
					<div class="time_filed">
					<?
					$filed->text_num('times['.$k.']',$k,$data_times[$k],0,10,1); print '<br/>';
					$filed->checkbox('times_pom['.$k.']','Пом',$data_times_pom[$k]);
					?>
					</div>
					 
					<?				
				}
			?>	
			<br/><br/>
			<input type="submit" value="Сохранить" />
			</form>
			</div>
			<?
  
	}
	  
	  
	  
	  
	  // Статистика дома
	  function act__stat_home()
	  {
		  // ВЫводим дом с кастомным методом как для печати!
	  }
	  
	 
	 
	 
	 
	 
	function act__zapisformx()
	{
		global $mysql;
		global $r;
		// print '<pre>';
		// print_r( $_POST );
		// print '</pre>';
			 
		$zapisx = $r->get_object('zapisx'); //конироллер zapisx
	if(!$_POST['pom']){$_POST['pom']=0;}else{$_POST['pom']=1;}
		$data=array();
		$data['home_id'] = (int) $_POST['home_id'];
		$data['date'] =  $_POST['date'] ; // Преобразуем дату в формат MYSQL
	$data['section']= (int) $_POST['section_id'];
		$data['apartment_num']= (int) $_POST['apartament_num'];
		$data['time']= $_POST['time'].':00';
		$data['phone']= $_POST['phone'];
		$data['new_passport']= (int) $_POST['passprot'];
		$data['fio']= $_POST['fio'];
		$data['pom']= $_POST['pom'];
		$data['email']= $_POST['email'];
		$data['at']= time();
  
		$q = 'SELECT zapis.* ,homes.title, homes.keys_adress FROM zapis LEFT JOIN homes ON homes.home_id = zapis.home_id WHERE 1=1 AND del="0" AND `zapis`.`home_id` = "'.$data['home_id'].'" AND `zapis`.`apartment_num` = "'.$data['apartment_num'].'" ';
			 
		$row = $mysql->get_arr($q,1);
		
		if($row) // Есть запись
	{
			// print '<b>Есть запись</b>';
			$this->card('',$row,'Вы были записаны ранее'); //  card($id='',$data='', $title='')
		}
		else
		{
			//print '<b>НЕТ записи</b>';
			// !!!!!!!! ВАЛИДАЦИЯ !!!!!!!!
			$free = 0;
			//$free =  $this->settings_arr_date_time_free[$data['home_id']][$data['date']][$data['time']];
			  
			  // $_POST['time'] - ПО тому что в массиве при проверке время в формате xx:xx 
			$free = $this->act__testfree($data['home_id'],$data['date'],$_POST['time']);// Другим контроллером получаем !свободность для дома времени !
			
			 
			if( 0 < $free )
			{
				$data['date'] =  date("Y-m-d", strtotime( $data['date'] )); // Преобразуем дату в формат MYSQL
				$insid = $mysql->insert( 'zapis' , $data );
				if( $insid )
				{ 	 
					ob_start();
					$this->card($insid,'','Ваша заявка принята'); //  card($id='',$data='', $title='')
					print $con = ob_get_clean();
					
					multi_attach_mail('89236470002@mail.ru', 'Запись на выдачу ключей - дом:'.$this->homes_arr[$data['home_id']]['title'].' кв.:'.$data['apartment_num'].' дата:'.$_POST['date'].' время:'.$data['time'], $con, 'admin@<?=$GLOBALS['config']['domains']['main']?>', $GLOBALS['config']['domain']);
					multi_attach_mail($data['email'], 'Запись на выдачу ключей - дом:'.$this->homes_arr[$data['home_id']]['title'].' кв.:'.$data['apartment_num'].' дата:'.$_POST['date'].' время:'.$data['time'], $con, 'admin@<?=$GLOBALS['config']['domains']['main']?>', $GLOBALS['config']['domain']);
					multi_attach_mail('op15@em-nsk.group', 'Запись на выдачу ключей - дом:'.$this->homes_arr[$data['home_id']]['title'].' кв.:'.$data['apartment_num'].' дата:'.$_POST['date'].' время:'.$data['time'], $con, 'admin@<?=$GLOBALS['config']['domains']['main']?>', $GLOBALS['config']['domain']);
			 
					/// ОТПРАВКА НА ПОЧТУ	
				}
				else
				{
					print '<center> <br/><br/><br/>Произошла ошибка - дата и время которые вы выбрали уже занято<br/> попробуйте  записаться на другое время </center>';
				}
			}
			else
			{
				print $free;
				print '<center> <br/><br/><br/>! Произошла ошибка - дата и время которые вы выбрали уже занято<br/> попробуйте  записаться на другое время </center>';
			}
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
		 <form method="post" action="<?=$GLOBALS['config']['domains']['em']?>/sahmatka/ajax_router.php?ctr=zapiskeys&act=zapisformx" id="zapisform">
		 <div id="form_progressbar">Загрузка...<br/><img src="<?=$GLOBALS['config']['domains']['em']?>/sahmatka/loader.gif"></div>

			<?=$filed->checkbox('admin_mode','<b>ЛЮБЫЕ ДОМА И ВРЕМЯ</b>',$_GET['admin_mode'] ,' data-bl="-1" ' , 'admin_mode');?><br/>
		
			<?=$filed->checkbox('pom','Помогающая компания', $_GET['pom'],' data-bl="0" ' , 'pom' );?><br/>
		
			
			<select class="input_edit" name="home_id" id="home_id" class="suk_form" data-bl="1" required="required" disabled><option value="">Номер дома</option></select> <br/>
			 
			<select class="input_edit" name="section_id" id="section_id" class="suk_form" data-bl="2" required="required" disabled><option value="">Подъезд</option> </select> <br/>
			 
			<select class="input_edit" name="apartament_num" id="apartament_num"  class="suk_form" data-bl="3" required="required" disabled><option value="">Квартира</option> </select> <br/>
			 
			<select class="input_edit" name="date" id="date" class="suk_form" data-bl="4" required="required" disabled><option value="">Дата</option></select> <br/>
		 
			<select class="input_edit" name="time" id="time" class="suk_form" data-bl="5" required="required" disabled><option value="">Время</option> </select> <br/>
			<input class="input_edit" name="phone" id="phone" class="suk_form phone_mask"  type="tel" placeholder="Телефон" required="required">  <br/>
			<input class="input_edit" name="email" id="email" class="suk_form" type="email" placeholder="E-Mail" required="required">   <br/>
		    <input class="input_edit" name="fio" id="fio" class="suk_form" type="text" placeholder="ФИО" required="required">   <br/>
			<?=$filed->submit()?>
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
			
			
			$("#home_id").fwloadx("<?=$GLOBALS['config']['domains']['em']?>/sahmatka/ajax_router.php?ctr=zapisx&act=sel_home_zapis",function() {
				$('#home_id option[value='+home_id+']').prop('selected', true);
			});
			 
			 
			 
			$("#admin_mode").change(function() 
			{
				if ($(this).val()){
					$("#home_id").fwloadx("<?=$GLOBALS['config']['domains']['em']?>/sahmatka/ajax_router.php?ctr=zapisx&act=sel_home_zapis");
				}
				else{
					blocked_child_select($(this).attr('data-bl'));
				}
			});
			
			$("#pom").change(function() 
			{
				if ($(this).val()){
					$("#home_id").fwloadx("<?=$GLOBALS['config']['domains']['em']?>/sahmatka/ajax_router.php?ctr=zapisx&act=sel_home_zapis");
				}
				else{
					blocked_child_select($(this).attr('data-bl'));
				}
			});
			
			
			
			$("#home_id").change(function() 
			{
				if ($(this).val()){
					$("#section_id").fwloadx("<?=$GLOBALS['config']['domains']['em']?>/sahmatka/ajax_router.php?ctr=zapisx&act=sel_section_zapis");
				}
				else{
					blocked_child_select($(this).attr('data-bl'));
				}
			});
			
			$("#section_id").change(function() 
			{
				if ($(this).val()){
					$("#apartament_num").fwloadx("<?=$GLOBALS['config']['domains']['em']?>/sahmatka/ajax_router.php?ctr=zapisx&act=sel_apartament_zapis");
				}
				else{
					blocked_child_select($(this).attr('data-bl'));
				}
			});
			
			
			$("#apartament_num").change(function() 
			{
				if ($(this).val()){
					$("#date").fwloadx("<?=$GLOBALS['config']['domains']['em']?>/sahmatka/ajax_router.php?ctr=zapisx&act=sel_date_zapisx");
				}
				else{
					blocked_child_select($(this).attr('data-bl'));
				}
			});
			
			
			
			 
			$("#date").change(function() 
			{
				if ($(this).val()){
					$("#time").fwloadx("<?=$GLOBALS['config']['domains']['em']?>/sahmatka/ajax_router.php?ctr=zapisx&act=sel_time_zapisx");
				}
				else{
					blocked_child_select($(this).attr('data-bl'));
				}
			});
			
			
			 
			 
			 
		
		( "#form_home_submit" ).click(function() {	( "#zapisform" ).submit(); return false;	});
			
		 
		
		
		});
        </script>
		<?
	 }
	  
	  
}