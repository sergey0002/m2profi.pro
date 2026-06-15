<?


header('Access-Control-Allow-Origin: *'); 
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s")." GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0,pre-check=0", false);
header("Cache-Control: max-age=0", false);
header("Pragma: no-cache");
 
include('config.php');
 
if(!$_GET['home'])
{
	// $_GET['home']=27;
}
  
$sa = new sahmatka( $_SESSION , $connection );
$h = $sa->get_home_arr( $_GET['home'] );

// if(!$h){$h = $sa->get_home_arr( $_GET['home'] ,2);} // Показывать дома доступные только для админа
   
 
 if(!$h){die();} // Скрываем дома которые не надо показывать
 
	// Новый вывод 				 
	if($_GET['home'])
	{
			$_GET['home'] = (int) $_GET['home'];
			$q= 'SELECT homes_sections.section_id FROM homes 
			LEFT JOIN homes_sections on homes_sections.homes_id = homes.homes_id 
			WHERE homes.home_id = "'.$_GET['home'].'" ';
			$arrs = $mysql->get_arr( $q );
		
			foreach($arrs as $k=>$v)
			{
				
				print ' <div class="col" style="flex-grow:0; ">';
				print '<table class="house-table">';
	 
				// Запрос по секциям дома делаем 
				$sa->disp_home_p( $_GET['home']  , $v['section_id']  ,1); 
				
				 print ' </table>';
				 print ' </div>';
			}
	}
 		
 