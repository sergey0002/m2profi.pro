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
		if(  $_SESSION['sh_login'] != 'admin' && $_SESSION['sh_login'] != 'goodzem'){ die('Доступ запрещен'); }
		
		$data=$this->getfiltr($filtr);
		$this->data=$data; // Сохраняем данные	
		  
		$data_nofiltr=$this->getfiltr([1]); // Данные без фильров для селектов?! очень ресуроемко ! по сути все брони перебирает циклом , но с другой стороны у нас все записи выводятся и ничего 
		$this->data_nofiltr=$data_nofiltr; // Сохраняем данные
		 
		
		$titles[$this->key_filed] = 'id';
		 
		$titles['add_datetime'] = 'Регистрация';
		$titles['caption'] = 'Агентство';
	 
		$titles['gl_login'] = 'Доступы';
		$titles['gl_name'] = 'Контакт';
		$titles['lastactiv'] = 'Активность'; 
		$titles['usc'] = 'Us'; 
		$titles['activ'] = 'Ac'; 
		$titles['edit'] = '*'; 
		$titles['exrow'] = ''; // плюсик
		$this->ajcrud_table_titles=$titles;
		 
		$order=array();
		$order[$this->key_filed]=$this->table.'.'.$this->key_filed;
		$order['add_datetime']='agency.add_datetime';
		$order['activ']='activ';
		$order['usc']='usc';
		$order['lastactiv']='lastactiv';
		$this->ajcrud_table_order=$order; 
		 
		// Не переносить по словам
		$nowrap=array();
		$nowrap['edit'] = 1;
		$this->table_nowrap=$nowrap;
 
		$this->aj_crud_addbutton=1;
		$this->display_table_exrow=1; // раскрывать строки
		
		$this->aj_crud_edit_iframe=1; // РЕдактор в фреймах 
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
			if( !$filtr_data['stop'] ){ $filtr_data['stop'] = 1000; } // Сколько выводим
 
			$q.=' LIMIT '.$filtr_data['start'].' , '.$filtr_data['stop'];
			
		}
			
		
		// if($_GET['id']){$q.=''}
		  //  print $q;
		return $q;
	}
	
	
	
	
	
	function act__edit()
	{
		global $t;
		$t['h1'] = 'Редактирование агентства';
		
		global $filed;
		global $mysql;
		global $r;
		global $filed_errors;
 
		# Данные редактирования
		$id = (int) $_GET['id'];
		if($id) //
		{
			 $data = $mysql->get_for_key($this->table,$this->key_filed,$_GET['id']);
			// print '<h2>Редактирование объекта </h2>';
			 
			if($_POST) ############# Обработка данных пост
			{
				$data_agency = array();
				if($_POST['caption']){$data_agency['caption'] = $_POST['caption'];}
				if($_POST['unactiv']){$data_agency['unactiv'] = 1;}else{$data_agency['unactiv']=0;}
				if(!$data_agency['caption']){$filed_errors['caption'][] = 'Заполните поле';}
				
				$mysql->update_for_key('agency','agency_id',$id,$data_agency);
				$data = $mysql->get_for_key($this->table,$this->key_filed,$_GET['id']);
			}
			?> 
			<style>
			.input_edit {width:100%; max-width:100%;}
			</style>
			<div id="ajaxcontent" class="sta!t">
			<form action="" method="POST" id="editform">
			<br/>
			<br/>
			<?=$this->formpanel($r->acturl($this->ctr,'index'));?>
			<div class="row">
				<div class="col-md-12">
				<?
				   $filed->text('caption','Название (Юр. Лицо ИП/ООО)',$data['caption'],'id="agcaption"'); print '<br/>';
				   $filed->checkbox('unactiv','Доступ заблокирован',$data['unactiv']); print '<br/>';
				 
				?>
				</div>
			</div>	 
			</form>
			</div>	
			<?
			// print_r($data);
		}
		else
		{
			if($_POST) ############# Обработка данных пост
			{
				// print_r($_POST);
				$data = array();
				 
				if($_POST['caption']){$data['caption'] = $_POST['caption'];}
				if($_POST['inn']){$data['inn'] = $_POST['inn'];}
				if($_POST['login2']){$data['login2'] = $_POST['login2'];}
				if($_POST['password2']){$data['password2'] = $_POST['password2'];}
				if($_POST['name2']){$data['name2'] = $_POST['name2'];}
				if($_POST['phone']){$data['phone'] = $_POST['phone'];}
				if($_POST['e_mail']){$data['e_mail'] = $_POST['e_mail'];}
		  
				// ФИЛЬТРАЦИЯ
				if(!$data['caption']){$filed_errors['caption'][] = 'Заполните поле';}
				if(!$data['inn']){$filed_errors['inn'][] = 'Заполните поле';}
				if(!$data['login2']){$filed_errors['login2'][] = 'Заполните поле';}
				if(!$data['password2']){$filed_errors['password2'][] = 'Заполните поле';}
				if(!$data['name2']){$filed_errors['name2'][] = 'Заполните поле';}
				if(!$data['phone']){$filed_errors['phone'][] = 'Заполните поле';}
				if(!$data['e_mail']){$filed_errors['e_mail'][] = 'Заполните поле';}
				
				 
				if( $mysql->get_arr('SELECT * FROM `users` WHERE `users`.`login` = "'.$data['login2'].'" ') ){	$filed_errors['login2'][] = 'Указанный логин занят';	}
				 
				if( $mysql->get_arr('SELECT * FROM `agency` WHERE `agency`.`inn` = "'.$data['inn'].'" ') )	{	$filed_errors['inn'][] = 'Агентство уже есть в системе';	}
				
				// if( $mysql->get_arr('SELECT * FROM `agency` WHERE `agency`.`caption` = "'.$data['caption'].'" ') )	{	$filed_errors['inn'][] = 'Агентство уже есть в системе';	}
				
				if(!$filed_errors)
				{				 
					$now = date("Y-m-d H:i:s");
					# ДОбавляем агентство
					$data_agency = array();
					$data_agency['caption']=$data['caption'];
					$data_agency['inn']=$data['inn'];
					$data_agency['admin_user_id']=1;
					$data_agency['add_datetime']=$now;
					$agency_id = $mysql->insert('agency',$data_agency);// ИД АГЕНТСТВА  
			
					if($agency_id)
					{
						$data_user = array();
						$data_user['login'] =  $data['login2'];
						$data_user['password'] =  $data['password2'];
						$data_user['name'] =  $data['name2'];
						$data_user['e_mail'] =  $data['e_mail'];
						$data_user['phone'] =  $data['phone'];
						$data_user['agency_id'] =  $agency_id;
						$n_uid = $mysql->insert('users',$data_user);// ИД Пользователя
						
						$data_agency['admin_user_id'] = $n_uid;
					}
					
					// ОБновляем таблицу агентств (ид пользователя админа)
					$mysql->update_for_key('agency','agency_id',$agency_id,$data_agency);
					
					
					?>
					<hr/>
					<h2>Агентство добавлено</h2>
					<span>
					Логин - <?=$data_user['login']?><br/>
					Пароль - <?=$data_user['password']?>
					</span>
					<?
				}
				
				//$this->act__index();
			}
			 
			
			if(!$_POST || 1==1 ) ############# ФОРМА
			{
		 
			?>
			<style>
			.input_edit {width:100%; max-width:100%;}
			</style>
			
			<div id="ajaxcontent" class="sta!t">
			<form action="" method="POST" id="editform">
			<br/>
			<br/>
			<?=$this->formpanel($r->acturl($this->ctr,'index'));?>
			<div class="row">
				<div class="col-md-6">
				<?
				   $filed->text('caption','Название (Юр. Лицо ИП/ООО)',$data['caption'],'id="agcaption"'); print '<br/>';
				   $filed->text('inn','ИНН',$data['inn']); print '<br/>';
				?>
				</div>
				<div class="col-md-6"> 
				<?
				   $filed->text('login2','Логин админстратора',$data['login2'],'id="aglogin2"'); print '<br/>';
				  
				   $filed->text('password2','Пароль',$data['password2'],'id="password2"'); print '<br/>';
				   $filed->text('name2','ФИО Администратора',$data['name2']); print '<br/>';
				   $filed->text('phone','Телефон',$data['phone']); print '<br/>';
				   $filed->text('e_mail','E-Mail',$data['e_mail']); print '<br/>';
				?>
				</div>
			</div>	 
			</form>
			</div>	
	 
			<script>

			$(document).ready(function() 
			{
				 
				$('table').on('click', 'tr.parent .downs', function(){
					//$(this).closest('tbody').toggleClass('open');
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
				$('#agcaption').bind('change keyup input click', function()
				{
					$('#aglogin2').val(urlLit($('#agcaption').val(),0)) ;
					$('#password2').val(Math.floor( Math.random() * 1000000000 ) ) ;
					
				});
				 
			});
			</script>	
		
			<?
			}
		}
 

		
	 
 
	
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
	
	
	
	
	###### 
	// доступы
	function display_table__login($v)
	{
		// '<td onclick="copytext(this)" id="access_'.$result['id'].'">Логин - '.$result['login'].'<br/> Пароль - '.$result['password'].'</td>'.
		return ' '.$v['login'].' <br/> '.$v['password'].' ';
	}
	
	
 



 
	 
	 
 
	// доступы
	function display_table__gl_login($v)
	{
return ' <span  onclick="copytext(this)" style="cursor:pointer">
Логин - '.$v['login'].'<br/> 
Пароль - '.$v['password'].'
</span>';
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
		 return '/sahmatka/ajax_router.php?ctr=users&act=exrow_users&id='.$v['agency_id'];
	}
	
	
 
	
 
	function ajcrud_filtr()
	{
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
		<?
	}
	
	function act__index()
	{
		global $r;
		global $mysql;
		global $t;
		$t['h1'] = 'Агентства';
	 
		?>
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
		$this->display_ajax_crud();
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