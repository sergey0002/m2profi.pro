 <?
 
 if(trim($_POST[newprice]) &&  $_SESSION['sh_login'] == 'admin' )
 {
		$_POST['newprice'] = str_replace(' ','',$_POST['newprice']); 
		$query = 'UPDATE `apartaments` SET `price` = "'.$_POST[newprice].'" WHERE `home_id` = "'.$_GET[home].'"  AND  ( `apartment_num` = "100000"   ';

		foreach($_POST[editapart][$_GET[home]] as $k=>$v )
		{
			//print 'Новая цена для квартиры '.$k.' : '.$_POST[newprice].' <br/>';
			// формируем запрос на обновление цены по ид дома и хаты
			$query.= ' OR `apartment_num` = "'.$k.'" ';
		}
		$query.= ');';
		// print $query;
		$result = mysqli_query($connection, $query) or die(  mysqli_error (  $connection ) ); // Отправляем переменную с запросом в базу данных 
		 
 }
 if(trim($_POST[newplan]) &&  $_SESSION['sh_login'] == 'admin' ) // Массовая редактирование планироваок
 {
		$query = 'UPDATE `apartaments` SET `image_pb` = "'.$_POST[newplan].'" WHERE `home_id` = "'.$_GET[home].'"  AND  ( `apartment_num` = "100000"   ';
		foreach($_POST[editapart][$_GET[home]] as $k=>$v )
		{
			print 'Новая планировка для квартиры '.$k.' : '.$_POST[newplan].' <br/>';
			// формируем запрос на обновление цены по ид дома и хаты
			$query.= ' OR `apartment_num` = "'.$k.'" ';
		}
		$query.= ');';
		// print $query;
			$result = mysqli_query($connection, $query) or die(  mysqli_error (  $connection ) ); // Отправляем переменную с запросом в базу данных 
 }	  
 
if(trim($_POST['newplan_floor']) &&  $_SESSION['sh_login'] == 'admin' ) // Массовая редактирование планироваок
 {
		$query = 'UPDATE `apartaments` SET `image_pb_plan` = "'.$_POST[newplan_floor].'" WHERE `home_id` = "'.$_GET[home].'"  AND  ( `apartment_num` = "100000"   ';
		foreach($_POST[editapart][$_GET[home]] as $k=>$v )
		{
			print 'Новая планировка для квартиры '.$k.' : '.$_POST[newplan].' <br/>';
			// формируем запрос на обновление цены по ид дома и хаты
			$query.= ' OR `apartment_num` = "'.$k.'" ';
		}
		$query.= ');';
		// print $query;
			$result = mysqli_query($connection, $query) or die(  mysqli_error (  $connection ) ); // Отправляем переменную с запросом в базу данных 
 }
 
 
 
  if(trim($_POST[newarea]) &&  $_SESSION['sh_login'] == 'admin' ) // Массовая редактирование планироваок
 {
		$query = 'UPDATE `apartaments` SET `area` = "'.$_POST[newarea].'" WHERE `home_id` = "'.$_GET[home].'"  AND  ( `apartment_num` = "100000"   ';
		foreach($_POST[editapart][$_GET[home]] as $k=>$v )
		{
			print 'Новая площадь  для квартиры '.$k.' : '.$_POST[newarea].' <br/>';
			// формируем запрос на обновление цены по ид дома и хаты
			$query.= ' OR `apartment_num` = "'.$k.'" ';
		}
		$query.= ');';
		// print $query;
			$result = mysqli_query($connection, $query) or die(  mysqli_error (  $connection ) ); // Отправляем переменную с запросом в базу данных 
	
 }	 
 if(trim($_POST[newrooms]) &&  $_SESSION['sh_login'] == 'admin' ) // Массовая редактирование планироваок
 {
		$query = 'UPDATE `apartaments` SET `rooms` = "'.$_POST[newrooms].'" WHERE `home_id` = "'.$_GET[home].'"  AND  ( `apartment_num` = "100000"   ';
		foreach($_POST[editapart][$_GET[home]] as $k=>$v )
		{
			print 'Новое количество комнат для квартиры '.$k.' : '.$_POST[newrooms].' <br/>';
			// формируем запрос на обновление цены по ид дома и хаты
			$query.= ' OR `apartment_num` = "'.$k.'" ';
		}
		$query.= ');';
		
		//print $query;
		
			$result = mysqli_query($connection, $query) or die(  mysqli_error (  $connection ) ); // Отправляем переменную с запросом в базу данных 
	
 }	 
  if(trim($_POST[newtext]) &&  $_SESSION['sh_login'] == 'admin' ) // Массовая редактирование планироваок
 {
		$query = 'UPDATE `apartaments` SET `text` = "'.$_POST[newtext].'" WHERE `home_id` = "'.$_GET[home].'"  AND  ( `apartment_num` = "100000"   ';
		foreach($_POST[editapart][$_GET[home]] as $k=>$v )
		{
			print 'Новое примечание для квартиры '.$k.' : '.$_POST[newtext].' <br/>';
			// формируем запрос на обновление цены по ид дома и хаты
			$query.= ' OR `apartment_num` = "'.$k.'" ';
		}
		$query.= ');';
		// print $query;
			$result = mysqli_query($connection, $query) or die(  mysqli_error (  $connection ) ); // Отправляем переменную с запросом в базу данных 
	
 }	
if(trim($_POST[newstatus]) &&  $_SESSION['sh_login'] == 'admin' ) // Массовая редактирование Статуса
 {
 
 
		foreach($_POST[editapart][$_GET[home]] as $k=>$v )
		{
			// print '<pre>';
			//  print_r($_POST);
			// print '</pre>';
			 
			$home_id=(int) $_GET[home];
			$section_id=$_POST[editapart_section][$_GET[home]][$k];
			$floor=$_POST[editapart_floor][$_GET[home]][$k];
			$apartments=$_POST[editapart_apartaments][$_GET[home]][$k];
 
			$user_id=1;
			$status=$_POST[newstatus];
			
		// print 'Новый статус для квартиры '.$k.' : '.$_POST[newstatus].' <br/>';
			 			 
		 
			$bron_id = $sa->new_broni($home_id,$k,$status,0);
	  	
	 	    add_log('Статус квартиры изменен администратором');
			 
			########### ДОБАВЛЯЕМ БРОНЬ И 
			/*
			print   $query = 'INSERT INTO `broni` (`home_id`, `section_id`,  `floor`, `apartments`,  `apartments_num`, `user_id`,`status`, `date`) VALUES ("'.$home_id.'", "'.$section_id.'", "'.$floor.'", "'.$apartments.'", "'.$k.'","'.$user_id.'","'.$status.'", NOW() );'; 
			print '<br/>';
			$result = mysqli_query($connection, $query) or die( mysql_error() ); // Отправляем переменную с запросом в базу данных 
			print  mysqli_error(  $connection );
				
			print $query = 'INSERT INTO apartaments( `home_id` , `apartment_num` , `section_id`, `apartments`, `floor`, `status` ) 
			VALUES 
			("'.$home_id.'","'.$k.'",  "'.$section_id.'",  "'.$apartments.'", "'.$floor.'" , "'.$status.'") 
			ON DUPLICATE KEY UPDATE
			home_id = "'.$home_id.'",
			apartment_num = "'.$k.'",
			section_id = "'.$section_id.'",
			apartments = "'.$apartments.'",
			floor = "'.$floor.'",
			status = "'.$status.'" 
			; ';

			print '<br/>';
			$result = mysqli_query($connection, $query) or die( mysql_error() ); // Отправляем переменную с запросом в базу данных 
			print  mysqli_error(  $connection );
			*/
		}
 }
 ?>
 
 
 <?
 $sa = new sahmatka( $_SESSION , $connection );
 $h = $sa->get_home_arr( $_GET['home'] );
 
 //print_r($h);
 if(!$h){ $h = $sa->get_home_arr( $_GET['home'] ,0);}
 
 // ТОлько для админов
  if(!$h){ $h = $sa->get_home_arr( $_GET['home'] ,2);}
 
 // ТОлько для ОП
  if(!$h){ $h = $sa->get_home_arr( $_GET['home'] ,3);} 
 
 
 # Массив домов (Из базы)
	$h_arr = $sa->get_homes_arr();
	//print '<pre>';
//	print_r($h_arr);
	//print '</pre>';
   
  
 ?>
		
		
						
						
<style>
.slick-initialized .slick-slide {
    display: inline-block;
}
.slick-slide {float:none;}
</style>









	  
	  
						
 
 <section class="section-object">
	<div class="container mobc">
	
		<div class="page-header" style="margin-bottom:0;">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">Объекты</div>
		</div>
<?
		object_menu();		
?>

						
						
						
						


		 
		<div class="stat-top stat-top_lp stat-top_user">
			
			<div style=""></div>
				<a href="print_home.php?home=<?=$_GET['home']?>" class="stat-top__print"></a>
			</div>
			
			
	 
		 
		<div class="objects">
			<div class="objects-head" style="display:none;">
				<div class="objects-head-top">
					<div class="objects-head-top__title"><?=$h['title']?></div>
					<div class="objects-head-nav">
					<form id="obj_nav_form" method="GET" action="user.php">
						<div class="objects-head-nav__select" style="float: left;">
						<input type="hidden" name="action" value="<?=$_GET['action']?>" />
							<select  name="home">
							<?
								?><option>Выбрать дом</option><?
							foreach($h_arr as $k=>$v)
							{
								?><option value="<?=$v['home_id']?>" <? if($v['home_id']==$_GET['home']){ /* print 'selected="selected" '; */} ?>><?=$v['long_title']?></option><?
							}
							?>
							<option value="sdan">Сданные объекты</option>
							</select>
							
						</div>
						<a href="#" class="objects-head-nav__btn btn btn_arrow" onclick="document.getElementById('obj_nav_form').submit(); return false;" style="margin-left:10px;">К объекту<i></i></a>
						</form>
						
					</div>
				</div>
				<div class="objects-head-row">
					<div class="objects-head-pict">
						<img src="render/<?=$_GET['home']?>.jpg" alt="">
						<div class="objects-head-pict-info">
							<div class="objects-head-pict__location"><?=$h['title']?></div>
							<div class="objects-head-pict__status objects-head-pict__status_sale"><?=$h['status_text']?></div>
						</div>
					</div>
					<div class="objects-head-main">
						<div class="objects-head-main__text">
						<?=$h['description']?>
						</div>
						<div class="objects-head-main__text objects-head-main__text_info">
							 <?=$h['actions_text']?>
						</div>
					</div>
					<div class="objects-head-status">
						<div class="objects-head-status__title">Статусы <br>квартир</div>
						<ul class="objects-head-status-list">
							<li class="objects-head-status__green">Свободна</li>
							<li class="objects-head-status__yellow">Забронирована</li>
							<li class="objects-head-status__red">Продана</li>
							<?
							if($_SESSION['sh_login'] == 'admin')
							{
								?>
								<li class="objects-head-status__grey">Забронирована <br>застройщиком</li>
								<li class="objects-head-status__blue">Забронирована <br>подрядчиком</li>
								<?
							}
							?>
						</ul>
					</div>
				</div>
			</div>












<?
$secc = count($homes[$_GET['home']])-1;
if( $secc==2)
{
	$d='data-slick=\'{"slidesToShow": 2, "slidesToScroll": 1}\'';
	?>
	<style>
	.cl-item{text-align:center;}
	</style>
	<?
}
if( $secc==1)
{
	$d='data-slick=\'{"slidesToShow": 1, "slidesToScroll": 1}\'';
	?>
	<style>
	.cl-item{text-align:center;}
	</style>
	<?
}
 ?>
 
	
					
					
					
					
	<form action="" method="POST">
			<div class="objects-price" style="margin-top:0;">
				<div class="objects-cl-nav" <?=$d?>  >
					<?
					// Меню подездов слайдера
					$sa->diaplay_home_secmenu( $homes , $_GET['home'] );
					?>
				</div>
	
					
			<div class="objects-cl" <?=$d?>  >
			<?
			/*
			foreach($homes[$_GET['home']] as $k=>$v)
			{
				if(is_array($v) )
				{
				 $sa->diaplay_home( $homes , $_GET['home']  , $k , $data_broni,'' );
				}
			}
			 
			*/
				
		
			 
				if($_GET['home'])
				{
					$_GET['home'] = (int) $_GET['home'];
					$q= 'SELECT homes_sections.section_id FROM homes 
					LEFT JOIN homes_sections on homes_sections.homes_id = homes.homes_id 
					WHERE homes.home_id = "'.$_GET['home'].'" ';
					$arrs = $mysql->get_arr( $q );
				
					foreach($arrs as $k=>$v)
					{
						// Запрос по секциям дома делаем 
						$sa->disp_home( $_GET['home']  , $v['section_id']  ); 
					}
				}

			?>
			</div>
			
			
			</div>
				
				
				
	 <div>
		<ul class="objects-head-status-list" style="text-align: right; margin-bottom:30px; margin-top:30px;">
			<li class="objects-head-status__green" style="display:inline-block; margin-right: 20px;">Свободна</li>
			<li class="objects-head-status__yellow" style="display:inline-block; margin-right: 20px;">Забронирована</li>
			<li class="objects-head-status__red" style="display:inline-block; margin-right: 20px; ">Продана</li>
			<?
				if($_SESSION['sh_login'] == 'admin')
				{
					?>
					<li class="objects-head-status__grey" style="display:inline-block; margin-right: 20px;">Забронирована  застройщиком</li>
					<li class="objects-head-status__blue" style="display:inline-block; margin-right: 20px;">Забронирована  подрядчиком</li>
					<?
				}
			?>
			</ul>
	 </div>
	 
	 
	 
 <div class="" style="width:100%; max-width:1000px;">
				 
				
				
				 
				 
<style>
 
#accordeon{width:100%;}
#accordeon .acc-head {
	padding: 10px 10px;
	background: #00CDAD;
	cursor: pointer;   
	color:#FFF;
	border: 2px solid #00CDAD;
	border-radius:10px;
	margin:2px;
}
 
#accordeon .acc-body {
	padding: 10px;
 border: 1px solid #00CDAD;
 border-radius:10px;
	display: none;
	text-align:center;
}


.inpx {
    display: inline-block;
	margin:2px;;
    width: 80%;
    height: 51px;
    padding: 0 35px 0 12px;
    outline: none;
    font-size: 18px;
    color: #01112B;
    text-transform: uppercase;
    border: 2px solid #00CDAD;
    background: transparent;
}
 
 input[type="radio"]:checked+label
 { 
 border:solid 2px #000;  
 display:block;  
 background:#000;
 }
</style>	


<?
function get_plans_radio($home,$section)
{
	global $mysql;
	
	$arr = $mysql->get_arr('SELECT image_pb FROM apartaments WHERE apartaments.home_id="'.$_GET['home'].'" group by image_pb');
	//print_r($arr);
	
	foreach($arr as $k=>$v)
	{
		$img = $v['image_pb'];
		$md5img = md5($img);
		?>
		<div style="display:inline-block; width:200px; height:200px; border:solid 1px;">
		<label for="<?=$md5img?>"><img src="<?=$img?>" style="width:170px; max-height:150px; width:100%;" /></label><br/>
		<input type="radio" id="<?=$md5img?>" name="newplan" value="<?=$img?>"> 
		</div>
		<?
	}
}


?>


		
		<?
/*

		<div class="acc-head">
			Планировка
		</div>
		<div class="acc-body">
			<?
			get_plans_radio(1,1);
			?>
		</div>
		
		
		<div class="acc-head">
			Другие свойства квартиры 
		</div>
		<div class="acc-body">
			 <input type="text" name="newplan" placeholder="План"  value=""  class="inpx" /> <br/>
			<input type="text" name="newarea" placeholder="Общая площадь"  value=""  class="inpx" /> <br/>
			<input type="text" name="newrooms"  placeholder="Количество комнат" class="inpx" /><br/>
 
		</div>
		
		
		
		<div class="acc-head">
			Новый статус
		</div>
		<div class="acc-body">
		Статус -   
		<select name="newstatus">
			  <option value="0">Не задан</option>
			  <option value="2">Свободна</option>
			  <option value="4">Забронирована</option>
			  <option value="3">Продана</option>
			  <option value="5">Забронирована застройщиком</option>
			  <option value="6">Квартира подрядчика</option>
		</select>
		
		
		*/
?>


		
		<div id="accordeon">
		<div class="acc-head">
			Новая цена
		</div>
		<div class="acc-body">
			<input type="text"  name="newprice"  min="0" placeholder="Введите новое значение цены" value="" class="inpx money">
		</div>
		 
		
		
		
		<div class="acc-head">
			Новый статус
		</div>
		<div class="acc-body">
		Статус -   
		<select name="newstatus">
			  <option value="">Не задан</option>
			  <option value="2">Свободна</option>
			  <option value="4">Забронирована</option>
			  <option value="3">Продана</option>
			  <option value="5">Забронирована застройщиком</option>
			  <option value="6">Квартира подрядчика</option>
		</select>
		
		</div>
		
		<div class="acc-head"  >
			Планировка
		</div>
		<div class="acc-body" style="display:none;" >
			 <input type="text" name="newplan" placeholder=" Ссылка на файл планировки"  value="" autocomplete="off" class="inpx" style="width:100%; text-transform: none;" /> <br/>
		</div>
		 
		 
		 <div class="acc-head"  >
			Планировка на плане этажа
		</div>
		
		
		<div class="acc-body" style="display:none;" >
			 <input type="text" name="newplan_floor" placeholder="Ссылка на файл планировки на этаже"  value="" autocomplete="off" class="inpx" style="width:100%; text-transform: none;" /> <br/>
		</div>
		
		
		
		
		<div class="acc-head" style="display:no!ne;" >
			Площадь
		</div>
		<div class="acc-body" style="display:non!e;" >
			 <input type="text" name="newarea" placeholder="Площадь"  value=""  class="inpx" style="width:100%; text-transform: none;" /> <br/>
		</div>
		
		
		
		
		<div class="acc-head" style="display:no!ne;" >
			Комнат
		</div>
		<div class="acc-body" style="display:non!e;" >
			 <input type="text" name="newrooms" placeholder="Комнат"  value=""  class="inpx" style="width:100%; text-transform: none;" /> <br/>
		</div>
		
		
		
		
		
		</div>
		
	</div>		 
<script>
$(document).ready(function() {
  //прикрепляем клик по заголовкам acc-head
	$('#accordeon .acc-head').on('click', f_acc);
});
 
function f_acc(){
//скрываем все кроме того, что должны открыть
  $('#accordeon .acc-body').not($(this).next()).slideUp(100);
// открываем или скрываем блок под заголовком, по которому кликнули
    $(this).next().slideToggle(300);
}



 
</script>
				 
				 
				 
				 
				<br/> 
 <button class="objects-bottom__btn btn btn_arrow">Сохранить<i></i></button>
 
			</div>
				
				
				</form>
			</div>

		
		</div>
	</div>
</section>
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
  
				
				
				
			 
 
 
	
 
 
<?
	   
	  
	  
 print '<pre>';
//print_r(  $GLOBALS[sss] );
 print '</pre>';
?>
<br> 
 
  
			 
 

	  
 
		 
  
</div>
 
 
			 <?