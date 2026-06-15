<?

class ctr__objectx
{
	
	
	
	function act__index()
	{
			
		try {
			$dsn = "mysql:host=localhost;dbname=m2profi_gl";
			$pdo = new PDO($dsn, $GLOBALS['config']['mysql_login'], $GLOBALS['config']['mysql_password'] );
		
			// Устанавливаем режим обработки ошибок (рекомендуется для отладки)
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			// echo "Подключение успешно установлено!";
		} catch (PDOException $e) {
			// Обрабатываем ошибку подключения
			echo "Ошибка подключения: " . $e->getMessage();
		}


		// 
		
		$objManager = new fw_dataflex($pdo, "landplots","lp_id");
		//$objManager->install();
		 
		$objManager->setUnified('filed1', 'varchar');
		$objManager->setUnified('filed2', 'varchar');
		
		$data['cmf__filed1']='1123';
		$data['cmf__filed2']='2123';
		
		// $data['cmf__filed3'][1]='4';
		//$data['cmf__третьяхуета'][4]=false;
		/**
		 * Вставляет или обновляет данные
		 *
		 * @param array $data Данные для вставки или обновления
		 * @param int|null $id Идентификатор записи
		 * @param int|null $edit_object_session Сессия редактирования объекта
		 * @param int|null $mass_object_session Сессия массового редактирования
		 * @param bool $no_main_table Флаг для обработки только произвольных полей
		 * @param bool $reset_many_num Флаг для сброса предыдущих значений множественных полей  $this->
		 * @return bool Возвращает true в случае успешного выполнения, иначе false
		 */
		//$objManager->up($data,120); 
		$objManager->up($data,100,null,null,true); // Удалить не указанные множественные параметры
		
		
		
		print $objManager-> getTreeStylesAndScript();
		//print $objManager->analyze();
		print $objManager->analitycs_objets(null,null,100);//analitycs_objets($obj_type = null, $title_field = null, $obj_id = null)


		print '<pre>';
		print_r($objManager->fields_config);
		print_r($objManager->log);
		print_r($objManager->sql);
		print_r($objManager->errors);
		print '</pre>';
		
		
		$objManager->get_all_custom_fields(); // Все кастомные поля
		
		 if (!empty($objManager->getErrors())) {
			print_r($objManager->getErrors());
		}
		  
			/*
			 

			// Пример использования:
			 
			//$objManager->deleteAllCustomProperties(100);
			$objManager->limit(0, 1);

			// $objManager->where('fw_node_id','=','101');

			//$data["cmf__phone"] = 55555555555;

			 
			//$objManager->where("fw_node_id", "=", "100");
			//$arr = $objManager->get();

 
			# Обновление значения кастомного свойства
			$data = [];
			$data["cmf__phone"] = '!!!!!!!!!!!';
			 
			# Восстановление сессии редактирования из бекапа 
			//$objManager->repair_session($session_id, $object_id = null);



			# Очистка истории не актуальных свойств в базе (удаляются все свойства с не актуальными значениями без возможности отката) - для экономии места и скорости работы 
			// Добавить возможность удаления свойств старше определенной даты 
			// $objManager->delete_non_actual_values();


			// Получаем историю изменений для объекта с ключом 123
			  $history = $objManager->get_historyid(100);
			print '<pre>';
			//print_r($history);
			print '</pre>';

			print $objManager-> getTreeStylesAndScript();
			//print $objManager->analyze();
			print $objManager->analitycs_objets(null,null,100);//analitycs_objets($obj_type = null, $title_field = null, $obj_id = null)

 
			print "<h1>!!!</h1>";
			print "<pre>";
			$arr[0]["content"] = "";
			print_R($arr);

			print "</pre>";

			//print $objManager->analyze();

			if (!empty($objManager->getErrors())) {
				print_r($objManager->getErrors());
			}

			print "<pre>";
			print_r($objManager->sql);
			print "</pre>";
			
			 
			Кеш сомнительно ключ обекта должен быть а там хуета!
			протестировать выборки руками 
 
			СЕССИИ ОБНОВЛЕНИЯ КОРРЕКТНОСТЬ РАБОТЫ!


			WHERE условия относительно Кастом полей? как работают ?
			*/
		 
	}
	
	
	 
	
	
}