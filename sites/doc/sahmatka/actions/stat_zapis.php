<?	
$home[16]='602';
$home[3]='451';
$home[16]='602';
$home[3]='451';
$home[16]='602';
$home[3]='451';
$home[16]='602';
$home[3]='451';
$home[16]='602';
$home[3]='451';
$home[16]='602';
$home[3]='451';
$home[5]='Приозерный №1';
$home[8]='Приозерный №2'; //~!
$home[3]='451';
$home[7]='452'; //!
$home[6]='453';//!
$home[9]='Тюленина №1';
$home[10]='Тюленина №2';
$home[12]='603';
$home[15]='601'; //!
$home[16]='602'; //!
$home[17]='704'; //!
$home[19]='417'; //!
$home[14]='ЖК Залесский'; //!
$home[20]='808'; //!
$home[21]='807'; //!
$home[22]='509'; //!
$home[23]='806'; //!
$home[24]='510'; //!
$home[25]='804'; //!
$home[26]='805'; //!
$home[27]='801'; //!
$home[28]='802'; //!
$home[29]='803'; //!
$home[30]='809'; //!
$home[32]='810'; //!
$home['38']='811'; //!
 
 
  $q = ' SELECT zapis.* ,apartaments.floor, apartaments.rooms, apartaments.area ,
CASE WHEN `zapis`.`date` >= CURDATE() THEN "2" ELSE "1" END AS arhiv
FROM `zapis` LEFT JOIN  apartaments ON apartaments.home_id = zapis.home_id AND zapis.apartment_num = apartaments.apartment_num  where  del="0"';
 
 /*
if( !$_GET['arhiv'] ){ $q.=' AND `zapis`.`date` >= CURDATE()  '; }
else{  $q.=' AND `zapis`.`date` < CURDATE() AND `zapis`.`date` >"2021-06-01"'; }
 */
 

//$q = ' SELECT * FROM `zapis` where home_id = "16" ';
$q .= ' order by  date desc,time   LIMIT 0,51000'; 
$query = mysqli_query($connection,$q);
 
//print $q;
 
 
if (isset($_GET['del_id'])) { //проверяем, есть ли переменная на удаление
    //$sql = mysqli_query($connection,'DELETE FROM `zapis` WHERE `zapis_id` = '.$_GET['del_id']); //удаляем строку из таблицы
	
	if($_SESSION['sh_login']=='admin' || $_SESSION['sh_login']=='op15')
	{
		$sql = mysqli_query($connection,'UPDATE `zapis` SET `del` = "1" WHERE `zapis_id` = '.$_GET['del_id']); //удаляем строку из таблицы
		/* Пишем в лог кто удалил когда */
	}
}
 
 
 
ob_start();
?>
 	 
	 <style>
	 .table table .del td span{color:red; 	text-decoration: line-through;	text-decoration-color: red;}
	 </style>
		<table>
		<thead>
		<tr>
			<th>id</th>	 
			<th>Дата</th> 
			<th>Время</th>
			<th>Дом</th>
			<th>Секция</th>
			<th>Квартира</th>
			<th>Телефон</th>
			
			<th>С помогающей</th>
			<th>ФИО</th>
		
			<th   style="min-width:70px;"> </th>
		</tr>
		</thead>
		<tbody id="zapisdata">
		
		<?	
		while ($result = mysqli_fetch_array($query)) 
		{
			
		 if( $_GET['home'] && $_GET['home']!=$result['home_id'] ){ continue;  }
			 
		 
		  $homes_arr[$result['home_id']]++;
					  
		 
		  
		  if( $_GET['arhiv'] && $result['arhiv']!=1 ){ continue;  }
		  elseif( !$_GET['arhiv'] && $result['arhiv'] !=2){ continue;  }
		  
		  
		   $sections[$result['section']]++;
			$apartment_nums[$result['apartment_num']]++;
		   $dates[$result['date']]++;
		   
		  if($_GET['date'] && $_GET['date'] != $result['date'] ){continue;}
		  if($_GET['section'] && $_GET['section'] != $result['section'] ){continue;}
		  if($_GET['apartment_num'] && $_GET['apartment_num'] != $result['apartment_num'] ){continue;}
		  
 
			echo     '<tr>
					  <td>'.$result['zapis_id'].'</td>'.
					 '<td style="white-space: nowrap;">'.$result['date'].'</td>'.
					 '<td>'.$result['time'].'</td>'.
					 '<td>'.$home[$result['home_id']].'</td>'.
					 '<td>'.$result['section'].'</td>'.
					 '<td>№'.$result['apartment_num'].' ('.$result['floor'].'эт, '.$result['rooms'].'к, '.$result['area'].'м<sup>2</sup>)</td>' .
					 '<td>'.$result['phone'].'</td>' .
					 '<td>'.$result['fio'].'</td>' .

					 '<td>!<a  href="user.php?action=zapis&del_id='.$result['zapis_id'].'" style="color:red; display:none;">X</a></td>' ;	 
		}
		 
		
		// print_r($homes_arr);
		?>
		</tbody>
		</table>
		
		<div style="width:100%; max-width:100vw; text-align:center; padding:50px; display:none;" id="progressbar"  >
		<img src="loader.gif"  />
		</div>
 
		
 
<?
$content = ob_get_clean();
ksort($dates);
ksort($sections);
ksort($apartment_nums);
?>


	  
	  <?
	 // print_r($_SESSION);
	  ?>

<section class="section-stat">
	<div class="container mobc" style=" min-height: 100vh;">
		<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">Выдача ключей</span></div>
		</div>
<div style="text-align:right; width:100%; padding:20px; padding-left:0; padding-right:0;" class="add_buttons">
<a href="form_zapis_editor.php" class="btn_2  iframe_r" >Запись </a>
<?
if($_SESSION['sh_login']=='admin' || $_SESSION['sh_login']=='op15')
{
?>

<?
}
?>
</div>
		
		
		







		
		<div class="stat">
			<div class="stat-top stat-top_lp stat-top_key">
			
				<form method="get" action="user.php?action=zapis" id="filtrform" data-controller="zapiskeys">
				<input type="hidden" name = "get_arr" value=" Кодированная гет строка "/>
				<div class="stat-top-filter">
				
					<div class=" stat-top-item  stat-top-select stat-top-item_house">
					<select name="home" id="sel_home" data-placeholder="Объект" >
						<option value="">Объект</option>
						<? 
						foreach($homes_arr as $k=>$v)
						{
						?>
						<option value="<?=$k?>" <? if($_GET['home']==$k){print 'selected="selected"';}?>><?=$home[$k]?> (<?=$v?>)</option>
						<?
						}
						?>
					</select>
					</div>


					<div class="stat-top-item  stat-top-select  stat-top-item_house">	
						<select name="section" id="sel_section" data-placeholder="Секция">
							<option value="">Секция</option>
							<? 
							foreach($sections as $k=>$v)
							{
								if($k)
								{
								?>
								<option value="<?=$k?>" <? if($_GET['section']==$k){print 'selected="selected"';}?>><?=$k?> (<?=$v?>)</option>
								<?
								}
							}
							?>
						</select>
					</div>
					
					
					<div class=" stat-top-item stat-top-select stat-top-item_house">	
						<select name="apartment_num" id="sel_apartment_num"  placeholder="Секция">
							<option value="">Квартира</option>
							<? 
							foreach($apartment_nums as $k=>$v)
							{
								if($k)
								{
								?>
								<option value="<?=$k?>" <? if($_GET['section']==$k){print 'selected="selected"';}?>><?=$k?>  </option>
								<?
								}
							}
							?>
						</select>
					</div>
					
				
					
					<div class="stat-top-item    stat-top-select   stat-top-item_house"  >
					 <select name="date"  id="sel_date" data-placeholder="Дата">
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
 
				<div class="stat-top-item    stat-top-select   stat-top-item_house"      style="min-width: 100px; line-height: 3.2em; ">
					<input type="checkbox" id="show_dell" name="show_dell" value="1" <? if( $_GET['show_dell'] ){ print ' checked="checked" ';}?> /> <label for="show_dell">Удаленные</label>
					<input type="checkbox" id="pom" name="pom" value="1"> <label for="pom">С помогающей</label>
					<input type="checkbox" id="arhiv" name="arhiv" value="1"  > <label for="arhiv">Архив</label>
				</div>				 
				
				</div>
				
<input type="hidden" name="action" value="zapis">
  
</form>



				<a href="JavaScript:window.print();" class="stat-top__print"></a>
			</div>
			<div class="stat-table stat-table-user stat-table_notpd table">
			
			<?=$content?>
	 
			</div>
		</div>
	</div>
</section>



<script>



 
 
$( document ).ready(function() {
	
	
/*
resultto - ид тега для загрузки результата
formid - ид формы 
url - 
append - добавлять к содержимому ajax
*/
function sendAjaxForm(resultto, formid, url,append=1,progressid='progressbar') {
 
 
 if(!append){$('#'+resultto).html('');	}
 $('#'+progressid).show();
 	
			
    $.ajax({
        url:     url, //url страницы (action_ajax_form.php)
        type:     "POST", //метод отправки
        dataType: "html", //формат данных
        data: $("#"+formid).serialize(),  // Сеарилизуем объект
        success: function(response) { //Данные отправлены успешно
		 
        	// result = $.parseJSON(response);
        	//$('#'+resultto).html('Имя: '+result.name+'<br>Телефон: '+result.phonenumber);
			if(append)
			{
				$('#'+resultto).append(response);
			}
			else
			{
				$('#'+resultto).html(response);
			}
			$('#'+progressid).hide();
			
				// Перезагржаем фенси 
				 $('.iframe_rajax').magnificPopup({type:'iframe',
				  removalDelay: 100,
				  fixedContentPos: true, 
				  disableOn:1,
				   tLoading: 'Загрузка #%curr%...',
					callbacks: {
					open: function() {
					  // Will fire when this exact popup is opened
					  // this - is Magnific Popup object
					},
					close: function() {
						// Перезагрузить отображение!
						sendAjaxForm( 'zapisdata' , 'filtrform' , '/sahmatka/ajax_actions.php?load=data&controller=zapiskeys',0); // Грузим содержимое селек
					},
					open: function() {
						  location.href = location.href.split('#')[0] + "#pop";
						} 
					 
					// e.t.c.
				  }
				   
				  });
				//////////////////////////////////
  
  
  
    	},
    	error: function(response) { // Данные не отправлены
            // $('#'+resultto).html('Ошибка. Данные не отправлены.');
			 alert('ajax error');
			$('#'+progressid).hide();
    	}
 	});
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

  
  
  
  
}
	
	
/*
select_id_list = 'идселекта1,ид селекта2' - селекты которые необходимо перезагрузить 
*/
function relate_ajax_select(th,select_id_list,formid='filtrform')
{
	// Получаем массив селектов которые затрагивает данный 
	var array = select_id_list.split(",");
 
	// Цикл по массиву селектов
	array.forEach(function(item, i, arr) {
		// alert( i + ": " + item + " (массив:" + arr + ")" );
		 $("#"+item).prop('disabled', 'disabled'); // Блокируем селекты в которые предстоит загрузка  
		 $('#'+item).find('option[value!=""]').remove(); // Удаляем все НЕПУСТЫЕ опшены
	});
	//
	var controller= $('#'+formid).attr('data-controller');
	// Цикл по массиву селектов
	array.forEach(function(item, i, arr) {		 
		// alert(item);
		 sendAjaxForm( item , formid , '/sahmatka/ajax_actions.php?load='+item+'&controller='+ controller); // Грузим содержимое селек
		// РАЗБлокируем селекты в которые предстоит загрузка   
		  $("#"+item).prop('disabled', '');
	});
	//
}




$('#submitform').hide();


// Начальная загрузка данных 
relate_ajax_select('','sel_home,sel_section,sel_apartment_num,sel_date');
sendAjaxForm( 'zapisdata' , 'filtrform' , '/sahmatka/ajax_actions.php?load=data&controller=zapiskeys',0); // Грузим содержимое селек


$('#arhiv').change(function() {
 relate_ajax_select(this,'sel_home,sel_section,sel_apartment_num,sel_date');
});


$('#pom').change(function() {
 relate_ajax_select(this,'sel_home,sel_section,sel_apartment_num,sel_date');
});


$('#sel_home').on('change', function() {
 // alert( this.value );
 relate_ajax_select(this,'sel_section,sel_apartment_num,sel_date');
});


$('#sel_section').on('change', function() {
 // alert( this.value );
 relate_ajax_select(this,'sel_apartment_num,sel_date');
});

 
 
$('#sel_apartment_num').on('change', function() {
 // alert( this.value );
 relate_ajax_select(this,'sel_date');
});

 
 


 
 

// ЗАГРУЗКА ДАННЫХ ПРИЛЮБОЙ ОБРАБОТКЕ ФОРМЫ!
$( "#filtrform input,#filtrform select" ).change(function() {
  sendAjaxForm( 'zapisdata' , 'filtrform' , '<?=$GLOBALS['config']['domains']['em']?>/sahmatka/ajax_actions.php?load=data&controller=zapiskeys',0); // Грузим содержимое селек
});


});
</script>









 
 



	



<?

/*
https://qna.habr.com/q/414317 - ОТЛИЧНЫЕ СЕЛЕКТЫ КОМБОБУКСЫ


$.ajaxSetup({cache: false}); 
 
 
		
		*/
	 