<?
class ctr__usersx extends ctr__
{  
  
	var $table = 'users_group'; //Главная таблица
	var $key_filed = 'users_group_id'; // Ключевое поле главной таблицы
	var $ctr = 'usersx';
    var $title = 'Пользователи';
   
	function __construct()
	{
		if(  $_SESSION['sh_login'] != 'admin' ){ die('Доступ запрещен'); }
		
		$data=$this->getfiltr($filtr);
		$this->data=$data; // Сохраняем данные	
		  
		$data_nofiltr=$this->getfiltr([1]); // Данные без фильров для селектов?! очень ресуроемко ! по сути все брони перебирает циклом , но с другой стороны у нас все записи выводятся и ничего 
		$this->data_nofiltr=$data_nofiltr; // Сохраняем данные

		$titles[$this->key_filed] = 'id';
		 
		$titles['caption'] = 'Группа';
		 
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
		$q = 'SELECT  users_group.*
		FROM  users_group    
		LEFT JOIN users ON users_group.users_group_id = users.users_group_id 
		WHERE 1=1';
		$q.=' GROUP BY users_group.users_group_id  ';

		// if($_GET['id']){$q.=''}
		//  print $q;
		return $q;
	}
	
	
	
	
	
	function act__edit()
	{
		global $t;
		$t['h1'] = 'Редактирование группы';
		
		global $filed;
		global $mysql;
		global $r;
		global $filed_errors;
		
		$id = $_GET['id'];
 
		if($_POST) ############# Обработка данных пост
		{
			$data = array();
			if($_POST['caption']){$data['caption'] = $_POST['caption'];}
			if($_POST['unactiv']){$data['unactiv'] = 1;}else{$data['unactiv']=0;}
			
			if(!$data['group_name']){$data['group_name']='1';}
			
			if(!$data['caption']){$filed_errors['caption'][] = 'Заполните поле';}
			
			if($id)
			{
				$mysql->update_for_key('users_group','users_group_id',$id,$data);
			}
			else
			{
				$mysql->insert('users_group',$data);
			}
		}
		 
		# Данные редактирования
		$id = (int) $_GET['id'];
		if($id) //
		{
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
			   $filed->text('caption','Название группы',$data['caption'],'id="agcaption"'); print '<br/>';
			   $filed->checkbox('unactiv','Доступ заблокирован',$data['unactiv']); print '<br/>';
			 
			?>
			</div>
		</div>	 
		</form>
		</div>	
		<?
	 
	 
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
			$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
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
			$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
		}
		//$this->act__index();
	}
	
	
	 
	
	 
	
	
	 
	 
	
	
	// Контент раскрывающейся строки
	function display_hr_content($v)
	{
		return '<div style="width:100%; max-width:100vw; text-align:center; padding:5px; " class="loader"><img src="loader.gif" height=40 /></div>';
	}
	
	// Метод генерирует ссылку аякс для контента раскрывающейся строки
	function display_hr_ajax($v)
	{
		 return '/sahmatka/ajax_router.php?ctr=usersx&act=exrow&id='.$v['users_group_id'];
	}
	
	function act__exrow()
	{
		print 123;
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