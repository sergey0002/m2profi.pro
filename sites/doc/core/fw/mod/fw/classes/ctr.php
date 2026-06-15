<?
class ctr__
{
	
	function __construct()
	{
		$this->mysql=$GLOBALS['mysql'];
		$this->start();
	}
		
		
		
		
		
		
		
 
 
 
 
	// Для насделования
	function start()
	{
		
	}
	
	// В контролере обяхательно имя таблицы и ключевого поля + действия включить какие ? и механизмы
	
	# ДЕйствия CRUD
	/*
	Подя 
	uptime
	show
	block
	user_editor
	update_id
	*/
	function crud__sortup($id,$filed='order')
	{
		global $mysql;
		// Проверка доступа на действие с полем 
	}
	function crud__sortdown($id,$filed='order')
	{
		
	}
	
	function crud__hide()
	{
		
	}
	function crud__show()
	{
		
	}
	
	
	function crud__del()
	{
		
	}
	function crud__undel()
	{
		
	}
	
	
	
	function crud__status()
	{
		
	}
 
 
 
 // Генерирование содержимого стандартных полей (upuser id итп) - калбек для добавления удаления и редактироавния
	
	
	# отображение полей другое (дата, время, сколько назад дата, лайк действие)
	
	 
	
	
	
	
	
	############## Методы формирования полей
	
	# select таблица,условия [ключ]=значение, поля ключ=значение 

	# date
	# datetime
	# date_diapp - диаппазон дат 
	# map
	# photo
	# file
	
	
	## методы обработки полей
	# фото
	# дата 
	#
	
	
	######### ТАБЛИЦА CRUD
	
	// Метод содержимого столбца
	function display_table__edit($row)
	{
		/*
		ob_start();
		print_r($row);
		return ob_get_clean();
		*/
		return $link = '<a href="?id='.$row['id'].'">Редактировать</a>';
	}
	
	function display_table_tr()
	{
		
	}
	
	
	// отображение таблицы для двумерного массива полученного из mysql 
	function display_table($arr,$titles,$all=false,$skin='1' )
	{
	 
		// +СОРТИРОВКА 
		/*
			вверх вниз + по какому столбцу 
			+ включение сортировки для столбцов
			только для столбов разрешенных к сортировке (которые есть в исходниках)
		*/
  
		// Лези лоад 
		/*
			у каждой строки доп атрибут дата data-page=1
			при прокрутке на 100рх от низа
			грузим следующую страницу
			
			
			
			постраничное меню скрываем явой
		*/

		// скины таблиц
		$skin_arr[1]['tabletag']=' border="0" class="dtable" ';
		$skin_arr[1]['thtag']=' ';
		$skin_arr[1]['trtag']=' ';
		$skin_arr[1]['tdtag']=' ';
 
		
		// Все столбцы!
		if($all)
		{
			foreach($arr[0] as $k=>$v)
			{
				if(!$titles[$k])
				{
					$titles[$k]=$k;
				}
			}
		}
		$s=$skin_arr[$skin];
		?>
		<table <?=$s['tabletag']?> id="tablex" >
		<thead>
		<?
		foreach($titles as $k=>$v)
		{
			?><th <?=$s['tabletag']?>><?=$v?></th><?
		}
		?>
		</thead>
		
		<tbody>
		<?
		foreach($arr as $k=>$v)
		{
  
			// Атрибуты строки
			if(method_exists($this,'display_table_row_attr'))
			{
				$row_attr = $this->display_table_row_attr($v);	
			}
				
				
			?><tr <?=$s['tabletag']?> <?=$row_attr?>><?
			foreach($titles as $kt=>$vt)
			{
				// теут проверяем метод $kt и если есть заменяем им $v[kt]!
				$met = 'display_table__'.$kt;
				if(method_exists($this,$met))
				{
					$v2[$kt] = $this->$met($v);	
				}
				else{$v2[$kt] = $v[$kt];}
				?><td <?=$s['tabletag']?>><?=$v2[$kt]?></td><?
			}
			?></tr><?
		}
		?>
		</tbody>
		</table>
		<?
	}
	
	
	
	
	
	
	
	
	
	
	#### CRUD AJAX стандартный
	
	
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
		print 'Элемент удален';
	}
	
	
	# Удаление записи (пометка насовсем)
	function act__del2()
	{
		global $mysql;
		$id = $_GET['id'];
		if($id)
		{			
		 $data = array();
		 $data['del'] = 2;
		 $mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
		}
		print 'Элемент удален без возможности восстановления';
	}
	
	
	
	
	# Восстановление записи (пометка)
	function act__recovery()
	{
		global $mysql;
		$id = $_GET['id'];
		if($id)
		{			
		 $data = array();
		 $data['del'] = 0;
		 $mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
		}
		print 'Элемент восстановлен';
	}
	
	
	/*
	Починить нумерацию
	SET @rownumber = 0;    
	update menu set `menu`.`order` = (@rownumber:=@rownumber+1)
	order by `menu`.`order` desc
	*/
	
	# сортировка вверх
	function act__orderup()
	{
		/*
		Прибавляем ко всем чей ордер больше 1 
		*/
		global $mysql;
		$id = $_GET['id'];
		if($id)
		{			 
	//$sql = 'update '.$this->table.' set order = @num:=@num+1 where 0 in(select @num:=0) ORDER BY order desc';
	//$mysql ->get_arr($sqls);
	
			$arr = $mysql -> get_for_key($this->table,$this->key_filed,$id);		 
			//$mysql -> get_arr('UPDATE `'.$this->table.'` SET `order` = `order` + 1 WHERE `order`<='.$arr['order'].';');
			
			$data = array();
			$data['order'] = $arr['order']-1;
			$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
		}
	}
	
	# Сортровка вниз
	function act__orderdown()
	{
		/*
		Прибавляем ко всем чей ордер больше 1 
		*/
		global $mysql;
		$id = $_GET['id'];
		if($id)
		{			 
			$arr = $mysql -> get_for_key($this->table,$this->key_filed,$id);		 
			//$mysql -> get_arr('UPDATE `'.$this->table.'` SET `order` = `order` + 1 WHERE `order`>='.$arr['order'].';');
			
			$data = array();
			$data['order'] = $arr['order']+1;
			$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
		}
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	#### ЗАпоминание заполнения форм 
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
	
	
	
	
	
	
	
	
	
	
	
	
	
}