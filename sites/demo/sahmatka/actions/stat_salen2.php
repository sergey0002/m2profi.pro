<?
// Фильтр с первого числа месяца до сегодня
 $firstDayOfMonth = date('Y-m-01', time());
 $yastoday = date('Y-m-d', time());
if(!$_GET[date_limit]){ $_GET[date_limit] = $firstDayOfMonth.' : '.$yastoday ; }
 
 
  
 
?>
	
			
<?	 
ob_start();



 


 
// print_r($_REQUEST);
 
$home_id = $_REQUEST['home'];
   
$sql='SELECT *, round(sum(apartaments.area),2) as summ_area, sum(apartaments.price) as summ_price,  REGEXP_SUBSTR(apartaments.rooms,"[0-9]+") as roomsx, count(apartaments.apartament_id) as c  FROM  apartaments  
LEFT JOIN homes ON homes.home_id = apartaments.home_id
WHERE ( `apartaments`.`status2` != "3" /*Все  не проданные квартиры*/
) AND rooms>0 AND  (homes.show>0 ) ';


if($home_id)
{
	
	$sql.= " AND apartaments.home_id=".$home_id." ";
}
$sql.= " GROUP BY ";

if($home_id)
{
	
	$sql.= " apartaments.home_id, ";
}
 


$sql.= " roomsx; ";
  
 
$query = mysqli_query($GLOBALS['connection'], $sql); 
 
 
?> <table ><?
 
?>
<tr>
<th> к </th>
<th> Количество квартир </th>
<th> Суммарная площадь </th>
<th> Суммарная стоимость</th>
<th> Средняя стоимость м<sup>2</sup></th>
</tr>
<?
//цикл по строкам (комнат)
while($r = mysqli_fetch_ASSOC($query))
{	
	?>
	<tr>
	<td><?=$r[roomsx]?></td>
	<td><?=$r[c]?></td>
	<td><?=number_format($r[summ_area], 2, ',', ' ') ?></td>
	<td><?=number_format($r[summ_price], 2, ',', ' ')?></td>
	<td><?=number_format(round($r[summ_price]/$r[summ_area],2), 2, ',', ' ')?></td>
	</tr>
	<?
	
	$summ_arr[c] = $summ_arr[c] + $r[c];
	$summ_arr[area] = $summ_arr[area] + $r[summ_area];
	$summ_arr[price] = $summ_arr[price] + $r[summ_price];
	$avg_metr[]=$r[summ_price]/$r[summ_area]; 
}

 
 // средняя стоимость метра
 $avg_metr_ = array_sum($avg_metr)/count($avg_metr);
  ?>
  
  
  <tr>
	<td></td>
	<td><b><?=number_format($summ_arr[c], 0, ',', ' ') ?> </b></td>
	<td><b><?=number_format($summ_arr[area], 2, ',', ' ') ?> м<sup>2<sup></b></td>
	<td><b><?=number_format( $summ_arr[price], 2, ',', ' ') ?></b></td>
	<td><b><?=number_format( $avg_metr_, 2, ',', ' ') ?></b></td>
	</tr>
  
  </table> 
  <?
  $table=ob_get_clean();
  ?>
  
  
 

<section class="section-stat">
	<div class="container">
		<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">Сводная статистика</div>
		</div>

		<div class="stat">
			<div class="stat-top">
				<div class="stat-top-filter">
					<div class="stat-top-items">
						<a href="user.php?action=stat_salen2" class="stat-top-items__item">Все</a>
				 
						<? object_menux2('stat_salen2');?>
					</div>
					<div class="stat-top-btns">
						 
						<a href="JavaScript:window.print();" class="stat-top__print"></a>
					</div>
				</div>
			</div>
			<div class="stat-table stat-table-agency table">
			
			 <?=$table?>
			 
			</div>

		</div>

	</div>
</section> 

 
 
 
 
 
 
  