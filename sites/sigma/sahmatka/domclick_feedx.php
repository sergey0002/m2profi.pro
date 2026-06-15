<?php
header('Content-type: application/xml'); 
include('config.php');
 //floors_ready, building_phase, image, flats
$home_id = (int) $_GET['home_id'];
if(!$home_id){die('not home id');}
  
# ИНФОРМАЦИЯ О ДОМЕ
$data_home = $mysql->get_arr('
SELECT `homes`.* , 
homes_kvartal.title as k_title  

FROM `homes` 
LEFT JOIN `homes_kvartal` ON `homes_kvartal`.`homes_kvartal_id` = `homes`.`kvartal` WHERE  `homes`.`home_id` = "'.$home_id.'"',1); 
 

 


 


		
# ПРОВЕРКА
if(!$data_home['complex_domclick']){die('Error: Not complex_domclick');}
if(!$data_home['corpus_code_domclick']){die('Error: Not corpus_code_domclick');}
if(!$data_home['lat']){die('Error: Not map_mapkeys_lat');}
if(!$data_home['lon']){die('Error: Not map_mapkeys_lon');}
  
  
// не показывать дома инфинити и скрытые дома
if( $data_home['kvartal']==1 ||  $data_home['show']!=1 )
{
	//die();
}


# Квартиры
$data_apartaments = $mysql->get_arr('SELECT * FROM apartaments WHERE (`status`="2" OR `status`="0") AND home_id="'.$home_id.'" '); 

//print '<pre>';
//print_r($data_home);
//print '</pre>';

//print_r($data_apartaments);
 
 
// Этажей
$data_floors = $mysql->get_arr('SELECT max(`floor`) as floor FROM apartaments WHERE home_id="'.$home_id.'" ',1); 
$floors_total = $data_floors['floor'];

// Готовность
if($data_home['complite']){$building_state ='built';}
else{$building_state ='unfinished';}

 


 
// Год постройки
$built_year[5]='2018';
$built_year[8]='2019';
$built_year[3]='2019';
$built_year[7]='2019';
$built_year[6]='2019';
$built_year[9]='2019';
$built_year[10]='2019';
$built_year[12]='2020';
$built_year[15]='2020';
$built_year[16]='2021';
$built_year[20]='2022';
$built_year[21]='2022';
$built_year[22]='2022';
$built_year[24]='2023';
//???????????
$built_year[23]='2023';
$built_year[25]='2023';
$built_year[26]='2023';
$built_year[27]='2023';
$built_year[28]='2023';
$built_year[29]='2023';
$built_year[30]='2022';
$built_year[31]='2024';
$built_year[32]='2022';
$built_year[33]='2024';
$built_year[34]='2024';
$built_year[35]='2024';
$built_year[36]='2024';
$built_year[37]='2024';
 
$built_year[38]='2022';
$built_year[39]='2025';
$built_year[40]='2025';
$built_year[41]='2024';
 
 
 
$ready_quarter[8]='4';
$ready_quarter[3]='2';
$ready_quarter[7]='3';
$ready_quarter[6]='2';
$ready_quarter[9]='4';
$ready_quarter[10]='4';
$ready_quarter[12]='4';
$ready_quarter[15]='4';
$ready_quarter[16]='4';
$ready_quarter[20]='2';
$ready_quarter[21]='2';
$ready_quarter[22]='4';
$ready_quarter[24]='2';
$ready_quarter[23]='2';
$ready_quarter[25]='2';
$ready_quarter[26]='2';
$ready_quarter[27]='2';
$ready_quarter[28]='2';
$ready_quarter[29]='2';
$ready_quarter[30]='1';
$ready_quarter[31]='2';
$ready_quarter[32]='1';
$ready_quarter[33]='2';
$ready_quarter[34]='2';
$ready_quarter[35]='1';
$ready_quarter[36]='1';
$ready_quarter[37]='1';

$ready_quarter[38]='2';
$ready_quarter[39]='1';
$ready_quarter[40]='4';
$ready_quarter[41]='3';



// Берем данные из базы приоритетно 
if($data_home['built_year']){$built_year[$home_id]=$data_home['built_year'];}
if($data_home['ready_quarter']){$ready_quarter[$home_id]=$data_home['ready_quarter'];}
if($data_home['ready_quarter']){$ready_quarter[$home_id]=$data_home['ready_quarter'];}

$built_year = $built_year[$home_id];
$ready_quarter=$ready_quarter[$home_id];

// Заголовки описания
$jk_d_title[8]='ДОМА СЕРИИ LIFE';
$jk_d_title[7]='ДОМА СЕРИИ SMART';
$jk_d_title[6]='ДОМА СЕРИИ SMART';
$jk_d_title[9]='ДОМА СЕРИИ SMART';
$jk_d_title[10]='ДОМА СЕРИИ SMART';
$jk_d_title[12]='ДОМА СЕРИИ GREEN';
$jk_d_title[15]='ДОМА СЕРИИ GREEN';
$jk_d_title[16]='ДОМА СЕРИИ GREEN';
$jk_d_title[20]='НОВЫЙ КВАРТАЛ INFINITY';
$jk_d_title[21]='НОВЫЙ КВАРТАЛ INFINITY';
$jk_d_title[22]='ДОМА СЕРИИ RED';
$jk_d_title[24]='ДОМА СЕРИИ RED';
$jk_d_title[23]='ДОМА СЕРИИ INFINITY';
$jk_d_title[25]='ДОМА СЕРИИ INFINITY';
$jk_d_title[26]='ДОМА СЕРИИ INFINITY';
$jk_d_title[27]='ДОМА СЕРИИ INFINITY';
$jk_d_title[28]='ДОМА СЕРИИ INFINITY';
$jk_d_title[29]='ДОМА СЕРИИ INFINITY';
$jk_d_title[30]='ДОМА СЕРИИ INFINITY';
$jk_d_title[31]='ДОМА СЕРИИ INFINITY';
$jk_d_title[33]='ДОМА СЕРИИ LIFE';
$jk_d_title[35]='ДОМА СЕРИИ LIFE';
$jk_d_title[36]='ДОМА СЕРИИ LIFE';
$jk_d_title[37]='ДОМА СЕРИИ LIFE';
 
 
$jk_img[8][]='http://www.em-nsk.ru/new2/docs/pr2/render/033.jpg';
$jk_img[8][]='http://www.em-nsk.ru/new2/docs/pr2/render/032.jpg';
$jk_img[8][]='http://www.em-nsk.ru/new2/docs/pr2/render/011.jpg';
$jk_img[8][]='http://www.em-nsk.ru/new2/docs/pr2/render/019.jpg';
$jk_img[7][]='http://www.em-nsk.ru/new2/docs/t1/render/028--0000.jpg';
$jk_img[7][]='http://www.em-nsk.ru/new2/docs/t1/render/023--0000.jpg';
$jk_img[7][]='http://www.em-nsk.ru/new2/docs/t1/render/024--0000.jpg';
$jk_img[6][]='http://www.em-nsk.ru/new2/docs/t1/render/028--0000.jpg';
$jk_img[6][]='http://www.em-nsk.ru/new2/docs/t1/render/023--0000.jpg';
$jk_img[6][]='http://www.em-nsk.ru/new2/docs/t1/render/024--0000.jpg';
$jk_img[9][]='http://www.em-nsk.ru/new2/docs/t1/render/028--0000.jpg';
$jk_img[9][]='http://www.em-nsk.ru/new2/docs/t1/render/023--0000.jpg';
$jk_img[9][]='http://www.em-nsk.ru/new2/docs/t1/render/024--0000.jpg';
$jk_img[10][]='http://www.em-nsk.ru/new2/docs/t1/render/028--0000.jpg';
$jk_img[10][]='http://www.em-nsk.ru/new2/docs/t1/render/023--0000.jpg';
$jk_img[10][]='http://www.em-nsk.ru/new2/docs/t1/render/024--0000.jpg';
$jk_img[12][]='http://www.em-nsk.ru/new2/docs/601/render/014--0000.jpg';
$jk_img[12][]='http://www.em-nsk.ru/new2/docs/601/render/028--0000.jpg';
$jk_img[12][]='http://www.em-nsk.ru/new2/docs/601/render/023--0000.jpg';
$jk_img[15][]='http://www.em-nsk.ru/new2/docs/601/render/014--0000.jpg';
$jk_img[15][]='http://www.em-nsk.ru/new2/docs/601/render/028--0000.jpg';
$jk_img[15][]='http://www.em-nsk.ru/new2/docs/601/render/023--0000.jpg';
$jk_img[16][]='http://www.em-nsk.ru/sahmatka/render/16.jpg';
$jk_img[20][]='https://xdemo.m2profi.pro/sahmatka/render/20.jpg';
$jk_img[21][]='https://xdemo.m2profi.pro/sahmatka/render/21.jpg';
$jk_img[22][]='https://xdemo.m2profi.pro/sahmatka/render/22.jpg';
$jk_img[24][]='https://xdemo.m2profi.pro/sahmatka/render/24.jpg';
$jk_img[23][]='https://xdemo.m2profi.pro/sahmatka/render/23.jpg';
$jk_img[25][]='https://xdemo.m2profi.pro/sahmatka/render/25.jpg';
$jk_img[26][]='https://xdemo.m2profi.pro/sahmatka/render/26.jpg';
$jk_img[27][]='https://xdemo.m2profi.pro/sahmatka/render/27.jpg';
$jk_img[28][]='https://xdemo.m2profi.pro/sahmatka/render/28.jpg';
$jk_img[29][]='https://xdemo.m2profi.pro/sahmatka/render/29.jpg';
$jk_img[30][]='https://xdemo.m2profi.pro/sahmatka/render/30.jpg';
$jk_img[31][]='https://xdemo.m2profi.pro/sahmatka/render/31.jpg';
$jk_img[32][]='https://xdemo.m2profi.pro/sahmatka/render/32.jpg';
$jk_img[33][]='https://xdemo.m2profi.pro/sahmatka/render/33.jpg';
$jk_img[34][]='https://xdemo.m2profi.pro/sahmatka/render/34.jpg';
$jk_img[35][]='https://xdemo.m2profi.pro/sahmatka/render/35.jpg';
$jk_img[36][]='https://xdemo.m2profi.pro/sahmatka/render/36.jpg';
$jk_img[37][]='https://xdemo.m2profi.pro/sahmatka/render/37.jpg';
$jk_img[38][]='https://xdemo.m2profi.pro/sahmatka/render/38.jpg';
$jk_img[39][]='https://xdemo.m2profi.pro/sahmatka/render/39.jpg';
$jk_img[40][]='https://xdemo.m2profi.pro/sahmatka/render/40.jpg';
$jk_img[41][]='https://xdemo.m2profi.pro/sahmatka/render/41.jpg';

if(!$jk_img[$home_id]){	$jk_img[$home_id][]='https://xdemo.m2profi.pro/sahmatka/render/'.$home_id.'.jpg'; }



/*
$building_type[5]='панельный';
$building_type[8]='монолитный';
$building_type[3]='панельный';
$building_type[7]='панельный';
$building_type[6]='кирпичный';
*/
$building_type='панельный';





print '<?xml version="1.0" encoding="UTF-8" ?>';
?>
<complexes>   
	 <complex>
      <id><?=$data_home['complex_domclick']?></id>	  
<images>
<?
// Галлереи ЖК
foreach($jk_img[$home_id] as $ik => $iv )
{	
	if($iv)
	{
		?><image><?=$iv?></image><? 
	}
}
?>
</images>
<infrastructure>
	<playground>true</playground>
	<school>true</school>
</infrastructure>
<?
/*
<videos>
	<video>
	<type>youtube</type>
	<url>youtube.com/watch?v=CJRBbYuZyl4</url>
	</video>
	<video>
	<type>youtube</type>
	<url>youtube.com/watch?v=Hb7YH5F84V8</url>
	</video>
</videos>
*/
?>
<discounts>
  <discount>
  <name>Купи квартиру - оплатим проезд!</name>
  <description>
  Акция для иногородних покупателей.
  Условия распространяются на ограниченное количество квартир
  </description>
  <site>em-nsk.ru</site>
  </discount>
</discounts>
      <name><?=$data_home['long_title']?></name>
      <latitude><?=$data_home['lat']?></latitude>
      <longitude><?=$data_home['lon']?></longitude>
      <address><?=$data_home['map_mapkeys_adress']?></address>
   
      <description_main>
         <title><?=$data_home['long_title']?></title>
         <text><?=$data_home['description']?></text>
      </description_main>
 
      <profits_main>
         <profit_main>
            <title><?=$data_home['long_title']?></title>
            <text><?=$data_home['description']?></text>
		</profit_main>
      </profits_main>
 
	  <buildings>
		<building>
				<id><?=$data_home['corpus_code_domclick']?></id>
				<fz_214>true</fz_214>
				<name><?=$data_home['long_title']?></name>
				<floors><?=$floors_total?></floors>
				<building_state><?=$building_state?></building_state>
				<built_year><?=$built_year?></built_year>
				<ready_quarter><?=$ready_quarter?></ready_quarter>
				<building_type><?=$building_type?></building_type>
				
			
				<?
				 
					if(  is_array($data_apartaments  ) && $data_home['show'] ==1  ) // Тольуо если доступ =1 показываем квартиры
					{
						print '<flats>';
						foreach($data_apartaments as $flats_k=>$flats_v)
						{
							if(is_array($flats_v) && $flats_v['apartment_num'])
							{
							$flats_v['rooms'] = filter_var( $flats_v['rooms'], FILTER_SANITIZE_NUMBER_INT ); 
							
							  if( $flats_v['image_pb_png'] ){ $flats_v['image_pb'] = $flats_v['image_pb_png']; }
							?>
							<flat>  
							  <flat_id><?=@$flats_v['apartament_id']?></flat_id>
							  <apartment><?=@$flats_v['apartment_num']?></apartment>
							  <floor><?=@$flats_v['floor']?></floor>
							  <room><?=@$flats_v['rooms']?></room>
							  <plan><?=@$flats_v['image_pb']?></plan>
							  <balcony>Нет</balcony>
							  <renovation><?=$data_home['renovation']?></renovation><? /* да */?>
							  <price><?=@$flats_v[price]?></price>
							  <area><?=@$flats_v['area']?></area>
							  <decoration>1</decoration>
						 	  <ready_housing>0</ready_housing>
							  <kitchen_area>0</kitchen_area>
							  <living_area><?=@$flats_v['area']?></living_area>
							  <window_view>На улицу</window_view>
							  <bathroom>Раздельный</bathroom>
							</flat>
							<?
							}
						
						}
						print '</flats>';
					}
					else
					{
						print '<flats>';
						?>
						<flat>  </flat>
						<?
						print '</flats>';
					}
				?>
			  </building>
	   </buildings>
	   
	   <sales_info>
         <sales_phone>+73833474700</sales_phone>

         <responsible_officer_phone>+79059521067</responsible_officer_phone>
		 
         <sales_address>Новосибирск, ул. Тюленина, д. 26., 1 этаж, каб 102-103 </sales_address>
		 <sales_address>Новосибирск, ул. Гребенщикова д. 9.,</sales_address>
		 <sales_address>Новосибирск, ул. Мясниковой, д. 35.</sales_address>
		   
		   
         <sales_latitude />
         <sales_longitude />
         <timezone>+3</timezone>
         <work_days>
            <work_day>
               <day>пн</day>
               <open_at>09:00</open_at>
               <close_at>20:00</close_at>
            </work_day>
            <work_day>
               <day>вт</day>
               <open_at>09:00</open_at>
               <close_at>20:00</close_at>
            </work_day>
            <work_day>
               <day>ср</day>
               <open_at>09:00</open_at>
               <close_at>20:00</close_at>
            </work_day>
            <work_day>
               <day>чт</day>
               <open_at>09:00</open_at>
               <close_at>20:00</close_at>
            </work_day>
            <work_day>
               <day>пт</day>
               <open_at>09:00</open_at>
               <close_at>20:00</close_at>
            </work_day>
            <work_day>
               <day>сб</day>
               <open_at>09:00</open_at>
               <close_at>18:00</close_at>
            </work_day>
			
			<work_day>
               <day>вс</day>
               <open_at>09:00</open_at>
               <close_at>18:00</close_at>
            </work_day>
			
			
         </work_days>
      </sales_info>
      <developer>
         <id>244049</id>
         <name>ООО "Энергомонтаж"</name>
         <phone>+73833474700</phone>
         <site>http://em-nsk.ru/</site>
		 <logo>https://em-nsk.ru/logo/vert/logo.png</logo>
      </developer> 
	   
	  </complex>
</complexes>
 
