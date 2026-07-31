<?
class fw_ctr_router
{
    public $ctr_dir = 'fw/mod';
	
	public $mod_get = 'mod';
    public $ctr_get = 'ctr';
    public $act_get = 'act';

    function get_object($mod='fw',$controller = 'index')
    {
		global $basedir;
		
		fw::load_mod( $mod ); // Загрузка модуля 
        $class = $mod.'__ctr__' . $controller;
        $file_class =  $basedir.$this->ctr_dir .'/'.$mod.'/ctr/'.$class.'.php';

		// print '<h1>'.   $this->ctr_dir .'</h1>';
		// print '<h1>'.  $class.'</h1>';
	
        // Грузим файл класса
        if (!class_exists($class))
        {
            if (file_exists($file_class))
            {
				fw_log(' File exist - '.$file_class,'ctr_router');
                include_once ($file_class);
				
				if (class_exists($class))
				{
					fw_log(' Class exist - '.$class,'ctr_router');
					return $obj = new $class();
				}
				else
				{
					fw_log(' Class NOT exist ERROR - '.$class,'ctr_router');
					throw new Exception(' Class NOT exist ERROR '.$class); // пользовательское исключение
					return false;
				}
            }
            else
            {
				
                fw_log('file ' . $file_class . ' NOT FOUND','ctr_router',500);
                throw new Exception('file ' . $file_class . ' NOT FOUND'); // пользовательское исключение
				return false;
            }
        }
    }

    // выполняем экшен и выводим контент
    function action_content($mod='',$controller = '', $action = '', $data = '')
    {
		if (!$mod){$mod = $_GET[$this->mod_get]; }
		if(!$mod){$mod = 'fw';}
		
        if (!$controller){$controller = $_GET[$this->ctr_get]; }
        if (!$action){$action = $_GET[$this->act_get];}
        if (!$controller){$controller = 'index';}
        if (!$action){ $action = 'index';  }
 
		$method = 'act__'.$action;
		
		$obj = $this->get_object($mod,$controller);
		if(method_exists($obj,$method))
		{
			
			// Проверять права пользователя на действие
			if( !$this->check_user_rules( $mod , $controller , $action ) )
			{
				fw_log('No ACCESS this controller+method on this user!','ctr_router');
		 
				return false;
			}
			else
			{
				fw_log('ACCESS confirmed!','ctr_router');
			}
 
			fw_log('run method' . $method .' ','ctr_router');
			return $obj->$method();
		}
		else
		{
			fw_log(' method not found' . $class .'->'.$method,'ctr_router');
		}
    }
	
	
	
	
	
	
	

    // Проверить права пользователя на контроллер и метод
    function check_user_rules($mod,$controller, $action)
    {
		//if (!preg_match('/^[\w\._]+$/', $mod)) { print 'НЕТ МОДУЛЯ';}
		//if (!preg_match('/^[\w\._]+$/', $controller)) { print 'НЕТ КОНТРОЛЛЕРА';}
 
        return true;
    }

    /*
    // URL на контроллер и действие
    function acturl($controller='',$action='',$file='')
    {
    // Текущие действия если не указаны
    if(!$controller){ $controller = $_GET[$this->ctr_get];}
    if(!$action){ $action = $_GET[$this->act_get];}
    if(!$file){$file =$_SERVER['PHP_SELF']; }
    
    $u=$file;
    $u.='?'.$this->ctr_get.'='.$controller;
    $u.='&'.$this->act_get.'='.$action;
    return $u;
    }
    */
	
	
	
	
	

    // ИЗ m2oc
    function acturl( $mod='' , $controller = '', $action = '', $file = '', $other = '')
    {
        // Текущие действия если не указаны
        if (!$controller)
        {
            $controller = $_GET[$this->ctr_get];
        }
        //if(!$action && $_GET[$this->ctr_get] == $controller ){ $action = $_GET[$this->act_get];}
        if (!$file)
        {
            $file = '/admin/';
        } //$_SERVER['PHP_SELF']; }
        $u = $file;
		
		
		
		
        $u .= '?' . $this->mod_get . '=' . $mod;
		if ($controller)
        {
            $u .= '&' . $this->ctr_get . '=' . $controller;
        }
	 
        if ($action)
        {
            $u .= '&' . $this->act_get . '=' . $action;
        }
        $u .= $other;
        return $u;
    }

    function actlink($controller = '', $action = '', $caption = '- link -', $tpl = '<a href="#url#">#caption#</a>')
    {

        // Проверять права пользователя на действие
        if (!$this->check_user_rules($controller, $action))
        {
            add_logx('No ACCESS this controller+method on this user!');
            return;
        }

        $url = $this->acturl($controller, $action);
        $tpl = str_replace('#url#', $url, $tpl);
        $tpl = str_replace('#caption#', $caption, $tpl);
        return $tpl;

    }

}