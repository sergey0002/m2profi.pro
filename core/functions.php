<?
$start = microtime(true);
function t($txt='')
{
	global $start;
	global $tlog;
 
	$tlog[]= $txt.' - Время выполнения скрипта: '.round(microtime(true) - $start, 4).' сек.<br/>	';
}

// Добавление в шаблон переменных 
function tpl_add($str)
{
	global $tpl_vars;
	$tpl_vars['head'][]=$str;
	
}


// вывод переменных шаблона 
function tpl_disp($name)
{
	global $tpl_vars;
	foreach($tpl_vars[$name] as $k=>$v)
	{
		print $v;
	}
}





function add_logx($s)
{
    $GLOBALS['log'][] = $s;
}

if (!function_exists('check_access')) {
	/**
	 * Проверка роли по логину сессии. Список admin_logins — в $GLOBALS['config']['admin_logins'].
	 * Без конфига на домене: только логин admin (обратная совместимость).
	 */
	function check_access($role = 'admin', $login = null)
	{
		if ($login === null) {
			$login = isset($_SESSION['sh_login']) ? (string) $_SESSION['sh_login'] : '';
		}
		if ($role !== 'admin') {
			return false;
		}
		$list = array('admin');
		if (!empty($GLOBALS['config']['admin_logins']) && is_array($GLOBALS['config']['admin_logins'])) {
			$list = $GLOBALS['config']['admin_logins'];
		}
		return in_array($login, $list, true);
	}
}

# Склонение числительных
// $titles = array('Сидит %d котик', 'Сидят %d котика', 'Сидит %d котиков');
function declOfNum($number, $titles)
{
    $cases = array(
        2,
        0,
        1,
        1,
        1,
        2
    );
    $format = $titles[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5) ]];
    return sprintf($format, $number);
}
 
 
 

if(!function_exists('curPageURL'))
{
	function curPageURL() 
	{
		 $pageURL = 'http';
		 if (isset($_SERVER["HTTPS"]) &&  $_SERVER["HTTPS"]== "on") {$pageURL .= "s";}
		 $pageURL .= "://";
		 if ($_SERVER["SERVER_PORT"] != "80") {
		  $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		 } else {
		  $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		 }
		 return $pageURL;
	}
}
$url =  curPageURL();






if(!function_exists('add_log'))
{
	// ДОбавляем запись в лог 
	function add_log($action)
	{
		$connection = $GLOBALS['connection'];
		$users_id = $_SESSION['sh_id'];
		$query = "INSERT INTO `users_stat` (`users_id`, `date`, `action`) VALUES ('$users_id', now(), '$action')"; // Создаем переменную с запросом к базе данных на отправку нового юзера
		$result = mysqli_query($connection, $query) or die(print mysqli_connect_error()); // Отправляем переменную с запросом в базу данных 
	}
}






if(!function_exists('XMail'))
{
	function XMail( $from, $to, $subj, $text, $files='')
	{
	$un = strtoupper(uniqid(time()));
	$head = "From: $from\n";
	$head .= "To: $to\n";
	$head .= "Subject: $subj\n";
	$head .= "X-Mailer: PHPMail Tool\n";
	$head .= "Reply-To: $from\n";
	$head .= "Mime-Version: 1.0\n";
	$head .= "Content-Type:multipart/mixed;";
	$head .= "boundary=\"----------".$un."\"\n\n";

	//$zag = "------------".$un."\nContent-Type:text/html;\n";

	$text = iconv('utf-8','windows-1251',$text);

	$zag = "------------".$un."\nContent-Type: text/html; charset=windows-1251\r\n";
	$zag .= "Content-Transfer-Encoding: 8bit\n\n$text\n\n";

	if($files)
	{
		foreach($files as $key=>$value) 
		{
		$filename = $value;
		$f = fopen($filename,"rb");
		 
		$zag .= "------------".$un."\n";
		$zag .= "Content-Type: application/octet-stream;";
		$zag .= "name=\"".basename($filename)."\"\n";
		$zag .= "Content-Transfer-Encoding:base64\n";
		$zag .= "Content-Disposition:attachment;";
		$zag .= "filename=\"".basename($filename)."\"\n\n";
		$zag .= chunk_split(base64_encode(fread($f,filesize($filename))))."\n";
		}
	}


	if (!@mail("$to", "$subj", $zag, $head))
	return 0;
	else
	return 1;
	}
}








//template_apartament - шаблон аппартамента (инклайд)
/*
$conf - Массив конфиг квартир
$home - дом/район номер
$data - данные о бронировании
$template_apartament
*/
function diaplay_home_print( $config , $home, $section, $data=array() , $editurl='')
{ 
 
$data = $data[$home][$section];
$conf = $config[$home][$section];
 
 $ckv=0;
 // Количество пустых квартир вычисляем
 if( $conf['clean_apartments'] &&  is_Array($conf['clean_apartments'] ) )
 {
		foreach( $conf['clean_apartments'] as $k=>$v)
		{
			foreach($v as $k2=>$v2)
			{
				$ckv++;
			}
		}
 }
  
 # автонумерация квартир
 // вычисляем последнюю 
 //количество этажей * квартир на этаже + стартовый номер квартиры в секции ( НУмеруем в другую сторону! )
 $endnum = ($conf['floor']*$conf['apartments']) + $conf['start_num']-$ckv; // Количество квартир в секции
 
  
 
 	?>
	<div style="display:inline-block; margin-left:10px;">
  
    <table class="home_sh2" cellpadding="0" cellspacing ="0" width="100%" border=1 >
	<tr><td colspan="100" style="font-size:10px;"><b><?=$conf[caption]?></b></td></tr>
	  
	<?
	// Цикл по строкам (количество этажей)
	for($i=$conf['floor']; $i>0; $i--)
	{
	
		// количество нежилых квартир на этаже
		$nezk = count($conf['clean_apartments'][$i]); 

		$end_etza_num = $endnum-$conf['apartments']-1+$nezk; // стартовый номер для этажа (- квартир на этаже СО СТАТУСОМ ЕСТЬ КВАРТИРА!)
		// РАБОЧИЙ ПОРЯДКОВЫЙ НОМЕР НА ЭТАЖЕ
 
		if(check_access('admin') && $i==$conf['floor'])
		{
			  print '<tr>';
			// Цикл по столбам (количество квартир шапка)
			for($k=0; $k<=$conf['apartments']; $k++)
			{
				print '<th style="text-align:center;">';
				print '</th>';
			}
			  print '</tr>';
		}		
		print '<tr>';
		
		// Цикл по столбам (количество квартир)
		for($k=0; $k<=$conf['apartments']; $k++)
		{
 
			### Запрашиваем конфиг номера квартиры 
			/*
			Если для данной секции для этажа указан номер этой квартиры , если указанно что тут нет квартиры!
			*/
			if( $conf['clean_apartments'][$i][$k] )
			{
				
			}
			elseif( $conf['spn_apartments'][$i][$k] )
			{
			//	$end_etza_num = $conf['spn_apartments'][$i][$k];
				 $end_etza_num++;
			}
			else
			{
				$end_etza_num++;
			}
			
			// нулевой столбей номер этажа
			if($k==0)
			{
				print '<td style="font-size:8px;">';
				print  $i;
				print '</td>';
			}
			
			else // ИНфа о квартире
			{
				 $tdst='';
				// граница поседней квартиры на этаже
				if($k==$conf['apartments'])
				{
					$tdst.=' border-right:2px solid #5d6c74; ';
				}
				elseif($k==1)
				{
					$tdst.=' border-left:2px solid #5d6c74; ';
				}
				if($i==1)
				{
					 $tdst.=' border-bottom:2px solid #5d6c74; ';
				}
				
				
				
				### ИНФОРМАЦИЯ О КВАРТИРЕ
				$type= $conf['types'][$k-1]; // тип
				$size = $conf['types_s'][$k]; // площадь
			//	print_r($conf['types_s'][$k]);
			
			
			    // Для ссылок редактирования !!! 19 дома 
				if($_GET[home]=='19'){$end_etza_num2='№ '.$conf['spn_apartments'][$i][$k]; $end_etza_num=$conf['spn_apartments'][$i][$k];}
				
				  
				$apart = $GLOBALS['data_apart'][$home][$section][$end_etza_num];
			 	 if(!$apart[rooms]){$apart[rooms]='';}
				  if(!$apart[price]){$apart[price]='';}

				if( !$data[$i][$k] ){$data[$i][$k][status]='2';} // по умолчанию свободны


// СТАТУСЫ СЕКРЕТНЫЕ ТОЛЬКО АДМИНУ
if( !check_access('admin') && $_SESSION['sh_login'] != 'em_nsv')
{
	if($data[$i][$k][status]!=2 && $data[$i][$k][status]!=3 && $data[$i][$k][status]!=4)
	{
		$data[$i][$k][status]=3;
	}
}
				if($conf['clean_apartments'][$i][$k])
				{
					$status_text = 'Нет квартиры';
				 
					$value='';
					
				}
				///*[status] - 0 -нет информации 1 - нет квартиры 2 - свободна 3 куплена 4 -бронь */
				elseif($data[$i][$k][status]=='0' || $data[$i][$k][status]=='1' || !$data[$i][$k] ) // Нет информации
				{
					$end_etza_num=1;
					$status_text = ' нет данных ';
					$tdst.=' background-color:#5d6c74;  ';	
					$value='<b>№ '.$end_etza_num.'</b>';
					$value='<b>№ '.$end_etza_num.'</b>';
					
					
				} 
				elseif($data[$i][$k][status]=='2')
				{
					
					if($_GET[home]=='19')
					{
						$end_etza_num2 = '№ '.$end_etza_num;
					}
					else{$end_etza_num2 = '№ '.$end_etza_num;}
					
					$tdst.=' background-color:#00FF00;   ';	
					$status_text = '<b>Свободна</b> <br/> Комнат: '. $apart[rooms].' <br/>   Площадь: '. $apart[area].'м<sup>2</sup> <br/> Цена: '. $apart[price].'р. 
				 
					';
			 
						$tdst.='  ';
						$value=' <b> '.$end_etza_num2.'</b> 
	 
						<br/> <span style="font-size:8px;">'.$apart[area].' <br/> '.$apart[price].'</span> ';
			
					 
				}
				elseif($data[$i][$k][status]=='3')
				{
					if($_GET[home]=='19'){$end_etza_num2='#';}
					else{$end_etza_num2 = '№ '.$end_etza_num;}
 
					$status_text = 'Продана';
					$tdst.=' background-color:#FF8A90;   ';	
					 	$value=' <b>№ '.$end_etza_num.'</b> ';
					 
						$value=' <b> '.$end_etza_num2.'</b> 
 
						<br/> <span style="font-size:8px;">'.$apart[area].' <br/> '.$apart[price].'</span> ';

						  //print_r($apart);
					 
				}
				elseif($data[$i][$k][status]=='5')
				{
					if($_GET[home]=='19'){$end_etza_num2='#';}
					else{$end_etza_num2 = '№ '.$end_etza_num;}
					
				 
						 
						$status_text = 'Забронирована застройщиком';
						$tdst.=' background-color:#D4E6FF;   ';	
						$value=' <b> '.$end_etza_num2.'</b></a>
 
						<br/> <span style="font-size:8px;">'.$apart[area].' <br/> '.$apart[price].'</span> ';
					 
				}
				elseif($data[$i][$k][status]=='4')
				{
						$value=' <b>№ '.$end_etza_num.'</b> ';
						 
						 
							$tdst.=' background-color:#FFFF3E; color:#000; ';	
							$value=' <b>№ '.$end_etza_num.'</b> 
							<br/> <span style="font-size:8px;">'.$apart[area].' <br/> '.$apart[price].'</span> 
							';
							$status_text = 'Бронь '.$data[$i][$k][date] . '<br>' . $data[$i][$k][login] . '<br>'. $data[$i][$k][name];
							$status_text .=' <br/><b>'.$data[$i][$k][agency_caption].'</b>';
						  
				}
				
				elseif($data[$i][$k][status]=='6')
				{
						$status_text = 'Квартира подрядчика';
						$tdst.=' background-color:#9933ff;   ';	
						$value=' <b>№ '.$end_etza_num.'</b> 
					 	<br><span style="font-size:8px;">'.$apart[area].' <br/> '.$apart[price].'</span> '; 
				}
				
				
				if($conf['clean_apartments'][$i][$k]) // пустая квартира
				{
						print '<td>&nbsp;</td>'; 
				}
				else
				{
					print '<td align="center" >';
					print '<div style="    -webkit-print-color-adjust: exact; '.$tdst.'  border: solid 1px #FFF; font-size:8px;  padding: 3px;" title="'.$status_text.'    " class="tdapartment" rel="tooltip">';
					print $value;
					print '</div>';
					print '</td>';
					
					$endnum--; // вытонумерация квартир
					
				}
				
				
			}
		}
		print '</tr>';
	}
		
print ' </table>
</div>

';
 if($_GET['home']==19)
 {
	print  $sss;
 }
}










//template_apartament - шаблон аппартамента (инклайд)
/*
$conf - Массив конфиг квартир
$home - дом/район номер
$data - данные о бронировании
$template_apartament
*/
function diaplay_home_print_dev( $config , $home, $section, $data=array() , $editurl='')
{ 
 
$data = $data[$home][$section];
$conf = $config[$home][$section];
 
 $ckv=0;
 // Количество пустых квартир вычисляем
 if( $conf['clean_apartments'] &&  is_Array($conf['clean_apartments'] ) )
 {
		foreach( $conf['clean_apartments'] as $k=>$v)
		{
			foreach($v as $k2=>$v2)
			{
				$ckv++;
			}
		}
 }
  
 # автонумерация квартир
 // вычисляем последнюю 
 //количество этажей * квартир на этаже + стартовый номер квартиры в секции ( НУмеруем в другую сторону! )
 $endnum = ($conf['floor']*$conf['apartments']) + $conf['start_num']-$ckv; // Количество квартир в секции
 
  
 
 	?>
	<div style="display:inline-block; margin-left:10px;">
  
    <table class="home_sh2" cellpadding="0" cellspacing ="0" width="100%" border=1 >
	<tr><td colspan="100" style="font-size:10px;"><b><?=$conf[caption]?></b></td></tr>
	  
	<?
	// Цикл по строкам (количество этажей)
	for($i=$conf['floor']; $i>0; $i--)
	{
	
		// количество нежилых квартир на этаже
		$nezk = count($conf['clean_apartments'][$i]); 

		$end_etza_num = $endnum-$conf['apartments']-1+$nezk; // стартовый номер для этажа (- квартир на этаже СО СТАТУСОМ ЕСТЬ КВАРТИРА!)
		// РАБОЧИЙ ПОРЯДКОВЫЙ НОМЕР НА ЭТАЖЕ
 
		if(check_access('admin') && $i==$conf['floor'])
		{
			print '<tr>';
			// Цикл по столбам (количество квартир шапка)
			for($k=0; $k<=$conf['apartments']; $k++)
			{
				print '<th style="text-align:center;">';
				print '</th>';
			}
		    print '</tr>';
		}		
		print '<tr>';
		
		// Цикл по столбам (количество квартир)
		for($k=0; $k<=$conf['apartments']; $k++)
		{
 
			### Запрашиваем конфиг номера квартиры 
			/*
			Если для данной секции для этажа указан номер этой квартиры , если указанно что тут нет квартиры!
			*/
			if( !$conf['clean_apartments'][$i][$k] )
			{
				 $end_etza_num++;
			}
			  
			
			// нулевой столбей номер этажа
			if($k==0)
			{
				print '<td style="font-size:8px;">';
				print  $i;
				print '</td>';
			}
			
			else // ИНфа о квартире
			{
	  			### ИНФОРМАЦИЯ О КВАРТИРЕ
				$type= $conf['types'][$k-1]; // тип
				$size = $conf['types_s'][$k]; // площадь
			//	print_r($conf['types_s'][$k]);
			
			 
				
				 //Данные по квартирам (площади итп)
				$apart = $GLOBALS['data_apart'][$home][$section][$end_etza_num];
			 	if(!$apart[rooms]){$apart[rooms]='';}
				if(!$apart[price]){$apart[price]='';}

				if( !$data[$i][$k] ){$data[$i][$k][status]='2';} // по умолчанию свободны


// СТАТУСЫ СЕКРЕТНЫЕ ТОЛЬКО АДМИНУ
if( !check_access('admin') && $_SESSION['sh_login'] != 'em_nsv')
{
	if($data[$i][$k][status]!=2 && $data[$i][$k][status]!=3 && $data[$i][$k][status]!=4)
	{
		$data[$i][$k][status]=3;
	}
}
				if($conf['clean_apartments'][$i][$k]) # НЕТ КВАРТИРЫ
				{
					$status_text = '';
					$value='';
				}
				///*[status] - 0 -нет информации 1 - нет квартиры 2 - свободна 3 куплена 4 -бронь */
				elseif($data[$i][$k][status]=='0' || $data[$i][$k][status]=='1' || !$data[$i][$k] ) // НЕТ ИНФОРМАЦИИ О КВАРТИРЕ
				{
					$status_text = ' нет данных ';
					$tdst.=' background-color:#5d6c74;  ';	
					$value='<b>№ '.$end_etza_num.'</b>';
					$value='<b>№ '.$end_etza_num.'</b>';
					
					
				} 
				elseif($data[$i][$k][status]=='2')
				{
					$end_etza_num2 = '№ '.$end_etza_num; 
					$tdst.=' background-color:#00FF00;   ';	
					$status_text = '<b>Свободна</b> <br/> Комнат: '. $apart[rooms].' <br/>   Площадь: '. $apart[area].'м<sup>2</sup> <br/> Цена: '. $apart[price].'р. ';
					$tdst.='  ';
					$value=' <b> '.$end_etza_num2.'</b> 
					<br/> <span style="font-size:8px;">'.$apart[area].' <br/> '.$apart[price].'</span> ';	 
				}
				elseif($data[$i][$k][status]=='3')
				{
					 
					 $end_etza_num2 = '№ '.$end_etza_num; 
 
					$status_text = 'Продана';
					$tdst.=' background-color:#FF8A90;   ';	
					 	$value=' <b>№ '.$end_etza_num.'</b> ';
					 
						$value=' <b> '.$end_etza_num2.'</b> 
 
						<br/> <span style="font-size:8px;">'.$apart[area].' <br/> '.$apart[price].'</span> ';

						  //print_r($apart);
					 
				}
				elseif($data[$i][$k][status]=='5')
				{
					if($_GET[home]=='19'){$end_etza_num2='#';}
					else{$end_etza_num2 = '№ '.$end_etza_num;}
					
				 
						 
						$status_text = 'Забронирована застройщиком';
						$tdst.=' background-color:#D4E6FF;   ';	
						$value=' <b> '.$end_etza_num2.'</b></a>
 
						<br/> <span style="font-size:8px;">'.$apart[area].' <br/> '.$apart[price].'</span> ';
					 
				}
				elseif($data[$i][$k][status]=='4')
				{
						$value=' <b>№ '.$end_etza_num.'</b> ';
						 
						 
							$tdst.=' background-color:#FFFF3E; color:#000; ';	
							$value=' <b>№ '.$end_etza_num.'</b> 
							<br/> <span style="font-size:8px;">'.$apart[area].' <br/> '.$apart[price].'</span> 
							';
							$status_text = 'Бронь '.$data[$i][$k][date] . '<br>' . $data[$i][$k][login] . '<br>'. $data[$i][$k][name];
							$status_text .=' <br/><b>'.$data[$i][$k][agency_caption].'</b>';
						  
				}
				
				elseif($data[$i][$k][status]=='6')
				{
						$status_text = 'Квартира подрядчика';
						$tdst.=' background-color:#9933ff;   ';	
						$value=' <b>№ '.$end_etza_num.'</b> 
					 	<br><span style="font-size:8px;">'.$apart[area].' <br/> '.$apart[price].'</span> '; 
				}
				
				
				if($conf['clean_apartments'][$i][$k]) // пустая квартира
				{
						print '<td>&nbsp;</td>'; 
						
				}
				else
				{
					print '<td align="center" >';
					print '<div style="-webkit-print-color-adjust: exact; '.$tdst.'  border: solid 1px #FFF; font-size:8px;  padding: 3px;" title="'.$status_text.'    " class="tdapartment" rel="tooltip">';
					print $value;
					print '</div>';
					print '</td>';
					
					$endnum--; // вытонумерация квартир	
				}
				
				
			}
		}
		print '</tr>';
	}
		
print ' </table>
</div>

';
 if($_GET['home']==19)
 {
	print  $sss;
 }
}









//template_apartament - шаблон аппартамента (инклайд)
/*
$conf - Массив конфиг квартир
$home - дом/район номер
$data - данные о бронировании
$template_apartament
*/
function diaplay_home_public( $config , $home, $section, $data=array() , $editurl='')
{
 
$data = $data[$home][$section];
$conf = $config[$home][$section];
 
 $ckv=0;
 // Количество пустых квартир вычисляем
 if( $conf['clean_apartments'] &&  is_Array($conf['clean_apartments'] ) )
 {
		foreach( $conf['clean_apartments'] as $k=>$v)
		{
			foreach($v as $k2=>$v2)
			{
				$ckv++;
			}
		}
 }
 
  
 # автонумерация квартир
 // вычисляем последнюю 
 //количество этажей * квартир на этаже + стартовый номер квартиры в секции ( НУмеруем в другую сторону! )
 $endnum = ($conf['floor']*$conf['apartments']) + $conf['start_num']-$ckv; // Количество квартир в секции
  
 	?>
	<div style="float:left;">
 
	  
    <table class="home_sh" cellpadding="0" cellspacing ="0" style="margin-right:5px;" >
	<tr><td colspan="100" style="text-align:center;"><?=$conf[caption]?></td></tr>
	  
	
	<?
	// Цикл по строкам (количество этажей)
	for($i=$conf['floor']; $i>0; $i--)
	{
	
		// количество нежилых квартир на этаже
		$nezk = count($conf['clean_apartments'][$i]); 

		$end_etza_num = $endnum-$conf['apartments']-1+$nezk; // стартовый номер для этажа (- квартир на этаже СО СТАТУСОМ ЕСТЬ КВАРТИРА!)
		// РАБОЧИЙ ПОРЯДКОВЫЙ НОМЕР НА ЭТАЖЕ
 
		print '<tr>';
		// Цикл по столбам (количество квартир)
		for($k=0; $k<=$conf['apartments']; $k++)
		{ 
			### Запрашиваем конфиг номера квартиры 
			/*
			Если для данной секции для этажа указан номер этой квартиры , если указанно что тут нет квартиры!
			*/
			if( $conf['clean_apartments'][$i][$k] )
			{
				
			}
			else
			{
				$end_etza_num++;
			}
			
			// нулевой столбей номер этажа
			if($k==0)
			{
				print '<td class="tdfloor"  title="Этаж" rel="tooltip">';
				print ' <i>'.$i.'</i> ';
				print '</td>';
			}
			
			else // ИНфа о квартире
			{
				 $tdst='';
				// граница поседней квартиры на этаже
				if($k==$conf['apartments'])
				{
					$tdst.=' border-right:2px solid #5d6c74; ';
				}
				elseif($k==1)
				{
					$tdst.=' border-left:2px solid #5d6c74; ';
				}
				if($i==1)
				{
					 $tdst.=' border-bottom:2px solid #5d6c74; ';
				}
				
				
				
				### ИНФОРМАЦИЯ О КВАРТИРЕ
				$type= $conf['types'][$k-1]; // тип
				$size = $conf['types_s'][$k]; // площадь
 
				$edit_url = '?home='.$home.'&section='.$section.'&floor='.$i.'&apartments='.$k.'&num='.$end_etza_num.$editurl.'&apartment_num='.$end_etza_num;
			    $edit_url = 'http://em-nsk.ru/new2/form_order.php?home_id='.$home.'&apartment_num='.$end_etza_num.'&apartments='.$k;
				
				//$edit_url ='#';
				
				$apart = $GLOBALS['data_apart'][$home][$section][$end_etza_num];
 
				if( !$data[$i][$k] ){$data[$i][$k][status]='2';} // по умолчанию свободны

				if($conf['clean_apartments'][$i][$k])
				{
					$status_text = 'Нет квартиры';
					$tdst='';	
					$value='';
					
				}
				///*[status] - 0 -нет информации 1 - нет квартиры 2 - свободна 3 куплена 4 -бронь */
				elseif($data[$i][$k][status]=='0' || $data[$i][$k][status]=='1' || !$data[$i][$k] ) // Нет информации
				{
					$end_etza_num=1;
					$value='<a class="iframe" href="#2" style="font-size:9px; color:#FFF;" ><b>№ '.$end_etza_num.'</b></a>';
				} 
				elseif($data[$i][$k][status]=='2')
				{
					$status_text = '<b>Свободна</b> <br/> Комнат: '. $apart[rooms].' <br/>   Площадь: '. $apart[area].'м<sup>2</sup> <br/> Цена: '. $apart[price].'р. 
					<br/>
					<img src=\''.$apart[image_pb].'?x=234\' width=100>
					';
					$tdst.=' background-color:#89FFA4;  ';	
					$value='<a class="iframe" href="'.$edit_url.'" style="font-size:12px; color:#000;" ><b> '.$end_etza_num.'</b></a>';
				}
				elseif($data[$i][$k][status]=='3')
				{
					$status_text = '<b>Свободна</b> <br/> Комнат: '. $apart[rooms].' <br/>   Площадь: '. $apart[area].'м<sup>2</sup> <br/> Цена: '. $apart[price].'р. 
					<br/>
					<img src=\''.$apart[image_pb].'?x=234\' width=100>
					';
					$tdst.=' background-color:#000;  ';	
					$value='<a class="iframe" href="'.$edit_url.'" style="font-size:12px; color:#000;" ><b> '.$end_etza_num.'</b></a>';
				}
				else 
				{
					$status_text = 'Продана';
					$tdst.=' background-color:#FF8A90;   ';	
					$value='<a href="#" style="font-size:12px;" ><b> '.$end_etza_num.'</b></a>';				 
				}
 
				if($conf['clean_apartments'][$i][$k]) // пустая квартира
				{
					print '<td style=" ">&nbsp;</td>';
				}
				else
				{
					print '<td align="center">';
					print '<div style="'.$tdst.' border-radius: 5px;   border: solid 1px #FFF;  padding: 2px; margin:1px;" title="'.$status_text.'    " class="tdapartment" rel="tooltip">';
					print $value;
					print '</div>';
					print '</td>';
					
					$endnum--; // вытонумерация квартир
					
				}
				
				
			}
		}
		print '</tr>';
	}
		
print '</table>
</div>

';


}









//template_apartament - шаблон аппартамента (инклайд)
/*
$conf - Массив конфиг квартир
$home - дом/район номер
$data - данные о бронировании
$template_apartament
*/
function diaplay_home_public2( $config , $home, $section, $data=array() , $editurl='')
{
	if ( $home==32 || $home==30 || $home==38 ){return;}
 //print '<pre>';
 //print_r( $data);
 ///print '</pre>';
 
$data = $data[$home][$section];
$conf = $config[$home][$section];
 
 $ckv=0;
 // Количество пустых квартир вычисляем
 if( $conf['clean_apartments'] &&  is_Array($conf['clean_apartments'] ) )
 {
		foreach( $conf['clean_apartments'] as $k=>$v)
		{
			foreach($v as $k2=>$v2)
			{
				$ckv++;
			}
		}
 }
 
  
 # автонумерация квартир
 // вычисляем последнюю 
 //количество этажей * квартир на этаже + стартовый номер квартиры в секции ( НУмеруем в другую сторону! )
 $endnum = ($conf['floor']*$conf['apartments']) + $conf['start_num']-$ckv; // Количество квартир в секции
  
 	?>
	<div class="col" style="flex-grow:0; ">
 
	  
							<table class="house-table">
								<thead>
									<tr>
										<td>&nbsp;</td>
										<td colspan="100"><?=$conf[caption]?></td>
									</tr>
								</thead>
								<tbody>
								
   
	
	<?
	// Цикл по строкам (количество этажей)
	for($i=$conf['floor']; $i>0; $i--)
	{
	
		// количество нежилых квартир на этаже
		$nezk = count($conf['clean_apartments'][$i]); 

		$end_etza_num = $endnum-$conf['apartments']-1+$nezk; // стартовый номер для этажа (- квартир на этаже СО СТАТУСОМ ЕСТЬ КВАРТИРА!)
		// РАБОЧИЙ ПОРЯДКОВЫЙ НОМЕР НА ЭТАЖЕ
 
		print '<tr>';
		// Цикл по столбам (количество квартир)
		for($k=0; $k<=$conf['apartments']; $k++)
		{ 
			### Запрашиваем конфиг номера квартиры 
			/*
			Если для данной секции для этажа указан номер этой квартиры , если указанно что тут нет квартиры!
			*/
			if( $conf['clean_apartments'][$i][$k] )
			{
				
			}
			else
			{
				$end_etza_num++;
			}
			
			// нулевой столбей номер этажа
			if($k==0)
			{
				print '<td class="tdfloor"  title="Этаж" rel="tooltip" >';
				print $i;
				print '</td>';
			}
			
			else // ИНфа о квартире
			{
				 $tdst='';
				// граница поседней квартиры на этаже
				if($k==$conf['apartments'])
				{
					$tdst.=' border-right:2px solid #5d6c74; ';
				}
				elseif($k==1)
				{
					$tdst.=' border-left:2px solid #5d6c74; ';
				}
				if($i==1)
				{
					 $tdst.=' border-bottom:2px solid #5d6c74; ';
				}
				
				
				
				### ИНФОРМАЦИЯ О КВАРТИРЕ
				$type= $conf['types'][$k-1]; // тип
				$size = $conf['types_s'][$k]; // площадь
 
 
				// Для ссылок редактирования !!! 19 дома 
				if($home=='19'){$end_etza_num2='№ '.$conf['spn_apartments'][$i][$k]; $end_etza_num=$conf['spn_apartments'][$i][$k];}
				
				
			    $edit_url = 'http://em.m2profi.pro/sahmatka/form_order.php?home_id='.$home.'&apartment_num='.$end_etza_num.'&apartments='.$k;
				
				//$edit_url ='#';
				
				$apart = $GLOBALS['data_apart'][$home][$section][$end_etza_num];
 
				if( !$data[$i][$k] ){$data[$i][$k][status]='2';} // по умолчанию свободны

				if($conf['clean_apartments'][$i][$k])
				{
					$status_text = 'Нет квартиры';
					$tdst='';	
					$value='';
					
				}
				///*[status] - 0 -нет информации 1 - нет квартиры 2 - свободна 3 куплена 4 -бронь */
				elseif($data[$i][$k][status]=='0' || $data[$i][$k][status]=='1' || !$data[$i][$k] ) // Нет информации
				{
					$end_etza_num=1;
					$value='<a class="iframe" href="#2" style="font-size:9px; color:#FFF;" ><b>№ '.$end_etza_num.'</b></a>';
					
					$value='<a href="#room-modal" class="iframe" data-room="1k" data-status="free" title="" style="background: rgb(137, 255, 164);">'.$end_etza_num.'</a>';
				} 
				elseif($data[$i][$k][status]=='2')
				{
					
					
					if($home=='19')
					{
						$end_etza_num2 = ' '.$end_etza_num;
					}
					else{$end_etza_num2 = ' '.$end_etza_num;}
					
					$status_text = '<b>Свободна</b> <br/> Комнат!: '. $apart[rooms].' <br/>   Площадь: '. $apart[area].'м<sup>2</sup> <br/> Цена: '. $apart[price].'р. 
					<br/>
					<img src=\''.$apart[image_pb].'?x=234\' width=100>
					';
					
			 
					$value='<a href="'.$edit_url.'" class="iframe" data-room="'.$apart[rooms].'k" data-status="free"  style="background: rgb(137, 255, 164);">'.$end_etza_num2.'</a>';
			
				}
				elseif($data[$i][$k][status]=='4')
				{
					$status_text = '<b>Бронь</b> <br/> Комнат!: '. $apart[rooms].' <br/>   Площадь: '. $apart[area].'м<sup>2</sup> <br/> Цена: '. $apart[price].'р. 
					<br/>
					<img src=\''.$apart[image_pb].'?x=234\' width=100>
					';
					
					 $value='<a href="'.$edit_url.'" class="iframe"  data-room="'.$apart[rooms].'k" data-status=""  style="background:#FFFF3E;">'.$end_etza_num.'</a>';
			
				}
				else 
				{
					$status_text = 'Продана';
 			 
					$value='<a href="#" class="popup" data-room="'.$apart[rooms].'k" data-status="sale"  style="background: rgb(255, 138, 144);">'.$end_etza_num.'</a>';
				}
 
				if($conf['clean_apartments'][$i][$k]) // пустая квартира
				{
					print '<td>&nbsp;</td>';
				}
				else
				{
					print '<td >';
				    	print '<div padding: 0px; margin:0px;" title="'.$status_text.'" class="tdapartment" rel="tooltip">';
					print $value;
					 print '</div>'; 
					print '</td>';
					
					$endnum--; // вытонумерация квартир
					
				}
				
				
			}
		}
		print '</tr>';
	}
		
print '</table>
</div>

';


}









//template_apartament - шаблон аппартамента (инклайд)
/*
$conf - Массив конфиг квартир
$home - дом/район номер
$data - данные о бронировании
$template_apartament
*/
function diaplay_home( $config , $home, $section, $data=array() , $editurl='')
{ 
 
$data = $data[$home][$section];
$conf = $config[$home][$section];
 
 $ckv=0;
 // Количество пустых квартир вычисляем
 if( $conf['clean_apartments'] &&  is_Array($conf['clean_apartments'] ) )
 {
		foreach( $conf['clean_apartments'] as $k=>$v)
		{
			foreach($v as $k2=>$v2)
			{
				$ckv++;
			}
		}
 }
 
 
 
 
 
 # автонумерация квартир
 // вычисляем последнюю 
 //количество этажей * квартир на этаже + стартовый номер квартиры в секции ( НУмеруем в другую сторону! )
 $endnum = ($conf['floor']*$conf['apartments']) + $conf['start_num']-$ckv; // Количество квартир в секции
 
 
 
 
 
 	?>
	<div style="float:left;">
 
	<?
	//print_r($conf[caption]);
	//print_r($data);
	?>
	 
    <table class="home_sh2" cellpadding="0" cellspacing ="0"  >
	<tr><td colspan="100"><?=$conf[caption]?></td></tr>
	<?

	  //print '<pre>';
	  //print_r($data);
	  //print '</pre>';
	?>
  
	
	
	<?
	// Цикл по строкам (количество этажей)
	for($i=$conf['floor']; $i>0; $i--)
	{
	
		// количество нежилых квартир на этаже
		$nezk = count($conf['clean_apartments'][$i]); 

		$end_etza_num = $endnum-$conf['apartments']-1+$nezk; // стартовый номер для этажа (- квартир на этаже СО СТАТУСОМ ЕСТЬ КВАРТИРА!)
		// РАБОЧИЙ ПОРЯДКОВЫЙ НОМЕР НА ЭТАЖЕ
 
		
		
		if(check_access('admin') && $i==$conf['floor'])
		{
			  print '<tr>';
			// Цикл по столбам (количество квартир шапка)
			for($k=0; $k<=$conf['apartments']; $k++)
			{
				print '<th style="text-align:center;">';
				if($k>0)
				{
					print '<input type="checkbox">';
				}
				print '</th>';
			}
			  print '</tr>';
		}
		
		
		print '<tr>';
		// Цикл по столбам (количество квартир)
		for($k=0; $k<=$conf['apartments']; $k++)
		{
 
			### Запрашиваем конфиг номера квартиры 
			/*
			Если для данной секции для этажа указан номер этой квартиры , если указанно что тут нет квартиры!
			*/
			if( $conf['clean_apartments'][$i][$k] )
			{
				
			}
			elseif( $conf['spn_apartments'][$i][$k] )
			{
			//	$end_etza_num = $conf['spn_apartments'][$i][$k];
				 $end_etza_num++;
			}
			else
			{
				$end_etza_num++;
			}
			
			// нулевой столбей номер этажа
			if($k==0)
			{
				print '<td class="tdfloor"  title="Этаж" rel="tooltip">';
				print ' <i>'.$i;
				
				 if(check_access('admin'))
				 {
					// print '<br/><input type="checkbox">';
				 }
				
				
				print '</i> ';
				print '</td>';
			}
			
			else // ИНфа о квартире
			{
				 $tdst='';
				// граница поседней квартиры на этаже
				if($k==$conf['apartments'])
				{
					$tdst.=' border-right:2px solid #5d6c74; ';
				}
				elseif($k==1)
				{
					$tdst.=' border-left:2px solid #5d6c74; ';
				}
				if($i==1)
				{
					 $tdst.=' border-bottom:2px solid #5d6c74; ';
				}
				
				
				
				### ИНФОРМАЦИЯ О КВАРТИРЕ
				$type= $conf['types'][$k-1]; // тип
				$size = $conf['types_s'][$k]; // площадь
			//	print_r($conf['types_s'][$k]);
			
			
			    // Для ссылок редактирования !!! 19 дома 
				if($_GET[home]=='19'){$end_etza_num2='№ '.$conf['spn_apartments'][$i][$k]; $end_etza_num=$conf['spn_apartments'][$i][$k];}
				
				
				$edit_url = '?home='.$home.'&section='.$section.'&floor='.$i.'&apartments='.$k.'&num='.$end_etza_num.$editurl.'&apartment_num='.$end_etza_num;
			    $edit_url = 'iframe_apart.php?home_id='.$home.'&apartment_num='.$end_etza_num.'&apartments='.$k;
				 
			
				  
				  
		 ####### Растановка номеров квартир
		 //$GLOBALS[sql_u] .= 'UPDATE `broni` SET `apartments_num_new` = "'.$end_etza_num.'" WHERE `home_id` = "'.$home.'" AND `section_id` = "'.$section.'" AND `floor` = "'.$i.'" AND `apartments` = "'.$k.'" '.";  ";
		 
					if($home==19)
					{
						//   $sss .= ' INSERT IGNORE  INTO `apartaments` (`home_id`, `section_id`, `apartment_num`, `apartments`, `floor`, `price`, `price_m`, `area`, `rooms`, `kitchen_area`, `text`, `house_adress`, `adress`, `plan_code`, `status`, `date`, `image_pb`, `plan_type`, `image`, `area2`) VALUES ("'.$_GET[home].'", "'.$section.'", "'.$end_etza_num.'", "'.$k.'","'.$i.'", "", NULL, "", "", "", "", "", "", NULL, NULL, "", NULL, "", "", ""); <br/>';
						//  $sss .= ' INSERT INTO `broni` (`home_id`, `section_id`, `floor`, `apartments`, `apartments_num`, `user_id`,`status`, `date`) VALUES ("'.$home.'", "'.$section.'", "'.$i.'", "'.$k.'", "'.$end_etza_num.'","1","5", NOW() ); <br/>';
					}

				$apart = $GLOBALS['data_apart'][$home][$section][$end_etza_num];
			 	 if(!$apart[rooms]){$apart[rooms]='';}
				  if(!$apart[price]){$apart[price]='';}

				if( !$data[$i][$k] ){$data[$i][$k][status]='2';} // по умолчанию свободны

               // if($apart[home_id]){$sss.='UPDATE `apartaments` SET `status` = "'.$data[$i][$k][status].'" WHERE `apartament_num` = "'.$end_etza_num.'" AND home_id="'.$apart[home_id].'" <br/>';}

				

				if($conf['clean_apartments'][$i][$k])
				{
					$status_text = 'Нет квартиры';
					$tdst='height:100px;';	
					$value='';
					
				}
				///*[status] - 0 -нет информации 1 - нет квартиры 2 - свободна 3 куплена 4 -бронь */
				elseif($data[$i][$k][status]=='0' || $data[$i][$k][status]=='1' || !$data[$i][$k] ) // Нет информации
				{
					$end_etza_num=1;
					$status_text = ' нет данных ';
					$tdst.=' background-color:#5d6c74;  ';	
					$value='<a href="'.$edit_url.'" style="font-size:9px;" ><b>№ '.$end_etza_num.'</b></a>';
					
					
					
						 
						
						
						
					if($_SESSION['adm_caption'] && !check_access('admin')) //  админ агентства
					{
						$value='<a class="iframe_r" href="#1'.$_SESSION['sh_login'].'" style="font-size:9px; color:#FFF;" ><b>№ '.$end_etza_num.'</b></a>';
					}
					elseif(check_access('admin'))
					{
						$value='<a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" ><b>№ '.$end_etza_num.'</b></a>';
					}
					else
					{
						$value='<a class="iframe_r" href="#2" style="font-size:9px; color:#FFF;" ><b>№ '.$end_etza_num.'</b></a>';
					}
					
				} 
				elseif($data[$i][$k][status]=='2')
				{
					
					if($_GET[home]=='19')
					{
						//$end_etza_num2='№ '.$conf['spn_apartments'][$i][$k]; 
						//$end_etza_num=$conf['spn_apartments'][$i][$k];
						
						$end_etza_num2 = '№ '.$end_etza_num;
					}
					else{$end_etza_num2 = '№ '.$end_etza_num;}
					
					
					$status_text = '<b>Свободна</b> <br/> Комнат: '. $apart[rooms].' <br/>   Площадь: '. $apart[area].'м<sup>2</sup> <br/> Цена: '. $apart[price].'р. 
					<br/>
					<img src=\''.$apart[image_pb].'?x=23234\' width=100>
					';
					

					$tdst.=' background-color:#89FFA4;  ';	
					if(!$_SESSION['adm_caption']  && !check_access('admin')) // не админ агентства
					{
						$value='<a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" ><b>'.$end_etza_num2.'</b></a>';
					}
					elseif(check_access('admin'))
					{
						$tdst.=' height:95px; width:46px; ';
						 
						$value='<a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" ><b>'.$end_etza_num2.'</b></a>
						
						

						
						
						<br/><input type="checkbox" name="editapart['.$_GET[home].']['.$end_etza_num.']" value="1"><br>
						
						<a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" >
						<span style="font-size:8px;">'.$apart[area].' <br/> '.$apart[price].'</span> 
						</a>
						';
			
					}
					else
					{
						$value='<a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" ><b>№ '.$end_etza_num.'</b></a>';
					}
				}
				elseif($data[$i][$k][status]=='3')
				{
					if($_GET[home]=='19'){$end_etza_num2='#';}
					else{$end_etza_num2 = '№ '.$end_etza_num;}
						
						if(check_access('admin'))
						{
						$tdst.=' height:95px; ';
						}
						
					$status_text = 'Продана';
					$tdst.=' background-color:#FF8A90;   ';	
					 	$value='<a   href="#" style="font-size:10px;" ><b>№ '.$end_etza_num.'</b></a>';
					if(check_access('admin'))
					{
						$value='<a class="iframe_r" href="'.$edit_url.'" style="font-size:10px;" ><b> '.$end_etza_num2.'</b></a>
						
						 
						<br/><input type="checkbox" name="editapart['.$_GET[home].']['.$end_etza_num.']" value="1"><br>
						
						 
						
						<a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" >
						<span style="font-size:8px;">'.$apart[area].' <br/> '.$apart[price].'</span> 
						</a>
						  ';

						  //print_r($apart);
					}
				}
				elseif($data[$i][$k][status]=='5')
				{
					if($_GET[home]=='19'){$end_etza_num2='#';}
					else{$end_etza_num2 = '№ '.$end_etza_num;}
					
					if(check_access('admin') )
					{
						$tdst.=' height:95px; ';
						$status_text = 'Забронирована застройщиком';
						$tdst.=' background-color:#D4E6FF;   ';	
						$value='<a  class="iframe_r" href="'.$edit_url.'" style="font-size:10px;" ><b> '.$end_etza_num2.'</b></a>
						
						 
						
						<br/><input type="checkbox" name="editapart['.$_GET[home].']['.$end_etza_num.']" value="1"><br>
						
						
						<a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" >
						<span style="font-size:8px;">'.$apart[area].' <br/> '.$apart[price].'</span> 
						</a>
						 
						 
						   ';
					}
					elseif($_SESSION['agency_id'] == "92")
					{
						$status_text = 'Забронирована застройщиком';
						$tdst.=' background-color:#D4E6FF;   ';	
						$value='<a  class="iframe_r" href="'.$edit_url.'" style="font-size:10px;" ><b> '.$end_etza_num2.'</b></a>';
					}
					else
					{
						$status_text = 'Продана';
						$tdst.=' background-color:#FF8A90;   ';	
						
						$value='<a href="#'.$_SESSION['agency_id'].'" style="font-size:10px;" ><b> '.$end_etza_num2.'</b></a>';
						
					}
				}
				elseif($data[$i][$k][status]=='4')
				{
						$value='<a href="#" style="font-size:10px; color:#000000;" ><b>№ '.$end_etza_num.'</b></a>';
						if(check_access('admin'))
						{
							$tdst.=' height:95px; ';
							$tdst.=' background-color:#FFFF3E; color:#000; ';	
							$value='<a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000000;" ><b>№ '.$end_etza_num.'</b></a>
						 
							<br/><input type="checkbox" name="editapart['.$_GET[home].']['.$end_etza_num.']" value="1"><br>
							
							<a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" >
						<span style="font-size:8px;">'.$apart[area].' <br/> '.$apart[price].'</span> 
						</a>
						 
							 
							';
							$status_text = 'Бронь '.$data[$i][$k][date] . '<br>' . $data[$i][$k][login] . '<br>'. $data[$i][$k][name];
							$status_text .=' <br/><b>'.$data[$i][$k][agency_caption].'</b>';
						}
						elseif($_SESSION['adm_caption'] && $_SESSION['agency_id'] == $data[$i][$k][agency_id] ) // Если админ агенства и бронь сотрудником агентства
						{
							
							$status_text = 'Бронь '.$data[$i][$k][date] . '<br>' . $data[$i][$k][login] . '<br>'. $data[$i][$k][name];
							$value='<a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" ><b>№ '.$end_etza_num.'</b></a>';
							$tdst.=' background-color:#FFA500; color:#000; ';	
						}
						elseif(  $_SESSION['agency_id'] == '92' ) //СОТРУДНИК ОП
						{
							
							$status_text = 'Бронь '.$data[$i][$k][date] . '<br/>' . $data[$i][$k][login] . '<br>'. $data[$i][$k][name];
							$value='<a href="#" style="font-size:10px; color:#000;" ><b>№ '.$end_etza_num.'</b></a>';
							$tdst.=' background-color:#FFFF00; color:#000; ';	
						}
						else
						{	
							$value='<a  class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" ><b>№ '.$end_etza_num.'</b></a>';
							$tdst.=' background-color:#FFFF00; color:#000; ';	
						//	$status_text = 'Бронь '.$data[$i][$k][date] . '<br/>';
							
							$status_text = '<b>Бронь <br/> '.$data[$i][$k][date] . '</b> <br/> Комнат: '. $apart[rooms].' <br/>   Площадь: '. $apart[area].'м<sup>2</sup> <br/> Цена: '. $apart[price].'р. 
							<br/>
							<img src=\''.$apart[image_pb].'?x=23234\' width=100>
							';
					
					
						}
						
				
					
					 
				}
				
				elseif($data[$i][$k][status]=='6')
				{
					if(check_access('admin'))
					{
						
						$status_text = 'Квартира подрядчика';
						
						
						
					 
						$tdst.=' background-color:#9933ff;   ';	
							$tdst.=' height:95px; ';	
						$value='<a  class="iframe_r" href="'.$edit_url.'" style="font-size:10px;" ><b>№ '.$end_etza_num.'</b></a>
						<br/>
						 
						
						
						<input type="checkbox" name="editapart['.$_GET[home].']['.$end_etza_num.']" value="1"><br>
						
						<a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" >
						<span style="font-size:8px;">'.$apart[area].' <br/> '.$apart[price].'</span> 
						</a>
						 
						 
					 ';
						
					}
					elseif($_SESSION['agency_id'] == "92")
					{
						$status_text = 'Квартира подрядчика';
						$tdst.=' background-color:#9933ff;   ';	
						$value='<a class="iframe_r" href="'.$edit_url.'" style="font-size:10px;" ><b>№ '.$end_etza_num.'</b></a>';
						
					}
					else
					{
						$status_text = 'Продана';
						$tdst.=' background-color:#FF8A90;   ';	
						$value='<a href="#'.$_SESSION['agency_id'].'" style="font-size:10px;" ><b>№ '.$end_etza_num.'</b></a>';
						
					}
				}
				
				
				if($conf['clean_apartments'][$i][$k]) // пустая квартира
				{
					if(check_access('admin'))
					{
					print '<td style=" height:100px;">&nbsp;</td>';
					}
					else
					{
						print '<td style="height:36px;">&nbsp;</td>';
					}
				}
				else
				{
					print '<td align="center">';
					print '<div '.$tdst2.' style="'.$tdst.' border-radius: 7px;   border: solid 1px #FFF;  padding: 5px;" title="'.$status_text.'    " class="tdapartment" rel="tooltip">';
					print $value;
					print '</div>';
					print '</td>';
					
					$endnum--; // вытонумерация квартир
					
				}
				
				
			}
		}
		print '</tr>';
	}
		
print ' </table>
</div>

';
 if($_GET['home']==19)
 {
	print  $sss;
 }
}