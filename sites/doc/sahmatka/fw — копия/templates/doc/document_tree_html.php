<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<div id="doc-search-container" style="margin-bottom: 15px; position: relative;">
	<input type="text" id="doc-search-input" placeholder="Поиск по названию документа..." style="width: 100%; padding: 8px; padding-right: 80px; box-sizing: border-box; border: 1px solid #ccc; border-radius: 4px;">
	<button id="doc-search-clear" style="position: absolute; right: 5px; top: 50%; transform: translateY(-50%); padding: 5px 10px; cursor: pointer;">Очистить</button>
</div>

<div style="margin-bottom: 10px;">
	<label style="cursor: pointer;">
		<input type="checkbox" id="show-deleted-checkbox" style="cursor: pointer;">
		<span style="margin-left: 5px;">Показывать удаленные элементы</span>
	</label>
</div>

<div style="margin-bottom: 15px; display: flex; gap: 10px; align-items: center; flex-wrap: wrap;" class="date-filters">
	<label style="display: flex; align-items: center; gap: 5px;">
		<span>Дата документа от:</span>
		<input type="text" id="date-from" placeholder="дд.мм.гггг" style="padding: 5px; border: 1px solid #ccc; border-radius: 4px; width: 120px;">
	</label>
	<label style="display: flex; align-items: center; gap: 5px;">
		<span>до:</span>
		<input type="text" id="date-to" placeholder="дд.мм.гггг" style="padding: 5px; border: 1px solid #ccc; border-radius: 4px; width: 120px;">
	</label>
	<button id="date-clear" style="padding: 5px 10px; cursor: pointer;">Очистить даты</button>
</div>

<div id="doc_tree"></div>
