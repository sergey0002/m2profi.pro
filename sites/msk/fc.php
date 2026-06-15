<?

 
/*
var,метод, источник
метод проверки 
0 - все (1-GET,2-POST,3-COOKIE)
аргументы
*/
class vcheck_filed
{
	 
   public $log;

	function __construct($var_value)
	{
		$this->var_value = $var_value;
 
	}
 
	
	
	   function __call($method_ch, $arguments){
 
		$method = 'ch__'.$method_ch;
 
		
		if(method_exists($this,$method))
		{
			$this->log[]='ВЫПОЛНЯЕМ МЕТОД '.$method;
			//$result = call_user_func_array( array( $this, $method ), $arguments );
			
			$result = $this->$method($arguments);
		}
		else{
			$this->log[]='Метод не найден';
		} 
	 
		if($result)
		{
			return $result;
		}
		else{
			return false;
		}
		
		
    }
	
	
	
 
	
	function ch__int ($val)
	{
		$this->log[]='выполнен метод проверки ';
		
		print_R($val);
		return $this->var_value;
	}
	
	 
}










// функция проверки переменной (хелпер)
	function ch($var,$source=0)
	{
		global $log;
		
		if($source==0)
		{
			$var_value=$_GET[$var];
			$filed = new vcheck_filed( $var_value );
			$log[] = $filed->log;
			
			print_r($filed);
			return $filed;
		}
	}
	
	


 

 
print_r( ch('load')->in2t(1,100)  );
 
print_R($log);


 