<?php
include('config.php');
 


$sql ='SELECT * from apartaments ';


$sql ='
SELECT *  FROM `apartaments` 
  LEFT JOIN (
	 SELECT home_id as b_hid, apartments_num as b_anum, status as b_status FROM `broni`
         where date=(SELECT max(date) FROM broni as b WHERE broni.home_id = b.home_id AND `b`.`apartments_num`=`broni`.`apartments_num`)
 ) 
  as br on (br.b_hid=home_id  AND apartaments.apartment_num = b_anum)
WHERE image_pb !="" AND (b_status is null or b_status = 2) order by home_id
';


// print $sql;

$query = mysqli_query($connection, $sql); 
	
  
 

print '<?xml version="1.0" encoding="utf-8"?>';
 ?>
  
<realty-feed xmlns="http://webmaster.yandex.ru/schemas/feed/realty/2010-06">
<generation-date><?=$date?></generation-date>   


<?
while ($result = mysqli_fetch_array($query)) 
{	


	if( ($result[home_id]==8   || $result[home_id]==3 || $result[home_id]==7 || $result[home_id]==6  || $result[home_id]==9  || $result[home_id]==10) && $result[rooms] && $result[image_pb] )
	{		
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
   <address><?=$adress[$result[home_id]]?></address>
   <apartment><?=$result['apartment_num']?></apartment> 
</location>
<price>
  <value><?=$result[price]?></value>
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

 
<floor><?=$result[floor]?></floor>
<floors-total><?=$floors_total[$result[home_id]]?></floors-total>
<building-name><?=$hn[$result[home_id]]?></building-name>

<yandex-building-id><?=$jk_code[$result[home_id]]?></yandex-building-id>
<yandex-house-id><?=$corpus_code[$result[home_id]]?></yandex-house-id>

<building-section>Корпус 1</building-section>
<building-state><?=$building_state[$result[home_id]]?></building-state>
 

<ready-quarter><?=$ready_quarter[$result[home_id]]?></ready-quarter>
<built-year><?=$built_year[$result[home_id]]?></built-year>
<building-phase><?=$building_phase[$result[home_id]]?></building-phase>


<image><?=$result[image_pb]?></image>
<description>Продается <?=$result[rooms]?> к. кв., <?=$result[floor]?> этаж.</description>
<area>
  <value><?=$result[area]?></value> 
  <unit>кв. м</unit>
</area>
  

</offer>
<?
	}	
}
?>
 

</realty-feed>
  
 

	 

