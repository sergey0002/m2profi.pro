<? 
include('config.php');
include('incudes_/header.php');
 
 if(!$_GET['ctr'] )
 {
	 $_GET['ctr'] = 'index';
	 $_GET['act'] = 'index';
 }
 
 
 
# ПОлучаем результат работы контроллера--------
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