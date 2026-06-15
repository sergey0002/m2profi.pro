<?
$SERVER_NAME=$_SERVER['HTTP_HOST'];
$SERVER_NAME=preg_replace('/^http:\/\//', '', $SERVER_NAME);
$SERVER_NAME=preg_replace('/^www\./', '', $SERVER_NAME);
define("CookiePath","/");
define("CookieDomain",$SERVER_NAME);    //".".$SERVER_NAME    домен
define("live_sess_time","1000"); 
 
ini_set('session.auto_start', '0'); // автостарт сессий не нужен
ini_set('session.use_cookies', '1');// передавать идентификатор сессии в куках
ini_set('session.use_trans_sid', '1'); // не передавать идентификатор сессии добавляя его к URL и формам
ini_set('session.name', 'FW_SID'); // Имя сессии
ini_set('session.gc_maxlifetime', '1800'); // время жизни сессии, 30 минут (60*30)
//ini_set ('session.cookie_lifetime', '2000'); // 0 - кука умирает при закрытии браузера

// Задаем параметры сессионной куки: (время жизни= 0 - умрет при закрытии браузера, путь, домен, true= доступно только из https зоны)
session_set_cookie_params (0, CookiePath, CookieDomain, false);

//Выставляем вероятность запуска функции fw_session_clean в процентах (допустимые значения 1-100, по умолчанию равно 1%)
ini_set('session.gc_probability', 10);

session_set_save_handler('fw_session_open','fw_session_close','fw_session_read','fw_session_write','fw_session_destroy','fw_session_clean');
 
 
########## Подключение БД ПОЛЬЗОВАТЕЛЕЙ И СЕССИЙ (может отличаться от основной базы данных)
$mysqli = new mysqli( $GLOBALS['config']['server'] , $GLOBALS['config']['mysql_login'] , $GLOBALS['config']['mysql_password'] , $GLOBALS['config']['mysql_base']) or die(mysqli_error());

if ($mysqli->connect_error) {
    die('Ошибка подключения (' . $mysqli->connect_errno . ') '
     . $mysqli->connect_error);
}



function fw_session_open() 
{
	GLOBAL $mysqli;
	if(!$mysqli)
	{
		die(mysqli_error($mysqli));
	}
	return true;
}

function fw_session_close() 
{	
	GLOBAL $mysqli;
	return true;
}


function fw_session_read($id) 
{
    GLOBAL $mysqli;
	
	if (strlen ($id) != 32) {
        error_log ("_read(): Invalid SessionID = ".$session_id);
        return false;
    }	
    $stmt = $mysqli->prepare(" SELECT data FROM fw_sessions WHERE  id = ? ");
	if(!$stmt)
	{
		die(mysqli_error($mysqli));
	}
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $num = $result->num_rows;

    if ($num>0) {
        $record = $result->fetch_assoc();
        return $record['data'];
    }
    return "";
}



function fw_session_write($id, $data)
{
    GLOBAL $mysqli;

	$id = $mysqli->real_escape_string($id);
	$data = $mysqli->real_escape_string($data);

	// Получаем предидущую запись сессии перед обновлением
	$result_query = $mysqli->query(' SELECT * FROM fw_sessions WHERE  id = "'.$id.'" ');
	if ($result_query === false) 
	{
		die(mysqli_error($mysqli));
	}
	$record = $result_query->fetch_assoc();
	  
    $access = time();
	$ip = $_SERVER['REMOTE_ADDR']; //ip адрес
	$ua = $_SERVER['HTTP_USER_AGENT']; // юзер агент
	
	# print_R( $record );
 	
	// Счетчик просмотров обновлений сессии
	if( isset($record['session_counter']) ){$session_counter = $record['session_counter']+1; }
	else{$session_counter = 0;}
	
 
	// Время создания сессии
	if( isset($record['session_created']) ){ $session_created = $record['session_created']; }
	else{ $session_created = time(); }
	
	
	if( isset($_SESSION['uid'])  )
	{
		$session_user_id = (int) $_SESSION['uid'];
	}
	else
	{
		$session_user_id = 0;
	}
	// Экранируем данные
	 
	$ip = $mysqli->real_escape_string($ip);
	$ua = $mysqli->real_escape_string($ua);
	
	$q = 'REPLACE INTO
	fw_sessions
	SET
	id = "'.$id.'",
	access = "'.$access.'",
	data = "'.$data.'",
	ip = "'.$ip.'",
	ua = "'.$ua.'",
	session_user_id = "'.$session_user_id.'",
	session_counter = "'.$session_counter.'",
	session_created = "'.$session_created.'" ';
 
	$result_query = $mysqli->query($q);
	if ($result_query === false) {
		die(mysqli_error($mysqli));
	}
	 
	return true;
}




function fw_session_destroy($id) {
    GLOBAL $mysqli;

    $stmt = $mysqli->prepare("DELETE FROM fw_sessions WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
	return true;
}

// Сборщик мусора (старых сессий раз в 30 минут )
function fw_session_clean($max) {
    GLOBAL $mysqli;

    $old = time() - $max;

    $stmt = $mysqli->prepare("DELETE FROM fw_sessions WHERE access < ?");
    $stmt->bind_param('s', $old);
    $stmt->execute();
	return true;
}


 
 
 
 
 
# Класс для работы с пользователями
class fw_user
{
	public $log;
	function __construct()
	{
		GLOBAL $mysqli;
		if(!$mysqli)
		{
			die('Sessions mysql error');
		}
		$this->table='users';
		//user_id	login	user_group_id	agency_id	corpus_id	password	name	f_name	o_name	hotel_id	e_mail	phone	addtime	status	del
		$this->login_filed = 'login';
		$this->password_filed = 'password';
		$this->status_filed = 'status'; // 
		$this->uid_filed = 'id'; //  
 
		$this->log[] = 'Поля заданы, подключение к mysql выполнено';
	}
	
	
	function auth($login='',$pass='')
	{
		if(!$login){$login = $_SESSION[$this->login_filed]; }
		if(!$pass){$pass = $_SESSION[$this->password_filed]; }
		 
		GLOBAL $mysqli;
		
		$login = trim($login);
		$pass = trim($pass);
		
		$login = mb_strtolower($login); // Логин к нижнему регистру
		
		$pass = $this->hash_password($pass);
		$login = $mysqli->real_escape_string($login);
		$pass = $mysqli->real_escape_string($pass);


		if(!$login || !$pass)
		{
			$this->log[]='Пустой логин либо пароль  '.$login.' | '.$pass.' ';
			return false;
		}
		
		// Получаем инфу о юзерер из базы
		$record = $this -> other_sdata($login,$pass);
		
		// Не корректные данные
		if( !is_array($record)  )
		{
			$this->log[] = 'auth не найдено записи с логином и паролем в базе '.$login.' | '.$pass.' - выход';
			$this->out();
			return false;
		}
		else // вход пользователя
		{
			// ДОп проверка 
			if ($record[ $this->login_filed ]!=$login || $record[$this->password_filed]!==$pass ) 
			{
				$this->log[] = 'auth не пройдена доп проверка - выход';
				$this->out();
				return false;
			}
			else
			{	
				$this->log[] = 'auth выполнен вход!';
				$_SESSION[$this->login_filed] = $record[$this->login_filed]; // 
				$_SESSION[$this->password_filed] =  $record[$this->password_filed]; //
				$_SESSION[$this->status_filed] =  $record[$this->status_filed]; // 
				$_SESSION['uid'] =  $record[$this->uid_filed]; // 
				
				
				return $record;
			}
		}
	}
	
	// Для наследования альтернативная запись в сессию
	function other_sdata($login,$pass)
	{
		// Получаем предидущую запись сессии перед обновлением
		$q = ' SELECT * FROM '.$this->table.' WHERE  '.$this->login_filed.' = "'.$login.'" AND '.$this->password_filed.' = "'.$pass.'" ';
		$this->log[]=$q;
		$result_query = $mysqli->query($q);
		
		if ($result_query === false) 
		{
			$this->log[] = 'auth ошибка mysql - выход';
			die(mysqli_error($mysqli));
		}
		$record = $result_query->fetch_assoc();
		return $record;
	}
	
	
	
	 
	// хеширование пароля 
	function hash_password($password)
	{
		return $password;
	}
	
	
	function out()
	{
		unset($_SESSION[$this->password_filed]);
		unset($_SESSION[$this->status_filed]);
		// session_destroy();
		// session_start();		 
	}
	
}