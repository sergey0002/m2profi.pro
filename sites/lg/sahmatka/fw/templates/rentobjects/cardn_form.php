 
	


<div id="app_rent_commercial" class="m_okno"  >
	<h2 class="flex_space-between ">
	Заявка на аренду торговой площади
	<a href="javascript:(print());">
	<img src="/img/print.svg" alt=""></a>
	</h2>

	<p>Перед отправкой, пожалуйста, проверьте корректность внесенных данных</p>

	<form name="rent" method="post" class="ajax_form" action="/ajax_form.php"> 
		<div class="row">
			<div class="col-md-6">
				<input name="caption" class="form_rent_app" type="text" placeholder="Название" required="">
			</div>
			<div class="col-md-6">
				<input name="inn" class="form_rent_app" type="number" placeholder="ИНН (ОРГН)" required="">
			</div>
			<div class="col-md-12">
				<textarea name="adressa" class="form_rent_app" type="text" placeholder="Новосибирский район, Станционный сельсовет, посёлок Садовый, Онежская улица, 3 Офис 1 Добавьте адреса" required="" cols="40" rows="3"></textarea>
			</div>
			<div class="col-md-12">
				<input name="profile" class="form_rent_app" type="text" placeholder="Профиль деятельности" required=""> </div>
			<div class="col-md-12">
				<textarea name="assortiment" class="form_rent_app" type="text" placeholder="Ассортиментная линейка (услуги)" required="" cols="40" rows="3"></textarea>
			</div>
			<div class="col-md-6">
				<input name="area" class="form_rent_app" type="text" placeholder="Интересуемая площадь" required="">
			</div>
			<div class="col-md-6">
				<input name="opendate" class="form_rent_app" type="text" placeholder="Планируемая дата открытия" required="">
			</div>
			<div class="col-md-12">
				<input name="adresf" class="form_rent_app" type="text" placeholder="Адреса действующих отделов арендатора" required="">
			</div>
			<div class="col-md-12">
				<textarea name="power" class="form_rent_app" type="text" placeholder="Требования к арендуемому помещению" required="" cols="40" rows="3"></textarea>
			</div>
			<div class="col-md-6">
				<div class="form_rent_file">
					<p style="   padding: 16px 11px 0 0;">Отсканированный документ</p>
					<div class="input-file-row">
						<label class="input-file input-file_blue">
							<input type="file" name="file[]" multiple>
							<span>Выберите файл</span>
						</label>
						<div class="input-file-list"></div>
					</div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="form_rent_file">
					<p style="   padding: 16px 11px 0 0;"> Копия ОГРН, ЕГРИП</p>
					<div class="input-file-row">
						<label class="input-file input-file_blue">
							<input type="file" name="file[]" multiple>
							<span>Выберите файл</span>
						</label>
						<div class="input-file-list"></div>
					</div>
				</div>
			</div>
			<div class="col-md-12">
				<input name="fio" class="form_rent_app" type="text" placeholder="ФИО" required="">
			</div>
			<div class="col-md-6">
				<input name="phone" class="form_rent_app" type="tel" placeholder="Телефон" required="">
			</div>
			<div class="col-md-6">
				<input name="email" class="form_rent_app" type="email" placeholder="Email" required="">
			</div>
			<div class="col-md-12">
				<div class="btn_form m20">
					<button type="submit" class="btn_form_blue">Отправить заявку</button>
				</div>
			</div>
			<div class="col-md-12">
				<div class="pers">
					<input type="checkbox" id="pers" name="pers" checked>
					<label for="pers">Я даю свое согласие на обработку персональных данных</label>
				</div>
			</div>

		</div>
	</form>

</div>



 