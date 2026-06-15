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
			?><div class="sch">
			
			
			<div class="sch-ftr" style="display:none;">
        <div class="sch-ftr__title">Показать квартиры:</div>
        <div class="sch-ftr-chks" style="display:flex;" >
          
          <label class="sch-ftr-chk">
            <input type="checkbox" name="1">
            <div class="sch-ftr-chk__check"></div>
            <div class="sch-ftr-chk__title">1K</div>
          </label>
          <label class="sch-ftr-chk">
            <input type="checkbox" name="2">
            <div class="sch-ftr-chk__check"></div>
            <div class="sch-ftr-chk__title">2K</div>
          </label>
          <label class="sch-ftr-chk">
            <input type="checkbox" name="3">
            <div class="sch-ftr-chk__check"></div>
            <div class="sch-ftr-chk__title">3K</div>
          </label>
          <label class="sch-ftr-chk">
            <input type="checkbox" name="4">
            <div class="sch-ftr-chk__check"></div>
            <div class="sch-ftr-chk__title">4K</div>
          </label>
        </div>
      </div>
	  <div class="sch-body">
	  <div class="sch-sl swiper">
          <div class="swiper-wrapper" style="display:flex;">
           
	  <?
			foreach($arrs as $k=>$v)
			{
				
				//print '<div class="sch-item swiper-slide" >';
				//
	 
				 
				 
				
				 if($_GET['new'])
				 {
					 ?> <div class="sch-item swiper-slide">
					 <br><br>
					 <div class="sch-item__title">Секция <?=$k+1?></div>
					 
					 <?
					 print '<table class="sch-tb" border="0"> ';
						$sa->disp_home_p_n( $_GET['home']  , $v['section_id']  ,1);
					 print ' </table>';
					?></div><?						
				 }
				 else{
					 print '<table class="sch-tb"  border="0"> ';
						$sa->disp_home_p( $_GET['home']  , $v['section_id']  ,1); 
							 print ' </table>';
				 }
				 
				 
	 
				 
				 //print ' </div>';
			}
			 ?>
			 </div>
			 </div>
			 </div>
			 </div>
			 
			 <?
	}
 		
 