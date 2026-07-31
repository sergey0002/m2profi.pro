<?
 //print_r($data);
?>
<section>
			<div class="container rent_p">
				<h1 class="rent_h3" style="margin-top: 35px; font-family: 'Exo 2';">ЗАЯВКА НА АРЕНДУ ПОМЕЩЕНИЯ</h1>
				<p class="form_rz_p" style="font-size: 16px; line-height: 20px;">Перед отправкой, пожалуйста, проверьте корректность внесенных данных</p>
				<form name="rent_zayavka2" method="post" action="" enctype="multipart/form-data">
				
					<div class="row">
						<div class="col-sm-6">
							<p class="form_rz">Название компании</p>
							<input name="caption" type="text" placeholder="Название" required class="form_rz_text form_rent_z" style="width: 100%;">
						 </div>
						 
						 <div class="col-sm-6">
							<p class="form_rz">ИНН (ОГРН)</p>
							<input name="inn" type="text" placeholder="ИНН (ОГРН)" required class="form_rz_text form_rent_z" style="width: 100%;">
						 </div>
 
					</div>
					
					<div class="row">
						<div class="col-sm-12">
							<p class="form_rz">Интересующие адреса</p>
							<textarea name="adress" class="form_rz_text form_rent_z" placeholder="Интересующие адреса" required=""   rows="5" style="width: 100%; padding:10px; height: auto;"><?=$data['h_adress']?> <?=$data['adress']?></textarea>
						 </div>
					</div>
					  
					<div class="row">
						<div class="col-sm-12">
							<p class="form_rz">Профиль деятельности</p>
							<input name="profile" type="text" placeholder="Профиль деятельности" required class="form_rz_text form_rent_z" style="width: 100%;">
						 </div>
					</div>
					<div class="row">
						<div class="col-sm-12">
							<p class="form_rz">Ассортиментная линейка (услуги)</p>
							<textarea name="assortiment" class="form_rz_text form_rent_z" placeholder="Ассортимент (товарные марки, производители, ценовая категория)" required="" cols="40" rows="3" style="width: 100%; height: auto; padding:10px;"></textarea>
						 </div>
					</div>
					 
					<div class="row">
						<div class="col-sm-6">
							<p class="form_rz">Интересуемая площадь</p>
							<input name="area" type="text" placeholder="Площадь" required class="form_rz_text form_rent_z" style="width: 100%;" value="<?=$data['area']?>">
						</div>
						<div class="col-sm-6">
							<p class="form_rz">Планируемая дата открытия</p>
							<input name="opendate" type="text" placeholder="Дата открытия" required class="form_rz_text form_rent_z" style="width: 100%;">
						</div>
					</div>
				  
					 <div class="row">
						<div class="col-sm-12">
							<p class="form_rz">Адреса действующих отделов</p>
							<textarea name="adress_d" class="form_rz_text form_rent_z" placeholder="Адреса действующих отделов" required="" cols="40" rows="3" style="width: 100%; padding:10px; height: auto;"></textarea>
						 </div>
					</div>
					
					<div class="row">
						<div class="col-sm-12">
							<p class="form_rz">Требования к арендуемому помещению</p>
							<textarea name="treb" class="form_rz_text form_rent_z" placeholder="Мощность линии, наличие водопровода, разгрузка и т.д." required="" cols="40" rows="3" style="width: 100%; padding:10px; height: auto;"></textarea>
						 </div>
					</div>
					 
					<div class="row">
						<div class="col-sm-6">
							<p class="form_rz">Скан файла</p>
							 <label><input name="scan" class="doc" type="file" style="padding-left:0;"> </label>
						 </div>
						 
						 <div class="col-sm-6">
							<p class="form_rz">Копия ОГРН, ЕГРИП</p>
							 <label><input name="scan2" class="doc" type="file" style="padding-left:0;"> </label>
						 </div>
						 
						 
					</div>
					 
					<div class="row">
						<div class="col-sm-12">
							<p class="form_rz">ФИО</p>
							<input name="fio" class="form_rent_z form_rz_text" type="text"  required="required" style="width: 100%;" > </div>
					</div>
					
					<div class="row">
						<div class="col-sm-12">
							<p class="form_rz">Телефон</p>
							<input name="phone" class="form_rent_z form_rz_text" type="text"   required="required" style="width: 100%;"> </div>
					</div>
					
					<div class="row">
						<div class="col-sm-12">
							<p class="form_rz">E-Mail</p>
							<input name="email" class="form_rent_z form_rz_text" type="text"   required="required" style="width: 100%;"> </div>
					</div>
					
					<div style="margin: 40px 0px 35px 0px; text-align: center;">
						<button type="submit" class="btn_bg_border">
							<div class="btn_bg_text p20"> ОТПРАВИТЬ ЗАЯВКУ <i class="btn_arrowx"></i> </div>
						</button>
					</div>
					<p class="form_rz_p"> Отправляя заявку вы автоматически даете свое <a href="https://em-nsk.ru/politics.docx">согласие на обработку персональных данных</a> </p>
				</form>
			</div>
		</section>