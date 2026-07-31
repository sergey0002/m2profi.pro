<?
  header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
  header("Last-Modified: " . gmdate("D, d M Y H:i:s")." GMT");
  header("Cache-Control: no-cache, must-revalidate");
  header("Cache-Control: post-check=0,pre-check=0", false);
  header("Cache-Control: max-age=0", false);
  header("Pragma: no-cache");
 


	 //ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ERROR );


include('config.php');
 	 
 $sa = new sahmatka( $_SESSION , $connection );
 $homes = $sa->homes_config;
 

 

	include('SendMailSmtpClass11.php');
 	
	 $mailSMTP = new SendMailSmtpClass('energomontaz452@mail.ru', '!uuAyUoyLO3,3', 'ssl://smtp.mail.ru',465,"UTF-8"); // создаем экземпляр класса
	// от кого
	$from = array(
		"EM-NSK", // Имя отправителя
		"energomontaz452@mail.ru" // почта отправителя
	);
	
	
	
	 
	
if( $_SESSION['agency_id'] != "92" && $_SESSION['agency_id']  !="1")
{
 //die('Ошибка - FIle not found, попробуйте зайти на сайт позже.');
}	



?>
<html lang="ru">

	<head>

		<meta charset="utf-8">
		<meta name="robots" content="noindex, nofollow" />
		<meta name="googlebot" content="noindex, nofollow" />
		<meta name="yandex" content="none" />

		<title>M2 Profi</title>

		<meta name="description" content="">

		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">

		<link rel="icon" href="/sahmatka/template/default/images/favicon/favicon.png">
		<link rel="shortcut icon" href="/sahmatka/template/default/images/favicon/favicon.ico" type="image/x-icon">
		<link rel="apple-touch-icon" href="/sahmatka/template/default/images/favicon/apple-touch-icon-57x57.png">
		<link rel="apple-touch-icon" sizes="72x72" href="/sahmatka/template/default/images/favicon/apple-touch-icon-72x72.png">
		<meta property="og:image" content="/sahmatka/template/default/images/home-og.jpg">

		<!-- <link rel="preconnect" href="https://fonts.gstatic.com">
				<link
					href="https://fonts.googleapis.com/css2?family=Inter:wght@100;200;300;400;500;600;700;800;900&family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;0,900;1,100;1,300;1,400;1,500;1,700;1,900&display=swap"
					rel="stylesheet"> -->

		<link rel="stylesheet" href="/sahmatka/template/default/libs/air-datepicker/css/datepicker.min.css">
		<!-- <link rel="stylesheet" href="/sahmatka/template/default/libs/chartjs/chart.min.css"> -->
		<link rel="stylesheet" href="/sahmatka/template/default/libs/formstyler/jquery.formstyler.css">
		
		<style>
		.jq-checkbox{margin-right:0;}
		</style>
		<link rel="stylesheet" href="/sahmatka/template/default/libs/aos/aos.css">
 
		<link rel="stylesheet" href="/sahmatka/template/default/libs/slick/slick.css">

		<link rel="stylesheet" href="/sahmatka/template/default/css/style.css">
		<link rel="stylesheet" href="/sahmatka/template/default/css/media.css">

		<script src="/sahmatka/template/default/libs/jquery-3.3.1/jquery-3.3.1.min.js"></script>
  
 
<link rel="stylesheet" type="text/css" href="fancybox-3.0/dist/jquery.fancybox.min.css">
<script src="fancybox-3.0/dist/jquery.fancybox.min.js"></script>
<script src="/sahmatka/template/default/libs/formstyler/jquery.formstyler.min.js"></script>

 


 
<style>
input,select
{
	border:1px solid #000;
	border-radius:5px;
	padding:4px;
	font-size:16px;
	margin:6px;
	width:95%;
	font-size:14px;
}
	
	
	
	
	
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



</style>

</head>
 

<body style="margin-top:0;  padding 10px;">
<?
 if(!isset($_POST)){$_POST=array();}
 
@$apartments=$_GET['apartments'];
@$apartments_num=$_GET['apartment_num'];
@$apartment_num=$_GET['apartment_num'];
@$home_id = (int) $_GET['home_id'];
 

if($_GET['action'] == 'broni'  )
 {	 
	 if($_POST && ($_SESSION['sh_login'] == 'admin' || $_SESSION['sh_login'] == 'em_nsv'  || $_SESSION['sh_login'] == 'demo_admin') )
	 {
	  // Форма бронирования обработка
	  
	    $bron_id = rand(0,999999);
		$bron_id = $sa->new_broni($_GET['home_id'],$_GET['apartment_num'],$_POST['status'],$_GET['apartments']);
		add_log('Статус квартиры изменен администратором');
	  }
	elseif( ($_POST || $_FILES ) && $_SESSION['sh_login'] != 'admin' &&  $_SESSION['sh_login'] != 'demo_admin' && $_SESSION['sh_login'] != 'em_nsv'  && $_SESSION['sh_id']  )
	{
 
		// Получаем статус квартиры
		$sql = 'SELECT * FROM broni LEFT JOIN users ON broni.user_id = users.id  WHERE home_id="'.$_GET['home_id'].'" AND  section_id="'.$_GET['section_id'].'" AND floor="'.$_GET['floor'].'" AND 


 AND (apartments="'.$apartments.'" OR (apartments_num="'.$apartments_num.'" AND apartments="0") )
		ORDER BY date asc;';
		$query = mysqli_query($connection, $mysql->c); 
		while ($result2 = mysqli_fetch_array($query)) {	$stat = $result2['status'];	}	
 
		if($stat!='' && $stat!='2' && $stat!='5' && $stat!='0' ){print '<h2 style="color:red">Ошибка бронирования квартира уже забронирована другим пользователем</h2>';  $err_m[]='Квартира уже забронирована другим пользователем';}
		else
		{
			 
		if( !$_FILES['passport_scan']['type'] || !$_FILES['passport_scan2']['type'] || !$_FILES['anket']['type'] )
		{
			?><h2 style="color:red">Для бронирования необходимо загрузить указанные файлы</h2><?
		}
		else
		{
			$bron_id = $sa->new_broni($_GET['home_id'],$_GET['apartment_num'],4,$_GET['apartments']);
			add_log('Забронированно помещение ');

			$dir = "uploads/$bron_id/";
			mkdir($dir, 0777);
		 
		 
		 if($_FILES['passport_scan']['type'])
		 {
			$ext =  substr(strrchr(basename($_FILES['passport_scan']['name']), '.'), 1);
			
			$uploadfile = $dir . basename('passport_scan'.'.'.$ext);
			$files[0] = 	$uploadfile; // для письма
			 
			if (move_uploaded_file($_FILES['passport_scan']['tmp_name'], $uploadfile))
			{
				echo "Скан пспорта 1 - Файл был успешно загружен.\n <br>";
			} 
			else 
			{
				echo "Ошибка!\n";
				$err_m[]='Скан паспорта не был загружен';
			}
		  }
		  
		 if($_FILES['passport_scan2']['type'])
		 {
			$ext =  substr(strrchr(basename($_FILES['passport_scan2']['name']), '.'), 1);
			
			$uploadfile = $dir . basename('passport_scan2'.'.'.$ext);
			$files[1] = 	$uploadfile; // для письма
			if (move_uploaded_file($_FILES['passport_scan2']['tmp_name'], $uploadfile))
			{
				echo "Скан пспорта 2 - Файл был успешно загружен.\n<br>";
			} 
			else 
			{
				echo "Ошибка!\n";
				$err_m[]='Скан паспорта 2 не был загружен';
			}
		  }



		  
		 if($_FILES['anket']['type'])
		 {
			$ext =  substr(strrchr(basename($_FILES['anket']['name']), '.'), 1);
			$uploadfile = $dir . basename('anket'.'.'.$ext);
			 $files[2] = 	$uploadfile; // для письма
			if (move_uploaded_file($_FILES['anket']['tmp_name'], $uploadfile))
			{
				echo "Анкета- Файл был успешно загружен.\n<br>";
			} 
			else 
			{
				echo "Ошибка!\n";
				$err_m[]='Анкета-  не был загружен';
			}
		  }
		  
		  // Успешная бронь!
		  if($_POST && !$err_m)
		  {
		  ?>
		  <h2 style="color:#000; text-align:center;">Квартира забронирована</h2>
		  <p style="color:#00CDAD; font-weight:bold;; font-size:20px; text-align:center;">Срок действия брони - 1 календарный день, по прошествии 1 дня бронь будет анулирована автоматически</h2>
		  <hr/>
		  <?
		  }
		# формирвем сообщение на почту о бронировании квартиры
		// вложения файлов
		$message = "Бронирование квартиры \r\n <br/>";
		$message .= "Заявка поступила от пользователя - <b>".$_SESSION['sh_name'].'</b> Представителя агентства - <b>'.$_SESSION['ucaption']."</b>\r\n </b><br/> ";
		$message .= "Дом <b>".$homes[$home_id]['caption']."</b> секция-<b>".$section_id."</b> Этаж-<b>". $floor."</b> кв-<b>".$apartments_num."</b> ";
			
		// XMail('89236470002@mail.ru', 'Бронирование квартиры '.$homes[$home_id]['caption']. '/сек-'.$section_id.'/этаж-'.$floor.'/кв'.$_GET['num'], $message, $files);
		# XMail( 'site@em-nsk.ru', 'em-opd@mail.ru', 'Бронирование квартиры '.$homes[$home_id]['caption']. '/сек-'.$section_id.'/этаж-'.$floor.'/кв'.$_GET['num'], $message, $files);

 

	// кому отправка. Можно указывать несколько получателей через запятую
	$to = 'op@em-nsk.group,   89236470002@mail.ru';
	//$to = '89236470002@mail.ru';
	// добавляем файлы
	$mailSMTP->addFile($files[0]);
	$mailSMTP->addFile($files[1]);
	$mailSMTP->addFile($files[2]);
	
	// отправляем письмо
	############## $result =  $mailSMTP->send($to,  'Бронирование квартиры '.$homes[$home_id]['caption']. '/сек-'.$section_id.'/этаж-'.$floor.'/кв '.$apartments_num, $message, $from); 
	//if($result === true){	echo "Done";	}
	//else{echo "Error: " . $result;	}



		  
	}//проверка формы
		
		
	}}
 }
 
 
 

 

/*
$home_id=5;
$apartment_num = 64;
@$apartments = 4;
*/

	$sql = 'SELECT * FROM `apartaments` WHERE home_id="'.$home_id.'" AND apartment_num="'.$apartment_num.'" ';
	 
	$result = $mysql->get_arr($sql,1);
	
	$result['price'] =  number_format($result['price'], 0, '.', ' ');
	$result['price'].=' руб';
	
	if(  1==1  )
	{
		
?>



         <div class="container-fluid">
            <div class="row">
               <div class="col-md-12 col-xs-12" style="text-align:center;">
                  <h1 style="font-size:34px; text-align:center;"><b><?=$homes[$home_id]['caption'];?></b></h1>   
               </div>
			   <div class="col-md-12 col-xs-12" style="text-align:center; font-size:22px;">
				
				 
					Секция - <span style="color:#00CDAD; font-weight:bold;"><?=$result['section_id'];?>
					 
				
				
				</span> Этаж - <span style="color:#00CDAD; font-weight:bold;"><?=$result['floor'];?></span> Квартира - <span style="color:#00CDAD; font-weight:bold;"><?=$result['apartment_num'];?></span>
			   <hr/>
			   </div>
			   
			</div>
			
			 <div class="row">
			 
				<div class="col-md-6 col-xs-12" style="text-align:center;">
                  <img src="<?=$result['image_pb'];?>?x=178" style="max-height:400px; max-width:100%"  >
                </div>
			    <div class="col-md-6 col-xs-12" style="text-align:left; font-size:24px;">
				 
				Количество комнат - <b><?=$result['rooms']?></b><br/>
				<?
				if($result['text'])
				{
					?>
					Примечание - <b><?=$result['text']?></b><br/>
					<?
				}
				?>
				
				Площадь - <b><?=$result['area']?></b> м<sup>2</sup>
			 			<br/><br/>
						
						
				

			
					
					
	<?			//apartments_num="'.$apartments.'"
	// Получаем статус квартиры
	  $sql = 'SELECT * FROM broni LEFT JOIN users ON broni.user_id = users.id  WHERE home_id="'.$home_id.'" AND  section_id="'.$result['section_id'].'" AND floor="'.$result['floor'].'" 
	  AND (apartments="'.$apartments.'" OR (apartments_num="'.$apartments_num.'" AND apartments="0") ) ORDER BY date desc;';
	$result2 = $mysql->get_arr($sql,1);
	$stat = $result2['status'];
  
  
  
	 # НЕ у всех квартир тут есть тстатусы почемуто бля! нет броней потому что 
	 // print_r($result2);
	?>
	
	
	<?
	if(!$result2['status'] || $result2['status'] == 2)
	{
		?>	Цена - <b><?=$result['price'];?></b> 	<br/><br/>	<?
	}
	?>
						
	
	 
	<?
	if( ($_SESSION['sh_login'] == 'admin'  || $_SESSION['sh_login'] == 'em_nsv' || $_SESSION['sh_login'] == 'demo_admin')   )
	{
	?>
	<form action="iframe_apart.php?home_id=<?=$home_id?>&section_id=<?=$result['section_id']?>&floor=<?=$result['floor']?>&apartment_num=<?=$apartment_num;?>&apartments=<?=$apartments;?>&action=broni" method="post" enctype="multipart/form-data" method="post">
		Статус -   
		<select name="status">
		
		<?
		if( $_SESSION['sh_login'] == 'em_nsv')
		{
		?>
		  <option value="4" <? if(4==$stat){ ?> selected="selected" <? }?>>Забронирована</option>
 		  <option value="6" <? if(6==$stat){ ?> selected="selected" <? }?>>Квартира подрядчика</option> 
		  <option value="5" <? if(5==$stat){ ?> selected="selected" <? }?>>Забронирована застройщиком</option>
		  <?
		}
		else
		{
			?>
			  <option value="0" <? if(0==$stat){ ?> selected="selected" <? }?>>Не задан</option>
			  <option value="2" <? if(2==$stat){ ?> selected="selected" <? }?>>Свободна</option>
			  <option value="4" <? if(4==$stat){ ?> selected="selected" <? }?>>Забронирована</option>
			  <option value="3" <? if(3==$stat){ ?> selected="selected" <? }?>>Продана</option>
			  <option value="5" <? if(5==$stat){ ?> selected="selected" <? }?>>Забронирована застройщиком</option>
			  <option value="6" <? if(6==$stat){ ?> selected="selected" <? }?>>Квартира подрядчика</option>
			<?
		}
		  ?>
		  
		</select>
		<br><br>
        <input type="submit" value="Сохранить" style="background-color:#00CDAD;  color:#FFF; padding:10px;  padding-right:40px; padding-left:40px; font-size:16px; font-weight:bold;   border-radius:7px;  ">
    </form>
	<?
	}
	elseif($_SESSION['sh_login'] != 'admin' &&  $_SESSION['sh_login'] != 'demo_admin' && $_SESSION['sh_login'] !='em_nsv'  ) // Админ агентства или агент
	{
		?>
		<div style="font-size:14px;">
	

<? 

$_GET['home_id'] = trim( $_GET['home_id'] );

if( $stat=="2" || !$stat   ) 
{
	  print '';
	//print '<h1>!!!'.$stat.'</h1>';
	?>
	<form action="iframe_apart.php?home_id=<?=$home_id?>&section_id=<?=$result['section_id']?>&floor=<?=$result['floor']?>&apartment_num=<?=$apartment_num;?>&apartments=<?=$_GET['apartments'];?>&action=broni" method="post" enctype="multipart/form-data" method="post">
			<h2 style="font-size:16px;">Данные покупателя</h2>
			Скан паспорта страницы с фото: <input type="file" name="passport_scan" accept="image/*;capture=camera"  ></br/><br/>
			Скан паспорта страницы с пропиской: <input type="file" name="passport_scan2" accept="image/*;capture=camera" ><br/>
			
			 
			Форма №2 бронь: <input type="file" name="anket" accept="image/*;capture=camera"><br/> 
	<br/>
<b style="font-size:20px; color:#ff0000;">Дни приема актов: понедельник, вторник, четверг с 9.30 до 14.00</b><br/>
<br/>


<input type="checkbox" id="checkbox" name="checkbox" style="width:auto;" onchange="document.getElementById('submit').disabled = !this.checked;">
<span style="font-size:20px;">Подтверждаю согласие с <a target="_blanc"  style="font-size:20px;" href="http://em-nsk.ru/sahmatka/reglament.php">регламентом </a></span><br/><br/>



			<input type="submit" id="submit" disabled="disabled" value="ЗАБРОНИРОВАТЬ"   class="stat-top-btn btn btn_arrow-long" style="  margin-left:0;" />
		</form>
		
		<?
}
		?>
		
		
		
		</div>
		<?
	}
	?>

			    </div>
                  
				  
			   
			   
            </div>
         </div>
<?
	}
 
?>

</body>
</html>

 