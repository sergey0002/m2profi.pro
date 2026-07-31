<?
session_start();
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
  header("Last-Modified: " . gmdate("D, d M Y H:i:s")." GMT");
  header("Cache-Control: no-cache, must-revalidate");
  header("Cache-Control: post-check=0,pre-check=0", false);
  header("Cache-Control: max-age=0", false);
  header("Pragma: no-cache");
 
include('config.php');
 
  
 



if( $_SESSION['sh_login'] )
{
	
	print $login = $_SESSION['sh_login'];
	$password = $_SESSION['sh_password']   ;
	
	  $query = mysqli_query($connection, "SELECT users.*,	agency.agency_id as agency_adm_id, agency.caption as adm_caption , user_agency.caption as ucaption  FROM `users` left join agency on agency.admin_user_id = users.id  left join agency as user_agency on user_agency.agency_id = users.agency_id WHERE `login` = '$login' AND `password` = '$password'"); // Формируем переменную с запросом к базе данных с проверкой пользователя
	
	$result = mysqli_fetch_array($query); // Формируем переменную с исполнением запроса к БД 

 
	if($_SESSION['sh_id'] )
	{
	
		$_SESSION['agency_id'] = $result['agency_id']; // Название агентства пользователя
		$_SESSION['ucaption'] = $result['ucaption']; // Название агентства пользователя
		$_SESSION['adm_caption'] = $result['adm_caption']; // администратор агентства название
		
		$_SESSION['sh_password'] = $password; // Заносим в сессию  пароль
		$_SESSION['sh_login'] = $login; // Заносим в сессию  логин
		$_SESSION['sh_id'] = $result['id']; // Заносим в сессию  id
		$_SESSION['sh_name'] = $result['name']; // Заносим в сессию  id
		$_SESSION['agency_adm_id'] = $result['agency_adm_id']; // Заносим в сессию  id агентства которого админ
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
 		exit;
	}
}
else{ exit; }
 
 
 
if($_GET['xls'])
{
	
	
 //	include('excel/Spreadsheet.php');
 //	include('excel/Writer/Xlsx.php');
	

 

	//$file="zapis.xls";
	//header('Content-Type: text/html');
	//$table = $_POST['tablehidden'];//i get this from another php file.It is HTML table
	//header("Content-type: application/x-msexcel"); //tried adding  charset='utf-8' into header
	//header("Content-Disposition: attachment; filename=$file");
}


else{
?>
<html>
<head>
<!DOCTYPE html>
<html>
  <head>
    <title>Энергомонтаж</title>
	 
    <link rel="icon" href="/templates/jv-framework/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
 

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
	<meta charset="utf-8">
	<link href="https://fonts.googleapis.com/css?family=PT+Sans" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css?family=Exo+2" rel="stylesheet">

	<link href="http://www.em-nsk.ru/fonts/ptsans/ptsans.css" rel="stylesheet">
	<link href="http://www.em-nsk.ru/fonts/exo2/exotwo.css" rel="stylesheet">

<style>
	body{font-family:"Exo 2"; color:#000;}
body {
    font: 90.5%/1.3 normal Helvetica, sans-serif;
    padding-top: 50px;
    margin-top: 50px;
	
	    font: 90.5%/1.3 normal Helvetica, sans-serif;
    padding-top: 50px;
    margin-top: 50px;
	
}

.actfix {
    padding-left: 10px;
    width: 100%;
    display: inline-block;
}
 
.vcardem{margin-top:5px;margin-bottom:5px;}


*,td,body{
-webkit-print-color-adjust: exact;
printer-friendly-colors:avoid;
printer-color-adjust:avoid;
printer-colors:exact;
color-adjust:exact;
conserve-ink:avoid;
expensive-colors:exact;
 
  }
  
  
  td,th{border:solid 1px #000; padding:3px; font-size:12px;}
	</style>

  </head>

<body style="margin-top:0; padding-top:0">
<div style="vertical-align:bottom;">
<h1> Пользователи</h1>
<?
}





 
 
 
$sql="
SELECT users.*, max(users_stat.date) as ac, count(users_stat.date) as ac_c, user_agency.admin_user_id,
CASE
    WHEN admin_user_id = users.id 
        THEN 'Да'
    ELSE 'Нет'
END AS adm,
user_agency.caption as user_agency ,
user_agency.agency_id as agid
FROM `users` 
LEFT JOIN agency as user_agency ON user_agency.agency_id = users.agency_id 
LEFT JOIN users_stat ON users_stat.users_id= users.id  WHERE 1=1 ";
 
$sql.='
Group by users.id
order by agid desc , adm, ac desc';

$query = mysqli_query($connection, $sql); 
 
 
 
 
 ?>
 <div class="table-wrap">			 
		<table border="1">
	<table>
					<thead>
						<tr>
							<th><b>id</b></th>
							<th><a href="#"><b>Доступы</b></a></th>
							<th><a href="#"><b>Имя</b></a></th>
							<th><a href="#"><b>E-Mail</b></a></th>
							<th><a href="#"><b>Телефон</b></a></th>
							<th><a href="#"><b>Агентство</b></a></th>
					 
					 
					 
						 
							<th><a href="#"><b> Последний вход</b></a></th>
						</tr>
					</thead>
		<?	
		while ($result = mysqli_fetch_array($query)) 
		{
			  //$summ_arr['agency'][$result['agency_id']]=$result['user_agency'];
			  //if(!$summ_arr['agency_activ'][$result['agency_id']]){$summ_arr['agency_activ'][$result['agency_id']] =  $result['ac'];}// последняя активност
			  //$summ_arr['agency_activ_c'][$result['agency_id']]=$result['ac_c']; // всего активностей
			  
			 // $summ_arr['agency_us_c'][$result['agency_id']]++; // всего активностей
			   
			  //if($_GET['agency'])	{	if($result['agency_id']!=$_GET['agency']) {continue;} }

			 if($result['adm']!='Да'){continue;}
			 
			 if( stripos($result['password'], '!')   ){continue;}
			print '<tr>   ';
		// '<td class="downs">'.$result['adm'].'</td>'.
			echo ' 
	  		  <td>'.$result['id'].'</td>'.
             '<td onclick="copytext(this)" id="access_'.$result['id'].'">Логин - '.$result['login'].' Пароль - '.$result['password'].'</td>'.
			 '<td>'.$result['name'].'</td>'.
			 '<td>'.$result['e_mail'].'</td>'.
			 '<td>'.$result['phone'].'</td>'.
			 '<td>'.$result['user_agency'].'</td>'.
		 
			 '<td>'.$result['ac'].'</td>'.
             ' 
			 </tr>';
					 
		}
		?>
		</table>
		</div>
 
 
 
 
 
 
 
</div>
</body>
</html>

 