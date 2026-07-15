<?php
$home_id    = (int) $data['home_id'];
$image_url  = $data['image_url'];
$image_w    = (int) $data['image_w'];
$image_h    = (int) $data['image_h'];
$max_floor  = (int) $data['max_floor'];
$ajax_base  = $data['ajax_base'];
$max_upload_bytes = (int) ($data['max_upload_bytes'] ?? (20 * 1024 * 1024));
$sections   = isset($data['sections']) && is_array($data['sections']) ? $data['sections'] : [
    ['id' => 1, 'caption' => 'Секция 1', 'maxFloor' => $max_floor ?: 30],
];
?>
<link rel="stylesheet" href="/sahmatka/template/default/libs/leaflet/leaflet.css" />
<link rel="stylesheet" href="/sahmatka/template/default/libs/leaflet-draw/leaflet.draw.css" />
<link rel="stylesheet" href="/sahmatka/template/default/css/facade_editor.css" />

<div class="facade-editor" id="facade_editor">
    <div class="facade-editor__head">
        <h2 class="facade-editor__title">Разметка фасада</h2>
    </div>

    <div class="facade-editor__panel-card">
        <div id="facade_floor_panel" class="facade-editor__floor-panel">
            <label class="facade-editor__field">
                <span class="facade-editor__field-label">Секция</span>
                <select id="facade_section_select">
                    <?php foreach ($sections as $sec): ?>
                        <option value="<?= (int) $sec['id'] ?>"
                                data-max-floor="<?= (int) $sec['maxFloor'] ?>">
                            <?= htmlspecialchars($sec['caption']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label class="facade-editor__field">
                <span class="facade-editor__field-label">Этаж</span>
                <select id="facade_floor_select"></select>
            </label>
            <label class="facade-editor__field facade-editor__field--label">
                <span class="facade-editor__field-label">Подпись</span>
                <input type="text" id="facade_label_input" maxlength="64" placeholder="необяз." />
            </label>
            <span id="facade_dirty_actions" class="facade-editor__dirty-actions" style="display:none;">
                <button type="button" id="facade_floor_save" class="facade-editor__btn facade-editor__btn--primary">Сохранить</button>
                <button type="button" id="facade_floor_cancel" class="facade-editor__btn">Отменить</button>
            </span>
        </div>
        <div id="facade_upload_panel" class="facade-editor__upload-panel">
            <input type="file" id="facade_upload_input" accept=".png,.jpg,.jpeg,.webp,image/*" hidden />
            <button type="button" id="facade_upload_btn" class="facade-editor__btn">Загрузить фасад</button>
            <button type="button" id="facade_clear_floor" class="facade-editor__btn facade-editor__btn--danger">Очистить этаж</button>
            <button type="button" id="facade_clear_all" class="facade-editor__btn facade-editor__btn--danger">Очистить весь фасад</button>
        </div>
        <div id="facade_messages" class="facade-editor__messages" role="status" aria-live="polite"></div>
    </div>

    <div id="facade_map" class="facade-editor__map"></div>
</div>

<script src="/sahmatka/template/default/libs/leaflet/leaflet.js"></script>
<script src="/sahmatka/template/default/libs/leaflet-draw/leaflet.draw.js"></script>
<script src="/sahmatka/template/default/libs/leaflet-path-drag/L.Path.Drag.js"></script>
<script>
window.FACADE_CONFIG = {
    homeId: <?= $home_id ?>,
    imageUrl: <?= json_encode($image_url) ?>,
    imageWidth: <?= $image_w ?>,
    imageHeight: <?= $image_h ?>,
    maxFloor: <?= $max_floor ?>,
    sections: <?= json_encode(array_values($sections), JSON_UNESCAPED_UNICODE) ?>,
    ajaxBase: <?= json_encode($ajax_base) ?>,
    maxUploadBytes: <?= (int) $max_upload_bytes ?>
};
</script>
<script src="/sahmatka/template/default/js/facade_editor.js"></script>
