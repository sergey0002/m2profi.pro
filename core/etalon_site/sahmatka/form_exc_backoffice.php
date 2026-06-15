<?
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ERROR );
 
include('config.php');


	if( $_SESSION['agency_id'] == "92" || $_SESSION['sh_login'] =='admin' ) //  
	{
		?>
		 
		<?	
	}
	else
	{
		die('Ошибка: не авторизированный пользователь');
	}
	//print_r($_SESSION);
	
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
select *
{
	 
    background-color:#FFF;
    color: #000;
	line-height:2em;
}




select option:checked , select option:hover{
	  background-color:#E91D28;
	  color: #FFF;
}


 

option:hover {
      background-color:#E91D28;
  color: #FFF;
}






select {
 height: auto;
    text-transform: none;
 
}

select option{
  padding:10px;
}

		input,select
{
	border:1px solid #000;
 
	padding:8px;
	font-size:18px;
	margin:6px;
	width:95%;
	font-size:14px;

}
		body{margin-top:20px;}
</style>

 




		
 <script>
 <!-- прелоадер -->
 
  
    window.onload = function () {
      document.body.classList.add('loaded_hiding');
      window.setTimeout(function () {
        document.body.classList.add('loaded');
        document.body.classList.remove('loaded_hiding');
      }, 2000);
    }
	 
  </script>
  
  
  
  
    <style>
 
        .preloader {
			opacity: .9;
      /*фиксированное позиционирование*/
      position: fixed;
      /* координаты положения */
      left: 0;
      top: 0;
      right: 0;
      bottom: 0;
      /* фоновый цвет элемента */
      background: #fbfbfb;
      /* размещаем блок над всеми элементами на странице (это значение должно быть больше, чем у любого другого позиционированного элемента на странице) */
      z-index: 1001;
    }
    .preloader__row {
       position: relative;
       top: 50%;
       width:100%;
      
       text-align: center;
	   max-width:100%;
    }
     
      
	 .preloader__item {
  position: absolute;
  display: inline-block;
  top: 0;
  background-color: #337ab7;
  border-radius: 100%;
  width: 35px;
  height: 35px;
  animation: preloader-bounce 2s infinite ease-in-out;
}

.preloader__item:last-child {
  top: auto;
  bottom: 0;
  animation-delay: -1s;
}

@keyframes preloader-rotate {
  100% {
    transform: rotate(360deg);
  }
}

@keyframes preloader-bounce {

  0%,
  100% {
    transform: scale(0);
  }

  50% {
    transform: scale(1);
  }
}


.loaded_hiding .preloader {
  transition: 0.3s opacity;
  opacity: 0;
}


    .loaded .preloader {
      display: none;
    } 
	
	
	
	.preloader {
   /*   display: none; */
    } 
	
	a:hover {
    color: #000;
}

 
</style>
		<!-- Прелоадер -->
  <div class="preloader">
    <div class="preloader__row">
      <center>
	  <table   style="display:inline-block; width:100px; max-width:100%; ">
	  <tr>
	  <td align="center"><img src="loader000.gif" style="max-width:100%; width:100%;" /> </td> 
	  </tr>
	  </table>
	  </center>
    </div>
  </div>



	</head>
	<body>










<?
if(!$_POST)
{
	
	

	
	/*
	
	<option value="3">ЖК Залесский</option>
	<option value="44">Cерия GREEN (601 кирпичная секция)</option>
	
	*/
?> 
<center> 

<div style="display:inline-block; max-width:98%; font-size:16px; text-align:centr;">



<style>
#excform input[disabled] {
    opacity: .6;
    cursor: not-allowed;
}


.btn {
    position: relative;
    display: -webkit-inline-box;
    display: -ms-inline-flexbox;
    display: inline-flex;
    -webkit-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    -webkit-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    height: 48px;
    padding: 0 20px 0 15px;
    font-size: 16px;
    font-weight: 500;
 
    background: #FFF;
    color: #FF4A3D;
	border:solid 2px #FF4A3D;
	border-radius:0;
}

.btn:hover {
	background: #FF4A3D;
    color: #FFF;
}
	
</style>
<form method="post" id="excform">
			
 <h1>Запись на экскурсии</h1>
<div style="background-color:#FFF;  text-align:center;  padding:10px;  ">
	<center>
	 
	<input type="text" style="padding:8px;  border:solid 1px;      font-size:18px; display:inline-block; margin:10px; " name="name" id="name" placeholder="Ваше имя">  
		<input type="text" style="padding:8px;   border:solid 1px;     font-size:18px; display:inline-block; margin:10px;" name="phone" id="phone"  placeholder="Ваш телефон">
	<input type="text" style="padding:8px;   border:solid 1px;      font-size:18px; display:inline-block; margin:10px;" name="message" id="message"  placeholder="Комментарии к записи">

<br/>
 
<br/>

<select id="home" name="home" style="max-width:100%;">
	<option value="">Выберите экскурсию</option>
 	<option value="46">Красный проспект, 327/3 (Шоу-рум)</option>
	<option value="1">Каспийская, 5 (готовые квартиры с ремонтом)</option>
	<option value="38">811</option>
	<option value="200" style="display:none;">Автобусный тур выходного дня</option>
</select>
 
<select id="data" name="data" style="max-width:100%;">
	<option value="">Выберите дату</option>
</select>
	
<select id="time" name="time" style="max-width:100%;">
<option value="">Выберите время</option>
</select>	 



<select id="peoples" name="peoples" style="max-width:100%;">
<option value="">Количество человек</option>
<option value="1">1</option>
<option value="2">2</option>
</select>	


<br/><br/> 

 <div style="text-align:center; color:red; font-size16px;  ;">
			   ОБРАЩАЕМ ВНИМАНИЕ:<br/>
			   </div>
			   <div style="font-size: 14px;">
			   1. Въезд на территорию стройки на личном транспорте запрещен<br/>
			   2. Посещение строящихся объектов с детьми запрещено<br/>
			   3. Запрещено посещать экскурсию с признаками ОРВИ<br/>
			   4. При себе необходимо иметь маску<br/>
			   
			   </div>
			   <br>
			  
<br/> 
	    <input style="display: inline; width: auto;" id="checkbox" type="checkbox" name="checkbox" onchange="document.getElementById('submit').disabled = !this.checked;" />
			  <label for="checkbox" style="display: inline; font-weight:bold;"> Подтверждаю согласие с правилами проведения экскурсий</label><br/>
		<input type="submit" id="submit" value="Отправить заявку" disabled="disabled" class="btn" style="font-size: 16px;  " onClick= "ga('send', 'event', 'submit', 'exc_button'); yaCounter18713149.reachGoal('exc_button'); return true;">
		
		
		<br><br>
	</center>
</div>
  
					
			   </form>
			  
		</div>
		
		

		</center>	   

 
        <script type="text/javascript">
$( document ).ready(function() {

$.ajaxSetup({cache: false}); 
//$("#time").prop('disabled', 'disabled');
 
 
 
 
		$("#home").change(function()
		{
			if( $("#home").val()   )
			{
				 $("#data").load("ajax_form_exc.php", { load:"date", home: $("#home option:selected").val() });
				 $("#data").removeAttr("disabled");
			}
			else
			{
				$("#data").prop('disabled', 'disabled');
				$('#data').find('option').remove();  
				$('#data').append('<option value="">Дата</option>');
				
				
				
			}
				 
				$("#time").prop('disabled', 'disabled');
				$('#time').find('option').remove();  
				$('#time').append('<option value="">Время</option>');
        });
		
		


	 $("#data").change(function(){
		 
		 
		if( $("#data").val()   )
		{
 			 $("#time").load("ajax_form_exc.php", { load:"time", data: $("#data option:selected").val() , home: $("#home option:selected").val() });
			 $("#time").removeAttr("disabled");
		}
	   	else
		{
			$("#time").prop('disabled', 'disabled');
			$('#time').find('option').remove();  
			$('#time').append('<option value="">Время</option>');
		}
			 
			 // $("#time").prop('disabled', 'disabled');
			
        });


 



 
 
		
		 $("#excform").submit(function (e) {
			 
		 if( !$("#data").val() || !$("#time").val() || !$("#name").val() || !$("#phone").val() )
			 {
				 alert('Необходимо заполнить все поля формы!');
				return false;
			 }
 
        //$(this).submit();
    });
	
	
	    
});
        </script>



	<?
}
else
{
	
	
	$mess='';
 
 
	if(!$_POST['data'] || !$_POST['phone']  || !$_POST['time']  || !$_POST['name'] ){   print 'Необходимо заполнить все поля!';}
	else
	{
		
		
	 
		
		
	######################## КОличество записей пробиваем	
	$d_sql = date("Y-m-d",strtotime($_POST[data]));  
	$q1="SELECT count(*) as c FROM  `excurs` where home ='". $_POST['home']."' AND date = '".$d_sql."' AND time = '".$_POST[time]."' AND del=\"0\" ";
	$row = array();
	$row = $mysql->get_arr($q1,1);
	
	
	if($row) // Есть запись
	{
		$count_zapis_time = $row[c];
		// print_R($row);
	}
		 
		 
		 
	### Валидотор после 16ч	
	$h = date('H', time()); // текущий час!
	$date_t = date('d.m.Y',time()); // Текущая дата
 
	$tom_date_ = new DateTime('+1 days'); 
	$tom_date = $tom_date_->format('d.m.Y');// Завтрашняя дата 
	
	
	
	// меньше трех записей в базе на это время
	if($count_zapis_time>=5 && $_POST['home']!=200 )
	{
		//print_r($count_zapis_time);
		$query='';
		print '<div style="padding:30px; font-size:18px; ">Произошла ошибка - дата и время которые вы выбрали уже занято<br/> попробуйте <a href="#" onclick="window.history.back()">записаться на другое время</a></a>';
	}
	elseif( $h>=16 && (  $_POST['date']==$date_t ||  $_POST['date']==$tom_date ) )
	{
		print 'Произошла ошибка - дата и время которые вы выбрали уже занято<br/> попробуйте <a href="#" onclick="window.history.back()">записаться на другое время</a> <br/>(После 16 часов не возможна запись на следующие сутки)';
	}
	
	
	else // Меньше 2х записей
	{
		
	#############
    $mess.= 'Имя  - <b>'.  $_POST[name].'</b><br/>';
	$mess.= 'Контактный телефон  - <b>'.  $_POST[phone].'</b><br/>';
	$mess.= 'Дата  - <b>'.  $_POST[data].'</b><br/>';
	$mess.= 'Время  - <b>'.  $_POST[time].'</b><br/>';
	$mess.= 'Сообщение  - <b>'.  $_POST[message].'</b><br/>';
	$mess.= 'Человек  - <b>'.  $_POST[peoples].'</b><br/>';


$t_x = strtotime($_POST['data']);
$day_x = date("N",$t_x);


if($_POST['home']==2)
{
		$mess.= '<b>Ждем вас   по адресу: Красный проспект 314, 1 подъезд, вход со двора </b><br/>';
		//Красный проспект 314, 1 подъезд, вход со двора //ул.Тюленина д.26., 1 этаж (отдел продаж)
}
elseif($_POST['home']==1)
{
		$mess.= '<b> Ждем вас по адресу:   Каспийская 5, 1 подъезд со стороны парковки</b><br/>';
}
elseif($_POST['home']==3)
{
		$mess.= '<b>Ждем вас по адресу: Залесского 8/1, 1 подъезд</b><br/>';
}
elseif($_POST['home']==4)
{
		$mess.= '<b>Ждем вас по адресу: Мясниковой 35 а.</b><br/> <br/> <span style="color:red;  font-size:24px;">Внимание: Проход на стройку с детьми запрещен правилами техники безопасности</span><br/>';
}	
 
elseif($_POST['home']==44)
{
		$mess.= '<b>Ждем вас по адресу: Мясниковой 35 а.</b><br/> <br/> <span style="color:red;  font-size:24px;">Внимание: Проход на стройку с детьми запрещен правилами техники безопасности</span><br/>';
}	
elseif($_POST['home']==45)
{
		$mess.= '<b>Ждем вас по адресу: ул. Краузе 14/1а </b><br/>  <br/> <div style="text-align:center; color:red; font-size18px; font-weight:bold;">
			   ОБРАЩАЕМ ВНИМАНИЕ:<br/>
			   1. Посещение строящихся объектов с детьми запрещено<br/>
			   2. Запрещено посещать экскурсию с признаками ОРВИ<br/>
			   3. При себе необходимо иметь маску
			   </div>';
}	
 elseif($_POST['home']==46)
{
		$mess.= '<b>Ждем вас по адресу: Красный проспект 327/3.</b> (2-й подъезд)<br/>  <br/> <div style="text-align:center; color:red; font-size18px; font-weight:bold;">
			   ОБРАЩАЕМ ВНИМАНИЕ:<br/>
<br/>
			   1. Запрещено посещать экскурсию с признаками ОРВИ<br/>
			   2. При себе необходимо иметь маску<br/>
			   
			   </div>';
}	
 elseif($_POST['home']==38)
{
		$mess.= '<b>Ждем вас по адресу: Красный проспект 331/2</b><br/>';
}	


 elseif($_POST['home']==200)
{
		$mess.= '<b>Ждем вас за 15 мин до начала экскурсии по адресу: Площадь Калинина, остановка метро Заельцовская</b>   <br/> <br/><div style="text-align:center; color:red; font-size18px; font-weight:bold;">
			   ОБРАЩАЕМ ВНИМАНИЕ:<br/>
				<br/>
			   1. Запрещено посещать экскурсию с признаками ОРВИ<br/>
			   2. При себе необходимо иметь маску<br/>
			   </div>';
}


 
  
	$_POST[data] =  date("Y-m-d", strtotime($_POST[data])); // Преобразуем дату в формат MYSQL

	 $now = date("Y-m-d H:i:s");
$data=array();
$data['date']=$_POST['data'];
$data['time']=$_POST[time].':00';
$data['phone']=$_POST['phone'];
$data['message']=$_POST['message'];
$data['name']=$_POST['name'];
$data['home']=(int) $_POST['home'];
$data['datetime']= $now;
$data['peoples']=(int) $_POST['peoples'];
$data['formhach']=rand(0,10000);


	//$sql = "INSERT INTO `excurs` (`date`, `time`, `phone` , `message`,`name`,`home`,`datetime`,`peoples`) VALUES ('".."', '"., '".$_POST[phone] ."', '".$_POST[message] ."', '".$_POST[name] ."', '".$_POST[home] ."', NOW(),  '".$_POST[peoples] ."'); ";
$query = $mysql->insert('excurs',$data);
	 
	
	
	
		}
	##################
	
	
	
	
	
	
	
	
	
	
	
	
	} ###### не все поля заполнены
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
if($query)
{
print "<center><h2>Ваша заявка принята</h2>";
 
?>
<div style="border:2px solid #000; text-align: center; padding:10px; margin-top:20px; margin-bottom:20px;">	
<u><b>Вы успешно записанны на экскурсию!</b></u><br/><br/>
  <?=$mess?>
 <br/>
</div>
</center>
<?
}
else
{
   print "<center><h2>К сожалению пока вы заполняли форму данное время было занято, попробуйте, записаться на другое время</h2>";
   print '<a href="#" onclick="window.history.back()">Записаться</a>';
}
	
	 include('messages/class.php');
     $message = new messages();
	
	# $message->send( '89236470002@mail.ru' ,'Заявка на экскурсию ' , $mess );
	 
	
 	 $mess->send( '89236470002@mail.ru,op03@em-nsk.group,op15@em-nsk.group' ,'Заявка на экскурсию ', $mess );
 
	}
	
 
 ?>		

 </body>
 </html>