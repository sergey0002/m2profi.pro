 <div class="container">


<?
// Фильтр с первого числа месяца до сегодня
 $firstDayOfMonth = date('Y-m-01', time());
 $yastoday = date('Y-m-d', time());
if(!$_GET[date_limit]){ $_GET[date_limit] = $firstDayOfMonth.' : '.$yastoday ; }
?>
 

<style>
td{padding:3px;}
</style>
 <center>
 
		 <h1>Статистика броней и продаж </h1><br/>
 
	<? 
/*
 
  свои брони чтобы вносили агенты
  сделать ларисе николаевне 
  
  
  1. ДОПОЛНИТЬ ЮЗЕР И ИФРЕЙМ Группой дминистраторы
  2. Сброс броней! верный меняем статус на свободный в последней брони
  только если группа не администратор!
  
  3. Перестроить график и актуальные дома в статистику продаж текущую
  
  
  рейтинг агентств (диапазон дат и прочие)
  рейтинг агентов
  
  
группировка и запрос на каждую линию ! на основе текущего 



Показывать на графике 

Проданные (если есть такой статус)
1к 2к 3к
Бронированные 
1к 2к 3к

сумма сделок 
1к 2к 3к


Свободно квартир ???????????????? только освободившиеся покажет ! новые свободные!?
1к 2 к 3к 
 
 
 
 КОСЯК 1 - свободные квартиры не покажет , только освободившиеся в период!
 
 проданные тоже не понятен процент проданных от всего квартир?!
 
 
 ЛИНИЮ СВОБОДНЫХ КВАРТИР ПРОРИСОВЫВАТЬ ОТДЕЛЬНО НАРАСТАЮЩИМ БЕЗ СТАРТОВОЙ ДАТЫ
 ЛИНИю проданых отдельно + сумма продажи из цен по которым продана фиксировать в бронях!!!!
нарастающим итогом
 линии в процентах сколько от общего числа квартир продано 
 
 
 
 
 
 
 
 
 
 
 СВОДНАЯ ТАБЛИЦА
 
 строки даты
 - столбы 1к 2к 3 каждую
 
 + ВСего свободно 1к 2к 3к обяз
 + всего продано 13
 
 + % проданных 1к 2к 3к
 
 
 +
 
 
*/
			
			 
 
  
 
    
 //print_R($_GET);
 
 
 
 $sa = new sahmatka( $_SESSION , $connection );
 
 
 ?>
<form method="GET">

<div style="width:100%; border-bottom:1px solid; text-align:left; padding:5px; "><span style="font-size:20px;width:170px; display:inline-block;">ПЕРИОД </span> 
<input type="text" name="date_limit" placeholder="Даты" style="width:300px;"  value="<?=$_GET[date_limit]?>" >
 </div>


<div style="width:100%; border-bottom:1px solid; padding:5px;  text-align:left;"><span style="font-size:20px; width:170px; display:inline-block;">ДОМ</span><?  $sa->show_house_check(); ?></div>
<div style="width:100%; border-bottom:1px solid; padding:5px;  text-align:left;"><span style="font-size:20px; width:170px; display:inline-block;">КОМНАТ</span><?  $sa->show_rooms_check(); ?></div>
<div style="width:100%; border-bottom:1px solid; padding:5px;  text-align:left;"><span style="font-size:20px;width:170px; display:inline-block;">СТАТУС</span><?  $sa->show_status_check(); ?></div>
<div style="width:100%; border-bottom:1px solid; padding:5px; text-align:left;"><span style="font-size:20px;width:170px; display:inline-block;">ГРУППИРОВАТЬ </span><?  $sa->show_gr_fileds(); ?></div>
 
   <?
  
 
 //// НАследуем гет значения в скрытых полях
  $vars[]='action';
  $hlp->formgetval($vars);
 ?>
 
 <input type=submit>
 </form>
 
 
 
 
 

























<?
// ТОЛЬКО АКТУАЛЬНЫЕ БРОНИ ПО ТАБЛИЦЕ АППАРТАМЕНТОВ 

/*
Строки для сводной таблицы - месяца, недели итп

столбцы
- суммарная стоимость
- суммарная квадратура
- количество

одна линия = 1 запрос к результатам запроса обзего с групировкой

1. по комнатам если задано несколько --- только те статусы которые есть!
2. По статусу - если различается  - - только те статусы которые есть!
*/
//YEARWEEK(broni.date) as yw - номер недели и года - группировка по неделям
//  EXTRACT(YEAR_MONTH FROM broni.date) as ym, месяц и год - по месяцам
//TO_DAYS(date) функция возвращает номер дня для даты, указанной в аргументе date, (количество дней, прошедших с года 0):
$q = '
 SELECT  apartaments.*,
broni.broni_id , 
broni.date_fu ,
broni.apartments_num ,
broni.user_id ,
broni.date as bdate ,
broni.status as bstatus ,
 
 users.name,
 agency.caption,
 homes.title,
 
 YEARWEEK ( broni.date ) as yw, 
 EXTRACT( YEAR_MONTH FROM broni.date ) as ym,
 TO_DAYS( broni.date) as yd,
 
 ROUND((UNIX_TIMESTAMP()-UNIX_TIMESTAMP(broni.date))/60/60/24,0) as da  
 FROM broni
 left join apartaments  on broni.broni_id = apartaments.status_broni_id  
 left join users on users.id =  broni.user_id     
 left join `agency` on users.agency_id = agency.agency_id 
 left join `homes` on homes.home_id = broni.home_id 
 WHERE 1=1  
 ';
  
// период анализа -РАБОТАЕТ
if($_GET[date_limit])
{
$sb=explode(' : ',$_GET[date_limit]);
$d1=$sb[0];
$d2=$sb[1];

$q.='
AND broni.date   < "'.$d2.'" 
AND broni.date   >  "'.$d1.'" 
 ';
}
 
// Статусы броней -РАБОТАЕТ
if(	is_array($_GET[status])  )
{
	$q.=' AND broni.status IN( ';
	$i=0;
	foreach( $_GET[status] as $k=>$v )
	{
		$i++; if($i>1){ $q.=' , '; } 
		$q.='  "'.$v.'" '; 
	}
	$q.=' ) ';
}



// Комнат -РАБОТАЕТ
if(	is_array($_GET[status])  )
{
	$q.=' AND apartaments.rooms IN( ';
	$i=0;
	foreach( $_GET[rooms] as $k=>$v )
	{
		$i++; if($i>1){ $q.=' , '; } 
		$q.='  "'.$v.'" '; 
	}
	$q.=' ) ';
}



 

 
// ДОма - работает
if(	is_array($_GET[homes])  )
{
	
	$q.=' AND apartaments.home_id IN( ';
	$i=0;
	foreach( $_GET[homes] as $k=>$v )
	{
		$i++; if($i>1){ $q.=' , '; } 
		$q.='  "'.$v.'" '; 
	}
	$q.=' ) ';
}
else{
	// ТОлько дома открытые к показу!
}
$q.=' ORDER by broni.broni_id desc  ';
 
 
 print '<br><br>';
 
 
 
 # ЗАпрос с группировкой для итоговой таблицы! (сумма цен и сумма площадей)
 print  $q2 = 'SELECT *,sum(sel.price) as sum_price , sum(sel.area) as sum_area  FROM ('.$q.') as sel GROUP BY  sel.ym'; 
 print '<br><br>';
 $arr = $hlp -> sql->get_arr( $q2 );
 print '<pre>';
 print_r($arr);
  print '</pre>';
 print $q ;
 
 
 foreach($arr as $k=>$v)
 {
	 
 }
 
 
 
 
 
 
 
 
 
 
 # Вывод сообщений 
 $sa->display_mess();

$query = mysqli_query($connection,$q);

		 ?>
		
		<table class='stripy2'>
		<tr>
			<th>id</th>	 
			<th>Дата  </th>
			<th>Агентство</th>
			<th>Пользователь </th>
			<th>Дом</th>
			<th><?= unit_label_cap('nom') ?></th>
			<th>Комнат</th>
			<th>Площадь</th>
			<th>Новый статус  </th>
			<th> </th>
		 
		</tr>
		<?
		while ($result = mysqli_fetch_array($query)) 
		{
		 $dates[$result['date']]++;
		 if($_GET[date] && $_GET[date] != $result['date'] ){continue;}
		
			echo     '<tr>
					  <td>'.$result['broni_id'].'</td>'.
					 '<td>'.$result['bdate'].'</td>'.
					 '<td>'.$result['caption'].'</td>'.
					 '<td><b>'.$result['login'].'</b> ('.$result['name'].') '.$result['phone'].' '.$result['email'].'</td>' .
					 '<td>'.$result[title].'</td>'. 
					 '<td>'.$result['apartments_num'].'</td>'.
					  '<td>'.$result['rooms'].'</td>'.
					   '<td>'.$result['area'].'</td>'.
					 '<td>'.$sa->status[$result['bstatus']].'</td>'.
					 '<td><b>';
					  
if( $result['status']==4)
{
	if(  $result['da']<=7 )
	{
		print 'До окончания брони ' ;
		print 7-$result['da'];
	}
	else
	{
		print 'Бронь просрочена на ';
		print $result['da']-7;
		print ' дней'; 
	}
	?> 
	</b>
	- <a href="?action=broni&broni_id=<?=$result['broni_id']?>&broni_up=1">Продлить</a>  
	 <?
}
 
 
 
 
 print '</td>' ; 		 
}
?> 
</table></div>
<br><br>
 




























 
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
           'Прошлый месяц': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
		   'Текущий год': [moment().startOf('year'), moment().endOf('year')],
		   'Прошлый год': [moment().subtract(1, 'year').startOf('year'), moment().subtract(1, 'year').endOf('year')]
        }
  });
  
    
});


</script>