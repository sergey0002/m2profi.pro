<?
 $sa = new sahmatka( $_SESSION , $connection );
 $h = $sa->get_home_arr( $_GET['home'] );
 
 
 # Массив домов (Из базы)
 $h_arr = $sa->get_homes_arr();
	
	
// Отдел продаж видит статус 3 
if(  $_SESSION['agency_id'] == "92" )
{
  // ТОлько для ОП
  if(!$h){ $h = $sa->get_home_arr( $_GET['home'] ,3);} 
}
 
 
 //print_r($h);
 if(!$h){die('Ошибка');}
 ?>  
 
 						
<style>
.slick-initialized .slick-slide {
    display: inline-block;
}
.slick-slide {float:none;}
</style>
				
				
				
  <section class="section-object">
	<div class="container mobc">
			<div class="page-header" style="margin-bottom:0;">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title">Апартаменты</div>
		</div>
<?
		object_menu();		
?>
 
		<div class="objects">
			<div class="objects-head" style="display:none;">
				<div class="objects-head-top">
					<div class="objects-head-top__title"><?=$h['title']?></div>
					<div class="objects-head-nav">
					<form id="obj_nav_form" method="GET" action="user.php">
						<div class="objects-head-nav__select" style="float: left;">
						<input type="hidden" name="action" value="<?=$_GET['action']?>" />
							<select  name="home">
							<?
								?><option>Выбрать дом</option><?
							foreach($h_arr as $k=>$v)
							{
								?><option value="<?=$v['home_id']?>" <? if($v['home_id']==$_GET['home']){ /* print 'selected="selected" '; */} ?>><?=$v['long_title']?></option><?
							}
							?>
							<option value="sdan">Сданные объекты</option>
							</select>
							
						</div>
						<a href="#" class="objects-head-nav__btn btn btn_arrow" onclick="document.getElementById('obj_nav_form').submit(); return false;" style="margin-left:10px;">К объекту<i></i></a>
						</form>
						
					</div>
				</div>
				<div class="objects-head-row">
					<div class="objects-head-pict">
						<img src="render/<?=$_GET['home']?>.jpg" alt="">
						<div class="objects-head-pict-info">
							<div class="objects-head-pict__location"><?=$h['title']?></div>
							<div class="objects-head-pict__status objects-head-pict__status_sale"><?=$h['status_text']?></div>
						</div>
					</div>
					<div class="objects-head-main">
						<div class="objects-head-main__text">
						<?=$h['description']?>
						</div>
						<div class="objects-head-main__text objects-head-main__text_info">
							 <?=$h['actions_text']?>
						</div>
					</div>
					<div class="objects-head-status">
						<div class="objects-head-status__title">Статусы <br><?= unit_label('pl_gen') ?></div>
						<ul class="objects-head-status-list">
							<li class="objects-head-status__green">Свободна</li>
							<li class="objects-head-status__yellow">Забронирована</li>
							<li class="objects-head-status__red">Продана</li>
					 
						</ul>
					</div>
				</div>
			</div>
 
			<div class="objects-price"  style="margin-top:0;">
				<div class="objects-cl-nav">
					<?
					// Меню подездов слайдера
					$sa->diaplay_home_secmenu( $homes , $_GET['home'] );
					?>
				</div>
				<div class="objects-cl">
						<?
						/*
						foreach($homes[$_GET['home']] as $k=>$v)
						{
							if(is_array($v) )
							{
							 $sa->diaplay_home( $homes , $_GET['home']  , $k , $data_broni,'' );
							}
						}
						
							*/
						
						
				if($_GET['home'])
				{
					$_GET['home'] = (int) $_GET['home'];
					$q= 'SELECT homes_sections.section_id FROM homes 
					LEFT JOIN homes_sections on homes_sections.homes_id = homes.homes_id 
					WHERE homes.home_id = "'.$_GET['home'].'" ';
					$arrs = $mysql->get_arr( $q );
				
					foreach($arrs as $k=>$v)
					{
						// Запрос по секциям дома делаем 
						$sa->disp_home( $_GET['home']  , $v['section_id']  ); 
					}
				}
				
			
				
						?>
				</div>
			</div>
		 
		 
		 	 <div>
		<ul class="objects-head-status-list" style="text-align: right; margin-bottom:30px; margin-top:30px;">
			<li class="objects-head-status__green" style="display:inline-block; margin-right: 20px;">Свободна</li>
			<li class="objects-head-status__yellow" style="display:inline-block; margin-right: 20px;">Забронирована</li>
			<li class="objects-head-status__red" style="display:inline-block; margin-right: 20px; ">Продана</li>
		 
			</ul>
	 </div>
	 
	 
			</div>
 
		</div>
	</div>
</section>123