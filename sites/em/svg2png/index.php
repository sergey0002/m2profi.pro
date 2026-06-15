<?
set_time_limit(0);
ignore_user_abort(true);

include('../sahmatka/config.php');


 function svg2png( $source_svgurl )
 {
 	 if(!$source_svgurl){die('Не указан файл');}
 
		$url =  parse_url($source_svgurl); 
		$path_parts = pathinfo($url['path']);
		 // print_r($path_parts);
		/*
		  [dirname] => /sahmatka/pbplans/34/1
		  [basename] => 82.1.svg
		  [extension] => svg
		  [filename] => 82.1
		*/
		// $root_dir = $_SERVER['DOCUMENT_ROOT'];

		$cache_dir =  'cache'.$path_parts['dirname'];
	 
		$cache_dir_arr = explode('/',$cache_dir);
	 
		// print_r( $cache_dir_arr);
		
		### СОЗДАЕМ ДИРРЕТОРИЮ ПО УКАЗАННОМУ ПУТИ
		$dir='';
		foreach($cache_dir_arr as $k=>$v)
		{
			$dir.=$v.'/';
			//print $dir;
			if(is_dir($dir))
			{
				// print '-ok';
			}
			else{
				if(mkdir($dir))
				{
					 //print '-created';
				}
			}
			//print '</br>';
		}
		######
			
	$buf_file = $cache_dir.'/'.$path_parts['basename']; // Временный файл для конвертации
	$new_file = $cache_dir.'/'.$path_parts['filename'].'.png'; // Путь к новому файлу png

	// Есть файл в кеше 
	if(file_exists($new_file))
	{
		print 'файл из кеша - ';
		return 'https://em.m2profi.pro/svg2png/'.$new_file;
	}
	// нет файла в кеше
	else
	{
		
		if(is_file($buf_file)){unlink($buf_file);}
		print $content = file_get_contents(urldecode($source_svgurl));
		print strlen($content);
	 
		file_put_contents($buf_file, $content);
		
	/*
		$ch = curl_init($source_svgurl);
		$fp = fopen( $buf_file, 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
	*/	
		if( 1==2 ) 
		{      
			//die('Не удалось загрузить файл');
			//curl_close($ch);
			//fclose($fp);
		}
		else
		{
		  
			print $cline = 'inkscape -z --export-png='.$new_file.' '.$buf_file;
		 
			print shell_exec($cline);
			
			// Есть файл   
			if( file_exists( $new_file ) )
			{
				if(is_file($buf_file)){unlink($buf_file);}
				return 'https://em.m2profi.pro/svg2png/'.$new_file;
				
			}
			// нет файла в кеше
			else
			{
				 
				print '<img src="'.$buf_file.'" width="100"/>';
				//die('error нет файла после обработки');
				print '<b>ОШИБКА нет файла после обработки</b>';
				//if(is_file($buf_file)){unlink($buf_file);}
				return '0';
				
			}
		}
		 
	}

 }
 
 
 
 $i=0;
$app = $mysql->get_arr('SELECT apartaments.image_pb,apartament_id FROM apartaments LEFT JOIN homes ON homes.home_id = apartaments.home_id WHERE homes.show="1"  AND homes.home_id="38"');
foreach($app as $k=>$v)
{
	$i++;
	print $i.' ';
	print $v['image_pb'];
	print '<br/>';
	
	print $result = svg2png( str_replace(' ','_',urldecode($v['image_pb'])) );

	if( $result )
	{			
		$data = array();
		$data['image_pb_png'] =$result;
		//$mysql -> update_for_key( 'apartaments' , 'apartament_id' , $v['apartament_id'] , $data );
	}
 	
	print '<hr/>';
}