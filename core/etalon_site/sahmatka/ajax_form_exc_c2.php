<?php
  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
  header("Last-Modified: " . gmdate("D, d M Y H:i:s")." GMT");
  header("Cache-Control: no-cache, must-revalidate");
  header("Cache-Control: post-check=0,pre-check=0", false);
  header("Cache-Control: max-age=0", false);
  header("Pragma: no-cache");
    
include('config.php');


//check_count_zapis(1,'01.02.2020','13:00'); Количество записей на экскурсию 
function check_count_zapis($home,$data,$time)
{
	$tsql = 'SELECT count(*) as c FROM `excurs` WHERE "'.$data.'" = DATE_FORMAT(date,"%d.%m.%Y") AND `home` = "'.$home.'" ';
	
	if($time){$tsql .= ' AND `time` = "'.$time.'" ';}
	
	$query = mysqli_query($GLOBALS[connection], $tsql); 
	while ($result = mysqli_fetch_array($query)) { $x = $result[0] ; }	
	return $x;
}

 $sp_dates0=array(); // не рабочие дни
/*
В гет переменной имя списка задается который грузим
а также значения всех родителских списков

в формате [список]=значение
*/
 
 // ПОДСЧЕТ КОЛИЧЕСТВА ЗАПИСЕЙ ПО КАЖДОМУ ДОМУ
 if($_REQUEST[home])
 {
	$sql =' SELECT count(*) as c,date, time
	FROM `excurs`
	WHERE `home` = "'.$_REQUEST[home].'"
	GROUP BY date,time
	';

  	$query = mysqli_query($connection, $sql); 
	
	while ($result = mysqli_fetch_array($query)) 
	{	
		$count_datest[$result[date]][$result[time]]=$result[c];
		$count_dates[$result[date]]=$result[c];
	}	
	 
	// print_r($count_dates);
 }	 
 
		$w=date("w", strtotime($_REQUEST[data])); // номекр дня недели
		
		// даты со спец режимом РОДНИКИ (402,600 серия )
		$sp_dates[]='';
		// даты со спец режимом ПРИОЗЕРНЫЙ (702 дом)
		$sp_dates2[]='';
		// даты со спец режимом ЗАЛЕЕССКИЙ
		$sp_dates3[]='';
 	 
  
if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' || 1==1)
{
 
	if($_REQUEST['load'] =='time' ) //  
	{
		
				if($_REQUEST['home'] == 2) // ШОУРУМ
				{
					if(@in_array($_REQUEST[data],$sp_dates)) // спец даты
					{
						?>
							 
						<?
					} 
					else  
					{
						// Суббота воскресенье ПН СР ПТ СБ ВС  , 1 3 5 6 0
						$w = date("w", strtotime($_REQUEST[data]));
						if($w!=0 ) //Вт, чт. 13.00
						{		
							 
							if( 1 > check_count_zapis(2,$_REQUEST[data],'10:00') ) //  
							{
								?>
								<option value="10:00">10:00</option>
								<?
							}	 
							if( 1 > check_count_zapis(2,$_REQUEST[data],'13:00') ) //  
							{
								?>
								<option value="13:00">13:00</option>
								<?
							}	 
							if( 1 > check_count_zapis(2,$_REQUEST[data],'16:00') ) //  
							{
								?>
								<option value="16:00">16:00</option>
								<?
							}	 
						}
					}
				}	
	}
}
else 
{
    echo 'Bad request!';
    exit;
}

