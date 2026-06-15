<?	


function select_filed_o( $arr , $val='' )
{
	if(!$val){ $val=''; }
	foreach($arr as $k=>$v)
	{
		?>
		<option value="<?=$k?>" <? if($k==$val){print ' selected="selected" ';} ?>><?=$v?></option>
		<?
	}
}



// parse_str($b, $arr); - разбирает query в массив
// http_build_query - собирает массив в строку без ?
/*
Array
(
    [scheme] => https
    [host] => snipp.ru
    [path] => /php/parse-url
    [query] => page=1&sort=1
    [fragment] => sample
)
*/

function reverse_parse_url(array $parts)
{
	$url = '';
	if (!empty($parts['scheme'])) {
		$url .= $parts['scheme'] . ':';
	}
	if (!empty($parts['user']) || !empty($parts['host'])) {
		$url .= '//';
	}	
	if (!empty($parts['user'])) {
		$url .= $parts['user'];
	}	
	if (!empty($parts['pass'])) {
		$url .= ':' . $parts['pass'];
	}
	if (!empty($parts['user'])) {
		$url .= '@';
	}	
	if (!empty($parts['host'])) {
		$url .= $parts['host'];
	}
	if (!empty($parts['port'])) {
		$url .= ':' . $parts['port'];
	}	
	if (!empty($parts['path'])) {
		$url .= $parts['path'];
	}	
	if (!empty($parts['query'])) {
		if (is_array($parts['query'])) {
			$url .= '?' . http_build_query($parts['query']);
		} else {
			$url .= '?' . $parts['query'];
		}
	}	
	if (!empty($parts['fragment'])) {
		$url .= '#' . $parts['fragment'];
	}
	
	return $url;
}

# НОРМАЛИЗАЦИЯ УРЛ (замена на абсолютные?, замена протокола ?! замена двойных директорий)
// принимает переменные в виде ?x=1&y=2 и дописывает в текущий url
function url2($str)
{
	// текущий URL
	$url = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$arru = parse_url($url);
	 
	parse_str( $str, $nvars ); // Получаем массив новых переменных
	parse_str( $arru['query'], $old_vars ); // Получаем массив старых переменных

	foreach( $nvars as $k => $v )
	{
		$old_vars[$k] = $v;		
	}
	
	// Удаляем пустые переменные
	foreach( $old_vars as $k => $v )
	{
		if(!$v)
		{
			unset($old_vars[$k]);		
		}
	}
	ksort($old_vars); // сортируем переменные 
	
	// Собираем обновленные переменные в строку
	$new_q = http_build_query( $old_vars );
	
	// Собираем url в строку
	$arru['query'] = $new_q;
	
	return reverse_parse_url( $arru );
}










class controller_broni
{
	
	
	function __construct()
	{
		$this->sql = $GLOBALS['mysql']; // обект MYSQL
		// print_r($this->sql);
		
	 
		$this->pp = 50; // на страницу
		$this->c = 0; // СТрок в результате!
		
		
	}


	function filtr_form()
	{
		$this->get();



		// Получаем количество комнат
		$arr = $this -> sql -> get_arr( '
		SELECT apartaments.rooms ,agency.caption,agency.agency_id, count(*) as c  
		FROM apartaments 
		
		LEFT JOIN broni ON apartaments.status_broni_id = broni.broni_id /* Брони */
		LEFT JOIN users ON users.id = broni.user_id /* Пользователи */
		LEFT JOIN agency ON agency.agency_id = users.agency_id /* Агентства */
		
		WHERE rooms!=0 AND status_broni_id>0 
		
		GROUP BY agency.caption, rooms 
		 '  
		); 
		
		// print_r($arr);
		
		
		foreach($arr as $k=>$v)
		{
			$rooms_arr[$v['rooms']]=$v['rooms']; 	# массив для селекта комнат 
			$agency_arr[$v['agency_id']]=$v['caption'];  
		}
		
		arsort($rooms_arr);
		arsort($agency_arr);
		   
		t('Данные для фильтра получены');
		 
		$h = $GLOBALS['sa']->get_homes_arr();
 
		# массив для селекта статусов
 
		//$status_arr[2]='Свободна';
		$status_arr[4]='Забронирована';
		$status_arr[3]='Продана';
		//$status_arr[5]='Забронирована застройщиком';
		//$status_arr[6]='Квартира подрядчика';
		
		// Массив для селекта
	 
		foreach($h as $k=>$v){$h_arr[$v['home_id']]=$v['title'];	}
 
		?>
		<form method="GET" action="user.php?action=stat_salen" id="filtrform">
		<input type="hidden" name="action" value="<?=$_GET[action]?>" >
	 
				<div class="stat-top-filter">
					<div class="stat-top-item stat-top-select stat-top-item_house">
						<select  name="home" data-placeholder="Дом">
							<option value="">Дом</option>
							<? select_filed_o( $h_arr , $_GET['home'] ); ?> 
						</select>
					</div>
					<div class="stat-top-item stat-top-select stat-top-item_room">
						<select name="rooms" data-placeholder="Комнат">
							 <option value="">Комнат</option>
							 <? select_filed_o( $rooms_arr , $_GET['rooms'] ); ?> 
						</select>
					</div>
					<div class="stat-top-item stat-top-select stat-top-item_status">
						<select name="status" data-placeholder="Статус">
							<option value="">Статус</option>
							<? select_filed_o( $status_arr , $_GET['status'] ); ?>
						</select>
					</div>
					<div class="stat-top-item stat-top-select stat-top-item_agency">
						<?
						if( $_SESSION['sh_login']=='admin')
						{
						?>	
						<select data-placeholder="Агентства" name="agency">
						<option value="">Агентства</option>
						<? select_filed_o( $agency_arr , $_GET['agency'] ); ?>
						</select>
						<?
						}
						?>
					</div>
					
					
					 
					
					
					
					
					<?
						// Фильтр с первого числа месяца до сегодня
						$firstDayOfMonth = date('Y-m-01', time());
						$yastoday = date('Y-m-d', time());
						if(!$_GET[date_limit]){ $_GET[date_limit] = $firstDayOfMonth.' : '.$yastoday ; }
					?>
					<div class="stat-top-item stat-top-in stat-top-in_date stat-top-item_date ">
						<input type="text" name="date_limit" placeholder="За все время"  value="<?=$_GET[date_limit]?>" autocomplete="off">
					</div>
					<a href="#" class="stat-top-btn btn btn_arrow-long" onclick="document.getElementById('filtrform').submit(); return false;">Выбрать<i></i></a>
				</div>
				
				</form>
 
				<?
	}
	

	
	
	
	function get_sql( $nofiltr=0 )
	{
		if( isset($_GET['status'] ) ){ $status = $_GET['status']; }
		if( isset($_GET['date_start'] ) ){ $date_start = $_GET['date_start']; }
		if( isset($_GET['date_end'] ) ){ $date_end = $_GET['date_end']; }
		if( isset($_GET['home'] ) ){ $home = $_GET['home']; }
		if( isset($_GET['rooms'] ) ){ $rooms = $_GET['rooms']; }
 
		# if(!$status){ $status=3; }
		   
		// ТОЛЬКО АКТУАЛЬНЫЕ БРОНИ ПО ТАБЛИЦЕ АППАРТАМЕНТОВ 
		$q = ' SELECT  apartaments.*, homes.*,

		users.login, users.name, users.phone, users.e_mail, /* текущая бронь */
		agency.caption as caption, agency.agency_id as agency_id,broni.broni_id ,

		users_b.login as login_b, users_b.name as name_b, users_b.phone  as phone_b, users_b.e_mail as e_mail_b,  /* Предидущая бронь */
		agency_b.caption as caption_b, agency_b.agency_id as agency_id_b,
		  
		 ROUND((UNIX_TIMESTAMP()-UNIX_TIMESTAMP(broni.date))/60/60/24,0) as da , broni.date as fdate FROM apartaments

		left join `homes` on homes.home_id = apartaments.home_id 

		 /* Данные броней не продажи*/
		left join broni on broni.broni_id = apartaments.status_broni_id 
		left join users on users.id = broni.user_id 
		left join `agency` on users.agency_id = agency.agency_id 
		 
		 
		 /* Брони перед продажей предидущая бронь */
		 /* актуально только для проданых квартир ищем максимальную бронь с статусом отличным от продано для данной квартиры */
		 left join ( SELECT broni.*, max(broni.date)  FROM broni where status!=3 AND user_id !=1 group by broni.apartament_id ) AS broni_b 
		 on broni_b.home_id = apartaments.home_id AND  broni_b.apartments_num = apartaments.apartment_num  
		 
		 left join users as users_b on users_b.id = broni_b.user_id    
		 left join `agency` as agency_b on users_b.agency_id = agency_b.agency_id 
		  
		 WHERE 1=1  
		 ';
		  
		  
		  // Фильтр с первого числа месяца до сегодня
						$firstDayOfMonth = date('Y-m-01', time());
						$yastoday = date('Y-m-d', time());
						if(!$_GET[date_limit]){ $_GET[date_limit] = $firstDayOfMonth.' : '.$yastoday ; }
						
						
						
		  // даты одной строкой
		if($_GET[date_limit])
		{
			$sb=explode(' : ',$_GET[date_limit]);
			$date_start = $sb[0];
			$date_end = $sb[1];
		}
		
		
		if(!$nofiltr)
		{
			if( $rooms )  {	  $q.=' AND apartaments.rooms = "'.$rooms.'" ';  }
			if( $home )  {	  $q.=' AND broni.home_id = "'.$home.'" ';  }
			if( $status )  {	  $q.=' AND apartaments.status2 = "'.$status.'" ';  }else{ $q.=' AND (apartaments.status2 = "4" OR apartaments.status2 = "3" ) ';}
			if( $date_start )  { 	$q.=' AND broni.date >= "'.$date_start.'" ';  }
			if( $date_end )  { 	$q.=' AND broni.date <= "'.$date_end.'"  + interval "1" day ' ;  }
 
			// простой пользователь не админ агентства
			if($_SESSION['sh_login'] && !$_SESSION['adm_caption'] && $_SESSION['sh_login']!='admin' &&  $_SESSION['sh_login']!='fd' &&   $_SESSION['sh_login']!='uservip' &&  $_SESSION['sh_login']!='partner' )
			{
				$q.='AND users.login="'.$_SESSION['sh_login'].'"  '; // только его брони
			}
			//  админ агентства
			elseif($_SESSION['sh_login'] && $_SESSION['adm_caption'] && $_SESSION['sh_login']!='admin' && $_SESSION['sh_login']!='fd'   &&  $_SESSION['sh_login']!='uservip' &&  $_SESSION['sh_login']!='partner' )
			{
				$q.=' AND agency.agency_id="'.$_SESSION['agency_id'].'"  '; // только брони агентства
				$q.=' AND users.login="'.$_SESSION['sh_login'].'"  '; // только его брони
			}	
		}
		$q.=' ORDER by broni.date_fu desc  ';
		
		return $q;
	}
	
	
	
	
	
	
	function get()
	{
 
		if(!$this->result_arr)
		{
			t('получение массива данных');
			
			/*
			$this -> sql ->  sql( $this->get_sql() ); // выполняем запрос
			$this -> c =  mysqli_affected_rows( $this -> sql ->c ); // получаем количество результатов!
			// получаем с лимитами запрос в массив
			 	print $this->get_limits();
			$this->result_arr = $this -> sql -> get_arr( $this->get_sql() . $this->get_limits() ); // Получаем массив С фильтрацией для вывода
			*/
			$this->result_arr = $this -> sql -> get_arr( $this->get_sql() ); // Получаем массив С фильтрацией для вывода
		
			t('Массив данных получен!');
		
		}
  
	}
	
	
	
	
	
	
	
	
	
	
	
	
		
	
	function pages_menu()
	{
		$num_pages = ceil( $this->c / $this->pp  );
		print 'Всего строк: '.$this->c;
		print ' / Страницы: ';
		for( $i=1; $i <= $num_pages; $i++ ) 
		{
		  $url = url2('page='.$i);
		  echo '<a href="'.$url.'">'.$i."</a>\n";
		}
	}
	
	function get_limits()
	{
		if ( isset( $_GET['page'] ) ) $page = ( $_GET['page'] - 1 ); else $page = 0;
		// вычисляем первый оператор для LIMIT
		$start = abs( $page * $this->pp );
		return " LIMIT $start,$this->pp ";
	}
	
	
	
	
	
	
	
	
	
	
	function display()
	{
		$this->get();
		//$status_arr[2]='Свободна';
		$status_arr[4]='Забронирована';
		$status_arr[3]='Продана';
		//$status_arr[5]='Забронирована застройщиком';
		//$status_arr[6]='Квартира подрядчика';
		 
		 
		 
 
		 ?>
		<table>
			<thead> 
				<tr>
							<th><b>id</b></th>
							<th style="width:100px;"><a href="#"><b>Дата</b></a></th>
							
							<th><a href="#"><b>Агентство</b></a></th>
							
							
							<th><a href="#"><b>Пользователь</b></a></th>
							<th><a href="#"><b>Дом</b></a></th>
							<th><a href="#"><b>Квартира</b></a></th>
							<th><a href="#"><b>Комнат</b></a></th>
							<th><a href="#"><b>м<sup>2</sup></b></a></th>
							<th style="width:130px;"><a href="#"><b>Цена</b></a></th>
							
							<th><a href="#"><b>Статус</b></a></th>
							
							<th><a href="#"><b>Бронь</b></a></th>
						</tr>
		</thead>
		
		<?
 		$color_arr[0]='Статус';
		$color_arr[2]='00FF00';
		$color_arr[4]='FFFF00';
		$color_arr[3]='FF0000';
		$color_arr[5]='D4E6FF';
		$color_arr[6]='9933ff';
			
		foreach($this->result_arr as $k=>$result) 
		{
		 
		 $dates[$result['date']]++;
		 if($_GET[date] && $_GET[date] != $result['date'] ){continue;}
		 $result['area'] = str_replace(',','.',$result['area']);
		 $summ_arr['area']= $summ_arr[area]+ $result['area'];
		 $summ_arr['price']= $summ_arr[price]+$result['price'];
		 
		 $summ_arr['c_all']++;
					if( $result['status']==3 && $result['login_b']) //продажа - предидущая бронь!
					{
						$summ_arr['agency'][$result['agency_id_b']] = $result['caption_b'];
						$summ_arr['agency_c'][$result['agency_id_b']]++;
						if($_GET['agency'])	{	if($result['agency_id_b']!=$_GET['agency']) {continue;}		}
					}
					else   //не продажа (оригинальная бронь) 
					{
						$summ_arr['agency'][$result['agency_id']] = $result['caption'];
						$summ_arr['agency_c'][$result['agency_id']]++;
						if($_GET['agency'])	{	if($result['agency_id']!=$_GET['agency']) {continue;}		}
					}
		  
			$summ_arr['c']++;
		 
			echo     '<tr>
					  <td>'.$result['broni_id'].'</td>'.
					 '<td>'.$result['fdate'].'</td>';
					 
					 
					if( $result['status']==3 && $result['login_b']) //продажа - предидущая бронь!
					{
						print  '<td>'.$result['caption_b'].'</td>';	 
						print  '<td><b>'.$result['login_b'].'</b> ('.$result['name_b'].') '.$result['phone_b'].' '.$result['email_b'].'</td>';
						
						 
		
					}
					else   //не продажа (оригинальная бронь) 
					{
						print  '<td>'.$result['caption'].'</td>';	 
						print  '<td><b>'.$result['login'].'</b> ('.$result['name'].') '.$result['phone'].' '.$result['email'].'</td>';
					 
					}
					echo '<td>'.$result[title].'</td>';
					if( $_SESSION['sh_login']=='admin')
					{
					 print '<td><a href="user.php?action=broni_history&home='.$result['home_id'].'&apartament_num='.$result['apartment_num'].'">'.$result['apartment_num'].'</a></td>';
					}
					else
					{
						print '<td>'.$result['apartment_num'].'</td>';	
					}	
					 echo
					 '<td>'.$result['rooms'].'</td>'.
					 '<td>'.$result['area'].'</td>'.
					 '<td>'.$result['price'].'</td>'.
					 '<td><b style="background-color:#'.$color_arr[ $result['status'] ].';">';
					 print  $status_arr[ $result['status2'] ];
					 
					 print '</td>' ; 
  
		print '<td> ';
		if($result['status']==4)
		{
			if(  $result['da']<=10 )
			{
				 print 'До снятия ';
				print 10-$result['da'];
				print ' дней';

			 }
			else
			{
				 print 'Бронь просрочена на ';
				 print $result['da']-10;
				 print ' дней';
			}

		 
			
			
	if( $_SESSION['sh_login']!='fd')
	{
	?> 
	 </b>
	- <a href="?action=show_broni&broni_id=<?=$result['broni_id']?>&broni_up=1" style="display:none;">Продлить</a>  
	<?
	}
	
	
		}

						
			print '</td>' ; 
			print '</tr>' ; 					 
			}
			?> 
	 <tfoot>
			 <tr>
				<td colspan="7" style="text-align:left;" align="left"><b>Квартир: <?=$summ_arr['c']?></b></td>
	 
				<td><b><?=$summ_arr['area']?></b></td>
				<td><b><?=number_format($summ_arr['price'], 0, '', ' ');  ?></b></td>
				<td></td>		   
				<td></td>
			</tr>
		 </tfoot>
	 
			</table> 
			 <br><br>
	<?
	//  $this->pages_menu();
	
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	# Удаление брони 
	function action__delete()
	{
		if($_GET[broni_del] && $_GET[broni_id]) // Отмена брони
		{
			// ДОбавляем новую запись в брони - квартира свободна!
			// обновляем статус квартиры в аппартаментах
			$sa->up_broni( $_GET[broni_id] , 2 );
		}
	}
	
	#  Обновление брони
	function action__up()
	{
		if($_GET[broni_up] && $_GET[broni_id]) // Продление брони
		{
			// Добавляем новую запись бронирования в брони
			// обновляем статус квартиры в аппартаментах
			$sa->up_broni( $_GET[broni_id] , 4 );
		}
	}
	
	
	
	




}
 
 
 
 
 
 
 
 

 
 
 
  
 
 
 
 
 # Вывод сообщений 
 $sa->display_mess();

$query = mysqli_query($connection,$q);
 
 
 
 ?>
 
 
 
 
 

 
 
 
 
 
 
 
 
 
 
 
  













<?
 $broni = new controller_broni();
?>
 
<section class="section-stat">
	<div class="container mobc">
		<div class="page-header">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">Брони</div>
		</div>
		<div class="stat">
			<div class="stat-top">
			<?
				$broni->filtr_form();
			?>	
			</div>
			<div class="stat-table stat-table-bron table">
			<?
				$broni->display();
			?>
			</div>
		</div>
	</div>
</section>
 


 <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script>
$(function() {
	 
  $('input[name="date_limit"]').daterangepicker({
      "locale": {
        "format": "YYYY-MM-DD",
        "separator": " : ",
        "applyLabel": "Применить",
        "cancelLabel": "Закрыть",
        "fromLabel": "От",
        "toLabel": "До",
        "customRangeLabel": "Настроить",
        "weekLabel": "W",
        "daysOfWeek": [
            "Вс",
            "Пн",
            "Вт",
            "Ср",
            "Чт",
            "Пт",
            "Сб"
        ],
        "monthNames": [
            "Январь",
            "Февраль",
            "Март",
            "Апрель",
            "Май",
            "Июнь",
            "Июль",
            "Август",
            "Сентябрь",
            "Октябрь",
            "Ноябрь",
            "Декабрь"
        ],
        "firstDay": 1
    } ,
	ranges: {
           'Сегодня': [moment(), moment()],
           'Вчера': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
           '7 дней': [moment().subtract(6, 'days'), moment()],
           '30 дней': [moment().subtract(29, 'days'), moment()],
           'Текущий месяц': [moment().startOf('month'), moment().endOf('month')],
           'Прошлый месяц': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
  });
  
    
});


</script>

  
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
 <script>

$(document).ready(function()
	{
		//$("#sorted").tablesorter( {sortList: [[0,0], [1,0]]} );
		
		$("#sorted").tablesorter();
	}
);
</script>
 
  <?
 
 
 
 
 
 
 
 
 
 
 
 
 
 
  