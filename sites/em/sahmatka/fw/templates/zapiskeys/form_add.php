<?php
$form_action = $data['form_action'];
$ajax_base   = $data['ajax_base'];
?>
<link rel="stylesheet" href="/sahmatka/template/default/css/zapiskeys_add.css">

<form method="post" action="<?= htmlspecialchars($form_action) ?>" id="zapisform_add" class="zapis-add-form">
	<div id="form_progressbar" class="zapis-add-form__progress">Загрузка…<br/><img src="/sahmatka/loader.gif" alt=""></div>

	<p>
		<?= $filed->checkbox('vne_grafika', 'Запись вне графика', 0, ' data-bl="-1" ', 'vne_grafika') ?>
	</p>

	<p>
		<label for="home_id">Дом</label><br/>
		<select name="home_id" id="home_id" class="input_edit" data-bl="1" required disabled>
			<option value="">Выбрать дом</option>
		</select>
	</p>

	<p>
		<label for="apartament_num">Квартира</label><br/>
		<select name="apartament_num" id="apartament_num" class="input_edit" data-bl="2" required disabled>
			<option value="">Выбрать квартиру</option>
		</select>
		<input type="hidden" name="section_id" id="section_id" value="">
	</p>

	<p>
		<label for="date">Дата</label><br/>
		<select name="date" id="date" class="input_edit" data-bl="3" required disabled>
			<option value="">Выбрать дату</option>
		</select>
	</p>

	<p>
		<label for="time">Время</label><br/>
		<select name="time" id="time" class="input_edit" data-bl="4" required disabled>
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

	<p>
		<?= $filed->checkbox('pom', 'С помогающей', 0, ' data-bl="0" ', 'pom') ?>
	</p>

	<p>
		<label for="dkp">Тип договора</label><br/>
		<select name="dkp" id="dkp" class="input_edit" data-bl="0">
			<option value="0">ДДУ</option>
			<option value="1">ДКП</option>
		</select>
	</p>

	<p><?= $filed->submit('Сохранить') ?></p>
</form>

<?php include __DIR__ . '/form_add_js.php'; ?>
