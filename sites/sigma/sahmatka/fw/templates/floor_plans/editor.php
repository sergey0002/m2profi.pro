<?php
$home_id    = (int) $data['home_id'];
$ajax_base  = $data['ajax_base'];
$sections   = isset($data['sections']) && is_array($data['sections']) ? $data['sections'] : [
    ['id' => 1, 'caption' => 'Секция 1', 'maxFloor' => 30],
];
$max_upload_bytes = (int) ($data['max_upload_bytes'] ?? (20 * 1024 * 1024));
$widget_demo_url = isset($data['widget_demo_url']) ? (string) $data['widget_demo_url'] : '';
?>
<link rel="stylesheet" href="/sahmatka/template/default/libs/leaflet/leaflet.css" />
<link rel="stylesheet" href="/sahmatka/template/default/libs/leaflet-draw/leaflet.draw.css" />
<link rel="stylesheet" href="/sahmatka/template/default/css/floor_plan_editor.css" />

<div class="fp-editor" id="floor_plan_editor">
    <div class="fp-editor__head">
        <h2 class="fp-editor__title">Разметка планов этажей</h2>
    </div>

    <div class="fp-editor__panel-card">
        <div id="fp_floor_panel" class="fp-editor__floor-panel">
            <label class="fp-editor__field">
                <span class="fp-editor__field-label">Секция</span>
                <select id="fp_section_select">
                    <?php foreach ($sections as $sec): ?>
                        <option value="<?= (int) $sec['id'] ?>"
                                data-max-floor="<?= (int) $sec['maxFloor'] ?>">
                            <?= htmlspecialchars($sec['caption']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="fp-editor__field">
                <span class="fp-editor__field-label">Этаж</span>
                <select id="fp_floor_select"></select>
            </label>
            <label class="fp-editor__field">
                <span class="fp-editor__field-label"><?= htmlspecialchars(function_exists('unit_label_cap') ? unit_label_cap('nom') : 'Квартира') ?></span>
                <select id="fp_apartment_select"></select>
            </label>
            <label class="fp-editor__field fp-editor__field--label">
                <span class="fp-editor__field-label">Подпись</span>
                <input type="text" id="fp_label_input" maxlength="64" placeholder="необяз." />
            </label>
            <span id="fp_dirty_actions" class="fp-editor__dirty-actions" style="display:none;">
                <button type="button" id="fp_save" class="fp-editor__btn fp-editor__btn--primary">Сохранить</button>
                <button type="button" id="fp_cancel" class="fp-editor__btn">Отменить</button>
            </span>
        </div>
        <div id="fp_upload_panel" class="fp-editor__upload-panel">
            <input type="file" id="fp_upload_input" accept=".png,.jpg,.jpeg,.svg,.webp,image/*" hidden />
            <button type="button" id="fp_upload_btn" class="fp-editor__btn">Загрузить план этажа</button>
            <button type="button" id="fp_copy_floor" class="fp-editor__btn">Копировать разметку этажа</button>
            <button type="button" id="fp_clear_apartment" class="fp-editor__btn fp-editor__btn--danger">Очистить квартиру</button>
            <button type="button" id="fp_clear_markup" class="fp-editor__btn fp-editor__btn--danger">Очистить разметку этажа</button>
            <button type="button" id="fp_clear_plan" class="fp-editor__btn fp-editor__btn--danger">Очистить весь план</button>
        </div>
        <div id="fp_messages" class="fp-editor__messages" role="status" aria-live="polite"></div>
        <?php if ($widget_demo_url !== ''): ?>
        <p class="fp-editor__demo">
            <a id="fp_widget_demo_link"
               href="<?= htmlspecialchars($widget_demo_url, ENT_QUOTES, 'UTF-8') ?>"
               target="_blank"
               rel="noopener">Демо публичного виджета (этот план)</a>
            <span class="fp-editor__demo-hint">откроется в новом окне с #fw=секция.этаж</span>
        </p>
        <?php endif; ?>
    </div>

    <div id="fp_map" class="fp-editor__map"></div>
</div>

<script src="/sahmatka/template/default/libs/leaflet/leaflet.js"></script>
<script src="/sahmatka/template/default/libs/leaflet-draw/leaflet.draw.js"></script>
<script src="/sahmatka/template/default/libs/leaflet-path-drag/L.Path.Drag.js"></script>
<script>
window.FLOOR_PLAN_CONFIG = {
    homeId: <?= $home_id ?>,
    sections: <?= json_encode(array_values($sections), JSON_UNESCAPED_UNICODE) ?>,
    ajaxBase: <?= json_encode($ajax_base) ?>,
    maxUploadBytes: <?= (int) $max_upload_bytes ?>,
    unitLabelNomCap: <?= json_encode(function_exists('unit_label_cap') ? unit_label_cap('nom') : 'Квартира', JSON_UNESCAPED_UNICODE) ?>,
    widgetDemoUrl: <?= json_encode($widget_demo_url, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
};
</script>
<script src="/sahmatka/template/default/js/floor_plan_editor.js"></script>
