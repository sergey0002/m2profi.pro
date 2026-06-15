<?
 $v = $data;
 
 // ТУТ ПОЛУЧИТЬ КАРТИНКИ ПОЭТАЖНЫХ ПЛАНОВ!
//print '<pre>';
//print_r($v);
//print '</pre>';

$plan_files = explode('|', $v['plan_files']);

// $v['parking_building_id']
?>


<div class="row card_long" data-id="<?=$v['parking_building_id']?>">
		<div class="col-lg-5">
			<div class="pl_red">ПОКУПКА</div>

			<div style="margin-bottom: 15px;">
				<div class="swiper mySwiper">
					<div class="swiper-wrapper">
 
						<div class="swiper-slide"> <img src="https://em.m2profi.pro/sahmatka/parking/drander.png" alt=""></div>
                        <div class="swiper-slide"> <img src="https://em.m2profi.pro/sahmatka/parking/drander.png" alt=""></div>
						
					</div>
					<div class="swiper-button-next"></div>
					<div class="swiper-button-prev"></div>
					<div class="swiper-pagination"></div>
				</div>
			</div>

		</div>
		<div class="col-lg-4">
			<h3><?=$v['adress_disp']?></h3>
			<br/>
			<p>Этажей <?=$v['floor_c']?> </p>
			<p> Всего мест <?=$v['space_c']?> </p>
	 
		</div>
		<div class="col-lg-3">
		
		
		 
		
		
			<a href="/parking/?street_pp=<?=$v['parking_building_id']?>"><button class="btn_red"  >ПОДРОБНЕЕ</button></a>
			<button class="btn_white showmap">НА КАРТЕ</button>
		</div>
		
		
		
		<div class="formap"></div>
	</div>
	<br> 