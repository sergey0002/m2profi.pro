 
			
 
	





		
		 
		
<?
  
	if (isset($_GET['del_id'])) { //проверяем, есть ли переменная на удаление
      //  $sql = mysqli_query($connection,'DELETE FROM `excurs` WHERE `zapis_id` = '.$_GET['del_id']); //удаляем строку из таблицы
		$sql = mysqli_query($connection,'UPDATE `excurs` SET `del` = "1" WHERE `zapis_id` = '.$_GET['del_id']); //удаляем строку из таблицы
    }
 
$objects[1]='Приозерный';
$objects[2]='Родники';
$objects[3]='ЖК Залесский';
$objects[4]='Cерия GREEN';
$objects[44]='Cерия GREEN (кирп вставка)';
$objects[45]='704 - 6 секция';
$objects[46]='Шоу-рум';
$objects[38]='811';
$objects[200]='Автобусный тур';
 
$q = ' (SELECT * FROM `excurs` where del=0 AND 1=1 ';
 
if( !$_GET['arhiv'] ){ $q.=' AND `excurs`.`date` >= CURDATE() '; }
else{  $q.=' AND `excurs`.`date` < CURDATE() '; }

$q.=' )   ORDER BY `date` DESC,`time` DESC LIMIT 0,50000; '; 
  
  
  
//  print $q;
$query = mysqli_query($connection,$q);


//print $q;
ob_start();

 //print $q;
		 ?>

		<table>
		<thead>
		<tr>
			<th>id</th>	 
			<th>Дата экскурсии</th> 
			<th>Время <br/>экскурсии</th>
			<th style="max-width:200px;">ФИО</th>
			<th style="max-width:200px;">Сообщение</th>
			<th>Телефон</th>
			<th>Объект</th>
			<th>Дата записи</th>
			<th>Человек</th>
			<th>Менеджер</th>
			<th></th>
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
					 '<td style="white-space: nowrap;">'.$result['date'].'</td>'.
					  '<td>'.$result['time'].'</td>'.
   '<td style="max-width:200px;">'.$result['name'].'</td>'.
					 '<td style="max-width:200px;">'.$result['message'].'</td>'.
					 '<td>'.$result['phone'].'</td>' .
				
'<td>'.$objects[$result['home']].'</td>'  .
'<td>'.$result['datetime'].'</td>'   .
'<td>'.$result['peoples'].'</td>' .
'<td>'.$result['manager'].'</td>' .
'<td><a href="user.php?action=exc_zapis&del_id='.$result['zapis_id'].'" style="color:red;">X</a></td>' ;
	 
		}
		?>
		</table>
		 
	
		<?
		$content = ob_get_clean();
		?>
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
 
<section class="section-stat">
	<div class="container mobc" style=" min-height: 100vh;">
		<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">Экскурсии</span></div>
		</div>
		 
		
	<div style="text-align:right; width:100%;" class="hm">
	 <a href="print_zapis_exc.php?date=<?=$_GET['date']?>&object=<?=$_GET['object']?>&arhiv=<?=$_GET['arhiv']?>">Печать</a>
 	 </div>
	  
	  
	  
	  
	 <div style="text-align:right; width:100%; padding:20px; padding-right:0;">

 
 <a class="btn_arrow iframe_r mw100" style="margin-left:0;" href="form_exc_backoffice.php"> Запись на экскурсию</a>
		
		</div>
		 
		
		<div class="stat">
			<div class="stat-top stat-top_lp stat-top_key">
			
				<form method="get" action="user.php?action=zapis_exc" id="filtrform">
				<div class="stat-top-filter">
					<div class="stat-top-item  stat-top-select stat-top-item_house  "  >
					 <select name="date" data-placeholder="Дата">
							<option value="">Дата</option>
							<? 
							foreach($dates as $k=>$v)
							{
							?>
							<option value="<?=$k?>" <? if($_GET['date']==$k){print 'selected="selected"';}?>> <?=$k?> (<?=$v?>)</option>
							<?
							}
							?>
						</select>
					 
					 
					 
					</div>
					<div class="stat-top-item  stat-top-select stat-top-item_house ">
					<select name="home" data-placeholder="Объект">
						<option value="">Объект</option>
				 
							<? 
							foreach($objectsx as $k=>$v)
							{
							?>
							<option value="<?=$k?>" <? if($_GET['home']==$k){print 'selected="selected"';}?>><?=$objects[$k]?></option>
							<?
							} 
							?>
					</select>

					</div>
		 
					<div class="stat-top-item  stat-top-select stat-top-item_house " style="    min-width: 100px; line-height: 3.2em;">
						<input type="checkbox" id="arhiv" name="arhiv" value="1" <? if( $_GET['arhiv'] ){ print ' checked="checked" ';}?> /> <label for="arhiv">Архив</label>
					</div>



					<a href="#" class="stat-top-btn btn btn_arrow-long" onclick="document.getElementById('filtrform').submit(); return false;">Выбрать <i></i></a>
				</div>
				
				
				
				
				
<input type="hidden" name="action" value="exc_zapis">
  
</form>



				<a href="JavaScript:window.print();" class="stat-top__print"></a>
			</div>
			<div class="stat-table stat-table-user stat-table_notpd table">
			
			<?=$content?>
	 
			</div>
		</div>
	</div>
</section>














 
		<?
		 