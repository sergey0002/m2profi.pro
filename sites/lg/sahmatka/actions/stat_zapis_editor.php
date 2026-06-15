<?	

 

?>



<section class="section-stat">
	<div class="container mobc" style=" min-height: 100vh;">
		<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">Расписание экскурсий</span></div>
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
							$mysql->sql( ' DELETE FROM `excurs_grafic` WHERE `place_id` = "'.$k.'"  AND `date` = "'.$k2.'" AND  `time` = "'.$k3.'" ');
							if($v3 > 0) 
							{
								// ДОбавляем новую запись
								$mysql->sql( ' INSERT INTO `excurs_grafic` (`place_id`, `date`, `time`, `сapacity`) VALUES ("'.$k.'", "'.$k2.'", "'.$k3.'", "'.$v3.'"); ');
								//print  ' INSERT INTO `excurs_grafic` (`place_id`, `date`, `time`, `сapacity`) VALUES ("'.$k.'", "'.$k2.'", "'.$k3.'", "'.$v3.'"); <br/>';
							}
						}
					}
				}
			}
			?>
			
			
			
			
			
			
			
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
			print ' / <a href="https://{$GLOBALS['config']['domain']}/sahmatka/user.php?action=zapis_editor&month='.$m.'" '.$st.'>'.$m.'</a>  ';
		}
		
		$m='01';
	 
		$y = date('Y')+1;
		if($m==$tm && $y==$ty){$st=' style="font-weight:bold; font-size: 16px;" ';  }
		else{$st='';}
		
		print ' / <a href="https://{$GLOBALS['config']['domain']}/sahmatka/user.php?action=zapis_editor&month=01&year='.$y.'" '.$st.'>01-'.$y.'</a>  ';
		
		
		print '<br/><br/>';
	    ?>
	
	
	
			
			<?
			$x = new gantt2();		
			
			$data_str[46]['caption'] = 'Шоурум';
			$data_str[46]['id'] = '46';

			$data_str[1]['caption'] = '704 Приоерный';
			$data_str[1]['id'] = '1';
			
			
			
			$data_str[38]['caption'] = '811';
			$data_str[38]['id'] = '38';
			
			
			
			$data = $mysql->get_arr('SELECT * FROM excurs_grafic ');

			// Формируем массив id[дата][время]='вся инфа'
			foreach( $data as $k => $v )
			{
				$data_broni[$v['place_id']][$v['date']][$v['time']] = $v;
			}
			
			?>
			<form action="https://{$GLOBALS['config']['domain']}/sahmatka/user.php?action=zapis_editor&month=<?=$_GET['month']?>&year=<?=$_GET['year']?>" method="POST">
			<?
			$x->show($data_broni,$data_str);
			?>
			
			<br/>
			<input type="submit" value="Сохранить" />
			
			</form>
			
			
		
	 
			</div>
		</div>
	</div>
</section>


 