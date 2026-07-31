<?
class users
{
	// Получить текущего юзера
	function get_this()
	{
 		 return $this->in($_SESSION['sh_login'],$_SESSION['sh_password']);
	}
	 
	 
	function out()
	{
		
		# ВЫХОД
		 unset($_SESSION['sh_password']); // Чистим сессию пароля
		 unset($_SESSION['sh_login']); // Чистим сессию логина
		 unset($_SESSION['sh_name']); // Чистим 
		 unset($_SESSION['sh_id']); // Чистим 	
 
			unset($_SESSION['adm_caption']); // Чистим  
			unset($_SESSION['gl_user_id']); // Чистим  
			unset($_SESSION['ucaption']); // Чистим  
			unset($_SESSION['agency_adm_id']); // Чистим 
 
		 
	}


	function in($login='', $pass='')
	{
		$connection = $GLOBALS['connection'];
		$password = $pass; // Записываем пароль в переменную           
		//$sql = " SELECT * FROM `users` WHERE `login` = '$login' AND `password` = '$password' ";
		$sql="SELECT users.*,	agency.agency_id as agency_adm_id, agency.caption as adm_caption , user_agency.caption as ucaption  FROM `users` left join agency on agency.admin_user_id = users.id  left join agency as user_agency on user_agency.agency_id = users.agency_id WHERE `login` = '$login' AND `password` = '$password'";
		
	 
		$query = mysqli_query($connection, $sql ); // Формируем переменную с запросом к базе данных с проверкой пользователя
		$result = mysqli_fetch_assoc($query); // Формируем переменную с исполнением запроса к БД 
 
		 
		if (empty($result['id'])) // Если запрос к бд не возвразяет id пользователя
		{ 
			return false;
		}
		else // Если возвращяем id пользователя, выполняем вход под ним
		{
			$_SESSION['agency_id'] = $result['agency_id']; // Название агентства пользователя
			$_SESSION['ucaption'] = $result['ucaption']; // Название агентства пользователя
			$_SESSION['adm_caption'] = $result['adm_caption']; // администратор агентства название
			$_SESSION['sh_password'] = $password; // Заносим в сессию  пароль
			$_SESSION['sh_login'] = $login; // Заносим в сессию  логин
			$_SESSION['sh_id'] = $result['id']; // Заносим в сессию  id
			$_SESSION['sh_name'] = $result['name']; // Заносим в сессию  id
			$_SESSION['agency_adm_id'] = $result['agency_adm_id']; // Заносим в сессию  id агентства которого админ
			$_SESSION['gl_user_id'] = $result['gl_user_id']; // Заносим в сессию  id глобального пользователя
		}
	}
	
	
	
	function start()
	{
		if( !$this->get_this() ) 
		{
			if( $_POST && $_POST['login'] && $_POST['password'] )
			{
				$us = $this->in( $_POST['login'] , $_POST['password'] );
			}
			if( !$this->get_this() )
			{
				?>
				<div align="center"><h2>Авторизация на сайте:</h2>
				<form action="index.php" method="post">
				Логин: <input type="text" name="login"><br>
				Пароль: <input type="password" name="password"><br>
				<input type="submit" name="submit">
				</form></div>
				<?
			}
		}
		else
		{
			exit;
		}

	}
		
		
}
$users = new users();
 

 $us = $users->get_this();
 
// ВЫХОД
if (isset($_GET['exit'])) 
{ 
	 $us = $users->out();
}
