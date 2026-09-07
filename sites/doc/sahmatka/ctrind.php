<? 
include('config.php');
include('incudes_/header.php');

# ПОлучаем результат работы контроллера--------
# ctr/act без GET берутся из $r->default_controller / default_action (config.php)
ob_start();
if( $_SESSION['sh_login'] )
{ 
	include('router.php'); 
}
$xxx = ob_get_clean();
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