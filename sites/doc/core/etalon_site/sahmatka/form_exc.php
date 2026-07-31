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
	
	</style>


<?
if(!$_POST)
{
	
	/*
	
	<option value="3">ЖК Залесский</option>
	<option value="44">Cерия GREEN (601 кирпичная секция)</option>
	
	*/
?> 
<center> 

<div style="display:inline-block; max-width:98%; font-size:16px; text-align:right;">
<form method="post" id="excform">
			

<div style="background-color:#FFF;  text-align:center;  padding:10px;  ">
	<center>
	 
	<input type="text" style="padding:5px; border-radius:8px; border:solid 1px;  border: 1px solid #E0E0E0; box-sizing: border-box; box-shadow: 0px 1px 6px; font-size:16px; display:inline-block; margin:10px;" name="name" id="name" placeholder="ФИО">  
	<input type="text" style="padding:5px;  border-radius:8px; border:solid 1px; border: 1px solid #E0E0E0; box-sizing: border-box; box-shadow: 0px 1px 6px;  font-size:16px; display:inline-block; margin:10px;" name="message" id="message"  placeholder="Вопросы / пожелания">
	<input type="text" style="padding:5px;  border-radius:8px; border:solid 1px; border: 1px solid #E0E0E0; box-sizing: border-box; box-shadow: 0px 1px 6px;  font-size:16px; display:inline-block; margin:10px;" name="phone" id="phone"  placeholder="Ваш телефон">
<br/>


<br/>

<select id="home" name="home" style="max-width:100%;">
	<option value="">Место экскурсии</option>
 
	<option value="45">704 (6 секция)</option>
	<option value="46">Шоу-рум</option>

</select>
 
<select id="data" name="data" style="max-width:100%;">
	<option value="">Дата желаемая</option>
</select>
	
<select id="time" name="time" style="max-width:100%;">
<option value="">Время</option>
</select>	 
		<input type="submit" id="submit" value="Отправить заявку" style="     min-width: 30%; border:none; box-shadow: 0px 1px 6px; padding:7px; background: #E81D28; color:#FFF; border-radius:8px; margin:10px; font-weight:bold; font-size:16px;" onClick= "ga('send', 'event', 'submit', 'exc_button'); yaCounter18713149.reachGoal('exc_button'); return true;">
	</center>
</div>
  
					 
			   </form>
			   <div style="text-align:center; color:red; font-size18px; font-weight:bold;">
			   ОБРАЩАЕМ ВНИМАНИЕ:<br/>
			   1. Посещение строящихся объектов с детьми запрещено<br/>
			   2. Запрещено посещать экскурсию с признаками ОРВИ<br/>
			   3. При себе необходимо иметь маску
			   </div>
			   <br><br>
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
				 $("#data").load("sahmatka2/ajax_form_exc.php", { load:"date", home: $("#home option:selected").val() });
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
 			 $("#time").load("sahmatka2/ajax_form_exc.php", { load:"time", data: $("#data option:selected").val() , home: $("#home option:selected").val() });
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
$connection = mysqli_connect('localhost', 'u4603566_energo', 'EM78sd34ns', 'u4603566_sahmatka') or die(mysqli_error()); // Соединение с базой данных 
$result = mysqli_query($connection, "SET NAMES utf8");
 
	if(!$_POST['data'] || !$_POST['phone']  || !$_POST['time']  || !$_POST['name'] ){   print 'Необходимо заполнить все поля!';}
	else
	{
    $mess.= 'Имя  - <b>'.  $_POST[name].'</b><br/>';
	$mess.= 'Контактный телефон  - <b>'.  $_POST[phone].'</b><br/>';
	$mess.= 'Дата  - <b>'.  $_POST[data].'</b><br/>';
	$mess.= 'Время  - <b>'.  $_POST[time].'</b><br/>';
	$mess.= 'Сообщение  - <b>'.  $_POST[message].'</b><br/>';
 


$t_x = strtotime($_POST['data']);
$day_x = date("N",$t_x);


if($_POST['home']==2)
{
		$mess.= '<b>Ждем вас за 15 мин до начала экскурсии по адресу: Красный проспект 314, 1 подъезд, вход со двора </b><br/>';
		//Красный проспект 314, 1 подъезд, вход со двора //ул.Тюленина д.26., 1 этаж (отдел продаж)
}
elseif($_POST['home']==1)
{
		$mess.= '<b>Ждем вас за 15 мин до начала экскурсии по адресу: ул. Краузе, 14 стр.</b><br/>  <br/> <span style="color:red;  font-size:24px;">Внимание: Проход на стройку с детьми запрещен правилами техники безопасности</span><br/>';
}
elseif($_POST['home']==3)
{
		$mess.= '<b>Ждем вас за 15 мин до начала экскурсии по адресу: Залесского 8/1, 1 подъезд</b><br/>';
}
elseif($_POST['home']==4)
{
		$mess.= '<b>Ждем вас за 15 мин до начала экскурсии по адресу: Мясниковой 35 а.</b><br/> <br/> <span style="color:red;  font-size:24px;">Внимание: Проход на стройку с детьми запрещен правилами техники безопасности</span><br/>';
}	
 
elseif($_POST['home']==44)
{
		$mess.= '<b>Ждем вас за 15 мин до начала экскурсии по адресу: Мясниковой 35 а.</b><br/> <br/> <span style="color:red;  font-size:24px;">Внимание: Проход на стройку с детьми запрещен правилами техники безопасности</span><br/>';
}	
elseif($_POST['home']==45)
{
		$mess.= '<b>Ждем вас за 15 мин до начала экскурсии по адресу: ул. Краузе 14/1а </b><br/>  <br/> <div style="text-align:center; color:red; font-size18px; font-weight:bold;">
			   ОБРАЩАЕМ ВНИМАНИЕ:<br/>
			   1. Посещение строящихся объектов с детьми запрещено<br/>
			   2. Запрещено посещать экскурсию с признаками ОРВИ<br/>
			   3. При себе необходимо иметь маску
			   </div>';
}	
 elseif($_POST['home']==46)
{
		$mess.= '<b>Ждем вас за 15 мин до начала экскурсии по адресу: Красный проспект 314, 1 подъезд, вход со двора.</b><br/>  <br/> <div style="text-align:center; color:red; font-size18px; font-weight:bold;">
			   ОБРАЩАЕМ ВНИМАНИЕ:<br/>
<br/>
			   1. Запрещено посещать экскурсию с признаками ОРВИ<br/>
			   2. При себе необходимо иметь маску
			   </div>';
}	
 
  
	$_POST[data] =  date("Y-m-d", strtotime($_POST[data])); // Преобразуем дату в формат MYSQL
	$sql = "INSERT INTO `excurs` (`date`, `time`, `phone` , `message`,`name`,`home`,`datetime`) VALUES ('".$_POST[data]."', '".$_POST[time].":00', '".$_POST[phone] ."', '".$_POST[message] ."', '".$_POST[name] ."', '".$_POST[home] ."', NOW() ); ";

	$query = mysqli_query($connection, $sql); 
	}
	
	
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
//print "<center><h2>К сожалению пока вы заполняли форму данное время было занято, попробуйте, записаться на другое время</h2>";
//print '<a href="http://www.em-nsk.ru/zapis34.php">Записаться</a>';
}
	
	 include('messages/class.php');
     $message = new messages();
	
	// $message->send( '89236470002@mail.ru,op@em-nsk.group' ,'Заявка на экскурсию ' , $mess );
 	// $mess->send( 'op@em-nsk.group' ,'Заявка на экскурсию ', $mess );
 
	}
 ?>		
 