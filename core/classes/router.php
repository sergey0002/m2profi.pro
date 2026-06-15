<?
class router
{
	public $ctr_dir_1 = '../../core/etalon_site/fw/controllers';
    public $ctr_dir = 'fw/controllers/';
	 
    public $ctr_get = 'ctr';
    public $act_get = 'act';
    
    // Контроллер и экшен по умолчанию (если не указаны в URL)
    public $default_controller = 'index';
    public $default_action = 'index';

    function get_object($controller = '')
    {
        $class = 'ctr__' . $controller;
		
		$file_class_1 = $this->ctr_dir_1 . $class . '.php';
        $file_class = $this->ctr_dir . $class . '.php';
 

        // Грузим файл класса
        if (!class_exists($class))
        {
            if (file_exists($file_class_1))
            {
                add_logx('load file ' . $file_class_1);
                include_once ($file_class_1);
            }
			elseif( file_exists($file_class_1) )
			{
				add_logx('load file ' . $file_class);
                include_once ($file_class);
			}
            else
            {
                add_logx('file ' . $file_class . ' NOT FOUND');
                return false;
            }
        }
		
		if (!class_exists($class))
        {
            add_logx('class ' . $class . ' not declared ');
            return false;
        }
		
		
        return $obj = new $class();
    }
	
	
	
	
	

    // выполняем экшен и выводим контент


    // выполняем экшен и выводим контент
    function action_content($controller = '', $action = '', $vars = '')
    {
        if (!$controller)
        {
            $controller = $_GET[$this->ctr_get];
        }
        if (!$action)
        {
            $action = $_GET[$this->act_get];
        }

        if (!$controller)
        {
            // Используем default_controller если задан, иначе 'index'
            $controller = isset($this->default_controller) ? $this->default_controller : 'index';
        }

        if (!$action)
        {
            // Используем default_action если задан, иначе 'index'
            $action = isset($this->default_action) ? $this->default_action : 'index';
        }
        $class = 'ctr__' . $controller;
		 
		 
		$method = 'act__' . $action; // метод класса
        add_logx('load ' . $class . '->' . $method . '()');

        // Проверять права пользователя на действие
        if (!$this->check_user_rules($controller, $action))
        {
            add_logx('No ACCESS this controller+method on this user!');
            return;
        }
        else
        {
            add_logx('ACCESS confirmed!');
        }


		
		
		 
		$file_class = $this->ctr_dir . $class . '.php';

        // Грузим файл класса
        if (!class_exists($class))
        {
            if (file_exists($file_class))
            {
                add_logx('load file ' . $file_class);
                include_once ($file_class);
            }
            else
            {
                add_logx('file ' . $file_class . ' NOT FOUND');
                return false;
            }
        }

        if (!class_exists($class))
        {
            add_logx('class ' . $class . ' not declared');
            return false;
        }
		

		$obj = $this->get_object($controller);
        //$obj = new $class();
		
		 
        add_logx('run method ' . $method . ' ');
        $obj->$method();
        return true;
    }


    // Проверить права пользователя на контроллер и метод
    function check_user_rules($controller, $action)
    {
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
    function acturl($controller = '', $action = '', $file = '', $other = '')
    {
        // Текущие действия если не указаны
        if (!$controller)
        {
            $controller = $_GET[$this->ctr_get];
        }
        //if(!$action && $_GET[$this->ctr_get] == $controller ){ $action = $_GET[$this->act_get];}
        if (!$file)
        {
            $file = '/sahmatka/ctrind.php';
        } //$_SERVER['PHP_SELF']; }
        $u = $file;
        $u .= '?' . $this->ctr_get . '=' . $controller;
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