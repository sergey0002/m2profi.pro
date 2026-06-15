<?
 // header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
 // header("Last-Modified: " . gmdate("D, d M Y H:i:s")." GMT");
//  header("Cache-Control: no-cache, must-revalidate");
 // header("Cache-Control: post-check=0,pre-check=0", false);
 // header("Cache-Control: max-age=0", false);
 // header("Pragma: no-cache");
 
 
 error_reporting(1);
include('config.php');
$array = file('5.txt');
$i=0;
 //N участка	кадастровый номер	S участка	стоимость участка	стоимость 1 сотки
 
  $map_id = '60'; // 56 57 бахтеево (52 фенино лесное) 58 - РП,  59,60 - УН        61 патрикеево
      
 
  //  die('Отключен');
 
 $data = $mysql->get_arr( ' SELECT * FROM `landplots` WHERE `map_id` = "'.$map_id.'" ');
 foreach($data as $k=>$v)
 {
	 $data_nums[$v['num']] = $v;
 }


 
 
 
 

foreach($array as $k=>$v )
{ 
	$str_arr = explode('	' ,$v);
	

 
	 
	foreach($str_arr as $kk=>$vv)
	{
		$str_arr[$kk] = trim($str_arr[$kk]);
	}
	
		$num = $str_arr[0]; // Номер участка
		$kadastr = $str_arr[1]; // Кадастровый номер
		$area = $str_arr[2]; // площадь участка
		$price_inp = $str_arr[3]; //ЦЕНА
		$sotka = $str_arr[4]; // СТОИМОСТЬ СОТКИ
		$status = $str_arr[5]; //  
 
		 
		 //1	50:08:0070349:777	601	1 262 100,00	210 000,00	1 652 750,00	275 000,00
		 // Удаляем пробелы
		$price_inp = str_replace(' ', '', $price_inp);
		// Делим строку на две части по точке или запятой и берем первую часть
		$price_inp = explode('.', explode(',', $price_inp)[0])[0];
				
				 
		 // Удаляем пробелы
		$area = str_replace(' ', '', $area);
		// Делим строку на две части по точке или запятой и берем первую часть
		$area = explode('.', explode(',', $area)[0])[0];
		
		
		
		$sotka = str_replace(' ', '', $sotka);
		// Делим строку на две части по точке или запятой и берем первую часть
		$sotka = explode('.', explode(',', $sotka)[0])[0];
				
		$sotka = (int) $sotka;

		
		
 
	
	if(  $num  )
	{
		 print '<hr/>';
		 
		 print '<h2>'.$num.'</h2>';
		 
		 print 'Текущий статус - '. $data_nums[$num]['status'];
 
		$orig_data = $data_nums[$num]; // Оригинальные данные участка 
		
		if(!$orig_data){print '<h2>В базе нет данных по участку с номером '.$num.'</h2>'; continue;}
		
		
		
		
		if(!$price_inp || !$sotka )
		{
			
			 
		// Показываем как временно не скрытый!
			Print 'Скрываем участок';
			print $sql=' UPDATE `landplots` SET `tmp_hide` = "1" WHERE `map_id` = "'.$map_id.'" AND `num` = "'.trim($num).'" ';
			//$mysql->sql( $sql);
		}
		
		
		
		if('свободен'==trim($status))
		{
			print $sql=' UPDATE `landplots` SET `tmp_hide` = "0" WHERE `map_id` = "'.$map_id.'" AND `num` = "'.trim($num).'" ';
			$mysql->sql( $sql);
		}
		
		## ЦЕНА ЗА СОТКУ
		 
		if(trim($sotka)!=trim($orig_data['price_area']) && trim($sotka))
		{
			print '<h1>Участок №'.$num.'</h1>';
			print '<h2>Отличается цена за сотку  Новое значение='.$sotka.' старое значение '.$orig_data['price_area'].'</h2>';
			print '<hr/>';
			print $sql=' UPDATE `landplots` SET `price_area` = "'.trim($sotka).'" WHERE `map_id` = "'.$map_id.'" AND `num` = "'.trim($num).'" ';
		 $mysql->sql( $sql);
		}
		 
		
		## Кадастровый номер 
		if(trim($kadastr)!=trim($orig_data['kadastrnum'])  )
		{
			print '<h1>Участок №'.$num.'</h1>';
			print '<h2>Отличается Кадастровый Новое значение='.$kadastr.' старое значение '.$orig_data['kadastrnum'].'</h2>';
			print '<hr/>';
			
			
			print '<pre>';
			print_r($str_arr);
			print_r($orig_data);
			print '</pre>';
		
			print '<br/>';
		
		
		//	print $sql=' UPDATE `landplots` SET `kadastrnum` = "'.trim($kadastr).'" WHERE `map_id` = "'.$map_id.'" AND `num` = "'.trim($num).'" ';
		//	$mysql->sql( $sql);
		}
		 
		//тренд 
		
		## Площадь
		if(trim($area)!=trim($orig_data['area']) && trim($area))
		{
			print '<h1>Участок №'.$num.'</h1>';
	 
			print '<h2>Отличается площадь  Новое значение='.$area.' старое значение '.$orig_data['area'].'</h2>';
			print '<hr/>';
			
			print $sql=' UPDATE `landplots` SET `area` = "'.trim($area).'" WHERE `map_id` = "'.$map_id.'" AND `num` = "'.trim($num).'" ';
		//	$mysql->sql( $sql);
			
			
		}
		 
	    $price = $price_inp;
 
		if(!$price){   $price = ($orig_data['area']/100)*$sotka ; }
		 
	  
	  
		## ЦЕНА  
		if(trim($price)!=trim($orig_data['price']) && trim($price) )
		{
			 
			print '<h2>НОВАЯ ЦЕНА ='.$price.' / Старая цена ='.$orig_data['price'].'</h2>';
			
			print $sql=' UPDATE `landplots` SET `price` = "'.$price.'" WHERE `map_id` = "'.$map_id.'" AND `num` = "'.trim($num).'" ';
			 $mysql->sql( $sql);
			//
			
			print '<hr/>';
			
			
			
		}
		
		
		
		
  //	
 
	  //	$mysql->sql( $sql);
	}
 

	
	
	
	if($i>10000){break;}
	$i++;
}


print 123;