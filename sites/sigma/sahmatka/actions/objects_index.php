<?
// ВЫвод обектов если не выбран обьект

$sa = new sahmatka( $_SESSION , $connection );
$h = $sa->get_homes_arr();
// print_r($h);


	
?>
 
 
 
 <section class="section-objects">
	<div class="container mobc">
 
		
<style>
		@media (max-width: 767px) {.show-mobile{display:auto;} .hide-mobile{display:none;}  }
		@media (min-width: 768px) {.show-mobile{display:none;} .hide-mobile{display:auto;} }
</style>
		
 <div class="page-header" style="margin-bottom:0;">
			<div class="page-header__logo"><img src="template/default/images/logo.svg" alt="" /></div>
			<div class="page-header__title"><?= unit_phrase('page_title_pl_nom') ?></div>
			 
			<div style="width:100%; text-align:right; padding-top:30px; cursor:pointer;" class="open_xxpanel hide-mobile">
				<div style=" display:inline-block; padding: 15px; border-radius:20px; background: #00CDAE; width:500px; max-width:100%;  ">
					<div style="display:table-cell; text-align:left; vertical-align: top;">
						<span style="text-transform: uppercase; color: #FFF;  font-weight: bold; font-size: 12px; line-height: 2em;">Новое</span><br/>
						<span style="  color:#2F4049; font-weight: bold; font-size: 14px;">Добавьте иконку в телефон</span>
					</div>
					<div style="display:table-cell; text-align:left; vertical-align: baseline;">
						<span style="font-size:14px;">И заходите в М2 PROFI как в приложение.</span>
					</div>
					<div style="display:table-cell; ">
					<img src="/l.png" />
					</div>
				</div>
			</div>
			 
		</div>
		
		
		
		<div style="width:100%;  padding-top:30px; padding-bottom:30px; cursor:pointer;" class="open_xxpanel show-mobile">
				<div style=" display:inline-block; padding: 15px; border-radius:20px; background: #00CDAE; width:500px; max-width:100%;  ">
					<div style="display:table-cell; text-align:left; vertical-align: top; width: 100%;">
						<span style="text-transform: uppercase; color: #FFF;  font-weight: bold; font-size: 12px; line-height: 2em;">Новое</span><br/>
						<span style="  color:#2F4049; font-weight: bold; font-size: 14px;">Добавьте иконку <br/>в телефон</span><br/><br/>
						<span style="font-size:14px;">И заходите в М2 PROFI<br/> как в приложение.</span>
					</div>
					<div style="display:table-cell; text-align:right; ">
					<img src="/l2.png" width="100" />
					</div>
				</div>
			</div>
			
			
<?
		object_menu();		
?>

 
			
			
			
			
		<div class="objects">
			<div class="row">
				<?
				foreach($h as $k=>$v)
				{
					
					if(isset($_GET['sdan']))
					{
						if($_GET['sdan']){if($v['complite']=="0"){continue;}}
						else{if($v['complite']=="1"){continue;}}
					}
				?>
				
				<div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
					<div class="object">
						<div class="object__title"><?=$v[long_title]?></div>
						<div class="object__pict">
							<img src="render/<?=$v[home_id]?>.jpg" alt="">
							<div class="object__info">
							<?
							// Не выводим адрес!
							if($v[adress333])
							{
								?>
								<div class="object__location"><?=$v[adress]?></div>
								<?
							}
							?>
							<div class="object__status object__status_sale"><?=$v[complite_text]?></div>
							</div>
						</div>
						<a href="user.php?action=objects&home=<?=$v[home_id]?>&sdan=<?=$v['complite']?>" class="object__btn btn btn_arrow">К объекту<i></i></a>
					</div>
				</div>
					 <?
				}
				
				
				if($_GET['sdan'])
				{
					foreach($custom_apparts_all as $k=>$v)
					{
					?>
					<div class="col-sm-6 col-md-6 col-lg-4 col-xl-3">
						<div class="object">
						<div class="object__title"><?=$v['homecaption']?></div>
						<div class="object__pict" style="border: solid 1px #EEE; text-align:center;">
							<img src="<?=$v['image_pb']?>" alt="" style="width:auto; display:inline-block;">
							<div class="object__info">
								<div class="object__status object__status_sale">сдан</div>
							</div>
						</div>
						<a href="/sahmatka/form_order_custom.php?custom_home_id=<?=$v['home']?>&custom_appart_id=<?=$v['custom_appart_id']?>" class="iframe object__btn btn btn_arrow">К объекту<i></i></a>
						</div>
					</div>
					<?
					}
				}
					
					
					
				?> 

				
			</div>
		<a href="/sahmatka/yandex_feedx.php">XML Фид в формате Yandex</a>
		</div>
	</div>
</section>




		
				 