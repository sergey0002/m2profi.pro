 <?
 $v = $data;
 if(!$v['status']){$v['status']=2;}
if($v['show_b']){$show_b_panel = '<div class="rentobj_stp rentobj_stp1"> с комиссией</div>';}
else{$show_b_panel = '<div class="rentobj_stp rentobj_stp2"> без комиссии</div>';}
	
 ?><!-- Карточка объекта -->
      <div class="row rentcard">
        <div class="col-md-12">
          <div class="grey_shadow">
            <div class="row" style="padding: 13px 0px 13px 0px;">
              <div class="col-lg-4 p10" style="text-align:center;"> <?=$show_b_panel?> <img src="<?=$v['plan']?>" alt="" class="rent_img" style="max-height:250px;"> </div>
              <div class="col-lg-5 p10">
                <p class="rent_h3"><?=$area?></p>
                <p><?=$v['h_adress']?></p>
				 <p><?=$v['adress']?></p>
				
				<?
			//	print '<div style="display:none">';
			//	print_R($v); 
			//	print '</div>'; 
				?>
				
			 	<p>
                  <a href="https://xdemo.m2profi.pro/sahmatka/iframe_router.php?ctr=rentobjects&act=card&id=<?=$v['rent_objects_id']?>#map" class="rent_a iframerent"><img src="https://em-nsk.ru/m2rent/images/map.svg" alt=""> на карте</a>
                </p>
				<br/>
                <p><?=$v['comment']?></p>
			 
			  <p><a href="https://xdemo.m2profi.pro/sahmatka/iframe_router.php?ctr=rentobjects&act=card&id=<?=$v['rent_objects_id']?>" class="rent_a iframerent" style="text-decoration: underline;">Подробнее</a></p>
              </div>
			  
			  
			  
              <div class="col-lg-3 p10"> 
			  
			   
				  <?
				  if($v['appointment']!=4)
				  {
				  ?>
			  
                <div class="rent_tech" style="padding-top:0; padding-bottom:0;">
                  <div class="rent_subtitle">Технические характеристики:</div>
                  <div style="margin-top: 12px; margin-bottom: 20px;">
                    <ul class="rent_list">
						<?=$v['params']?>
                    </ul>
                  </div>
                </div>
				
				<?
				  }
				?>
					<div style="padding:5px; text-align:center;">
						<div style="font-size: 24px; line-height:1.2em; margin:10px; background:<?=$GLOBALS['broni_colors'][$v['status']]?>"><?=$GLOBALS['broni_status'][$v['status']]?></div> 
						
						 <?
						  if($v['appointment']==4 && $v['sale_price'])
						  {
						  ?><br/><br/>
							 <b style="font-size:28px;">  <?= number_format($v['sale_price'], 0, ' ', ' ')  ?> руб.</b>
							<br/><br/>
							<?
						  }
						?>
						<?
						
						?>
					</div>
				
			
				<?
					if($v['status'] == 2 || !$v['status'] )
					{
						?>	
			  <a class="iframerent" href="https://xdemo.m2profi.pro/sahmatka/iframe_router.php?ctr=rentobjects&act=card&id=<?=$v['rent_objects_id']?>">
                  <button class="btn_bg_border">
                    <div class="btn_bg_text p20"> ОТПРАВИТЬ ЗАЯВКУ <i class="btn_arrowx"></i> </div>
                  </button>
              </a>
					 <?
					}
					 ?>
              </div>
            </div>
          </div>
        </div>
        <!--  -->

      </div>