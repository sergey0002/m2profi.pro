<?php
include('config.php');
error_reporting(1);
 
 class fw_fileupload
 {
	 function skin__default__success($val)
	 {
		 return  '<p style="color: green">' . $val . '</p>';  
	 }
	 
	 function skin__default__error($val)
	 {
		  return  '<p style="color: red">' . $val . '</p>'; 
	 }
	 
	 function filename( $path , $name )
	 {
		if( file_exists( $path . $name) )
		{
			$path_parts = pathinfo($name);
			$path_name_parts = explode('.', $path_parts['filename']);
			 
			if( $path_name_parts )
			{
				$file_salt = trim( $path_name_parts[count($path_name_parts)] ); // Получаем последний фрагмент файла через точку
				unset($path_name_parts[count($path_name_parts)]);
				$path_parts['filename'] = implode('.',$path_name_parts);
			}
	 
			if(!is_numeric($file_salt))
			{
				$file_salt=1;
			}
			else
			{
				$file_salt = $file_salt+1;
			}
			
			$name = $path_parts['filename'].'.'.$file_salt.'.'.$path_parts['extension'];
			// unlink($path . $name);
		}
		if( file_exists( $path . $name) ) // рекурсивно дописываем к файлу
		{
			$name = $this->filename( $path , $name );
		}
		return $name;
	 }
	 
	 
	function upload()
	{
		global $mysql;
	// Название <input type="file">
	$input_name = 'file';
	 
	// Разрешенные расширения файлов.
	$allow = array('svg','jpg','jpeg','png','zip','rar','pdf','tar','docx','doc','xlsx','xls');
	 
	// Запрещенные расширения файлов.
	$deny = array(
		'phtml', 'php', 'php3', 'php4', 'php5', 'php6', 'php7', 'phps', 'cgi', 'pl', 'asp', 
		'aspx', 'shtml', 'shtm', 'htaccess', 'htpasswd', 'ini', 'log', 'sh', 'js', 'html', 
		'htm', 'css', 'sql', 'spl', 'scgi', 'fcgi', 'exe'
	);
	 
	 
	/*
	Добавлять тип файла в директорию путь
	 
	*/
	 
	 
	// Директория куда будут загружаться файлы.
	$path = $GLOBALS['config']['upload_dir'];
	
	$link_path = '/sahmatka/upload/'.$_POST['tdir'].'/';
	 
	$_POST['tdir'] = $_POST['tdir'];
	 
	if(!$_POST['tdir']){die('Ошибка директории');}
	 
	// Создаем временную директорию и перписываем в нее загрузку файлов
	mkdir( $path.'/'.$_POST['tdir'] );
	$path = $path.'/'.$_POST['tdir'].'/';
	 
	$data = array();
	 
	if (!isset($_FILES[$input_name])) {
		$error = 'Файлы не загружены.';
	}
	else 
	{
		// Преобразуем массив $_FILES в удобный вид для перебора в foreach.
		$files = array();
		$diff = count($_FILES[$input_name]) - count($_FILES[$input_name], COUNT_RECURSIVE);
		if ($diff == 0) 
		{
			$files = array($_FILES[$input_name]);
		} 
		else 
		{
			foreach($_FILES[$input_name] as $k => $l) 
			{
				foreach($l as $i => $v) 
				{
					$files[$i][$k] = $v;
				}
			}		
		}	
	 
		foreach ($files as $file) 
		{
			$error = $success = '';
	 
			// Проверим на ошибки загрузки.
			if ($file['error'] || empty($file['tmp_name'])) 
			{
			 
				$error = 'Не удалось загрузить файл 222.';
			} 
			elseif ($file['tmp_name'] == 'none' ) 
			{
				$error = 'Не удалось загрузить файл !!!.';
			} 
			else 
			{
				// Оставляем в имени файла только буквы, цифры и некоторые символы.
				$pattern = "[^a-zа-яё0-9,~!@#%^-_\$\?\(\)\{\}\[\]\.]";
				
				// имя файла из формы
				if($_POST['filename']){$file['name'] = $_POST['filename'];}
				$name = mb_strtolower($name);
				
				$name = mb_eregi_replace($pattern, '-', $file['name']);
				$name = mb_ereg_replace('[-]+', '-', $name);
				$parts = pathinfo($name);
				
				if (empty($name) || empty($parts['extension'])) 
				{
					$error = 'Недопустимый тип файла «' . $name . '»';
				} 
				elseif (!empty($allow) && !in_array(strtolower($parts['extension']), $allow)) 
				{
					$error = 'Недопустимый тип файла «' . $name . '»';
				} 
				elseif (!empty($deny) && in_array(strtolower($parts['extension']), $deny)) 
				{
					$error = 'Недопустимый тип файла «' . $name . '»';
				} 
				else 
				{
					// Перемещаем файл в директорию.
					// print $file['tmp_name'].'+'. $path .'+'. $name;
					if(file_exists($path . $name))
					{
						 $name = $this -> filename( $path , $name );
					}
					
					if( move_uploaded_file($file['tmp_name'], $path . $name) ) 
						{
						// Далее можно сохранить название файла в БД и т.п.
						$success = 'Файл «' . $name . ' успешно загружен.';
						
							// Не обязательно ID может не быть тогда обработка в форме!!!
							if($_POST['node_id'])
							{
								$node_files_data = array();
								$node_files_data['node_id'] = $_POST['node_id'];
								$node_files_data['name'] = $name;
								$node_files_data['ext'] = end(explode(".", $name));
								
								
								$file_ext = end(explode(".", $name));
								if($file_ext =='jpg' || $file_ext =='jpeg' || $file_ext =='png' || $file_ext =='svg' )
								{
									$file_icon = $link_path.$name;
								}
								else
								{
									$file_icon = '/sahmatka/template/file_icon.png';
								}
								$node_files_data['ext'] = $file_ext;
								$node_files_data['icon'] = $file_icon;
								$node_files_data['link'] = $link_path.$name;
								$node_files_data['puth'] = $path;
								$node_files_data['size'] = filesize($path.$name);
								$node_files_data['uptime'] = time();
								$node_files_data['node_name'] =$_POST['node_name'];  
								$node_files_data['user_id'] = $_SESSION['gl_user_id']; 
								$node_files_data['file_type'] = $_POST['file_type']; 
								
								
								$files2node_id = $mysql->insert('files2node',$node_files_data);
								
								if(!$files2node_id)
								{
									$error = 'Не удалось записать в базу файл «' . $name . '» "' .$file['tmp_name'].'" - "'. $path .'" - "'.$name.'"' ;
								}
							}
							else
							{
								// Не обязательно ID может не быть тогда обработка в форме!!!
							//	$error = 'Ошибка нет node_id';
							}
						} 
					else 
					{
						$error = 'Не удалось загрузить файл «' . $name . '»!!! '. $path .' - '. $name .'-'.$file['tmp_name'];
					}
				}
			}
			
			$file_type_display['11']='Договор М2';
			$file_type_display['12']='Акт М2';
			$file_type_display['21']='Договор Сталкер';
			$file_type_display['22']='Акт Сталкер';
			
			 
			if (!empty($success)) 
			{
				$ditem['message'] = $this->skin__default__success($success);
				$ditem['ext'] = $file_ext;
				$ditem['icon'] = $file_icon;
				$ditem['file'] = $name;
				$ditem['link'] = $link_path.$name;
				 $ditem['new'] = '<tr class="fiprew">
					<td width="200">'.date('d.m.Y H:m:s',$node_files_data['uptime']).'</td>
					<td width="200">'.$file_type_display[ $_POST['file_type'] ].'</td>
					<td width="200">'.$_SESSION['sh_login'].'</td>
					<td>
						<a href="'.$node_files_data['link'].'" target="_blank" style="display:block; width:100%; font-size: 15px;">
						<img src="'.$file_icon.'" width="20" /> 
						'.$name.'
						</a>
					</td></tr>';
				// $ditem['puth'] = $path . $name;
				$data[] = $ditem;
				
			}
			if (!empty($error)) 
			{
				$ditem['message'] = $this->skin__default__error($error);
				$ditem['error'] = 1;
				$data[] = $ditem;
			}
		}
	}
	
	return $data;
	 
	}
	
	
	
	
	
 }
 
 
 
// Вывод сообщений о результате загрузки.
header('Content-Type: application/json');

$x = new fw_fileupload();
$data = $x->upload();

echo json_encode($data, JSON_UNESCAPED_UNICODE);
exit();