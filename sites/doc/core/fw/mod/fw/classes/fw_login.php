<?
class fw_login__
{
	var $admin_login = 'admin';
	var $admin_password='zdctvjue';
	
	function logout()
	{
		foreach($_SESSION as $k=>$v)
		{
			unset($_SESSION[$k]); // Чистим сессию пароля
		}
		return true;
	}
	
	function get_arr($login='',$password='')
	{
		# $query = mysqli_query($connection, "SELECT users.*,	agency.agency_id as agency_adm_id, agency.caption as adm_caption , user_agency.caption as ucaption  FROM `users` left join agency on agency.admin_user_id = users.id  left join agency as user_agency on user_agency.agency_id = users.agency_id WHERE `login` = '$login' AND `password` = '$password'"); // Формируем переменную с запросом к базе данных с проверкой пользователя
		# $result = mysqli_fetch_array($query); // Формируем переменную с исполнением запроса к БД 
		return $result;
	}
		
	function login($login='',$password='')
	{
		t('login '.$login.'/'.$password);
		if(!$login || !$password)
		{
			$login = $_SESSION['fw_user_login'];
			$password = $_SESSION['fw_user_password'];
		}
		t('login sesion '.$login.'/'.$password);
		
		// Получаем данные из базы
		$result = $this->get_arr($login,$password);
		
		# СУПЕРАДМИНСКИЕ ЛОГИН И ПАРОЛЬ 
		if($login == $this->admin_login && $password == $this->admin_password )
		{
			$_SESSION['fw_user_id'] = 1; // Название агентства пользователя
			$_SESSION['fw_user_login'] = $this->admin_login; // Название агентства пользователя
			$_SESSION['fw_user_password'] = $this->admin_password; // администратор агентства название
			$_SESSION['group_name'] = 'admin';
			
			t('Выполнен вход в систему суперадмина');
			return true;
		}
		elseif(empty($result['user_id'])) // Если запрос к бд не возвразяет id пользователя
		{
			$this->messages.='<script>alert("Неверные Логин или Пароль");</script>'; // Значит такой пользователь не существует или не верен пароль
			return false;
		}
		elseif($result['user_id'])// Если возвращяем id пользователя, выполняем вход под ним
		{
			foreach($result as $k=>$v)
			{
				$_SESSION[$k] = $v;
			}
			
			$_SESSION['fw_user_id'] = $result['user_id']; // Название агентства пользователя
			$_SESSION['fw_user_login'] = $result['login']; // Название агентства пользователя
			$_SESSION['fw_user_password'] = $result['password']; // администратор агентства название

			t('Выполнен вход в систему');
			return true;
		}     
	}
}