<?
 // header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
 // header("Last-Modified: " . gmdate("D, d M Y H:i:s")." GMT");
//  header("Cache-Control: no-cache, must-revalidate");
 // header("Cache-Control: post-check=0,pre-check=0", false);
 // header("Cache-Control: max-age=0", false);
 // header("Pragma: no-cache");
 
include('config.php');
$array = file('5.txt');
$i=0;
 
foreach($array as $k=>$v )
{
	
	$area_id = '56';
	$str_arr = explode('/' ,$v);
	
	$buf = explode(' ',$str_arr[0]);
	$str_arr[0] = $buf[0];
	
	
	
	$str_arr[1] = preg_replace("/\([^)]+\)/","",$str_arr[1]); // 'ABC '
	$str_arr[1] = preg_replace('/[^0-9]/', '', $str_arr[1]);
	
	
	print '<pre>';
	print_r($str_arr);
	
	
	
	if($str_arr[1] && $str_arr[0])
	{
		$map_id = '52';
		print $sql=' UPDATE `landplots` SET `kadastrnum` = "'.$str_arr[0].'" WHERE `map_id` = "'.$map_id.'" AND `num` = "'.$str_arr[1].'" ';
		print '<br/>';
		//$mysql->sql( $sql);
	}
 
 print '</pre>';
	
	
	
	if($i>10000){break;}
	$i++;
}
 

