		<section>
			<div class="container-fluid rent_p">
				<h1 class="rent_h3" style="margin-top: 35px;  font-family: 'Exo 2';">ЗАЯВКА </h1>
				<form name="rent_zayavka" method="post" action="">
				<input name="id" type="hidden" value="<?=$_GET['id']?>" />
					<div class="row">
						<div class="col-sm-6">
							<p class="form_rz">Арендатор</p>
							<input name="name" class="form_rent_z form_rz_text" type="text" placeholder="ФИО" required=""> </div>
						<div class="col-sm-6">
							<p class="form_rz">Площадь</p>
							<input name="area" class="form_rent_z form_rz_text" type="text" placeholder="" required=""> </div>
						<div class="col-sm-6">
							<p class="form_rz">Телефон</p>
							<input  name="phone" class="form_rent_z form_rz_text" type="tel" placeholder="+7" required=""> </div>
						<div class="col-sm-6">
							<p class="form_rz">Адрес</p>
							<input name="adress" class="form_rent_z form_rz_text" type="text" placeholder="" required=""> </div>
						<div class="col-sm-6">
							<p class="form_rz">Email</p>
							<input name="email" class="form_rent_z form_rz_text" type="email" placeholder="" required=""> </div>
						<div class="col-sm-6">
							<p class="form_rz">Назначение помещения</p>
							<input name="naz" class="form_rent_z form_rz_text" type="text" placeholder="" required=""> </div>
						<div class="col-sm-6">
							<p class="form_rz">Название компании</p>
							<input name="comname"  class="form_rent_z form_rz_text" type="text" placeholder="" required=""> </div>
						<div class="col-sm-6">
							<p class="form_rz">Ассортиментная линейка, услуги</p>
							<input name="assortiment" class="form_rent_z form_rz_text" type="text" placeholder="" required=""> </div>
						<div class="col-sm-6">
							<p class="form_rz">ИНН (ОГРН)</p>
							<input name="inn" class="form_rent_z form_rz_text" type="number" placeholder="" required=""> </div>
						<div class="col-sm-6">
							<p class="form_rz">Дествующие филиалы</p>
							<input name="filial" class="form_rent_z form_rz_text" type="text" placeholder="" required=""> </div>
						<div class="col-sm-6">
							<p class="form_rz">Дата заявления</p>
							<input name="date" class="form_rent_z form_rz_text" type="date" value="today"  placeholder="" required=""> </div>
						<div class="col-sm-6">
							<p class="form_rz">Пожелания к помещению</p>
							<input name="pozelan" class="form_rent_z form_rz_text" type="text" placeholder="" required=""> </div>
					</div>
					<div style="margin: 40px 0px 35px 0px; text-align: center;"> 
							<button type="submit" class="btn_bg_border">
								<div class="btn_bg_text p20"> ОТПРАВИТЬ ЗАЯВКУ <i class="btn_arrowx"></i> </div>
							</button>
					</div>
					<p class="form_rz_p"> Отправляя заявку вы автоматически даете свое <a href="https://em-nsk.ru/politics.docx">согласие на обработку персональных данных</a></p>
				</form>
			</div>
		</section>