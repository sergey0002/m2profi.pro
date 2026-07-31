<?php
include('config.php');
 
/*
В гет переменной имя списка задается который грузим
а также значения всех родителских списков

в формате [список]=значение
*/


// print_r($_REQUEST);
$home_id = $_REQUEST['home'];
$rooms = $_REQUEST['rooms'];
$area = $_REQUEST['area'];







  $sql ='
 SELECT apartaments.apartment_num ,apartaments.rooms, apartaments.area, apartaments.price, br.apartments_num , br.status  FROM `apartaments` 
  
  LEFT JOIN (
	 SELECT apartments_num, status FROM `broni`
	 where `home_id` = "'.$home_id.'"
	 AND date=(SELECT max(date) FROM broni as b WHERE `home_id` = "'.$home_id.'" AND `b`.`apartments_num`=`broni`.`apartments_num`)
 ) 
  as br on (br.apartments_num = apartaments.apartment_num) where apartaments.home_id="'.$home_id.'"

';


  	$query = mysqli_query($connection, $sql); 
	
	while ($result = mysqli_fetch_array($query)) 
	{	
 

		//print_r($result);
		if($result[status]=="NULL" || !$result[status]){$result[status]=2;}

		if($result[status]==2)
		{
			$result2[] = $result;
			// Количество квартир для каждого количества комнат  
			$rooms_count[$result['rooms']]++;
			//print $result['rooms'];
			//print '<br/>';
		 
 
			// Площади каждого типа квартир уник
			$area_count[$result['rooms']][$result['area']]++;
			
			//Цены		
			$price_count[$result['rooms']][$result['area']][$result['price']]++;
		}
		
	}	
	 
	
 // print_r($rooms_count);

	// КОмнат
	// ('.$v.' квартир)
	asort($rooms_count);
		$roomst =  '<option value="">Выберите количество комнат</option>';
	foreach($rooms_count as $k => $v )	{ if($k){ $roomst.='<option value="'.$k.'">'.$k.' - ('.$v.')</option>'; }	}
	
	// Площадь
	asort($area_count[$rooms]);
	$areat =  '<option value="">Выберите площадь</option>';
	foreach($area_count[$rooms] as $k => $v ){	if($k){ $areat.='<option value="'.$k.'">'.$k.' м<sup>2</sup></option>'; }	}
	
	// Цена
	asort($price_count[$rooms][$area]);
	 $pricet='<option value="">Выберите цену</option>';
 	foreach($price_count[$rooms][$area] as $k => $v ){	if($k) { $pricet.='<option value="'.$k.'">'.$k.' руб.</option>'; }	}
	 
	 
	 
//print_r($price_count);
	 
	
if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' || 1==1)
{

	if($_REQUEST['load'] =='rooms' ) // вывод квартир по дому
	{
		print $roomst;     
	}
	
	
	elseif($_REQUEST['load'] =='area' ) // вывод квартир по дому
	{
	 //print_r($area_count);
	 
		print $areat;  
	}
	elseif($_REQUEST['load'] =='price' ) // вывод квартир по дому
	{
	 
		print $pricet;  
	}

} 



else 
{
    echo 'Bad request!';
    exit;
}

