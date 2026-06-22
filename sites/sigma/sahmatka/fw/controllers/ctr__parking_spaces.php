<?
class ctr__parking_spaces extends ctr__
{  

	var $table = 'parking_spaces'; //Главная таблица
	var $key_filed = 'parking_space_id'; // Ключевое поле главной таблицы
	var $ctr = 'parking_spaces';
    var $title = 'Парковочные места';
  
	function __construct()
	{ 
		$this->data=$this->getfiltr(); // Получаем данные для вывода
			 
		$data_nofiltr=$this->getfiltr([1]); // Данные без фильров для селектов?! очень ресуроемко ! по сути все брони перебирает циклом , но с другой стороны у нас все записи выводятся и ничего 
		$this->data_nofiltr=$data_nofiltr; // Сохраняем данные
		
		/*
		Перезагружать содержимое селектов при каждой выборке по хорошему? только те которые не указаны в гет запросе?
		+ в гет запросе указывать только не нулевые!
		
		+ как то псевдонимы прикрутить к гет запросам?!
		*/
		
		// Выводимые столбцы
		$titles = array();
		$titles[$this->key_filed] = 'id';
		$titles['num'] = 'Номер';
		$titles['adress'] = 'Здание';
		$titles['floor'] = 'Этаж';
		$titles['area'] = 'Площадь';
		$titles['price'] = 'Цена';
		$titles['status'] = 'Статус';
		$titles['edit'] = 'Действия'; 
		$this->ajcrud_table_titles=$titles;
		
		// Не переносить по словам
		$nowrap=array();
		$nowrap['edit'] = 1;
		$this->ajcrud_table_nowrap=$nowrap;
		
		// Сортировать по столбцам
		$order=array();
		$order[$this->key_filed]=$this->key_filed;
		$order['adress']='adress';
		$order['show']='show';
		 
		$this->aj_crud_table_order=$order; 
		
		$this->aj_crud_addbutton=1;
	}
	
	
 	// БАзовый запрос  menu
	function get_base_sql($filtr_data='')
	{
		// ПРиоритетно ставим гет переменные
		foreach($_GET as $k=>$v)
		{
			// $filtr_data[$k]=$v;
		}
		if(!$filtr_data){$filtr_data =  $_REQUEST;}
		
		global $mysql;
		$q = 'SELECT '.$this->table.'.* , 
		parking_floors.floor as floor, parking_buildings.adress, parking_buildings.street,  parking_buildings.adress_disp  

		FROM  '.$this->table.'   ';
		 
		$q.=' LEFT JOIN parking_buildings ON parking_buildings.parking_building_id = parking_spaces.parking_building_id ';
		
		$q.=' LEFT JOIN parking_floors ON parking_floors.parking_floor_id = parking_spaces.parking_floor_id ';
		
		$q.='  WHERE 1=1 ';
		
		if(!$_GET['show_dell']){	$q.=' AND ( `'.$this->table.'`.`del`="0" OR `'.$this->table.'`.`del` IS NULL  )';	}
		
		
		
		
		
		else{$q.=' AND `'.$this->table.'`.`del`="1" ';}
		
		if($filtr_data['id']){	$q.=' AND `'.$this->table.'`.`'.$this->key_filed.'`="'.$filtr_data['id'].'" ';	}
		
		if( $filtr_data['parking_building_id'] )
		{
			$q.=' AND parking_buildings.parking_building_id = "'.$filtr_data['parking_building_id'].'" '; // Только актуальные брони без истории
		}
		
		
		if( $filtr_data['floor'] )
		{
			$q.=' AND parking_floors.floor = "'.$filtr_data['floor'].'" ';  
		}
		
		if( $filtr_data['price'] )
		{
			$q.=' AND parking_spaces.price = "'.$filtr_data['price'].'" ';  
		}
		
		if( $filtr_data['status'] )
		{
			if($filtr_data['status'] == 0 || $filtr_data['status'] == 2 )
			{
				$q.=' AND ( parking_spaces.status = "0" OR   parking_spaces.status = "2" ) ';  
			}
			else
			{
					$q.=' AND   parking_spaces.status = "'.$filtr_data['status'].'" ';  
			}
		}
		
		// if($_GET['id']){$q.=''}
		// print $q;
		return $q;
	}
	
	
	
	# Удаление записи (пометка)
	function act__del()
	{
		global $mysql;
		$id = $_GET['id'];
		if($id)
		{			
			$data = array();
			$data['del'] = 1;
			# $mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
		}
		$this->act__index();
	}
	
	 
	
	### ДЕЙСТВИЯ КОНТРОЛЛЕРА
	
	
	function act__edit()
	{
		global $t;
		$t['h1'] = 'Редактирование парковочного места';
		
		global $filed;
		global $mysql;
		global $r;
		 
	   $broni_status[0] = 'Не задан';
	   $broni_status[2] = 'Свободна';
	   $broni_status[4] = 'Забронирована';
	   $broni_status[3] = 'Продано';	
	   $broni_status[5] = 'Забронировано застройщиком';
	   $broni_status[6] = 'Подрядчика';	
	 	  

	   $broni_colors[0] = '#8DFFA9';
	   $broni_colors[2] = '#8DFFA9';
	   $broni_colors[4] = '#FEFF52';
	   $broni_colors[3] = '#FF8A90';
	   
	   
		# Данные редактирования
		$id = $_GET['id'];
		if($id)
		{
			$data = $mysql->get_for_key($this->table,$this->key_filed,$_GET['id']);
			//print '<h2>Редактирование объекта </h2>';
		}
		else
		{
			//print '<h2>Добавление объекта</h2>';
		}
		
		
		if(!$_POST) ############# ФОРМА
		{
		?>
		
		<style>
		.input_edit {width:100%; max-width:100%;}
		</style>
					
			<form action="<?=$r->acturl($this->ctr,'edit');?>&id=<?=$_GET['id']?>" method="POST" id="editform"  >
		 
			<div id="tree_check"></div>

			<br/><br/>
			<?=$this->formpanel($r->acturl($this->ctr,'index'));?>
			<div class="row">
				<div class="col-md-6">
				<?
 
					$b = $mysql->get_arr('SELECT * FROM parking_floors LEFT JOIN parking_buildings ON parking_floors.parking_building_id = parking_buildings.parking_building_id ');
					 
					foreach($b as $k=>$v)
					{
						$bildings_arr[$v['parking_floor_id']] = $v['adress_disp'].' - ЭТАЖ '.$v['floor'];
					}
					print $filed->select('parking_floor_id','Здание + этаж',$bildings_arr,$data['parking_floor_id']);
					 
					print $filed->text('num','Номер',$data['num']); print '<br/>';
					print $filed->text('area','Площадь',$data['area']); print '<br/>';
					print $filed->text('price','Цена',$data['price']); print '<br/>';
					print $filed->select('status','Стаус помещения',$broni_status,$data['status']);
				  
					//print $filed->textarea('descr','Описание',$data['descr'],' rows="2" style="height: 100px;" '); print '<br/>';
				?>
				</div>
				<div class="col-md-6">
				<?
					//print $filed->image('photo','Фото ',$data['image'],'34256'); print '<br/>';
				?>
				</div>
			</div>
	 
			</form>
		
 		
			</div>
	
		<?
		}
 
		if($_POST) ############# Обработка данных пост
		{
			if(!$id){$id=$_GET['id'];}
			
			//print '<pre>';
			//print_r($_POST);
			//print '</pre>';

			$data = array();
			$data['num'] = $_POST['num'];
			$data['area'] = $_POST['area'];
			$data['price'] = $_POST['price'];
			$data['status'] = $_POST['status'];
			$data['parking_floor_id'] = $_POST['parking_floor_id'];
			$data['del'] = 0;
			
			$buf = $mysql->get_for_key('parking_floors','parking_floor_id',$data['parking_floor_id'],1);
			 
			$data['parking_building_id'] = $buf['parking_building_id'];
			 
			if($id) // Редактирваоние существующей записи
			{
				//	print 'Изменения сохранены!';
				$mysql -> update_for_key( $this->table , $this->key_filed , $id , $data );
				
			}
			else // Добавление новой записи
			{
				//	print 'Запись добавлена!';
				$mysql -> insert( $this->table , $data );
			}
			
			 $this->act__index();
		}
		 
	}
	
	

	function act__broni()
	{
		global $mysql;
		$id = $_GET['id'];
		if($id)
		{
			$filtr = array();
			$filtr['id'] = $id;
			$data = $mysql->get_arr($this->get_base_sql($filtr));
			$data=$data[0];
			 
		}
		else
		{
			print 'Не указан объект';
			return;
		}
		$this->tpl($data,'parking_spaces','form_broni_pub'); // ШАБЛОН ЗАПИСИ / БРОНИ для ПУБЛИЧНОГО ДОСТУПА БЕЗ ОБРАБОТКИ ЗАЯВОК!!!
		
		if($_POST)
		{
			print_r();
			// Обработка формы
		}
  
	}
	
	
	
	function act__card_ajaxform()
	{ 
	 
		global $formProtect;
		global $fw_mailer;
		header('Content-Type: application/json; charset=utf-8');
		try {
			$formProtect = new FormProtect();
 
			// 1. Валидация и все защиты одним методом
			$rules = [
				'adress_disp'    => 'required|string|min:3|max:164',
				'floor'    => 'required|string|min:1|max:64',
				'num'    => 'required|string|min:1|max:4',
				
				'fio'    => 'required|string|min:3|max:64',
				'phone'   => 'required|validPhone',
				'message' => 'string|max:300|noHtml|noLinks',
		 
			];
			$data = $formProtect->validateForm($rules); 
 
			$titles=[];
			$titles['adress_disp']='Адрес';
			$titles['floor']='Этаж';
			$titles['num']='Номер';
			$titles['fio']='ФИО';
			$titles['phone']='Телефон';
			$titles['message']='Сообщение';
			 
			// Собираем сообщение 
			 $message = fw_messages::build_message($data, $titles);
	 
			// Отправка письма нескольким получателям (указываем через запятую)
			$recipients = '89236470002@mail.ru,op@em-nsk.group'; //, ' 
			
		 
			if (!$fw_mailer->send($recipients, 'Заявка EM-NSK.RU - публичная карточка парковки ', $message)) {
				// Если send_to_multiple() вернул false, сообщаем об ошибке
				$formProtect->fail('Не удалось отправить заявку. Попробуйте позднее.');
				exit; 
			}
		  
			// Если мы дошли до этой точки, значит send_to_multiple() вернул true
			$formProtect->ok('Ваша заявка успешно отправлена');

		} catch (Throwable $e) {
			// Этот блок сработает при исключениях
			error_log('[FormProtect Backend Error] ' . $e->getMessage());
			$formProtect->fail('Ошибка сервера. Попробуйте позднее.');
		}
	}
	 
	
	
	
	### ФОрма брони для админов и агентств
	function act__order()
	{
		global $mysql;
		global $t;
		$t['h1']='Бронирование парковки';
		$data = $this->get_id_arr($_GET['id']);
		
		if(!$data){ print 'Объект не найден'; return;}
		
		if($_POST)
		{
			if(check_access('admin') ||  $_SESSION['sh_login'] =='em_nsv'  ) // Админ агентства или агент
			{
				// print 'Обработка админской формы';
				 
				$idx = (int) $_GET['id'];
				
				// Запись брони!  
				$broni_idx = $this->add_broni($idx,$_POST['status']);

				$datax = array();
				$datax['price'] = $_POST['price'];
				$datax['area'] = $_POST['area'];
				// $datax['status'] = $_POST['status']; // Статус меняется спец методом add_broni()
				$datax['size'] = $_POST['size'];
				$datax['num'] = $_POST['num'];
				$datax['status_broni_id'] = $broni_idx;
				$datax['del'] = $_POST['del'];
				if(!$datax['del']){$datax['del']=0;}
					
				$mysql->update_for_key('parking_spaces','parking_space_id',$idx,$datax,0);
	  
				print 'Данные сохранены';
				// 
				$data = $this->get_id_arr($_GET['id']);
				$this->tpl($data,'parking_spaces','form_broni_ag'); // ШАБЛОН ЗАПИСИ / БРОНИ для ПУБЛИЧНОГО ДОСТУПА БЕЗ ОБРАБОТКИ ЗАЯВОК!!!
			}
			elseif( $_SESSION['sh_login'] )
			{
				// Обработка формы бронирования
				  $this->add_broni_pf();
			}
			else
			{
				print 'ДОступ запрещен';
				return;
			}
		}
		else
		{
		$data = $this->get_id_arr($_GET['id']);
		$this->tpl($data,'parking_spaces','form_broni_ag'); // ШАБЛОН ЗАПИСИ / БРОНИ для ПУБЛИЧНОГО ДОСТУПА БЕЗ ОБРАБОТКИ ЗАЯВОК!!!
		}
		
		// История броней
		if( check_access('admin') )
		{
			$idx = (int) $_GET['id'];
			$this->act__broni_history($idx);
		}
		
	}
	
	// Добавление брони в базу
	function add_broni($space_id,$status)
	{
		 
		global $mysql;
		
		$data_space = $mysql->get_for_key('parking_spaces','parking_space_id',$space_id,1);
		 
		// print_r($data_space);
		
		// Проверяем если не изменился пользователь и статус
		if($data_space['status'] != $status)
		{
			// Записываем бронь
			$data = array();
			$data['parking_space_id'] = $space_id;
			$data['status'] = $status;
			$data['date'] = date('Y-m-d H:i:s',time()); // текущая дата
			$data['date_first'] = date('Y-m-d H:i:s',time()); // текущая дата
			$data['date_fu'] = date('Y-m-d H:i:s',time()); // текущая дата
			$data['broni_up_counter'] = 0; // текущая дата
			$data['comment'] = ''; // текущая дата
			$data['user_id'] = $_SESSION['sh_id'];
			$broni_id = $mysql->insert('parking_broni',$data);
			
			// Обновляем статус в основной таблице
			$data = array();
			$data['status'] = $status;
			$data['status_broni_id'] = $broni_id;
	 
			$mysql -> update_for_key( 'parking_spaces', 'parking_space_id', $space_id , $data );
		}
		else{$broni_id = $data_space['status_broni_id'];}
		return $broni_id;
	}
	
	
	
	
	
	function act__broni_history($space_id='',$ttitle=false)
	{
		if(!$space_id){$space_id = $_GET['id'];}
		if($_GET['nott']){$ttitle =false;}
		
		global $mysql;
		global $status_arr;
		global $status_color_arr;
		
		$q = 'SELECT parking_spaces.*,users.*,agency.caption as agcaption , parking_broni.status as b_status , parking_broni.date  , parking_broni.parking_broni_id ,parking_buildings.*
		FROM parking_spaces  
		LEFT JOIN parking_broni  ON parking_broni.parking_space_id =parking_spaces.parking_space_id
		
		LEFT JOIN parking_buildings  ON parking_buildings.parking_building_id =parking_spaces.parking_building_id
		 
		
		LEFT JOIN users ON users.id =parking_broni.user_id
		LEFT JOIN agency ON agency.agency_id = users.agency_id
		';
		if($space_id){ $q.=' WHERE parking_spaces.parking_space_id = "'.$space_id.'" '; }
		
		$q.=' ORDER BY parking_spaces.parking_space_id, parking_broni.date DESC';
		
		$space_data = $mysql->get_arr($q );
		if(!$space_data[0]['parking_broni_id']){return;}
		// print '<pre>'; 
		//  print_r($space_data );
		// print '</pre>';
		
		
		?>
		<table class="bronihtable">
		
		<?
		if($ttitle)
		{
		?>
		<tr>
			<td><b>Дата</b></td>
			<td><b>Агентство</b></td>
			<td><b>Пользовтель</b></td>
			<td><b>Статус</b></td>
		</tr>
		<?
		}
		?>
		<tbody>
		<?
		$i=0;
		foreach($space_data as $k => $v )
		{
			$i_ps=0;
			if(!$v['parking_broni_id']){continue;}
						 
			if($parking_space_id!=$v['parking_space_id'] && !$space_id)
			{
				?>
				<tr><td colspan="10" align="center">
				<?
				  if($v['parking_broni_id'] != $v['status_broni_id'] && $i_ps==0 )
				  {
					  print $v['adress_disp'].' - '.$v['num'];
					  print '<h1>Ошибка</h1>';
					  
					  if($v['b_status'] == $v['status'])
					  {
						 print '';
					  }
					  else
					  {
						   print 'Нельзя исправить   ';
					  }
				  }
				 else
				 {
					 print $v['adress_disp'].' - '.$v['num'];
					// print $v['status_broni_id'];
				 }
				
				?>
				</td></tr><?
				$parking_space_id = $v['parking_space_id'];
			}
			else // То же место следующая бронь
			{
				$i_ps++;
			}
			
			if($v['parking_broni_id'] == $v['status_broni_id']) {	 $style="font-weight:bold;";	$tb_text='(Текущий статус)'; } else{$style=""; $tb_text='';}
			?>
			<tr>
			<td style="<?=$style?>"><?=fromsql_date($v['date'])?></td>
			<td style="<?=$style?>"><?=$v['agcaption']?></td>
			<td style="<?=$style?>"><?=$v['login']?> (<?=$v['name']?>)</td>
			<td  style="<?=$style?>"><span style="background-color:<?=$status_color_arr[$v['b_status']]?>;" > <?=$status_arr[$v['b_status']]?> <?=$tb_text?></span></td>
			</tr>
			<?
			$i++;
		}
		?>
		</tbody>
		</table>
	 
		<?
		
	}
	
	
	
 
	
	
	
	// Обработка формы
	function add_broni_pf()
	{
		global $mysql;
		
		$space_id = (int) $_GET['id'];
		if(!$space_id){print 'Не указан id '; return;}

		// Получаем данные места
		$space_data = $mysql -> get_for_key('parking_spaces','parking_space_id',$space_id,1);
		 
		// print_r($space_data);
		
		// Проверяем текущий статус помещения  
		if( $space_data['status']  && $space_data['status']!='2' && 1==2) 
		{
			//	if($stat!='' && $stat!='2' && $stat!='5' && $stat!='0' ){print '<h2 style="color:red">Ошибка бронирования квартира уже забронирована другим пользователем</h2>';  $err_m[]='Квартира уже забронирована другим пользователем';}
		
			print 'Ошибка - конфликт статуса бронирования - вероятно место было забронированно другим пользователем, пока вы заполняли форму';
			return;
		}
		elseif($space_data['parking_space_id'])
		{
			//
			
			if( !$_FILES['passport_scan']['type'] || !$_FILES['passport_scan2']['type'] || !$_FILES['anket']['type'] )
			{
				?><h2 style="color:red">Для бронирования необходимо загрузить указанные файлы</h2><?
			}
			else
			{
				//print 'Применение брони успешно';
				//print ' ID Брони: '; 
				$new_broni_id =  $this->add_broni($space_id,4);
				
				$dir = "uploads_parking/$bron_id/";
				mkdir($dir, 0777);
				
				if($_FILES['passport_scan']['type'])
				{
						$ext =  substr(strrchr(basename($_FILES['passport_scan']['name']), '.'), 1);
						$uploadfile = $dir . basename('passport_scan'.'.'.$ext);
						$files[0] = 	$uploadfile; // для письма
						if (move_uploaded_file($_FILES['passport_scan']['tmp_name'], $uploadfile))
						{
							echo "Скан пспорта 1 - Файл был успешно загружен.\n <br>";
						} 
						else 
						{
							echo "Ошибка!\n";
							$err_m[]='Скан паспорта не был загружен';
						}
				}

				if($_FILES['passport_scan2']['type'])
				{
						$ext =  substr(strrchr(basename($_FILES['passport_scan2']['name']), '.'), 1);
						$uploadfile = $dir . basename('passport_scan2'.'.'.$ext);
						$files[1] = 	$uploadfile; // для письма
						if (move_uploaded_file($_FILES['passport_scan2']['tmp_name'], $uploadfile))
						{
							echo "Скан пспорта 2 - Файл был успешно загружен.\n<br>";
						} 
						else 
						{
							echo "Ошибка!\n";
							$err_m[]='Скан паспорта 2 не был загружен';
						}
				}

				if($_FILES['anket']['type'])
				{
						$ext =  substr(strrchr(basename($_FILES['anket']['name']), '.'), 1);
						$uploadfile = $dir . basename('anket'.'.'.$ext);
						 $files[2] = 	$uploadfile; // для письма
						if (move_uploaded_file($_FILES['anket']['tmp_name'], $uploadfile))
						{
							echo "Анкета- Файл был успешно загружен.\n<br>";
						} 
						else 
						{
							echo "Ошибка!\n";
							$err_m[]='Анкета-  не был загружен';
						}
				}
				
				if($_POST && !$err_m)
				{
					?>
					<h2 style="color:#000; text-align:center;">Место забронировано</h2>
					<p style="color:#00CDAD; font-weight:bold;; font-size:20px; text-align:center;">Срок действия брони - 10 календарных дней, по прошествии 10 дней бронь будет анулирована автоматически</h2>
					<hr/>
					<?
					
					
						$message = "Бронирование места \r\n <br/>";
						$message .= "Заявка поступила от пользователя - <b>".$_SESSION['sh_name'].'</b> Представителя агентства - <b>'.$_SESSION['ucaption']."</b>\r\n </b><br/> ";
						$message .= "Дом <b>".$homes[$home_id]['caption']."</b> секция-<b>".$section_id."</b> Этаж-<b>". $floor."</b> м/м-<b>".$apartments_num."</b> ";
							
						// XMail('89236470002@mail.ru', 'Бронирование квартиры '.$homes[$home_id]['caption']. '/сек-'.$section_id.'/этаж-'.$floor.'/кв'.$_GET['num'], $message, $files);
						# XMail( 'site@em-nsk.ru', 'em-opd@mail.ru', 'Бронирование квартиры '.$homes[$home_id]['caption']. '/сек-'.$section_id.'/этаж-'.$floor.'/кв'.$_GET['num'], $message, $files);

				 
						/*
				 
						include_once('SendMailSmtpClass11.php');
 	
						 $mailSMTP = new SendMailSmtpClass('energomontaz452@mail.ru', '!uuAyUoyLO3,3', 'ssl://smtp.mail.ru',465,"UTF-8"); // создаем экземпляр класса
						// от кого
						$from = array(
							"EM-NSK", // Имя отправителя
							"energomontaz452@mail.ru" // почта отправителя
						);

						// кому отправка. Можно указывать несколько получателей через запятую
						$to = 'op@em-nsk.group,   89236470002@mail.ru';
						$to = '89236470002@mail.ru';
						// добавляем файлы
						$mailSMTP->addFile($files[0]);
						$mailSMTP->addFile($files[1]);
						$mailSMTP->addFile($files[2]);
						
						// отправляем письмо
						############## $result =  $mailSMTP->send($to,  'Бронирование квартиры '.$homes[$home_id]['caption']. '/сек-'.$section_id.'/этаж-'.$floor.'/кв '.$apartments_num, $message, $from); 
						//if($result === true){	echo "Done";	}
						//else{echo "Error: " . $result;	}
					*/
		
				}
				
			}
			
			
			//
		}
		else
		{
			print 'Не корректный id';
			return ;
		}
		
		return;
		
  
	
	}
	
	
	
	
	
	
	
	
	
	
	##########################################################
	##### CRUD
	
	
	// Метод содержимого столбца
	function display_table__edit($row)
	{
		global $t;
		$t['h1'] = 'Парковочные места';
		return $link = '
		<a href="?ctr='.$this->ctr.'&act=edit&id='.$row[$this->key_filed].'" class="table-edit"> </a> 
		&nbsp;
		&nbsp;
		<a href="?ctr='.$this->ctr.'&act=del&id='.$row[$this->key_filed].'" style="color:red;  font-size: 21px;"> X </a>
		';
	}
	 
	 
	 
	 
	function display_table__status($v)
	{
		global $status_arr;
		global $status_color_arr;
		return  '<span style="background-color:'. $status_color_arr[$v['status']].';  "><b>'. $status_arr[$v['status']].'</b></span>';
	}
	 
	
	function ajcrud_filtr()
	{
		?>
		<div class="filter-item"> 
		<? $this->filtr_select('Здание','parking_building_id','adress_disp');	?>
		</div>	
		<div class="filter-item"> 
		<? $this->filtr_select('Этаж','floor','floor');	?>
		</div>	
		
		<div class="filter-item"> 
		<? $this->filtr_select('Цена','price','price');	?>
		</div>
		 
		<div class="filter-item"> 
			<span class="input_title">Статус</span>
			<select name="status" class="input_edit" style="text-transform:none; height:auto;">
				<option value="0" selected="selected">Не задан </option>
				<option value="2">Свободна </option>
				<option value="3">Продана </option>
				<option value="4">Забронирована </option>
				<option value="5">Забронирована застройщиком </option>
				<option value="6">Парковка подрядчика </option>
			</select>
		</div> 

		<div class="filter-item filter-item-checkbox"> 
			<input type="checkbox"    id="show_dell" name="show_dell" value="1" <?=$this->get_form_check($this->formid('indexsearch'),'show_dell','request')?>> <label for="show_dell">Удаленные</label><br/>
		</div>
		<?
	}
	
	
	
	
	
	function act__index()
	{
		
		global $t;
		$t['h1'] = 'Парковочные места';
	 
 
		$this->display_ajax_crud();
 
	}
	
	########################################################################
	  
	
	 
	
	
	
	
	
}

