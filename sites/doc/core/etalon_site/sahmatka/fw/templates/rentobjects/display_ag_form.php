<style>
	#hidemap{display:none;}
    .grey_shadow {
      box-shadow: 0px 0px 6px rgba(68, 97, 127, 0.3);
      height: 100%;
      padding: 15px;
    }
    .p10 {
      padding-bottom: 10px;
    }
    
    .p16 {
      padding-right: 16px;
      padding-bottom: 8px;
    }
    
    .p15 {
      padding-right: 7px;
    }
    
    .p20 {
      padding-left: 20px;
      padding-right: 20px;
    }
    
    .rentcard {
      padding-top: 20px;
    }
    
    body input:required:valid {
      color: #000000;
    }
    
    .rent_h2 {
    
      font-style: normal;
      font-weight: 500;
      font-size: 20px;
      line-height: 24px;
      /* identical to box height */
      letter-spacing: 0.2em;
      color: #232323;
      padding-bottom: 12px;
    }
    
    .container {
      overflow-x: inherit;
    }
    
    .btn_bg_border {
       
      border: 2px solid #445C79;
      border-radius: 40px;
      height: 52px;
    }
    
    .btn_bg {
      background: #445C79;
      border-radius: 40px;
    }
    
    .btn_bg_text {
      font-family: 'Montserrat';
      font-style: normal;
      font-weight: 700;
      font-size: 14px;
      line-height: 17px;
      letter-spacing: 0.1em;
      color: #44617F;
    }
    
    .btn_bg2 {
      height: 51px;
      background: #445C79;
      border-radius: 40px;
      border: hidden;
    }




.btn_arrowx2
{
  background-repeat : no-repeat;
  padding-left: 50px;
  margin-left: 5px;
  background-image: url("https://em-nsk.ru/m2rent/images/arrow_bg.svg");
   transition: all 0.5s ease-in-out;
}
.btn_arrow_bg{
  background-image: url("../m2rent/images/arrow_bg.svg");
background-repeat : no-repeat;
  padding-left: 50px;
  margin-left: 5px;
} /*
.btn_arrow_w:hover{
  background-image: url("../m2rent/images/arrow_w.svg");
transition: all 0.3s linear;
}
*/
.btn_arrow_w{
  background-image: url("../m2rent/images/arrow_w.svg");
background-repeat : no-repeat;
  padding-left: 50px;
  margin-left: 5px;
}


.btn_arrow_w:hover{
  background-image: url("../m2rent/images/arrow_bg.svg");
transition: all 0.3s linear;
}

    .btn_bg_text2 {
      font-family: 'Montserrat';
      font-style: normal;
      font-weight: 700;
      font-size: 14px;
      line-height: 17px;
      letter-spacing: 0.1em;
      color: #FFFFFF;
    }
    
    p {
      font-family: 'Montserrat';
      font-style: normal;
      font-weight: 400;
      font-size: 12px;
      line-height: 15px;
      color: #000000;
    }
    
    .rent_p {
      font-family: 'Montserrat';
      font-style: normal;
      font-weight: 400;
      font-size: 12px;
      line-height: 15px;
      color: #000000;
    }
    
    .rent_subtitle {
      font-family: 'Montserrat';
      font-style: normal;
      font-weight: 700;
      font-size: 12px;
      line-height: 18px;
      /* or 150% */
      color: #44617F;
    }
    
    .row {
      margin: 0 -12px;
    }
    
    .rent_img {
      max-width: 100%;
     
    }
    
    .rent_list {
      list-style-image: url("https://em-nsk.ru/m2rent/images/gal.svg");
    }
    
    .rent_list > li {
      margin-left: 22px;
      line-height: 2em;
    }
    
    .rent_a {
      font-family: 'Montserrat';
      font-style: normal;
      font-weight: 400;
      font-size: 12px;
      line-height: 15px;
      color: #56A4ED;
    } 
    
    .rent_a > img {
      margin-right: 9px;
    }
    
    .rent_h3 {
      font-style: normal;
      font-weight: 700;
      font-size: 20px;
      line-height: 24px;
      /* identical to box height */
      letter-spacing: 0.2em;
      color: #445C79;
    }
    
    .form_rect {
      background: #F5F5F5;
      border: 1px solid #44617F;
      height: 28px;
  
      font-style: normal;
      font-weight: 400;
      font-size: 12px;
      line-height: 15px;
      color: #000000;
      width: 100%;
      
    }
    
    .form_checkbox {
      background: #F5F5F5;
      border: 1px solid #44617F;
      width: 16px;
      height: 16px;
      margin: 3px;
    }
    
    .flex {
      display: flex;
    }
    
    .flex_box {
      display: flex;
      flex-flow: row wrap;
    }
    
    @media screen and (max-width: 991px) {
      .rent_m {
        margin-bottom: 20px;
      }
      .flex {
        padding-bottom: 10px;
        padding-top: 3px;
      }
    }
    
    .rent_h2_a {
      margin: 4px 0px 0px 19px;
    }
    
    @media screen and (max-width: 631px) {
      .rent_h2_a {
        margin: 0px;
      }
    }
    
    @media screen and (min-width: 991px) {
      .rent_tech {
        padding: 44px 0px 68px 0px;
      }
    }
	
/*------------ */
.mfp-iframe-scaler iframe {
    box-shadow: none;   
}
.mfp-no-margins img.mfp-img {
	padding: 0;
}
.mfp-no-margins .mfp-figure:after {
	top: 0;
	bottom: 0;
}
.mfp-no-margins .mfp-container {
	padding: 0;
}
.mfp-with-zoom .mfp-container,
.mfp-with-zoom.mfp-bg {
	opacity: 0;
	-webkit-backface-visibility: hidden; 
	transition: all 0.3s ease-out;
}
.mfp-with-zoom.mfp-ready .mfp-container {
		opacity: 1;
}

// КОнтент загруженного блока тень и фон
.mfp-with-zoom.mfp-ready .mfp-container .mfp-content
{ 
	box-shadow: 0 0 8px rgb(0 0 0 / 60%);
	background:#FFF;
}
.mfp-with-zoom.mfp-ready.mfp-bg {
		opacity: 0.8;
}

.mfp-with-zoom.mfp-removing .mfp-container, 
.mfp-with-zoom.mfp-removing.mfp-bg {
	opacity: 0;
}

.mfp-iframe-holder .mfp-content{
    height: 1200px;
    max-height: 100vh;
    width: 1500px;
    max-width: 100vw;
	background:#FFF;
}
  </style>
  
  
       

        <div class="col-lg-12 rent_m">
          <div class="grey_shadow">

           

            <form id="rentsearch_ag" action="" method="POST">
			<input id="rent_home_id" name="rent_home_id" type="hidden" value="<?=$_GET['rent_home_id']?>" />
             
			<input  name="sale" type="hidden" value="<?=$_GET['sale']?>" />
			 
              <div class="flex_box rent_p">
			  
			   <?
			   if( $_SESSION['sh_login'] == 'admin' )
			   {
			   ?>
				<div class="p16">
                  <label for="name">Статус</label>
                  <br>
                  <select name="status" class="form_rect">
				    <option value="">-</option>
                    <option value="2">Свободно</option>
                    <option value="4">Забронировано</option>
                    <option value="3">В аренде</option>
                  </select>
                </div>
				<?
			    }
				?>
				
				
                <div class="p16">
                <label>Площадь</label>
                  <br>
                  <input type="text" name="area_min" type="От" placeholder="От" class="form_rect" style="width: 80px;" />
                  <input  type="text" name="area_max" type="До" placeholder="До" class="form_rect" style="width: 80px;" />
                </div>

                <div class="p16">
                  <label for="name">Назначение</label>
                  <br>
                  <select name="appointment" class="form_rect">
				    <option value="">-</option>
                    <option value="1">Офисное</option>
                    <option value="2">Под магазин</option>
                    <option value="3">Под детский центр</option>
                  </select>
                </div>

                <div class="p16">
                  <label for="name">Этаж</label>
                  <br>
                  <select name="floor" class="form_rect" >
				    <option value="">-</option>
                    <option value="0">Цоколь</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                  </select>
                </div>

                <div class="p16">
                  <label for="name">Вход</label>
                  <br>
                  <select name="separate_entrance" class="form_rect" >
                    <option value="">Любой</option>
                    <option value="2">Отдельный</option>
                    <option value="3">Коридорный</option>
                  </select>
                </div>

                <div class="p16">
                  <label for="name">Помещений</label>
                  <br>
                  <select name="rooms" class="form_rect"  >
				    <option value="">-</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                  </select>
                </div>


				<div class="p16">
                  <label for="name">Улица</label>
                  <br>
                  <select name="street" id="street" class="form_rect" style="width:100px;" >
				  <option value=""> - </option>
                  </select>
                </div>
              </div>
 
              <div class="flex_box">
                <div class="p16">
                  <input name="otd" type="checkbox" value="1" class="form_checkbox" id="rent1">
                  <label for="rent1" style="position: relative;top: -3px;padding-left: 3px;">Отдельно стоящее здание</label>
                </div>
                <div class="p16" >
                  <input name="live" type="checkbox" value="2" class="form_checkbox" id="rent2">
                  <label for="rent2" style="position: relative;top: -3px;padding-left: 3px;">Жилое здание </label>
                </div>
              
                <div class="p16">
                  <input name="place_for_unloading" type="checkbox" name="a" value="4" class="form_checkbox" id="rent4">
                  <label for="rent4" style="position: relative;top: -3px;padding-left: 3px;">Место под разгрузку </label>
                </div>
              </div>
  
            </form>

          </div>
        </div>