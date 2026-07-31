<?
 

class ctr__stat extends ctr__
{ 

	var $table = 'tra_broni'; //Главная таблица
	var $key_filed = 'broni_id'; // Ключевое поле главной таблицы
 
	
	
	// Метод содержимого столбца
	function display_table__edit($row)
	{
	
 
		return $link = '
		<a href="?ctr=broni&act=edit&id='.$row[$this->key_filed].'" class="table-edit"> </a> 
		&nbsp;
		&nbsp;
		<a href="?ctr=broni&act=del&id='.$row[$this->key_filed].'" style="color:red;  font-size: 21px;"> X </a>
		
		 
		
		';
	}
	
	
	
	
	
	function formfiltr()
	{
		?>
		<form action="" method="get">
			123
		</form>
		<?
	}
	
	function today()
	{
		$this->formfiltr();
		
		?>
			 
		<div class="stat">
			<div class="stat-top stat-top_lp stat-top_user">
				<div class="stat-top-filter" >
					<a href="<?=$r->acturl('','edit');?>" class="stat-top-btn btn btn_arrow-long"  style="margin-left: 0;">ДОБАВИТЬ КОРПУС<i></i></a>
				</div>
				<a href="JavaScript:window.print();" class="stat-top__print" ></a>
			</div>
			<div class="stat-table stat-table_notpd stat-table-user table">
			<?			
				// $this ->display_table($arr,$titles);
			?>
			Сегодня, 27 марта, 20:04:39
			Заезды1
			Выезды0
			Проживания1
			Дни рождения5
			Задачи0 (2)
			Всего номеров8 (19)
			Всего мест26 (1)
			Свободно номеров6 (16)
			Занято номеров2 (3)
			Загрузка25% (16%)
			</div>
		</div>			
			 
		
			
		<?
	}
	
	
 
	 
	 
	

	
	function act__index()
	{
		global $t;
		global $r;
		$t['h1'] = 'Статистика';
		
		$sql = 'SELECT tra_corpus.*,
		tra_hotels.caption as hcaption,
		tra_locals.caption as lcaption
		 
		FROM tra_corpus 
		LEFT JOIN tra_hotels ON tra_hotels.hotel_id = tra_corpus.hotel_id  
		LEFT JOIN tra_locals ON tra_hotels.local_id = tra_locals.local_id  
		 
		WHERE 
		del=0 
	 
		  
		';
		$arr = $this->mysql->get_arr($sql);
		
		$titles['caption'] = 'Корпус';
		$titles['descr'] = 'Описание';
		$titles['edit'] = 'Действия'; 
		 
	 
		
		?>
		<div class="stat">
			<div class="stat-top stat-top_lp stat-top_user">
				<div class="stat-top-filter" >
					<a href="<?=$r->acturl();?>" class="stat-top-btn btn  "  style="margin-left: 0;">Показать<i></i></a>
				</div>
				<a href="JavaScript:window.print();" class="stat-top__print" ></a>
			</div>
			<div class="stat-table stat-table_notpd stat-table-user table">
			<?			
			//	$this ->display_table($arr,$titles);
			?>
			</div>
		</div>			
			<?
		
		
		//print_R($this->mysql);
	}
	
	
	 
	
	
}