<?
	?>
		
		


<section class="section-stat">
	<div class="container">
		<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">Агентства</div>
		</div>
		
		
		
		
		
		
<? 
		 
#Удаление
    if (isset($_GET['del_id'])) { //проверяем, есть ли переменная на удаление
        $sql = mysqli_query($connection,'DELETE FROM `agency` WHERE `agency_id` = '.$_GET['del_id']); //удаляем строку из таблицы
    }
	
 if($_GET[reg]==1)
 {

		 
		 
	 if($_POST[caption] && !$_GET[id])
	 { 
 
 
			$caption = $_POST['caption']; // Присваеваем переменной значение из поля с логином             
			$admin_user_id = $_POST['admin_user_id']; // Присваеваем другой переменной значение из поля с паролем
			 
			$query = "INSERT INTO `agency` (caption, admin_user_id) VALUES ('$caption', '$admin_user_id')"; // Создаем переменную с запросом к базе данных на отправку нового юзера
			$result = mysqli_query($connection, $query) or die(mysql_error()); // Отправляем переменную с запросом в базу данных 
			 
			 
			$aid =  mysqli_insert_id($connection); // ИД АГЕНТСТВА ========= 
			print '<b> Агентство №'.$aid.'</b>';
			
			
			
			
 
			$login = $_POST['login2']; // Присваеваем переменной значение из поля с логином             
			$password = $_POST['password2']; // Присваеваем другой переменной значение из поля с паролем
			$name = $_POST['name2']; // Присваеваем другой переменной значение из поля с паролем
			
			$e_mail = $_POST['e_mail'];
			$phone = $_POST['phone'];
			$agency_id = $_POST['agency_id'];
			
		    $query = "INSERT INTO `users` (login, password,name,e_mail,phone,agency_id) VALUES ('$login', '$password' ,'$name' ,'$e_mail','$phone','$aid')"; // Создаем переменную с запросом к базе данных на отправку нового юзера
			$result = mysqli_query($connection, $query) or die(mysql_error()); // Отправляем переменную с запросом в базу данных 
			echo "<div align='center'>Данные сохранены!</div>"; // Сообщаем что все получилось
 
			$n_uid =  mysqli_insert_id($connection); // ИД АГЕНТСТВА ========= 
 
			// Обновляем данные агенства делаем админом добавленного пользователя
			
			$q = 'UPDATE `agency` SET '
                    .'`admin_user_id` = "'.$n_uid.'" '
                    .'WHERE `agency_id` = "'.$aid.'" ';
					// print $q;
			$sql = mysqli_query($connection,$q);
					
					
					
					
 
		# формирвем сообщение на почту о бронировании квартиры
		// вложения файлов
		$message = "Кабинет дилеров находится по адресу  <a href=\"http://www.em-nsk.ru/images/sahmatka.htm\">http://www.em-nsk.ru/images/sahmatka.htm</a>  \r\n <br/>";
		$message .= "Ваш доступ к кабинету дилеров \r\n <br/>";
		$message .= "логин -   ".$login."\r\n </b><br/> ";
		$message .= "Пароль ".$password."</b> ";
			
  
	  
	 }
	 elseif($_POST[caption]  && $_GET[id])
	 {
					$q = 'UPDATE `agency` SET '
                    .'`caption` = "'.$_POST['caption'].'",'
                    .'`admin_user_id` = "'.$_POST['admin_user_id'].'" '
                    .'WHERE `agency_id` = "'.$_GET['id'].'" ';
					// print $q;
					$sql = mysqli_query($connection,$q);
	 }
	 elseif($_POST)
	 {
		 print '<span style="color:red">Заполните все поля!</span>';
		 
	 }
	 	 
		 
 
     // Редактирование
	 if($_GET[id])
	 {
		 $data= 1;

		 
		  $t='Редактирование агентства:'; 

		// Все пользователи агентсва -  собираем для селект поля
		$query = mysqli_query($connection, "SELECT * FROM `users`   "); // WHERE agency_id='".$_GET['id']."'
		while( $row = mysqli_fetch_array($query ))
		{
		   $users_arr[$row['id']] = $row['name'];
		}
		$query = mysqli_query($connection, "SELECT * FROM `agency` WHERE agency_id='".$_GET['id']."' "); 
		$result = mysqli_fetch_array($query);
	 }
	 else
	 {
		 // Все пользователи  
		$query = mysqli_query($connection, "SELECT * FROM `users`  "); //WHERE agency_id='".$_GET['id']."'
		while( $row = mysqli_fetch_array($query ))
		{
		   // print_r($row);
		   $users_arr[$row['id']] = $row['name'];
		}
		  $t='Новое агентство:';
	 }
	 ?>




<div class="bottom-form">
				<div id="bottom-form" class="bottom-form__title"><?=$t?></div>
				<form action="user.php?action=agency&reg=1&id=<?=$_GET[id]?>" method="post">
					<div class="bottom-form-wrap">
						<div class="bottom-form-fields">
							<div class="bottom-form__in">
								<input type="text" placeholder="Название агентства" name="caption" value="<?=$result[caption]?>">
							</div>
							
							
							
							<?
							if(!$_GET[id]) // только для новых агентств
							{
							?>
							<div class="bottom-form__in">
								<input type="text" placeholder="Логин администратора" name="login2" value="<?=$result[login]?>">
							</div>
							<div class="bottom-form__in">
								<input type="text" placeholder="Пароль" name="password2" value="<?=$result[password]?>">
							</div>
							<div class="bottom-form__in">
								<input type="text" placeholder="ФИО администратора" name="name2" value="<?=$result[name]?>">
							</div>
							<div class="bottom-form__in">
								<input type="text" placeholder="E-mail" name="e_mail" value="<?=$result[e_mail]?>">
							</div>
							<div class="bottom-form__in">
								<input class="phone-in" type="text" placeholder="Телефон" im-insert="true" name="phone" value="<?=$result[phone]?>">
							</div>
							<?
							}
							?>
							
							
						</div>
						<button class="btn btn_size6 btn_arrowxl bottom-form__btn">Сохранить<i></i></button>
					</div>
				</form>
			</div>
 <?

 }
?>
		
		
		
		
		
		
		
		<div class="stat">
			<div class="stat-top stat-top_lp stat-top_user">
				<div class="stat-top-filter">
					<div class="stat-top-item stat-top-in stat-top-in_search">
						<input type="search" placeholder="Найти">
					</div>
				<?
				
				if(check_access('admin'))
					{
						 print '<a href="?action=agency&reg=1" class="stat-top-btn btn btn_arrow-long" >ДОБАВИТЬ агентство<i></i></a>';
					}
					
					?>

				
						
						
				</div>
				<a href="JavaScript:window.print();" class="stat-top__print"></a>
			</div>
			<div class="stat-table stat-table_notpd stat-table-agency-in table">
				<table>
					<thead>
						<tr>
							<th><b>id</b></th>
							<th><a href="#"><b>НАЗВАНИЕ АГЕНТСТВА</b></a></th>
							<th><a href="#"><b>АГЕНТСТВО</b></a></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
				 
					
					
					<?php
		$query = mysqli_query($connection, 
		"SELECT agency.* , users.login, users.name 
		FROM `agency` LEFT JOIN users ON agency.admin_user_id = users.id  order by agency_id desc"); 
		
		while ($result = mysqli_fetch_array($query)) 
		{
			
			 
					echo     
					'<tr>
					  <td>'.$result['agency_id'].'</td>'.
					 '<td>'.$result['caption'].'</td>'.
					 '<td>'.$result['login'].' '.$result['name'].'</td>'.
					  
					 '<td> 
					 
					 
					';
					
					
					
					if(check_access('admin'))
					{
						 print '<a href="?action=agency&reg=1&id='.$result['agency_id'].'" class="table-edit  "></a>';
					}
					print '</td></tr>';
			 
		}
		?>
		
		
		
					</tbody>
				</table>
			</div>
		</div>
	</div>
</section>



 
 
 
 
		 
	
		
		 

