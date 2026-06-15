<style>
.parent ~ .cchild {
  display: none;
  
     -moz-transition: all 1s 0.1s ease-in;
    -o-transition: all 1s 0.1s ease-in;
    -webkit-transition: all 1s 0.1s ease-in;
	
}
.open .parent ~ .cchild {
  display: table-row;
}
.parent {  cursor: pointer;}
 
.open {}

.open .cchild {}
.parent > *:last-child {
  width: 30px;
}
.parent i {
  transform: rotate(0deg);
  transition: transform .3s cubic-bezier(.4,0,.2,1);
  margin: -.5rem;
  padding: .5rem;
}
.open .parent i {
  transform: rotate(180deg)
}
</style>


<script>
// Копирование логина и пароля по клику
function copytext(el) {
    var $tmp = $("<textarea>");
    $("body").append($tmp);
    $tmp.val($(el).text()).select();
    document.execCommand("copy");
    $tmp.remove();
	alert('Скопировано');
}
</script>
 
			
			
 
 
 
 
 
 
 
 
 
 
<?

function select_filed_o($arr,$val='',$color_arr='')
{
	if(!$val){ $val=''; }
	foreach($arr as $k=>$v)
	{
		?>
		<option value="<?=$k?>" <? if($k==$val){print ' selected="selected" ';} ?>><?=$v?></option>
		<?
	}
}
?>
 
 
 
 
<?
ob_start();
	#Удаление
    if (isset($_GET['del_id'])) { //проверяем, есть ли переменная на удаление
        //$sql = mysqli_query($connection,'DELETE FROM `users` WHERE `id` = '.$_GET['del_id']); //удаляем строку из таблицы
    }
	
 if($_GET[reg]==1)
 {
	 
	 if($_POST[login2] && $_POST[password2]  && $_POST[name2]  && !$_GET[id])
	 { 
			$login = $_POST['login2']; // Присваеваем переменной значение из поля с логином             
			$password = $_POST['password2']; // Присваеваем другой переменной значение из поля с паролем
			$name = $_POST['name2']; // Присваеваем другой переменной значение из поля с паролем
			
			$e_mail = $_POST['e_mail'];
			$phone = $_POST['phone'];
			$agency_id = $_POST['agency_id'];
			
		    $query = "INSERT INTO `users` (login, password,name,e_mail,phone,agency_id) VALUES ('$login', '$password' ,'$name' ,'$e_mail','$phone','$agency_id')"; // Создаем переменную с запросом к базе данных на отправку нового юзера
			$result = mysqli_query($connection, $query) or die(mysql_error()); // Отправляем переменную с запросом в базу данных 
			echo "<div align='center'>Данные сохранены!</div>"; // Сообщаем что все получилось
	 }
	 elseif($_POST[login2] && $_POST[password2]  && $_POST[name2] && $_GET[id])
	 {
					$q = 'UPDATE `users` SET '
                    .'`name` = "'.$_POST['name2'].'",'
                    .'`password` = "'.$_POST['password2'].'" , '
					.'`login` = "'.$_POST['login2'].'" , '
					
					.'`e_mail` = "'.$_POST['e_mail'].'",'
                    .'`phone` = "'.$_POST['phone'].'" , '
					.'`agency_id` = "'.$_POST['agency_id'].'" '
					
                    .'WHERE `id` = "'.$_GET['id'].'" ';
					// print $q;
					$sql = mysqli_query($connection,$q);
	 }
	 elseif($_POST)
	 {
		 print '<span style="color:red">Заполните все поля!</span>';
		 
	 }
		 
		 
 
		 // Редактирование
	 if($_GET[id])
{
		 $data= 1;
		 
		 
		 
		$t='Редактирование пользователя:';

// Все агенства собираем для селект поля
$query = mysqli_query($connection, "SELECT * FROM `agency` "); 
 
while( $row = mysqli_fetch_array($query ))
{
   // print_r($row);
   $agency_arr[$row['agency_id']] = $row['caption'];
}
 
	 
$query = mysqli_query($connection, "SELECT * FROM `users` WHERE id='".$_GET['id']."' ");   
$result = mysqli_fetch_array($query);
	
	 }
	 else
	 {
		 $t='Новый пользователь:';
		// Все агенства собираем для селект поля
		$query = mysqli_query($connection, "SELECT * FROM `agency` "); 
		 
		while( $row = mysqli_fetch_array($query ))
		{
		   // print_r($row);
		   $agency_arr[$row['agency_id']] = $row['caption'];
		}
		 
			 
		$query = mysqli_query($connection, "SELECT * FROM `users` WHERE id='".$_GET['id']."' ");   
		$result = mysqli_fetch_array($query);
		
	 }
	 ?>

	<div id="bottom-form" class="bottom-form">
				<div class="bottom-form__title"><?=$t?></div>
				<form action="user.php?action=users&reg=1&id=<?=$_GET[id]?>" method="post">
					<div class="bottom-form-wrap">
						<div class="bottom-form-fields">
								<div class="bottom-form__in">
								<input type="text" placeholder="Логин" name="login2" value="<?=$result[login]?>">
							</div>
							<div class="bottom-form__in">
								<input type="text" placeholder="E-mail" name="e_mail" value="<?=$result[e_mail]?>">
							</div>
							<div class="bottom-form__in">
								<input type="text" placeholder="ФИО" name="name2" value="<?=$result[name]?>">
							</div>
							<div class="bottom-form__in">
								<input type="text" placeholder="Пароль" name="password2" value="<?=$result[password]?>">
							</div>
							<div class="bottom-form__in">
								<input class="phone-in" type="text" placeholder="Телефон" im-insert="true" name="phone" value="<?=$result[phone]?>">
							</div>
							<select data-placeholder="Агенство" name="agency_id">
								<?
								foreach($agency_arr as $k11=>$v11)
								{ 
									 ?><option value="<?=$k11?>" <? if($k11==$result[agency_id]){ ?> selected="selected" <? }?>><?=$v11?></option><?
								}
								?>  
							</select>
						</div>
						<button class="btn btn_size6 btn_arrowxl bottom-form__btn">Сохранить<i></i></button>
					</div>
				</form>
			</div>
			
 	 
	 <?

 }
 
 
 
 
 
 
 
 
if($_GET[reg]==2)
 {

 
		 
	 if($_POST[caption] && !$_GET[id])
	 { 
 
			if( $mysql->get_for_key('users','login', $_POST['login2'] ) )
			{
				print '<span style="color:red">Такой логин уже занят!</span>';
			 
				$check_errors=1;
				$errors[]='Такой логин уже занят';
			}
 
 
			$caption = $_POST['caption'];    
			  
			# Проверка данных
			
			  
			if(!$check_errors  ) // нет ошибок
			{
				
			$now = date("Y-m-d H:i:s");
							
			# ДОбавляем агентство
			$data = array();
			$data['caption']=$caption;
			$data['admin_user_id']=1;
			$data['add_datetime']=$now;
			
			
			$aid = $mysql->insert('agency',$data);// ИД АГЕНТСТВА =========  

			print '<b> Агентство №'.$aid.'</b>';
			 
			$login = $_POST['login2']; // Присваеваем переменной значение из поля с логином             
			$password = $_POST['password2']; // Присваеваем другой переменной значение из поля с паролем
			$name = $_POST['name2']; // Присваеваем другой переменной значение из поля с паролем			
			$e_mail = $_POST['e_mail'];
			$phone = $_POST['phone'];
			$agency_id = $_POST['agency_id'];
			
			
			
			#Пользователь
			$data = array();
			$data['login']=$login;
			$data['password']=$password;
			$data['name']=$name;
			$data['e_mail']=$e_mail;
			$data['phone']=$phone;
			$data['agency_id']=$aid;
			$data['add_datetime']=$now;
				
			$n_uid =  $mysql->insert('users',$data);// ИД Пользователя =========  
 
			echo "<div align='center'>Данные сохранены!</div>"; // Сообщаем что все получилось
 
			// Обновляем данные агенства делаем админом добавленного пользователя
			$data = array();
			$data['admin_user_id']=$n_uid;
 
			$mysql-> update_for_key( 'agency' , 'agency_id' , $aid , $data );
 	
			}	
					
					
					
 
		# формирвем сообщение на почту о бронировании квартиры
		// вложения файлов
		$message = "Кабинет дилеров находится по ссылке  <a href=\"http://www.em-nsk.ru/images/sahmatka.htm\">http://www.em-nsk.ru/images/sahmatka.htm</a>  \r\n <br/>";
		$message .= "Ваш доступ к кабинету дилеров \r\n <br/>";
		$message .= "логин -   ".$login."\r\n </b><br/> ";
		$message .= "Пароль ".$password."</b> ";
			
 
		// XMail( 'site@em-nsk.ru', trim($e_mail),'Доступы для администратора агентства ', $message );
	  
	 }
	 elseif($_POST[caption]  && $_GET[id])
	 {
					$q = 'UPDATE `agency` SET '
                    .'`caption` = "'.$_POST['caption'].'",'
                    .'`admin_user_id` = "'.$_POST['admin_user_id'].'" '
                    .'WHERE `agency_id` = "'.$_GET['id'].'" ';
					// print $q;
					$sql = mysqli_query($connection,$q);
	 }
	 elseif($_POST)
	 {
		 print '<span style="color:red">Заполните все поля!</span>';
		 
	 }
	 	 
		 
 
     // Редактирование
	 if($_GET[id])
	 {
		 $data= 1;
 
		  $t='Редактирование агентства:'; 

		// Все пользователи агентсва -  собираем для селект поля
		$query = mysqli_query($connection, "SELECT * FROM `users`   "); // WHERE agency_id='".$_GET['id']."'
		while( $row = mysqli_fetch_array($query ))
		{
		   $users_arr[$row['id']] = $row['name'];
		}
		$query = mysqli_query($connection, "SELECT * FROM `agency` WHERE agency_id='".$_GET['id']."' "); 
		$result = mysqli_fetch_array($query);
		
		$result2 = $result;
	 }
	 else
	 {
		 // Все пользователи  
		$query = mysqli_query($connection, "SELECT * FROM `users`  "); //WHERE agency_id='".$_GET['id']."'
		while( $row = mysqli_fetch_array($query ))
		{
		   // print_r($row);
		   $users_arr[$row['id']] = $row['name'];
		}
		  $t='Новое агентство:';
		  
		  $result2['password']= rand('100000000','999999999'); // генерируем пароль
		  $result2['name'] = 'Администратор';
	 }
	 ?>


			<div class="bottom-form">
				<div id="bottom-form" class="bottom-form__title"><?=$t?></div>
				<form action="user.php?action=users&reg=2&id=<?=$_GET[id]?>" method="post">
					<div class="bottom-form-wrap">
						<div class="bottom-form-fields">
							<div class="bottom-form__in">
								Название: <input type="text" placeholder="Название агентства" name="caption" <? if(!$_GET[id]){?> class="new_agency_name" <?}?> value="<?=$result2[caption]?>">
							</div>
							
							
							
							<?
							if(!$_GET[id]) // только для новых агентств
							{
							?>
							<div class="bottom-form__in">
								Логин: <input type="text" placeholder="Логин администратора" name="login2" <? if(!$_GET[id]){?> class="new_agency_login" <?}?>  value="<?=$result2[login]?>">
							</div>
							<div class="bottom-form__in">
								Пароль: <input type="text" placeholder="Пароль" name="password2" class="password2" value="<?=$result2[password]?>">
							</div>
							<div class="bottom-form__in">
								Имя: <input type="text" placeholder="ФИО администратора" name="name2" value="<?=$result2[name]?>">
							</div>
							<div class="bottom-form__in">
								E-Mail: <input type="text" placeholder="E-mail" name="e_mail" value="<?=$result2[e_mail]?>">
							</div>
							<div class="bottom-form__in">
								Тел: <input class="phone-!in" type="text" placeholder="Телефон" im-insert="true" name="phone" value="<?=$result2[phone]?>">
							</div>
							<?
							}
							?>
							
							
						</div>
						<button class="btn btn_size6 btn_arrowxl bottom-form__btn">Сохранить<i></i></button>
					</div>
				</form>
			</div>
			
			
			
 
	 <?
	
 }
 
 $editor=ob_get_clean();
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
ob_start();
?>

				<table>
					<thead>
						<tr>
							<th><b>id</b></th>
							<th><a href="#"><b>Доступы</b></a></th>
							<th><a href="#"><b>ФИО</b></a></th>
							<th><a href="#"><b>E-Mail</b></a></th>
							<th><a href="#"><b>Телефон</b></a></th>
							<th><a href="#"><b>Агентство</b></a></th>
							<th><a href="#"><b>Последний визит</b></a></th>
							<th></th>
						</tr>
					</thead>
<?php
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
 
while ($result = mysqli_fetch_array($query)) 
{
	// print '<pre>';
	 //  print_r($result);
	 //print '</pre>';
	
	if($result['login']!='admin')
	{
	  $summ_arr['agency'][$result['agency_id']]=$result['user_agency'];
	  if(!$summ_arr['agency_activ'][$result['agency_id']]){$summ_arr['agency_activ'][$result['agency_id']] =  $result['ac'];}// последняя активност
	  $summ_arr['agency_activ_c'][$result['agency_id']]=$result['ac_c']; // всего активностей
	  
	  $summ_arr['agency_us_c'][$result['agency_id']]++; // всего активностей
	   
	  if($_GET['agency'])	{	if($result['agency_id']!=$_GET['agency']) {continue;} }

	if($result['adm']=='Да'){print '</tbody><tbody><tr class="parent">';}
	else{print '<tr class="cchild">   ';}
// '<td class="downs">'.$result['adm'].'</td>'.
	echo ' 
	  		  <td>'.$result['id'].'</td>'.
             '<td onclick="copytext(this)" id="access_'.$result['id'].'">Логин - '.$result['login'].'<br/> Пароль - '.$result['password'].'</td>'.
			 '<td>'.$result['name'].'</td>'.
			 '<td>'.$result['e_mail'].'</td>'.
			 '<td>'.$result['phone'].'</td>'.
			 '<td>'.$result['user_agency'].'</td>'.
			 '<td>'.$result['ac'].'</td>'.
             '<td> 
			 <a href="?action=users&reg=1&id='.$result['id'].'" class="table-edit  "></a>
			 </td>
			 </tr>';
	}
}
?>
</tbody>
				</table>
 

<?
$content = ob_get_clean();
?>


 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
<section class="section-stat">
	<div class="container">
		<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">ПОЛЬЗОВАТЕЛИ</div>
		</div>
		
		
		
<?=$editor?>
<a href="?action=users&reg=1" class="btn-add btn btn_size5 btn_arrow" style="display:none;">Добавить пользователя<i></i></a>
<a href="?action=users&reg=2" class="btn-add btn btn_size5 btn_arrow">Добавить агентство<i></i></a>
<br/><br/>

		<div class="stat">
			<div class="stat-top stat-top_lp stat-top_user">
				<div class="stat-top-filter">
					<div class="stat-top-item stat-top-in stat-top-in_search">
						<input type="search" placeholder="Найти">
					</div>
					<a href="#add-user" class="stat-top-btn btn btn_arrow-long" data-fancybox>Найти<i></i></a>
				</div>
				<a href="JavaScript:window.print();" class="stat-top__print"></a>
			</div>
			<div class="stat-table stat-table_notpd stat-table-user table">
			
			
			<?=$content;?>
				<table style="display:none;">
					<thead>
						<tr>
							<th><b>id</b></th>
							<th><a href="#"><b>Доступы</b></a></th>
							<th><a href="#"><b>ФИО</b></a></th>
							<th><a href="#"><b>E-Mail</b></a></th>
							<th><a href="#"><b>Телефон</b></a></th>
							<th><a href="#"><b>Агентство</b></a></th>
							<th><a href="#"><b>Последний вход</b></a></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>2396</td>
							<td style="white-space: nowrap;">an_fond_nedvizimosti0007</td>
							<td>65784678345</td>
							<td>Авилов Андрей Владимирович</td>
							<td style="white-space: nowrap;">krasnikov@metr-nsk.ru</td>
							<td style="white-space: nowrap;">+7 (913) 777-72-11</td>
							<td>ООО Фонд недвижимости</td>
							<td><a href="#edit-users" class="table-edit" data-fancybox></a></td>
						</tr>
				 
					</tbody>
				</table>
			</div>
		</div>
	</div>
</section>




<script>

$(document).ready(function() 
{
	
	
$('table').on('click', 'tr.parent .downs', function(){
    $(this).closest('tbody').toggleClass('open');
});




// Транслит названия агентства
function urlLit(w,v) 
{
	var tr='a b v g d e ["zh","j"] z i y k l m n o p r s t u f h c ch sh ["shh","shch"] ~ y ~ e yu ya ~ ["jo","e"]'.split(' ');
	var ww=''; w=w.toLowerCase();
	for(i=0; i<w.length; ++i) {
	cc=w.charCodeAt(i); ch=(cc>=1072?tr[cc-1072]:w[i]);
	if(ch.length<3) ww+=ch; else ww+=eval(ch)[v];}
	return(ww.replace(/[^a-zA-Z0-9\-]/g,'-').replace(/[-]{2,}/gim, '-').replace( /^\-+/g, '').replace( /\-+$/g, ''));
}


// Транслит названия в логин нового агентства 
$('.new_agency_name').bind('change keyup input click', function(){ $('.new_agency_login').val(urlLit($('.new_agency_name').val(),0)) });




});
</script>	

<?