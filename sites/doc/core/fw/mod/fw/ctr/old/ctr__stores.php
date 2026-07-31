<?
class ctr__stores extends ctr__nodes
{ 

var $table = 'fw_nodes'; //Главная таблица
var $key_filed = 'fw_node_id'; // Ключевое поле главной таблицы
var $ctr = 'stores';


	function __construct()
	{
		global $mysql;
		
		$this->title='Арендаторы';
		$GLOBALS['t']['title']=$this->title;
		$this->post_type = '3'; // Тип поста
		
		// $this->session_form_save();
		
		// Получаем данные 
		$this->data_arr = $mysql->get_arr($this->get_base_sql());
		 
  
		$dir_data_arr = $mysql->get_arr('SELECT * FROM `dir` WHERE  dir_type="'.$this->post_type.'"',true,'dir_id' ); // Данные для селекта Разделов
		$this->dir_date[0]='- Не указан - ';
		foreach($dir_data_arr as $k=>$v)
		{
			$this->dir_date[$k] = $v['dir_title'];
		}
		 
		// Этажи
		$this->floor_date['-1']='-1';
		$this->floor_date[1]=1;
		$this->floor_date[2]=2;
		$this->floor_date[3]=3;
		$this->floor_date[4]=4;
		$this->floor_date[5]=5;
		
		// Спец размещения
		$this->pin_node[0] = 'Стандартное размещение';
		$this->pin_node[1] = 'Первое место';
		$this->pin_node[2] = 'Второе место';
		$this->pin_node[3] = 'Третье место';
	}
	 
 
}