 <?
 $v=$data;
 
 
if($v['show_b']){$show_b_panel = '<div class="rentobj_stp rentobj_stp1"> с комиссией</div>';}
else{$show_b_panel = '<div class="rentobj_stp rentobj_stp2"> без комиссии</div>';}

 ?><!-- Карточка объекта -->
      <div class="row rentcard" style="opacity: 0.9; -webkit-filter: grayscale(100%); filter: grayscale(100%);">
        <div class="col-md-12">
          <div class="grey_shadow">
            <div class="row" style="padding: 13px 0px 13px 0px;">
              <div class="col-lg-4 p10" style="text-align:center;"> <?=$show_b_panel?> <img src="<?=$v['plan']?>" alt="" class="rent_img" style="max-height:250px;  -webkit-filter: grayscale(100%); filter: grayscale(100%);"> </div>
              <div class="col-lg-5 p10">
                <p class="rent_h3"><?=$area?></p>
                <p><?=$v['h_adress']?></p>

				<br/>
                <p><?=$v['comment']?></p>
		
				<br/><p><b>Подробности по телефону</b></p>

              </div>
              <div class="col-lg-3 p10"> 
                <div class="rent_tech">
                  <div class="rent_subtitle">Технические характеристики</div>
                  <div style="margin-top: 12px; margin-bottom: 20px;">
                    <ul class="rent_list">
						<?=$v['params']?>
                    </ul>
                  </div>
                </div>

                  <button class="btn_bg_border">
                    <div class="btn_bg_text p20">+ 7 (383) 347-47-00<i class="btn_arrowx"></i> </div>
                  </button>
             
              </div>
            </div>
          </div>
        </div>
        <!--  -->

      </div>