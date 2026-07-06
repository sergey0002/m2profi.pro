<?php
$form_action = $data['form_action'];
$ajax_base   = $data['ajax_base'];
?>
<link rel="stylesheet" href="/sahmatka/template/default/css/zapiskeys_add.css">

<form method="post" action="<?= htmlspecialchars($form_action) ?>" id="zapisform_add" class="zapis-add-form">
	<div id="form_progressbar" class="zapis-add-form__progress">Загрузка…<br/><img src="/sahmatka/loader.gif" alt=""></div>

	<p class="zapis-add-form__static">
		<label class="zapis-add-form__check" for="zapis_add_vne">
			<input type="checkbox" name="vne_grafika" id="zapis_add_vne" value="1">
			<span>Запись вне графика</span>
		</label>
	</p>

	<p>
		<label for="home_id">Дом</label><br/>
		<select name="home_id" id="home_id" class="input_edit" data-bl="1" required disabled>
			<option value="">Выбрать дом</option>
		</select>
	</p>

	<p>
		<label for="section_id">Секция</label><br/>
		<select name="section_id" id="section_id" class="input_edit" data-bl="2" required disabled>
			<option value="">Выбрать секцию</option>
		</select>
	</p>

	<p>
		<label for="apartament_num">Квартира</label><br/>
		<select name="apartament_num" id="apartament_num" class="input_edit" data-bl="3" required disabled>
			<option value="">Выбрать квартиру</option>
		</select>
	</p>

	<p>
		<label for="date">Дата</label><br/>
		<select name="date" id="date" class="input_edit" data-bl="4" required disabled>
			<option value="">Выбрать дату</option>
		</select>
	</p>

	<p>
		<label for="time">Время</label><br/>
		<select name="time" id="time" class="input_edit" data-bl="5" required disabled>
			<option value="">Выбрать время</option>
		</select>
	</p>

	<p>
		<label for="fio">ФИО</label><br/>
		<input class="input_edit" name="fio" id="fio" type="text" placeholder="ФИО" required>
	</p>

	<p>
		<label for="phone">Телефон</label><br/>
		<input class="input_edit phone_mask" name="phone" id="phone" type="tel" placeholder="Телефон" required>
	</p>

	<p>
		<label for="email">E-Mail</label><br/>
		<input class="input_edit" name="email" id="email" type="email" placeholder="E-Mail" required>
	</p>

	<p class="zapis-add-form__static">
		<label class="zapis-add-form__check" for="zapis_add_pom">
			<input type="checkbox" name="pom" id="zapis_add_pom" value="1">
			<span>С помогающей</span>
		</label>
	</p>

	<p class="zapis-add-form__static">
		<span class="zapis-add-form__label">Тип договора</span><br/>
		<label class="zapis-add-form__radio"><input type="radio" name="dkp" value="0" checked> ДДУ</label>
		<label class="zapis-add-form__radio"><input type="radio" name="dkp" value="1"> ДКП</label>
	</p>

	<p><?= $filed->submit('Сохранить') ?></p>
</form>

<?php include __DIR__ . '/form_add_js.php'; ?>
