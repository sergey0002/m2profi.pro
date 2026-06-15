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

			<ul class="mmenu">
		 		<li><a href="user.php?action=stat_sale" style="color:#000; text-decoration:underline;">Все</a> </li> 
				<li><a href="user.php?action=stat_sale&amp;home=5" style="color:#000; text-decoration:underline;">Приозерный №1</a> </li> 
				<li><a href="user.php?action=stat_sale&amp;home=8" style="color:#000; text-decoration:underline;">Приозерный №2</a> </li> 
				<li><a href="user.php?action=stat_sale&amp;home=3" style="color:#000; text-decoration:underline;">451</a> </li> 
				<li><a href="user.php?action=stat_sale&amp;home=7" style="color:#000; text-decoration:underline;">452</a> </li>
				<li><a href="user.php?action=stat_sale&amp;home=6" style="color:#000; text-decoration:underline;">453</a> </li>
				<li><a href="user.php?action=stat_sale&amp;home=9" style="color:#000; text-decoration:underline;">Тюленина 1</a> </li>
				<li><a href="user.php?action=stat_sale&amp;home=10" style="color:#000; text-decoration:underline;">Тюленина 2</a> </li>


<li><a href="user.php?action=stat_sale&amp;home=12" style="color:#000; text-decoration:underline;">603</a> </li>

<li><a href="user.php?action=stat_sale&amp;home=15" style="color:#000; text-decoration:underline;">601</a> </li>
<li><a href="user.php?action=stat_sale&amp;home=16" style="color:#000; text-decoration:underline;">603</a> </li>
<li><a href="user.php?action=stat_sale&amp;home=14" style="color:#000; text-decoration:underline;">ЗАЛЕССКИЙ</a> </li>

			</ul>

			
			<?
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

$sql='SELECT MONTH(broni.date) as month, count(apartaments.apartament_id) as c, apartaments.rooms , broni.home_id as home_id from broni 
LEFT JOIN users ON broni.user_id = users.id
 LEFT JOIN agency ON users.agency_id = agency.agency_id
 LEFT JOIN apartaments ON (apartaments.home_id= broni.home_id AND apartaments.apartment_num= broni.apartments_num)
 where broni.date = (select max(date) from broni as b where b.home_id = broni.home_id 
AND b.apartments_num = broni.apartments_num) 
AND broni.status="3"  
 and rooms >0
  
group by    apartaments.rooms  , MONTH(broni.date)  ';
if($home_id){$sql.=', broni.home_id';}


//print $sql;

 print '<pre>';
$query = mysqli_query($GLOBALS['connection'], $sql); 
while($result = mysqli_fetch_array($query))
{	 

	if(!$home_id){$result[home_id]=0;}
	$arr[$result[home_id]][$result[rooms]][$result[month]] = $result[c];
}
// print_r($arr);

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


foreach( $arr2  as $k=>$v )
{
	$ds =''; $ds2 ='';



	// Заполняем значения графика по месяцам
	for($i=1; $i<=24; $i++)
	{
		 if(!$v[$i]){$v[$i]=0;}
	 
		if($i<=12)
		{
		  $ds .= ',' . $v[$i];
	 	  $ds2 .= ',' . ''.$i.'.2019'; // Название оси X
		}
/*
  		else
  		{
  		  $ds .= ',' . $v[$i]-12;
  	 	  $ds2 .= ',' . ''.$i.'.2019'; // Название оси X
  		}
*/
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
	if(($result[home_id] == 5 ||  $result[home_id] == 8 || $result[home_id] == 3 || $result[home_id] == 7 || $result[home_id] == 6 || $result[home_id] == 9 || $result[home_id] == 10 || $result[home_id] == 12|| $result[home_id] == 14|| $result[home_id] == 15 || $result[home_id] == 16) && $result[rooms])
	{  
	$all_arr[$result[home_id]][$result[rooms]]=$result[c];
	$all_arr[all][$result[rooms]]=$all_arr[all][$result[rooms]]+$result[c]; // по всем домам
	}
}


 



$sql = 'SELECT  count(apartaments.apartament_id) as c, apartaments.rooms , broni.home_id as home_id, broni.date , MONTH(broni.date) as month, year(broni.date) as year ,broni.status   from broni 

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
	 if(( $result[home_id] == 5 ||  $result[home_id] == 8 || $result[home_id] == 3 || $result[home_id] == 7 || $result[home_id] == 6 || $result[home_id] == 9 || $result[home_id] == 10 || $result[home_id] == 12 || $result[home_id] == 14 || $result[home_id] == 15 || $result[home_id] == 16 ) &&  $result[rooms] ) 
	 {  
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
		}
		elseif( $result[status]==5) // застройщика
		{
			$sale_arr5[$result[home_id]][$result[rooms]]=$sale_arr5[$result[home_id]][$result[rooms]]+$result[c];
			$sale_arr_m5[ $result[home_id] ][ $result[year] ][ $result[month] ][ $result[rooms] ]=$result[c];
			$sale_arr5['all'][ $result[rooms] ]  = $sale_arr5['all'][ $result[rooms] ] + $result[c]; // по всем домам
			$sale_arr_m5['all'][ $result[year] ][ $result[month] ][ $result[rooms] ]=$sale_arr_m5['all'][ $result[year] ][ $result[month] ][ $result[rooms] ] + $result[c];
		}
		elseif( $result[status]==6) // подрядчика
		{
			$sale_arr6[$result[home_id]][$result[rooms]]=$sale_arr6[$result[home_id]][$result[rooms]]+$result[c];
			$sale_arr_m6[ $result[home_id] ][ $result[year] ][ $result[month] ][ $result[rooms] ]=$result[c];
			$sale_arr6['all'][ $result[rooms] ]  = $sale_arr6['all'][ $result[rooms] ] + $result[c]; // по всем домам
			$sale_arr_m6['all'][ $result[year] ][ $result[month] ][ $result[rooms] ]=$sale_arr_m6['all'][ $result[year] ][ $result[month] ][ $result[rooms] ] + $result[c];
		}
	 } 
}

?>
<center>




<?
if(!$_GET[home])
{
	?>
 <h3><?= unit_phrase('free_units') ?></h3> 


<table border=1 style="max-width:90%; width:70%">
<tr>
	<td wIdth="20%"><b>Обьект</b></td>
	<td wIdth="20%"><b>1к</b></td>
	<td wIdth="20%"><b>2к</b></td>
	<td wIdth="20%"><b>3к</b></td>
	<td wIdth="20%"><b>Итого</b></td>
</tr>
 <?
 foreach($all_arr as $kaa=>$vaa)
 {
	if($homes[$kaa]['caption'])
	{
	?>
	<tr>
	<td><?=$homes[$kaa]['caption']?></b></td>
	<td>
		<?=$all_arr[$kaa][1]-$sale_arr[$kaa][1]-$sale_arr4[$kaa][1]-$sale_arr5[$kaa][1]-$sale_arr6[$kaa][1]?>
		<sup>
			<? if($sale_arr4[$kaa][1]) { ?> / <span style="padding:1px; margin:2px; background:#FFFF00" title="Бронь"><?=$sale_arr4[$kaa][1]?></span>  <? } ?>
			<? if($sale_arr5[$kaa][1]) { ?> / <span style="padding:1px;  margin:2px; background:#D4E6FF" title="Застройщика"><?=$sale_arr5[$kaa][1]?></span> <? } ?>
			<? if($sale_arr6[$kaa][1]) { ?> / <span style="padding:1px; margin:2px;  background:#9933ff; color:#FFF;" title="Подрядчика"><?=$sale_arr6[$kaa][1]?></span> <? } ?>
		</sup>
	</td>
	
	<td>
		<?=$all_arr[$kaa][2]-$sale_arr[$kaa][2]-$sale_arr4[$kaa][2]-$sale_arr5[$kaa][2]-$sale_arr6[$kaa][2]?>
		<sup>
			<? if($sale_arr4[$kaa][2]) { ?> / <span style="padding:1px; margin:2px; background:#FFFF00" title="Бронь"><?=$sale_arr4[$kaa][2]?></span>  <? } ?>
			<? if($sale_arr5[$kaa][2]) { ?> / <span style="padding:1px;  margin:2px; background:#D4E6FF" title="Застройщика"><?=$sale_arr5[$kaa][2]?></span> <? } ?>
			<? if($sale_arr6[$kaa][2]) { ?> / <span style="padding:1px;  margin:2px; background:#9933ff; color:#FFF;" title="Подрядчика"><?=$sale_arr6[$kaa][2]?></span> <? } ?>
		</sup>
	</td>
	<td>
		<?=$all_arr[$kaa][3]-$sale_arr[$kaa][3]-$sale_arr4[$kaa][3]-$sale_arr5[$kaa][3]-$sale_arr6[$kaa][3]?>
		<sup>
			<? if($sale_arr4[$kaa][3]) { ?> / <span style="padding:1px; margin:2px;  background:#FFFF00" title="Бронь"><?=$sale_arr4[$kaa][3]?></span>  <? } ?>
			<? if($sale_arr5[$kaa][3]) { ?> / <span style="padding:1px; margin:2px;  background:#D4E6FF" title="Застройщика"><?=$sale_arr5[$kaa][3]?></span> <? } ?>
			<? if($sale_arr6[$kaa][3]) { ?> / <span style="padding:1px; margin:2px;  background:#9933ff; color:#FFF;" title="Подрядчика"><?=$sale_arr6[$kaa][3]?></span>  <? } ?>
		</sup>
	</td>






	<td>
		<?
		$_x1 = $all_arr[$kaa][1]-$sale_arr[$kaa][1]-$sale_arr4[$kaa][1]-$sale_arr5[$kaa][1]-$sale_arr6[$kaa][1];
		$_x2 = $all_arr[$kaa][2]-$sale_arr[$kaa][2]-$sale_arr4[$kaa][2]-$sale_arr5[$kaa][2]-$sale_arr6[$kaa][2];
		$_x3 = $all_arr[$kaa][3]-$sale_arr[$kaa][3]-$sale_arr4[$kaa][3]-$sale_arr5[$kaa][3]-$sale_arr6[$kaa][3];
		print $_x1+$_x2+$_x3;
		?>
 	<sup>
			<? 
if($sale_arr4[$kaa][1] || $sale_arr4[$kaa][2] || $sale_arr4[$kaa][3] ) { 
?> 
/ <span style="padding:1px; margin:2px;  background:#FFFF00" title="Бронь"><?=$sale_arr4[$kaa][1]+$sale_arr4[$kaa][2]+$sale_arr4[$kaa][3]?></span>  
<? 
} 
?>

<? if($sale_arr5[$kaa][1] || $sale_arr5[$kaa][2] || $sale_arr5[$kaa][3]) { 
?> 
/ <span style="padding:1px; margin:2px;  background:#D4E6FF" title="Застройщика"><?=$sale_arr5[$kaa][1]+$sale_arr5[$kaa][2]+$sale_arr5[$kaa][3]?></span> 
<? 
} 
?>

<? if( $sale_arr6[$kaa][1] ||$sale_arr6[$kaa][2] ||$sale_arr6[$kaa][3]) { 
?> 
/ <span style="padding:1px; margin:2px;  background:#9933ff; color:#FFF;" title="Подрядчика">
<?=$sale_arr6[$kaa][1]+$sale_arr6[$kaa][2]+$sale_arr6[$kaa][3]?>
</span>  
<?
 }
 ?>
		</sup>
	</td>





	</tr>
	<?
	}
 }
 ?>

</table>

 <?
}
 ?>



<h3>Сводная статистика</h3> 

<table border=1 style="max-width:90%; width:70%">
<tr>
	<td><b>Комнат</b></td>
	<td><b>Всего</b></td>
	<td><b>Продано</b></td>
	<td><b>Продано %</b></td>

	<td><b>Свободно</b></td>
	<td><b>Свободно %</b></td>

</tr>


<?
$home = $_GET[home];
if(!$home){$home='all';}

foreach($all_arr[$home] as $k=>$v)
{
$free= $v-$sale_arr[$home][$k];

	?>
	<tr>
	<td width="14%"><?=$k?></td>
	<td width="14%"><?=$v?></td>
	<td width="14%"><?=$sale_arr[$home][$k]?></td>
	<td width="14%"><?=round($sale_arr[$home][$k]/$v*100,2)?></td>
	<td width="14%"><?=$free?></td>
	<td width="14%"><?=round($free/$v*100,2)?></td>
	</tr>
	<?
}

print '</table><center>';	

 ?>
 

 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 

 <?
 print '<h3>Статистика продаж по месяцам</h3>';
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


$alla = $all_arr[$home][1]+$all_arr[$home][2]+$all_arr[$home][3]+$all_arr[$home][4]; // всего квартир в доме
?>
 
<table border=1 style="max-width:90%; width:70%">
<tr>
	<td width="14%"><b>Месяц</b></td>
	<td width="10%"><b>1к</b></td>
	<td width="10%"><b>2k</b></td>
	<td width="10%"><b>3k</b></td>
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
	$pr_month = $itog/($alla/100);
	$pr_itogo =$pr_itogo+$pr_month;
	?>
<tr>
	<td><?=$month[$k]?></td>
	<td><? if($v[1]){print $v[1];}else{print 0;} ?>
	
		<sup>
			<? if($sale_arr_m4[$home][$year][$k][1]) { ?> / <span style="padding:1px; margin:2px; background:#FFFF00" title="Бронь"> <?=$sale_arr_m4[$home][$year][$k][1]?> </span>  <? } ?>
			<? if($sale_arr_m5[$home][$year][$k][1]) { ?> / <span style="padding:1px;  margin:2px; background:#D4E6FF" title="Застройщика"> <?=$sale_arr_m5[$home][$year][$k][1]?> </span> <? } ?>
			<? if($sale_arr_m6[$home][$year][$k][1]) { ?> / <span style="padding:1px; margin:2px;  background:#9933ff; color:#FFF;" title="Подрядчика"> <?=$sale_arr_m6[$home][$year][$k][1]?> </span> <? } ?>
		</sup>
	</td>
	
	
	    
		
		
	<td><? if($v[2]){print $v[2];}else{print 0;} ?>
	
		<sup>
			<? if($sale_arr_m4[$home][$year][$k][2]) { ?> / <span style="padding:1px; margin:2px; background:#FFFF00" title="Бронь"> <?=$sale_arr_m4[$home][$year][$k][2]?> </span>  <? } ?>
			<? if($sale_arr_m5[$home][$year][$k][2]) { ?> / <span style="padding:1px;  margin:2px; background:#D4E6FF" title="Застройщика"> <?=$sale_arr_m5[$home][$year][$k][2]?> </span> <? } ?>
			<? if($sale_arr_m6[$home][$year][$k][2]) { ?> / <span style="padding:1px; margin:2px;  background:#9933ff; color:#FFF;" title="Подрядчика"> <?=$sale_arr_m6[$home][$year][$k][2]?> </span> <? } ?>
		</sup>
	</td>
	<td><? if($v[3]){print $v[3];}else{print 0;} ?>
	
	
	<sup>
			<? if($sale_arr_m4[$home][$year][$k][3]) { ?> / <span style="padding:1px; margin:2px; background:#FFFF00" title="Бронь"> <?=$sale_arr_m4[$home][$year][$k][3]?> </span>  <? } ?>
			<? if($sale_arr_m5[$home][$year][$k][3]) { ?> / <span style="padding:1px;  margin:2px; background:#D4E6FF" title="Застройщика"> <?=$sale_arr_m5[$home][$year][$k][3]?> </span> <? } ?>
			<? if($sale_arr_m6[$home][$year][$k][3]) { ?> / <span style="padding:1px; margin:2px;  background:#9933ff; color:#FFF;" title="Подрядчика"> <?=$sale_arr_m6[$home][$year][$k][3]?> </span> <? } ?>
		</sup>
		
		</td>
	<td><?=$itog?></td>
	<td><?=round($pr_itogo,2)?></td>
	<td><?=round($pr_month,2)?></td>
</tr>
	<?
}

print '</table><center>';	

}
 
 
 
 ?><br><br><?
 
 
 
 
 


 
 
 
 
 
 
 
 
  