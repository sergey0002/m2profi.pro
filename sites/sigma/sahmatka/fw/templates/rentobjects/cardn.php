 <?
 /*
<script src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&amp;apikey=9998badd-d4f7-462f-b4a5-9c3aa51768c0" type="text/javascript"></script>
<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
 
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
<!-- Link Swiper's CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
	 
<link rel="stylesheet" type="text/css" href="https://nsk.rent/css/style.css" />
	*/
	?>
	<?
	//print_r($data);
	
	?>
	
	<div  class="m_okno card_long" style="border:none;" data-id="<?=$data['rent_home_id']?>">

	<h2 style="margin-bottom: 30px;">
     <?=$data['h_adress']?> <?= $data['adress']?></h2>
  		
	<div class="row">
		<div class="col-lg-6">
 

			<div style="margin-bottom: 15px;">
				<div class="swiper mySwiper">
					<div class="swiper-wrapper">
		 
						
						 <div class="swiper-slide"  > <img src="<?=$data['plan']?>" alt="" class="rent_img" style="object-fit: contain;" /></div>
						  
						   <?
							$dir = $_SERVER['DOCUMENT_ROOT'].'/sites/em/sahmatka/rent/objects/'.$data['rent_objects_id'].'/';
							$files = scandir($dir);
							# print_R($files);
	 
					 
							
							foreach($files  as $file) 
							{
								if(is_file($dir.$file))
								{		
									$src=$dir.$file;
									if('jpg'==end(explode(".", $src)))
									{
										
										$src = str_replace('home/m2profi/web/m2profi.pro/public_html/sites/em/sahmatka','',$src);
										//https://xdemo.m2profi.pro/thumbs/?name=sahmatka/logo90.jpeg
										?>  
										<div  class="swiper-slide" ><img class="rent_img"  src="https://xdemo.m2profi.pro/thumbs/?name=/sahmatka/<?=$src;?>&w=800&h=800" style="object-fit: contain;" /> </div>
										<? 
									}
									elseif(1==2)
									{
										?>  <div class="swiper-slide"><img class="rent_img"  src="https://xdemo.m2profi.pro/thumbs/?name=/sahmatka/<?=$src;?>" style="object-fit: contain;" /></div><? 
									}							
								} 
							}
						  ?>
 
					</div>
					<div class="swiper-button-next"></div>
					<div class="swiper-button-prev"></div>
					<div class="swiper-pagination" StYLE="background: #fff;    PADDING: 5PX;    MARGIN-BOTTOM: -2PX;"></div>
	 
				</div>
			</div>
 
		</div>
		<div class="col-lg-6">
			<h4 class="flex_space-between c_blue">
        Технические характеристики <a href="javascript:(print());"> <img src="https://nsk.rent/img/print.svg" alt=""></a> </h4>
			<p>
			
			<?
			// Вывести все доступные параметры 
			//print_r($data);
			?>
			
			<?=$data['comment']?>			
			<b>
			<?=$data['area']?>м<sup>2</sup><br/>
			<?=$data['params']?>
			</b>
				<p>
				
				<?=$data['h_build_type']?><br/>
				<?=$data['h_adress']?> <?= $data['adress']?>
			</p>
			<button class="btn_gray_map showmap"  data-text-show="Помещение на карте" data-text-hide="СКРЫТЬ КАРТУ"  style="padding: 1em 0; font-weight: 400;">
			<img src="https://nsk.rent/img/gray_znak.svg" alt="">Помещение на карте</button>
			
			
			
	 
			<a data-wrapper="#ajax-wrapper" class="ajfb" data-url="https://xdemo.m2profi.pro/sahmatka/ajax_router.php?ctr=rentobjects&act=cardn_form&id=<?=$data['rent_objects_id']?>"  href="https://xdemo.m2profi.pro/sahmatka/ajax_router.php?ctr=rentobjects&act=cardn_form&id=<?=$data['rent_objects_id']?>"  >
			<button class="btn_blue">ЗАБРОНИРОВАТЬ</button>
			</a>
		</div>
	</div>

	<div class="btn_gray_map m20">
	
	<a class="showmap" href="#"    data-text-show="<?=$data['h_adress']?> / <?= $data['adress']?>"  data-text-hide="Скрыть карту" ><img src="https://nsk.rent/img/gray_znak.svg" alt=""> <?=$data['h_adress']?> / <?= $data['adress']?></a>
 
 
 
	</div>


	<div class="formap" style="display: none;"></div>

  
	</div>
</div>




<?

/*
<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script src="https://nsk.rent/js/scripts.js"></script>
<script type="text/javascript" src="https://nsk.rent/js/script.js"></script>
 
*/
 
?>


