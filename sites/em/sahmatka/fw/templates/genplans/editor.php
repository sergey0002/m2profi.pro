<?php
$kvartal_id = (int) $data['kvartal_id'];
$kvartal_title = (string) ($data['kvartal_title'] ?? '');
$image_url = $data['image_url'];
$image_w = (int) $data['image_w'];
$image_h = (int) $data['image_h'];
$ajax_base = $data['ajax_base'];
$max_upload_bytes = (int) ($data['max_upload_bytes'] ?? (20 * 1024 * 1024));
$widget_demo_url = isset($data['widget_demo_url']) ? (string) $data['widget_demo_url'] : '';
?>
<link rel="stylesheet" href="/sahmatka/template/default/libs/leaflet/leaflet.css" />
<link rel="stylesheet" href="/sahmatka/template/default/libs/leaflet-draw/leaflet.draw.css" />
<link rel="stylesheet" href="/sahmatka/template/default/css/genplan_editor.css" />

<div class="genplan-editor" id="genplan_editor">
    <div class="genplan-editor__head">
        <h2 class="genplan-editor__title">Интерактивный план — <?= htmlspecialchars($kvartal_title, ENT_QUOTES, 'UTF-8') ?></h2>
    </div>

    <div class="genplan-editor__panel-card">
        <div class="genplan-editor__floor-panel" id="genplan_toolbar">
            <span class="genplan-editor__mode-toggle">
                <button type="button" id="genplan_mode_poly" class="genplan-editor__btn genplan-editor__btn--primary">Полигоны</button>
                <button type="button" id="genplan_mode_labels" class="genplan-editor__btn">Подписи</button>
                <button type="button" id="genplan_add_point" class="genplan-editor__btn" style="display:none;">Точка</button>
            </span>
            <span id="genplan_dirty_actions" class="genplan-editor__dirty-actions" style="display:none;">
                <button type="button" id="genplan_save" class="genplan-editor__btn genplan-editor__btn--primary">Сохранить</button>
                <button type="button" id="genplan_cancel" class="genplan-editor__btn">Отменить</button>
            </span>
        </div>

        <div id="genplan_upload_panel" class="genplan-editor__upload-panel">
            <input type="file" id="genplan_upload_input" accept=".png,.jpg,.jpeg,.webp,image/*" hidden />
            <button type="button" id="genplan_upload_btn" class="genplan-editor__btn">Загрузить план</button>
            <button type="button" id="genplan_clear_all" class="genplan-editor__btn genplan-editor__btn--danger">Очистить план</button>
        </div>

        <div id="genplan_meta_panel" class="genplan-editor__meta-panel" style="display:none;">
            <div class="genplan-editor__meta-grid">
                <div class="genplan-editor__meta-main">
                    <label class="genplan-editor__field genplan-editor__field--title">
                        <span class="genplan-editor__field-label">Заголовок <span class="genplan-editor__hint">можно пустым, если выбран дом</span></span>
                        <input type="text" id="genplan_title_input" maxlength="4000" placeholder="например, Дом 26" />
                    </label>
                    <label class="genplan-editor__field">
                        <span class="genplan-editor__field-label">Контент <span class="genplan-editor__hint">HTML · тело карточки</span></span>
                        <textarea id="genplan_content_input" maxlength="8000" placeholder="Дополнительный текст…" rows="3"></textarea>
                    </label>
                </div>
                <div class="genplan-editor__meta-side">
                    <label class="genplan-editor__field">
                        <span class="genplan-editor__field-label">Дом <span class="genplan-editor__hint">опционально</span></span>
                        <select id="genplan_home_select">
                            <option value="">— не выбран —</option>
                        </select>
                    </label>
                    <label class="genplan-editor__field">
                        <span class="genplan-editor__field-label">URL по клику</span>
                        <input type="text" id="genplan_link_input" maxlength="512" placeholder="https://…" autocomplete="off" />
                    </label>
                    <div class="genplan-editor__checks">
                        <label class="genplan-editor__check">
                            <input type="checkbox" id="genplan_show_title_desktop" checked />
                            <span>Заголовок на десктопе</span>
                        </label>
                        <label class="genplan-editor__check">
                            <input type="checkbox" id="genplan_show_title_mobile" checked />
                            <span>Заголовок на мобиле</span>
                        </label>
                        <label class="genplan-editor__check">
                            <input type="checkbox" id="genplan_show_apt_links" disabled />
                            <span>Ссылки на квартиры</span>
                        </label>
                    </div>
                </div>
            </div>
            <div id="genplan_home_preview" class="genplan-editor__home-preview" aria-live="polite"></div>
        </div>

        <div id="genplan_messages" class="genplan-editor__messages" role="status" aria-live="polite"></div>
        <?php if ($widget_demo_url !== ''): ?>
        <p class="genplan-editor__demo">
            <a id="genplan_widget_demo_link"
               href="<?= htmlspecialchars($widget_demo_url, ENT_QUOTES, 'UTF-8') ?>"
               target="_blank"
               rel="noopener">Демо виджет</a>
        </p>
        <?php endif; ?>
    </div>

    <div id="genplan_map" class="genplan-editor__map"></div>
</div>

<script src="/sahmatka/template/default/libs/leaflet/leaflet.js"></script>
<script src="/sahmatka/template/default/libs/leaflet-draw/leaflet.draw.js"></script>
<script src="/sahmatka/template/default/libs/leaflet-path-drag/L.Path.Drag.js"></script>
<script>
window.GENPLAN_CONFIG = {
    kvartalId: <?= $kvartal_id ?>,
    imageUrl: <?= json_encode($image_url) ?>,
    imageWidth: <?= $image_w ?>,
    imageHeight: <?= $image_h ?>,
    ajaxBase: <?= json_encode($ajax_base) ?>,
    maxUploadBytes: <?= (int) $max_upload_bytes ?>,
    publicSiteUrl: <?= json_encode((getenv('PUBLIC_URL') && getenv('PUBLIC_URL') !== '') ? rtrim((string) getenv('PUBLIC_URL'), '/') : 'https://em-nsk.ru') ?>
};
</script>
<script src="/sahmatka/template/default/js/genplan_editor.js"></script>
