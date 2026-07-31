<?


class fw_mod__fw_mailer extends fw_mod
{
	
	// Обязательный метод выполняется при загрузке модуля
	function start( $cnf )
	{
		global $fw_mailer;
		
		require_once 'classes/phpmailer/Exception.php';
		require_once 'classes/phpmailer/PHPMailer.php';
		require_once 'classes/phpmailer/SMTP.php';
 
		require_once 'classes/phpmailer/fw_wrapper_mailer.php';
	
		$fw_mailer = new fw_wrapper_mailer( $cnf );
 
	}
 
}
 
 
 
 