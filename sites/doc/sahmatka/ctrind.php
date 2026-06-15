<? 
include('config.php');
include('incudes_/header.php');
 
 // Убрано принудительное назначение ctr и act
 // Теперь роутер сам определяет контроллер и экшен по умолчанию
 // из настроек $r->default_controller и $r->default_action в config.php
 
 
 
# ПОлучаем результат работы контроллера--------
ob_start();
if( $_SESSION['sh_login'] )
{ 
	include('router.php'); 
}
else
{
	// Отладка: если сессия не установлена
	if(isset($_GET['dev']))
	{
		print '<h2>DEBUG: Сессия не установлена</h2>';
		print '<pre>'; print_r($_SESSION); print '</pre>';
	}
}
$xxx = ob_get_clean();

// Отладка: проверяем, что вернул роутер
if(isset($_GET['dev']) && empty($xxx))
{
	$xxx = '<h2>DEBUG: Роутер вернул пустой результат</h2>';
	$xxx .= '<p>Контроллер: ' . ($r->default_controller ?? 'не задан') . '</p>';
	$xxx .= '<p>Экшен: ' . ($r->default_action ?? 'не задан') . '</p>';
	$xxx .= '<p>$_GET[ctr]: ' . ($_GET['ctr'] ?? 'не задан') . '</p>';
	$xxx .= '<p>$_GET[act]: ' . ($_GET['act'] ?? 'не задан') . '</p>';
}
#-----------------------------------------------
?>

<div class="container-fluid" >
	<div class="row">
		<div class="col-md-12"> 
			<section class="section-objects">
				<div class="container mobc">
					<div class="page-header" style="margin-bottom:0;">
						<div class="page-header__logo"><img src="/sahmatka/template/default/images/logo.svg" alt="" /></div>
						<div class="page-header__title"><?=$t['h1']?></div>
					</div>
					<div> 
					<?
					#### ИНТЕРФЕЙС АДМИНИСТРАТОРА
					print $xxx;
					?>
					</div>
				</div>
			</section>		
		</div>
	</div>
</div>


<?
include('incudes_/foother.php');
?>