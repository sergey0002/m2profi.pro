<? 

if(!function_exists('get_app_url')) {
    function get_app_url() { 
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'em.m2profi.pro';
        return getenv('APP_URL') ?: "$protocol$host";
    }
}
if(!function_exists('get_app_domain')) {
    function get_app_domain() { 
        return getenv('APP_DOMAIN') ?: ($_SERVER['HTTP_HOST'] ?? 'em.m2profi.pro');
    }
}
if(!function_exists('get_public_url')) {
    function get_public_url() { return get_app_url(); }
}
if(!function_exists('get_public_domain')) {
    function get_public_domain() { return get_app_domain(); }
}



// хелперы 
class hlp {
	public $sqlc = ''; // Соединение с mYSQL
 
	public $sql = ''; //  
	public $c = ''; //  
	
	function __construct( $connection )
	{
		$this -> c = $connection;
		$this -> sql = new m_mysql($connection);
	
	}
	
	
	// Методы многопользовательского режима
	
	// Получить ид агентства отдела продаж!
	function get_op_id()
	{
		
	}
	
	// Получить ид агентства администраторов юзера!
	function get_admins_id()
	{
		
	}
	
	
	
	
	// Отправка сообщения с фиксацией в БД
	function send_message($to,$subject,$text, $files='')
	{
		
		$data['to'] = $to;
		$data['subject'] = $subject;
		$data['text'] = $text;		 
		 
		$this->sql->insert( 'messages' , $data);
 
 
 
		// Дублируем на Email, если не отправлено меняем статус 
		// статус crm (id юзера который отвечает) отдел продаж может видеть свои сообщения можно (ид глобального бзера + ид админского агентства)
		
		print 'Сообщение отправлено';
	}
	
	
	
	
	
	// наследуем все гет переменные в форме !
	// vars - массив значений которые надо наследовать
    function formgetval($vars)
	{
		foreach($_GET as $k=>$v)
		{
			if( in_array($k,$vars) )
			{
			?>
			<input type="hidden" name="<?=$k?>" value="<?=$v?>" />
			<?
			}
		}
	}
	
}



















 
class sahmatka {
	public $sqlc = ''; // Соединение с mYSQL
    public $user = ''; // Массив переменных пользователя
	public $errors_messages = ''; // Сообщения об ошибках для пользователей массив
	public $messages = array(); // Сообщения  
	public $log = array(); // ЛОг ошибок для отладки
	public $status = ''; //  статусы квартир
	public $sql = ''; //  
	
	function __construct( $user , $connection )
	{
		$this -> user = $user;
		$this -> c = $connection;
		$this -> sql = new m_mysql($connection);
		
		$this -> mysql = $this -> sql;
		
		//$this->status[0]='Не задан';
		$this->status[2]='Свободна';
		$this->status[3]='Продана';
		$this->status[4]='Забронирована';
		$this->status[5]='Застройщика';
		$this->status[6]='Подрядчика';
 
		
		$this->rooms[1] = '1к';
		$this->rooms[2] = '2к';
		$this->rooms[3] = '3к';
		$this->rooms[4] = '4к';
		
		$this->group[1] = 'Месяцам';
		$this->group[2] = 'Неделям';
		$this->group[3] = 'Дням';
		
		
		// Конфиг домов
		$q = 'SELECT * FROM `homes` LEFT JOIN homes_sections ON homes_sections.homes_id = homes.homes_id ';
        $homes_arr = $this->sql->get_arr( $q  );
		foreach($homes_arr as $k=>$v )
		{
			$this->homes_config[$v['home_id']]['caption'] = $v['long_title'];
			$this->homes_config[$v['home_id']][$v['section_id']]['caption'] = $v['caption'];
		}
		
		
		
		
	}
	
	/*
	
	Переменные
	1. Соединение с mysql
	
	2. Сессия пользователя
	[agency_id] => 95
    [ucaption] => НСКАН
    [adm_caption] => 
    [sh_password] => awDJa8aSJF8aebfDJf
    [sh_login] => nskan_titarov
    [sh_id] => 355
    [sh_name] => Сергей
    [agency_adm_id] => 
	
	
	*/
	
	//Сообщения пользователям Mess- текст $status 1-
	function mess($mess, $status='')
	{
		$this->messages[]=$mess;
	}
	  
	//вывод сообщений
	function display_mess()
	{
		foreach($this->messages as $k=>$v)
		{	
			print $v.'<br/>'; 
		}
	}
	
	
	
	// Новая запись о броне выдает ид брони + меняет статус квартиры в аппартаментах
	function new_broni($home_id, $apartment_num, $status, $apartments = 0)
{
     
    add_log("new_broni: home_id=$home_id, apartment_num=$apartment_num, status=$status, apartments=$apartments");
$user_id = isset($_SESSION['sh_id']) ? (int)$_SESSION['sh_id'] : 0;

    $now = date("Y-m-d H:i:s");

    // 1. Получаем данные о квартире
    $q = 'SELECT * FROM apartaments WHERE home_id = "'.$home_id.'" AND apartment_num = "'.$apartment_num.'" ';
     $app_arr = $this->sql->get_arr($q, 1);

    if (!$app_arr) {
        $this->mess('Ошибка: квартира не найдена');
        print "Ошибка: квартира не найдена<br>";
        add_log("Ошибка: квартира не найдена ($q)");
        return 0;
    }
    


 
    // 3. Готовим данные для новой брони
    $data = [
        'home_id'         => $app_arr['home_id'],
        'section_id'      => $app_arr['section_id'],
        'floor'           => $app_arr['floor'],
        'apartments'      => $apartments,
        'apartments_num'  => $app_arr['apartment_num'],
        'apartments_num1' => $app_arr['apartment_num'],
        'apartament_id'   => $app_arr['apartament_id'],
        'user_id'         => $user_id,
        'status'          => $status,
        'date'            => $now,
        'date_fu'         => $now,
        'date_first'      => $now,
        'comment'         => 'Бронь через iframe apart',
        'price'           => $app_arr['price'],
    ];
 
    // 4. Вставляем бронь
    $broni_id = $this->sql->insert('broni', $data);
     add_log('Забронировано помещение (new_broni), id='.$broni_id);

    if (!$broni_id) {
        $this->mess('Ошибка: не удалось создать запись в broni');
        print "Ошибка: не удалось создать запись в broni<br>";
        add_log("Ошибка insert в broni: ".print_r($data,1));
        return 0;
    }

    // 5. Обновляем статус в апартаментах
    $data2 = [
        'status'            => $status,
        'status2'           => $status,
        'status_broni_id'   => $broni_id,
        'status_broni_date' => $now,
    ];
     $update_result = $this->sql->update_for_key('apartaments', 'apartament_id', $app_arr['apartament_id'], $data2);
     add_log("Обновление apartaments: apartament_id=$ap_id, статус=$status, broni_id=$broni_id");

    return (int)$broni_id;
}

	
	
	
	
	
	
	function up_broni($broni_id, $status = '', $comment = '')
{
    // 1. Текущий пользователь и его права
    $current_user_id   = $_SESSION['sh_id'];
    $is_admin          = (check_access('admin') || $_SESSION['sh_login'] == 'demo_admin'  || $_SESSION['sh_login'] == 'em_nsv');
    $is_agency_admin   = !empty($_SESSION['adm_caption']);

    // 2. Получаем старую бронь
    $data = $this->sql->get_for_key('broni', 'broni_id', $broni_id);
    if (!$data) {
        $this->mess('Ошибка: бронь не найдена');
        return;
    }

    // === ДОБАВЬ: подтягиваем актуальную цену ===
    $apartament_id = $data['apartament_id'];
    $apartment = $this->sql->get_for_key('apartaments', 'apartament_id', $apartament_id);
    $data['price'] = isset($apartment['price']) ? $apartment['price'] : 0;
    // === /ДОБАВЬ ===

    // 3. Проверка прав доступа
    $is_owner = ($data['user_id'] == $current_user_id);
    $user_info = $this->sql->get_for_key('users', 'id', $data['user_id']);
    $is_same_agency = isset($user_info['agency_id']) && ($user_info['agency_id'] == $_SESSION['agency_id']);

    if (!$is_admin && !$is_owner && !($is_agency_admin && $is_same_agency)) {
        $this->mess('Ошибка: у вас нет прав на управление этой бронью');
        return;
    }

    // 4. Проверка давности продлеваемой брони (для НЕ админов)
    $old_date = strtotime($data['date']);
    $max_days = 15;
    if (!$is_admin && time() - $old_date > 60 * 60 * 24 * $max_days) {
        $this->mess("Ошибка: бронь устарела (более $max_days дней)");
        return;
    }

    // 5. Обновляем статус
    if (!empty($status)) {
        $data['status'] = $status;
    }

    // 6. Подготовка новой записи брони
    $new_date = date("Y-m-d H:i:s");
    unset($data['broni_id']);
    $data['date'] = $new_date;
    $data['comment'] = $comment;

    // ✅ Меняем user_id только если это автоматическое системное удаление (например, из cron)
    if ($status == 2) {
        $data['user_id'] = $current_user_id;
    }

    // 7. Оставляем только допустимые поля
    $allowed_fields = [
        'home_id', 'section_id', 'floor', 'apartments', 'apartments_num1',
        'status', 'user_id', 'date', 'date_first', 'date_fu',
        'apartments_num', 'apartament_id', 'broni_up_counter',
        'comment', 'price'
    ];
    $data = array_intersect_key($data, array_flip($allowed_fields));

    // 8. Вставляем новую запись брони
    $new_broni_id = $this->sql->insert('broni', $data);

    // 9. Обновляем состояние квартиры
    if ($new_broni_id) {
        $this->sql->sql("
            UPDATE `apartaments` SET
                `status` = {$data['status']},
                `status2` = {$data['status']},
                `status_broni_id` = {$new_broni_id},
                `status_broni_date` = NOW()
            WHERE `apartament_id` = {$data['apartament_id']}
        ");
    }

    // 10. Вывод сообщения
    if ($data['status'] == 2) {
        $this->mess('Отмена брони успешно выполнена');
    } elseif ($data['status'] == 4) {
        $this->mess('Продление брони успешно выполнено');
    } else {
        $this->mess('Бронь обновлена');
    }
}


	
	
	
	
	
	// Список броней выдать (в зависимости от статуса мои/всех пользователей/пользователей агентства) учетом поиска
    function show_broni($home_id='', $apartment_num='', $agency_id='', $user_id='', $date_start='', $date_end='' ) {
		 
    }
	
	
	
	// Меню обектов (разное в зависимости от тстатуса)
	function show_objects_menu()
	{
			
	}
	
	// Меню действий (разное в зависимости от тстатуса)
	function show_action_menu()
	{
				
	}	
	
	
	// показать чекбоксы для выбора домов
	function show_house_check()
	{
		// Получеем массив домов
		$q = ' SELECT * FROM homes WHERE homes.show="1"; ' ; 
		$arr = $this ->sql->get_arr( $q );
		 ?>
		<input type="checkbox" name="home_mch" id="home_mch" <?if($_GET['home_mch']){?> checked="checked" <?}?>/> - <label for="home_mch">Все</label> &nbsp;&nbsp;&nbsp;
		 <?
		foreach($arr as $k=>$v)
		{
			?> <input type="checkbox" name="homes[<?=$v['home_id']?>]" id="home_<?=$v['home_id']?>" class="home_ch" value="<?=$v['home_id']?>" <?if(in_array($v['home_id'],$_GET['homes'])){?> checked="checked" <?}?> /> 
			- <label for="home_<?=$v['home_id']?>"><?=$v['title']?></label> &nbsp;&nbsp;&nbsp;<?
			
		}
		
		?>
		<script type="text/javascript">
			$(document).ready( function() {
			   $("#home_mch").click( function() {
					if($('#home_mch').attr('checked')){
						$('.home_ch').attr('checked', true);
					} else {
						$('.home_ch').attr('checked', false);
					}
			   });
			});
		</script>
 
		<?
	}
	
	 
	
	
	// показать чекбоксы для выбора домов
	function show_status_check()
	{
		// Получеем массив домов
 
		 ?>
		<input type="checkbox" name="status_mch" id="status_mch" <?if($_GET['home_mch']){?> checked="checked" <?}?>/> - <label for="status_mch">Все</label> &nbsp;&nbsp;&nbsp;
		 <?
		 
		 
		foreach($this->status as $k=>$v)
		{
			?> <input type="checkbox" name="status[<?=$k?>]" id="status_<?=$k?>" class="status_ch" value="<?=$k?>" <?if(in_array($k,$_GET['status'])){?> checked="checked" <?}?> /> 
			- <label for="status_<?=$k?>"><?=$v?></label> &nbsp;&nbsp;&nbsp;<?
			
		}
		
		?>
		<script type="text/javascript">
			$(document).ready( function() {
			   $("#status_mch").click( function() {
					if($('#status_mch').attr('checked')){
						$('.status_ch').attr('checked', true);
					} else {
						$('.status_ch').attr('checked', false);
					}
			   });
			});
		</script>
 
		<?
	}
	
	
	
	
	
	
	
 
	function show_rooms_check()
	{
		// Получеем массив домов
 
		 ?>
		<input type="checkbox" name="rooms_mch" id="rooms_mch" <?if($_GET['rooms_mch']){?> checked="checked" <?}?>/> - <label for="rooms_mch">Все</label>  &nbsp;&nbsp;&nbsp;
		 <?
		 
		 
		foreach($this->rooms as $k=>$v)
		{
			?> <input type="checkbox" name="rooms[<?=$k?>]" id="rooms_<?=$k?>" class="rooms_ch" value="<?=$k?>" <?if(in_array($k,$_GET['rooms'])){?> checked="checked" <?}?> /> 
			- <label for="rooms_<?=$k?>"><?=$v?></label> &nbsp;&nbsp;&nbsp;<?
			
		}
		
		?>
		<script type="text/javascript">
			$(document).ready( function() {
			   $("#rooms_mch").click( function() {
					if($('#rooms_mch').attr('checked')){
						$('.rooms_ch').attr('checked', true);
					} else {
						$('.rooms_ch').attr('checked', false);
					}
			   });
			});
		</script>
 
		<?
	}
	
	
	 
	
	 
	function show_gr_fileds()
	{
		// Получеем массив домов
 
		 ?><select name="group"><?
		foreach($this->group as $k=>$v)
		{
			?><option value="<?=$k?>" <?if( $k==$_GET['group']){?> selected  <?}?> ><?=$v?></option> <?
			
		}
		?></select><?
		 
	}
	
	
	
	
	
	
	
	
	
	
	
	
		
	// Массив описаний домов
    function get_homes_arr($show=1,$where='') 
	{
		
		
 		 $q='SELECT * FROM `homes` WHERE `show` = "'.$show.'"   ';

			// Только Админ видит статус 2
			if(check_access('admin') || $_SESSION['sh_login'] == 'fd')
			{
				 $q.=' OR `show` = "2" ';	
				 $q.=' OR `show` = "3" ';
				
			}
			// Отдел продаж видит статус 3 
			if(  $_SESSION['agency_id'] == "92" )
			{
				 $q.=' OR `show` = "3" ';	
				
			}
			
			
			$q.=' '.$where.' ';
			
		 $q.=' ORDER BY `order` ; ';	
		 return $this->sql->get_arr($q); 
    }
	
	
	
	
		
	// ИНформация о доме массив 
    function get_home_arr($id=1,$show=1) 
	{
		if(check_access('admin'))
		{
			 // для админа все show допускать если заданно all
		}
	
 		 $q='SELECT * FROM `homes` WHERE `show` = "'.$show.'" AND home_id="'.$id.'" ; ';	
		 $arr =  $this->sql->get_arr($q); 
		 return $arr[0] ;
    }
	
	 
		
	// ИНформация о секции
    function get_sec_arr( $home_id=1 , $section_id = '' ) 
	{
 		 $q='
		 SELECT homes.* ,
		 homes_sections.caption as s_caption, 
		 homes_sections.floor as s_floor, 
		 homes_sections.apartments as s_apartments, 
		 homes_sections.start_num as s_start_num,
		 homes_sections.section_id,
		 homes_sections.homes_sections_id,
		 homes_sections_cl.floor as cl_floor,	
		 homes_sections_cl.appart as cl_appart,
		 homes_sections_cl.alt_html as cl_althtml
		 
		 FROM `homes` 
		 LEFT JOIN homes_sections ON homes_sections.homes_id = homes.homes_id 
		 LEFT JOIN homes_sections_cl ON homes_sections.homes_sections_id = homes_sections_cl.homes_sections_id   
		 WHERE   home_id="'.$home_id.'" ';

		if( $section_id )
		{
			$q.=' AND homes_sections.section_id = "'.$section_id.'" ';
		}

		$q.=' ; ';
 
		 
		 $arr =  $this->sql->get_arr($q); 
		 
		 $narr = array();
		 foreach($arr as $k=>$v)
		 {
			$narr[$v['section_id']]['home_id'] = $v['home_id'];
			$narr[$v['section_id']]['homes_sections_id'] = $v['homes_sections_id'];
			$narr[$v['section_id']]['caption'] = $v['s_caption'];
			$narr[$v['section_id']]['floor'] = $v['s_floor'];
			$narr[$v['section_id']]['apartments'] = $v['s_apartments'];
			$narr[$v['section_id']]['start_num'] = $v['s_start_num'];
			$narr[$v['section_id']]['section_id'] = $v['section_id']; 
			
			// ПИшем пустые аппартаменты только если они есть 
			if($v['cl_appart'])
			{
				if( $v['cl_althtml'] ){ $cl_althtml = $v['cl_althtml']; }else{ $cl_althtml =1; }
				$narr[$v['section_id']]['clean_apartments'][ $v['cl_floor']][ $v['cl_appart']] = $cl_althtml;
			}
		 }
		 
		 if($section_id)
		 {
			return $narr[$section_id];
		 }
		 else
		 {
			return $narr;
		 }
    }
	
	
	
	
	
	
	
	// ПОЛУЧИТЬ ДАННЫЕ КВАРТИР ДЛЯ ОТРИСОВКИ ШАХМАТКИ
	function get_data_appart_arr( $home_id, $section_id='' )
	{
		 // broni.date = (select max(date) from broni as b where b.home_id = broni.home_id AND b.apartments_num = broni.apartments_num ) 
		 
		
		$q='
		select users.*,agency.*, broni.*,apartaments.*, broni.date as bdate, broni.price as bprice 
		from apartaments 
		LEFT JOIN broni ON apartaments.status_broni_id = broni.broni_id
		LEFT JOIN users ON broni.user_id = users.id 
		LEFT JOIN agency ON users.agency_id = agency.agency_id
 
		where 1=1
		 
		
		AND apartaments.home_id="'.$home_id.'" 
		 
		';
		
		if( $section_id )
		{
			$q.=' AND  apartaments.section_id = "'.$section_id.'" ';
		}
		  
		  
		  
		 
		  
		 $app_arr = $this->sql->get_arr($q);
		  
		
		 foreach( $app_arr as $k=>$result)
		 { 
			if(!$result['status'] ){$result['status']='2';}
			if(!$result['status2'] ){$result['status2']='2';}
			
			if( $result['status'] != $result['status2'] ){$result['status'] = $result['status2'];}
			
		    $data_broni[$result['home_id']][$result['apartment_num']] = $result;
			 
		 
			$data_broni[$result['home_id']][$result['apartment_num']]['status']=$result['status'];
			
			$data_broni[$result['home_id']][$result['apartment_num']]['user']=$result['user_id'];
			$data_broni[$result['home_id']][$result['apartment_num']]['date']=$result['date'];
			$data_broni[$result['home_id']][$result['apartment_num']]['login']=$result['login'];
			$data_broni[$result['home_id']][$result['apartment_num']]['name']=$result['name'];
			
			$data_broni[$result['home_id']][$result['apartment_num']]['agency_id']=$result['agency_id'];
			$data_broni[$result['home_id']][$result['apartment_num']]['agency_caption']=$result['caption'];
			$data_broni[$result['home_id']][$result['apartment_num']]['apartment_num']=$result['apartment_num']; // Номер квартиры
			$data_broni[$result['home_id']][$result['apartment_num']]['area']=$result['area']; // Площадь
			$data_broni[$result['home_id']][$result['apartment_num']]['price']=$result['price']; // Цена
			$data_broni[$result['home_id']][$result['apartment_num']]['rooms']=$result['rooms']; // Комнат
			 
			
			
			// $homes_stat[$result['home_id]'][$result[status]]++; 
		 }
 
		/*
		# Данные квартир !
		$data_apart = array();
		$query = mysqli_query($connection, "SELECT * FROM apartaments "); 
		while ($result = mysqli_fetch_array($query)) 
		{
			$data_apart[$result['home_id']][$result['section_id']][$result['apartment_num']] = $result;
		}
		*/
		
		return $data_broni;
	}
	
	
	
	
	
	
# Меню подездов в бекенде шахматки дома
function diaplay_home_secmenu( $config , $home )
{ 
	if(!$config[$home])
	{
		$config = $this->homes_config;
	}
	 //print_r($config[$home]);
	foreach($config[$home] as $k=>$v)
	{
		if( is_numeric($k) )
		{
		?>
			<div class="cl-item">
				<div class="objects-cl-nav__title"><?=$v['caption']?></div>
			</div>
		<?
		}
	}
}
	
	
	
	
# Шапка таблицы секции бекенде 
function display_home_sechead( $config , $home , $sec=1)
{
		//print $config[$home][$sec]['apartments'];
	   for($i=1; $i<=$config[$home][$sec]['apartments']; $i++)
	   {
			?>
			<td>
				<div class="tdcheck tdcheck-head">
					<input type="checkbox" data-col=".td-<?=$i?>">
				</div>
			</td>
			<?
	   }
 
	
}
	
	
	
	
	
# Шапка таблицы секции бекенде 
function display_home_sechead2( $config )
{
	   for($i=1; $i<=$config['apartments']; $i++)
	   {
			?>
			<td>
				<div class="tdcheck tdcheck-head">
					<input type="checkbox" data-col=".td-<?=$i?>">
				</div>
			</td>
			<?
	   }
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
		<div class="cl-item" style="float:none; display:inline-block;">
		<table class="objects-table" style="display:inline-block;">
	<?
 	  //print '<pre>';
	  //print_r($data);
	  //print_r($conf[caption]);
	  //print '</pre>';
	?>
 
	<?
	// Цикл по строкам (количество этажей)
	for($i=$conf['floor']; $i>0; $i--)
	{
	
		// количество нежилых квартир на этаже
		$nezk = count((array)($conf['clean_apartments'][$i] ?? [])); 

		$end_etza_num = $endnum-$conf['apartments']-1+$nezk; // стартовый номер для этажа (- квартир на этаже СО СТАТУСОМ ЕСТЬ КВАРТИРА!)
		// РАБОЧИЙ ПОРЯДКОВЫЙ НОМЕР НА ЭТАЖЕ
 
 
 
 
		#########################################
		// ТОлько для админа показываем шапку с чекбуксом
		if( (check_access('admin') || $_SESSION['sh_login'] == 'demo_admin') && $i==$conf['floor'])
		{
			?>
				<thead>
					  <tr>
							<td colspan="5" class="tdtitle" style="display:none;">Подъезд №<?=$section?></td>
						</tr>  
					<tr>
						<td>&nbsp;</td>
						<?
						 $this->display_home_sechead( $config , $home , $section );
						?> 
					</tr>
				</thead>
			<?
		}
		#########################################
		
		 
		
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
			
			#
			#################
			// нулевой столбей номер этажа
			if($k==0)
			{
				print '<td class="tdfloor" title="Этаж">'.$i.'</td>';
			}
			###################
			
			#
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
			
			
			    
			    $edit_url = 'iframe_router.php?ctr=apartments&act=order&home_id='.$home.'&apartment_num='.$end_etza_num.'&apartments='.$k;
				 
			 
		 ####### Растановка номеров квартир
		 //$GLOBALS[sql_u] .= 'UPDATE `broni` SET `apartments_num_new` = "'.$end_etza_num.'" WHERE `home_id` = "'.$home.'" AND `section_id` = "'.$section.'" AND `floor` = "'.$i.'" AND `apartments` = "'.$k.'" '.";  ";
		  
				$apart = $GLOBALS['data_apart'][$home][$section][$end_etza_num];
				
				 
				
				$apart['price'] = number_format($apart['price'], 0, '.', ' ');  
				// $apart['price'] = ' '.$apart['price'].' руб.';
		
			 	if(!$apart['rooms']){$apart['rooms']='';}
				if(!$apart['price']){$apart['price']='';}
				if( !$data[$i][$k] ){$data[$i][$k]['status']='2';} // по умолчанию свободны

               // if($apart[home_id]){$sss.='UPDATE `apartaments` SET `status` = "'.$data[$i][$k][status].'" WHERE `apartament_num` = "'.$end_etza_num.'" AND home_id="'.$apart[home_id].'" <br/>';}

				if($conf['clean_apartments'][$i][$k])
				{
					$status_text = 'Нет квартиры';
					$tdst='height:100px;';	
					$value='';
				}
				/*
		 	  <option value="0" selected="selected">Не задан</option>
			  <option value="2">Свободна</option>
			  <option value="4">Забронирована</option>
			  <option value="3">Продана</option>
			  <option value="5">Забронирована застройщиком</option>
			  <option value="6">Квартира подрядчика</option>
				*/
				elseif($data[$i][$k]['status']=='0' || $data[$i][$k]['status']=='1' || !$data[$i][$k] ) // Нет информации
				{
					
					$end_etza_num2 = '№'.$end_etza_num;
					
					$end_etza_num=1;
					$status_text = ' нет данных ';
					$tdst.=' background-color:#5d6c74;  ';	
					$value='<a class="iframe_r" href="'.$edit_url.'" style="font-size:14px;" ><b>№ '.$end_etza_num.'</b></a>';
					
					if($_SESSION['adm_caption'] && !check_access('admin') &&  $_SESSION['sh_login'] != 'demo_admin') //  админ агентства
					{
						$class="tdgreen";
						$value='<a class="iframe_r" href="#1'.$_SESSION['sh_login'].'" style="font-size:14px; color:#FFF;" ><b>№ '.$end_etza_num.'</b></a>';
					}
					elseif(check_access('admin')  || $_SESSION['sh_login'] == 'demo_admin')
					{
						$class="tdgreen";
				$value='
						<div class="tdroom"><a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" >'.$end_etza_num2.'</a></div>
						<div class="tdcheck tdcheck-body">
							<input type="checkbox" name="editapart['.$_GET['home'].']['.$end_etza_num.']" value="1" class="td-'.$k.'">
						</div>
						<div class="tdsquare">'.$apart['area'].'</div>
						<div class="tdprice">'.$apart['price'].'</div>
						
						 
						';
					}
					else
					{
						$value='<a class="iframe_r" href="'.$edit_url.'" style="font-size:14px; color:#FFF;" ><b>№'.$end_etza_num.'</b></a>';
					}
					
				} 
				elseif($data[$i][$k]['status']=='2')
				{
					$class="tdgreen";
					
					
					$end_etza_num2 = '№'.$end_etza_num;
					$status_text = '<b>Свободна</b> <br/> Комнат: '. $apart['rooms'].' <br/>   Площадь: '. $apart['area'].'м<sup>2</sup> <br/> Цена: '. $apart['price'].'р. 
					<br/>
					<img src=\''.$apart['image_pb'].'\' height=150 style=\'max-width:130px;\' />
					';

					$tdst.=' background-color:#89FFA4;  ';	
					if(!$_SESSION['adm_caption']  && !check_access('admin')) // не админ агентства
					{
						$value='<a class="iframe_r" href="'.$edit_url.'" style="font-size:14px; color:#000;" ><b>'.$end_etza_num2.'</b></a>';
					}
					elseif(check_access('admin')  || $_SESSION['sh_login'] == 'demo_admin')
					{
					 	  
				$value='
						<div class="tdroom"><a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" >'.$end_etza_num2.'</a></div>
						<div class="tdcheck tdcheck-body">
							<input type="checkbox" name="editapart['.$_GET['home'].']['.$end_etza_num.']" value="1" class="td-'.$k.'">
						</div>
						<div class="tdsquare">'.$apart['area'].'</div>
						<div class="tdprice">'.$apart['price'].'</div>
						
						 
						';
			
					}
					else
					{
						$value='<a class="iframe_r" href="'.$edit_url.'" style="font-size:14px; color:#000;" ><b>№'.$end_etza_num.'</b></a>';
					}
				}
				elseif($data[$i][$k]['status']=='3')
				{
					
					$class="tdred";
					$end_etza_num2 = '№'.$end_etza_num;
						
						if(check_access('admin')  || $_SESSION['sh_login'] == 'demo_admin')
						{
						$tdst.=' height:95px; ';
						}
					$status_text = '<b>Продана</b> <br/> Комнат: '. $apart['rooms'].' <br/>   Площадь: '. $apart['area'].'м<sup>2</sup> <br/>
					<img src=\''.$apart['image_pb'].'\' height=150 style=\'max-width:130px;\' />
					';
					
					
					$tdst.=' background-color:#FF8A90;   ';	
					 $value='<a class="iframe_r" href="'.$edit_url.'" style="font-size:14px;" ><b>№'.$end_etza_num.'</b></a>';
					if(check_access('admin'))
					{
					$value='
						<div class="tdroom"><a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" >'.$end_etza_num2.'</a></div>
						<div class="tdcheck tdcheck-body">
							<input type="checkbox" name="editapart['.$_GET['home'].']['.$end_etza_num.']" value="1" class="td-'.$k.'">
						</div>
						<div class="tdsquare">'.$apart['area'].'</div>
						<div class="tdprice">'.$apart['price'].'</div>
						
						 
						';
					}
				}
				elseif($data[$i][$k]['status']=='5')
				{
					$class="tdgrey";
					$end_etza_num2 = '№'.$end_etza_num;	
					
				
					 
					if(check_access('admin')  || $_SESSION['sh_login'] == 'demo_admin')
					{
						$tdst.=' height:95px; ';
						$status_text = 'Забронирована застройщиком';
						$tdst.=' background-color:#D4E6FF;   ';	
						$value='
						<div class="tdroom"><a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" >'.$end_etza_num2.'</a></div>
						<div class="tdcheck tdcheck-body">
							<input type="checkbox" name="editapart['.$_GET['home'].']['.$end_etza_num.']" value="1" class="td-'.$k.'">
						</div>
						<div class="tdsquare">'.$apart['area'].'</div>
						<div class="tdprice">'.$apart['price'].'</div>
						
						 
						';
			
			
					}
					elseif($_SESSION['agency_id'] == "92" && 1==2) // Скрываем брони застройщика для ОП
					{
						$class="tdgrey";
						$status_text = 'Забронирована застройщиком';
						$tdst.=' background-color:#D4E6FF;   ';	
						$value='<a class="iframe_r" href="'.$edit_url.'" style="font-size:14px;" ><b> '.$end_etza_num2.'</b></a>';
					}
					else
					{
						$class="tdred";
						$status_text = 'Продана';
						$tdst.=' background-color:#FF8A90;   ';	
						$value='<a  class="iframe_r" href="'.$edit_url.'" style="font-size:14px;" ><b> '.$end_etza_num2.'</b></a>';
					}
				} 
				elseif($data[$i][$k]['status']=='4')
				{
					$end_etza_num2 = '№'.$end_etza_num;	
					$class="tdyellow";
					
						$value='<a  class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000000;" ><b>№'.$end_etza_num.'</b></a>';
						if(check_access('admin')  || $_SESSION['sh_login'] == 'demo_admin')
						{
							$tdst.=' height:95px; ';
							$tdst.=' background-color:#FFFF3E; color:#000; ';	
							
						$value='
						<div class="tdroom"><a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" >'.$end_etza_num2.'</a></div>
						<div class="tdcheck tdcheck-body">
							<input type="checkbox" name="editapart['.$_GET['home'].']['.$end_etza_num.']" value="1" class="td-'.$k.'">
						</div>
						<div class="tdsquare">'.$apart['area'].'</div>
						<div class="tdprice">'.$apart['price'].'</div>
						
						 
						';
			
						
							$status_text = 'Бронь '.$data[$i][$k]['date'] . '<br>' . $data[$i][$k]['login'] . '<br>'. $data[$i][$k]['name'];
							$status_text .=' <br/><b>'.$data[$i][$k]['agency_caption'].'</b>';
						}
						elseif($_SESSION['adm_caption'] && $_SESSION['agency_id'] == $data[$i][$k]['agency_id'] ) // Если админ агенства и бронь сотрудником агентства
						{
							
							$status_text = 'Бронь '.$data[$i][$k]['date'] . '<br>' . $data[$i][$k]['login'] . '<br>'. $data[$i][$k]['name'];
							$value='<a class="iframe_r" href="'.$edit_url.'" style="font-size:14px; color:#000;" ><b>№'.$end_etza_num.'</b></a>';
							$tdst.=' background-color:#FFA500; color:#000; ';	
						}
						elseif(  $_SESSION['agency_id'] == '92' ) //СОТРУДНИК ОП
						{
							
							$status_text = 'Бронь '.$data[$i][$k]['date'] . '<br/>' . $data[$i][$k]['login'] . '<br>'. $data[$i][$k]['name'];
							$value='<a  class="iframe_r" href="'.$edit_url.'" style="font-size:14px; color:#000;" ><b>№'.$end_etza_num.'</b></a>';
							$tdst.=' background-color:#FFFF00; color:#000; ';	
						}
						
						
						
						
						
						else
						{	
							$value='<a  class="iframe_r" href="'.$edit_url.'" style="font-size:14px; color:#000;" ><b>№'.$end_etza_num.'</b></a>';
							$tdst.=' background-color:#FFFF00; color:#000; ';	
						//	$status_text = 'Бронь '.$data[$i][$k][date] . '<br/>';
							
							$status_text = '<b>Бронь <br/> '.$data[$i][$k]['date'] . '</b> <br/> Комнат: '. $apart['rooms'].' <br/>   Площадь: '. $apart['area'].'м<sup>2</sup>    
							<br/>
							<img src=\''.$apart['image_pb'].'\' height=150 style=\'max-width:130px;\' />
							';
					
						}
				}
				elseif($data[$i][$k]['status']=='6')
				{
						$end_etza_num2 = '№'.$end_etza_num;	
						$class="tdblue";
					
					if(check_access('admin')  || $_SESSION['sh_login'] == 'demo_admin')
					{
						
						$status_text = 'Квартира подрядчика';
						$tdst.=' background-color:#9933ff;   ';	
							$tdst.=' height:95px; ';	
							
					
						/*
						$value='<a  class="iframe_r" href="'.$edit_url.'" style="font-size:10px;" ><b>№'.$end_etza_num.'</b></a>
						<br/>
						<input type="checkbox" name="editapart['.$_GET[home].']['.$end_etza_num.']" value="1"><br>
						
						<a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" >
						<span style="font-size:8px;">'.$apart[area].' <br/> '.$apart[price].'</span> 
						</a>

					 ';
					 */
					 
					 
					 	$value='
						<div class="tdroom"><a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" >'.$end_etza_num2.'</a></div>
						<div class="tdcheck tdcheck-body">
							<input type="checkbox" name="editapart['.$_GET['home'].']['.$end_etza_num.']" value="1" class="td-'.$k.'">
						</div>
						<div class="tdsquare">'.$apart['area'].'</div>
						<div class="tdprice">'.$apart['price'].'</div>
						
						 
						';
			

					}
					elseif($_SESSION['agency_id'] == "92")
					{
						$class="tdblue";
						$status_text = 'Квартира подрядчика';
						$value='<a class="iframe_r" href="'.$edit_url.'" style="font-size:14px;" ><b>№ '.$end_etza_num.'</b></a>';
					}
					else
					{
						$class="tdred"; 
						$status_text = 'Продана';
						$value='<a href="#'.$_SESSION['agency_id'].'" style="font-size:14px;" ><b>№ '.$end_etza_num.'</b></a>';
					}
				}
				
				
				if($conf['clean_apartments'][$i][$k]) // пустая квартира
				{
					/*
					if(check_access('admin'))
					{
					print '<td style=" height:100px;">&nbsp;</td>';
					}
					else
					{
						print '<td style="height:36px;">&nbsp;</td>';
					}
					*/
					
					?>
					<td  <? if(!check_access('admin') &&  $_SESSION['sh_login'] != 'demo_admin'){print 'style="height:auto;"';} else{print 'style="height:74px;"';}?>>
						
					</td>
					<?
					
				}
				else
				{
					?>
					<td class="<?=$class?>" rel="tooltip"  title="<?=$status_text?>" <? if(!check_access('admin')){print 'style="height:auto;"';}?>>
						<?=$value?>
					</td>
					<?
					/*
					print '<td align="center">';
					print '<div '.$tdst2.' style="'.$tdst.' border-radius: 7px;   border: solid 1px #FFF;  padding: 5px;" title="'.$status_text.'    " class="tdapartment" rel="tooltip">';
					print $value;
					print '</div>';
					print '</td>';
					*/
					$endnum--; // вытонумерация квартир
					
				}
				
				
			}
		}
		print '</tr>';
	}
		
print ' </table>
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
function disp_home(  $home, $section ,$notable='' )
{ 
 
/*




ТУДУ 
1 ПУБЛИЧНАЯ ВЕРСИЯ ДЛЯ САЙТА
2. ВЕРСИЯ ДЛЯ ПЕЧАТИ

1кk

НЕ ВЕРНО ВЫВОДТСЯ ДАТА АТРИБУТЫ в публичной части

шаблоны? обертка для таблицы? чанки типа - методы просто вывода таблицы и вывода тд
глобальная ссылка на ифрейм!
*/
 
  /*
 
 $config = $GLOBALS['homes']; 
 
 print '<pre>';
 print_r($config);
 print '</pre>';
 $data= $GLOBALS['data_broni'];

$q = 'SELECT * FROM homes';
$h_arr = $this->mysql->get_arr($q);
foreach( $h_arr  as $hk => $hv )// ЦИкл по домам из базы (не берем лишние дома из конфига)
{
	print '<h1>home_id-'.$hv['home_id'].'</h1>';
 
	foreach( $config[ $hv['home_id'] ] as $kd => $vd)
	{
		print_r($vd);
 
		if( !is_int($kd) ){print 'ИД ДОМА НЕ ЧИСЛО (служебное чтото)<br/>'; continue;}
		
		print '<h3>!!секция-'.$kd.'</h3>';
		$data=array();
		
		$data['homes_id'] = $hv['homes_id'];
		$data['section_id'] = $kd;
		$data['caption'] = 'Секция №'.$kd;
		$data['floor'] = $vd['floor'];
		$data['apartments'] = $vd['apartments'];
		$data['start_num'] = $vd['start_num'];
		$id=0;
		
		$find_array['homes_id'] = $hv['homes_id']; 
		$find_array['section_id'] = $kd; 
		 
		//$id=$this->mysql->insert('homes_sections',$data);
		$id = $this->mysql->insert_or_not('homes_sections',$find_array,$data,'homes_sections_id');
		print 'ВСТАВИЛИ ИНФОРМАЦИЮ О СЕКЦИИ<br>';
		print '<pre>';
		print_r($data);
		print '</pre>';
		
			print 'МАССИВ ПУСТЫХ<br>';
		print '<pre>';
		print_R($vd);
		print '</pre>';
		print '---МАССИВ ПУСТЫХ<br>';
		if($id)
		{			
			foreach( $vd['clean_apartments'] as $cl_k => $cl_v )
			{
				
				foreach($cl_v as $cl_c2=>$cl_v2 )
				{
					 
					print '<h3>homes_sections_id-'.$id.'</h3>';
					$data=array();
					$data['homes_sections_id'] = $id;
					$data['floor'] = $cl_k;
					$data['appart'] = $cl_c2;
					
					print '<pre>';
					print_r($data);
					print '</pre>';
					 $this->mysql->insert('homes_sections_cl',$data,1);
					 print '<br/>';
					
				}
			}
		}
	}
	
}
 
  */

  
$data = $this->get_data_appart_arr( $home , $section  );
$conf = $this->get_sec_arr( $home , $section );

//print '<pre>';
//print_r($conf);
//print '</pre>';
 
 $ckv=0;
 
 
 //print '<pre>';
 //print_r($conf['clean_apartments']);
 //print '</pre>';
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
 
 
 if(!$notable)
 {
 	?>
		<div class="cl-item" style="float:none; display:inline-block;">
		<table class="objects-table" style="display:inline-block;">
	<?
 }
 
 	 
	// Цикл по строкам (количество этажей)
	for($i=$conf['floor']; $i>0; $i--)
	{
		
	 
		// количество нежилых квартир на этаже
		$nezk = count((array)($conf['clean_apartments'][$i] ?? [])); 
		
		// РАБОЧИЙ ПОРЯДКОВЫЙ НОМЕР НА ЭТАЖЕ
		$end_etza_num = $endnum-$conf['apartments']-1+$nezk; // стартовый номер для этажа (- квартир на этаже СО СТАТУСОМ ЕСТЬ КВАРТИРА!)

		#########################################
		// ТОлько для админа показываем шапку с чекбуксом
		if(  $i==$conf['floor'])
		{
			?>
				<thead>
					  <tr>
							<td colspan="5" class="tdtitle" style="display:none;"> <?=$conf['caption']?></td>
						</tr>  
					<tr>
						<td>&nbsp;</td>
						<?
						if( check_access('admin') || $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'demo_admin')
						{
							$this->display_home_sechead2( $conf  );
						}
						?>  
					</tr>
				</thead>
			<?
		}
		#########################################

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
			
			#
			#################
			// нулевой столбей номер этажа
			if($k==0)
			{
				print '<td class="tdfloor" title="Этаж">'.$i.'</td>';
			}
			###################
			
			#
			else // ИНфа о квартире
			{
			  
				
				// Данные по квартире читаются по дому+номеру квартиры
				$apart = $data[$home][$end_etza_num];
				  
				  $bprice = '';  // Сбрасываем перед каждой квартирой
				  if($apart['price'] != $apart['bprice'])
				  {
					  $bprice = number_format($apart['bprice'], 0, '.', ' ');  
				  }
				$apart['price'] = number_format($apart['price'], 0, '.', ' ');  
				 
				
				
				// Убираем нули
			 	if(!$apart['rooms']){$apart['rooms']='';}
				if(!$apart['price']){$apart['price']='';}
				
				 $apart['bdate'] = date('d.m.Y H:i',strtotime( $apart['bdate']) );
			 
			 
				if($conf['clean_apartments'][$i][$k])
				{
					$value='';
					$status_text = 'Нет квартиры';
					$stext = '';
					$value='';
					
						// 811 кватиры с одинаковыми номерами
						if( ($_GET['home']==38 && $i==14 && $k==3) || ($_GET['home']==38 && $i==16 && $k==6 ) )
						{
							$class="tdred";
							$value=$conf['clean_apartments'][$i][$k];
							
							if( check_access('admin') || $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'demo_admin') // АДМИН
							{
								
								
								if( $_GET['home']==38 && $i==14 && $k==3 ){$apart['area']='26,7'; $apart['image_pb'] = 'https://' . $GLOBALS['config']['domain'] . '/sahmatka/pbplans/38/1sec_8-16flor/1 sec_ 8-16 flor_1С 26_7.svg';}
								
								
								if( $_GET['home']==38 && $i==16 && $k==6 ){$apart['area']='58,6'; $apart['image_pb'] = 'https://' . $GLOBALS['config']['domain'] . '/sahmatka/pbplans/38/1sec_8-16flor/1 sec_ 8-16 flor_2K 58_6.svg';}
	
								
								
								$value='
								<div class="tdroom"><a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" >'.$end_etza_num2.'</a></div>
								<div class="tdcheck tdcheck-body">';
								if( check_access('admin') || $_SESSION['sh_login'] == 'demo_admin')
								{
									$value.='<input type="checkbox" name="editapart['.$_GET['home'].']['.$end_etza_num.']" value="1" class="td-'.$k.'">';
								}
									$value.='</div>
								<div class="tdsquare">'.$apart['area'].'</div>
								<div class="tdsquare">'.$apart['price'].'</div>
								';
		 
								$status_text  = '  '. $apart['rooms'].', '. $apart['area'].'м<sup>2</sup> 
								<br/>
								<img src=\''.$apart['image_pb'].'\' height=150 style=\'max-width:150px;\' />
								';
							}
							
							else //   ОСТАЛЬНЫЕ
							{
								$value='<a class="iframe_r" href="'.$edit_url.'"  style="font-size:18px;" ><b>'.$end_etza_num.'</b></a>'; 
								$status_text = 'Комнат: '. $apart['rooms'].' <br/>   Площадь: '. $apart['area'].'м<sup>2</sup>  
								<br/>
								<img src=\''.$apart['image_pb'].'\' height=150 style=\'max-width:150px;\' />
								';
							}
						}
					
					
					
					
					
					
				}
				 
				else // не пустая квартира
				{
					$value='';
					$edit_url = 'iframe_router.php?ctr=apartments&act=order&home_id='.$home.'&apartment_num='.$end_etza_num.'&apartments='.$k;
					$end_etza_num2 = '№'.$end_etza_num;
 
					if( check_access('admin') || $_SESSION['sh_login'] == 'fd'  || $_SESSION['sh_login'] == 'demo_admin' ) // АДМИН
					{
						$value='
						<div class="tdroom"><a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" >'.$end_etza_num2.'</a></div>
						<div class="tdcheck tdcheck-body">';
						
						if( check_access('admin') || $_SESSION['sh_login'] == 'demo_admin')
						{
						$value.='<input type="checkbox" name="editapart['.$_GET['home'].']['.$end_etza_num.']" value="1" class="td-'.$k.'">';
						}
						
						$value.='</div>
						<div class="tdsquare">'.$apart['area'].'</div>
						<div class="tdsquare">'.$apart['price'].'</div>
						';
 
						$status_text  = '  '. $apart['rooms'].', '. $apart['area'].'м<sup>2</sup> 
						<br/>
						<img src=\''.$apart['image_pb'].'\' height=150 style=\'max-width:150px;\' />
						';
					}
					
					else //   ОСТАЛЬНЫЕ
					{
						$value='<a class="iframe_r" href="'.$edit_url.'"  style="font-size:18px;" ><b>'.$end_etza_num.'</b></a>'; 
						$status_text = 'Комнат: '. $apart['rooms'].' <br/>   Площадь: '. $apart['area'].'м<sup>2</sup>  
						<br/>
						<img src=\''.$apart['image_pb'].'\' height=150 style=\'max-width:150px;\' />
						';
					}
				if($apart['status']=='0' || $apart['status']=='1' || !$apart ) // Нет информации
				{  
					$stext = 'Свободна';
					$class="tdgreen";
				} 
				elseif($apart['status']=='2')
				{
					$stext = 'Свободна';
					$class="tdgreen";
					$stext .=' <br/><b>'.$apart['price'].'</b> ';
				}
				elseif($apart['status']=='3')
				{
					if( check_access('admin') ||  $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'demo_admin')
					{
						$stext = 'Продана <br/>'.$apart['bdate'].'<br/>';
						$class="tdred";
					}
					else
					{
						
					$stext = 'Продана';
					$class="tdred";
					}
					
				}
				elseif($apart['status']=='4')
				{
					$class="tdyellow";
					if( check_access('admin')     || $_SESSION['sh_login'] == 'demo_admin' )
					{
						$stext = 'Бронь '.$apart['bdate'].'<br/>'.$apart['login'] . ' - '. $apart['name'];
						$stext .=' <br/><b>'.$apart['agency_caption'].'</b> ';
						if($bprice){ $stext .=' <br/><b style=\'color:#00CDAD;\'>Бронь: '.$bprice.'</b> '; }
					}
					elseif( $_SESSION['sh_login'] == 'fd' )
					{
						$stext = 'Бронь '; 
					}
					elseif($_SESSION['adm_caption'] && $_SESSION['agency_id'] == $apart['agency_id'] ) // Если админ агенства и бронь сотрудником агентства
					{
						$stext = 'Бронь '.$apart['bdate'].'<br>'.$apart['login'].' - '. $apart['name']; 
					}
					elseif(  $_SESSION['agency_id'] == '92' ) //СОТРУДНИК ОП
					{
						$stext = 'Бронь '.$apart['bdate'].'<br/>'.$apart['login'].' - '. $apart['name'];	
						$stext .=' <br/><b>'.$apart['price'].'</b> ';
					}
					else
					{	
						$stext = 'Бронь'; 
					}
				}
				elseif($apart['status']=='5')
				{
					
					if( check_access('admin')  || $_SESSION['sh_login'] == 'em_nsv' || $_SESSION['sh_login'] == 'director' || $_SESSION['sh_login'] == 'demo_admin')
					{
						$class="tdgrey";
						$stext ='Забронирована застройщиком <br/>'.$apart['bdate'].'<br>'.$apart['login'];
					}
					elseif( $_SESSION['sh_login'] == 'fd' )
					{
						$class="tdgrey";
						$stext ='Забронирована застройщиком  ';
					}
					else
					{
						$class="tdred";
						$stext = 'Продана'; 
					}
				}
				elseif($apart['status']=='6')
				{
					if(check_access('admin')   || $_SESSION['sh_login'] == 'em_nsv' || $_SESSION['sh_login'] == 'director' || $_SESSION['sh_login'] == 'demo_admin' )
					{
						$class="tdblue";
						$stext = 'Квартира подрядчика <br/>'.$apart['bdate'].'<br>'.$apart['login']; 
					}
					elseif(  $_SESSION['sh_login'] == 'fd' )
					{
						$class="tdblue";
						$stext = 'Квартира подрядчика '; 
					}
					else
					{
						$class="tdred"; 
						$stext = 'Продана';
					}
				}
				
 	 
							
				} // Не пустая
				 
				
	// Версия для печати
	// Версия для публичной части сайта 
	// (разные шаблоны вывода квартир)
				if($conf['clean_apartments'][$i][$k]) // пустая квартира
				{
					
					if( ($_GET['home']==38 && $i==14 && $k==3) || ($_GET['home']==38 && $i==16 && $k==6) )
					{
						?>
						<td class="<?=$class?>" rel="tooltip"  title="<a style='color:#FFF;' href='<?=$edit_url?>'><?=$stext?><br/><?=$status_text?></a>" <? if(!check_access('admin')){print 'style="height:auto;"';}?>>
						<?=$value?>
						</td>
						<?
					}
					else
					{
						?>
						<td  <? if(!check_access('admin') && $_SESSION['sh_login'] != 'demo_admin'){print 'style="height:auto;"';} else{print 'style="height:74px;"';}?>>	<?=$value?> </td>			
						<?
					}
				}
				else
				{
					?>
					<td class="<?=$class?>" rel="tooltip"  title="<a style='color:#FFF;' href='<?=$edit_url?>'><?=$stext?><br/><?=$status_text?></a>" <? if(!check_access('admin')){print 'style="height:auto;"';}?>>
						<?=$value?>
					</td>
					<?
					$endnum--; // вытонумерация квартир
				}
				
				
			}
		}
		print '</tr>';
	}
	


if(!$notable)
 {
	print ' </table>
	</div>

	';
 }
 


}

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
		
	
//template_apartament - шаблон аппартамента (инклайд)
/*
$conf - Массив конфиг квартир
$home - дом/район номер
$data - данные о бронировании
$template_apartament
*/
function disp_homen(  $home, $section ,$notable='' )
{ 
 
 

  
$data = $this->get_data_appart_arr( $home , $section  );
$conf = $this->get_sec_arr( $home , $section );

//print '<pre>';
//print_r($conf);
//print '</pre>';
 
 $ckv=0;
 
 
 //print '<pre>';
 //print_r($conf['clean_apartments']);
 //print '</pre>';
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
 
 
 if(!$notable)
 {
 	?>
		<div class="cl-item" style="float:none; display:inline-block;">
		<table class="objects-table" style="display:inline-block;">
	<?
 }
 
 	 
	// Цикл по строкам (количество этажей)
	for($i=$conf['floor']; $i>0; $i--)
	{
		
	 
		// количество нежилых квартир на этаже
		$nezk = count((array)($conf['clean_apartments'][$i] ?? [])); 
		
		// РАБОЧИЙ ПОРЯДКОВЫЙ НОМЕР НА ЭТАЖЕ
		$end_etza_num = $endnum-$conf['apartments']-1+$nezk; // стартовый номер для этажа (- квартир на этаже СО СТАТУСОМ ЕСТЬ КВАРТИРА!)

		#########################################
		// ТОлько для админа показываем шапку с чекбуксом
		if(  $i==$conf['floor'])
		{
			?>
				<thead>
					  <tr>
							<td colspan="5" class="tdtitle" style="display:none;"> <?=$conf['caption']?></td>
						</tr>  
					<tr>
						<td>&nbsp;</td>
						<?
						if( check_access('admin') || $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'demo_admin')
						{
							$this->display_home_sechead2( $conf  );
						}
						?>  
					</tr>
				</thead>
			<?
		}
		#########################################

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
			
			#
			#################
			// нулевой столбей номер этажа
			if($k==0)
			{
				print '<td class="tdfloor" title="Этаж">'.$i.'</td>';
			}
			###################
			
			#
			else // ИНфа о квартире
			{
			  
				
				// Данные по квартире читаются по дому+номеру квартиры
				$apart = $data[$home][$end_etza_num];
				  
				  $bprice = '';  // Сбрасываем перед каждой квартирой
				  if($apart['price'] != $apart['bprice'])
				  {
					  $bprice = number_format($apart['bprice'], 0, '.', ' ');  
				  }
				$apart['price'] = number_format($apart['price'], 0, '.', ' ');  
				 
				
				
				// Убираем нули
			 	if(!$apart['rooms']){$apart['rooms']='';}
				if(!$apart['price']){$apart['price']='';}
				
				 $apart['bdate'] = date('d.m.Y H:i',strtotime( $apart['bdate']) );
			 
			 
				if($conf['clean_apartments'][$i][$k])
				{
					$value='';
					$status_text = 'Нет квартиры';
					$stext = '';
					$value='';
					
						// 811 кватиры с одинаковыми номерами
						if( ($_GET['home']==38 && $i==14 && $k==3) || ($_GET['home']==38 && $i==16 && $k==6 ) )
						{
							$class="tdred";
							$value=$conf['clean_apartments'][$i][$k];
							
							if( check_access('admin') || $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'demo_admin') // АДМИН
							{
								
								
								if( $_GET['home']==38 && $i==14 && $k==3 ){$apart['area']='26,7'; $apart['image_pb'] = get_app_url() . '/sahmatka/pbplans/38/1sec_8-16flor/1 sec_ 8-16 flor_1С 26_7.svg';}
								
								
								if( $_GET['home']==38 && $i==16 && $k==6 ){$apart['area']='58,6'; $apart['image_pb'] = get_app_url() . '/sahmatka/pbplans/38/1sec_8-16flor/1 sec_ 8-16 flor_2K 58_6.svg';}
	
								
								
								$value='
								<div class="tdroom"><a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" >'.$end_etza_num2.'</a></div>
								<div class="tdcheck tdcheck-body">';
								if( check_access('admin') || $_SESSION['sh_login'] == 'demo_admin')
								{
									$value.='<input type="checkbox" name="editapart['.$_GET['home'].']['.$end_etza_num.']" value="1" class="td-'.$k.'">';
								}
									$value.='</div>
								<div class="tdsquare">'.$apart['area'].'</div>
								<div class="tdsquare">'.$apart['price'].'</div>
								';
		 
								$status_text  = '  '. $apart['rooms'].', '. $apart['area'].'м<sup>2</sup> 
								<br/>
								<img src=\''.$apart['image_pb'].'\' height=150 style=\'max-width:150px;\' />
								';
							}
							
							else //   ОСТАЛЬНЫЕ
							{
								$value='<a class="iframe_r" href="'.$edit_url.'"  style="font-size:18px;" ><b>'.$end_etza_num.'</b></a>'; 
								$status_text = 'Комнат: '. $apart['rooms'].' <br/>   Площадь: '. $apart['area'].'м<sup>2</sup>  
								<br/>
								<img src=\''.$apart['image_pb'].'\' height=150 style=\'max-width:150px;\' />
								';
							}
						}
					
					
					
					
					
					
				}
				 
				else // не пустая квартира
				{
					$value='';
					$edit_url = 'iframe_router.php?ctr=apartments&act=order&home_id='.$home.'&apartment_num='.$end_etza_num.'&apartments='.$k;
					$end_etza_num2 = '№'.$end_etza_num;
 
					if( check_access('admin') || $_SESSION['sh_login'] == 'fd'  || $_SESSION['sh_login'] == 'demo_admin' ) // АДМИН
					{
						
						## КАСТОМНЫЙ НОМЕР !!!!!!!!!!!
						if($apart['display_num'])
						{
							$dispnum = $apart['display_num'];
						}
						else
						{
							$dispnum = $end_etza_num;
						}
						$value='
						<div class="tdroom"><a class="iframe_r" href="'.$edit_url.'" style="font-size:10px; color:#000;" >'.$dispnum.'</a></div>
						<div class="tdcheck tdcheck-body">';
						
						if( check_access('admin') || $_SESSION['sh_login'] == 'demo_admin')
						{
						$value.='<input type="checkbox" name="editapart['.$_GET['home'].']['.$end_etza_num.']" value="1" class="td-'.$k.'">';
						}
						
						$value.='</div>
						<div class="tdsquare">'.$apart['area'].'</div>
						<div class="tdsquare">'.$apart['price'].'</div>
						';
 
						$status_text  = '  '. $apart['rooms'].', '. $apart['area'].'м<sup>2</sup> 
						<br/>
						<img src=\''.$apart['image_pb'].'\' height=150 style=\'max-width:150px;\' />
						';
					}
					
					else //   ОСТАЛЬНЫЕ
					{
						$value='<a class="iframe_r" href="'.$edit_url.'"  style="font-size:18px;" ><b>'.$end_etza_num.'</b></a>'; 
						$status_text = 'Комнат: '. $apart['rooms'].' <br/>   Площадь: '. $apart['area'].'м<sup>2</sup>  
						<br/>
						<img src=\''.$apart['image_pb'].'\' height=150 style=\'max-width:150px;\' />
						';
					}
				if($apart['status']=='0' || $apart['status']=='1' || !$apart ) // Нет информации
				{  
					$stext = 'Свободна';
					$class="tdgreen";
				} 
				elseif($apart['status']=='2')
				{
					$stext = 'Свободна';
					$class="tdgreen";
					$stext .=' <br/><b>'.$apart['price'].'</b> ';
				}
				elseif($apart['status']=='3')
				{
					if( check_access('admin') ||  $_SESSION['sh_login'] == 'fd' || $_SESSION['sh_login'] == 'demo_admin')
					{
						$stext = 'Продана <br/>'.$apart['bdate'].'<br/>';
						$class="tdred";
					}
					else
					{
						
					$stext = 'Продана';
					$class="tdred";
					}
					
				}
				elseif($apart['status']=='4')
				{
					$class="tdyellow";
					if( check_access('admin')     || $_SESSION['sh_login'] == 'demo_admin' )
					{
						$stext = 'Бронь '.$apart['bdate'].'<br/>'.$apart['login'] . ' - '. $apart['name'];
						$stext .=' <br/><b>'.$apart['agency_caption'].'</b> ';
						if($bprice){ $stext .=' <br/><b style=\'color:#00CDAD;\'>Бронь: '.$bprice.'</b> '; }
					}
					elseif( $_SESSION['sh_login'] == 'fd' )
					{
						$stext = 'Бронь '; 
					}
					elseif($_SESSION['adm_caption'] && $_SESSION['agency_id'] == $apart['agency_id'] ) // Если админ агенства и бронь сотрудником агентства
					{
						$stext = 'Бронь '.$apart['bdate'].'<br>'.$apart['login'].' - '. $apart['name']; 
					}
					elseif(  $_SESSION['agency_id'] == '92' ) //СОТРУДНИК ОП
					{
						$stext = 'Бронь '.$apart['bdate'].'<br/>'.$apart['login'].' - '. $apart['name'];	
						$stext .=' <br/><b>'.$apart['price'].'</b> ';
					}
					else
					{	
						$stext = 'Бронь'; 
					}
				}
				elseif($apart['status']=='5')
				{
					
					if( check_access('admin')  || $_SESSION['sh_login'] == 'em_nsv' || $_SESSION['sh_login'] == 'director' || $_SESSION['sh_login'] == 'demo_admin')
					{
						$class="tdgrey";
						$stext ='Забронирована застройщиком <br/>'.$apart['bdate'].'<br>'.$apart['login'];
					}
					elseif( $_SESSION['sh_login'] == 'fd' )
					{
						$class="tdgrey";
						$stext ='Забронирована застройщиком  ';
					}
					else
					{
						$class="tdred";
						$stext = 'Продана'; 
					}
				}
				elseif($apart['status']=='6')
				{
					if(check_access('admin')   || $_SESSION['sh_login'] == 'em_nsv' || $_SESSION['sh_login'] == 'director' || $_SESSION['sh_login'] == 'demo_admin' )
					{
						$class="tdblue";
						$stext = 'Квартира подрядчика <br/>'.$apart['bdate'].'<br>'.$apart['login']; 
					}
					elseif(  $_SESSION['sh_login'] == 'fd' )
					{
						$class="tdblue";
						$stext = 'Квартира подрядчика '; 
					}
					else
					{
						$class="tdred"; 
						$stext = 'Продана';
					}
				}
				
 	 
							
				} // Не пустая
				 
				
	// Версия для печати
	// Версия для публичной части сайта 
	// (разные шаблоны вывода квартир)
				if($conf['clean_apartments'][$i][$k]) // пустая квартира
				{
					
					if( ($_GET['home']==38 && $i==14 && $k==3) || ($_GET['home']==38 && $i==16 && $k==6) )
					{
						?>
						<td class="<?=$class?>" rel="tooltip"  title="<a style='color:#FFF;' href='<?=$edit_url?>'><?=$stext?><br/><?=$status_text?></a>" <? if(!check_access('admin')){print 'style="height:auto;"';}?>>
						<?=$value?>
						</td>
						<?
					}
					else
					{
						?>
						<td  <? if(!check_access('admin') && $_SESSION['sh_login'] != 'demo_admin'){print 'style="height:auto;"';} else{print 'style="height:74px;"';}?>>	<?=$value?> </td>			
						<?
					}
				}
				else
				{
					?>
					<td class="<?=$class?>" rel="tooltip"  title="<a style='color:#FFF;' href='<?=$edit_url?>'><?=$stext?><br/><?=$status_text?></a>" <? if(!check_access('admin')){print 'style="height:auto;"';}?>>
						<?=$value?>
					</td>
					<?
					$endnum--; // вытонумерация квартир
				}
				
				
			}
		}
		print '</tr>';
	}
	


if(!$notable)
 {
	print ' </table>
	</div>

	';
 }
 


}

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
// Для виджета 
function disp_home_p(  $home, $section ,$notable='' )
{  
 
 
$data = $this->get_data_appart_arr( $home,$section  );
$conf = $this->get_sec_arr( $home , $section );
 
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
  
	// Цикл по строкам (количество этажей)
	for($i=$conf['floor']; $i>0; $i--)
	{ 
		 
		// количество нежилых квартир на этаже
		$nezk = count((array)($conf['clean_apartments'][$i] ?? [])); 
		
		// РАБОЧИЙ ПОРЯДКОВЫЙ НОМЕР НА ЭТАЖЕ
		$end_etza_num = $endnum-$conf['apartments']-1+$nezk; // стартовый номер для этажа (- квартир на этаже СО СТАТУСОМ ЕСТЬ КВАРТИРА!)  

		#########################################
	 
		if(  $i==$conf['floor'])
		{
			?>
				<thead>
					  <tr>
							<td colspan="10" class="tdtitle"> <?=$conf['caption']?></td>
						</tr>  
					 
				</thead>
			<?
		}
		#########################################

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
			
			#
			#################
			// нулевой столбей номер этажа
			if($k==0)
			{
				print '<td class="tdfloor" title="Этаж">'.$i.'</td>';
			}
			###################
			
			#
			else // ИНфа о квартире
			{
				// Данные по квартире читаются по дому+номеру квартиры
				$apart = $data[$home][$end_etza_num];
				  
				$apart['price'] = number_format($apart['price'], 0, '.', ' ');  
				 
				// Убираем нули
			 	if(!$apart['rooms']){$apart['rooms']='';}
				if(!$apart['price']){$apart['price']='';}
				
			 
				if($conf['clean_apartments'][$i][$k])
				{
					$status_text = 'Нет квартиры';
					$stext = '';
					$value='';
				}
				 
				else // не пустая квартира
				{
				
				
				if($apart['status']=='0' || $apart['status']=='1' || !$apart || $apart['status']=='2') // Свободна
				{  
					$data_status = ''; 
					$st_bg = 'background: rgb(137, 255, 164);';
				} 
				elseif($apart['status']=='4') // бронь
				{
					$data_status = '';
					$st_bg = 'background:#FFFF3E;';
				}
				else // Все остальные проданы
				{
					$data_status = 'sale';
					$st_bg = 'background: rgb(255, 138, 144);';
				} 
					
					
					$edit_url = get_app_url() . '/sahmatka/form_order.php?home_id='.$home.'&apartment_num='.$end_etza_num.'&apartments='.$k;
					$end_etza_num2 = '№'.$end_etza_num;
  
						$int_rooms = (int) $apart['rooms'].'k';
						$value='<div padding: 0px; margin:0px;"   class="tdapartment sch-tb__cell c-sale"  >
						<a href="'.$edit_url.'" class="iframe" data-room="'. $int_rooms.'" data-status="'.$data_status.'"  style="'.$st_bg.'">'.$end_etza_num.'</a>
						</div>';
 
						$status_text = 'Комнат: '. $apart['rooms'].' <br/>   Площадь: '. $apart['area'].'м<sup>2</sup>  
						<br/>						 
						<img src=\''.$apart['image_pb'].'\' height=150 style=\'max-width:150px;\' />
						';
					 
				if($apart['status']=='0' || $apart['status']=='1' || !$apart ) // Нет информации
				{  
					$stext = 'Свободна';
					$class="tdgreen";
				} 
				elseif($apart['status']=='2')
				{
					$stext = 'Свободна';
					$class="tdgreen";
				}
				elseif($apart['status']=='3')
				{
					$stext = 'Продана';
					$class="tdred";
				}
				elseif($apart['status']=='4')
				{
					$class="tdyellow";
					if( check_access('admin') || $_SESSION['sh_login'] == 'demo_admin' )
					{
						$stext = 'Бронь '.$apart['date'].'<br/>'.$apart['login'] . ' - '. $apart['name'];
						$stext .=' <br/><b>'.$apart['agency_caption'].'</b> ';
					}
					elseif($_SESSION['adm_caption'] && $_SESSION['agency_id'] == $apart['agency_id'] ) // Если админ агенства и бронь сотрудником агентства
					{
						$stext = 'Бронь '.$apart['date'].'<br>'.$apart['login'].' - '. $apart['name']; 
					}
					elseif(  $_SESSION['agency_id'] == '92' ) //СОТРУДНИК ОП
					{
						$stext = 'Бронь '.$apart['date'].'<br/>'.$apart['login'].' - '. $apart['name'];	
					}
					else
					{	
						$stext = 'Бронь'; 
					}
				}
				elseif($apart['status']=='5')
				{
					
					if( check_access('admin') || $_SESSION['agency_id'] == "92" || $_SESSION['sh_login'] == 'em_nsv' || $_SESSION['sh_login'] == 'director' || $_SESSION['sh_login'] == 'demo_admin')
					{
						$class="tdgrey";
						$stext .='Забронирована застройщиком';
					}
					else
					{
						$class="tdred";
						$stext = 'Продана'; 
					}
				}
				elseif($apart['status']=='6')
				{
					if(check_access('admin') || $_SESSION['agency_id'] == "92" || $_SESSION['sh_login'] == 'em_nsv' || $_SESSION['sh_login'] == 'director' || $_SESSION['sh_login'] == 'demo_admin')
					{
						$class="tdblue";
						$stext = 'Квартира подрядчика'; 
					}
					else
					{
						$class="tdred"; 
						$stext = 'Продана';
					}
				}
				
 	 
							
				} // Не пустая
				 
				
	// Версия для печати
	// Версия для публичной части сайта 
	// (разные шаблоны вывода квартир)
				if($conf['clean_apartments'][$i][$k]) // пустая квартира
				{
					?>
					<td  <? if(!check_access('admin') && $_SESSION['sh_login'] != 'demo_admin'){print 'style="height:auto;"';} else{print 'style="height:74px;"';}?>></td>
					<?
				}
				else
				{
					?>
					<td class="<?=$class?>" rel="tooltip"  title="<?=$stext?><br/><?=$status_text?>" <? if(!check_access('admin')){print 'style="height:auto;"';}?>>
						<?=$value?>
					</td>
					<?
					$endnum--; // вытонумерация квартир
				}
				
				
			}
		}
		print '</tr>';
	}
	

 


}
















	
	
// Для виджета 
function disp_home_p_n(  $home, $section ,$notable='' )
{  
 
 
$data = $this->get_data_appart_arr( $home,$section  );
$conf = $this->get_sec_arr( $home , $section );
 
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
  
	// Цикл по строкам (количество этажей)
	for($i=$conf['floor']; $i>0; $i--)
	{
		 
		// количество нежилых квартир на этаже
		$nezk = count((array)($conf['clean_apartments'][$i] ?? [])); 
		
		// РАБОЧИЙ ПОРЯДКОВЫЙ НОМЕР НА ЭТАЖЕ
		$end_etza_num = $endnum-$conf['apartments']-1+$nezk; // стартовый номер для этажа (- квартир на этаже СО СТАТУСОМ ЕСТЬ КВАРТИРА!)

		#########################################
	 
		/*
		if(  $i==$conf['floor'])
		{
			?>
				<thead>
					  <tr>
							<td class="tdtitle"> <?=$conf['caption']?></td>
						</tr>  
					 
				</thead>
			<?
		}
		*/
		#########################################

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
			
			#
			#################
			// нулевой столбей номер этажа
			if($k==0)
			{
				print '<td><div class="sch-tb__num">'.$i.'</div></td>';
 			}
			###################
			
			#
			else // ИНфа о квартире
			{
				// Данные по квартире читаются по дому+номеру квартиры
				$apart = $data[$home][$end_etza_num];
				  
				$apart['price'] = number_format($apart['price'], 0, '.', ' ');  
				 
				// Убираем нули
			 	if(!$apart['rooms']){$apart['rooms']='';}
				if(!$apart['price']){$apart['price']='';}
				$rooms = $apart['rooms'].'К';
			 
				if($conf['clean_apartments'][$i][$k])
				{
					$status_text = 'Нет квартиры';
					$stext = '';
					$value='';
				}
				 
				else // не пустая квартира
				{
				
				
				if($apart['status']=='0' || $apart['status']=='1' || !$apart || $apart['status']=='2') // Свободна
				{  
					$data_status = 'Свободна'; 
 					$st_class = 'c-sale';
					$stext = 'Свободна';
					$data_color = 'rgb(146, 182, 146);';
				} 
				elseif($apart['status']=='4') // бронь
				{
					$data_status = 'Бронь';
 					$st_class = 'c-res';
					$stext = 'Бронь';
					$data_color = 'rgb(218, 161, 82);';
				}
				else // Все остальные проданы
				{
					$data_status = 'Продана'; 
 					$st_class = 'c-sold';
					$stext = 'Продана';
					$data_color = 'rgb(211, 211, 211);';
				} 
					$stext = '<span class=\'stat_color\'></span>';
					
					//$edit_url = 'https://lg.m2profi.pro/sahmatka/form_order.php?home_id='.$home.'&apartment_num='.$end_etza_num.'&apartments='.$k;
					
					if($_GET['new'] ==1)
					{
					$edit_url = get_app_url() . '/sahmatka/iframe_router.php?ctr=apartments&act=card&home_id='.$home.'&apartment_num='.$end_etza_num.'&apartments='.$k;
					}
					elseif($_GET['new']==2)
					{
							$edit_url = get_app_url() . '/sahmatka/iframe_router.php?ctr=apartments&act=card&home_id='.$home.'&apartment_num='.$end_etza_num.'&apartments='.$k;
					}
					
					$end_etza_num2 = '№'.$end_etza_num;
  
   
						$int_rooms = (int) $apart['rooms'];
						$value='<div class="sch-tb__cell '.$st_class.'"  >
						<a href="'.$edit_url.'" class="iframe" 
						
						>'.$int_rooms .'</a>
						</div>';
 
						$status_text = '';
	 
							
				} // Не пустая
				 
				
	// Версия для печати
	// Версия для публичной части сайта 
	// (разные шаблоны вывода квартир)
				if($conf['clean_apartments'][$i][$k]) // пустая квартира
				{
					?>
					<td></td>
					<?
				}
				else
				{
					?>
					<td class="<?=$class?>" rel="tooltip" 
						data-rooms_int="<?=$int_rooms?>"
						data-num="<?=$apart['apartment_num']?>" 
						data-area="<?=$apart['area']?>" 
						<?	if($_GET['new']!=2){?>data-price="<?=$apart['price']?>" <?}?>
						data-image="<?=$apart['image_pb']?>"
						data-rooms="<?=$int_rooms?>" 
						data-status="<?=$data_status?>" 
						data-color="<?=$data_color?>" 


						  >
						<?=$value?>
					</td>
					<?
					$endnum--; // вытонумерация квартир
				}
				
				
			}
		}
		print '</tr>';
	}
	

 


}









	
// Для виджета 
function disp_home_print(  $home, $section ,$notable='' )
{  
 
 
$data = $this->get_data_appart_arr( $home,$section  );
$conf = $this->get_sec_arr( $home , $section );
 
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
 
 
 
 
 	 
	// Цикл по строкам (количество этажей)
	for($i=$conf['floor']; $i>0; $i--)
	{
		
	 
		// количество нежилых квартир на этаже
		$nezk = count((array)($conf['clean_apartments'][$i] ?? [])); 
		
		// РАБОЧИЙ ПОРЯДКОВЫЙ НОМЕР НА ЭТАЖЕ
		$end_etza_num = $endnum-$conf['apartments']-1+$nezk; // стартовый номер для этажа (- квартир на этаже СО СТАТУСОМ ЕСТЬ КВАРТИРА!)

		#########################################
	 
		if(  $i==$conf['floor'])
		{
			?>
				<thead>
					  <tr>
							<td colspan="10" class="tdtitle" style="font-size:10px;"> <?=$conf['caption']?></td>
						</tr>  
					 
				</thead>
			<?
		}
		#########################################

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
			
			#
			#################
			// нулевой столбей номер этажа
			if($k==0)
			{
				print '<td class="tdfloor" title="Этаж" style="font-size:10px;">'.$i.'</td>';
			}
			###################
			
			#
			else // ИНфа о квартире
			{
			  
				
				// Данные по квартире читаются по дому+номеру квартиры
				$apart = $data[$home][$end_etza_num];
				  
				$apart['price'] = number_format($apart['price'], 0, '.', ' ');  
				 
				// Убираем нули
			 	if(!$apart['rooms']){$apart['rooms']='';}
				if(!$apart['price']){$apart['price']='';}
				
			 
				if($conf['clean_apartments'][$i][$k])
				{
					$status_text = 'Нет квартиры';
					$stext = '';
					$value='';
				}
				 
				else // не пустая квартира
				{
				
				
				if($apart['status']=='0' || $apart['status']=='1' || !$apart || $apart['status']=='2') // Свободна
				{  
					$data_status = ''; 
					$st_bg = 'background: rgb(137, 255, 164);';
				} 
				elseif($apart['status']=='4') // бронь
				{
					$data_status = '';
					$st_bg = 'background:#FFFF3E;';
				}
				
			 
				elseif($apart['status']=='3')
				{
					$stext = 'Продана';
					$class="tdred";
					$st_bg = 'background-color:#FF8A90; ';
				}
				 
				elseif($apart['status']=='5')
				{
					
					if( check_access('admin') || $_SESSION['agency_id'] == "92" || $_SESSION['sh_login'] == 'em_nsv' || $_SESSION['sh_login'] == 'director' || $_SESSION['sh_login'] == 'demo_admin')
					{
						$st_bg = 'background-color:#D4E6FF; ';
						$class="tdgrey";
						$stext .='Забронирована застройщиком';
					}
					else
					{
						$st_bg = 'background-color:#FF8A90; ';
						$class="tdred";
						$stext = 'Продана'; 
					}
				}
				elseif($apart['status']=='6')
				{
					if(check_access('admin') || $_SESSION['agency_id'] == "92" || $_SESSION['sh_login'] == 'em_nsv' || $_SESSION['sh_login'] == 'director' || $_SESSION['sh_login'] == 'demo_admin')
					{
						$st_bg = 'background-color:#9933ff; ';
						$class="tdblue";
						$stext = 'Квартира подрядчика'; 
					}
					else
					{
						$st_bg = 'background-color:#FF8A90; ';
						$class="tdred"; 
						$stext = 'Продана';
					}
				}
				else // Все остальные проданы
				{
					$st_bg = 'background-color:#FF8A90; ';
				 
				 
				} 
					
					
					 
					$end_etza_num2 = '№'.$end_etza_num;
           
						$value='<div style="padding: 0px; margin:0px; border: solid 1px #FFF; font-size:10px;  padding: 3px; text-align: center; "> 
						<b>№'.$end_etza_num.'</b>  
						('. $apart['rooms'].') <br/>
						'. $apart['area'].'м<sup>2</sup> <br/>
						'. $apart['price'].'  
						</div>';
 
							
				} // Не пустая
				 
				
	// Версия для печати
	// Версия для публичной части сайта 
	// (разные шаблоны вывода квартир)
				if($conf['clean_apartments'][$i][$k]) // пустая квартира
				{
					?>
					<td></td>
					<?
				}
				else
				{
					?>
					<td class="<?=$class?>" rel="tooltip"  title="<?=$stext?><br/>
					<?=$status_text?>" 
					<? if(!check_access('admin')){print 'style="height:auto; '.$st_bg.'"';}else{print 'style=" '.$st_bg.' " ';}?>
					>
						<?=$value?>
					</td>
					<?
					$endnum--; // вытонумерация квартир
				}
				
				
			}
		}
		print '</tr>';
	}
	

 


}






	
	
	
	
	
	
}
$sa=new sahmatka($_SESSION , $connection);


$hlp = new hlp($connection);