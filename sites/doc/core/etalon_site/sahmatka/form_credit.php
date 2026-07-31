<?
session_start();
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
  header("Last-Modified: " . gmdate("D, d M Y H:i:s")." GMT");
  header("Cache-Control: no-cache, must-revalidate");
  header("Cache-Control: post-check=0,pre-check=0", false);
  header("Cache-Control: max-age=0", false);
  header("Pragma: no-cache");
 
include('config.php');
?>
<!DOCTYPE html>
<html>
<head>
  <head>
    <title>Энергомонтаж</title>

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>

    <link rel="icon" href="/templates/jv-framework/favicon.ico">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Bootstrap -->
    <link href="http://www.em-nsk.ru/css/bootstrap.min.css" rel="stylesheet">

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



input,select
{
	border:1px solid #000;
	border-radius:5px;
	padding:4px;
	font-size:16px;
	margin:6px;
	width:100%;
}
	
	</style>

  </head>

<body style="margin-top:0; padding-top:0">

<!-- Global site tag (gtag.js) - Google Analytics -->
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-126816776-1"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-126816776-1');
</script>

<?
if(!$_POST)
{
	?>
	
 
			    <span onClick="gtag('config', 'UA-126816776-1', {'page_path': '/send_form_credit'}); alert(1); return true;"  >
				11111
				</span>
				
				<span onclick="gtag('event', 'credit_form_submit', {'event_category': 'credit_form_submit', 'event_action': 'credit_form_submit'}); alert(2); return true;"  >
				22222
				</span>
				
				
        <center>
                  <h1 style="font-size:34px; text-align:center;">Заявка на расчет ипотечного кредита</h1>   
               


<div style="display:inline-block; max-width:400px; font-size:16px; text-align:right;">
			   <form method="post">
			    

				<input type="text" name="name" placeholder="Контактное лицо" required  /><br/>
				<input type="text" name="phone" placeholder="Телефон / Email" required  /><br/>
 			

			  Дом: <select id="home" name="home_id" style="max-width:300px;">
                    	 <option value="0">Все</option>

						<option value="3">Родники №451</option>
					 <option value="7">Родники №452</option>
					 <option value="6">Родники №453</option>


                   	 
                  	 <option value="8">Приозерный №2</option>
 		 
					 <option value="9">Тюленина №1</option>
                  	 <option value="10">Тюленина №2</option>
 
					 <option value="12">Жилой дом №603 по генплану</option>
					 <option value="15">Жилой дом №601 по генплану</option>
		
                </select>

		<br/>

        	Комнат:  <select id="rooms" name="rooms" style="max-width:300px;"> 
                    <option value="0"> --- </option>
					 <option value="1">1 </option>
					  <option value="2"> 2 </option>
					   <option value="3"> 3 </option>
                 </select>

		 

     		  <select id="area" name="area"  style="max-width:300px; display:none;">
                    <option value="0">---</option>
                </select>

		 

     		  <select id="price" name="price"   style="max-width:300px; display:none;">
                    <option value="0">---</option>
                 </select>


				
				
			    <input type="text" name="srok" placeholder="Желаемый срок кредита"   />
			    <input type="text" name="vznos" placeholder="Первоначальный взнос"   />
			    <input type="text" name="platez" placeholder="Допустимый размер ежемесячного платежа"   />

			 
 
			    <input type="submit"  style="background-color:#E2302D; color:#FFF; padding:10px;  padding-left:40px; padding-right:40px; font-size:16px; font-weight:bold;   border-radius:7px;" value="Расcчитать" onClick="yaCounter18713149.reachGoal('credit_form_submit');  gtag('event', 'Произвольное название события', { 'event_category': 'credit_form_submit', 'event_action': 'credit_form_submit', }); return true;"  >
 
					 
			   </form>
		</div>

		</center>	   



        <script type="text/javascript">
     /*  
	 $("#home").change(function(){
            	 $("#rooms").load("ajax_form_credit.php", { load:"rooms", home: $("#home option:selected").val()  });
		 
		 $("#rooms").removeAttr("disabled");
		 $("#area").prop('disabled', 'disabled');
		 $("#price").prop('disabled', 'disabled');
        });

  	$("#rooms").change(function(){
          	$("#area").load("ajax_form_credit.php", { load:"area", home: $("#home option:selected").val()  , rooms: $("#rooms option:selected").val()  });
 		 $("#area").removeAttr("disabled");
		 $("#price").prop('disabled', 'disabled');
        });

	$("#area").change(function(){
            $("#price").load("ajax_form_credit.php", { load:"price", home: $("#home option:selected").val() ,  rooms: $("#rooms option:selected").val(),  area: $("#area option:selected").val()});
 	    $("#price").removeAttr("disabled");
        });
		
		*/
        </script>



	<?
}
else
{



$homes[5]='Приозерный 1';
$homes[8]='Приозерный 2';
$homes[3]='451';
$homes[7]='452';
$homes[6]='453';
$homes[9]='Тюленина 1';
$homes[10]='Тюленина 2';
$homes[12]='603';
$homes[15]='601';


$home = $homes[ $_POST['home_id'] ];


		 include('SendMailSmtpClass11.php');
	 
	 $mailSMTP = new SendMailSmtpClass('energomontaz452@mail.ru', 'zdctvjue123!!!!', 'ssl://smtp.mail.ru',465,"UTF-8"); // создаем экземпляр класса
	// от кого
	$from = array(
		"EM-NSK", // Имя отправителя
		"energomontaz452@mail.ru" // почта отправителя
	);
 
		 

		 $text = '
		 Имя - '.$_POST['name'].' <br/>
		 Контакт - '.$_POST['phone'].'<br/>
		 
		 Первоначальный взнос - '.$_POST['vznos'].'<br/>
		 Желаемый срок - '.$_POST['srok'].' <br/>
		 Допустимый платеж - '.$_POST['platez'].' <br/>


Дом - '.$home.'<br/>
Количество комнат - '.$_POST['rooms'].' <br/>
Площадь - '.$_POST['area'].' <br/>
Цена - '.$_POST['price'].' <br/>

		 ';
 
		 $result =  $mailSMTP->send('89236470002@mail.ru', 'Заявка с сайта EM-NSK.ru - '.$_POST['name'].' ', $text, $from); // отправляем письм
		 $result =  $mailSMTP->send('op@em-nsk.group', 'Заявка с сайта EM-NSK.ru - '.$_POST['name'].' ', $text, $from); // отправляем письмо
	// отправляем письмо
	 
		// $result =  $mailSMTP->send('Кому письмо', 'Тема письма', 'Текст письма', 'Заголовки письма');
		if($result === true){
			echo "<br><br><br><br> <center><h1><br><br>Ваша заявка успешно отправлена, <br/>менеджер свяжется с вами в ближайшее время!</h1></center>";
		}else{
			echo " Ошибка: " . $result;
		}


}

 ?>		
 
  <!-- Yandex.Metrika counter --> <script type="text/javascript" > (function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter18713149 = new Ya.Metrika({ id:18713149, clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true, trackHash:true }); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = "https://mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks"); </script> <noscript><div><img src="https://mc.yandex.ru/watch/18713149" style="position:absolute; left:-9999px;" alt="" /></div></noscript> <!-- /Yandex.Metrika counter -->	
	   
			   


 


</body>
</html>