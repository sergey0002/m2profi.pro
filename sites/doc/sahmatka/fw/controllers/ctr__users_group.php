<?
class ctr__users_group extends ctr__
{  
 
	var $table = 'users_group'; //Главная таблица
	var $key_filed = 'users_group_id'; // Ключевое поле главной таблицы
	var $ctr = 'users_group';
    var $title = 'Пользователи';
   
	function __construct()
	{
		if(  $_SESSION['sh_login'] != 'admin' && $_SESSION['sh_login'] != 'goodzem' ){ die('Доступ запрещен'); }
		
		$data=$this->getfiltr($filtr);
		$this->data=$data; // Сохраняем данные	
		  
		$data_nofiltr=$this->getfiltr([1]); // Данные без фильров для селектов?! очень ресуроемко ! по сути все брони перебирает циклом , но с другой стороны у нас все записи выводятся и ничего 
		$this->data_nofiltr=$data_nofiltr; // Сохраняем данные
	 
		$titles[$this->key_filed] = 'id';
	 
		$titles['caption']='Роль';
		$titles['group_name']='role';
		// $titles['color']='Цвет';
		 
		$titles['edit'] = '*';
	 
		$this->ajcrud_table_titles=$titles;
		 
		$order=array();
		$order[$this->key_filed]=$this->table.'.'.$this->key_filed;
		 
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
		SELECT users_group.* 
		FROM `users_group` 
		WHERE 1=1 ";
		 
	 
		  
		if( $filtr_data['order_filed'] )
		{
			 $q.=' ORDER BY '.$filtr_data['order_filed']; // Только актуальные брони без истории
			 if( $filtr_data['order_asc'] ) { $q.=' ASC '; } else { $q.=' DESC '; }
		}
		else
		{
			// $q.=' order by agid desc , adm, ac desc'; 
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
			$t['h1'] = 'Новая группа';
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
			   $filed->text('login','Логин',$data['login'],'id="aglogin"'); print '<br/>';
			   $filed->text('password','Пароль',$data['password'],'id="password"'); print '<br/>';
			   $filed->text('name','ФИО',$data['name']); print '<br/>';
			   $filed->text('phone','Телефон',$data['phone']); print '<br/>';
			   $filed->text('e_mail','E-Mail',$data['e_mail']); print '<br/>';
			   
			   $filed->select('role','Роль',$data['role']); print '<br/>';
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
		$link .= '&nbsp;&nbsp; <a href="?ctr=permissions&act=edit&id='.$row[$this->key_filed].'" class="btn btn-sm btn-info" style="font-size: 12px; padding: 2px 5px;">Права</a>';
		 
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
 
  
	
	
  
	
	function act__index()
	{
		global $r;
		global $mysql;
		global $t;
		$t['h1'] = 'Группы пользователей';
	 
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