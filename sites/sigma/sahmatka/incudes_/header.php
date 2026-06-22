<?
 

# ВЫХОД
if (isset($_GET['exit'])) 
{ 
	//add_log('Выполнен выход из системы');
	// если вызвали переменную "exit"
	unset($_SESSION['sh_password']); // Чистим сессию пароля
	unset($_SESSION['sh_login']); // Чистим сессию логина
	unset($_SESSION['agency_id']); // Чистим  
	unset($_SESSION['sh_name']); // Чистим  
	unset($_SESSION['sh_id']); // Чистим  
	unset($_SESSION['adm_caption']); // Чистим  
} 

 

if( $_SESSION['sh_login'] )
{
	
	$login = $_SESSION['sh_login'];
	$password = $_SESSION['sh_password']   ;
	
	$query = mysqli_query($connection, "SELECT users.*,	agency.agency_id as agency_adm_id, agency.caption as adm_caption , user_agency.caption as ucaption  FROM `users` left join agency on agency.admin_user_id = users.id  left join agency as user_agency on user_agency.agency_id = users.agency_id WHERE `login` = '$login' AND `password` = '$password'"); // Формируем переменную с запросом к базе данных с проверкой пользователя
	
	$result = mysqli_fetch_array($query); // Формируем переменную с исполнением запроса к БД 
 
 
	if($result['id'])
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
	else
	{
 
		  add_log('Выполнен выход из системы');
		//unset($_SESSION);
		// если вызвали переменную "exit"
		unset($_SESSION['sh_password']); // Чистим сессию пароля
		unset($_SESSION['sh_login']); // Чистим сессию логина
		unset($_SESSION['agency_id']); // Чистим  
		unset($_SESSION['sh_name']); // Чистим  
		unset($_SESSION['sh_id']); // Чистим  
		unset($_SESSION['adm_caption']); // Чистим  
		unset($_SESSION['gl_user_id']); // Чистим  
		unset($_SESSION['ucaption']); // Чистим  
		unset($_SESSION['agency_adm_id']); // Чистим  
	}
 
}






################## ШАПКА ШАБЛОНА!
include('template/default/header.php');

 



?>

<?php 
### ВХОД В СИСТЕМУ (ОБРАБОТКА ФОРМЫ)
if (isset($_POST['submit'])) // Отлавливаем нажатие кнопки "Отправить"
{
if (empty($_POST['login'])) // Если поле логин пустое
{
echo '<script>alert("Поле логин не заполненно");</script>'; // То выводим сообщение об ошибке
}
elseif (empty($_POST['password'])) // Если поле пароль пустое
{
echo '<script>alert("Поле пароль не заполненно");</script>'; // То выводим сообщение об ошибке
}
else  // Иначе если все поля заполненны
{    
$login = trim($_POST['login']); // Записываем логин в переменную 
$password = trim($_POST['password']); // Записываем пароль в переменную           
$query = mysqli_query($connection, "SELECT users.*,	agency.agency_id as agency_adm_id, agency.caption as adm_caption , user_agency.caption as ucaption  FROM `users` left join agency on agency.admin_user_id = users.id  left join agency as user_agency on user_agency.agency_id = users.agency_id WHERE `login` = '$login' AND `password` = '$password'"); // Формируем переменную с запросом к базе данных с проверкой пользователя
//print "SELECT users.*,	agency.admin_user_id  FROM `users` left join agency on agency.admin_user_id = users.id WHERE `login` = '$login' AND `password` = '$password'";
$result = mysqli_fetch_array($query); // Формируем переменную с исполнением запроса к БД 
//print_r($result);
if (empty($result['id'])) // Если запрос к бд не возвразяет id пользователя
{
	echo '<script>alert("Неверные Логин или Пароль!");</script>'; // Значит такой пользователь не существует или не верен пароль
}
else // Если возвращяем id пользователя, выполняем вход под ним
{
// print_r($result);
$_SESSION['agency_id'] = $result['agency_id']; // Название агентства пользователя
$_SESSION['ucaption'] = $result['ucaption']; // Название агентства пользователя
$_SESSION['adm_caption'] = $result['adm_caption']; // администратор агентства название
$_SESSION['sh_password'] = $password; // Заносим в сессию  пароль
$_SESSION['sh_login'] = $login; // Заносим в сессию  логин
$_SESSION['sh_id'] = $result['id']; // Заносим в сессию  id
$_SESSION['sh_name'] = $result['name']; // Заносим в сессию  id
$_SESSION['agency_adm_id'] = $result['agency_adm_id']; // Заносим в сессию  id агентства которого админ

// print_r($_SESSION);
//echo '<div align="center">Вы успешно вошли в систему: '.$_SESSION['sh_login'].' '; // Выводим сообщение что пользователь авторизирован        
//print '</div>';

$GLOBALS['inlogin'] = 'true';

?>

<?
add_log('Выполнен вход в систему');
}     
}		
} 

 
?>

 

 <script>
 <!-- прелоадер -->
    window.onload = function () {
      document.body.classList.add('loaded_hiding');
      window.setTimeout(function () {
        document.body.classList.add('loaded');
        document.body.classList.remove('loaded_hiding');
      }, 3000);
    }
  </script>
   


<?

// ФОРМА ВХОДА
if( !$_SESSION['sh_login'] )
{
	///sahmatka/template/default/images/logo-2.png
	?>
	
	<style>
	@media only screen and (max-width: 992px) {
		.login-modal-form {
			margin-top: 20px;
		}
	}
	</style>
	
	
	<div class="login-modal">
	<div class="row">
		<div class="col login-modal-col-main">
			<div class="login-modal-main">
				<a href="<?=htmlspecialchars((string)$GLOBALS['config']['client_site_url'], ENT_QUOTES, 'UTF-8')?>" class="login-modal__linkback">Вернуться на сайт</a>
				
				<div style="background: #FFF; padding: 30px;   position:relative; margin:5vh 0 0;">
					<div class="login-modal-form__title" style="font-size: 18px;">Агентствам недвижимости</div>
					<div>Ознакомьтесь с регламентом и списком</div>
					<span style="color: #00CDAD; display: block; margin-top: 10px;">Документов для сотрудничества &nbsp;&nbsp; <img src="https://em.m2profi.pro/rstr.png"></span>
					<img src="https://em.m2profi.pro/voskl.png" style="position:absolute; top: -30px; right: 10px;"/>
				</div>
				<div class="login-modal-form" style="margin: 3vh 0 5vh;">
					<div class="login-modal-form__logo"><img src="https://m2profi.pro/images/logo.svg" alt=""></div>
					<div class="login-modal-form__title">Кабинет риэлтора</div>
					<div class="login-modal-form__subtitle">Авторизация</div>
					<form action="user.php" method="post">
						<input type="text" placeholder="Логин" name="login">
						<input id="password-input2" type="password"  name="password"  placeholder="Пароль">
						<div class="login-modal-form__password">
							<input type="checkbox" id="password-check" class="password-checkbox" style="display:inline-block; width:auto; height:auto;     margin: 5px;">
							<label for="password-check" class="login-modal-form__password-label">Показать пароль</label>
						</div>
						 
						<script>
								$('body').on('click', '.password-checkbox', function(){
 									if ($('#password-input2').attr('type')=='password')
									{
										$('#password-input2').attr('type', 'text');
									 } 
									else 
									{
										$('#password-input2').attr('type', 'password');
									}
								}); 
						</script>
						<input type="hidden" name="submit" value="1" />
						<button class="login-modal-form__btn btn">Войти в
							кабинет<i></i></button>
					</form>
					<div class="login-modal-form__login" style="display:none;">Еще не зарегистрированы в сервисе? <br>
						<a href="https://m2profi.pro">Регистрация</a>
					</div>
				</div>
			</div>
		</div>
		<div class="col login-modal-col-pict">
			<div class="login-modal-pict">
				<div class="login-modal__caption">
					<div class="login-modal__logo"><img src="/sahmatka/template/default/images/logo.svg" alt=""></div>
					<div class="login-modal__title">ДОБРО&nbsp;ПОЖАЛОВАТЬ В&nbsp;СИСТЕМУ&nbsp;ПРОДАЖ</div>
					<div class="login-modal__text">Специалист по работе с партнерами: <br> Евгений Маслов</div>
					<div class="login-modal__contact">
						<div class="login-modal__contact-item">Тел./whatsapp: <a href="https://wa.me/79538697247">+7 953 869 72-47</a></div>
						<div class="login-modal__contact-item">E-Mail: <a
								href="mailto:op-an@em-nsk.group"> info@stalker.group</a></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	</div>
 
	<?
	include('incudes_/foother.php');
	exit;
}
?>
 


<?
################## ШАПКА ШАБЛОНА!
include('template/default/in_head.php');
?>
