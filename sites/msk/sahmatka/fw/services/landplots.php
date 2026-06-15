<?

// Сервисный класс для работы с участками 


class serv_landplots
{
	
	
	
	// получить по кадастровому номеру , получить номеру+карты и участка, получить по ...
	
	
	// все участки для которых помечаем в рамках сессии статусом 
	
	
	// Подключаем историю обджексикс!!!! 
	
	
	
	// Получить участок один
	function get( $serach = [], $one = null )
	{
		
	}
	
	 
	
	// Обновить участок по ид
	function up( $space_id , $data , $mass_session = null , $data_space=null )
	{
		global $mysql;
		
		// получаем оригинальные данные участка 
		if(!$data_space)
		{
			$data_space = $mysql->get_for_key('landplots','lp_id',$space_id,1);
		}
		
		
		
	}	 
	
	
	
	
	// Добавление брони в базу в историю броней
	function add_broni( $space_id , $status , $mass_session = null, $data_space = null )
	{ 
		global $mysql;
		
		// для сравнения статуса (если не отличается ни статус ни юзер то не нужно добавлять бронь)
		if(!$data_space)
		{
			$data_space = $mysql->get_for_key('landplots','lp_id',$space_id,1);
		}
		//  print_r($data_space);
		
		// Проверяем если не изменился пользователь и статус
		if($data_space['status'] != $status )
		{ 
			// Записываем бронь
			$data = array();
			$data['lp_id'] = $space_id;
			$data['status'] = $status;
			$data['date'] = date('Y-m-d H:i:s',time()); // текущая дата
			$data['date_first'] = date('Y-m-d H:i:s',time()); // текущая дата
			$data['date_fu'] = date('Y-m-d H:i:s',time()); // текущая дата
			$data['broni_up_counter'] = 0; // текущая дата
			$data['comment'] = ''; // текущая дата
			$data['user_id'] = $_SESSION['sh_id'];
			
			$data['price'] = $data_space['price'];
		  
			print $broni_id = $mysql->insert('landplots_broni',$data);
			 
			
			// Обновляем статус в основной таблице
			$data = array();
			$data['status'] = $status;
			$data['status_broni_id'] = $broni_id;
	 
			$mysql -> update_for_key( 'landplots', 'lp_id', $space_id , $data );
		}
		else{$broni_id = $data_space['status_broni_id'];}
		return $broni_id;
	}
	
	
	
}