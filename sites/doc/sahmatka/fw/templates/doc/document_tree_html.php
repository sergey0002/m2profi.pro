<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

<div class="doc-controls-wrapper">
    <div class="doc-search-container">
        <input type="text" id="doc-search-input" class="doc-search-input" placeholder="Поиск по названию документа...">
        <button id="doc-search-clear" class="doc-search-clear" title="Очистить">✕</button>
    </div>

    <div class="doc-options-container">
        <label class="doc-checkbox-label">
            <input type="checkbox" id="show-deleted-checkbox" class="doc-checkbox">
            <span class="doc-checkbox-text">Показывать удаленные элементы</span>
        </label>
    </div>
</div>

<div class="date-filters-container">
    <div class="date-filter-group">
        <label for="date-from" class="date-filter-label">Дата документа от:</label>
        <div class="date-input-wrapper">
            <input type="text" id="date-from" class="date-filter-input" placeholder="дд.мм.гггг">
            <button type="button" class="date-filter-clear" data-target="date-from" title="Очистить">✕</button>
        </div>
    </div>
    <div class="date-filter-group">
        <label for="date-to" class="date-filter-label">до:</label>
        <div class="date-input-wrapper">
            <input type="text" id="date-to" class="date-filter-input" placeholder="дд.мм.гггг">
            <button type="button" class="date-filter-clear" data-target="date-to" title="Очистить">✕</button>
        </div>
    </div>
</div>

<div id="doc_tree"></div>
