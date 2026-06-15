 <div class="container">
<!-- Load c3.css -->
<link href="c3/c3.css" rel="stylesheet">

<!-- Load d3.js and c3.js -->
<script src="https://d3js.org/d3.v5.min.js" charset="utf-8"></script>
<script src="c3/c3.min.js"></script>


<style>
td{padding:3px;}
</style>
 <center>
 
 
 
 <?
 # Суперглобальный массив статусов
$s_arr[0]='Не задан';
$s_arr[2]='Свободна';
$s_arr[4]='Забронирована';
$s_arr[5]='Забронирована застройщиком';
$s_arr[6]='Квартира подрядчика';

 
		
		
		


$h = $sa->get_homes_arr();
//print '<pre>';
//print_r($h);
 ?>
<ul class="mmenu">
<li><a href="user.php?action=stat_sale" style="color:#000; text-decoration:underline;">Все</a> </li> 
<?
foreach( $h as $k=>$v )
{
	?>
	<li><a href="user.php?action=stat_sale&amp;home=<?=$v['home_id']?>" style="color:#000; text-decoration:underline;"><?=$v['title']?></a> </li> 
	<?
	$actual_homes[]=$v['home_id'];
}
?>
</ul>

			
			<?
			
			print '<pre>';
			//print_R($homes);
			print '</pre>';
			
			print ' <h2>';
if($_GET[home]){print 'Статистика продаж "'.$homes[$_GET[home]]['caption'] .'"';}else{ print 'Сводная статистика продаж'; }
print '</h2>';
?>
</center>
<div id="chart"></div>

 
<?

// print_r($_REQUEST);
$home_id = $_REQUEST['home'];
 
 
 
 
 
 
 
 
 
 
 
 
 
// СВОДНАЯ ПО ВСЕМ ДОМАМ
// суммировать значения за предидуущие месяцы + количество квартир в доме!

  $sql='SELECT MONTH(broni.date) as month, year(broni.date) as year, count(apartaments.apartament_id) as c, apartaments.rooms , broni.home_id as home_id from broni 
LEFT JOIN users ON broni.user_id = users.id
 LEFT JOIN agency ON users.agency_id = agency.agency_id
 LEFT JOIN apartaments ON (apartaments.home_id= broni.home_id AND apartaments.apartment_num= broni.apartments_num)
 where broni.date = (select max(date) from broni as b where b.home_id = broni.home_id  
AND b.apartments_num = broni.apartments_num) 
AND broni.status="3"  
 and rooms >0
  
group by    apartaments.rooms  , YEAR(broni.date) , MONTH(broni.date)  ';
if($home_id){$sql.=', broni.home_id';}


 //print $sql;

 print '<pre>';
$query = mysqli_query($GLOBALS['connection'], $sql); 
while($result = mysqli_fetch_array($query))
{	 
if(!$home_id){$result[home_id]=0;} // Если не указан дом сводная статистика для всех 

// Для графика берем только 2021 год
if($result['year'] == '2021')
{
	
	
	$arr[$result[home_id]][$result[rooms]][$result[month]] = $result[c];
	
	print '<pre>';
	//print_r($result);
	print '</pre>';
}

}


 




if($home_id){$arr2 = $arr[$home_id];}
else{ $arr2 = $arr[0]; }
 


// Определяем иинимальные и максимальные точки графика по оси X
foreach( $arr2  as $k=>$v )
{
	foreach( $v as $k2=>$v2 )
	{
		if( $k2<=$min ){ $min=$k2; }
		if( $k2>=$min ){ $max=$k2; }
	}
}


//print $max;

print '<pre>';
// print_R($arr2);
print '</pre>';

foreach( $arr2  as $k=>$v )
{
	$ds =''; $ds2 ='';



	// Заполняем значения графика по месяцам
	for($i=1; $i<=12; $i++)
	{
		 if(!$v[$i]){$v[$i]=0;}
	 
		if($i<=12)
		{
		  $ds .= ',' . $v[$i];
	 	  $ds2 .= ',' . ''.$i.'.2021'; // Название оси X
		}
 
  		else
  		{
  		  //$ds .= ',' . $v[$i]-12;
  	 	  //$ds2 .= ',' . ''.$i.'.2020'; // Название оси X
  		}
 
	}


//вбивать нули если нет данных
	$str3.='[\'x\' '.$ds2.'],'."\r\n"; // подписи оси X
	$str.='[\''.$k.'\' '.$ds.'],'."\r\n"; // Значение оси X 	
}
  //print $str;
 

 

?>
<script>
var chart = c3.generate({
    bindto: '#chart',
    data: {
 	x: 'x',
      	columns: [
        <?
	print  $str3;
	print $str;
	?>
      ]
    }
});
</script>
 </div>
 





<?



  $sql = 'SELECT count(apartaments.apartment_num) as c, home_id , rooms from apartaments group by  home_id , rooms ';

// Массив квартир всего 
$query = mysqli_query($GLOBALS['connection'], $sql); 
while( $result = mysqli_fetch_array($query) )
{	
	if( in_array($result[home_id],$actual_homes) && $result[rooms])
	{  
	$all_arr[$result[home_id]][$result[rooms]]=$result[c];
	$all_arr[all][$result[rooms]]=$all_arr[all][$result[rooms]]+$result[c]; // по всем домам АКТУАЛЬНЫМ
	}
}

 
//print '<pre>';
//print_r($all_arr);
//print '</pre>';





 print   $sql = 'SELECT  count(apartaments.apartament_id) as c, apartaments.rooms , broni.home_id as home_id, broni.date , MONTH(broni.date) as month, year(broni.date) as year ,broni.status   from broni 

LEFT JOIN apartaments ON (apartaments.home_id= broni.home_id AND apartaments.apartment_num= broni.apartments_num) 

where broni.date = (select max(date) from broni as b where b.home_id = broni.home_id 
AND b.apartments_num = broni.apartments_num AND ( broni.status="3" or broni.status="4" or broni.status="5" or broni.status="6" ))    


group by status , year ,month , apartaments.rooms , apartaments.home_id
';


//Массив проданных квартир
$query = mysqli_query($GLOBALS['connection'], $sql); 

$result = array();
$sale_arr = array();
 

while($result = mysqli_fetch_array($query))
{	 

//print '<pre>';
//print_r($result);
//print '</pre>';
/*
3 - продана 
4 - брони
5 - застройщика
6 - подрядчика
*/
	 if(  in_array($result[home_id],$actual_homes)    &&  $result[rooms] ) 
	 {  
 
		$rooms_arr[$result['rooms']]=1; // Массив с наименованием количества комнат 
		
		if( $result[status]==3) // проданные
		{
			$xxx=$xxx+$result[c];
			$sale_arr[$result[home_id]][$result[rooms]]=$sale_arr[$result[home_id]][$result[rooms]]+$result[c];
			$sale_arr_m[ $result[home_id] ][ $result[year] ][ $result[month] ][ $result[rooms] ]=$result[c];
			$sale_arr['all'][ $result[rooms] ]  = $sale_arr['all'][ $result[rooms] ] + $result[c]; // по всем домам
			
			$sale_arr_m['all'][ $result[year] ][ $result[month] ][ $result[rooms] ]=$sale_arr_m['all'][ $result[year] ][ $result[month] ][ $result[rooms] ] + $result[c];
		}
		elseif( $result[status]==4) // брони
		{
			 
			$sale_arr4[$result[home_id]][$result[rooms]]=$sale_arr4[$result[home_id]][$result[rooms]]+$result[c];
			$sale_arr_m4[ $result[home_id] ][ $result[year] ][ $result[month] ][ $result[rooms] ]=$result[c];
			$sale_arr4['all'][ $result[rooms] ]  = $sale_arr4['all'][ $result[rooms] ] + $result[c]; // по всем домам
			$sale_arr_m4['all'][ $result[year] ][ $result[month] ][ $result[rooms] ]=$sale_arr_m4['all'][ $result[year] ][ $result[month] ][ $result[rooms] ] + $result[c];
			
			$sale_arr2[4][$result[home_id]][$result[rooms]]=$sale_arr2[4][$result[home_id]][$result[rooms]]+$result[c];
			$sale_arr2_m[4][ $result[home_id] ][ $result[year] ][ $result[month] ][ $result[rooms] ]=$result[c];
			$sale_arr2[4]['all'][ $result[rooms] ]  = $sale_arr2[4]['all'][ $result[rooms] ] + $result[c]; // по всем домам
			$sale_arr2_m[4]['all'][ $result[year] ][ $result[month] ][ $result[rooms] ]=$sale_arr2_m[4]['all'][ $result[year] ][ $result[month] ][ $result[rooms] ] + $result[c];
			
			
		}
		elseif( $result[status]==5) // застройщика
		{
			$sale_arr5[$result[home_id]][$result[rooms]]=$sale_arr5[$result[home_id]][$result[rooms]]+$result[c];
			$sale_arr_m5[ $result[home_id] ][ $result[year] ][ $result[month] ][ $result[rooms] ]=$result[c];
			$sale_arr5['all'][ $result[rooms] ]  = $sale_arr5['all'][ $result[rooms] ] + $result[c]; // по всем домам
			$sale_arr_m5['all'][ $result[year] ][ $result[month] ][ $result[rooms] ]=$sale_arr_m5['all'][ $result[year] ][ $result[month] ][ $result[rooms] ] + $result[c];
			
			$sale_arr2[5][$result[home_id]][$result[rooms]]=$sale_arr2[5][$result[home_id]][$result[rooms]]+$result[c];
			$sale_arr2_m[5][ $result[home_id] ][ $result[year] ][ $result[month] ][ $result[rooms] ]=$result[c];
			$sale_arr2[5]['all'][ $result[rooms] ]  = $sale_arr2[5]['all'][ $result[rooms] ] + $result[c]; // по всем домам
			$sale_arr2_m[5]['all'][ $result[year] ][ $result[month] ][ $result[rooms] ]=$sale_arr2_m[5]['all'][ $result[year] ][ $result[month] ][ $result[rooms] ] + $result[c];

		}
		elseif( $result[status]==6) // подрядчика
		{
			$sale_arr6[$result[home_id]][$result[rooms]]=$sale_arr6[$result[home_id]][$result[rooms]]+$result[c];
			$sale_arr_m6[ $result[home_id] ][ $result[year] ][ $result[month] ][ $result[rooms] ]=$result[c];
			$sale_arr6['all'][ $result[rooms] ]  = $sale_arr6['all'][ $result[rooms] ] + $result[c]; // по всем домам
			$sale_arr_m6['all'][ $result[year] ][ $result[month] ][ $result[rooms] ]=$sale_arr_m6['all'][ $result[year] ][ $result[month] ][ $result[rooms] ] + $result[c];
			
			$sale_arr2[6][$result[home_id]][$result[rooms]]=$sale_arr2[6][$result[home_id]][$result[rooms]]+$result[c];
			$sale_arr2_m[6][ $result[home_id] ][ $result[year] ][ $result[month] ][ $result[rooms] ]=$result[c];
			$sale_arr2[6]['all'][ $result[rooms] ]  = $sale_arr2[6]['all'][ $result[rooms] ] + $result[c]; // по всем домам
			$sale_arr2_m[6]['all'][ $result[year] ][ $result[month] ][ $result[rooms] ]=$sale_arr2_m[6]['all'][ $result[year] ][ $result[month] ][ $result[rooms] ] + $result[c];
		}
	 } 
}

?>
 



<?

print '<pre>';
ksort($rooms_arr); // Сортируем массив комнат
 // print_r($rooms_arr);
print '</pre>';
if(!$_GET[home])
{
	?>
 <h3>Свободные квартиры</h3> 


<table border=1 style="max-width:100%; width:100%">
<tr>
	<td wIdth="15%"><b>Обьект</b></td>
	<?
	foreach($rooms_arr as $rk=>$rv)
	{
		print '<td><b>'.$rk.'</b></td>';
	}
	?>
	<td wIdth="15%"><b>Итого</b></td>
</tr>
 <?
 
 $itogo_free_arr=array();
 foreach($all_arr as $kaa=>$vaa)
 {
	if($homes[$kaa]['caption'])
	{
	?>
	<tr>
	<td><?=$homes[$kaa]['caption']?></b></td>
	
	<?
	foreach($rooms_arr as $rk=>$rv)
	{
		print '<td>';
		$itogo_free_arr[$rk]= $itogo_free_arr[$rk]+$all_arr[$kaa][$rk]-$sale_arr[$kaa][$rk]-$sale_arr4[$kaa][$rk]-$sale_arr5[$kaa][$rk]-$sale_arr6[$kaa][$rk];
		print $all_arr[$kaa][$rk]-$sale_arr[$kaa][$rk]-$sale_arr4[$kaa][$rk]-$sale_arr5[$kaa][$rk]-$sale_arr6[$kaa][$rk];
		
			print '<sup>';
			?>
			<? if($sale_arr2[4][$kaa][$rk]) { ?> / <span style="padding:1px; margin:2px; background:#FFFF00" title="Бронь"><?=$sale_arr2[4][$kaa][$rk]?></span>  <? $sale_arr3[$kaa][4] = $sale_arr3[$kaa][4]+ $sale_arr2[4][$kaa][$rk]; } ?>
			<? if($sale_arr2[5][$kaa][$rk]) { ?> / <span style="padding:1px;  margin:2px; background:#D4E6FF" title="Застройщика"><?=$sale_arr2[5][$kaa][$rk]?></span> <? $sale_arr3[$kaa][5] = $sale_arr3[$kaa][5]+ $sale_arr2[5][$kaa][$rk];} ?>
			<? if($sale_arr2[6][$kaa][$rk]) { ?> / <span style="padding:1px; margin:2px;  background:#9933ff; color:#FFF;" title="Подрядчика"><?=$sale_arr2[6][$kaa][$rk]?></span> <? $sale_arr3[$kaa][6] = $sale_arr3[$kaa][6]+ $sale_arr2[6][$kaa][$rk];} ?>
			<?
			print '</sup>';
			
			// $sale_arr3[дом][комнат][статус]=Количество
			 
			 
		print '</td>';
	}
	?>
 

	<td>
		<?
		 
		 	foreach($rooms_arr as $rk=>$rv)
			{
				 $_x = $_x + $all_arr[$kaa][$rk]-$sale_arr[$kaa][$rk]-$sale_arr2[4][$kaa][$rk]-$sale_arr2[5][$kaa][$rk]-$sale_arr2[6][$kaa][$rk];
			}
			print  $_x;
		?>
		
		
 	<sup>
	
			<? 
			
		//	PRINT_R($sale_arr2[4][$kaa]);
		//	print '<br><br>';
		//	print_r($sale_arr3[$kaa]);
			
			if( $sale_arr3[$kaa][4] ) { ?> 	/ <span style="padding:1px; margin:2px;  background:#FFFF00" title="Бронь"><?= $sale_arr3[$kaa][4]?></span>  <?  } 
			if( $sale_arr3[$kaa][5] ) { ?> 	/ <span style="padding:1px; margin:2px;  background:#D4E6FF" title="Застройщика"><?= $sale_arr3[$kaa][5]?></span>  <?  } 
			if( $sale_arr3[$kaa][6] ) { ?> 	/ <span style="padding:1px; margin:2px;  background:#9933ff; color:#FFF;"  title="Подрядчика"><?= $sale_arr3[$kaa][6]?></span>  <?  } 

			?>		</sup>
	</td>





	</tr>
	<?
	}
 }
 ?>
<tr>
<td><b>Итого</b></td>

<?
foreach($rooms_arr as $rk=>$rv)
{
	?><td><?
		print $itogo_free_arr[$rk]
		 // ТУТ ВСТАВИТЬ СУММЫ БРОНЕЙ ИТП 
	
	?></td><?
	//$itogo_arr55 = $itogo_arr55 + $itogo_free_arr[$rk];
	
	
}
?>
<td><?= $itogo_arr55 ?></td>
 
</tr>
</table>

 <?
}
 ?>










<br><br>

<h3>Сводная статистика</h3> 

<table border="1" style="max-width:100%; width:100%">
<tr>
	<td><b>Комнат</b></td>
	<td><b>Всего</b></td>
	<td><b>Продано</b></td>
	<td><b>Продано %</b></td>

	<td><b>Свободно</b></td>
	<td><b>Свободно %</b></td>
 
	<td><b>Брони</b></td>
	<td><b>Брони %</b></td>

</tr>


<?
$home = $_GET[home];
if(!$home){$home='all';}
$itogo_arr_2 = array(); // итоговый массиы

foreach($all_arr[$home] as $k=>$v)
{
$free= $v-$sale_arr[$home][$k]-$sale_arr4[$home][$k]-$sale_arr5[$home][$k]-$sale_arr6[$home][$k];


### $free= $itogo_free_arr[$k];


	?>
	<tr>
	<td width="12%"><?=$k?></td>
	<td width="12%">
		<? $itogo_arr_2[2] = $itogo_arr_2[2]+ $v ; ?>
		<?=$v?>
	</td>
	<td width="12%">
		<? $itogo_arr_2[3] = $itogo_arr_2[3] + $sale_arr[$home][$k] ; ?>
		<?=$sale_arr[$home][$k]?>
	</td>
	<td width="12%"><?=round($sale_arr[$home][$k]/$v*100,2)?>%</td>
	<td width="12%">
		<? $itogo_arr_2[5] =$itogo_arr_2[5]+ $free ; ?>
	<?=$free?>
	
	</td>
	<td width="12%"><?=round($free/$v*100,2)?>%</td>
	
	
	
	<td width="12%"><?=$v-$sale_arr[$home][$k]-$free?>
	<? $itogo_arr_2[6] =$itogo_arr_2[6]+ $v-$sale_arr[$home][$k]-$free ; ?>
	</td>
	<td width="12%"><?=round( ($v-$sale_arr[$home][$k]-$free)/$v*100,2)?>%</td>
	
	
	</tr>
	<?
}
?>
<tr>
<td><b>Итого</b></td>
<td><?= $itogo_arr_2[2]?></td>
<td><?= $itogo_arr_2[3]?></td>
<td><?=round($itogo_arr_2[3]/$itogo_arr_2[2]*100,2)?>% </td>
<td><?= $itogo_arr_2[5]?></td>
 <td> <?=round($itogo_arr_2[5]/$itogo_arr_2[2]*100,2)?>%  </td>
 
  <td> <?=$itogo_arr_2[6]?> </td>
  <td> <?=round($itogo_arr_2[6]/$itogo_arr_2[2]*100,2)?>%  </td>
  
  
</tr>
<?
print '</table> ';	

 ?>
 

 
 
 
 
 
 
 
 
 
 
 
 
 <br><br>
 
 
 
 
 
 

 <?
// print '<pre>';
 
 //print_r($sale_arr2_m);
 // print '</pre>';
 
 
 print '<h3>Статистика продаж по месяцам</h3><br/>';
 foreach($sale_arr_m[$home] as $ky => $vy )
 {
 $year=$ky;
print '<h4>'.$year.'г.</h4>';
 
//print '<pre>';
//print_r( $sale_arr_m[$home][$year] );
//print '</pre>';


$month[1] = 'Январь';
$month[2] = 'Февраль';
$month[3] = 'Март';
$month[4] = 'Апрель';
$month[5] = 'Май';
$month[6] = 'Июнь';
$month[7] = 'Июль';
$month[8] = 'Август';
$month[9] = 'Сентябрь';
$month[10] = 'Октябрь';
$month[11] = 'Ноябрь';
$month[12] = 'Декабрь';


$alla = array_sum($all_arr[$home]);  // всего квартир в доме
?>
 
<table border=1 style="max-width:100%; width:100%">
<tr>
	<td width="14%"><b>Месяц</b></td>


	<?
	foreach($rooms_arr as $rk=>$rv)
	{
		?>
		<td>
		<b><?=$rk ?></b>
		</td>
		<?
		// print '<td><b>'.$rk.'</b></td>';
	}
	?>
	 
	<td width="10%"><b> итого </b></td>
	<td width="10%"><b>% итог</b></td>
	<td width="10%"><b>% мес</b></td>
</tr>

<?
 

foreach($sale_arr_m[$home][$year] as $k=>$v)
{
	//print_r( $k );
	// $free= $v-$sale_arr[$home][$k];
	$itog = $v[1]+$v[2]+$v[3]+$v[4]; // квартир всего продано
	
	$itog = array_sum($v);
	
	$pr_month = $itog/($alla/100);
	$pr_itogo =$pr_itogo+$pr_month;
	?>
<tr>
	<td><?=$month[$k]?></td>
	
	
	<?
	foreach($rooms_arr as $rk=>$rv)
	{
		?>
		<td><? if($v[$rk]){print $v[$rk];}else{print 0;} ?>
		<sup>
			<? if($sale_arr2_m[4][$home][$year][$k][$rk]) { ?> / <span style="padding:1px; margin:2px; background:#FFFF00" title="Бронь"> <?=$sale_arr2_m[4][$home][$year][$k][$rk]?> </span>  <? } ?>
			<? if($sale_arr2_m[5][$home][$year][$k][$rk]) { ?> / <span style="padding:1px;  margin:2px; background:#D4E6FF" title="Застройщика"> <?=$sale_arr2_m[5][$home][$year][$k][$rk]?> </span> <? } ?>
			<? if($sale_arr2_m[6][$home][$year][$k][$rk]) { ?> / <span style="padding:1px; margin:2px;  background:#9933ff; color:#FFF;" title="Подрядчика"> <?=$sale_arr2_m[6][$home][$year][$k][$rk]?> </span> <? } ?>
		</sup>
		</td>
		<?
		// print '<td><b>'.$rk.'</b></td>';
	}
	?>
	
	 
	
	
	 
 
		
		
	<td><?=$itog?></td>
	<td><?=round($pr_itogo,2)?></td>
	<td><?=round($pr_month,2)?></td>
</tr>
	<?
}

print '</table> ';	

}
 
 
 
 ?><br><br><?
 
 
 
 
 


 
 
 
 
 
 
 
 
  