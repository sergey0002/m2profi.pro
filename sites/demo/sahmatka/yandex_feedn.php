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
	
 
$hn[5]='Жилой дом Приозерный №1 (по генплану)';
$hn[8]='Жилой дом Приозерный №2 (по генплану)';
$hn[3]='Жилой дом №451 (по генплану)';
$hn[7]='Жилой дом №452 (по генплану)';
$hn[6]='Жилой дом №453 (по генплану)';

$hn[9]='Жилой дом ул. Тюленина №1 (по генплану)';
$hn[10]='Жилой дом ул. Тюленина №2 (по генплану)';
$hn[12]='Жилой дом 603 (по генплану)';
$hn[15]='Жилой дом 601 (по генплану)';



$adress[5]='ул. Краузе, 14 стр';
$adress[8]='ул. Краузе, 14 стр';
$adress[3]='ул. Гребенщикова, 451 стр 1';
$adress[7]='ул. Гребенщикова, 451 стр 2';
$adress[6]='ул. Гребенщикова, 451 стр 3';


$adress[9]='ул. Тюленина №1';
$adress[10]='ул. Тюленина №2';

$adress[12]='ул. Мясникова,603 стр.';
$adress[15]='ул. Мясникова,601 стр.';



// стадия строительства «built» (дом построен, но не сдан)  «hand-over» (сдан в эксплуатацию)  «unfinished» (строится)
$building_state[5]='unfinished';
$building_state[8]='unfinished';
$building_state[3]='unfinished';
$building_state[7]='unfinished';
$building_state[6]='unfinished';

$building_state[9]='unfinished';
$building_state[10]='unfinished';


$building_state[12]='unfinished';
$building_state[15]='unfinished';

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

// Квартал сдачи «1», «2», «3», «4».
$ready_quarter[5]='4';

$ready_quarter[8]='4';
$ready_quarter[3]='2';
$ready_quarter[7]='3';
$ready_quarter[6]='2';
$ready_quarter[9]='4';
$ready_quarter[10]='4';

$ready_quarter[12]='4';
$ready_quarter[15]='4';

// Материал стен  «кирпичный»  «монолит» «панельный».
$building_type[5]='панельный';
$building_type[8]='монолит';
$building_type[3]='панельный';
$building_type[7]='панельный';
$building_type[6]='кирпичный';

$building_type[9]='панельный';
$building_type[10]='панельный';

$building_type[12]='панельный';
$building_type[15]='панельный';

// Этажность
$floors_total[5]='17';
$floors_total[8]='19';
$floors_total[3]='19';
$floors_total[7]='17';
$floors_total[6]='19';
$floors_total[9]='17';
$floors_total[10]='17';

$floors_total[12]='17';
$floors_total[15]='17';

$date=date("Y-m-d"); //YYYY-MM-DDTHH:mm:ss+00:00. YYYY-MM-DDTHH:mm:ss+00:00.
$date.='T';
$date.=date("H:i:s+06:00");

// Код жк
$jk_code[5]='1628478';
$jk_code[8]='1628478';
$jk_code[3]='496855';
$jk_code[7]='496855';
$jk_code[6]='496855';

$jk_code[9]='496855';
$jk_code[10]='496855';

$jk_code[12]='496855';
$jk_code[15]='496855';

// Код Корпуса
$corpus_code[5]='1628587';
$corpus_code[8]='1628609';
$corpus_code[3]='1583094';
$corpus_code[7]='1583102';
$corpus_code[6]='1583112';

$corpus_code[9]='1583112';
$corpus_code[10]='1583112';


$building_phase[5] = "очередь 1";
$building_phase[8] = "очередь 1";
$building_phase[3] = "очередь 1";
$building_phase[7] = "очередь 1";
$building_phase[6] = "очередь 1";
$building_phase[9] = "очередь 1";
$building_phase[10] = "очередь 1";

print '<?xml version="1.0" encoding="UTF-8"?>';
 ?>
  
<realty-feed xmlns="http://webmaster.yandex.ru/schemas/feed/realty/2010-06">
<generation-date><?=$date?></generation-date>   


<?
while ($result = mysqli_fetch_array($query)) 
{	


	if( ($result[home_id]==8   || $result[home_id]==3 || $result[home_id]==7 || $result[home_id]==6  || $result[home_id]==9  || $result[home_id]==10) && $result[rooms] && $result[image_pb] )
	{
	/*
Новосибирская область	микрорайон «Приозерный»	1628478	1 очередь	IV кв. 2018	адрес не задан	1628587	https://realty.yandex.ru/newbuilding/1628478	
Новосибирская область	микрорайон «Приозерный»	1628478	1 очередь	IV кв. 2018	Россия, Новосибирская область, Новосибирский район, микрорайон Приозёрный, Каспийская улица, 2	1628609	https://realty.yandex.ru/newbuilding/1628478	
Новосибирская область	микрорайон «Приозерный»	1628478	2 очередь	II кв. 2019	Россия, Новосибирск, улица Краузе, с14	1628628	https://realty.yandex.ru/newbuilding/1628478

yandex bilding 1i 
1628478	


496794 - девелопер ид
	*/	
		
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
  
 

	 

