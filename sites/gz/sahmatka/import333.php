<?
 // header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
 // header("Last-Modified: " . gmdate("D, d M Y H:i:s")." GMT");
//  header("Cache-Control: no-cache, must-revalidate");
 // header("Cache-Control: post-check=0,pre-check=0", false);
 // header("Cache-Control: max-age=0", false);
 // header("Pragma: no-cache");
 
include('config.php');
$array = file('4.txt');
$i=0;

foreach($array as $k=>$v )
{
	$str_arr = explode('	' ,$v);
	
	print_r($str_arr);
	// Предварительная обработка значений
	foreach($str_arr as $k2=>$v2)
	{
		$v2=str_replace('"','',$v2);
		$v2=str_replace('	','',$v2);
		$v2 = preg_replace( "/(^\s+)|(\s+$)/us", "", $v2 );
		$str_arr[$k2]=$v2;
	}
	
$str_arr3['home_id'] = 43;
$str_arr3['section_id'] = $str_arr[2];
$str_arr3['apartment_num']= (int) $str_arr[3];
$str_arr3['floor']= $str_arr[0];
$str_arr3['price']= $str_arr[6]; 
$str_arr3['area']= $str_arr[4];
$str_arr3['rooms']= $str_arr[1];
$a=str_replace(',','.',$str_arr3['area']);




if($str_arr[0]==1)
{
	$floor_imgdir='1';
}
elseif($str_arr[0]==2)
{
	$floor_imgdir='2';
}
elseif($str_arr[0]>2)
{
	if($str_arr3['section_id']==1)
	{
		$floor_imgdir='3-17';
	}
	elseif($str_arr3['section_id']==2 || $str_arr3['section_id']==3 )
	{
		$floor_imgdir='3-10';
	}
}
if($str_arr[0]>10)
{
	if($str_arr3['section_id']==2 || $str_arr3['section_id']==3 )
	{
		$floor_imgdir='11-17';
	}
}
else
{ 
	$floor_imgdir = $str_arr[0]; 
}

$a = str_ireplace('.','.',$a);




$str_arr3['image_pb'] = 'https://' . $GLOBALS['config']['domain'] . '/'sahmatka/pbplans/'.$str_arr3['home_id'].'/'.$str_arr[2].'/'.$floor_imgdir.'/'.$a.'.svg';
	
//	/home/m2profi/web/m2profi.pro/public_html/sites/em/sahmatka/pbplans/34
	//	https://em.m2profi.pro/sahmatka/pbplans/34/3/
	
	 #3 для ооо
	
   $sql = 'INSERT INTO `apartaments` (`home_id`, `section_id`, `apartment_num`, `floor`, `price`, `area`, `rooms`, `kitchen_area`, `text`, `house_adress`, `adress`, `image_pb`)
VALUES (
"'.$str_arr3['home_id'].'",
"'.$str_arr3['section_id'].'", 
"'.$str_arr3['apartment_num'].'", 
"'.preg_replace('~\D+~','', $str_arr3['floor']).'",
"'.$str_arr3['price'].'", 
"'.$str_arr3['area'].'",
"'.$str_arr3['rooms'].'",
"0",
"'.$str_arr3['text'].'",
"'.$str_arr3['house_adress'].'",
"'.$str_arr3['adress'].'",
 "'.$str_arr3['image_pb'].'"); ';
 
 // $mysql->sql( $sql);
 /*
 // ОБновление клонирование строки
 INSERT INTO `apartaments` (`home_id`, `section_id`, `apartment_num`, `apartments`, `floor`, `price`, `price_m`, `area`, `rooms`, `kitchen_area`, `text`, `house_adress`, `adress`, `plan_code`, `status`, `status2`, `status_broni_id`, `status_broni_date`, `date`, `image_pb`, `plan_type`, `image`, `area2`, `area_t`)
SELECT '34', '2', '110', '0', '1', '5200000', NULL, '64.60', '3с', '0', '', '', '', NULL, '3', '3', '30086', '2021-10-21 10:01:25', NULL, 'https://' . $GLOBALS['config']['domain'] . '/'sahmatka/pbplans/32/2/64.6.svg', NULL, NULL, NULL, NULL
FROM `apartaments`
WHERE ((`apartament_id` = '20870'));
(0.015 s)
 */
  print $sql;
  
 print '<br><br>';
 
 /*
 print  $sql ="
UPDATE `apartaments` SET
 
`price` = '".$str_arr3['price']."',
`section_id` = '".$str_arr3['section_id']."',
 

 `image_pb` = '".$str_arr3['image_pb']."',
`plan_code` = '".$str_arr3['area']."'
WHERE `home_id` = '".$str_arr3['home_id']."' AND apartment_num='".$str_arr3['apartment_num']."'
 ;";
 
*/

 
/*

1 Секция нет в нарезке 3к квартир 64,70 метров (4-17 этаж)
*/


 //UPDATE apartaments SET image_pb = REPLACE(image_pb, 'https://em-nsk.ru/sahmatka/pbplans/', 'https://' . $GLOBALS['config']['domain'] . '/'sahmatka/pbplans/') where apartaments.home_id=33

// ДОбавляем запись!
// $query = mysqli_query($connection, $sql); 

  //$mysql->sql( 'DELETE FROM `apartaments` WHERE `home_id` = "'.$str_arr3['home_id'].'";');
  //DELETE FROM `apartaments` WHERE ((`home_id` = '34'));
  
  

 

	$str_arr=$str_arr2;
	
	print '<pre>';
	print_r($str_arr3);
	print '<pre>';
	 
	
	
	
	if($i>10000){break;}
	$i++;
}
 



//print '<h1>M</h1>';
//print $xx;
 