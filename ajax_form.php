<?

 
		
		
  print_r($_POST);

 

		
		
		
/* получатели */ 
	$to= "89236470002@mail.ru"; //обратите внимание на запятую info@g-lounge.ru,
 
	$text='';

	if($_POST['name']){$text.='<b>Имя:</b> '.$_POST['name']."<br/>";}
	if($_POST['phone']){$text.='<b>Телефон:</b> '.$_POST['phone']."<br/>";}
	if($_POST['email']){ $text.='<b>E-Mail:</b> '.$_POST['email']."<br/>"; }
 	if($_POST['org']){$text.='<b>Организация:</b> '.$_POST['text']."<br/>";}
	if($_POST['message']){$text.='<b>Комментарий:</b> '.$_POST['message']."<br/>";}
	
/* тема/subject */

  
 
 if($_POST['name'] && $_POST['phone'] && $_POST['email'])
 { 
 
	  $from    = 'info@m2profi.pro';
	  $to      = '89236470002@mail.ru';
	  $subject = "Заявка с сайта - m2profi.pro";
	  
	  $message = $text;
	  $headers = "From: $from\r\n";
	  $headers .= "MIME-Version: 1.0\r\n";
	  $headers .= "Content-Type: text/html; charset=\"utf-8\"\r\n"."Content-Transfer-Encoding: 8bit\r\n";
	  mail($to, "=?utf-8?B?".base64_encode($subject)."?=", $message, $headers, "-f ".$from);
	  
	  
	  
	  
	  
	  $from    = 'info@m2profi.pro';
	  $to      = 'rv516@mail.ru';
	  $subject = "Заявка с сайта - m2profi.pro";
	  
	  $message = $text;
	  $headers = "From: $from\r\n";
	  $headers .= "MIME-Version: 1.0\r\n";
	  $headers .= "Content-Type: text/html; charset=\"utf-8\"\r\n"."Content-Transfer-Encoding: 8bit\r\n";
	  mail($to, "=?utf-8?B?".base64_encode($subject)."?=", $message, $headers, "-f ".$from);
	   
	  
	  
	  // Обратное писмо!
	  $from    = 'info@m2profi.pro';
	  $to      = trim($_POST['email']);
	  $subject = "Ваша заявка на сайте m2profi.pro";
	  
	  $message = '<h2>Здравствуйте</h2>
	  <p>Вы оставили заявку на сайте <a href="https://m2profi.pro">m2profi.pro</a> совсем скоро с вами свяжутся наши менеджеры.</p>
	  ';
	  
	  $headers = "From: $from\r\n";
	  $headers .= "MIME-Version: 1.0\r\n";
	  $headers .= "Content-Type: text/html; charset=\"utf-8\"\r\n"."Content-Transfer-Encoding: 8bit\r\n";
	  mail($to, "=?utf-8?B?".base64_encode($subject)."?=", $message, $headers, "-f ".$from);
	  
	  
	  
	  
	  
	  
  
	#### Пишем лог заявок
	$data_log = array();
	
	// ДОбавить элемент в начало массива
	$data_log[] =  date( 'd.m.Y - h:m:s' );
	$data_log[] =  $to;
	
	foreach($_POST as $k=>$v)
	{
		$data_log[] = $v;
	}
 
	// Склеивае  троку через разделитель
	$data_str = implode(' ; ', $data_log);
	// Пишем строку в конец файла с логом 
	file_put_contents('orderlog.csv', $data_str."\r\n", FILE_APPEND | LOCK_EX);
	
	
	
	
 
	#### Пишем лог заявок
	$data_log = array();
	
	// ДОбавить элемент в начало массива
	$data_log['datetime'] =  date( 'd.m.Y - h:m:s' );
	$data_log['timestamp'] =  time();
	
	foreach($_POST as $k=>$v)
	{
		$data_log[$k] = $v;
	}
 
	// Склеивае  троку через разделитель
	$data_str = base64_encode(serialize($data_log));
	// Пишем строку в конец файла с логом 
	file_put_contents('orderlogx.csv', $data_str."\r\n", FILE_APPEND | LOCK_EX);
 




 
 }
 



 

 