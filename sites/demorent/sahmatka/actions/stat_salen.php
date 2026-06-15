 

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

/* 

ФИЛЬТРЫ ДЛЯ ЗАПРОСОВ
  1. ВЫбор дома селект
  2. Выбор даты от до
  3. выбор статуса ( брони / продажи )
  
  4. ДОМА РАЗРЕШЕННЫЕ К ПОКАЗУ (админство и список в таблице!)
  + перенос каонфига в базу
  
*/
if($_GET[date_limit])
{
	$sb=explode(' : ',$_GET[date_limit]);
	$d1=$sb[0];
	$d2=$sb[1];
}
$sql='SELECT *, count(apartaments.apartament_id) as c, GROUP_CONCAT(DISTINCT apartaments.apartment_num ORDER BY apartaments.apartment_num ASC SEPARATOR ", ") AS apartment_nums, REGEXP_SUBSTR(apartaments.rooms,"[0-9]+") as roomsx
FROM `apartaments`
LEFT JOIN broni ON broni.broni_id = apartaments.status_broni_id  AND `broni`.`status` = "3"
LEFT JOIN homes ON homes.home_id = apartaments.home_id
WHERE `apartaments`.`status2` = "3"
AND broni.date   <= "'.$d2.'"  + interval "1" day
AND broni.date   >=  "'.$d1.'" - interval "1" day 
GROUP BY apartaments.home_id, roomsx';
  
 
// PRINT_R($_GET);
 // print $sql;

$query = mysqli_query($GLOBALS['connection'], $sql); 


if(!mysqli_num_rows($query) )
{
	print 'Нет данных за выбранный период';
}
else
{
	while($r = mysqli_fetch_ASSOC($query))
	{	 
$r[rooms] = $r[roomsx];
	//print '<pre>';
	//print_r($r);
	//print '</pre>';
		if( $r[title] )
		{
			$homes_arr[ $r[home_id] ][ $r[rooms] ] = $r[c]; // Проданные квартиры в домах 
			$homes_names[ $r[home_id] ] = $r[title]; // Заголовки домов  
		}
		$rooms[ $r['rooms'] ] = $r['rooms']; // массив всех уник наименований комнат
	}

	asort($rooms);
	//print_r($homes_arr);
	 
	 ?>
	<table> 
	<thead>
	<tr>
	<th> 
	</th>
	<?
	//выводим шапку   
	foreach($homes_arr as $hk => $hv ){
		?>
		<th style="width:100px; text-align:center;"  ><?=$homes_names[$hk]?></th>
		<?
	}
	?>
	<th>Итого</th>
	<th>%</th>
	</tr>
	</thead>
	<tbody>
	<?
	// ПРЕДВАРИТЕЛЬНЫЙ ЦИКЛ СчиТАЕМ ПРОЦЕНТЫ
	//цикл по строкам (комнат)
	foreach($rooms as $rk =>$rv )
	{
		//выводим строку   
		$s=0;
		foreach($homes_arr as $hk => $hv )
		{
			if( $hv[$rk] )
			{
				//print $hv[$rk];
			}
			else
			{ 
				//print 0;
			}
			$s=$s+$hv[$rk];
			$summ_col[$hk]=$summ_col[$hk]+$hv[$rk];
		}
		$s_itogo = $s_itogo + $s;
	}


	//цикл по строкам (комнат)
	foreach($rooms as $rk =>$rv )
	{
		
		?>
		<tr>
		<td><?=$rv?></td><?
		//выводим строку   
		$s=0;
	 
		foreach($homes_arr as $hk => $hv )
		{ 
			?> 
			<td align="center">
			<?
			if( $hv[$rk] ){print '<a href="user.php?action=show_broni&home='.$hk.'&rooms='.$rk.'&status=3&date_limit='.$d1.'+%3A+'.$d2.'">'.$hv[$rk].'</a>'; }
			else{ print 0;}
			$s=$s+$hv[$rk];
			?>
			</td>
			<?
		}
	 
		?>
		<td ><? print '<a href="user.php?action=show_broni&rooms='.$rk.'&status=3&date_limit='.$d1.'+%3A+'.$d2.'">'.$s.'</a>';   ?>  </td>
		
		<td ><?
		
		print round($s/$s_itogo*100,2);
		?></td>
		</tr>
		<?
	}

	?>
	<tr>
	<td><b>Итого</b></td>
	<?
	//выводим шапку   
	foreach($homes_arr as $hk => $hv ){
		?>
		<td width="100px; " align="center"><b><? print '<a href="user.php?action=show_broni&home='.$hk.'&status=3&date_limit='.$d1.'+%3A+'.$d2.'">'.$summ_col[$hk].'</a>';   ?></b></td>
		<?
	}
	?>
	<td> <? print '<a href="user.php?action=show_broni&status=3&date_limit='.$d1.'+%3A+'.$d2.'"><b>'.$s_itogo.'</b></a>';   ?>      </td>
	<td>   </td>
	</tr>
	</tbody>
	</table>
	  <?
}
  $content=ob_get_clean();
?>
  
  
  
  
  
  
  
  
  
  
  
 
 
<section class="section-stat">
	<div class="container">
		<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">СТАТИСТИКА <span>подписанные договоры</span></div>
		</div>
		<div class="stat">
			<div class="stat-top stat-top_lp stat-top_dogovor">
				<form method="GET" action="user.php?action=stat_salen&" id="filtrform">
					<input type="hidden" name="action" value="<?=$_GET[action]?>" >
					<div class="stat-top-filter">
						<div class="stat-top-item stat-top-in stat-top-item_date">
							<input type="text"   placeholder="За все время" name="date_limit" value="<?=$_GET[date_limit]?>" autocomplete="off" style="max-width:100%; min-width:250px;">
						</div>
						<a href="#" class="stat-top-btn btn btn_arrow-long" onclick="document.getElementById('filtrform').submit(); return false;" style="margin-left:45px;">Выбрать<i></i></a>
					</div>
				</form>
				
				
				<a href="JavaScript:window.print();" class="stat-top__print"></a>
			</div>
			<div class="stat-table table">
			<?=$content?>
			</div>
		</div>
	</div>
</section>




 
 
 	
 
  
 
  
  
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script>
$(function() {
	 
  $('input[name="date_limit"]').daterangepicker({
      "locale": {
        "format": "YYYY-MM-DD",
        "separator": " : ",
        "applyLabel": "Применить",
        "cancelLabel": "Закрыть",
        "fromLabel": "От",
        "toLabel": "До",
        "customRangeLabel": "Настроить",
        "weekLabel": "W",
        "daysOfWeek": [
            "Вс",
            "Пн",
            "Вт",
            "Ср",
            "Чт",
            "Пт",
            "Сб"
        ],
        "monthNames": [
            "Январь",
            "Февраль",
            "Март",
            "Апрель",
            "Май",
            "Июнь",
            "Июль",
            "Август",
            "Сентябрь",
            "Октябрь",
            "Ноябрь",
            "Декабрь"
        ],
        "firstDay": 1
    } ,
	ranges: {
           'Сегодня': [moment(), moment()],
           'Вчера': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           '7 дней': [moment().subtract(6, 'days'), moment()],
           '30 дней': [moment().subtract(29, 'days'), moment()],
           'Текущий месяц': [moment().startOf('month'), moment().endOf('month')],
           'Прошлый месяц': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
  });
  
    
});


</script>



  <?
 
 
 
 
 
 
 
 
 
 
 
 
 
 
  