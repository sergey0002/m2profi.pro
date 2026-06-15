   
<!-- Load c3.css -->
<link href="c3/c3.css" rel="stylesheet">

<!-- Load d3.js and c3.js -->
<script src="https://d3js.org/d3.v5.min.js" charset="utf-8"></script>
<script src="c3/c3.min.js"></script>

 
 
 <?
 # Суперглобальный массив статусов
$s_arr[0]='Не задан';
$s_arr[2]='Свободна';
$s_arr[4]='Забронирована';
$s_arr[5]='Забронирована застройщиком';
$s_arr[6]='Квартира подрядчика';

 
		
		
		


$h = $sa->get_homes_arr();
  
  
 
foreach( $h as $k=>$v )
{
	if($_GET['sdan'] )
	{
		if($v['complite']==1)
		{
		$actual_homes[]=$v['home_id'];
		}
	}
	else
	{
		$actual_homes[]=$v['home_id'];
	}
	$homesx[$v['home_id']] = $v;
	
	$homesx[$v['home_id']]['caption'] =  $v['title'];
}


 
 
 
$home_id = $_REQUEST['home'];
  
 
 
 
########## График
ob_start();
// СВОДНАЯ ПО ВСЕМ ДОМАМ
// суммировать значения за предидуущие месяцы + количество квартир в доме!
  $sql='SELECT MONTH(broni.date) as month, year(broni.date) as year, count(apartaments.apartament_id) as c, apartaments.rooms , REGEXP_SUBSTR(apartaments.rooms,"[0-9]+") as roomsx, broni.home_id as home_id from broni 
LEFT JOIN users ON broni.user_id = users.id
 LEFT JOIN agency ON users.agency_id = agency.agency_id
 LEFT JOIN apartaments ON (apartaments.home_id= broni.home_id AND apartaments.apartment_num= broni.apartments_num)
 where broni.date = (select max(date) from broni as b where b.home_id = broni.home_id  
AND b.apartments_num = broni.apartments_num) 
AND broni.status="3"  
 and rooms >0
 group by    roomsx  , YEAR(broni.date) , MONTH(broni.date)  ';
if($home_id){$sql.=', broni.home_id';}
 //print $sql;
$query = mysqli_query($GLOBALS['connection'], $sql); 
while($result = mysqli_fetch_array($query))
{	 
if(!$home_id){$result[home_id]=0;} // Если не указан дом сводная статистика для всех 

// Для графика берем только 2022 год
if($result['year'] == '2022')
{
	$arr[$result[home_id]][$result[roomsx]][$result[month]] = $result[c];
	//print '<pre>';
	//print_r($result);
	//print '</pre>';
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

//print '<pre>';
// print_R($arr2);
//print '</pre>';

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
	 	  $ds2 .= ',' . ''.$i.'.2022'; // Название оси X
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
<?
 ########## График
 $graf=ob_get_clean();
 








 





#### ГОТОВИМ ДАННЫЕ


  $sql = 'SELECT count(apartaments.apartment_num) as c, home_id ,apartaments.rooms, REGEXP_SUBSTR(apartaments.rooms,"[0-9]+") as roomsx from apartaments group by  home_id , roomsx ';

// Массив квартир всего 
$query = mysqli_query($GLOBALS['connection'], $sql); 
while( $result = mysqli_fetch_array($query) )
{	
	$result['rooms'] = $result['roomsx'];

	if( in_array($result[home_id],$actual_homes) && $result[rooms])
	{  
	$all_arr[$result[home_id]][$result[rooms]]=$result[c];
	$all_arr[all][$result[rooms]]=$all_arr[all][$result[rooms]]+$result[c]; // по всем домам АКТУАЛЬНЫМ
	}
}

 
//print '<pre>';
//print_r($all_arr);
//print '</pre>';






   $sql = 'SELECT  count(apartaments.apartament_id) as c, apartaments.rooms, REGEXP_SUBSTR(apartaments.rooms,"[0-9]+") as roomsx  , broni.home_id as home_id, broni.date , MONTH(broni.date) as month, year(broni.date) as year ,broni.status   from broni 

LEFT JOIN apartaments ON (apartaments.home_id= broni.home_id AND apartaments.apartment_num= broni.apartments_num) 

where broni.date = (select max(date) from broni as b where b.home_id = broni.home_id 
AND b.apartments_num = broni.apartments_num AND ( broni.status="3" or broni.status="4" or broni.status="5" or broni.status="6" ))    


group by status , year ,month , roomsx , apartaments.home_id
';

//Массив проданных квартир
$query = mysqli_query($GLOBALS['connection'], $sql); 

$result = array();
$sale_arr = array();
 

while($result = mysqli_fetch_assoc($query))
{	 
$result[rooms] =  $result[roomsx];
  //print '<pre>';
 // print_r($result);
 // print '</pre>';
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
ob_start();
 //print '<pre>';
ksort($rooms_arr); // Сортируем массив комнат
  //print_r($rooms_arr);
// print '</pre>';
if(!$_GET[home])
{
	?> 
<table>

<thead>
<tr>
	<th></th>
	<?
	foreach($rooms_arr as $rk=>$rv)
	{
		print '<th><b>'.$rk.'</b></th>';
	}
	?>
	<td wIdth="15%"><b>Итого</b></td>
</tr>
</thead>
 <?
 
 $itogo_free_arr=array();
 foreach($all_arr as $kaa=>$vaa)
 {
	 
 
	 
	if($homesx[$kaa]['caption'])
	{
	?>
	<tr>
	<td><?=$homesx[$kaa]['caption']?> <? if($homesx[$kaa]['complite']==1){print ' <b>сдан</b> ';} ?></b></td>
	
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

$free_table = ob_get_clean();
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
  











 
ob_start();
?>
 
<table>
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
$svodnaya = ob_get_clean();
 ?>
 

 
 
 
 
 
 
 
 
  
 
 
 
 
 
 

 <?
 ob_start();
// print '<pre>';
//print_r($sale_arr2_m);
// print '</pre>';

  
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
 
<table style="width:100%">
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


$stat_month = ob_get_clean();
 ?>
 
  
  
   
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  
  

<section class="section-stat">
	<div class="container">
		<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">СТАТИСТИКА <span>продаж</span></div>
		</div>
		
		<?
print ' <h2>';
if($_GET[home]){print 'Статистика продаж "'.$homes[$_GET[home]]['caption'] .'"';}else{ print 'Сводная статистика продаж'; }
print '</h2>';
?> 



		<div class="stat">
			<div class="stat-top">
				<div class="stat-top-filter">
					<div class="stat-top-items">




<?
$h = $sa->get_homes_arr();


 ?>
 <a href="user.php?action=stat_sale" class="stat-top-items__item">Все</a>
 <?
foreach( $h as $k=>$v )
{
	?>
	<a href="user.php?action=stat_sale&amp;home=<?=$v['home_id']?>" class="stat-top-items__item active"><?=$v['title']?></a>
	<?
	$actual_homes[]=$v['home_id'];
}
?>
 
					</div>
					<div class="stat-top-btns">
						 
						<a href="#" class="stat-top__print"></a>
					</div>
				</div>
			</div>
			<div class="chart-wrap">
				<div id="chart"></div>
			</div>

			<div class="stat-wrap">
			
			<?
			if(!$_GET[home])
			{
				?>
				<div class="room-table stat-content">
					<div class="second-title stat-title active">Свободные квартиры</div>
					<div class="table-block stat-body open">
 					<?=$free_table?>
					
					 
						<table style="display:none;">
							<thead>
								<tr>
									<th></th>
									<th><b>1к</b></th>
									<th><b>2к</b></th>
									<th><b>3к</b></th>
									<th><b>4к</b></th>
									<th><b>Итого</b></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><b>704</b></td>
									<td>0<span class="dark">5</span><span class="orange">1</span><span
											class="blue">46</span>
									</td>
									<td>0<span class="dark">5</span><span class="orange">1</span><span
											class="blue">46</span>
									</td>
									<td>0<span class="dark">5</span><span class="orange">1</span><span
											class="blue">46</span>
									</td>
									<td>0<span class="dark">5</span><span class="orange">1</span><span
											class="blue">46</span>
									</td>
									<td>0<span class="dark">5</span><span class="orange">1</span><span
											class="blue">46</span>
									</td>
								</tr>
						 
							</tbody>
						</table>
 
					</div>
				</div>
				<?
			}
			?>
				
				
				
				
				
				
				
				
				
				
				<div class="total-stat stat-content">
					<div class="second-title stat-title">Сводная статистика</div>
					<div class="total total-stat__body stat-body">
					
					<div class="table-block">
					<?=$svodnaya?>
					</div>
					
					<div style="display:none;">
						<div class="total-item">
							<div class="total-item__title">1к</div>
							<div class="total-item-line">
								<div class="total-item-line__start">1402</div>
								<div class="total-item-line__main" style="width: 93%;"><span>1240 / 89,91%</span></div>
							</div>
							<div class="total-item__end">153/10,91%</div>
						</div>
						<div class="total-item">
							<div class="total-item__title">2к</div>
							<div class="total-item-line">
								<div class="total-item-line__start">1301</div>
								<div class="total-item-line__main" style="width: 88%;"><span>1101 / 89,91%</span></div>
							</div>
							<div class="total-item__end">200/10,91%</div>
						</div>
						<div class="total-item">
							<div class="total-item__title">3к</div>
							<div class="total-item-line">
								<div class="total-item-line__start">509</div>
								<div class="total-item-line__main" style="width: 75%;"><span>368 / 89,91%</span></div>
							</div>
							<div class="total-item__end">141/10,91%</div>
						</div>
						<div class="total-item">
							<div class="total-item__title">4к</div>
							<div class="total-item-line">
								<div class="total-item-line__start">16</div>
								<div class="total-item-line__main" style="width: 30%;"><span>4 / 25%</span></div>
							</div>
							<div class="total-item__end">12/76%</div>
						</div>
					</div>
						
						
						
					</div>
				</div>
				<div class="month-stat stat-content">
					<div class="second-title stat-title">Статистика продаж по месяцам</div>
					<div class="month-stat__body stat-body">
						<div class="month-stat-filter filter" style="display:none;">
							<div class="filter-wrap">
								<div class="filter-in">
									<input type="text" data-range="true" data-date-format="d-m-yy"
										data-multiple-dates-separator=" - " class="datepicker-here"
										placeholder="За все время">
								</div>
								<button class="month-stat-filter__btn btn btn_arrow-long">Показать
									статистику<i></i></button>
							</div>
						</div>
						<div class="table-block">
						<?=$stat_month ?>
						
						
							<table style="display:none;">
								<thead>
									<tr>
										<th><b>Месяц</b></th>
										<th><b>1к</b></th>
										<th><b>2к</b></th>
										<th><b>3к</b></th>
										<th><b>4к</b></th>
										<th><b>Итого</b></th>
										<th><b>% Итог</b></th>
										<th><b>% Мес</b></th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td><b>Январь</b></td>
										<td>30<span class="dark">1</span></td>
										<td>33<span class="blue">4</span></td>
										<td>11<span class="orange">5</span><span class="blue">1</span></td>
										<td>11<span class="orange">5</span><span class="blue">1</span></td>
										<td>75</td>
										<td>77.14</td>
										<td>2.32</td>
									</tr>
									<tr>
										<td><b>Февраль</b></td>
										<td>27<span class="orange">4</span><span class="blue">13</span></sup>
										</td>
										<td>21<span class="orange">1</span><span class="dark">2</span><span
												class="blue">3</span></td>
										<td>8<span class="blue">1</span></td>
										<td>8<span class="blue">1</span></td>
										<td>56</td>
										<td>78.87</td>
										<td>1.73</td>
									</tr>
									<tr>
										<td><b>Март</b></td>
										<td>55<span class="orange">7</span><span class="dark">33</span><span
												class="blue">1</span></td>
										<td>22<span class="orange">14</span><span class="dark">3</span><span
												class="blue">5</span></td>
										<td>16<span class="orange">7</span></td>
										<td>16<span class="orange">7</span></td>
										<td>93</td>
										<td>81.75</td>
										<td>2.57</td>
									</tr>
									<tr>
										<td><b>Апрель</b></td>
										<td>34<span class="orange">34</span><span class="dark">3</span><span
												class="blue">5</span></td>
										<td>32<span class="orange">33</span><span class="dark">3</span><span
												class="blue">8</span></td>
										<td>16<span class="orange">24</span><span class="dark">1</span><span
												class="blue">2</span></td>
										<td>16<span class="orange">24</span><span class="dark">1</span><span
												class="blue">2</span></td>
										<td>83</td>
										<td>84.32</td>
										<td>2.57</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>

		</div>

	</div>
</section>
 
 
 


 
 
 <?=$graf?>
 
 
 
 
 
  