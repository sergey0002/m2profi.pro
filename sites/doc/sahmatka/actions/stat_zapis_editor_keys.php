<?	
		if(!$_GET['month']){	$_GET['month'] = date('m');	}
		$tm = $_GET['month'];	
?>




<section class="section-stat">
	<div class="container mobc" style=" min-height: 100vh;">
		<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">Расписание выдачи ключей</span></div>
		</div>
		 
	
	
	


		<div class="stat">
			 
			<div class="stat-table stat-table-user stat-table_notpd table">
		 
		 
		 
		 	<?
			if( $_POST )
			{
				
				
				
				# Сохраняем данные
				/// По обектам (places)
				foreach( $_POST['сapacity'] as $k=>$v )
				{
					foreach($v as $k2=>$v2) // ДАТЫ
					{
						foreach($v2 as $k3=>$v3) // Время
						{
							// insert_or update
							$mysql->sql( ' DELETE FROM `keys_grafic` WHERE `place_id` = "'.$k.'"  AND `date` = "'.$k2.'" AND  `time` = "'.$k3.'" ');
							if($v3 > 0) 
							{
								// ДОбавляем новую запись
								$mysql->sql( ' INSERT INTO `keys_grafic` (`place_id`, `date`, `time`, `сapacity`) VALUES ("'.$k.'", "'.$k2.'", "'.$k3.'", "'.$v3.'"); ');
								// print  ' INSERT INTO `keys_grafic` (`place_id`, `date`, `time`, `сapacity`) VALUES ("'.$k.'", "'.$k2.'", "'.$k3.'", "'.$v3.'"); <br/>';
							}
						}
					}
				}
			}
			?>
			
			
			
			
	<?
		
 		 $q = 'SELECT * FROM `homes` WHERE `show` = "1" AND show_keys="1"  ';

		 $q .= ' ORDER BY `order` ; ';	
		 $h = $mysql->get_arr($q);
 
		//print_r($h);
	?>
	<div style="display:none;   padding-right:0; padding-left:0; min-height:auto;     margin-bottom: 0;    margin-top: 15px;" class="page-header">	 
			 <ul class="mmenu">
				<?
				foreach($h as $k=>$v)
				{
					if( $_GET['home'] == $v['home_id'] )
					{
						$style=' style=" color:#ff0000; font-size: 18px;  font-weight:bold;  " '; 
					}
					else
					{ 
						$style=' style= " color:#000;    font-size: 18px;  font-weight:bold;   '; 
						if($v['show']==2){ $style.='   color:#FFA500;  ';  }// ТОлько админам
						elseif($v['show']==3){ $style.='   color:#999999;  ';  } // Админам и отделу продаж
						else{$style.='  color:#000;  '; }
						$style.='" ';
					}
					?>
					  <li><a href="user.php?action=zapis_editor_keys&home=<?=$v['home_id']?>&month=<?=$_GET['month']?>" <?=$style?> class="stat-top-items__item"> <?=$v['title']?> </a>   </li>
					<?
				}
				?>  		
			</ul>
	</div>
	
	
	
	
	
			
			
			
			
		<?
		if(!$_GET['month']){	$tm = date('m');	}	
		else{	$tm = $_GET['month'];	}	
		
		if(!$_GET['year']){	$ty = date('Y');	}
		else{	$ty = $_GET['year'];	}	
						
		$y=date('Y');

		
		print '<b>Месяц:</b> ';
		for( $m=1; $m<=12; $m++ )
		{
			if($m==$tm && $y==$ty){$st=' style="font-weight:bold; font-size: 16px;" ';}
			else{$st='';}
			if(strlen($m)<2){$m = '0'.$m;} 
			print ' / <a href="<?=$GLOBALS['config']['domains']['em']?>/sahmatka/user.php?action=zapis_editor_keys&month='.$m.'" '.$st.'>'.$m.'</a>  ';
		}
		
		$m='01';
	 
		$y = date('Y')+1;
		if($m==$tm && $y==$ty){$st=' style="font-weight:bold; font-size: 16px;" ';  }
		else{$st='';}
		
		print ' / <a href="<?=$GLOBALS['config']['domains']['em']?>/sahmatka/user.php?action=zapis_editor_keys&month=01&year='.$y.'" '.$st.'>01-'.$y.'</a>  ';
		
		
		print '<br/><br/>';
	    ?>
		
		 
	
	
			
			<?
			$x = new gantt2();	
 
			$x->time_intervals=array();
			$x->time_intervals['09:00']='1';
			$x->time_intervals['10:00']='1';
			$x->time_intervals['11:00']='1';
			$x->time_intervals['13:30']='1';
			$x->time_intervals['14:30']='1';
			$x->time_intervals['15:30']='1';
			$x->time_intervals['16:30']='1';
		
			
			  $data_str[26]['caption'] = '805';
		// НЕпосредственно при добавлении формы 
		//	$data_str['all']['caption'] = '805, 806, 807,808, 809, 810, 704';
		    $data_str['22']['caption'] = '509';
			$data_str['27']['caption'] = '801';
		
			
			$data_str['28']['caption'] = '802';
			$data_str['29']['caption'] = '803'; 
			
			
		   	$data_str['31']['caption'] = '816';
			
			$data_str['26']['caption'] = '805';
			
			
			
					 
		// 	$data_str['27-28']['caption'] = '801+802';
		 	$data_str['31']['caption'] = '816';

			
			//$data_str['509-1']['id'] = '509-1';
			//$data_str['509-2']['caption'] = '509 - секция №2';
			//$data_str['509-2']['id'] = '509-2';
			//$data_str['509-3']['caption'] = '509 - секция №3';
			//$data_str['509-3']['id'] = '509-3';
			
			//$data_str['27']['caption'] = '801';
			//$data_str[28]['caption'] = '802';
			//$data_str[26]['caption'] = '805';
			//$data_str[23]['caption'] = '806';
			//$data_str[21]['caption'] = '807';
			//$data_str[20]['caption'] = '808';
			//$data_str[30]['caption'] = '809';
			//$data_str[32]['caption'] = '810';
			//$data_str[38]['caption'] = '811';
		//	$data_str[31]['caption'] = '816';
 
			$data = $mysql->get_arr('SELECT * FROM keys_grafic ');

			// Формируем массив id[дата][время]='вся инфа'
			foreach( $data as $k => $v )
			{
				$data_broni[$v['place_id']][$v['date']][$v['time']] = $v;
			}
			
			 ?>
			 <form action="<?=$GLOBALS['config']['domains']['em']?>/sahmatka/user.php?action=zapis_editor_keys&month=<?=$_GET['month']?>&year=<?=$_GET['year']?>" method="POST" id="ajax_form">
			 <?
			$x->show($data_broni,$data_str);
			
			?>
			
			<br/>
			<input type="submit" value="Сохранить" /><br/><br/>
			
			
			
			</form>
			
			
			
<script>
/*	
<span id="btn">123</span><br/><br/>
<div id="result_form"></div>

$( document ).ready(function() {
    $("#btn").click(
        function(){
            sendAjaxForm('result_form', 'ajax_form', 'ajax_router.php?ctr=zapiskeys&act=mountheditor');
            return false; 
        }
    );
});

function sendAjaxForm(result_form, ajax_form, url) {
    jQuery.ajax({
        url:     url, //url страницы (action_ajax_form.php)
        type:     "POST", //метод отправки
        dataType: "html", //формат данных
        data: jQuery("#"+ajax_form).serialize(),  // Сеарилизуем объект
        success: function(response) { //Данные отправлены успешно
            
            document.getElementById(result_form).innerHTML = response;
        },
        error: function(response) { // Данные не отправлены
            document.getElementById(result_form).innerHTML = "Ошибка. Данные не отправленны.";
        }
    });
}
*/
</script>
			
		
	 
			</div>
		</div>
	</div>
</section>


 