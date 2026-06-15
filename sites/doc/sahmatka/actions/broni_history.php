<?	
//  админ всея руси
if(   $_SESSION['sh_login']!='admin'  )
{
	exit;
}




/*
1. Только актуальные брони (последняя запись)
2. История бронирования каждой квартиры

-----
+ Добавить поле актуальность брони! 
при бронировании убираем актуальность для этой брони у всех для этой квартиры кроме текущей
+ при запросе получаем только актуальные
-----
псевдо крон задача сброс актуальности броней для не админа старше 10 дней  каждый вход в кабинет (каждый час)


 

отнять от текущей даты дату брони = сколько дней назад была сделана бронь 
если более 7 то ее надо гасить
и если не админ




СТАТУСЫ БРОНЕЙ ДОПполе (как фиксировать историю изменений этих статусов?!)

41- актуальная 

42- просроченная - ставится не бронь
43- отмененная пользователем - ставится не бронь

44- на рассмотрении администратора - Н!Е СТАВИТСЯ НЕ БРОНЬ
45- отправлена на доработку администратором - Н!Е СТАВИТСЯ НЕ БРОНЬ


1. в статистике полностью переписывать работу с бронями?!
новый статус "не бронь" ! в таблице брони
который отменяет статус брони но не участвует в выборке броней по времени  для документов! - таким образом всегда можно получить документы по брони и востановить бронь


1
======================

*/
 
 
$h = $sa->get_homes_arr();
// print_r($h);
?>
  
				
<?
ob_start();
$sa = new sahmatka( $_SESSION , $connection );
  $q = '
 SELECT * , broni.date as fdate , broni.status as bstatus
FROM `broni`
LEFT JOIN users ON users.id= broni.user_id
LEFT JOIN agency ON agency.agency_id= users.agency_id
LEFT JOIN apartaments on apartaments.status_broni_id= broni_id

where 1=1 ';

if( $_GET[home] )
{
	$q.=' AND broni.home_id="'.$_GET[home].'" ';
}
if( $_GET['apartament_num'] )
{
	$q.=' AND broni.apartments_num="'.$_GET['apartament_num'].'" ';
}

$q.='  order by broni.home_id, apartments_num,broni.date ';
//print $q;
 
 
 
 
 
# Вывод сообщений 
$sa->display_mess();
$query = mysqli_query($connection,$q);
		 ?>
		<table>
		<thead>
		<tr>
			<th>id</th>	 
			<th>Дата </th>
			<th>Агентство</th>
			<th>Пользователь </th>
			<th>Дом</th>
			<th>Квартира</th>
			<th>Комнат</th>
			<th>Статус брони</th>
			<th>Актуальный статус</th>
		</tr>
		</thead>
		<tbody>
		<?
 
			
		while ($result = mysqli_fetch_assoc($query)) 
		{
			
			//print '<pre>';
			
			// print_R($result);
			 
			// print '</pre>';
		// $dates[$result['date']]++;
		// if($_GET[date] && $_GET[date] != $result['date'] ){continue;}
		 
		// актуальный статус
		if(1==1)
		{
			
		}
		
		// Смена квартиры
		if($result['apartments_num']!=$anum)
		{
			$anum = $result['apartments_num'];
			$trstyle=' style="border-top: 5px solid;" ';
		}
		else
		{
			$trstyle=' style="" ';
		}
		
		
			echo     '<tr '.$trstyle.'>
					  <td>'.$result['broni_id'].'</td>'.
					 '<td>'.$result['fdate'].'</td>'.
					 '<td>'.$result['caption'].'</td>'.
					 '<td><b>'.$result['login'].'</b> ('.$result['name'].') '.$result['phone'].' '.$result['email'].'</td>' .
					 '<td>'.$result['home_id'].'</td>'.
					 '<td>'.$result['apartments_num'].'</td>'.
					 
					  '<td>'.$result['rooms'].'</td>'.
					 
					 					 '<td> ';
					 
if(  $result['bstatus']==2 ){ print 'Свободна';} 
elseif(  $result['bstatus']==3 ){ print 'Продана';} 
elseif(  $result['bstatus']==4 ){ print 'Забронирована';} 
elseif(  $result['bstatus']==5 ){ print 'Бронь застройщика';} 
elseif(  $result['bstatus']==6 ){ print 'Квартира подрядчика';} 
elseif(  $result['bstatus']===0 ){ print 'Свободна';} 
print '</td>' .
 
 
 
 
					 '<td> ';
					 
if(  $result['status2']==2 ){ print 'Свободна';} 
elseif(  $result['status2']==3 ){ print 'Продана';} 
elseif(  $result['status2']==4 ){ print 'Забронирована';} 
elseif(  $result['status2']==5 ){ print 'Бронь застройщика';} 
elseif(  $result['status2']==6 ){ print 'Квартира подрядчика';} 
 elseif(  $result['status2']===0 ){ print 'Свободна';} 
 print '</td>' .
 
  '<td> </td>';
					 
					 
		}
		?>
		</tbody>
		</table> 
 <?
 $content = ob_get_clean();
 ?>
 
 
 
 
 
 
 
 <section class="section-stat">
	<div class="container">
		<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">История бронирования</span></div>
		</div>
		<div class="stat">
			<div class="stat-top stat-top_lp stat-top_key">
				<div class="stat-top-filter">
					<div class="stat-top-item stat-top-select ">
						 <select data-placeholder="Дом">
							<option></option>
							<option>1</option>
							<option>2</option>
							<option>3</option>
							<option>4</option>
						</select>
					</div>
					<div class="stat-top-item stat-top-select stat-top-item_section">
						<select data-placeholder="Секция">
							<option></option>
							<option>1</option>
							<option>2</option>
							<option>3</option>
							<option>4</option>
						</select>
					</div>
					<div class="stat-top-item stat-top-select stat-top-item_kv">
						<select data-placeholder="Квартира">
							<option></option>
							<option>1</option>
							<option>2</option>
							<option>3</option>
						</select>
					</div>
					<a href="#" class="stat-top-btn btn btn_notbdrs">Выбрать</a>
				</div>
				<a href="JavaScript:window.print();" class="stat-top__print"></a>
			</div>
			<div class="stat-table stat-table-user stat-table_notpd table">
				 <?=$content?>
			</div>
		</div>
	</div>
</section>
		 