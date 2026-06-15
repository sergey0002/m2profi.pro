<?php
header('Content-type: application/xml'); 
include('config.php');
 //floors_ready, building_phase, image, flats
$home_id = (int) $_GET['home_id'];
//if(!$home_id){die('not home id');}
  
# МАССИВ ИНФОРМАЦИИ О ДОМАХ
$data_homes = $mysql->get_arr('
SELECT `homes`.* , 
homes_kvartal.title as k_title  
FROM `homes` 
LEFT JOIN `homes_kvartal` ON `homes_kvartal`.`homes_kvartal_id` = `homes`.`kvartal`
 WHERE  `homes`.`show`="1"  ORDER BY `homes`.`home_id` '); // AND homes.`yandex-building-id` >0 AND `homes`.`yandex-house-id` >0 
foreach($data_homes as $k=>$v){	$homes[$v['home_id']]=$v; } //homes[home_id]=data
 

# Квартиры
$data_apartaments = $mysql->get_arr('SELECT apartaments.* , homes.title as hcaption , homes.built_year, homes.ready_quarter, homes.adress, `homes`.`yandex-house-id` , `homes`.`yandex-building-id` ,  `homes`.`complite`  FROM apartaments 
LEFT JOIN `homes` ON `homes`.`home_id` = `apartaments`.`home_id`
LEFT JOIN `homes_kvartal` ON `homes_kvartal`.`homes_kvartal_id` = `homes`.`kvartal`
WHERE (`apartaments`.`status2`="2" OR `apartaments`.`status2`="0") AND `homes`.`show` = "1" '); // AND homes.`yandex-building-id` >0 AND `homes`.`yandex-house-id` >0 
 
  
  
// Этажей
$data_floors = $mysql->get_arr('SELECT home_id, max(`floor`) as floor FROM apartaments GROUP by `apartaments`.`home_id` '); 
foreach($data_floors as $k=>$v)
{
	$floors[$v['home_id']] = $v;
} 
 
// Готовность
//
// Берем данные из базы приоритетно 
//if($data_home['built_year']){$built_year[$home_id]=$data_home['built_year'];}
//if($data_home['ready_quarter']){$ready_quarter[$home_id]=$data_home['ready_quarter'];}
 
//$built_year = $built_year[$home_id];
//$ready_quarter=$ready_quarter[$home_id];
 

//if(!$jk_img[$home_id]){	$jk_img[$home_id][]='https://xdemo.m2profi.pro/sahmatka/render/'.$home_id.'.jpg'; }
 
$datetime = new DateTime();
$date =  $datetime->format('c'); // '2010-10-05T16:36:00+04:00';

print '<?xml version="1.0" encoding="utf-8"?>';
 ?>
  
<realty-feed xmlns="http://webmaster.yandex.ru/schemas/feed/realty/2010-06">
<generation-date><?=$date?></generation-date>   

<?
$i=0;
foreach($data_apartaments as $k=>$result) 
{	 	
$i++;

 if($result['complite']){$building_state ='hand-over';}
 else{$building_state ='unfinished';}
 
  
 $result['rooms'] =  preg_replace('/[^0-9]/', '', $result['rooms']);
 
 
 
if(!$result['ready_quarter']){$result['ready_quarter'] = 1;}
if(!$result['built_year']){$result['built_year'] = 2028;}
 
?>
<offer internal-id="<?=$result[apartament_id]?>">
	<type>продажа</type>
	<property-type>жилая</property-type>
	<category>квартира</category>
	<creation-date><?=$date?></creation-date>
	<last-update-date><?=$date?></last-update-date>
	<location>
		<country>Россия</country>
		<locality-name>Новосибирск</locality-name>
		<address><?=$result['adress']?></address>
		<apartment><?=$result['apartment_num']?></apartment>  
	</location>
	<price>
		<value><?=$result['price']?></value>
		<currency>RUR</currency>
	</price>

	<sales-agent>
		<phone>+73833474700</phone>
		<organization>ООО "Энергомонтаж"</organization>
		<url>http://em-nsk.ru/</url>
		<category>developer</category>
		<photo>http://em-nsk.ru/ic/logo.png</photo>
	</sales-agent>

	<deal-status>первичная продажа</deal-status>

	<rooms><?=$result[rooms]?></rooms>
	<new-flat>1</new-flat>

	<floor><?=$result['floor']?></floor>
	<floors-total><?=$floors[$result['home_id']]['floor']?></floors-total>
	<building-name><?=$result['hcaption']?></building-name>

	<yandex-building-id><?=$result['yandex-building-id']?></yandex-building-id>
	<yandex-house-id><?=$result['yandex-house-id']?></yandex-house-id>

	<building-section><?=$result['section_id']?></building-section>
	<building-state><?=$building_state?></building-state>

	<ready-quarter><?=$result['ready_quarter']?></ready-quarter>
	<built-year><?=$result['built_year']?></built-year>
	<building-phase>1</building-phase>


	<image tag="plan"><?=$result[image_pb]?></image>
	<description>Продается <?=$result[rooms]?> к. <?= unit_abbrev() ?>, <?=$result[floor]?> этаж.</description>
	<area>
		<value><?=$result[area]?></value>
		<unit>кв. м</unit>
	</area>
</offer>
<?
	 
}
?>


</realty-feed>
  
<?
#print $i;
?>