<?	
print '<pre>';

//print_r( $_SESSION );
print '</pre>';


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
print '	<div class="page-title">Брони</div>';
	
 




$sa = new sahmatka( $_SESSION , $connection );
 
/*
1. вывод броней для каждого пользователя
 
Каталог домов по ид в базе (массив) - +метод получения списка домов 
 
для фильтра
+ для фида
+ для каталога
+ итп права на дома
 редактирование домов
 
+ Добавление домов
(мастер) добавить секцию
+ расставить номера квартир 

*/
// 



if($_GET[broni_del] && $_GET[broni_id]) // Отмена брони
{
	// ДОбавляем новую запись в брони - квартира свободна!
	// обновляем статус квартиры в аппартаментах
	$sa->up_broni( $_GET[broni_id] , 2 );
}
elseif($_GET[broni_up] && $_GET[broni_id]) // Продление брони
{
	// Добавляем новую запись бронирования в брони
	// обновляем статус квартиры в аппартаментах
	$sa->up_broni( $_GET[broni_id] , 4 );
}







 /*
 // КОПИРУЕМ ДАТУ дата не обновляется вместе с бронью ! наследуется старая!
 UPDATE `broni` as t1 , (SELECT date,broni_id FROM broni as t2 ) as t2 SET t1.date_fu =  t2.date WHERE t1.broni_id = t2.broni_id; 
 */
 
 
 
# РАБОЧИЙ ЗАПРОС ПЕРЕНОС АПАРТАМЕНТ ИД В БРОНИ
$q = ' UPDATE `broni` as t1 , (SELECT apartament_id , home_id , apartment_num FROM apartaments  ) as t2 
SET t1.apartament_id =  t2.apartament_id 
WHERE t1.home_id = t2.home_id AND t1.apartments_num = t2.apartment_num ; ';
  
$query = mysqli_query($connection,$q);



# РАБОЧИЙ ЗАПРОС НА ДОБАВЛЕНИЕ АКТУАЛЬНОГО СТАТУСА В ТАБЛИЦУ АППАРТАМЕНТОВ
$q = ' UPDATE apartaments AS T1,(select broni.broni_id, broni.status , broni.date , broni.home_id, broni.apartments_num FROM broni where broni.date = (select max(date) from broni as b where b.home_id = broni.home_id AND b.apartments_num = broni.apartments_num )) AS T2 
  SET 
  T1.status_broni_id= T2.broni_id,
  T1.status_broni_date= T2.date,
  T1.status2= T2.status
WHERE T1.home_id = T2.home_id AND T1.apartment_num = T2.apartments_num; ';
  
$query = mysqli_query($connection,$q);

 
# todo ! Как показывать первую бронь?! этого пользователя действительная дата бронирования теряется!!!!!!!!!!!!! сохранять ее в отдельном поле! обновляется только при смене пользователя! брони
 
// ТОЛЬКО АКТУАЛЬНЫЕ БРОНИ ПО ТАБЛИЦЕ АППАРТАМЕНТОВ 
$q = ' SELECT  *, ROUND((UNIX_TIMESTAMP()-UNIX_TIMESTAMP(broni.date))/60/60/24,0) as da , broni.date as fdate FROM apartaments
 left join broni on broni.broni_id = apartaments.status_broni_id  
 left join users on users.id = broni.user_id    
 left join `agency` on users.agency_id = agency.agency_id 
 left join `homes` on homes.home_id = broni.home_id 
 WHERE apartaments.status2=4 
 ';
  
// простой пользователь не админ агентства
if($_SESSION['sh_login'] && !$_SESSION['adm_caption'] && $_SESSION['sh_login']!='admin' &&  $_SESSION['sh_login']!='uservip' &&  $_SESSION['sh_login']!='partner'  &&  $_SESSION['sh_login']!='admin_demo')
{
	$q.='AND users.login="'.$_SESSION['sh_login'].'"  '; // только его брони
}
//  админ агентства
elseif($_SESSION['sh_login'] && $_SESSION['adm_caption'] && $_SESSION['sh_login']!='admin' &&  $_SESSION['sh_login']!='uservip' &&  $_SESSION['sh_login']!='partner'  &&  $_SESSION['sh_login']!='admin_demo')
{
	$q.='AND agency.agency_id="'.$_SESSION['agency_id'].'"  '; // только брони агентства
	$q.='AND users.login="'.$_SESSION['sh_login'].'"  '; // только его брони
}	
//  админ всея руси
elseif(   $_SESSION['sh_login']=='admin'   ||  $_SESSION['sh_login']!='admin_demo')
{
	// все брони
	?>
	<div style="text-align:right; width:100%;">
	<a href="user.php?action=broni_history">История бронирования</a>
	</div>
	<?
}

$q.='ORDER by date_fu desc  ';
 
 
 
 
 
 
 
 
  
 
 
 
 
 # Вывод сообщений 
 $sa->display_mess();

$query = mysqli_query($connection,$q);

		 ?>
		<div class="table-wrap">
				 
	 
		<table >
		<tr>
			<th>id</th>	 
			<th>Дата брони</th>
			<th>Агентство</th>
			<th>Пользователь </th>
			<th>Дом</th>
			<th>Квартира</th>
			<th>До окончания брони (дней)</th>
		 
		</tr>
		<?
 
			
		while ($result = mysqli_fetch_array($query)) 
		{
			
			//print_R($result);
		 $dates[$result['date']]++;
		 if($_GET[date] && $_GET[date] != $result['date'] ){continue;}
		
			echo     '<tr>
					  <td>'.$result['broni_id'].'</td>'.
					 '<td>'.$result['fdate'].'</td>'.
					 '<td>'.$result['caption'].'</td>'.
					 '<td><b>'.$result['login'].'</b> ('.$result['name'].') '.$result['phone'].' '.$result['email'].'</td>' .
					 '<td>'.$result[title].'</td>'. 
					 '<td>'.$result['apartments_num'].'</td>'.
					 '<td><b>';
					 
					 
 
 
if(  $result['da']<=10 )
{
	print 'До снятия брони! ';
	print 10-$result['da'];
	print ' дней';

 }
else
{
	 print 'Бронь просрочена на ';
	 print $result['da']-10;
	 print ' дней';
	 
	 
	 
	 // КОСТЫЛЬ НА СНЯТИЕ БРОНЕЙ ПРОСРОЧЕННЫХ!
	  if( $result['agency_id']!=92 && $result['id']!=1)
	  {
		  print_r($result);
 
		 
		  print '<br><br>';
		  $status = '2
		  ';
		  // 1. ДОбавляем строку в таблицу броней о снятии просрочки
		 // print $queryw = 'INSERT INTO `broni` (`home_id`, `section_id`,  `floor`, `apartments`,  `apartments_num`, `user_id`,`status`, `date`,`date_fu`) VALUES ("'.$result['home_id'].'", "'.$result['section_id'].'", "'.$result['floor'].'", "'.$result['apartments'].'", "'.$result['apartments_num'].'","'.$result['id'].'","'.$status.'", NOW(), NOW() )'; 
	 
		  // 2. Меняем статус в таблице апартаменты
		 // print '<br><br>';
		 // print  $queryw = 'UPDATE `apartaments` SET `status` = "'. $status.'" WHERE `home_id` = "'.$result['home_id'].'" AND `apartment_num` = "'.$result['apartment_num'].'";'; 
		
		
		
		 $sa->up_broni( $result['broni_id'] , 2 , 'автоматическое снятие в связи с просрочкой 10 дней' );
		
		
		  $rq = '';
	  }
	 
}
 

					 ?> 
					 </b>
					- <a href="?action=broni&broni_id=<?=$result['broni_id']?>&broni_up=1">Продлить</a>  
					 <?
					 print '</td>' ; 
					 
					 
		}
		?>
		 
		</table></div>
		 <br><br>
 
		<?
		
		
		print '<pre>';
		// print_r($_SESSION);
		print '</pre>';