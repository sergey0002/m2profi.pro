<?
class fw_mod__fw_messages    extends fw_mod
{
	
	// Обязательный метод выполняется при загрузке модуля
	function start( $cnf )
	{
		
		global $r;
		global $us;
		global $filed;
		
		global $fw_messages;
		
		
		 
		require_once('classes/fw_messages.php'); //  Логин админ и пользователей
		 
		$fw_messages = new fw_messages();
  
	}
 
}
 
 
 
 