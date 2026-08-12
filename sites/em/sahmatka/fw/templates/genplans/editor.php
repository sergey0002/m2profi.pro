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
                <button type="button" id="genplan_mode_life" class="genplan-editor__btn">Жизнь</button>
                <button type="button" id="genplan_add_point" class="genplan-editor__btn" style="display:none;">Точка</button>
                <span id="genplan_life_tools" class="genplan-editor__life-tools" style="display:none;">
                    <button type="button" id="genplan_life_car" class="genplan-editor__btn">Машина</button>
                    <button type="button" id="genplan_life_person" class="genplan-editor__btn">Человек</button>
                    <button type="button" id="genplan_life_persp" class="genplan-editor__btn">Перспектива</button>
                </span>
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
                        <input type="text" id="genplan_link_input" maxlength="512" placeholder="https://…" />
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

        <div id="genplan_life_panel" class="genplan-editor__life-panel" style="display:none;">
            <p class="genplan-editor__hint genplan-editor__life-hint">Жизнь сохраняется сразу. Рисуйте open-линию (≥2 точки). Клик по треку — правка углов, направления и параметров + «Применить»; «Удалить» — убрать путь. Машины едут только в одну сторону (без встречки); можно чередовать разные варианты по одному проезду.</p>
            <div class="genplan-editor__life-grid">
                <div class="genplan-editor__life-form">
                    <label class="genplan-editor__field">
                        <span class="genplan-editor__field-label">Скорость <span class="genplan-editor__hint">px/s</span></span>
                        <input type="number" id="genplan_life_speed" min="1" max="500" step="1" value="55" />
                    </label>
                    <label class="genplan-editor__field">
                        <span class="genplan-editor__field-label">Периодичность <span class="genplan-editor__hint">сек</span></span>
                        <input type="number" id="genplan_life_period" min="1" max="300" step="1" value="10" />
                    </label>
                    <label class="genplan-editor__field">
                        <span class="genplan-editor__field-label">Цветовой вариант</span>
                        <select id="genplan_life_sprite"></select>
                    </label>
                    <label class="genplan-editor__field">
                        <span class="genplan-editor__field-label">Направление</span>
                        <select id="genplan_life_direction">
                            <option value="1">По линии (от первой точки → к последней)</option>
                            <option value="-1">Обратно (от последней → к первой)</option>
                        </select>
                    </label>
                    <label class="genplan-editor__field genplan-editor__field--check" id="genplan_life_rotate_wrap">
                        <input type="checkbox" id="genplan_life_rotate_variants" checked />
                        <span>Чередовать разные варианты <span class="genplan-editor__hint">появляется тот, кого меньше видно</span></span>
                    </label>
                    <div class="genplan-editor__life-actions">
                        <button type="button" id="genplan_life_apply" class="genplan-editor__btn genplan-editor__btn--primary">Применить</button>
                        <button type="button" id="genplan_life_delete" class="genplan-editor__btn genplan-editor__btn--danger" disabled>Удалить</button>
                    </div>
                    <div id="genplan_life_persp_fields" class="genplan-editor__life-persp-fields" style="display:none;">
                        <label class="genplan-editor__field">
                            <span class="genplan-editor__field-label">Масштаб у основания <span class="genplan-editor__hint">близко</span></span>
                            <input type="number" id="genplan_life_scale_near" min="0.2" max="3" step="0.05" value="1" />
                        </label>
                        <label class="genplan-editor__field">
                            <span class="genplan-editor__field-label">Масштаб у вершины <span class="genplan-editor__hint">далеко</span></span>
                            <input type="number" id="genplan_life_scale_far" min="0.05" max="1.5" step="0.05" value="0.35" />
                        </label>
                        <button type="button" id="genplan_life_persp_clear" class="genplan-editor__btn">Сбросить перспективу</button>
                    </div>
                    <div class="genplan-editor__life-ground">
                        <div class="genplan-editor__field-label">Наклон рендера <span class="genplan-editor__hint">машины/люди как на oblique-плане, не сверху</span></div>
                        <label class="genplan-editor__field">
                            <span class="genplan-editor__field-label">Наклон плоскости <span class="genplan-editor__hint">0 = сверху, 1 = сильный aerial</span></span>
                            <input type="number" id="genplan_life_ground_pitch" min="0" max="1" step="0.05" value="0.32" />
                        </label>
                        <label class="genplan-editor__field">
                            <span class="genplan-editor__field-label">Увод к сходy <span class="genplan-editor__hint">боковой skew</span></span>
                            <input type="number" id="genplan_life_ground_skew" min="0" max="1" step="0.05" value="0.22" />
                        </label>
                        <label class="genplan-editor__field genplan-editor__field--check">
                            <input type="checkbox" id="genplan_life_person_billboard" checked />
                            <span>Люди столбиком (¾), не сверху</span>
                        </label>
                    </div>
                    <div class="genplan-editor__life-birds">
                        <div class="genplan-editor__field-label">Птицы</div>
                        <label class="genplan-editor__field">
                            <span class="genplan-editor__field-label">В стае <span class="genplan-editor__hint">шт, 0 — без стай</span></span>
                            <input type="number" id="genplan_life_bird_flock" min="0" max="12" step="1" value="5" />
                        </label>
                        <label class="genplan-editor__field">
                            <span class="genplan-editor__field-label">Периодичность стаи <span class="genplan-editor__hint">сек</span></span>
                            <input type="number" id="genplan_life_bird_flock_period" min="4" max="300" step="1" value="26" />
                        </label>
                        <label class="genplan-editor__field">
                            <span class="genplan-editor__field-label">Одиночные <span class="genplan-editor__hint">шт за волну</span></span>
                            <input type="number" id="genplan_life_bird_singles" min="0" max="8" step="1" value="2" />
                        </label>
                        <label class="genplan-editor__field">
                            <span class="genplan-editor__field-label">Периодичность одиночных <span class="genplan-editor__hint">сек</span></span>
                            <input type="number" id="genplan_life_bird_single_period" min="3" max="300" step="1" value="15" />
                        </label>
                        <button type="button" id="genplan_life_birds_save" class="genplan-editor__btn">Сохранить настройки птиц</button>
                    </div>
                    <div class="genplan-editor__life-clouds">
                        <div class="genplan-editor__field-label">Облака <span class="genplan-editor__hint">только тени, без светлых пятен; внутри рендера</span></div>
                        <label class="genplan-editor__field genplan-editor__field--check">
                            <input type="checkbox" id="genplan_life_clouds_on" checked />
                            <span>Показывать тени облаков</span>
                        </label>
                        <label class="genplan-editor__field">
                            <span class="genplan-editor__field-label">Количество <span class="genplan-editor__hint">0–4</span></span>
                            <input type="number" id="genplan_life_cloud_count" min="0" max="4" step="1" value="2" />
                        </label>
                        <label class="genplan-editor__field">
                            <span class="genplan-editor__field-label">Сила тени <span class="genplan-editor__hint">0.05–0.85</span></span>
                            <input type="number" id="genplan_life_cloud_opacity" min="0.05" max="0.85" step="0.05" value="0.42" />
                        </label>
                        <label class="genplan-editor__field">
                            <span class="genplan-editor__field-label">Затемнение <span class="genplan-editor__hint">глубина тени</span></span>
                            <input type="number" id="genplan_life_cloud_shade" min="0" max="0.45" step="0.02" value="0.18" />
                        </label>
                        <label class="genplan-editor__field">
                            <span class="genplan-editor__field-label">Скорость <span class="genplan-editor__hint">px/s</span></span>
                            <input type="number" id="genplan_life_cloud_speed" min="0.5" max="20" step="0.5" value="3.5" />
                        </label>
                        <button type="button" id="genplan_life_clouds_save" class="genplan-editor__btn">Сохранить настройки облаков</button>
                    </div>
                    <div class="genplan-editor__life-sun">
                        <div class="genplan-editor__field-label">Освещение <span class="genplan-editor__hint">тени машин и людей; по рендеру сверху-слева (48°), тень вниз-вправо</span></div>
                        <label class="genplan-editor__field">
                            <span class="genplan-editor__field-label">Свет откуда <span class="genplan-editor__hint">0° верх, 90° право, 180° низ</span></span>
                            <input type="number" id="genplan_life_light_from" min="0" max="359" step="5" value="48" />
                        </label>
                        <label class="genplan-editor__field">
                            <span class="genplan-editor__field-label">Длина тени <span class="genplan-editor__hint">px</span></span>
                            <input type="number" id="genplan_life_shadow_len" min="0" max="24" step="1" value="7" />
                        </label>
                        <label class="genplan-editor__field">
                            <span class="genplan-editor__field-label">Плотность тени <span class="genplan-editor__hint">0–0.5</span></span>
                            <input type="number" id="genplan_life_shadow_opacity" min="0" max="0.5" step="0.02" value="0.34" />
                        </label>
                        <div class="genplan-editor__life-sun-preview" id="genplan_life_sun_preview" aria-hidden="true">
                            <span class="genplan-editor__life-sun-preview-label">тень →</span>
                        </div>
                        <button type="button" id="genplan_life_sun_save" class="genplan-editor__btn">Сохранить освещение</button>
                    </div>
                </div>
                <div class="genplan-editor__life-list-wrap">
                    <div class="genplan-editor__field-label">Треки</div>
                    <ul id="genplan_life_list" class="genplan-editor__life-list"></ul>
                </div>
            </div>
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
    maxUploadBytes: <?= (int) $max_upload_bytes ?>
};
</script>
<script src="/sahmatka/template/default/js/genplan_editor.js"></script>
