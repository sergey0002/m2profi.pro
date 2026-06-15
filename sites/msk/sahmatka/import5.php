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
 
  $map_id = '59'; // 56 57 бахтеево (52 фенино лесное) 58 - РП,  59,60 - УН        61 патрикеево
      
 
    // die('Выключено');
 
 $data = $mysql->get_arr( ' SELECT * FROM `landplots` WHERE `map_id` = "'.$map_id.'" ');
 foreach($data as $k=>$v)
 {
	 $data_nums[$v['kadastrnum']] = $v;
 }

 
 
 
?><table border="1"><tr><?
foreach($array as $k=>$v )
{ 

?><tr><?
	$str_arr = explode('	' ,$v);
	

 
	 
	foreach($str_arr as $kk=>$vv)
	{
		$str_arr[$kk] = trim($str_arr[$kk]);
	}
	
	 
	$kadastr = $str_arr[0]; // Кадастровый номер
	$area = $str_arr[1]; // площадь участка
	   
			 
	// Удаляем пробелы
	$area = str_replace(' ', '', $area);
	// Делим строку на две части по точке или запятой и берем первую часть
	$area = explode('.', explode(',', $area)[0])[0];
	
	$orig_data = $data_nums[$kadastr]; // Оригинальные данные участка 
	
	
	
	if(  $kadastr && $orig_data['num'] )
	{ 

		print '<td> Номер'.$orig_data['num'].'</td>';
		print '<td> Кадастровый'.$orig_data['kadastrnum'].'</td>';
	
		
		print '<td> Площадь оригинальная: '.$orig_data['area'].'</td>';
	 
		//if(!$orig_data){print '<h2>В базе нет данных по участку с номером '.$num.'</h2>'; continue;}
		## Площадь
		
		$area = (float) $area;
		$orig_data['area'] = (float) $orig_data['area'];
		
		if( $area != $orig_data['area'] && trim($area))
		{
			
			print '<td> Площадь новая: '.$area.'</td>';
			//print '<h1>Участок №'.$orig_data['num'].'</h1>';
	 
			//print '<h2>Отличается площадь  Новое значение='.$area.' старое значение '.$orig_data['area'].'</h2>';
			//print '<hr/>';
			
			 print $sql=' UPDATE `landplots` SET `area` = "'.trim($area).'" WHERE `map_id` = "'.$map_id.'" AND `kadastrnum` = "'.trim($kadastr).'" ';
			 $mysql->sql( $sql);
		}
		else
		{
			print '<td>Площадь совпадает </td>';
		}
	 
	}
	
	print '<td> Оригинальная цена - '.$orig_data['price'].'</td>'; 
		$price = ($orig_data['area']/100)*$orig_data['price_area'];  
		print '<td> Расчитаная новая цена - '.$price.'</td>'; 
	  
	  
		## ЦЕНА  
		if(trim($price)!=trim($orig_data['price']) && trim($price) )
		{
			 
			print '<td>НОВАЯ ЦЕНА ='.$price.' / Старая цена ='.$orig_data['price'].'</td>';
			
			print $sql=' UPDATE `landplots` SET `price` = "'.$price.'" WHERE `map_id` = "'.$map_id.'" AND `kadastrnum` = "'.trim($kadastr).'" ';
			 $mysql->sql( $sql);
			//
			
			 
			
			
			
		}
		else
		{
			
			print '<td>Цена совпадает</td>';
		}
		
		
		
  
	
	if($i>10000){break;}
	$i++;
	?></tr><?
}
?><table><?

print 123;