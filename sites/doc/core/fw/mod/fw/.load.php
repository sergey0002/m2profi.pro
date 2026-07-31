<?

class fw_mod__fw extends fw_mod
{
	
	// Обязательный метод выполняется при загрузке модуля
	function start()
	{
	 
		global $r;
		global $us;
		global $filed;
		 
		require_once('classes/fw_login.php'); //  Логин админ и пользователей
		require_once('classes/fw_login_custom.php'); //  Логин админ и пользователей КАСТОМ
		$us = new fw_login();
		
	 
		require_once('classes/fw_sitedirtree.php');  // 
		
		
		require_once('classes/fw_validate.php');  // Валидация данных
		require_once('classes/filed.php'); //  Поля форм
		$filed = new filed();
		
		require_once('classes/ctr.php'); // ctr__ контроллер
		  
 		require_once('classes/fw_ctr_router.php'); // роутер (ctr act , на классах ctr__)  $_SESSION['group_name'] используется для загузки контроллеров группы доступа ctr__ИМЯ__Группа.php
		$r = new fw_ctr_router();
	}
 
 
 
 
 
 
 
 
 
	// Обязательный метод выполняется при загрузке модуля ОДНОКРАТНО!
	function load()
	{
		fw_log('load','fw_mod:fw');
		
		fw_mod::set('page','Свойство','Значение');
	}
	
	
	static function frontadmin()
	{
		print 'Фронтадмин';
	}
	
	
	static function backadmin()
	{
		print 'Бекадмин';
	}
	
	// Проверка связей необходимых 
	function check_relations_mod()
	{
		$this->relations[] = 'mysql';
	}
	
	
	
	function check_mysql_tables()
	{
		
	}
	
	// Установка таблиц БД
	static function install_db()
	{
		fw_log('load','fw_mod:fw');
		
	}
	
    // Удаление таблиц БД
	static function uninstall_db()
	{
		fw_log('load','fw_mod:fw');
	}
	 // Очистка таблиц БД
	static function clean_db()
	{
		fw_log('load','fw_mod:fw');
	}
}


