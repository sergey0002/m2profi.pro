<?php
 
  
  
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR );
 
include('config.php');
 

//check_count_zapis(1,'01.02.2020','13:00'); Количество записей на экскурсию 
function check_count_zapis($home,$data,$time='')
{
 
	$tsql = 'SELECT count(*) as c FROM `excurs` WHERE "'.$data.'" = DATE_FORMAT(date,"%d.%m.%Y") AND `home` = "'.$home.'" AND excurs.del="0" ';
	
	if($time){$tsql .= ' AND `time` = "'.$time.'" ';}
	
	$arr = $GLOBALS['mysql']->get_arr($tsql,1);
	return $arr['c'];
}

$w_rus[1]='ПН';
$w_rus[2]='ВТ';
$w_rus[3]='СР';
$w_rus[4]='ЧТ';
$w_rus[5]='ПТ';
$w_rus[6]='СБ';
$w_rus[7]='ВС';

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

  	 
	$array = $mysql->get_arr($sql);
	
	
	foreach( $array as $k=>$result ) 
	{	
		$count_datest[$result[date]][$result[time]]=$result[c];
		$count_dates[$result[date]]=$result[c];
	}	
	 
	// print_r($count_dates);
 }	 
 
		$w=date("w", strtotime($_REQUEST[data])); // номекр дня недели
		
 
  
if ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' || 1==1)
{
    ############## СОБИРАЕМ ДОСТУПНОЕ ВРЕМЯ И ЛИМИТЫ ЗАПИСЕЙ
    $data_settings = $mysql->get_arr('SELECT * FROM excurs_grafic ');
    // Формируем массив id[дата][время]='вся инфа'
	foreach( $data_settings as $k => $v )
	{
		if( $v )
		{
			$settings_arr[$v['place_id']][$v['date']][$v['time']] = $v['сapacity'];// Количество записей на время
			$settings_arr2[$v['place_id']][$v['date']] = $settings_arr2[$v['place_id']][$v['date']] + $v['сapacity']; // Количество записей на дату
		}
	}	
 //print '<pre>';
 //  print_r($settings_arr2);
 //print '</pre>';
 
 
	if($_REQUEST['load'] =='time' ) //  
	{
		?><option value="">Время</option><?
		// Цикл по доступному времени + дате
		foreach( $settings_arr[$_REQUEST['home']][$_REQUEST['data']] as $k=>$v)
		{
			
			
			if( strtotime( $_REQUEST['data'].' '.$k )  <time() ){continue;} // Прошедшее время 
						
						 
			// print strtotime( $_REQUEST['data'].' '.$k );
			/*
			if( $v > check_count_zapis($_REQUEST['home'] , $_REQUEST[data] , $k ) ) // не более 5 записей на обно время
			{
				?>
				<option value="<?=$k?>"><?=$k?></option>
				<?
			}
			*/
			
			if($_REQUEST['home']==46) // Красный проспект, 327/3 (Шоу-рум)
			{
				if( $v > check_count_zapis($_REQUEST['home'] , $_REQUEST[data] , $k ) &&  0 == check_count_zapis(1 , $_REQUEST['data'] , $k ) &&  0 == check_count_zapis(38 , $_REQUEST['data'] , $k ))  // ТОлько если нет записей на другую экскурсию на это время
				{
					?>
					<option value="<?=$k?>"><?=$k?></option>
					<?
				}
			}
			elseif($_REQUEST['home']==38) // 811
			{
				if( $v > check_count_zapis($_REQUEST['home'] , $_REQUEST[data] , $k ) &&  0 == check_count_zapis(1 , $_REQUEST['data'] , $k ) &&  0 == check_count_zapis(46 , $_REQUEST['data'] , $k ) )  // ТОлько если нет записей на другую экскурсию на это время
				{
					?>
					<option value="<?=$k?>"><?=$k?></option>
					<?
				}
			}
			elseif($_REQUEST['home']==1)  
			{
				if( $v > check_count_zapis($_REQUEST['home'] , $_REQUEST[data] , $k ) &&  0 == check_count_zapis(38 , $_REQUEST['data'] , $k ) &&  0 == check_count_zapis(46 , $_REQUEST['data'] , $k ) )  // ТОлько если нет записей на другую экскурсию на это время
				{
					?>
					<option value="<?=$k?>"><?=$k?></option>
					<?
				}
			}
			
			
			
			
			
		}
	}
	elseif($_REQUEST['load'] =='date' ) // Даты
	{
		
		list($year,$month,$day) = explode("|",date("Y|m|d",$i));
		$date = $day.'.'.$month.'.'.$year;
		  
		  
		?><option value="">Дата</option><?
		// Цикл по доступному 
		foreach( $settings_arr2[$_REQUEST['home']] as $k=>$v)
		{
			// Допускается запись на сегодняшнюю дату 
		    if( strtotime($k)<strtotime(date('d.m.Y'))){continue;} // Прошешие даты 
			
			
			
			$h = date('H', time()); // текущий час!
			$date_t = date('d.m.Y',time()); // Текущая дата
 
			$tom_date_ = new DateTime('+1 days'); 
		    $tom_date = $tom_date_->format('d.m.Y');// Завтрашняя дата 
			
			
			//Убрать ограничение по времени
			// if( $h>=16 && ( $k==$date_t || $k==$tom_date ) ){ continue; }
			
			
		  
			  //$tdate = date('d.m.Y');
		 
			
			 if( $v > check_count_zapis($_REQUEST['home'],$k)  )
			 {
				// print $k;
				$wn=date("w", strtotime($k)); // номекр дня недели
				?><option value="<?=$k?>"><?=$k?> (<?=$w_rus[$wn]?>)</option><?
			 }
		}
	}
}
else 
{
    echo 'Bad request!';
    exit;
}

