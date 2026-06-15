<?

####### MYSQL  
class m_mysql 
{
	public $с = ''; // Соединение с mYSQL
   
	public $errors_messages = ''; // Сообщения об ошибках для пользователей массив
	public $messages = array(); // Сообщения  
	public $log = array(); // ЛОг ошибок для отладки
	
	
	// Соединение
	function __construct()
	{
		$login=$GLOBALS['config']['mysql_login'];
		$password=$GLOBALS['config']['mysql_password'];
		$base=$GLOBALS['config']['mysql_base'];
		$server=$GLOBALS['config']['server'];
		
		$this->c  = mysqli_connect($server, $login, $password, $base) or die(mysqli_error()); // Соединение с базой данных 
	 	//$result = mysqli_query($this->c, "SET NAMES utf8mb4");
		
//$result = mysqli_query($this->c, "CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;");

	 
	}
	
	
	// Присвоение значения пост переменной при Mysql insert (значения по умолчанию при нулевом значении для чекбуксов итп)
	function data_value($val,$default=false,$data_type='')
	{
		//if( !$default ){ $default = false; }
		
		if( 
			( !$val && $val!==false && $val!==0 ) 
			&& 
			( $default || $defeult===false || $default === 0 )
		  )
		{
			$val = $default;
		}		
		return $val;
	}
	
	
	// Подклеить один результат к другому звязать по равентству ключей $key1 = $key2
	function split_data($data1,$data2,$key1,$key2)
	{
		foreach($data1 as $k=>$v)
		{
			foreach($data2 as $kk=>$vv)
			{
				if($v[$key1] == $vv[$key2])
				{
					foreach($vv as $kkk=>$vvv)
					{
						$data1[$k][$kkk]=$vvv;
					}
				}
			}
		}
		return $data1;
	}
	
	
	############################# ТЕкстовый поиск
	/*
	$fileds[]='users.name';
	$fileds[]='users.e_mail';
	$fileds[]='users.phone';
	$fileds[]='users.login';
	*/
	function search($fileds,$search='')
	{
 
		if(!$search){$search = urldecode( $_GET['search'] ); }
		if($_GET['search'])
		{
			$search = $_GET['search'];
			$sql.=' AND ( ';
			$i=0;
			foreach($fileds as $k=>$v)
			{
				if($i>0){$sql.=' OR ';} $i++;
				$sql.=' '.$v.' LIKE "%'.$search.'%" ';
			}
			$sql .=' )';
		}
		return $sql;
	}



 


	// получить по ключу
	function get_for_key( $table , $key_filed , $key_value, $dop_wh='' )
	{
		if($dop_wh){$dop_wh = ' AND '.$dop_wh;}
		$q = ' SELECT * FROM '.$table .' WHERE `'.$key_filed.'` = "'.$key_value.'" '.$dop_wh ;
		return $this -> get_arr( $q ,1);
	}
	
	
	// обновить строку таблицы
	function update_for_key( $table , $key_filed , $key_value , $data , $print_q = false)
	{
		
		if($_SESSION['sh_login'] == 'demo_admin'){return;}
	 
		$q = ' UPDATE '.$table .' SET ';
		$i = 0;
		
		// Удаляем пустые значения
		// foreach( $data as $k=>$v){	if(!$v && $v!==0){unset($data[$k]);}	}
		
		foreach( $data as $k=>$v )
		{
			//print var_dump($v);
			//print '<br/>';
			//print $k;
			//print '<br/>';
			 
			$data[mysqli_real_escape_string($this->c,$k)] = mysqli_real_escape_string($this->c,$v);
			$k = mysqli_real_escape_string($this->c,$k);
		 
			$vi = mysqli_real_escape_string($this->c,$v);
			$i++;
			
			if( !$v && $v!==0 ){$vi='NULL';}
			else{ $vi = '"'.$vi.'"'; }
			
			$q .= '`'.$k.'` = '.$vi.' ';
			if($i<count($data)){$q.=' , ';}
		}
		
		$key_value = mysqli_real_escape_string($this->c,$key_value);
		$key_filed = mysqli_real_escape_string($this->c,$key_filed);
		
	  
		$q.=' WHERE `'.$key_filed.'` = "'.$key_value.'" ' ;
		if($print_q){print $q;}  
		return $this -> sql( $q );
 	}
	
	
	
	
	
	// Удалить строку из таблицы
	function delete_for_key($table,$key_filed,$value )
	{
		
	}
	
	
	
	
	// Вставить строку в таблицу
	function insert( $table , $data, $ign_error = false,$print=false)
	{
		if($_SESSION['sh_login'] == 'demo_admin'){return;}
 
		$q = ' INSERT INTO '.$table .' ( ';
		$i = 0;
		foreach( $data as $k=>$v )
		{
			$data[mysqli_real_escape_string($this->c,$k)] = mysqli_real_escape_string($this->c,$v);
			$k = mysqli_real_escape_string($this->c,$k);
		    $v = mysqli_real_escape_string($this->c,$v);
		
			$i++;
			$q .=' `'.$k.'` ';
			if($i<count($data)){$q.=' , ';}
		}
		$q .=' ) VALUES (';
		
		$i = 0;
		foreach( $data as $k=>$v )
		{
			$i++;
			if($v!='NOW()')
			{
				$q .=' "'.$v.'" ';
			}
			else
			{
				$q .=' '.$v.' ';
			}
			if($i<count($data)){$q.=' , ';}
		}
		$q .=' );';
 
 if($print){print $q;}
		if( $this -> sql( $q , $ign_error ) )
		{
			return mysqli_insert_id ( $this->c ); // Если все получилось возвращаем последний вставленный ИД
		}
		else
		{
		//	print '--'.$sql.'--';
			return false;
			 
		}
	}
	
	
	
	// insert or not
	/*
	ДОбавить если нет такой записи 
	таблица + массив поле=значение
	возвращаем ид записи
	+ОБНОВЛЕНИЕ ТАБЛИЦЫ ПО УМОЛЧАНИЮ ДА!
	*/
	
	function insert_or_not($table,$find_array,$data,$key_filed,$rfiled='')
	{
		$q = 'SELECT * FROM `'.$table.'` WHERE ';
	//	print_r($table);
	//	print_r($find_array);
		foreach( $find_array as $k => $v )
		{
			  $k = mysqli_real_escape_string($this->c,$k);
			  $v = mysqli_real_escape_string($this->c,$v);
			
			  $q.= ' `'.$k.'` = "'.$v.'" ';
			  $q.=' AND ';
		}
		$q.=' 1 = 1';
		
		$data_array = $this->get_arr($q,1);
		
		
		//print '!!!!!!!!!!!!!!!!!!';
		 //print $q;
		//print_r($data_array);
		
		
		if($data_array)
		{ 
		if(!$rfiled){ return $data_array[$key_filed]; }
		else{ return $data_array[$rfiled]; }
		}
		else
		{
			return $this->insert($table,$data);
		}	
	}
	
 
	// получить ассоциативный массив по sql запросу + КЕШИРОВНИЕ
	function get_arr_c($sql , $first=false,$key=false , $cache_live="604800")
	{
		
		if($cache_live )
		{
			$arr =  get_cache( $sql.$key.$first , $cache_live ); // чтение кеша
		}
		if($arr)
		{
			return $arr;
		}
		else 
		{
		//	print $sql;
			$arr = $this->get_arr($sql, $first,$key);
			set_cache($sql.$key.$first ,$arr ); // пишем кеш
			return $arr;
		}
	}
	
	
	
	function get_select_data($sql,$value_filed,$title_filed,$null='')
	{
		
		$arr = $this->  get_arr($sql,true,$value_filed);
	 
		$new_arr=array();
		$new_arr['']=$null; 
		foreach($arr as $k=>$v)
		{
			if($v[$title_filed]){$caption = $v[$title_filed];}
			else{$caption = '-';}
			$new_arr[$k] = $caption;
		}
		return $new_arr;
	}
	
	
	
	// получить ассоциативный массив по sql запросу
	// key поле которое будет ключем
	function get_arr($sql,$first=false,$key=false)
	{
	   $query = $this->sql( $sql );
	   $i=0;
		while( $result = mysqli_fetch_assoc($query) ) 
		{
			 $result['i']=$i;
			 $i++;
			 if($key)
			 {
				 if( $first )
				 {
					 $arr[ $result[$key] ]=$result;
				 }
				 else
				 {
					 $arr[ $result[$key] ][]=$result;
				 } 
			 }
			 else
			 {
				 $arr[]=$result;
			 }
		}
		if($first && !$key){	$arr=$arr[0]; }
		return $arr;
	}
	
	// Обработка запроса и логирование 
	function sql($sql,$ign_error=false)
	{
		
		// DELETE
		
		if($_SESSION['sh_login'] == 'demo_admin') 
		{
			if( stripos( $sql , 'DELETE' ) || stripos( $sql , 'UPDATE' ) || stripos( $sql , 'INSERT' ) ) 
			{
				return false; //echo 'true';
			}
			if( stripos( $sql , 'delete' ) || stripos( $sql , 'update' ) || stripos( $sql , 'insert' ) ) 
			{
				return false; //echo 'true';
			}
		}


		 //t();
		 $start_t = microtime(true);
		 $result =  mysqli_query( $this->c , $sql );
		 $stop_t = round(microtime(true) - $start_t, 4);
	     //t('Выполнен запрос '. $sql);
		  
		 if( $result )
		 {
			 $log_data['q']=$sql;
			 $log_data['rows']=mysqli_affected_rows( $this->c);
			 $this->count = mysqli_affected_rows( $this->c );
			 $log_data['time']=$stop_t;
			 $GLOBALS['sql_log'][]=$log_data;
			 $GLOBALS['sql_log']['alltime']=$GLOBALS['sql_log']['alltime']+$stop_t;
			 return  $result;
		 }
		 else{
			 print '--'.$sql.'--';
			 print mysqli_error($this->c);
			 if(!$ign_error)
			 {
				die();
			 }
		 }
	}
	
	
	
	
	
	
	
	// вывод таблицы с произвольными полями 
	
	function select_fileds($main_table,$fileds_table)
	{
		
		
	}
	
	
	
	
	
 
	// отображение таблицы для двумерного массива полученного из mysql 
	function display_table($arr,$titles,$all=false,$skin='1' )
	{
 
		// скины таблиц
		$skin_arr[1]['tabletag']=' border="0" ';
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
		<table <?=$s['tabletag']?>>
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
  
			?><tr <?=$s['tabletag']?>><?
			foreach($titles as $kt=>$vt)
			{
				?><td <?=$s['tabletag']?>><?=$v[$kt]?></td><?
			}
			?></tr><?
		}
		?>
		</tbody>
		</table>
		<?
	}
	
	
	
	function pages_menu($allc,$pp,$tp)
	{ 
		global $r;
		$pages = ceil($allc/$pp);
		
		print '<ul class="pages">';
		for($i=1; $i<=$pages; $i++)
		{
 
			if($i==$tp){$litag=''; $atag='style="font-weight:bold;"';}
			else{$litag=''; $atag='';}
			
			$u = $r->acturl();
			print '<li '.$litag.'><a '.$atag.' href="'.$u.'&page='.$i.'">'.$i.'</a></li>';
			
		}
		print '</ul>';
		
		$start = floor($pp*$tp)-$pp;
		$end = $start+$pp;
		if($end>$allc){$end=$allc;}
	 	print ' Всего:'.$allc; 
		print ' Записи: '.$start.'-'.$end;
	 
	}
	
	function pages_limits($allc,$pp,$tp)
	{
		$start = floor($pp*$tp)-$pp;
		if($start<0){$start=0;}
		return ' LIMIT '.$start.','.$pp;
	}
	
	
	
	
}