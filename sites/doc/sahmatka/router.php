<?
// Получаем контент действия --------- 
if (!$r->action_content())
{
	// Если роутер вернул false, выводим сообщение об ошибке
	print '<h2>Ошибка роутера</h2>';
	print '<p>Контроллер или экшен не найден.</p>';
	print '<p>Проверьте настройки default_controller и default_action в config.php</p>';
}

// print '<pre>';
// print_r($log);
// print '</pre>';
// print '</pre>';
