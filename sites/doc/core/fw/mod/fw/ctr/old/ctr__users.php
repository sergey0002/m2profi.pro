<?
 

class ctr__users extends ctr__
{ 

	var $table = 'users'; //Главная таблица
	var $key_filed = 'user_id'; // Ключевое поле главной таблицы
 
	
	
	// Метод содержимого столбца
	function display_table__edit($row)
	{
		return $link = '
		<a href="?ctr=users&act=edit&id='.$row[$this->key_filed].'" class="table-edit"> </a> 
		&nbsp;
		&nbsp;
		<a href="?ctr=users&act=del&id='.$row[$this->key_filed].'" style="color:red;  font-size: 21px;"> X </a>
		';
	}
	 
	 
	 // ПОИСК ПОЛЬЗОВАТЕЛЯ ПО ТЕЛЕФОНУ (ajax)
	 function act__ajaxphone()
	 {
		global $mysql;
		$search = $_POST['search'];
		
		$arr = $mysql->get_arr('SELECT * FROM tra_guests WHERE phone LIKE "%'.$search.'%" LIMIT 0,10');
		
		foreach($arr as $k=>$v)
		{
			if($v['phone'])
			{
				$arr_row['label']=$v['phone'].'<b>'.$v['name'].'</b>';
				$arr_row['guest_id']=$v['guest_id'];
				$arr_row['name']=$v['name'];
				$arr_row['f_name']=$v['f_name'];
				$arr_row['o_name']=$v['o_name'];
				$arr_row['date_birth']=$v['date_birth'];
			}
			$arr[] = $arr_row;  
		} 
		print json_encode($arr);
	 }
	



// БАзовый запрос 
	function get_base_sql($where='')
	{
		$q = 'SELECT users.*,
		tra_corpus.caption as ccaption /* Название корпуса */
		FROM users 
		LEFT JOIN tra_corpus ON tra_corpus.corpus_id = users.corpus_id
	 
		WHERE users.hotel_id=1 AND users.user_id!="1" ';
		// if( $_GET['corpus_id'] ) {$q.=' AND tra_corpus.corpus_id="'. $_GET['corpus_id'].'"';}	
		return $q.' '.$where;
	}
	
	




	function act__edit()
	{
		global $filed;
		global $mysql;
		global $r;

		# Данные редактирования
		$id = $_GET['id'];
		if($id && $id!=1)
		{
			$q = $this->get_base_sql('AND users.user_id="'.$id.'"');
			$data = $mysql->get_arr($q,1);
			
			// print_r($data);
			print '<h2>Редактирование пользователя</h2>';
		}
		else
		{
			print '<h2>Новый пользователь</h2>';
			$data['password'] = rand('10000000','90000000');
		}
		if(!$_POST) ############# ФОРМА
		{
		$agency_login='mk'; // ДВУУКВЕННЫЙ КОД АГЕНТСТВА (СЛУЧАЙНЫЕ БУКВЫ)
		?>
		
		<form action="<?=$r->acturl('users','edit');?>&id=<?=$id?>" method="POST">
			
			<?
			// Данные селекта
			$sel_data = $mysql ->get_arr('SELECT * FROM tra_corpus WHERE hotel_id="1" ');
			$sel_arr[0]='Все';
			foreach($sel_data as $k=>$v){	$sel_arr[$v['corpus_id']]=$v['caption'];	}
			?>
			<?=$filed->select('corpus_id','Корпус',$sel_arr,$data['corpus_id'])?><br/>
			<hr/>
			
			<?=$filed->text('f_name','Фамилия',$data['f_name'],' required ');?><br/>
			<?=$filed->text('name','Имя',$data['name'],' required ');?><br/>
			<?=$filed->text('o_name','Отчество',$data['o_name'],' required ');?><br/>
			<?=$filed->text('login','Логин',$data['login'],' required ');?><br/>
			<?=$filed->text('password','Пароль',$data['password'],' required ');?><br/>
			<?=$filed->text('e_mail','E-Mail',$data['e_mail']);?><br/>
			<?=$filed->text('phone','Телефон',$data['phone'],' required ');?><br/> 
			<?=$filed->submit();?><br/>
			
			<script>
			$(document).ready(function() 
			{
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
				<?
				if(!$id)
				{
					?>
					// Транслит названия в логин нового агентства 
					$('input[name="f_name"]').bind('change keyup input click', function(){ $('input[name="login"]').val(urlLit('<?=$agency_login?>-'+$('input[name="f_name"]').val(),0)) });
					<?
				}
				?>
			});
			</script>	

		</form>
		<?
		}
 
		if($_POST) ############# Обработка данных пост
		{
			// 	login	password	name	f_name	o_name	hotel_id	e_mail	phone	addtime	status	del
			$data = array();
			$data['login'] = $_POST['login'];
			$data['password'] = $_POST['password'];
			$data['name'] = $_POST['name'];
			$data['f_name'] = $_POST['f_name'];
			$data['o_name'] = $_POST['o_name'];
			$data['hotel_id'] = 1;
			$data['e_mail'] = $_POST['e_mail'];
			$data['phone'] = $_POST['phone'];
			$data['addtime'] = time();
			
			$data['status'] = 1; // Статус 1 открыт доступ 2 закрыт доступ
			$data['del'] = 0;	
			if($_POST['corpus_id']){$data['corpus_id'] = $_POST['corpus_id'];}
			$data['agency_id'] = 1; // Администрация отеля 1
			$data['user_group_id'] = 1; //1 администратор 2-администратор корпуса 1 итп
			
				
			if($id) // Редактирваоние существующей записи
			{
				print 'Изменения сохранены!';
				$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
			}
			else // Добавление новой записи
			{
				print 'Запись добавлена!';
				$mysql -> insert( $this->table, $data );
			}
		}
		$this->act__index();
	}
	
	
	
	
	// ФИО
	function display_table__fio($row)
	{
		return $txt = $row['f_name'].'<br/>'.$row['name'].'<br/>'.$row['o_name'].'<br/>';
	}
	
	// Доступы 
	function display_table__access($row)
	{
		return $txt = 'Логин - '.$row['login'].'<br/>Пароль - '.$row['password'].'<br/>';
	}
	
	
	// Корпус
	function display_table__corpus_id($row)
	{
		if($row['corpus_id'])
		{
			return $row['ccaption'];
		}
		else
		{
			return 'Все';
		}
	}
	
	
	function act__index()
	{
		global $r;
		global $t;
		$t['h1'] = 'Персонал'; 
		 
		$q = $this->get_base_sql();
		$arr = $this->mysql->get_arr($q);
		
		print '<pre>';
		// print_r($arr);
		print '</pre>';
		
		$titles['fio'] = 'ФИО';
		$titles['access'] = 'Доступы';
		$titles['corpus_id'] = 'Корпус'; 
		$titles['phone'] = 'Телефон'; 
		$titles['e_mail'] = 'E-Mail'; 
		// $titles['group'] = 'Группа'; 
		// $titles['agcaption'] = 'Агентство'; 
		// $titles['status'] = 'Статус'; 
		$titles['edit'] = 'Дейтсвия'; 
		
		?>
		<div class="stat">
			<div class="stat-top stat-top_lp stat-top_user">
				<div class="stat-top-filter" >
				<a href="<?=$r->acturl('','edit');?>" class="stat-top-btn btn btn_arrow-long"  style="margin-left: 0;">Добавить пользователя<i></i></a>
				</div>
				<a href="JavaScript:window.print();" class="stat-top__print" ></a>
			</div>
			<div class="stat-table stat-table_notpd stat-table-user table">
			<?			
				$this ->display_table($arr,$titles);
			?>
			</div>
		</div>			
			<?
		// print_R($this->mysql);
	 
	}
	
	
	 
	
	
}