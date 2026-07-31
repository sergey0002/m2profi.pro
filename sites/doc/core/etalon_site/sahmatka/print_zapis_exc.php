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
	
	$login = $_SESSION['sh_login'];
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
<h1> Запись на экскурсии</h1>
<?
}





 
$objects[1]='Приозерный';
$objects[2]='Родники';
$objects[3]='ЖК Залесский';
$objects[4]='Cерия GREEN';
 
$objects[44]='Cерия GREEN (кирп вставка)';
	
$objects[45]='704 - 6 секция';
$objects[46]='Шоу-рум';
 
$q = ' (SELECT * FROM `excurs` where del=0 AND 1=1 ';
 
if( !$_GET['arhiv'] ){ $q.=' AND `excurs`.`date` > CURDATE() '; }
else{  $q.=' AND `excurs`.`date` < CURDATE() '; }

$q.=' order by  `time` desc)   ORDER BY `date` DESC,`time` DESC LIMIT 0,5000; '; 
 
$query = mysqli_query($connection,$q);
ob_start();
		 ?>
		<div class="table-wrap">	 
		<table>
		<thead>
		<tr>
			<th>id</th>	 
			<th>Дата экскурсии</th> 
			<th>Время экскурсии</th>
			<th style="max-width:200px;">ФИО</th>
			<th style="max-width:200px;">Сообщение</th>
			<th>Телефон</th>
		 
			<th>Объект</th>
			<th>Дата записи</th>
			 
		</tr>
		</thead>
		<?
 
			
		while ($result = mysqli_fetch_array($query)) 
		{
		  $dates[$result['date']]++;
		  $objectsx[$result['home']]++;
		  
		  
		  
		  if($_GET['date'] && $_GET['date'] != $result['date'] ){continue;}
		  if($_GET['home'] && $_GET['home'] != $result['home'] ){continue;}
		  
		  
		 $dates[$result['date']]++;
		 if($_GET[date] && $_GET[date] != $result['date'] ){continue; }
		
			echo     '<tr>
					  <td>'.$result['zapis_id'].'</td>'.
					 '<td>'.$result['date'].'</td>'.
					  '<td>'.$result['time'].'</td>'.
   '<td style="max-width:200px;">'.$result['name'].'</td>'.
					 '<td style="max-width:200px;">'.$result['message'].'</td>'.
					 '<td>'.$result['phone'].'</td>' .
				
'<td>'.$objects[$result['home']].'</td>'  .
'<td>'.$result['datetime'].'</td>'   ;
   
			
			 
		}
		?>
		</table>
		 
		</div>
		<?
		print $content = ob_get_clean();
		?>
	 
 
 
 
 
 
 
</div>
</body>
</html>

 