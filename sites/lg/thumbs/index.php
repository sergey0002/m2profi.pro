<?
error_reporting(0);
 /*
 1 htaccess 
 2 не увеличивать файлы
 3. Уменьшать все до указанныъ размеров
 4. 
 */
 
// print 123;
// exit();
 
 
include('resize_class.php');
// Вызов

$file = '../'.$_GET['name'];
$w = (int) $_GET['w'];
$h = (int) $_GET['h'];
  
if(file_exists($file))
{
	$puth = pathinfo($file);
	//  print '<pre>';
	//  print_r($puth);
	
	$md5 = filesize($file); // Соль от оригинального файла кеша
	$image_info = getimagesize($file); 
	
	 if(!$w){$w=800;}
	 if(!$h){$h=600;}
 
	# не больше оригинальных размеров
	
	 // print_r($image_info);
	 
	//формируем путь к файлу кеша
	$_tname_tpl         = '%s__%sx%s';
	$thumbFilename =__DIR__.'/cache/'; //.$puth['dirname'].'/'; ## СОЗДАВАТЬ ДИРЕКТОРИЮ КАК В КЛОНЕРЕ
    $thumbFilename .= sprintf($_tname_tpl, $puth['filename'], $w, $h).'_'.$md5. '.'.$puth['extension'];
	
	// Есть файл кеша для миниатюры - выводим его
	if(file_exists( $thumbFilename ))
	{
		 header('Content-type: ' . $image_info['mime']);
		 readfile($thumbFilename);  
	}
	else // Создаем миниатюру и выводим ее
	{
		#header('Content-type: ' . $image_info['mime']);
		$resizeObj = new resize( $file );
		$resizeObj -> resizeImage($w,$h);
		$resizeObj -> saveImage( $thumbFilename , 70 );
		readfile($thumbFilename);  
	}
}
  
 	
	   
 //print $file;	   
 //print $thumbFilename;
  
 
 
?>