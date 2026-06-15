<?
class fw_validate
{
	var $result;
	var $final_result;
	var $fileds;
	var $log;
	
		function __construct( )
		{
			 
			$this->finalresult = true;
		}
		
		
		function validate( $data ='' )
		{
			 
			$this->data = $data;
			
			if(is_array($data))
			{
				$conf_rez = $this->conf;
				foreach($data as $k=>$v )
				{
					if(isset($this->fileds[$k])){$caption = $this->fileds[$k];}else{$caption='Заголовок не задан';}
					$this->log[] = '<b> ====== Валидация переменной '.$k.'='.$v. ' ('.$caption.') </b>';	
					if(!isset($this->conf[$k])  ){	$this->log[] ='Правила не заданны';}
					// $this->validatevar($k,$v, $data , $this->conf, 'all_vars' );
					if(isset($conf_rez[$k])){unset($conf_rez[$k]);}
					$this->validatevar($k,$v, $data , $this->conf);
				}
				// Переменные валидаторы для которых заданны но значения отсутствуют
				foreach($conf_rez as $k=>$v)
				{
					if(isset($this->fileds[$k])){$caption = $this->fileds[$k];}else{$caption='Заголовок не задан';}
					$this->log[] = '<b> ====== Валидация НЕ ЗАДАННОЙ переменной  '.$k.'="" ('.$caption.') </b>';	
					// $this->validatevar($k,$v, $data , $this->conf, 'all_vars' );
					$this->validatevar($k,false, $data , $this->conf);
				}
				
				
			}
		}
		
		// ДОбавляем правило для валидации пременной
		function addrule( $name , $method , $config = array() )
		{  
			$rule=array();
			$rule = $config;
			
			if(isset($config['caption']))
			{
				$this->fileds[$name] = $config['caption']; // Заголовки переменных
			}
 
			$rule['method']= $method;
			$this->conf[$name][] = $rule;
		}
		
		function addcaption($name,$caption){
			$this->fileds[$name] = $caption; // Заголовки переменных
		}
		
		//
		function validatevar( $k , $v, $data , $config  )
		{
			if(!isset($config_varname)){$config_varname=$k;} // Для метода all_vars
			
			if(isset( $config[$config_varname] ) && is_array( $config[$config_varname] ))
			{
				foreach($config[$config_varname] as $k1=>$v1)
				{
					 
					if( isset( $this->result[$k] ) && $this->result[$k]['processing'] && !$this->result[$k]['status']){break;} // Были обработки и статус false останавливаем дальнейшую обработку
					$met = 'valid__'.$v1['method'];
					//$this->log[] = '<b> Метод:' .$v1['method'].' ('.$met.')</b>';						
					if( method_exists( $this , $met ) ) // Есть метод 
					{
						$this->log[] = 'Обработка методом '.$met .' '.print_r($v1,true);
						$this->result[$k]['result'] = $this->$met($k,$v,$v1); // Применяем метод валидации в конфиге
						
						
						if( !isset( $this->result[$k]['processing'] ) )
						{
							$this->result[$k]['processing']=0;
						}
						$this->result[$k]['processing']++;
						// Помечаем переменную как обработанную методом
						//$this->log[] = $this->result[$k];
						//$this->log[] = $k.'='.$v.' - обработано методом '.$met;
						
						// Останавливаем проверку этой переменной
						if(!$this->result[$k]['status']){  $this->log[] = '<b>НЕ валидно</b>'; }
						else{ $this->log[] = '<b>Успешно</b>';  };
					}
					else
					{
						$this->log[] = 'Метод '.$met. ' НЕ НАЙДЕН' ;
					}
					//$this->log[] = ' ====== Закончена Валидация переменной '.$k.'='.$v;	
				}
			}
			else // нет правил для переменной
			{
				 $this->result[$k]['processing']=0;
			}
		}
		
		
		// Переманная прошла валидацию
		function valid($name,$val)
		{
			$this->result[$name]['value'] = $val;
			$this->result[$name]['status'] = true;
		}
		// переменая не прошла валидацию
		function invalid($name,$message,$config)
		{
			if(isset($this->fileds[$name]) && $this->fileds[$name]) { $message = '<b>'.$this->fileds[$name].'</b> - '.$message;  }
			$this->result[$name]['error_message'][] = $message;
			$this->result[$name]['status'] = false;
			$this->finalresult = false;
 		}
		
		
		// ВЫВОД ОШИБОК ВАЛИДАЦИИ ВСЕХ ПЕРЕМЕННЫХ
		function perr_all()
		{
			$r='';
			if( is_array( $this->result ) )
			{
				foreach( $this->result as $k=>$v )
				{
					$r.=$this->perr($k);
				}
				return $r;
			}
			else
			{
				return '';
			}
			
		}
		
		// ВЫВОД ОШИБОК ВАЛИДАЦИИ ОДНОЙ ПЕРЕМЕННОЙ
		function perr($name='')
		{
			$r='';
			if(!$name){return $this->perr_all();}
			else
			{
				if( isset( $this->result[$name]['error_message'] ) && is_array( $this->result[$name]['error_message'] ) )
				{
					foreach( $this->result[$name]['error_message'] as $k=>$v )
					{
						$r.=$v.'<br/>';
					}
					return $r;
				}
				else
				{
					return false;
				}
			}
		}
		 
		 
		 
		 
		 
		################# Валидаторы
		
		
		
		// Не пустое НЕ РАБОТАЕТ ТАК КАК ЦИАЛ ПО ПЕРЕМЕННЫМ ДАТЫ а если ее нет она и не проверяется!
		function valid__req( $name, $val , $config )
		{
			$message = '';
			if(isset($config['error_message']))
			{
				$error_message = $config['error_message'];
			}
			
			if( !isset( $error_message ) || !$error_message ){ $error_message = 'Поле обязательно к заполнению'; }
			
			if(!$val)
			{
				$this->invalid($name,$error_message,$config);
				return;
			}
			else{ $this->valid($name,$val); }
			return $val;
		}
		
		
		
		
		// Число
		//$valid_conf['all_vars'][] = array("valid"=>"num", "max"=>"3", "min"=>"1");
		function valid__num( $name, $val , $config )
		{
			$message = '';
			 
			// Является числом
			if(!is_numeric($val))
			{
				$this->invalid($name,'Допустимы только числа',$config);
				return;
			}
			else{ $this->valid($name,$val); }
			
			// Минимальное значение 
			if( isset($config['min']) && $config['min'])
			{
				if($val<$config['min'])
				{
					$this->invalid($name,' Допустимое минимальное значение '.$config['min'],$config);
					return;
				}
				else{ $this->valid($name,$val); }
			}
			
						
			// Максимальное значение 
			if( isset($config['max']) && $config['max'])
			{
				if($val>$config['max'])
				{
					$this->invalid($name,' Допустимое максимальное значение '.$config['max'],$config);
					return;
				}
				else{ $this->valid($name,$val); }
			}		 
			return $val;
		}
		
		
		
		
		
		// Email
		// $valid_conf['var'][] = array("valid"=>"email");
		function valid__email( $name, $val , $config )
		{
			if (!filter_var($val, FILTER_VALIDATE_EMAIL)) 
			{
				$this->invalid($name,'Не корректный E-Mail',$config);
				return;
			}
			else{ $this->valid($name,$val); }
			return $val;
		}
		
		 // URL
		// $valid_conf['var'][] = array("valid"=>"email");
		function valid__url( $name, $val , $config )
		{
			if (!filter_var($val, FILTER_VALIDATE_URL)) 
			{
				$this->invalid($name,'Не корректный URL',$config);
				return;
			}
			else{ $this->valid($name,$val); }
			return $val;
		}
		
		 
		
		
		static function valid__phone($phone)
		{
			// Pass phone number in preg_match function 
			if(preg_match( 
				'/^\+[0-9]([0-9]{3})([0-9]{3})([0-9]{4})$/',  
			$phone, $value)) { 
			  
				// Store value in format variable 
				$format = $value[1] . '-' .  
					$value[2] . '-' . $value[3]; 
			} 
			else { 
				 
				// If given number is invalid 
				echo "Invalid phone number <br>"; 
			} 
			  
			// Print the given format 
			echo("$format" . "<br>"); 
	 
		}
		
		
		
		 /**
		 * Форматирование телефонного номера
		 * по шаблону и маске для замены
		 *
		 * @param string $phone
		 * @param string|array $format
		 * @param string $mask
		 * @return bool|string
		 */
		function valid__phone2($phone, $mask = '#')
		{
			
			$formats = array(
			'7' => '###-##-##',
			'10' => '+7 (###) ### ####',
			'11' => '# (###) ### ####'
			);

			$phone = preg_replace('/[^0-9]/', '', $phone);

			if (is_array($format)) {
				if (array_key_exists(strlen($phone), $format)) {
					$format = $format[strlen($phone)];
				} else {
					return false;
				}
			}

			$pattern = '/' . str_repeat('([0-9])?', substr_count($format, $mask)) . '(.*)/';

			$format = preg_replace_callback(
				str_replace('#', $mask, '/([#])/'),
				function () use (&$counter) {
					return '${' . (++$counter) . '}';
				},
				$format
			);

			return ($phone) ? trim(preg_replace($pattern, $format, $phone, 1)) : false;
		}



		
		
		// строка
		 
		function valid__string( $name, $val , $config )
		{
			$val = preg_replace( "/[^a-zA-ZА-Яа-я0-9\s\-\_\+\&]/ui", '', $val );
			$val = strip_tags($val);
			
			$message = '';
			// Является строкой
			if(!is_string($val))
			{
				$this->invalid($name,'Допустимы только строки',$config);
				return;
			}
			else{ $this->valid($name,$val); }
			
			// Минимальное значение 
			if($config['min'])
			{
				if(mb_strlen($val)<$config['min'])
				{
					$this->invalid($name,' Минимум '.$config['min'].' символов',$config);
					return;
				}
				else{ $this->valid($name,$val); }
			}
	 	
			// Максимальное значение 
			if($config['max'])
			{
				if(mb_strlen($val)>$config['max'])
				{
					$this->invalid($name,' Максимум '.$config['max'].' символов',$config);
					return;
				}
				else{ $this->valid($name,$val); }
			}		
						
			return $val;
		}
		
		
		
		
		
		
		
		
		
		

		
 	
}



/*
$valid_conf['all_vars'][] = array("valid"=>"num"  );
$valid_conf['var'][] = array("valid"=>"num"  );
$valid_conf['var2'][] = array("valid"=>"num",  "max"=>"3", "min"=>"1");
//$valid_conf['var'][] = array("valid"=>"email");

$valid = new fw_validate($_GET,$valid_conf);
$valid->fileds['var2'] = 'Заголовок переменной';



//print '<pre>';
//print_r($valid->log);
//print_r($valid->result);


// Не прошла форма
if(!$valid->final_result)
{
	print $valid->perr('text');
}

*/