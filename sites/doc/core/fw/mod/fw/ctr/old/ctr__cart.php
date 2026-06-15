<?
class ctr__cart extends ctr__
{ 

	var $table = 'cart'; //Главная таблица
	var $key_filed = 'cart_id'; // Ключевое поле главной таблицы
 
	function __construct()
	{
		$this->mysql = $GLOBALS['mysql']; 
		$this->cart_type='main';  // тип корзины по умолчанию
		$this->ctr_class = 'ctr__adm_menu'; // Класс вызываеем мтод get_ids()
	}
	
	/* 
	плюс и минус запрпшиваются через аякс пркладки 
	дел тоже через аякс 
	
	
	
	АЯКС ДОБАВЛЕНИЕ ФОРМ!
	
	
	ДОБВЫЛЯЕМ МЕТОДЫ  __CTR
	*/
 
 
 
	// Метод содержимого столбца
	function display_table__edit($row)
	{
		return $link = '
		<a href="?ctr=cart&act=minus&id='.$row[$this->key_filed].'" class="table-edit"> - </a> 
		&nbsp;
		&nbsp;
		<a href="?ctr=cart&act=plus&id='.$row[$this->key_filed].'" class="table-edit"> + </a> 
		&nbsp;
		&nbsp;
		<a href="?ctr=cart&act=del&id='.$row[$this->key_filed].'" style="color:red; "> Удалить </a>
		';
	}
	
	  
	  
	// добавить в корзину указанное количество указанного типа элементов
	function act__ajaxupcart()
	{
		if(!$id){$id = $_POST['id'];}
		if(!$count){$count = $_POST['count'];}
		
		if(!$id){$id = $_GET['id'];}
		if(!$count){$count = $_GET['count'];}
		
		$type = $this->cart_type;  
		
		
		if( $count==0 ){ unset( $_SESSION['cart'][$type][$id] ); return;}
		
		
		$_SESSION['cart'][$type][$id] = $count;
		
		// print_r($_POST);
		// print_r($_GET);
		
		
		print ' OK ';
		print 'type - '.$type.' ';
		print 'id - '.$id.' ';
		print 'count - '.$count.' ';
	}
	
	
	
	
	
	  
	// добавить в корзину указанное количество указанного типа элементов
	function act__plus(   $id , $count=1 )
	{
		if(!$id){$id = $_POST['id'];}
		if(!$count){$count = $_POST['count'];}
		
		$type = $this->cart_type;  
		$_SESSION['cart'][$type][$id] = (int) $_SESSION['cart'][$type][$id] + $count;
		print '';
	}
	
	
	
	
	// добавить в корзину указанное количество указанного типа элементов
	function act__minus( $id, $count=1 )
	{
		if(!$id){$id = $_POST['id'];}
		if(!$count){$count = $_POST['count'];}
		
		$type = $this->cart_type;
		$_SESSION['cart'][$type][$id] = (int) $_SESSION['cart'][$type][$id] - $count;
		
		
		
		
		if( !count( $_SESSION['cart'][$type]) ){ unset( $_SESSION['cart'][$type] ); }
		
		print '';
	}
	
	
	// удаляет запись из корзины
	function act__del( $id )
	{
		if(!$id){$id = $_POST['id'];}
		
		$type = $this->cart_type;  
		unset( $_SESSION['cart'][$type][$id] );
		if( !count( $_SESSION['cart'][$type]) ){ unset( $_SESSION['cart'][$type] ); }
		print '';
	}
	
	
	// Количество товаров в корзине
	function act__countcard()
	{
		$type = $this->cart_type;  
		print count( $_SESSION['cart'][$type] );
	}
	 
	 
	 
	 
	 
	
	function act__index()
	{
		$type = $this->cart_type;
		
		global $t;
		global $r;
		global $mysql;
 
		$this->act__plus(1,1);
 
		print '<h2>Корзина</h2>';
		if(!$_SESSION['cart'])
		{
			print 'Корзина пуста!';
		}
		else
		{
			print ' Содержимое корзины ';
		
			print ' ( Товаров : ';
			print $this->act__countcard();
			print ' )<br/>';

			$str_ids='';
			$da = $_SESSION['cart'][$this->cart_type];
			$i=0;
			foreach($da as $k=>$v )
			{
				$i++;
				$arr_ids[] = $k;
				$str_ids.='"'.$k.'"';
				if( $i<count( $da ) ){ $str_ids.=', ';};
				$arr_count[$k1] = $v1;
			}
			
			// Класс для получения ид
			$x = new $this->ctr_class;
			$where = ' AND '.$x->key_filed .' IN ('.$str_ids.')';
			print $q = $x->get_base_sql($where);		
			
			$data_arr = $mysql->get_arr( $q );
			
			//$arr_count - количество по ид
			 
			
			print '<pre>';
			print_r( $data_arr );
			print '</pre>';
		}
	}
	
	
	 
	
	
}