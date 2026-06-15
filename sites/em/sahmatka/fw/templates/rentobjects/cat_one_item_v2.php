 <?
 $v = $data;
 if(!$v['status']){$v['status']=2;}
if($v['show_b']){$show_b_panel = '<div class="rentobj_stp rentobj_stp1">с комиссией</div>';}
else{$show_b_panel = '<div class="rentobj_stp rentobj_stp2">без комиссии</div>';}
	
	
	
///print '<pre>';
 // print_r($v);
 //print '</pre>';
 ?><!-- Карточка объекта -->
 
 
 
 
 
 <div class="row card_long" data-id="<?=$v['rent_home_id']?>">
	<div class="col-lg-5">
		<div class="pl_blue">АРЕНДА</div>

		<div style="margin-bottom: 15px;">
			<div class="swiper mySwiper">
				<div class="swiper-wrapper">
					<div class="swiper-slide"> <img src="<?=$v['plan']?>" alt="" style="max-height:350px; width: auto;"></div>
  
  
  
  
  
  
		<?
	  	$dir = 'rent/objects/'.$v['rent_objects_id'].'/';
		$files = scandir($dir);
 
		// print_r($files);
 		foreach($files as $file) 
		{
			if(is_file($dir.$file))
			{		
				$src=$dir.$file;
				if('jpg'==end(explode(".", $src)))
				{
					//https://em.m2profi.pro/thumbs/?name=sahmatka/logo90.jpeg
					?><div class="swiper-slide"> <img src="https://em.m2profi.pro/thumbs/?name=/sahmatka/<?=$src;?>&w=800&h=800" alt="" style="max-height:350px; width: auto;"></div> <? 
				}
				else
				{
					?><div class="swiper-slide"> <img src="https://em.m2profi.pro/<?=$src;?>" alt="" style="max-height:350px; width: auto;"></div> <? 
		 
				}							
			} 
		}
	    ?>
	   
				</div>
				
				 
				<div class="swiper-button-next"></div>
				<div class="swiper-button-prev"></div>
				<div class="swiper-pagination"></div>
			</div>
		</div>
		
	</div>
	<div class="col-lg-4">
		<h3>  Помещение <?=$v['area']?> м²  </h3>
		 
		<h4><?=$v['h_adress']?></h4>
		<p>
			  
			 <?=$v['adress']?> 
			 
			 <?=$v['params']?>

		</p>
	</div>
	<div class="col-lg-3">
		<a data-wrapper="#ajax-wrapper" class="ajfb" data-url="https://em.m2profi.pro/sahmatka/ajax_router.php?ctr=rentobjects&act=cardn&id=<?=$v['rent_objects_id']?>"  >
		  
		<button class="btn_blue">ПОДРОБНЕЕ</button></a>
		 <button class="btn_white showmap">НА КАРТЕ</button> 
		<h3 style="text-align: center; back!ground:<?=$GLOBALS['broni_colors'][$v['status']]?>">
 
				 <?=$GLOBALS['broni_status'][$v['status']]?> 
				
				</h3>
	</div>
	<div class="formap"></div>
</div>
<br/><br/>
 
 
 
 
 
 
 
				
 
  