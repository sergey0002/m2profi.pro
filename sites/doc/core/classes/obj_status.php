<?
// Класс для работы с статусами обектов

/*
Таблицы 
	obj_status - настройки статусов по умолчанию
	obj_status_group - настройки статусов для групп пользователей
*/
class obj_status
{	
	function __construct()
	{
		
		
		
		
	}
	
	
	// Получить все данные статусов
	function get_status_arr( $users_group_id ="1")
	{
		global $mysql;
		$data = $mysql->get_arr('SELECT * FROM obj_status LEFT JOIN obj_status_group ON obj_status ');
		
		
	}
	
	
	
	// Получить данные статуса одного 
	function get_status_data()
	{
		
	}
	
	// ВЫвод селекта для редактора статуса
	function display_select()
	{
		
	}

	// вывод легенды статусов
	function display_legend()
	{
		
	}
	
	
		

	
}



$obj_status = new obj_status();