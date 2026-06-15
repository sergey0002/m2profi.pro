<?
 
  
 

// time monitoring
$start = microtime(true);
function fw_log($txt='',$r='', $error_code = false , $data_log = false )
{
	global $start;
	global $tlog;
	global $fw_log;
	
	$trace = debug_backtrace();
	 
	
	if($_GET['dev']==2)
	{
		$log_item = array();
		$log_item['text'] = $txt.' - Время выполнения скрипта: '.round(microtime(true) - $start, 4).' сек';
		$log_item['bugtrace'] = $trace;
		$log_item['data_log'] = $data_log;
		$log_item['error_code'] = $error_code;
		 
	}
	elseif( $_GET['dev'] )
	{
		$log_item  = $txt.' - Время выполнения скрипта: '.round(microtime(true) - $start, 4).' сек';
	}
	
	if($r)
	{
		$fw_log[$r][] = $log_item;
	}
	
	$tlog[] = $log_item;
	
	if( $error_code )
	{
		header('HTTP/1.0 '.$error_code.' Not Found');
		header('Status: 404 Not Found');
		print '<html><center>Страница не найдена <br/>404</center></html>';
		if( isset( $_GET['dev'] ) )
		{
			fw_display_log();
		}
		die();
	}
}
 

class fw_mod
{
	var $MOD; //
    var $MOD_VERSION; // 
    var $MOD_VERSION_DATE; // 
    var $MOD_NAME; // 
    var $MOD_DESCRIPTION; // 
	var $set;
	// Установка настроек модуля
	function set( $var , $value )
	{
		if($value)
		{
			$this->set[$var]=$value;
		}
		return $this->set[$var];
	}
}

 