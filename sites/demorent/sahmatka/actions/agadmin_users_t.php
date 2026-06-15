 <?



ob_start();
 #Удаление
  if (isset($_GET['del_id'])) { //проверяем, есть ли переменная на удаление
     // $sql = mysqli_query($connection,'DELETE FROM `users` WHERE `id` = '.$_GET['del_id']); //удаляем строку из таблицы
  }
  
  
  
	
 if($_GET[reg]==1)
 {
	 
	 if($_POST[login2] && $_POST[password2]  && $_POST[name2]  && !$_GET[id])
	 { 
			$login = $_POST['login2']; // Присваеваем переменной значение из поля с логином             
			$password = $_POST['password2']; // Присваеваем другой переменной значение из поля с паролем
			$name = $_POST['name2']; // Присваеваем другой переменной значение из поля с паролем
			
			$e_mail = $_POST['e_mail'];
			$phone = $_POST['phone'];
			$agency_id = $_SESSION['agency_id'];
			
		    $query = "INSERT INTO `users` (login, password,name,e_mail,phone,agency_id) VALUES ('$login', '$password' ,'$name' ,'$e_mail','$phone','$agency_id')"; // Создаем переменную с запросом к базе данных на отправку нового юзера
			$result = mysqli_query($connection, $query) or die(mysql_error()); // Отправляем переменную с запросом в базу данных 
			echo "<div align='center'>Данные сохранены!</div>"; // Сообщаем что все получилось
	 }
	 elseif($_POST[login2] && $_POST[password2]  && $_POST[name2] && $_GET[id])
	 {
					$q = 'UPDATE `users` SET '
                    .'`name` = "'.$_POST['name2'].'",'
                    .'`password` = "'.$_POST['password2'].'" , '
					.'`login` = "'.$_POST['login2'].'" , '
					
					.'`e_mail` = "'.$_POST['e_mail'].'",'
                    .'`phone` = "'.$_POST['phone'].'" , '
					.'`agency_id` = "'.$_SESSION['agency_id'].'" '
					
                    .'WHERE `id` = "'.$_GET['id'].'" ';
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
		 
		 
		 
		 ?><h2>Редактирование пользователя:</h2><? 
 
	 
$query = mysqli_query($connection, "SELECT * FROM `users` WHERE id='".$_GET['id']."' ");   
$result = mysqli_fetch_array($query);
	
	 }
	 else
	 {
		 
		 
			 
		$query = mysqli_query($connection, "SELECT * FROM `users` WHERE id='".$_GET['id']."' ");   
		$result = mysqli_fetch_array($query);
		
	 }
	 ?>

<form action="user.php?action=users&reg=1&id=<?=$_GET[id]?>" method="post">
Логин: <input type="text" name="login2" value="<?=$result[login]?>"><br><br>
Пароль: <input type="text" name="password2" value="<?=$result[password]?>"><br><br>
Имя: <input type="text" name="name2" value="<?=$result[name]?>"><br><br>
E-Mail: <input type="text" name="e_mail" value="<?=$result[e_mail]?>"><br><br>
Телефон: <input type="text" name="phone" value="<?=$result[phone]?>"><br><br>
<input type="submit" value="сохранить" >
</form><br><br>
	 <?

 }


$form = ob_get_clean();
	
	
	
	
	
	
	
ob_start();
?>	

<table>
<thead>
	<tr>
	<th><b>id</b></th>
	<th><a href="#"><b>Логин</b></a></th>
		<th><a href="#"><b>Пароль</b></a></th>
		<th><a href="#"><b>Имя</b></a></th>
		<th><a href="#"><b>E-Mail</b></a></th>
		<th><a href="#"><b>Телефон</b></a></th>
		<th></th>
		</tr>
</thead>		
<tbody>
<?php

$sql = "SELECT  *  FROM `users` WHERE `users`.`agency_id` = ".$_SESSION['agency_id']." ";



############################# ТЕкстовый поиск
$fileds[]='users.name';
$fileds[]='users.e_mail';
$fileds[]='users.phone';
$fileds[]='users.login';

if($_GET['search'])
{
	$search = $_GET['search'];
	$sql.=' AND ( ';
	$i=0;
	foreach($fileds as $k=>$v)
	{
		if($i>0){$sql.=' OR ';} $i++;
		$sql.=' '.$v.' LIKE "%'.$search.'%" ';
	}
	$sql .=' )';
}

// print $sql;
$query = mysqli_query($connection, $sql); 

 
  
while ($result = mysqli_fetch_array($query)) 
{
	// print_r($result);
	if($result['login']!='admin')
	{
    echo     '<tr>
			  <td>'.$result['id'].'</td>'.
             '<td>'.$result['login'].'</td>'.
             '<td>'.$result['password'].'</td>'.
			 '<td>'.$result['name'].'</td>'.
			 '<td>'.$result['e_mail'].'</td>'.
			 '<td>'.$result['phone'].'</td>'.
             '<td><a href="?action=users&reg=1&id='.$result['id'].'" style="color:#000">Редактировать</a></td></tr>';
	}
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
			<div class="page-header__title">ПОЛЬЗОВАТЕЛИ</div>
		</div>
		<?=$form?>
		<div class="stat">
			<div class="stat-top stat-top_lp stat-top_user">
			<form action="" method="get" id="searchform">
			<input type="hidden" name="action" value="users" />
				<div class="stat-top-filter">
					<div class="stat-top-item stat-top-in stat-top-in_search">
						<input type="search" name="search" placeholder="Найти" value="<?=$_GET['search']?>" style="wudth:auto; float:left;">
						<a href="#" onclick="document.getElementById('searchform').submit(); return false;" style="line-height: 50px; margin-left: -35px;" ><img src="template/default/images/search.svg" /></a>
					</div>
					<a href="?action=users&reg=1" class="stat-top-btn btn btn_arrow-long">ДОБАВИТЬ ПОЛЬЗОВАТЕЛЯ<i></i></a>
				</div>
				</form>
				<a href="JavaScript:window.print();" class="stat-top__print"></a>
			</div>
			<div class="stat-table stat-table_notpd stat-table-user table">
			
			<?=$content?>
				 
			</div>
		</div>
	</div>
</section>


