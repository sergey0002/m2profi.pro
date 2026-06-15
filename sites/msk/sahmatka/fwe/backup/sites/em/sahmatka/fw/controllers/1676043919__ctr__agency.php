<?
class ctr__agency extends ctr__
{  

/*
+ АВТОЗАГРУЗКА КЛАССА КОНТРОЛЛЕРОВ ТИПА ФАБРИКА яхз запускать один контроллер из другого или подключить все сразу! для отладки так проще!

- ПЕРЕНЕСТИ БРОНИ КВАРТИР НА КОНТРОЛЛЕР
+ АВТО ОБРАБОТКА 
- СКРЫТЬ 
- ПОКАЗАТЬ 
- Удалить 
- Восстановить


ПЕРЕНЕСТИ ИСТОРИЮ БРОНИРОВАНИЯ В БРОНИ конроллер


Заявки унитабла?!
$ut=new fw_ut('table',filedstable);
$ut->addf('name','caption',order,type=sel|text); // ДОбавляется поле
- порядок вывода
- тип поля редактирования

// каждый поле жто обект класса!
$ut->base_sql(); на каждое доп поле join

$ut->table_body
$ut->table head 

$ut->filtr[order]=$val;

$ut->get(); получить в $this->data

$ut->display_table();Вывести таблицу
$ut->display_tpl(tpl); //ВЫвести в шаблоне
$ut->get_arr(); 
$ut->display_id_tpl(tpl)
$ut->display_table_id(); - ВЫВЕСТИ СТРОКУ ТАБЛИЦЫ (для ajax)


----
Статистика пользователей
----
Статистика агентства


Пользователей 
- Входов в систему 
- Просмотров страниц
- действий в системе за время
- брони парковок
- брони квартир 
- ПРодажи квартир
- продажи парковок
- Продажи по аренде
- Брони по аренде

 по клику разворачивается каждая
 
 
 ДОмов
 - Всего / свободно / продано Забронировано
 - 1к 2к 3к
----







https://www.jqueryscript.net/tags.php?/Infinite%20Scroll/ - лези лоад



- СТИЛЬ СТРОКИ В ЗАВИСИМОСТИ ОТ СОДЕРЖИМОГО (БЛОКИРОВАННЫЕ УДАЛЕННЫЕ с кристала)

- блокировака конкретных пользователей
 НАЙТИ ГДЕ Я ПРИМЕНЯЛ nodisp filed

запоминание сортировки в сессиях
 

1. КЛик по столбу, или произвольному элементу а не всей строке опционально =2 + берется дата атрибут из элемента (просто класс ему присвоить и дата атрибут)
2. ФОрма редактирования агентства на всю страницу как в кристале с подсветкой результата с возможностью добавлять пользователей
3. ФОрма добавления агентства как сейчас ! но с доп полями блокировать разблокировать комментарий + история входов
 
Документировать и убрать в классы методы

+ ВОССТАНОВИТЬ КЕШ ЗАПРОСОВ ИЗ БЮТИПОИНТСА MYSQL!)


постраничный вывод?
ЭКСПОРТ В ЕКСЕЛЬ !
отчетов таблиц

ЛЕЗИ ЛОАД бесконечный скрол?!

+ Мастер таблиц (какие столюы показывать и в каком порядке ?!)
+ ПЕРЕВОД НА АВТОРИЗАЦИЮ СЕССИЙ!
+ ТЕКСТОВОЕ ЛОГИРОВАНИЕ ДЕЙСТВИЙ ДЛЯ ВЫВОДА ОНЛАЙН! 
+ КОЛИЧЕСТВО ПОЛЬЗОВАТЕЛЕЙ ОНЛАЙН 


Условие на кликабельность элемента ! (методы true|false по типу таблиц titles)
К примеру нет агентств
 
id элементам аякс редактироуемым для удаления и одиночного обновления + в гет аякс дата необязательный атрибут ид!
 
АЯКС ФОРМЫ В РАСКРЫВАЮЩИЙСЯ БЛОК С СОХРАНЕНЕМ И ПОСЛЕДУЮЩИМ ОБНОВЛЕНИЕМ СТРОКИ
 
ИНЖИНЕРНЫЙ ПОИСК ПО ВСЕМ ПОЛЯМ СОДЕРЖИТ МЕНЬШЕ БОЛЬШЕ 

+ БЫСТРОЕ РЕДАКТИРОВАНИЕ - КАК В АДМИНЕРЕ (ЗАМЕНЯТЬ СТОЛБЫ ЭЛЕМЕНТАМИ УПРАВЛЕНИЯ ы)

-------------
 
-------------
права доступа на экшены пользователей и ролей и групп(агентств) чекбуксами 
дерево Роль (все экшены и контроллеры)-> 

отдел  -> все экшены и контроллеры
 пользователь 

Проверка обекта на принедлежность тоже в права (можно ли править свою бронь или чужую)
отдельно

---


фильтрация данных
+ PDO MYSQL?
--
Рефакторинг + пересборка
--
документацмя




*/

	var $table = 'agency'; //Главная таблица
	var $key_filed = 'agency_id'; // Ключевое поле главной таблицы
	var $ctr = 'agency';
    var $title = 'Агентства';
   
	function __construct()
	{
		if(  $_SESSION['sh_login'] != 'admin' ){ die('Доступ запрещен'); }
		
		$data=$this->getfiltr($filtr);
		$this->data=$data; // Сохраняем данные	
	}
	
	// Получить массив элемента по ид
	function getid( $id )
	{
		global $mysql;
		$filtr = array();
		if( $id )
		{
			$filtr['id']=$id;
		}
		else{print 'Не указан id элемента'; return;}
		
		$sql = $this->get_base_sql( $filtr );
		$data = $mysql->get_arr($sql,1);
		if(!$data){ print 'Не найден указанный ID:'.$id.' '; return;}
		return $data;	
	}
	
	// Получить двумрный массив по фильтру
	function getfiltr($filtr)
	{
		global $mysql;
		$sql = $this->get_base_sql( $filtr );
		$data = $mysql->get_arr($sql);
		return $data;	
	}
	
 
 
 	// БАзовый запрос  menu
	function get_base_sql($where='')
	{
		$filtr_data =  $_REQUEST;
		
		global $mysql;
		$q = 'SELECT  
		gl_user.login as gl_login,    gl_user.password as gl_password, gl_user.name as gl_name, gl_user.e_mail as gl_e_mail, gl_user.phone as gl_phone, users.*,
		max(users_stat.date) as lastactiv, count(DISTINCT users_stat.date) as activ, count(DISTINCT users.id) as usc, agency.* 
		FROM  agency    
		LEFT JOIN users as gl_user ON agency.admin_user_id = gl_user.id 
		LEFT JOIN users as users ON users.agency_id = agency.agency_id 
		LEFT JOIN users_stat   ON users_stat.users_id = users.id  
		WHERE 1=1 
		
		/* AND users.`password` LIKE "%!%" */
		';
		
		// Удаленные
		if( !$_REQUEST['show_dell']){ $q.=' AND '.$this->table.'.del = "0" '; }
		else{ $q.=' AND '.$this->table.'.del = "1" '; }
		
		// Заблокированные 
		if( !$_REQUEST['show_block']){ $q.=' AND '.$this->table.'.unactiv = "0" '; }
		else{ $q.=' AND '.$this->table.'.unactiv = "1" '; }
		
		
		if( $_REQUEST['act']!='sel_dir' || 1==1 )
		{
			$search_arr = array();
			$search_arr[]=''.$this->table.'.caption';
			if( $_REQUEST['search']){ $q.=$mysql->search($search_arr,$_REQUEST['search']); }			 
		}
		
		if( $filtr_data['agency_id'] )
		{
			//$q.=' AND agency.agency_id = "'.$filtr_data['agency_id'].'" '; 
		}
		if( $filtr_data['user_id'] )
		{
			//$q.=' AND users.id = "'.$filtr_data['user_id'].'" ';  
		}
		
		$q.=' GROUP BY agency.agency_id  ';
		
	 
		
		if( $filtr_data['order_filed'] )
		{
			 $q.=' ORDER BY '.$filtr_data['order_filed']; // Только актуальные брони без истории
			 if( $filtr_data['order_asc'] ) { $q.=' ASC '; } else { $q.=' DESC '; }
		}
		else
		{
			$q.=' ORDER BY agency.agency_id'; 
			$q.=' DESC ';  	
		}
		
		// Лимиты
		if( $filtr_data['start'] || $filtr_data['stop'] || 1==1 )
		{
			if( !$filtr_data['start'] ){ $filtr_data['start'] = 0; }  // Стартовая позиция
			if( !$filtr_data['stop'] ){ $filtr_data['stop'] = 10000; } // Сколько выводим
 
			$q.=' LIMIT '.$filtr_data['start'].' , '.$filtr_data['stop'];
			
		}
			
		
		// if($_GET['id']){$q.=''}
		  //  print $q;
		return $q;
	}
	
	
	
	
	
	
	
	
		
	# Удаление записи (пометка)
	function act__del()
	{
		global $mysql;
		$id = $_GET['id'];
		if($id)
		{			
			$data = array();
			$data['del'] = 1;
			$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
		}
		//$this->act__index();
	}
	
	
	
	
	
	
		
	# БЛОКИРОВКА 
	function act__block()
	{
		global $mysql;
		$id = $_GET['id'];
		if($id)
		{			
			$data = array();
			$data['unactiv'] = 1;
			//$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
		}
		//$this->act__index();
	}
	
	# РАЗБЛОКИРОВКА 
	function act__unblock()
	{
		global $mysql;
		$id = $_GET['id'];
		if($id)
		{			
			$data = array();
			$data['unactiv'] = 0;
			//$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
		}
		//$this->act__index();
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	// Вынести в КОНТРОЛЛЕР
	
	
	
	// Вывод селект поля по данным (уникальные занчения)
	function filtr_select( $title , $value_col , $caption_col , $value='' , $data='' , $select_data='' )
	{
		global $filed;
		if( !$data ){ $data = $this->data; }
		$sel_data[0]=' - Все - ';
		foreach($data as $k=>$v)
		{
			$sel_data[$v[$value_col]] = $v[$caption_col];
		}
		if( $select_data ){ $sel_data = $select_data; }
		$filed->select($value_col,$title,$sel_data,'');
	}
		
		
		
	

	
	
	
	
	// Панель редактирования
	function actions_panel($result)
	{
		if($result['del']){$del_class = 'dtable_del_class';}
		else{ $del_class = ''; }
			
		if(!$result['del'])
					{
						print '<td class="dtable '.$del_class.'">'.$result['order'].'&nbsp; &nbsp;  
						
						<a href="ajax_router.php?ctr='.$this->ctr.'&act=orderup&id='.$result[$this->key_filed].'" class="fw_ajaxlink" data-id="'.$result[$this->key_filed].'" style="font-size: 18px;">&#8593; </a> 
						&nbsp; 
						<a href="ajax_router.php?ctr='.$this->ctr.'&act=orderdown&id='.$result[$this->key_filed].'" class="fw_ajaxlink" data-id="'.$result[$this->key_filed].'" style="font-size: 18px;">&#8595;	</a> 
						</td>';
			
						print '<td class="dtable" style="">';
						print '<a href="index.php?ctr='.$this->ctr.'&act=editpage&id='.$result[$this->key_filed].'" class="table-edit"  ></a>
						&nbsp;
						<a href="ajax_router.php?ctr='.$this->ctr.'&act=del&id='.$result[$this->key_filed].'" class="fw_ajaxlink" data-actionhide="1" data-reloadall="1" data-id="'.$result[$this->key_filed].'" data-confirm="Вы действительно хотите удалить элемент?" style="color:red; font-size: 18px;">X</a>';
						 print '</td>';
					}
					else
					{
						print '<td></td>';
						 print '
						 <td>
						 <a href="ajax_router.php?ctr='.$this->ctr.'&act=del2&id='.$result[$this->key_filed].'" class="fw_ajaxlink" data-actionhide="1" data-reloadall="1"  data-id="'.$result[$this->key_filed].'" data-confirm="Вы действительно хотите удалить элемент без возможности восстановления?" style="color:red; font-size: 18px;">X</a> 
						 &nbsp;
						 <a href="ajax_router.php?ctr='.$this->ctr.'&act=recovery&id='.$result[$this->key_filed].'" class="fw_ajaxlink" data-reloadall="1" data-id="'.$result[$this->key_filed].'" data-confirm="Вы действительно хотите восстановить элемент?" style="color:GREEN; font-size: 18px;">R</a> 
						 </td>';
			}
	}
	
	
	
	
	###### Перенести к контроллер пользователей
	// доступы
	function display_table__login($v)
	{
		// '<td onclick="copytext(this)" id="access_'.$result['id'].'">Логин - '.$result['login'].'<br/> Пароль - '.$result['password'].'</td>'.
		return ' '.$v['login'].' <br/> '.$v['password'].' ';
	}
	
	function act__ag_users()
	{
		$ag_id = $_GET['id'];
		if(!$ag_id){return;}
		
		global $mysql;
	 
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
		
		$sql.=' AND users.agency_id="'.$ag_id.'" ';
		 
		$sql.='
		Group by users.id
		order by agid desc , adm, ac desc';
		
		$data = $mysql->get_arr($sql);
		
		$titles = array();
		$titles['id']='id';
		$titles['login']='Доступы';
		$titles['name']='ФИО';
		$titles['e_mail']='E-Mail';
		$titles['phone']='Телефон';
		$titles['ac']='Последний визит';
		?><table><?
		$this ->display_tablex_body($data,$titles);
		?></table><?
	}
 
	#####




// Служебные поля для формы поиска
 	function form_sfileds($fid)
	{
		?>
		<input type="hidden" name="ctr" value="<?=$this->ctr?>">
		<input type="hidden" name="act" value="ajax_data">
		<input type="hidden" name="formid" value="<?=$this->formid($fid)?>">
		<input type="hidden" id="order_filed" name="order_filed" value="<?=$this->get_form_sval( $this->formid($fid)  ,'order_filed','request')?>">		
		<input type="hidden" id="order_asc" name="order_asc" value="<?=$this->get_form_sval( $this->formid($fid)  ,'order_asc','request')?>">	
		<?
	}
	
	// форма поиска
	function searchform()
	{
		global $filed;
		?>
		<form method="GET" id="filtrform" data-controller="<?=$this->ctr?>" data-router="/admin/ajax_router.php" data-ajaxurl="/sahmatka/ajax_router.php?ctr=<?=$this->ctr?>&act=ajax_data" style="width:100%;" >
		<?=$this->form_sfileds('searchform')?>
		<div class="filter-block">
			<?
				//print_r($this->data);
			?>
 
			<div class="filter-item"  > 
			 	<span class="input_title">Поиск</span>
				<input type="text" id="search" name="search" class="input_edit" value="" placeholder="">	
			 
			</div>	
			 
 
			<div class="filter-item  filter-item-checkbox "> 
				<input type="checkbox"  id="show_dell" name="show_dell" value="1" <?=$this->get_form_check($this->formid('indexsearch'),'show_dell','request')?>> <label for="show_dell">Удаленные</label><br/>
			</div>
			<div class="filter-item  filter-item-checkbox "> 
				<input type="checkbox"  id="show_block" name="show_block" value="1" <?=$this->get_form_check($this->formid('indexsearch'),'show_block','request')?>> <label for="show_block">Заблокированные</label><br/>
			</div>	
			 
		</div>
		</form>
		<?
	}
	
	
	// Отображение столбов
  
	function display_table__edit($row)
	{
		$link = '<a href="?ctr='.$this->ctr.'&act=edit&id='.$row[$this->key_filed].'" class="table-edit"> </a> ';
		 
		 if($row['del']=="0")
		 { 
			//$link .='&nbsp;&nbsp; <a href="?ctr='.$this->ctr.'&act=del&id='.$row[$this->key_filed].'" style="color:red;  font-size: 21px;"> X </a>';
		 }
		 
		 if($row['unactiv']=="0")
		 { 
			//$link .='&nbsp;&nbsp; <a href="?ctr='.$this->ctr.'&act=block&id='.$row[$this->key_filed].'" style="color:red;  font-size: 21px;"> B </a>';
		 }
		 else
		 {
			//$link .='&nbsp;&nbsp; <a href="?ctr='.$this->ctr.'&act=unblock&id='.$row[$this->key_filed].'" style="color:green;  font-size: 21px;"> UB </a>';
		 }
		 return $link;
	 
	}
 
	// доступы
	function display_table__gl_login($v)
	{
		// '<td onclick="copytext(this)" id="access_'.$result['id'].'">Логин - '.$result['login'].'<br/> Пароль - '.$result['password'].'</td>'.
		return ' '.$v['login'].' <br/> '.$v['password'].' ';
	}
	// Контакт
	function display_table__gl_name($v)
	{
		return ' '.$v['gl_name'].' <br/> '.$v['gl_e_mail'].' <br/>'.$v['gl_phone'];
	}
	 
		
	//Регистрация
	function display_table__add_datetime($v)
	{
		if($v['add_datetime'] && $v['add_datetime']!='0000-00-00 00:00:00')
		{
			$r .= fromsql_date($v['add_datetime'],1);
		}
		else{$r='-';}
		return $r;
		
	}
	
	// Активность
	function display_table__lastactiv($v)
	{
		if($v['lastactiv'])
		{
			$r .= fromsql_date($v['lastactiv'],1);
		}
		else{$r='-';}
		return $r;
	}
	
 
	
	
	function display_table__status($v)
	{
		global $status_arr;
		global $status_color_arr;
		return  '<span style="background-color:'. $status_color_arr[$v['status']].';  "><b>'. $status_arr[$v['status']].'</b></span>';
	}
	
	// Контент раскрывающейся строки
	function display_hr_content($v)
	{
		return '<div style="width:100%; max-width:100vw; text-align:center; padding:5px; " class="loader"><img src="loader.gif" height=40 /></div>';
	}
	
	// Метод генерирует ссылку аякс для контента раскрывающейся строки
	function display_hr_ajax($v)
	{
		 return '/sahmatka/ajax_router.php?ctr=agency&act=ag_users&id='.$v['agency_id'];
	}
	
	
	function act__ajax_data()
	{
		$data=$this->data; 
 
		$titles[$this->key_filed] = 'id';
		$titles['inn'] = 'ИНН';
		$titles['add_datetime'] = 'Регистрация';
		$titles['caption'] = 'Агентство';
		$titles['gl_login'] = 'Доступы';
		$titles['gl_name'] = 'Контакт';
		$titles['lastactiv'] = 'Активность'; 
		$titles['usc'] = 'Us'; 
		$titles['activ'] = 'Ac'; 
		$titles['edit'] = '*'; 
  
		// Не переносить по словам
		$nowrap=array();
		$nowrap['edit'] = 1; 
		
		// print '<pre>';
		//print_r($data[0]);
		// print '</pre>';
	
		if($data)
		{
			$this ->display_tablex_body( $data , $titles ,$nowrap,1);
			print '<tr><td colspan="10" style="text-align:right; border-top:#000 1px solid; font-weight:bold;">Найдено: '.count($data).' агентств </td></tr>'; // Итоговая строка
		}
		else
		{
			print '<tr><td colspan="10" style="text-align:center"> - нет данных -</td></tr>';
		}
	}
	
	
	function act__index()
	{
		global $r;
		global $mysql;
		global $t;
		$t['h1'] = 'Агентства';
		
		?>
		<style>
		.dtable_del_class{text-decoration:line-through; color:#CCC;}
		
		/* Раскрывающиеся строки */
		.fw_hiderow{display:none; border:solid 1px #EEE; background-color:#fcfcfc;}
		.dtable_ch:hover > td { background:#F0F0F0; cursor:pointer; } 
		.fw_selrow{ background:#F0F0F0;}
		
		#fw_ajaxdata table tr td{padding:10px 10px;}
		  .table table tr td 
		  {
			padding: 12px 12px;
		    line-height: 1;
		  }
		.fw_hiderow table tr td{font-size:12px;}
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
		
					<div class="stat">
						<div class="stat-top">
							<?=$this->searchform()?>	
						</div>
						<div class="stat-table stat-table-user stat-table_notpd table">
						
						 
							<table class="dtable" >
							<thead>
							 
							<tr class="dtable">
							<?
							$data=$this->data;
							
							$titles[$this->key_filed] = 'id';
							$titles['inn'] = 'ИНН';
							$titles['add_datetime'] = 'Регистрация';
							$titles['caption'] = 'Агентство';
							$titles['inn'] = 'ИНН';
							$titles['gl_login'] = 'Доступы';
							$titles['gl_name'] = 'Контакт';
							$titles['lastactiv'] = 'Активность'; 
							$titles['usc'] = 'Us'; 
							$titles['activ'] = 'Ac'; 
							$titles['edit'] = '*'; 
							
							$order=array();
							$order[$this->key_filed]=$this->table.'.'.$this->key_filed;
							$order['add_datetime']='agency.add_datetime';
							$order['activ']='activ';
							$order['usc']='usc';
							$order['lastactiv']='lastactiv';
							$this ->display_tablex_head( $data , $titles, $order );
							
							?>
							</tr>
							</thead>
							<tbody id="fw_ajaxdata">
							</tbody>
							</table>
	 
							
							<div style="width:100%; max-width:100vw; text-align:center; padding:50px; " id="progressbar"  >
								 <img src="loader.gif" />
							</div>
						</div>
					</div>			
 
		<div id="fw_data_tbody2"></div>
 
		<?
		$x=array();
		$this->tpl($x,'core','ajaxeditor');
 
	}
	
	
	
	
	
	
	
	
	 
	
	
	
	
	######################################################################### ЗАпоминание заполнения форм 
	function session_form_save()
	{
		 // Если передан ид формы пишем значения в сессию
		 if($_REQUEST['formid'])
		 {
			 $formid = $_REQUEST['formid'];
			 if(!$r){ if( $_POST ){ $r='post'; } else{ $r='get'; }	}
			 unset($_SESSION['sforms'][$r][$formid]);
			 
			 if(!$_SESSION['sforms'][$r][$formid]['generation_time']){ $_SESSION['sforms'][$r][$formid]['generation_time'] = time();  } // Время генерации формы
				 
				 
			 // print_r($_REQUEST);
			 foreach($_GET as $k=>$v )
			 {
				// $_SESSION['sforms']['get'][$formid][$k]=$v;
			 }
			 foreach($_POST as $k=>$v )
			 {
				 if($v )
				 { 
					$_SESSION['sforms']['post'][$formid][$k]=$v;
				 }
				//  else{unset($_SESSION['sforms']['post'][$formid][$k]);}
				//$_SESSION['sforms']['post'][$formid]['dir']='2';
				
				
			 }
			 foreach($_REQUEST as $k=>$v )
			 {
				 if($v)
				 { 
					$_SESSION['sforms']['request'][$formid][$k]=$v;
				 }
			 }
			 
		 }
		 
	}
		// Генерируем ид формы
	function formid($id)
	{
		// Директория Ctr и act остальные гет переменые убрать
		// $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']
		return   md5($_GET['ctr'].'-'.$_GET['act']  .'-'.$id);
	}
	
	// ПИшем значение формы в сесссию
	function form_sval($form_id,$name,$value,$r='')
	{
	}
	
	// Получаем значение поля формы из сессии
	function get_form_sval( $form_id  ,$name  , $r='' )
	{	
		if(!$r)	{ if( $_POST ){ $r='post'; } else{ $r='get'; }	}
 		return $_SESSION['sforms'][$r][$form_id][$name];  
	}
	
	
	// чекбукс
	function get_form_check( $form_id  ,$name  )
	{	
		if( $this->get_form_sval( $form_id  ,$name,'post' ) ){ return ' checked="checked" '; }
		else{print $form_id.'-'.$name;}
	}
	
	// select option
	function aj_select( $name, $val  )
	{	
	    $form_id = $_POST['formid'];
		if($val == $this->get_form_sval( $form_id  ,$name ) )
		{
			// print   $form_id.'-'. $this->get_form_sval( $form_id  ,$name );
			return ' selected="selected" ';
		}  
	}
	###########################################################################
	
	
	
	
}