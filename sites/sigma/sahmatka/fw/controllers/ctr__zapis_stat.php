<?
 
class ctr__zapis_stat extends ctr__
{ 
	var $table = 'keys_graficx_date'; //Главная таблица
	var $key_filed = 'keys_graficx_date_id'; // Ключевое поле главной таблицы
	var $ctr = 'zapis_stat';
	 
	
	function __construct()
	{
		 
	}
	
	
	
	
	function display_table__zapis_plan_c_nopom($v)
	{
		if(!$v['zapis_plan_c_nopom']){ return '<span style="color:#EEE;">0</span>';}
		else{return $v['zapis_plan_c_nopom'] ;}
	}
	function display_table__zapis_plan_c_pom($v)
	{
		if(!$v['zapis_plan_c_pom']){ return '<span style="color:#EEE;">0</span>';}
		else{return $v['zapis_plan_c_pom'] ;}
	} 
	
	
	
	
	function display_table__c_pom($v)
	{
		if(!$v['c_pom']){ return '<span style="color:#EEE;">0</span>';}
		else{return $v['c_pom']-$v['zapis_plan_c_pom'];}
	}
	function display_table__c_nopom($v)
	{
		if(!$v['c_nopom']){ return '<span style="color:#EEE;">0</span>';}
		else{return $v['c_nopom']-$v['zapis_plan_c_nopom'];}
	} 
	  
	
	
	  function act__index()
	  {
		  global $mysql;
		  global $t;
		    $t['h1'] = 'Статистика записи';
		  
		// Запрос количество доступных мест в обектах по графику С ПО
		$q='SELECT keys_graficx_objects.object_id, keys_graficx_time.pom,  sum(keys_graficx_time.c) as c_pom
		FROM keys_graficx_date 
		LEFT JOIN keys_graficx_time ON keys_graficx_date.keys_graficx_date_id = keys_graficx_time.keys_graficx_date_id
		LEFT JOIN keys_graficx_objects ON keys_graficx_date.keys_graficx_date_id = keys_graficx_objects.keys_graficx_date_id
		WHERE keys_graficx_date.del="0"
		AND `keys_graficx_time`.`pom`="1"
		AND  `keys_graficx_date`.`show` = "1"
		AND keys_graficx_date.date_mysql>= CURDATE()  

		GROUP BY keys_graficx_objects.object_id ';
		$datax = $mysql->get_arr($q);
	
	
		// Запрос количество доступных мест в обектах   по графику без ПО
		$q='SELECT keys_graficx_objects.object_id, keys_graficx_time.pom,  sum(keys_graficx_time.c) as c_nopom
		FROM keys_graficx_date 
		LEFT JOIN keys_graficx_time ON keys_graficx_date.keys_graficx_date_id = keys_graficx_time.keys_graficx_date_id
		LEFT JOIN keys_graficx_objects ON keys_graficx_date.keys_graficx_date_id = keys_graficx_objects.keys_graficx_date_id
		WHERE keys_graficx_date.del="0"
		AND `keys_graficx_time`.`pom`="0"
		AND `keys_graficx_date`.`show` = "1"
		AND keys_graficx_date.date_mysql>= CURDATE()  

		GROUP BY keys_graficx_objects.object_id  ';
		$datax2 = $mysql->get_arr($q);
		
	
	
	
	
		// Заплнированно записей
		$sql = '
			SELECT home_id, count(*) as zapis_plan_c_nopom
			FROM zapis 
			WHERE "1" = "1"
			AND zapis.del="0"
			AND zapis.date >= CURDATE() 
			AND zapis.pom="0"
			GROUP BY zapis.home_id
			';
		$datax3 = $mysql->get_arr($sql);
			
		$sql = '
			SELECT home_id, count(*) as zapis_plan_c_pom
			FROM zapis 
			WHERE "1" = "1"
			AND zapis.del="0"
			AND zapis.date >= CURDATE() 
			AND zapis.pom="1"
			GROUP BY zapis.home_id
			';
		$datax4 = $mysql->get_arr($sql);
			
			print '<pre>';
			//print_r($datax4);
			print '</pre>';
		
		  $sql = '
			SELECT
			zapis.home_id, 
			homes.title, 	
			count(*) as allz,
			(SELECT count(*) as cx FROM apartaments WHERE apartaments.home_id = zapis.home_id) as all_app ,
			(SELECT count(*) as cx2 FROM apartaments WHERE apartaments.home_id = zapis.home_id) - count(*) as allcx,
			min(zapis.date) as date_start,
			DATEDIFF(CURRENT_DATE(),min(zapis.date)) as days,
			count(*)/DATEDIFF(CURRENT_DATE(),min(zapis.date)) as appperd,
			concat( ROUND( (count(*)*100/(SELECT count(*) as cx FROM apartaments WHERE apartaments.home_id = zapis.home_id)),2 ),"%") as prec
			FROM zapis 
			LEFT JOIN homes ON homes.home_id= zapis.home_id
			WHERE "1" = "1"
			 
			AND zapis.del="0"
			AND show_keys >0
			GROUP BY zapis.home_id 
			ORDER by prec  
			';
			
			$data = $mysql->get_arr($sql);
				
			
			$titles=array();
			$titles['title'] = 'Дом';
			$titles['all_app'] = unit_label('pl_gen');
		
			
			$titles['allz'] = 'Записей';
			$colattr['allz'] = 'style="border-left:1px solid #EEE;"';
			$titles['allcx'] = 'Осталось';			
			$titles['prec'] = '% Записей';
			
			$titles['zapis_plan_c_nopom'] =' Записаны БЕЗ ПО';	
			$colattr['zapis_plan_c_nopom'] = 'style="border-left:1px solid #EEE;"';
			
			$titles['c_nopom'] = 'Доступно без ПО';
			
			$titles['zapis_plan_c_pom'] = 'Записаны с ПО';	
			$colattr['zapis_plan_c_pom'] = 'style="border-left:1px solid #EEE;"';
			$titles['c_pom'] = 'Доступно с ПО';
			
			
			
			
			
			//$titles['date_start'] = 'Первая запись';
				 
			// $titles['days'] = 'Дней';
			 //$titles['appperd'] = 'Квартир/день';
			
			$data = $mysql->get_arr($sql);
			
			// Подклеиваем один результат к другому!
			$data = $mysql->split_data($data,$datax,'home_id','object_id');
			$data = $mysql->split_data($data,$datax2,'home_id','object_id');
			$data = $mysql->split_data($data,$datax3,'home_id','home_id');
			$data = $mysql->split_data($data,$datax4,'home_id','home_id');
			 print '<pre>';
			//print_r($datax);
			 // print_r($data);
			 print '</pre>';
		
			
			
			 ?>
			<div class="stat">
				<div class="stat-top stat-top_lp stat-top_user">
				</div>
				<div class="stat-table stat-table_notpd stat-table-user table">
				<?			
					$this->display_table($data,$titles,false,1,$colattr);
					
 
				?>
				</div>
			</div>			
	 <?
			 
	  }
	  
	  
	  
}