<?
class ctr__users extends ctr__
{  
 
	var $table = 'users'; //Главная таблица
	var $key_filed = 'id'; // Ключевое поле главной таблицы
	var $ctr = 'users';
    var $title = 'Пользователи';
   
	function __construct()
	{
		if(  $_SESSION['sh_login'] != 'admin' && $_SESSION['sh_login'] != 'goodzem' ){ die('Доступ запрещен'); }
		
		$data=$this->getfiltr($filtr);
		$this->data=$data; // Сохраняем данные	
		  
		$data_nofiltr=$this->getfiltr([1]); // Данные без фильров для селектов?! очень ресуроемко ! по сути все брони перебирает циклом , но с другой стороны у нас все записи выводятся и ничего 
		$this->data_nofiltr=$data_nofiltr; // Сохраняем данные
	 
		$titles[$this->key_filed] = 'id';
		$titles['login']='Доступы';
		$titles['name']='ФИО';
		$titles['e_mail']='E-Mail';
		$titles['phone']='Телефон';
		$titles['ac']='Последний визит';
		$titles['edit'] = '*';
	 
		$this->ajcrud_table_titles=$titles;
		 
		$order=array();
		$order[$this->key_filed]=$this->table.'.'.$this->key_filed;
		//$order['add_datetime']='agency.add_datetime';
		//$order['activ']='activ';
		//$order['usc']='usc';
		//$order['lastactiv']='lastactiv';
		$this->ajcrud_table_order=$order; 
		 
		// Не переносить по словам
		$nowrap=array();
		$nowrap['edit'] = 1;
		$this->table_nowrap=$nowrap;
 
		$this->aj_crud_addbutton=1;
		$this->display_table_exrow=0; // раскрывать строки
	}
	
 

	
 
 
 	// БАзовый запрос  menu
	function get_base_sql($where='')
	{
		$filtr_data =  $_REQUEST;
	 
		global $mysql;
		 
		$q="
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
		 
		// Удаленные
		//if( !$_REQUEST['show_dell']){ $q.=' AND '.$this->table.'.del = "0" '; }
		//else{ $q.=' AND '.$this->table.'.del = "1" '; }
		
		// Заблокированные 
		// if( !$_REQUEST['show_block']){ $q.=' AND '.$this->table.'.unactiv = "0" '; }
		// else{ $q.=' AND '.$this->table.'.unactiv = "1" '; }
		  
		if( $filtr_data['agency_id'] )
		{
			 $q.=' AND agency.agency_id = "'.$filtr_data['agency_id'].'" '; 
		}
		if( $filtr_data['user_id'] )
		{
			 $q.=' AND users.id = "'.$filtr_data['user_id'].'" ';  
		}
		  
		$q.='GROUP by users.id';
		  
		if( $filtr_data['order_filed'] )
		{
			 $q.=' ORDER BY '.$filtr_data['order_filed']; // Только актуальные брони без истории
			 if( $filtr_data['order_asc'] ) { $q.=' ASC '; } else { $q.=' DESC '; }
		}
		else
		{
			$q.=' order by agid desc , adm, ac desc'; 
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
	
	
	
	
	
	
	function act__exrow_users()
	{
		$ag_id = $_GET['id'];
		if(!$ag_id){return;}
		
		global $mysql;
		global $r;
		 
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
		$titles['edit'] = '*';
		?><table><?
		$this ->display_tablex_body($data,$titles);
		?></table><?
	}
	
	
	
	
	function act__edit()
	{
		global $t;
		$t['h1'] = 'Редактирование пользователя';
		
		global $filed;
		global $mysql;
		global $r;
		global $filed_errors;
 
		# Данные редактирования
		$id = $_GET['id'];
		if($id)
		{
			$data = $mysql->get_for_key($this->table,$this->key_filed,$_GET['id']);
		}
		else
		{
			//print '<h2>Добавление объекта</h2>';
		}
 
		if($_POST) ############# Обработка данных пост
		{
			// print_r($_POST);
			$data = array();
			if($_POST['login']){$data['login'] = $_POST['login'];}
			if($_POST['password']){$data['password'] = $_POST['password'];}
			if($_POST['name']){$data['name'] = $_POST['name'];}
			if($_POST['phone']){$data['phone'] = $_POST['phone'];}
			if($_POST['e_mail']){$data['e_mail'] = $_POST['e_mail'];}
	  
			// ФИЛЬТРАЦИЯ
			if(!$data['login']){$filed_errors['login'][] = 'Заполните поле';}
			if(!$data['password']){$filed_errors['password'][] = 'Заполните поле';}
			if(!$data['name']){$filed_errors['name'][] = 'Заполните поле';}
			if(!$data['phone']){$filed_errors['phone'][] = 'Заполните поле';}
			if(!$data['e_mail']){$filed_errors['e_mail'][] = 'Заполните поле';}
			 
			if( $mysql->get_arr('SELECT * FROM `users` WHERE `users`.`login` = "'.$data['login2'].'" AND `users`.`id` != "'.$id.'"') ){	$filed_errors['login2'][] = 'Указанный логин занят';	}
			      
			// print_r($filed_errors);
			
			if(!$filed_errors)
			{				 
				$data_user = array();
				$data_user['login'] =  $data['login'];
				$data_user['password'] =  $data['password'];
				$data_user['name'] =  $data['name'];
				$data_user['e_mail'] =  $data['e_mail'];
				$data_user['phone'] =  $data['phone'];
		  
				// ОБновляем таблицу агентств (ид пользователя админа)
				$mysql->update_for_key('users','id',$id,$data_user);
			}
			
			//$this->act__index();
		}
		 
		
		if(!$_POST || 1==1 ) ############# ФОРМА
		{
		?>
		<style>
		.input_edit {width:100%; max-width:100%;}
		</style>
		
	 
		<form action="" method="POST" id="editform">
		<br/>
		<br/>
		<?=$this->formpanel($r->acturl($this->ctr,'index'));?>
		<div class="row">
		 
			<div class="col-md-12"> 
			<?
			   $filed->text('login','Логин админстратора',$data['login'],'id="aglogin"'); print '<br/>';
			   $filed->text('password','Пароль',$data['password'],'id="password"'); print '<br/>';
			   $filed->text('name','ФИО Администратора',$data['name']); print '<br/>';
			   $filed->text('phone','Телефон',$data['phone']); print '<br/>';
			   $filed->text('e_mail','E-Mail',$data['e_mail']); print '<br/>';
			?>
			</div>
		</div>	 
		</form>
	 
 
		<?
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
	
	
	
	  
	
	 
	
	
	
	
	###### Перенести к контроллер пользователей
	// доступы
	function display_table__login($v)
	{
		// '<td onclick="copytext(this)" id="access_'.$result['id'].'">Логин - '.$result['login'].'<br/> Пароль - '.$result['password'].'</td>'.
		return ' '.$v['login'].' <br/> '.$v['password'].' ';
	}
	
 
 
	#####




 
	 
	
	// Отображение столбов
  
	function display_table__edit($row)
	{
		$link = '<a href="iframe_router.php?ctr='.$this->ctr.'&act=edit&id='.$row[$this->key_filed].'" class="table-edit fw_iframeajax"> </a> ';
		 
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
		 return '/sahmatka/ajax_router.php?ctr=agency&act=ag_users&id='.$v['agency_id'];
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
		$t['h1'] = 'Пользователи';
	 
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
	
	
	
	
	
	
	
	
	 
	
	
 
	
	
}