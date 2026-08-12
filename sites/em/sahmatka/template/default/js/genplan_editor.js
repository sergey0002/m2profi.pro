/**
 * Admin editor: interactive genplan polygons (EM).
 * Patterns synced with sigma facade_editor.js (dirty / Draw / showMessage / upload),
 * without section/floor — parent is kvartalId.
 */
(function () {
    'use strict';

    var cfg = window.GENPLAN_CONFIG;
    if (!cfg || !window.L) {
        return;
    }

    L.Icon.Default.imagePath = '/sahmatka/template/default/libs/leaflet/images/';

    L.drawLocal = {
        draw: {
            toolbar: {
                actions: { title: 'Отменить рисование', text: 'Отмена' },
                finish: { title: 'Завершить рисование', text: 'Готово' },
                undo: { title: 'Удалить последнюю точку', text: 'Удалить точку' },
                buttons: {
                    polyline: 'Линия',
                    polygon: 'Многоугольник объекта',
                    rectangle: 'Прямоугольник',
                    circle: 'Круг',
                    marker: 'Маркер',
                    circlemarker: 'Круглый маркер'
                }
            },
            handlers: {
                circle: {
                    tooltip: { start: 'Нажмите и тяните, чтобы нарисовать круг.' },
                    radius: 'Радиус'
                },
                circlemarker: { tooltip: { start: 'Кликните по карте, чтобы поставить маркер.' } },
                marker: { tooltip: { start: 'Кликните по карте, чтобы поставить маркер.' } },
                polygon: {
                    tooltip: {
                        start: 'Кликните, чтобы начать рисовать объект.',
                        cont: 'Кликайте, чтобы продолжить рисовать.',
                        end: 'Кликните первую точку, чтобы замкнуть фигуру.'
                    }
                },
                polyline: {
                    error: '<strong>Ошибка:</strong> рёбра не должны пересекаться!',
                    tooltip: {
                        start: 'Кликните, чтобы начать линию.',
                        cont: 'Кликайте, чтобы продолжить линию.',
                        end: 'Кликните последнюю точку, чтобы завершить.'
                    }
                },
                rectangle: { tooltip: { start: 'Нажмите и тяните, чтобы нарисовать прямоугольник.' } },
                simpleshape: { tooltip: { end: 'Отпустите кнопку мыши, чтобы закончить.' } }
            }
        },
        edit: {
            toolbar: {
                actions: {
                    save: { title: 'Применить точки на карте (в БД — только после «Сохранить» в форме)', text: 'Применить точки' },
                    cancel: { title: 'Отменить правку точек на карте (не влияет на БД)', text: 'Отменить точки' },
                    clearAll: { title: 'Очистить все слои', text: 'Очистить всё' }
                },
                buttons: {
                    edit: 'Редактировать объекты',
                    editDisabled: 'Нет объектов для редактирования',
                    remove: 'Удалить объекты',
                    removeDisabled: 'Нет объектов для удаления'
                }
            },
            handlers: {
                edit: {
                    tooltip: {
                        text: 'Тяните маркеры, чтобы изменить фигуру.',
                        subtext: '«Применить точки» — на карте. «Сохранить» в форме — в БД.'
                    }
                },
                remove: { tooltip: { text: 'Кликните по объекту, чтобы удалить.' } }
            }
        }
    };

    var messagesEl = document.getElementById('genplan_messages');
    var dirtyActions = document.getElementById('genplan_dirty_actions');
    var saveBtn = document.getElementById('genplan_save');
    var cancelBtn = document.getElementById('genplan_cancel');
    var modePolyBtn = document.getElementById('genplan_mode_poly');
    var modeLabelsBtn = document.getElementById('genplan_mode_labels');
    var modeLifeBtn = document.getElementById('genplan_mode_life');
    var lifeToolsEl = document.getElementById('genplan_life_tools');
    var lifeCarBtn = document.getElementById('genplan_life_car');
    var lifePersonBtn = document.getElementById('genplan_life_person');
    var lifePerspBtn = document.getElementById('genplan_life_persp');
    var lifePanel = document.getElementById('genplan_life_panel');
    var lifeSpeedInput = document.getElementById('genplan_life_speed');
    var lifePeriodInput = document.getElementById('genplan_life_period');
    var lifeSpriteSelect = document.getElementById('genplan_life_sprite');
    var lifeDirectionSelect = document.getElementById('genplan_life_direction');
    var lifeRotateVariantsInput = document.getElementById('genplan_life_rotate_variants');
    var lifeRotateWrap = document.getElementById('genplan_life_rotate_wrap');
    var lifeApplyBtn = document.getElementById('genplan_life_apply');
    var lifeDeleteBtn = document.getElementById('genplan_life_delete');
    var lifeListEl = document.getElementById('genplan_life_list');
    var lifePerspFields = document.getElementById('genplan_life_persp_fields');
    var lifeScaleNearInput = document.getElementById('genplan_life_scale_near');
    var lifeScaleFarInput = document.getElementById('genplan_life_scale_far');
    var lifePerspClearBtn = document.getElementById('genplan_life_persp_clear');
    var groundPitchInput = document.getElementById('genplan_life_ground_pitch');
    var groundSkewInput = document.getElementById('genplan_life_ground_skew');
    var personBillboardInput = document.getElementById('genplan_life_person_billboard');
    var groundSaveBtn = document.getElementById('genplan_life_ground_save');
    var recalcPerspectiveBtn = document.getElementById('genplan_life_recalc_from_homes');
    var birdFlockInput = document.getElementById('genplan_life_bird_flock');
    var birdFlockPeriodInput = document.getElementById('genplan_life_bird_flock_period');
    var birdSinglesInput = document.getElementById('genplan_life_bird_singles');
    var birdSinglePeriodInput = document.getElementById('genplan_life_bird_single_period');
    var birdsSaveBtn = document.getElementById('genplan_life_birds_save');
    var cloudsOnInput = document.getElementById('genplan_life_clouds_on');
    var cloudCountInput = document.getElementById('genplan_life_cloud_count');
    var cloudOpacityInput = document.getElementById('genplan_life_cloud_opacity');
    var cloudShadeInput = document.getElementById('genplan_life_cloud_shade');
    var cloudSpeedInput = document.getElementById('genplan_life_cloud_speed');
    var cloudsSaveBtn = document.getElementById('genplan_life_clouds_save');
    var lightFromInput = document.getElementById('genplan_life_light_from');
    var shadowLenInput = document.getElementById('genplan_life_shadow_len');
    var shadowOpacityInput = document.getElementById('genplan_life_shadow_opacity');
    var sunPreviewEl = document.getElementById('genplan_life_sun_preview');
    var sunSaveBtn = document.getElementById('genplan_life_sun_save');
    var uploadInput = document.getElementById('genplan_upload_input');
    var uploadBtn = document.getElementById('genplan_upload_btn');
    var clearAllBtn = document.getElementById('genplan_clear_all');
    var metaPanel = document.getElementById('genplan_meta_panel');
    var titleInput = document.getElementById('genplan_title_input');
    var contentInput = document.getElementById('genplan_content_input');
    var homeSelect = document.getElementById('genplan_home_select');
    var linkInput = document.getElementById('genplan_link_input');
    var showTitleDesktopInput = document.getElementById('genplan_show_title_desktop');
    var showTitleMobileInput = document.getElementById('genplan_show_title_mobile');
    var showAptLinksInput = document.getElementById('genplan_show_apt_links');
    var addPointBtn = document.getElementById('genplan_add_point');
    var homePreview = document.getElementById('genplan_home_preview');

    var hasImage = !!(cfg.imageUrl && cfg.imageWidth && cfg.imageHeight);
    var currentOverlay = null;

    // Separate button: recalc tilt & perspective using outlined "houses" polygons.
    // We used to anchor it to "Сохранить наклон" button; now that button is removed,
    // anchor via the ground panel container instead.
    if (!recalcPerspectiveBtn) {
        var groundPanel = null;
        try {
            if (groundSaveBtn && groundSaveBtn.parentNode) groundPanel = groundSaveBtn.parentNode;
            else if (groundPitchInput && groundPitchInput.closest) groundPanel = groundPitchInput.closest('.genplan-editor__life-ground');
        } catch (e) { /* ignore */ }
        if (groundPanel && groundPanel.appendChild) {
            recalcPerspectiveBtn = document.createElement('button');
            recalcPerspectiveBtn.id = 'genplan_life_recalc_from_homes';
            recalcPerspectiveBtn.type = 'button';
            recalcPerspectiveBtn.textContent = 'Пересчитать наклон+перспективу';
            recalcPerspectiveBtn.className = 'genplan-editor__btn genplan-life-recalc-btn';
            groundPanel.appendChild(recalcPerspectiveBtn);
        }
    }
    var pendingDeletes = {};
    var flushingDeletes = false;
    var savingGeometry = false;
    var reverting = false;
    var uploadInProgress = false;
    var clearingMarkup = false;
    var drawToolActive = false;
    var deleteModeActive = false;
    var geometryDirty = false;
    var metaDirty = false;
    var editorMode = 'poly'; // 'poly' | 'labels' | 'life'
    var addPointMode = false;
    var selectedLayer = null;
    var labelMarker = null;
    var suppressMetaSync = false;
    var homesLoaded = false;
    var lifeDrawer = null;
    var lifeDrawSpecies = null; // 'car' | 'person'
    var lifeSaving = false;
    var selectedLifeLayer = null;
    var lifeDraft = null; // { layer, species, points } pending apply
    var lifeTracksCache = [];
    var lifeAgentsCache = [];
    var lifePerspective = null;
    var perspMarkers = [];
    var perspPoly = null;
    var perspSaving = false;
    var COLOR_VARIANTS = {
        // светлые кузова, как машины на рендерах генплана
        car: [
            { id: 'white', label: 'Белый', sprite: 'car_c', color: '#e4e8ec' },
            { id: 'gray', label: 'Серебристый', sprite: 'car_a', color: '#c3c9d0' },
            { id: 'graphite', label: 'Графитовый', sprite: 'car_b', color: '#8b939c' },
            { id: 'blue', label: 'Синий', sprite: 'car_b', color: '#7d97b4' },
            { id: 'red', label: 'Красный', sprite: 'car_a', color: '#a84a4a' },
            { id: 'dark', label: 'Тёмный', sprite: 'car_b', color: '#5c636b' },
            { id: 'green', label: 'Зелёный', sprite: 'car_c', color: '#8aa294' }
        ],
        person: [
            { id: 'm_navy', label: 'М / поло тёмное', sprite: 'person_m1', color: '#2f5a8a' },
            { id: 'm_white', label: 'М / поло светлое', sprite: 'person_m1', color: '#e6e0d4' },
            { id: 'm_teal', label: 'М / рубашка бирюза', sprite: 'person_m2', color: '#2f9a96' },
            { id: 'm_sand', label: 'М / рубашка песок', sprite: 'person_m2', color: '#c9a66b' },
            { id: 'w_coral', label: 'Ж / платье коралл', sprite: 'person_w1', color: '#e07068' },
            { id: 'w_mint', label: 'Ж / платье мята', sprite: 'person_w1', color: '#6cbc98' },
            { id: 'w_lilac', label: 'Ж / топ + шорты', sprite: 'person_w2', color: '#9a78b8' },
            { id: 'w_sky', label: 'Ж / топ + шорты голубой', sprite: 'person_w2', color: '#5aa8d8' },
            { id: 'w_sun', label: 'Ж / сарафан жёлтый', sprite: 'person_w3', color: '#e8c040' },
            { id: 'w_rose', label: 'Ж / сарафан розовый', sprite: 'person_w3', color: '#d87898' },
            { id: 'k_red', label: 'Ребёнок / футболка красная', sprite: 'person_k1', color: '#e04848' },
            { id: 'k_blue', label: 'Ребёнок / футболка синяя', sprite: 'person_k1', color: '#3f8ad8' },
            { id: 'k_lime', label: 'Ребёнок / зелёный', sprite: 'person_k2', color: '#6ec050' },
            { id: 'k_orange', label: 'Ребёнок / оранжевый', sprite: 'person_k2', color: '#ef9038' }
        ]
    };
    var LIFE_STYLE = {
        road: { color: '#2f80ed', weight: 3, opacity: 0.95 },
        walk: { color: '#28a745', weight: 3, opacity: 0.95 },
        dog: { color: '#fb8c00', weight: 3, opacity: 0.95 }
    };

    var STYLE_DEFAULT = {
        color: '#7eb6e8',
        weight: 2,
        fillColor: '#7eb6e8',
        fillOpacity: 0,
        opacity: 0.95,
        dashArray: null
    };
    var STYLE_SELECTED = {
        color: '#3388ff',
        weight: 4,
        fillColor: '#3388ff',
        fillOpacity: 0.08,
        opacity: 1,
        dashArray: null
    };

    var MESSAGE_TYPES = ['info', 'success', 'warning', 'error'];
    var MESSAGE_ICONS = { info: 'i', success: '\u2713', warning: '!', error: '\u2715' };
    var UPLOAD_ALLOWED_EXT = ['png', 'jpg', 'jpeg', 'webp'];

    function showMessage(text, type) {
        if (!messagesEl) return;
        if (MESSAGE_TYPES.indexOf(type) === -1) type = 'info';

        messagesEl.innerHTML = '';
        if (!text) {
            messagesEl.className = 'genplan-editor__messages';
            return;
        }

        messagesEl.className = 'genplan-editor__messages is-' + type;

        var icon = document.createElement('span');
        icon.className = 'genplan-editor__messages-icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = MESSAGE_ICONS[type];

        var textEl = document.createElement('span');
        textEl.className = 'genplan-editor__messages-text';
        textEl.textContent = text;

        messagesEl.appendChild(icon);
        messagesEl.appendChild(textEl);
    }

    function clonePoints(points) {
        return (points || []).map(function (p) {
            return [p[0], p[1]];
        });
    }

    var POINTS_EPS = 1e-6;

    function pointsEqual(a, b) {
        a = a || [];
        b = b || [];
        if (a.length !== b.length) return false;
        for (var i = 0; i < a.length; i++) {
            if (Math.abs(a[i][0] - b[i][0]) > POINTS_EPS || Math.abs(a[i][1] - b[i][1]) > POINTS_EPS) {
                return false;
            }
        }
        return true;
    }

    /** Average of vertices — default label anchor when labelX/Y missing. */
    function centroidOfPoints(points) {
        points = points || [];
        if (!points.length) return [0, 0];
        var sx = 0;
        var sy = 0;
        for (var i = 0; i < points.length; i++) {
            sx += Number(points[i][0]) || 0;
            sy += Number(points[i][1]) || 0;
        }
        return [Math.round(sx / points.length), Math.round(sy / points.length)];
    }

    function hasPendingDeletes() {
        return Object.keys(pendingDeletes).length > 0;
    }

    function eachPolygon(fn) {
        polygons.eachLayer(fn);
    }

    function eachPoint(fn) {
        pointLayers.eachLayer(fn);
    }

    function eachObject(fn) {
        eachPolygon(fn);
        eachPoint(fn);
    }

    function isPointLayer(layer) {
        return !!(layer && layer.genplanData && (layer.genplanData.kind === 'point' || !(layer.genplanData.points && layer.genplanData.points.length)));
    }

    function updateAptLinksUi() {
        if (!showAptLinksInput) return;
        var hasHome = !!(homeSelect && homeSelect.value);
        showAptLinksInput.disabled = !hasHome;
        if (!hasHome) {
            showAptLinksInput.checked = false;
            if (selectedLayer && selectedLayer.genplanData) {
                selectedLayer.genplanData.showAptLinks = false;
            }
        }
    }

    function updateAddPointUi() {
        if (!addPointBtn) return;
        addPointBtn.style.display = editorMode === 'labels' ? '' : 'none';
        addPointBtn.classList.toggle('is-active', addPointMode);
    }

    function hasPendingNews() {
        var found = false;
        eachObject(function (layer) {
            if (layer.genplanData && layer.genplanData.isNew) found = true;
        });
        return found;
    }

    function hasLayerGeometryDirty() {
        var found = false;
        eachObject(function (layer) {
            if (layer.genplanData && layer.genplanData.geometryDirty) found = true;
        });
        return found;
    }

    function isDirty() {
        return geometryDirty || metaDirty || hasPendingDeletes() || hasPendingNews() || hasLayerGeometryDirty();
    }

    function updateDirtyUi() {
        if (dirtyActions) {
            dirtyActions.style.display = isDirty() ? 'inline-flex' : 'none';
        }
    }

    function markGeometryDirty() {
        geometryDirty = true;
        updateDirtyUi();
        showMessage('Есть несохранённые правки', 'warning');
    }

    function markMetaDirty() {
        metaDirty = true;
        if (selectedLayer && selectedLayer.genplanData) {
            selectedLayer.genplanData.metaDirty = true;
        }
        updateDirtyUi();
        showMessage('Есть несохранённые правки', 'warning');
    }

    function clearDirtyFlags() {
        geometryDirty = false;
        metaDirty = false;
        eachObject(function (layer) {
            if (layer.genplanData) {
                layer.genplanData.geometryDirty = false;
                layer.genplanData.metaDirty = false;
            }
        });
        updateDirtyUi();
    }

    function isBusy() {
        return savingGeometry || flushingDeletes || reverting || uploadInProgress || clearingMarkup || lifeSaving;
    }

    function setControlsBusy(busy) {
        busy = !!busy;
        if (saveBtn) saveBtn.disabled = busy;
        if (cancelBtn) cancelBtn.disabled = busy;
        if (uploadBtn) uploadBtn.disabled = busy;
        if (clearAllBtn) clearAllBtn.disabled = busy;
        if (modePolyBtn) modePolyBtn.disabled = busy;
        if (modeLabelsBtn) modeLabelsBtn.disabled = busy;
        if (modeLifeBtn) modeLifeBtn.disabled = busy;
        if (lifeCarBtn) lifeCarBtn.disabled = busy;
        if (lifePersonBtn) lifePersonBtn.disabled = busy;
        if (lifePerspBtn) lifePerspBtn.disabled = busy;
        if (lifeApplyBtn) lifeApplyBtn.disabled = busy;
        if (lifeDeleteBtn) lifeDeleteBtn.disabled = busy || !selectedLifeLayer;
        if (lifeSpeedInput) lifeSpeedInput.disabled = busy;
        if (lifePeriodInput) lifePeriodInput.disabled = busy;
        if (lifeSpriteSelect) lifeSpriteSelect.disabled = busy;
        if (lifeDirectionSelect) lifeDirectionSelect.disabled = busy;
        if (lifeRotateVariantsInput) lifeRotateVariantsInput.disabled = busy;
        if (lifeScaleNearInput) lifeScaleNearInput.disabled = busy;
        if (lifeScaleFarInput) lifeScaleFarInput.disabled = busy;
        if (lifePerspClearBtn) lifePerspClearBtn.disabled = busy;
        if (birdFlockInput) birdFlockInput.disabled = busy;
        if (birdFlockPeriodInput) birdFlockPeriodInput.disabled = busy;
        if (birdSinglesInput) birdSinglesInput.disabled = busy;
        if (birdSinglePeriodInput) birdSinglePeriodInput.disabled = busy;
        if (birdsSaveBtn) birdsSaveBtn.disabled = busy;
        if (cloudsOnInput) cloudsOnInput.disabled = busy;
        if (cloudCountInput) cloudCountInput.disabled = busy;
        if (cloudOpacityInput) cloudOpacityInput.disabled = busy;
        if (cloudShadeInput) cloudShadeInput.disabled = busy;
        if (cloudSpeedInput) cloudSpeedInput.disabled = busy;
        if (cloudsSaveBtn) cloudsSaveBtn.disabled = busy;
        if (lightFromInput) lightFromInput.disabled = busy;
        if (shadowLenInput) shadowLenInput.disabled = busy;
        if (shadowOpacityInput) shadowOpacityInput.disabled = busy;
        if (sunSaveBtn) sunSaveBtn.disabled = busy;
        if (groundPitchInput) groundPitchInput.disabled = busy;
        if (groundSkewInput) groundSkewInput.disabled = busy;
        if (personBillboardInput) personBillboardInput.disabled = busy;
        if (groundSaveBtn) groundSaveBtn.disabled = busy;
        if (titleInput) titleInput.disabled = busy;
        if (contentInput) contentInput.disabled = busy;
        if (homeSelect) homeSelect.disabled = busy;
        if (linkInput) linkInput.disabled = busy;
        if (showTitleDesktopInput) showTitleDesktopInput.disabled = busy;
        if (showTitleMobileInput) showTitleMobileInput.disabled = busy;
        if (showAptLinksInput) showAptLinksInput.disabled = busy || !(homeSelect && homeSelect.value);
        if (addPointBtn) addPointBtn.disabled = busy;
    }

    function restorePageTextSelection() {
        try {
            if (L.DomUtil && typeof L.DomUtil.enableTextSelection === 'function') {
                L.DomUtil.enableTextSelection();
                L.DomUtil.enableTextSelection();
            }
        } catch (e) { /* ignore */ }
        var st = document.documentElement && document.documentElement.style;
        if (st) {
            ['userSelect', 'webkitUserSelect', 'MozUserSelect', 'msUserSelect', 'OUserSelect'].forEach(function (k) {
                if (st[k] === 'none') st[k] = '';
            });
        }
    }

    function confirmDialog(message) {
        return window.confirm(message);
    }

    // ─── Map ─────────────────────────────────────────────────

    var mapEl = document.getElementById('genplan_map');
    if (!mapEl) {
        return;
    }

    var map = L.map('genplan_map', {
        crs: L.CRS.Simple,
        minZoom: -8,
        maxZoom: 4,
        zoomSnap: 0.25,
        zoomDelta: 0.25,
        doubleClickZoom: false,
        boxZoom: false,
        keyboard: false,
        attributionControl: false
    });

    var imageBounds = hasImage
        ? L.latLngBounds([[0, 0], [cfg.imageHeight, cfg.imageWidth]])
        : L.latLngBounds([[0, 0], [1000, 1000]]);

    if (hasImage) {
        currentOverlay = L.imageOverlay(cfg.imageUrl, imageBounds).addTo(map);
    }

    function updateMinZoomFromImage() {
        if (!hasImage) return;
        var size = map.getSize();
        if (!size.x || !size.y) return;
        var minZ = map.getBoundsZoom(imageBounds, false);
        if (!isFinite(minZ)) return;
        map.setMinZoom(minZ);
        if (map.getZoom() < minZ) {
            map.setZoom(minZ);
        }
    }

    function setGenplanImage(url, w, h) {
        if (currentOverlay) {
            try { map.removeLayer(currentOverlay); } catch (e) { /* ignore */ }
            currentOverlay = null;
        }
        cfg.imageUrl = url || '';
        cfg.imageWidth = w || 0;
        cfg.imageHeight = h || 0;
        hasImage = !!(url && w && h);
        if (!hasImage) {
            imageBounds = L.latLngBounds([[0, 0], [1000, 1000]]);
            map.setMaxBounds(null);
            syncDrawAvailability();
            return;
        }
        imageBounds = L.latLngBounds([[0, 0], [h, w]]);
        currentOverlay = L.imageOverlay(url, imageBounds).addTo(map);
        map.fitBounds(imageBounds);
        updateMinZoomFromImage();
        map.setMaxBounds(imageBounds.pad(0.5));
        syncDrawAvailability();
    }

    if (hasImage) {
        map.fitBounds(imageBounds);
        updateMinZoomFromImage();
        map.setMaxBounds(imageBounds.pad(0.5));
    }
    map.on('resize', updateMinZoomFromImage);
    map.on('zoom', function () {
        var minZ = map.getMinZoom();
        if (map.getZoom() < minZ) map.setZoom(minZ);
    });

    var polygons = new L.FeatureGroup();
    var pointLayers = new L.FeatureGroup();
    var editableLayers = new L.FeatureGroup();
    var labelGroup = new L.FeatureGroup();
    var lifeLayers = new L.FeatureGroup();
    var perspectiveGroup = new L.FeatureGroup();
    map.addLayer(polygons);
    map.addLayer(pointLayers);
    map.addLayer(editableLayers);
    map.addLayer(labelGroup);
    map.addLayer(lifeLayers);
    map.addLayer(perspectiveGroup);

    var vertexIcon = new L.DivIcon({
        iconSize: new L.Point(8, 8),
        iconAnchor: new L.Point(4, 4),
        className: 'leaflet-div-icon leaflet-editing-icon facade-edit-vertex'
    });
    var vertexTouchIcon = new L.DivIcon({
        iconSize: new L.Point(12, 12),
        iconAnchor: new L.Point(6, 6),
        className: 'leaflet-div-icon leaflet-editing-icon leaflet-touch-icon facade-edit-vertex'
    });
    if (L.Edit && L.Edit.PolyVerticesEdit) {
        L.Edit.PolyVerticesEdit.mergeOptions({ icon: vertexIcon, touchIcon: vertexTouchIcon });
    }
    if (L.Draw && L.Draw.Polyline) {
        L.Draw.Polyline.mergeOptions({ icon: vertexIcon, touchIcon: vertexTouchIcon });
    }

    var drawControl = new L.Control.Draw({
        edit: { featureGroup: editableLayers, remove: true },
        draw: {
            polygon: {
                allowIntersection: false,
                showArea: false,
                guidelineDistance: 12,
                shapeOptions: {
                    color: STYLE_DEFAULT.color,
                    weight: STYLE_DEFAULT.weight,
                    opacity: STYLE_DEFAULT.opacity,
                    fillColor: STYLE_DEFAULT.fillColor,
                    fillOpacity: STYLE_DEFAULT.fillOpacity
                }
            },
            polyline: false,
            rectangle: false,
            circle: false,
            marker: false,
            circlemarker: false
        }
    });
    map.addControl(drawControl);

    /**
     * Leaflet.Draw рисует «резинку» крошечными div в overlayPane —
     * ImageOverlay лежит в том же pane и перекрывает их.
     * Дублируем пунктир SVG в отдельном pane поверх картинки (как в Sigma facade_editor).
     */
    function ensureDrawGuidePane() {
        if (map.getPane('drawGuidePane')) return;
        var pane = map.createPane('drawGuidePane');
        pane.style.zIndex = 550;
        pane.style.pointerEvents = 'none';
    }

    function attachDrawCursorGuide(drawer) {
        if (!drawer) return drawer;
        ensureDrawGuidePane();

        function ensurePreview() {
            if (drawer._gpCursorGuide) return;
            drawer._gpCursorGuide = L.polyline([], {
                pane: 'drawGuidePane',
                color: (drawer.options.shapeOptions && drawer.options.shapeOptions.color) || '#3388ff',
                weight: 2,
                dashArray: '8 8',
                opacity: 0.95,
                interactive: false,
                className: 'genplan-draw-cursor-guide'
            }).addTo(map);
        }

        function clearPreview() {
            if (drawer._gpCursorGuide) {
                try { map.removeLayer(drawer._gpCursorGuide); } catch (err) { /* ignore */ }
                drawer._gpCursorGuide = null;
            }
        }

        function updatePreview() {
            if (!drawer.enabled || !drawer.enabled()) return;
            ensurePreview();
            if (drawer._markers && drawer._markers.length && drawer._currentLatLng) {
                var last = drawer._markers[drawer._markers.length - 1].getLatLng();
                drawer._gpCursorGuide.setLatLngs([last, drawer._currentLatLng]);
            } else {
                drawer._gpCursorGuide.setLatLngs([]);
            }
            // Нативные dash-гиды — в тот же pane поверх картинки
            if (drawer._guidesContainer) {
                var pane = map.getPane('drawGuidePane');
                if (pane && drawer._guidesContainer.parentNode !== pane) {
                    pane.appendChild(drawer._guidesContainer);
                }
            }
        }

        ensurePreview();

        if (!drawer._gpGuideMoveBound) {
            drawer._gpGuideMoveBound = true;
            drawer._gpGuideOnMove = function () { updatePreview(); };
            // После enable (draw:drawstart) — тогда _currentLatLng уже обновлён Draw
            map.on('mousemove', drawer._gpGuideOnMove);
        }

        if (!drawer._gpDisableWrapped) {
            drawer._gpDisableWrapped = true;
            var origDisable = drawer.disable;
            drawer.disable = function () {
                clearPreview();
                if (this._gpGuideOnMove) {
                    map.off('mousemove', this._gpGuideOnMove);
                    this._gpGuideOnMove = null;
                    this._gpGuideMoveBound = false;
                }
                return origDisable.call(this);
            };
        }

        return drawer;
    }

    function forceExitVertexEdit() {
        var toolbars = drawControl && drawControl._toolbars;

        // Сброс недорисованного полигона (draw toolbar), иначе «резинка» остаётся в режиме подписей
        var drawTb = toolbars && toolbars.draw;
        if (drawTb && drawTb._modes) {
            Object.keys(drawTb._modes).forEach(function (mode) {
                var handler = drawTb._modes[mode].handler;
                if (!handler || !handler.enabled || !handler.enabled()) return;
                try { handler.disable(); } catch (e) { /* ignore */ }
            });
        }

        var tb = toolbars && toolbars.edit;
        if (tb && tb._modes) {
            Object.keys(tb._modes).forEach(function (mode) {
                var handler = tb._modes[mode].handler;
                if (!handler || !handler.enabled || !handler.enabled()) return;
                try {
                    if (handler.type === 'remove' && typeof handler.revertLayers === 'function' && !deleteModeActive) {
                        handler.revertLayers();
                    }
                } catch (e) { /* ignore */ }
                try { handler.disable(); } catch (e2) { /* ignore */ }
            });
        }
        if (editorMode !== 'life') {
            drawToolActive = false;
        }
        deleteModeActive = false;
        restorePageTextSelection();
    }

    function disableLifeDrawer() {
        if (lifeDrawer) {
            try { lifeDrawer.disable(); } catch (e) { /* ignore */ }
            lifeDrawer = null;
        }
        lifeDrawSpecies = null;
        if (lifeCarBtn) lifeCarBtn.classList.remove('is-active');
        if (lifePersonBtn) lifePersonBtn.classList.remove('is-active');
        if (editorMode === 'life') {
            drawToolActive = false;
        }
    }

    function setDrawControlVisible(visible) {
        var container = drawControl && drawControl._container;
        if (!container) return;
        container.style.display = visible ? '' : 'none';
    }

    function syncDrawAvailability() {
        var allowDraw = hasImage && editorMode === 'poly';
        setDrawControlVisible(allowDraw);
        if (!allowDraw) {
            forceExitVertexEdit();
        }
        if (editorMode !== 'life') {
            disableLifeDrawer();
        }
        if (!hasImage && editorMode !== 'life') {
            showMessage('Вначале загрузите файл плана', 'info');
        }
    }

    // ─── Layer helpers ───────────────────────────────────────

    function layerToPoints(layer) {
        var latlngs = layer.getLatLngs();
        var ring = latlngs;
        while (Array.isArray(ring) && ring.length && Array.isArray(ring[0]) && typeof ring[0].lat !== 'number') {
            ring = ring[0];
        }
        if (!Array.isArray(ring) || !ring.length) return [];
        return ring.map(function (ll) {
            return [ll.lng, ll.lat];
        });
    }

    function applyDefaultStyle(layer) {
        if (!layer) return;
        if (isPointLayer(layer)) {
            layer.setStyle({
                color: STYLE_DEFAULT.color,
                weight: 2,
                fillColor: STYLE_DEFAULT.fillColor,
                fillOpacity: 0.35
            });
            return;
        }
        layer.setStyle(STYLE_DEFAULT);
    }

    function applySelectedStyle(layer) {
        if (!layer) return;
        if (isPointLayer(layer)) {
            layer.setStyle({
                color: STYLE_SELECTED.color,
                weight: 4,
                fillColor: STYLE_SELECTED.fillColor,
                fillOpacity: 0.55
            });
            return;
        }
        layer.setStyle(STYLE_SELECTED);
        if (layer.bringToFront) layer.bringToFront();
    }

    function refreshAllStyles() {
        eachObject(function (layer) {
            if (layer === selectedLayer) applySelectedStyle(layer);
            else applyDefaultStyle(layer);
        });
    }

    var labelDragBound = false;

    function onLabelMapMouseMove(e) {
        var target = labelMarker;
        if (!target && selectedLayer && isPointLayer(selectedLayer)) {
            target = selectedLayer;
        }
        if (!target || !target._genplanDragging) return;
        target.setLatLng(e.latlng);
    }

    function onLabelMapMouseUp() {
        var target = labelMarker;
        if (!target && selectedLayer && isPointLayer(selectedLayer)) {
            target = selectedLayer;
        }
        if (!target || !target._genplanDragging) return;
        target._genplanDragging = false;
        if (map.dragging && !map.dragging.enabled()) map.dragging.enable();
        if (!selectedLayer || !selectedLayer.genplanData) return;
        var ll = target.getLatLng();
        selectedLayer.genplanData.labelX = Math.round(ll.lng);
        selectedLayer.genplanData.labelY = Math.round(ll.lat);
        markMetaDirty();
    }

    function clearLabelMarker() {
        if (labelMarker) {
            try { labelGroup.removeLayer(labelMarker); } catch (e) { /* ignore */ }
            labelMarker = null;
        }
        labelGroup.clearLayers();
    }

    function ensureLabelDefaults(data, points) {
        if (data.labelX == null || data.labelY == null) {
            var c = centroidOfPoints(points || data.points || []);
            if (data.labelX == null) data.labelX = c[0];
            if (data.labelY == null) data.labelY = c[1];
        }
    }

    function updateLabelMarkerFromSelection() {
        clearLabelMarker();
        if (editorMode !== 'labels' || !selectedLayer || !selectedLayer.genplanData) return;
        if (isPointLayer(selectedLayer)) return;

        var data = selectedLayer.genplanData;
        var pts = layerToPoints(selectedLayer);
        ensureLabelDefaults(data, pts);

        var latlng = L.latLng(data.labelY, data.labelX);
        labelMarker = L.circleMarker(latlng, {
            radius: 7,
            color: '#c0392b',
            weight: 2,
            fillColor: '#e74c3c',
            fillOpacity: 0.9
        });
        labelMarker._genplanDragging = false;

        labelMarker.on('mousedown', function (e) {
            if (editorMode !== 'labels') return;
            labelMarker._genplanDragging = true;
            if (map.dragging && map.dragging.enabled()) map.dragging.disable();
            L.DomEvent.stopPropagation(e);
            if (e.originalEvent) L.DomEvent.preventDefault(e.originalEvent);
        });

        if (!labelDragBound) {
            labelDragBound = true;
            map.on('mousemove', onLabelMapMouseMove);
            map.on('mouseup', onLabelMapMouseUp);
        }

        labelGroup.addLayer(labelMarker);
    }

    function fillMetaForm(layer) {
        suppressMetaSync = true;
        var data = (layer && layer.genplanData) || {};
        if (titleInput) titleInput.value = data.title || '';
        if (contentInput) contentInput.value = data.content || '';
        if (linkInput) linkInput.value = data.linkUrl || '';
        if (showTitleDesktopInput) showTitleDesktopInput.checked = data.showTitleDesktop !== false;
        if (showTitleMobileInput) showTitleMobileInput.checked = data.showTitleMobile !== false;
        if (showAptLinksInput) showAptLinksInput.checked = !!data.showAptLinks;
        if (homeSelect) {
            homeSelect.value = data.homeId ? String(data.homeId) : '';
        }
        updateAptLinksUi();
        if (!data.homeId) {
            if (homePreview) {
                homePreview.innerHTML = '';
                homePreview.style.display = 'none';
            }
        } else {
            fetchHomeAutofill(data.homeId, { onlyPreview: true });
        }
        suppressMetaSync = false;
    }

    function clearMetaForm() {
        suppressMetaSync = true;
        if (titleInput) titleInput.value = '';
        if (contentInput) contentInput.value = '';
        if (linkInput) linkInput.value = '';
        if (showTitleDesktopInput) showTitleDesktopInput.checked = true;
        if (showTitleMobileInput) showTitleMobileInput.checked = true;
        if (showAptLinksInput) {
            showAptLinksInput.checked = false;
            showAptLinksInput.disabled = true;
        }
        if (homeSelect) homeSelect.value = '';
        if (homePreview) {
            homePreview.innerHTML = '';
            homePreview.style.display = 'none';
        }
        suppressMetaSync = false;
    }

    function syncMetaFromFormToLayer() {
        if (!selectedLayer || !selectedLayer.genplanData) return;
        var data = selectedLayer.genplanData;
        data.title = titleInput ? titleInput.value : '';
        data.content = contentInput ? contentInput.value : '';
        data.linkUrl = linkInput ? (linkInput.value || '').trim() : '';
        data.showTitleDesktop = showTitleDesktopInput ? !!showTitleDesktopInput.checked : true;
        data.showTitleMobile = showTitleMobileInput ? !!showTitleMobileInput.checked : true;
        data.showAptLinks = showAptLinksInput && homeSelect && homeSelect.value ? !!showAptLinksInput.checked : false;
        var hid = homeSelect && homeSelect.value ? parseInt(homeSelect.value, 10) : 0;
        data.homeId = hid > 0 ? hid : null;
    }

    function selectLayer(layer) {
        if (selectedLayer === layer) {
            fillMetaForm(layer);
            updateLabelMarkerFromSelection();
            return;
        }
        if (selectedLayer) applyDefaultStyle(selectedLayer);
        selectedLayer = layer || null;
        if (selectedLayer) {
            applySelectedStyle(selectedLayer);
            fillMetaForm(selectedLayer);
        } else {
            clearMetaForm();
        }
        updateLabelMarkerFromSelection();
        updateMetaPanelVisibility();
    }

    function updateMetaPanelVisibility() {
        if (!metaPanel) return;
        var show = editorMode === 'labels';
        metaPanel.style.display = show ? '' : 'none';
    }

    function updateLifePanelVisibility() {
        if (!lifePanel) return;
        lifePanel.style.display = editorMode === 'life' ? '' : 'none';
    }

    // ─── Life tracks (task 12) ───────────────────────────────

    function lifeRoleForSpecies(species) {
        return species === 'person' ? 'walk' : 'road';
    }

    function lifeDefaultsForSpecies(species) {
        if (species === 'person') {
            return { speed: 7, periodSec: 20, colorId: 'gray' };
        }
        return { speed: 34, periodSec: 14, colorId: 'gray' };
    }

    function colorVariantsFor(species) {
        return COLOR_VARIANTS[species === 'person' ? 'person' : 'car'] || COLOR_VARIANTS.car;
    }

    function findColorVariant(species, colorId, sprite, color) {
        var list = colorVariantsFor(species);
        var i;
        if (colorId) {
            for (i = 0; i < list.length; i++) {
                if (list[i].id === colorId) return list[i];
            }
        }
        if (color) {
            for (i = 0; i < list.length; i++) {
                if (String(list[i].color).toLowerCase() === String(color).toLowerCase()) return list[i];
            }
        }
        if (sprite) {
            for (i = 0; i < list.length; i++) {
                if (list[i].sprite === sprite) return list[i];
            }
        }
        return list[0];
    }

    function fillLifeSpriteOptions(species, selectedId) {
        if (!lifeSpriteSelect) return;
        var list = colorVariantsFor(species || 'car');
        var pick = selectedId || (list[0] && list[0].id);
        lifeSpriteSelect.innerHTML = '';
        list.forEach(function (v) {
            var o = document.createElement('option');
            o.value = v.id;
            o.textContent = v.label;
            o.setAttribute('data-sprite', v.sprite);
            o.setAttribute('data-color', v.color);
            lifeSpriteSelect.appendChild(o);
        });
        lifeSpriteSelect.value = pick;
        if (lifeSpriteSelect.value !== pick && list[0]) {
            lifeSpriteSelect.value = list[0].id;
        }
    }

    function setLifeFormDefaults(species) {
        var d = lifeDefaultsForSpecies(species || 'car');
        fillLifeSpriteOptions(species || 'car', d.colorId);
        if (lifeSpeedInput) lifeSpeedInput.value = String(d.speed);
        if (lifePeriodInput) lifePeriodInput.value = String(d.periodSec);
        if (lifeDirectionSelect) lifeDirectionSelect.value = '1';
        if (lifeRotateVariantsInput) lifeRotateVariantsInput.checked = true;
        if (lifeRotateWrap) {
            lifeRotateWrap.style.display = (species === 'car' || species === 'person') ? '' : 'none';
        }
    }

    function selectedColorVariant(species) {
        var id = lifeSpriteSelect ? lifeSpriteSelect.value : '';
        return findColorVariant(species || 'car', id);
    }

    function syncLifeDirectionUi(species, params) {
        params = params || {};
        var dir = params.direction === -1 ? '-1' : '1';
        if (lifeDirectionSelect) lifeDirectionSelect.value = dir;
        if (lifeRotateVariantsInput) {
            lifeRotateVariantsInput.checked = (species === 'car' || species === 'person')
                ? params.rotateVariants !== false
                : false;
        }
        if (lifeRotateWrap) {
            lifeRotateWrap.style.display = (species === 'car' || species === 'person') ? '' : 'none';
        }
    }

    function discardLifeDraft() {
        if (lifeDraft && lifeDraft.layer) {
            try { lifeLayers.removeLayer(lifeDraft.layer); } catch (e) { /* ignore */ }
            try { map.removeLayer(lifeDraft.layer); } catch (e2) { /* ignore */ }
        }
        lifeDraft = null;
    }

    function disableLifeLayerEdit(layer) {
        if (!layer) return;
        if (layer.editing && layer.editing.enabled && layer.editing.enabled()) {
            try { layer.editing.disable(); } catch (e) { /* ignore */ }
        }
    }

    function enableLifeLayerEdit(layer) {
        if (!layer || !layer.editing || !layer.editing.enable) return;
        try {
            layer.editing.enable();
        } catch (e) { /* ignore */ }
    }

    function lifeLayerPoints(layer) {
        if (!layer || !layer.getLatLngs) return null;
        var latlngs = layer.getLatLngs();
        if (!latlngs || !latlngs.length) return null;
        // Leaflet may nest rings for Polygon; Polyline is flat
        if (latlngs[0] && latlngs[0].lat == null && Array.isArray(latlngs[0])) {
            latlngs = latlngs[0];
        }
        return latlngs.map(function (ll) {
            return [Math.round(ll.lng), Math.round(ll.lat)];
        });
    }

    function clearLifeSelection() {
        if (selectedLifeLayer) {
            disableLifeLayerEdit(selectedLifeLayer);
            var role = (selectedLifeLayer.lifeData && selectedLifeLayer.lifeData.role) || 'road';
            var st = LIFE_STYLE[role] || LIFE_STYLE.road;
            selectedLifeLayer.setStyle(st);
        }
        selectedLifeLayer = null;
        if (lifeDeleteBtn) lifeDeleteBtn.disabled = true;
        renderLifeListActive();
    }

    function selectLifeLayer(layer) {
        clearLifeSelection();
        if (!layer || !layer.lifeData) return;
        selectedLifeLayer = layer;
        layer.setStyle({
            color: '#111',
            weight: 4,
            opacity: 1
        });
        if (layer.bringToFront) layer.bringToFront();
        enableLifeLayerEdit(layer);
        var agent = layer.lifeData.agent || null;
        var species = (agent && agent.species) || (layer.lifeData.role === 'walk' ? 'person' : 'car');
        var params = (agent && agent.params) || {};
        var variant = findColorVariant(species, params.colorId, agent && agent.spriteKey, params.color);
        fillLifeSpriteOptions(species, variant.id);
        if (lifeSpeedInput) lifeSpeedInput.value = String((agent && agent.speed) || lifeDefaultsForSpecies(species).speed);
        if (lifePeriodInput) {
            var ms = (agent && agent.periodMs) || (lifeDefaultsForSpecies(species).periodSec * 1000);
            lifePeriodInput.value = String(Math.max(1, Math.round(ms / 1000)));
        }
        syncLifeDirectionUi(species, params);
        if (lifeDeleteBtn) lifeDeleteBtn.disabled = !layer.lifeData.id;
        renderLifeListActive();
        showMessage('Трек выбран — тащите углы линии, правьте скорость/направление/цвет и «Применить». «Удалить» — убрать путь.', 'info');
    }

    function agentForTrack(trackId) {
        var i;
        for (i = 0; i < lifeAgentsCache.length; i++) {
            if (String(lifeAgentsCache[i].trackId) === String(trackId)) {
                return lifeAgentsCache[i];
            }
        }
        return null;
    }

    function pointsToLatLngs(points) {
        return (points || []).map(function (p) {
            return L.latLng(p[1], p[0]);
        });
    }

    function addLifeTrackLayer(track, agent) {
        if (!track || !track.points || track.points.length < 2) return null;
        var role = track.role || 'road';
        var st = LIFE_STYLE[role] || LIFE_STYLE.road;
        var layer = L.polyline(pointsToLatLngs(track.points), {
            color: st.color,
            weight: st.weight,
            opacity: st.opacity,
            interactive: true
        });
        layer.lifeData = {
            id: track.id,
            role: role,
            title: track.title || '',
            points: clonePoints(track.points),
            agent: agent || null
        };
        layer.on('click', function (e) {
            if (editorMode !== 'life') return;
            if (drawToolActive) return;
            if (e.originalEvent) L.DomEvent.stopPropagation(e.originalEvent);
            discardLifeDraft();
            selectLifeLayer(layer);
        });
        lifeLayers.addLayer(layer);
        return layer;
    }

    function rebuildLifeLayers() {
        if (selectedLifeLayer) disableLifeLayerEdit(selectedLifeLayer);
        lifeLayers.clearLayers();
        selectedLifeLayer = null;
        lifeTracksCache.forEach(function (track) {
            addLifeTrackLayer(track, agentForTrack(track.id));
        });
        renderLifeList();
        if (lifeDeleteBtn) lifeDeleteBtn.disabled = true;
    }

    function renderLifeListActive() {
        if (!lifeListEl) return;
        var items = lifeListEl.querySelectorAll('li');
        var selId = selectedLifeLayer && selectedLifeLayer.lifeData ? String(selectedLifeLayer.lifeData.id) : '';
        Array.prototype.forEach.call(items, function (li) {
            if (li.getAttribute('data-id') === selId) li.classList.add('is-active');
            else li.classList.remove('is-active');
        });
    }

    function renderLifeList() {
        if (!lifeListEl) return;
        lifeListEl.innerHTML = '';
        if (!lifeTracksCache.length) {
            var empty = document.createElement('li');
            empty.textContent = 'Пока нет треков';
            empty.style.cursor = 'default';
            lifeListEl.appendChild(empty);
            return;
        }
        lifeTracksCache.forEach(function (track) {
            var agent = agentForTrack(track.id);
            var li = document.createElement('li');
            li.setAttribute('data-id', String(track.id));
            var role = track.role || 'road';
            var label = (role === 'walk' ? 'Человек' : 'Машина') + ' #' + track.id;
            if (agent) {
                var dir = (agent.params && agent.params.direction === -1) ? '←' : '→';
                label += ' · ' + dir + ' · ' + Math.round(agent.speed) + 'px/s · ' + Math.round(agent.periodMs / 1000) + 'с';
            }
            var span = document.createElement('span');
            span.className = 'role-' + role;
            span.textContent = label;
            li.appendChild(span);
            li.addEventListener('click', function () {
                var found = null;
                lifeLayers.eachLayer(function (layer) {
                    if (layer.lifeData && String(layer.lifeData.id) === String(track.id)) found = layer;
                });
                if (found) selectLifeLayer(found);
            });
            lifeListEl.appendChild(li);
        });
        renderLifeListActive();
    }

    function loadLifeList() {
        return fetch(cfg.ajaxBase + '&act=life_list&kvartal_id=' + encodeURIComponent(cfg.kvartalId), {
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res || !res.success) {
                    showMessage((res && res.message) || 'Не удалось загрузить жизнь', 'error');
                    return;
                }
                lifeTracksCache = Array.isArray(res.tracks) ? res.tracks : [];
                lifeAgentsCache = Array.isArray(res.agents) ? res.agents : [];
                lifePerspective = res.perspective || (res.settings && res.settings.perspective) || null;
                fillBirdSettings(res.settings);
                fillCloudSettings(res.settings);
                fillSunSettings(res.settings);
                fillGroundSettings(res.settings);
                rebuildLifeLayers();
                if (editorMode === 'life') {
                    if (lifePerspective && lifePerspective.enabled && lifePerspective.points) {
                        mountPerspectiveUi(lifePerspective, false);
                    } else {
                        clearPerspectiveUi();
                        updatePerspFieldsVisibility();
                    }
                }
            })
            .catch(function () {
                showMessage('Ошибка сети при загрузке жизни', 'error');
            });
    }

    function fillBirdSettings(settings) {
        var s = settings || {};
        if (birdFlockInput && s.birdFlockSize != null) birdFlockInput.value = String(s.birdFlockSize);
        if (birdFlockPeriodInput && s.birdFlockPeriodMs != null) {
            birdFlockPeriodInput.value = String(Math.round(Number(s.birdFlockPeriodMs) / 1000));
        }
        if (birdSinglesInput && s.birdSingles != null) birdSinglesInput.value = String(s.birdSingles);
        if (birdSinglePeriodInput && s.birdSinglePeriodMs != null) {
            birdSinglePeriodInput.value = String(Math.round(Number(s.birdSinglePeriodMs) / 1000));
        }
    }

    function fillCloudSettings(settings) {
        var s = settings || {};
        if (cloudsOnInput) cloudsOnInput.checked = s.clouds !== false;
        if (cloudCountInput && s.cloudCount != null) cloudCountInput.value = String(s.cloudCount);
        if (cloudOpacityInput && s.cloudOpacity != null) cloudOpacityInput.value = String(s.cloudOpacity);
        if (cloudShadeInput && s.cloudShade != null) cloudShadeInput.value = String(s.cloudShade);
        if (cloudSpeedInput && s.cloudSpeed != null) cloudSpeedInput.value = String(s.cloudSpeed);
    }

    function updateSunPreview() {
        if (!sunPreviewEl) return;
        var deg = parseFloat(lightFromInput ? lightFromInput.value : 48);
        var len = parseFloat(shadowLenInput ? shadowLenInput.value : 7);
        if (!isFinite(deg)) deg = 48;
        if (!isFinite(len)) len = 7;
        var rad = (deg * Math.PI) / 180;
        var dx = Math.sin(rad) * len;
        var dy = Math.cos(rad) * len;
        sunPreviewEl.style.setProperty('--sun-dx', dx.toFixed(1) + 'px');
        sunPreviewEl.style.setProperty('--sun-dy', dy.toFixed(1) + 'px');
    }

    function fillSunSettings(settings) {
        var s = settings || {};
        if (lightFromInput && s.lightFromDeg != null) lightFromInput.value = String(Math.round(Number(s.lightFromDeg)));
        if (shadowLenInput && s.shadowLen != null) shadowLenInput.value = String(s.shadowLen);
        if (shadowOpacityInput && s.shadowOpacity != null) shadowOpacityInput.value = String(s.shadowOpacity);
        updateSunPreview();
    }

    function fillGroundSettings(settings) {
        var s = settings || {};
        if (groundPitchInput && s.groundPitch != null) groundPitchInput.value = String(s.groundPitch);
        if (groundSkewInput && s.groundSkew != null) groundSkewInput.value = String(s.groundSkew);
        if (personBillboardInput) personBillboardInput.checked = s.personBillboard !== false;
    }

    function saveGroundSettings() {
        if (isBusy()) return;
        var body = new FormData();
        body.append('kvartal_id', cfg.kvartalId);
        body.append('groundPitch', String(parseFloat(groundPitchInput ? groundPitchInput.value : 0.32) || 0));
        body.append('groundSkew', String(parseFloat(groundSkewInput ? groundSkewInput.value : 0.22) || 0));
        body.append('personBillboard', personBillboardInput && personBillboardInput.checked ? '1' : '0');

        lifeSaving = true;
        setControlsBusy(true);
        fetch(cfg.ajaxBase + '&act=life_save_settings', {
            method: 'POST',
            credentials: 'same-origin',
            body: body
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                lifeSaving = false;
                setControlsBusy(false);
                if (!res || !res.success) {
                    showMessage((res && res.message) || 'Не удалось сохранить наклон', 'error');
                    return;
                }
                fillBirdSettings(res.settings);
                fillCloudSettings(res.settings);
                fillSunSettings(res.settings);
                fillGroundSettings(res.settings);
                showMessage('Наклон рендера сохранён', 'success');
            })
            .catch(function () {
                lifeSaving = false;
                setControlsBusy(false);
                showMessage('Ошибка сети при сохранении наклона', 'error');
            });
    }

    function saveBirdSettings() {
        if (isBusy()) return;
        var body = new FormData();
        body.append('kvartal_id', cfg.kvartalId);
        body.append('birdFlockSize', String(parseInt(birdFlockInput ? birdFlockInput.value : 5, 10) || 0));
        body.append('birdFlockPeriodMs', String((parseInt(birdFlockPeriodInput ? birdFlockPeriodInput.value : 26, 10) || 26) * 1000));
        body.append('birdSingles', String(parseInt(birdSinglesInput ? birdSinglesInput.value : 2, 10) || 0));
        body.append('birdSinglePeriodMs', String((parseInt(birdSinglePeriodInput ? birdSinglePeriodInput.value : 15, 10) || 15) * 1000));

        lifeSaving = true;
        setControlsBusy(true);
        fetch(cfg.ajaxBase + '&act=life_save_settings', {
            method: 'POST',
            credentials: 'same-origin',
            body: body
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                lifeSaving = false;
                setControlsBusy(false);
                if (!res || !res.success) {
                    showMessage((res && res.message) || 'Не удалось сохранить настройки птиц', 'error');
                    return;
                }
                fillBirdSettings(res.settings);
                fillCloudSettings(res.settings);
                fillSunSettings(res.settings);
                fillGroundSettings(res.settings);
                showMessage('Настройки птиц сохранены', 'success');
            })
            .catch(function () {
                lifeSaving = false;
                setControlsBusy(false);
                showMessage('Ошибка сети при сохранении настроек птиц', 'error');
            });
    }

    function saveCloudSettings() {
        if (isBusy()) return;
        var body = new FormData();
        body.append('kvartal_id', cfg.kvartalId);
        body.append('clouds', cloudsOnInput && cloudsOnInput.checked ? '1' : '0');
        body.append('cloudCount', String(parseInt(cloudCountInput ? cloudCountInput.value : 2, 10) || 0));
        body.append('cloudOpacity', String(parseFloat(cloudOpacityInput ? cloudOpacityInput.value : 0.42) || 0.42));
        body.append('cloudShade', String(parseFloat(cloudShadeInput ? cloudShadeInput.value : 0.18) || 0));
        body.append('cloudSpeed', String(parseFloat(cloudSpeedInput ? cloudSpeedInput.value : 3.5) || 3.5));

        lifeSaving = true;
        setControlsBusy(true);
        fetch(cfg.ajaxBase + '&act=life_save_settings', {
            method: 'POST',
            credentials: 'same-origin',
            body: body
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                lifeSaving = false;
                setControlsBusy(false);
                if (!res || !res.success) {
                    showMessage((res && res.message) || 'Не удалось сохранить настройки облаков', 'error');
                    return;
                }
                fillBirdSettings(res.settings);
                fillCloudSettings(res.settings);
                fillSunSettings(res.settings);
                fillGroundSettings(res.settings);
                showMessage('Настройки облаков сохранены', 'success');
            })
            .catch(function () {
                lifeSaving = false;
                setControlsBusy(false);
                showMessage('Ошибка сети при сохранении настроек облаков', 'error');
            });
    }

    function saveSunSettings() {
        if (isBusy()) return;
        var body = new FormData();
        body.append('kvartal_id', cfg.kvartalId);
        body.append('lightFromDeg', String(parseFloat(lightFromInput ? lightFromInput.value : 48) || 48));
        body.append('shadowLen', String(parseFloat(shadowLenInput ? shadowLenInput.value : 7) || 0));
        body.append('shadowOpacity', String(parseFloat(shadowOpacityInput ? shadowOpacityInput.value : 0.34) || 0));

        lifeSaving = true;
        setControlsBusy(true);
        fetch(cfg.ajaxBase + '&act=life_save_settings', {
            method: 'POST',
            credentials: 'same-origin',
            body: body
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                lifeSaving = false;
                setControlsBusy(false);
                if (!res || !res.success) {
                    showMessage((res && res.message) || 'Не удалось сохранить освещение', 'error');
                    return;
                }
                fillBirdSettings(res.settings);
                fillCloudSettings(res.settings);
                fillSunSettings(res.settings);
                fillGroundSettings(res.settings);
                showMessage('Освещение сохранено', 'success');
            })
            .catch(function () {
                lifeSaving = false;
                setControlsBusy(false);
                showMessage('Ошибка сети при сохранении освещения', 'error');
            });
    }

    function defaultPerspectivePoints() {
        var w = cfg.imageWidth || 1000;
        var h = cfg.imageHeight || 1000;
        return [
            [Math.round(w * 0.5), Math.round(h * 0.78)],
            [Math.round(w * 0.12), Math.round(h * 0.12)],
            [Math.round(w * 0.88), Math.round(h * 0.12)]
        ];
    }

    function clamp(n, a, b) {
        if (!isFinite(n)) return a;
        if (n < a) return a;
        if (n > b) return b;
        return n;
    }

    /**
     * Auto default groundPitch/groundSkew from outlined "houses" polygons.
     * Uses only what is already mounted on the map (polygons FeatureGroup).
     */
    function autoGroundFromHomesPolygons(persp) {
        if (!persp || !(persp.points && persp.points.length >= 3)) return null;

        var V = persp.points[0];
        var L = persp.points[1];
        var R = persp.points[2];

        var yFar = Number(V[1]);
        var yNear = (Number(L[1]) + Number(R[1])) / 2;
        if (!isFinite(yFar) || !isFinite(yNear)) return null;

        var denom = Math.abs(yNear - yFar);
        if (!(denom > 0)) denom = 1;

        var houses = [];
        polygons.eachLayer(function (layer) {
            try {
                if (!layer || !layer.genplanData) return;
                if (layer.genplanData.kind !== 'polygon') return;
                // Houses come from DB polygons where home_id is set.
                if (layer.genplanData.homeId == null) return;
                if (Number(layer.genplanData.homeId) <= 0) return;
                var pts = layer.genplanData.points || [];
                if (!Array.isArray(pts) || pts.length < 3) return;
                var minX = Infinity, maxX = -Infinity, minY = Infinity, maxY = -Infinity;
                for (var i = 0; i < pts.length; i++) {
                    var x = Number(pts[i][0]);
                    var y = Number(pts[i][1]);
                    if (!isFinite(x) || !isFinite(y)) continue;
                    if (x < minX) minX = x;
                    if (x > maxX) maxX = x;
                    if (y < minY) minY = y;
                    if (y > maxY) maxY = y;
                }
                if (!(isFinite(minX) && isFinite(maxX) && isFinite(minY) && isFinite(maxY))) return;
                var cx = (minX + maxX) / 2;
                var cy = (minY + maxY) / 2;
                houses.push({ cx: cx, cy: cy });
            } catch (e) { /* ignore */ }
        });

        if (!houses.length) return null;

        var yMin = Infinity, yMax = -Infinity, xSum = 0;
        for (var hI = 0; hI < houses.length; hI++) {
            if (houses[hI].cy < yMin) yMin = houses[hI].cy;
            if (houses[hI].cy > yMax) yMax = houses[hI].cy;
            xSum += houses[hI].cx;
        }
        var xAvg = xSum / houses.length;
        if (!isFinite(yMin) || !isFinite(yMax)) return null;

        // How much houses span in depth relative to the perspective triangle.
        var spread = (yMax - yMin) / denom; // ~0..+
        spread = clamp(spread, 0, 1);

        // Map spread → pitch:
        // 0 = сверху (слишком "вид сверху"),
        // 1 = сильно oblique.
        // Для наших сцен обычно нужен ощутимый oblique, поэтому нижняя граница выше.
        var groundPitch = clamp(0.45 + 0.45 * spread, 0.25, 0.95);

        // Skew strength from lateral offset of houses vs the vanishing X.
        var vanX = Number(V[0]);
        var nearSpanX = Math.abs(Number(R[0]) - Number(L[0]));
        nearSpanX = Math.max(80, nearSpanX * 0.55); // stable scale
        var avgDx = 0;
        for (var dI = 0; dI < houses.length; dI++) {
            avgDx += Math.abs(houses[dI].cx - vanX);
        }
        avgDx = avgDx / houses.length;
        var skewAmt = clamp(avgDx / nearSpanX, 0, 1);

        var groundSkew = clamp(0.08 + 0.48 * skewAmt * (0.45 + 0.55 * spread), 0, 0.95);

        return { groundPitch: groundPitch, groundSkew: groundSkew };
    }

    function collectHomesBoundsFromPolygons() {
        var minX = Infinity,
            maxX = -Infinity,
            minY = Infinity,
            maxY = -Infinity,
            sumX = 0,
            count = 0;
        polygons.eachLayer(function (layer) {
            try {
                if (!layer || !layer.genplanData) return;
                if (layer.genplanData.kind !== 'polygon') return;
                if (layer.genplanData.homeId == null) return;
                if (Number(layer.genplanData.homeId) <= 0) return;
                var pts = layer.genplanData.points || [];
                if (!Array.isArray(pts) || pts.length < 3) return;
                var localMinX = Infinity,
                    localMaxX = -Infinity,
                    localMinY = Infinity,
                    localMaxY = -Infinity;
                for (var i = 0; i < pts.length; i++) {
                    var x = Number(pts[i][0]);
                    var y = Number(pts[i][1]);
                    if (!isFinite(x) || !isFinite(y)) continue;
                    if (x < localMinX) localMinX = x;
                    if (x > localMaxX) localMaxX = x;
                    if (y < localMinY) localMinY = y;
                    if (y > localMaxY) localMaxY = y;
                }
                if (!(isFinite(localMinX) && isFinite(localMaxX) && isFinite(localMinY) && isFinite(localMaxY))) return;
                var cx = (localMinX + localMaxX) / 2;
                minX = Math.min(minX, localMinX);
                maxX = Math.max(maxX, localMaxX);
                minY = Math.min(minY, localMinY);
                maxY = Math.max(maxY, localMaxY);
                sumX += cx;
                count += 1;
            } catch (e) { /* ignore */ }
        });

        if (!isFinite(minX) || !isFinite(maxX) || !isFinite(minY) || !isFinite(maxY) || count <= 0) return null;

        return {
            minX: minX,
            maxX: maxX,
            minY: minY,
            maxY: maxY,
            xAvg: sumX / count,
            count: count
        };
    }

    function autoPerspectiveFromHomesPolygons() {
        var bounds = collectHomesBoundsFromPolygons();
        if (!bounds) return null;

        var imgW = cfg.imageWidth || 1000;
        var imgH = cfg.imageHeight || 1000;

        // CRS: y=0 у низа, чем больше y — тем выше по плану.
        var yNear = bounds.minY;
        var yFar = bounds.maxY;
        if (!(isFinite(yNear) && isFinite(yFar))) return null;

        var ySpan = Math.max(1, yFar - yNear);

        // Чуть расширяем треугольник по Y, чтобы масштаб "садился" на дома.
        yFar = clamp(yFar + ySpan * 0.25, 0, imgH * 2);
        yNear = clamp(yNear - ySpan * 0.05, -imgH * 0.2, imgH * 2);

        var spanX = Math.max(80, bounds.maxX - bounds.minX);
        var xLeft = clamp(bounds.minX - spanX * 0.35, -imgW * 0.5, imgW * 1.5);
        var xRight = clamp(bounds.maxX + spanX * 0.35, -imgW * 0.5, imgW * 1.5);

        var xVan = bounds.xAvg;

        return {
            points: [
                [xVan, yFar], // vanishing (far)
                [xLeft, yNear], // left near
                [xRight, yNear] // right near
            ]
        };
    }

    function perspCornerIcon(label) {
        return L.divIcon({
            className: 'genplan-persp-vertex',
            html: '<span>' + label + '</span>',
            iconSize: [22, 22],
            iconAnchor: [11, 11]
        });
    }

    function clearPerspectiveUi() {
        perspMarkers.forEach(function (m) {
            try { perspectiveGroup.removeLayer(m); } catch (e) { /* ignore */ }
        });
        perspMarkers = [];
        if (perspPoly) {
            try { perspectiveGroup.removeLayer(perspPoly); } catch (e2) { /* ignore */ }
            perspPoly = null;
        }
        if (lifePerspBtn) lifePerspBtn.classList.remove('is-active');
    }

    function readPerspectivePointsFromUi() {
        if (perspMarkers.length < 3) return null;
        return perspMarkers.map(function (m) {
            var ll = m.getLatLng();
            return [Math.round(ll.lng), Math.round(ll.lat)];
        });
    }

    function syncPerspectivePolyFromMarkers() {
        var pts = readPerspectivePointsFromUi();
        if (!pts || !perspPoly) return;
        perspPoly.setLatLngs(pointsToLatLngs(pts));
    }

    function updatePerspFieldsVisibility() {
        var show = !!(lifePerspective && lifePerspective.enabled && editorMode === 'life');
        if (lifePerspFields) lifePerspFields.style.display = show ? '' : 'none';
        if (show) {
            if (lifeScaleNearInput) lifeScaleNearInput.value = String(lifePerspective.scaleNear != null ? lifePerspective.scaleNear : 1);
            if (lifeScaleFarInput) lifeScaleFarInput.value = String(lifePerspective.scaleFar != null ? lifePerspective.scaleFar : 0.35);
        }
        if (lifePerspBtn) {
            if (show) lifePerspBtn.classList.add('is-active');
            else lifePerspBtn.classList.remove('is-active');
        }
    }

    function mountPerspectiveUi(persp, doSave) {
        if (!hasImage) {
            showMessage('Вначале загрузите файл плана', 'info');
            return;
        }
        clearPerspectiveUi();
        var points = (persp && persp.points && persp.points.length >= 3)
            ? persp.points.slice(0, 3)
            : defaultPerspectivePoints();
        var scaleNear = persp && persp.scaleNear != null ? persp.scaleNear : 1;
        var scaleFar = persp && persp.scaleFar != null ? persp.scaleFar : 0.35;
        lifePerspective = {
            enabled: true,
            points: points,
            scaleNear: scaleNear,
            scaleFar: scaleFar
        };
        perspPoly = L.polygon(pointsToLatLngs(points), {
            color: '#e67e22',
            weight: 2,
            opacity: 0.95,
            fillColor: '#e67e22',
            fillOpacity: 0.08,
            dashArray: '6 4',
            interactive: false,
            className: 'genplan-persp-triangle'
        });
        perspectiveGroup.addLayer(perspPoly);
        var labels = ['V', 'L', 'R'];
        points.forEach(function (p, idx) {
            var marker = L.marker(L.latLng(p[1], p[0]), {
                draggable: true,
                icon: perspCornerIcon(labels[idx] || String(idx + 1)),
                zIndexOffset: 800
            });
            marker.on('drag', function () {
                syncPerspectivePolyFromMarkers();
            });
            marker.on('dragend', function () {
                savePerspectiveFromUi();
            });
            perspectiveGroup.addLayer(marker);
            perspMarkers.push(marker);
        });
        updatePerspFieldsVisibility();
        if (doSave) savePerspectiveFromUi();
        showMessage('Перспектива: тащите углы V (сход) / L / R (основание). Масштаб — в панели.', 'info');
    }

    function savePerspectiveFromUi() {
        if (perspSaving || editorMode !== 'life') return;
        var points = readPerspectivePointsFromUi();
        if (!points || points.length < 3) return;
        var scaleNear = lifeScaleNearInput ? parseFloat(lifeScaleNearInput.value) : 1;
        var scaleFar = lifeScaleFarInput ? parseFloat(lifeScaleFarInput.value) : 0.35;
        if (!(scaleNear > 0) || !(scaleFar > 0)) {
            showMessage('Масштабы перспективы должны быть > 0', 'error');
            return;
        }
        lifePerspective = {
            enabled: true,
            points: points,
            scaleNear: scaleNear,
            scaleFar: scaleFar
        };
        perspSaving = true;
        var body = new URLSearchParams({
            kvartal_id: String(cfg.kvartalId),
            enabled: '1',
            points: JSON.stringify(points),
            scaleNear: String(scaleNear),
            scaleFar: String(scaleFar)
        });
        fetch(cfg.ajaxBase + '&act=life_save_perspective', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString()
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                perspSaving = false;
                if (!res || !res.success) {
                    showMessage((res && res.message) || 'Не удалось сохранить перспективу', 'error');
                    return;
                }
                lifePerspective = res.perspective || lifePerspective;
                updatePerspFieldsVisibility();

                // Auto default ground tilt based on outlined houses (DB polygons mounted on map).
                try {
                    var auto = autoGroundFromHomesPolygons(lifePerspective);
                    if (auto && groundPitchInput && groundSkewInput) {
                        groundPitchInput.value = String(auto.groundPitch.toFixed(3)).replace(/\.?0+$/, '');
                        groundSkewInput.value = String(auto.groundSkew.toFixed(3)).replace(/\.?0+$/, '');
                        // Persist as new defaults.
                        saveGroundSettings();
                    }
                } catch (e2) { /* ignore */ }

                // Remove perspective triangle/lines overlay after saving (keep stored values).
                try { clearPerspectiveUi(); } catch (e3) { /* ignore */ }
                try { updatePerspFieldsVisibility(); } catch (e4) { /* ignore */ }
            })
            .catch(function () {
                perspSaving = false;
                showMessage('Ошибка сети при сохранении перспективы', 'error');
            });
    }

    function startPerspectiveTool() {
        if (!hasImage || editorMode !== 'life' || isBusy()) return;
        disableLifeDrawer();
        discardLifeDraft();
        clearLifeSelection();
        if (lifePerspective && lifePerspective.enabled && lifePerspective.points) {
            mountPerspectiveUi(lifePerspective, false);
        } else {
            mountPerspectiveUi(null, true);
        }
    }

    function clearPerspective() {
        if (isBusy() || perspSaving) return;
        if (!confirmDialog('Сбросить перспективу?')) return;
        clearPerspectiveUi();
        lifePerspective = null;
        updatePerspFieldsVisibility();
        perspSaving = true;
        var body = new URLSearchParams({
            kvartal_id: String(cfg.kvartalId),
            enabled: '0'
        });
        fetch(cfg.ajaxBase + '&act=life_save_perspective', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString()
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                perspSaving = false;
                if (!res || !res.success) {
                    showMessage((res && res.message) || 'Не удалось сбросить', 'error');
                    return;
                }
                showMessage('Перспектива сброшена', 'success');
            })
            .catch(function () {
                perspSaving = false;
                showMessage('Ошибка сети', 'error');
            });
    }

    function startLifeDraw(species) {
        if (!hasImage || editorMode !== 'life' || isBusy() || lifeSaving) return;
        forceExitVertexEdit();
        disableLifeDrawer();
        discardLifeDraft();
        clearLifeSelection();
        lifeDrawSpecies = species === 'person' ? 'person' : 'car';
        setLifeFormDefaults(lifeDrawSpecies);
        var role = lifeRoleForSpecies(lifeDrawSpecies);
        var st = LIFE_STYLE[role] || LIFE_STYLE.road;
        lifeDrawer = new L.Draw.Polyline(map, {
            allowIntersection: true,
            showLength: false,
            guidelineDistance: 12,
            shapeOptions: {
                color: st.color,
                weight: st.weight,
                opacity: st.opacity
            }
        });
        attachDrawCursorGuide(lifeDrawer);
        lifeDrawer.enable();
        drawToolActive = true;
        if (lifeCarBtn) {
            if (lifeDrawSpecies === 'car') lifeCarBtn.classList.add('is-active');
            else lifeCarBtn.classList.remove('is-active');
        }
        if (lifePersonBtn) {
            if (lifeDrawSpecies === 'person') lifePersonBtn.classList.add('is-active');
            else lifePersonBtn.classList.remove('is-active');
        }
        showMessage((lifeDrawSpecies === 'person' ? 'Человек' : 'Машина') + ': клики по вершинам, ≥2 точки, без замыкания', 'info');
    }

    function handleLifeCreated(layer) {
        var species = lifeDrawSpecies || 'car';
        disableLifeDrawer();
        drawToolActive = false;
        var points = layerToPoints(layer);
        if (points.length < 2) {
            showMessage('Нужно минимум 2 точки линии', 'warning');
            return;
        }
        var role = lifeRoleForSpecies(species);
        var st = LIFE_STYLE[role] || LIFE_STYLE.road;
        var poly = L.polyline(pointsToLatLngs(points), {
            color: st.color,
            weight: st.weight,
            opacity: st.opacity,
            dashArray: '6 4',
            interactive: true
        });
        poly.lifeData = {
            id: null,
            role: role,
            title: '',
            points: clonePoints(points),
            agent: null,
            isDraft: true,
            species: species
        };
        lifeLayers.addLayer(poly);
        lifeDraft = { layer: poly, species: species, points: clonePoints(points) };
        setLifeFormDefaults(species);
        selectedLifeLayer = poly;
        poly.setStyle({ color: '#111', weight: 4, opacity: 1, dashArray: '6 4' });
        if (lifeDeleteBtn) lifeDeleteBtn.disabled = true;
        showMessage('Черновик пути — укажите скорость/период и нажмите «Применить»', 'warning');
    }

    function readLifeForm() {
        var species = (lifeDraft && lifeDraft.species)
            || (selectedLifeLayer && selectedLifeLayer.lifeData && selectedLifeLayer.lifeData.agent && selectedLifeLayer.lifeData.agent.species)
            || (selectedLifeLayer && selectedLifeLayer.lifeData && selectedLifeLayer.lifeData.role === 'walk' ? 'person' : 'car');
        var variant = selectedColorVariant(species);
        var speed = lifeSpeedInput ? parseFloat(lifeSpeedInput.value) : 0;
        var periodSec = lifePeriodInput ? parseFloat(lifePeriodInput.value) : 0;
        var direction = lifeDirectionSelect && lifeDirectionSelect.value === '-1' ? -1 : 1;
        var rotateVariants = (species === 'car' || species === 'person')
            ? !!(lifeRotateVariantsInput && lifeRotateVariantsInput.checked)
            : false;
        return {
            species: species,
            speed: speed,
            periodMs: Math.round(periodSec * 1000),
            sprite: variant.sprite,
            color: variant.color,
            colorId: variant.id,
            direction: direction,
            rotateVariants: rotateVariants
        };
    }

    function applyLifeForm() {
        if (isBusy() || lifeSaving || editorMode !== 'life') return;
        var form = readLifeForm();
        if (!(form.speed > 0) || form.periodMs < 1000) {
            showMessage('Укажите скорость (>0) и периодичность (≥1 с)', 'error');
            return;
        }

        if (lifeDraft && lifeDraft.layer) {
            lifeSaving = true;
            setControlsBusy(true);
            var body = new URLSearchParams({
                kvartal_id: String(cfg.kvartalId),
                species: lifeDraft.species,
                points: JSON.stringify(lifeDraft.points),
                speed: String(form.speed),
                period_ms: String(form.periodMs),
                sprite_key: form.sprite,
                color: form.color,
                direction: String(form.direction),
                rotate_variants: form.rotateVariants ? '1' : '0'
            });
            // color_id for UI restore
            body.set('color_id', form.colorId);
            fetch(cfg.ajaxBase + '&act=life_save_path_agent', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: body.toString()
            })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    lifeSaving = false;
                    setControlsBusy(false);
                    if (!res || !res.success) {
                        showMessage((res && res.message) || 'Не удалось сохранить путь', 'error');
                        return;
                    }
                    discardLifeDraft();
                    showMessage('Путь сохранён', 'success');
                    loadLifeList().then(function () {
                        if (res.track && res.track.id) {
                            var found = null;
                            lifeLayers.eachLayer(function (layer) {
                                if (layer.lifeData && String(layer.lifeData.id) === String(res.track.id)) found = layer;
                            });
                            if (found) selectLifeLayer(found);
                        }
                    });
                })
                .catch(function () {
                    lifeSaving = false;
                    setControlsBusy(false);
                    showMessage('Ошибка сети при сохранении пути', 'error');
                });
            return;
        }

        if (!selectedLifeLayer || !selectedLifeLayer.lifeData || !selectedLifeLayer.lifeData.id) {
            showMessage('Сначала нарисуйте путь (Машина / Человек)', 'warning');
            return;
        }
        var agent = selectedLifeLayer.lifeData.agent;
        if (!agent || !agent.id) {
            showMessage('У трека нет агента', 'error');
            return;
        }
        var editedPoints = lifeLayerPoints(selectedLifeLayer);
        if (!editedPoints || editedPoints.length < 2) {
            showMessage('У линии должно быть минимум 2 точки', 'error');
            return;
        }
        var nextParams = Object.assign({}, agent.params || {}, {
            color: form.color,
            colorId: form.colorId,
            direction: form.direction,
            rotateVariants: form.rotateVariants
        });
        lifeSaving = true;
        setControlsBusy(true);
        var keepId = selectedLifeLayer.lifeData.id;
        var role = selectedLifeLayer.lifeData.role || (agent.species === 'person' ? 'walk' : 'road');

        // сначала геометрия трека (углы можно править), потом параметры агента
        var trackBody = new URLSearchParams({
            kvartal_id: String(cfg.kvartalId),
            track_id: String(keepId),
            role: role,
            points: JSON.stringify(editedPoints)
        });
        fetch(cfg.ajaxBase + '&act=life_save_track', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: trackBody.toString()
        })
            .then(function (r) { return r.json(); })
            .then(function (trackRes) {
                if (!trackRes || !trackRes.success) {
                    throw new Error((trackRes && trackRes.message) || 'Не удалось сохранить линию');
                }
                var upd = new URLSearchParams({
                    kvartal_id: String(cfg.kvartalId),
                    agent_id: String(agent.id),
                    track_id: String(keepId),
                    species: agent.species || (role === 'walk' ? 'person' : 'car'),
                    speed: String(form.speed),
                    period_ms: String(form.periodMs),
                    sprite_key: form.sprite,
                    enabled: '1',
                    params_json: JSON.stringify(nextParams)
                });
                return fetch(cfg.ajaxBase + '&act=life_save_agent', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                    body: upd.toString()
                }).then(function (r2) { return r2.json(); });
            })
            .then(function (res) {
                lifeSaving = false;
                setControlsBusy(false);
                if (!res || !res.success) {
                    showMessage((res && res.message) || 'Не удалось обновить агента', 'error');
                    return;
                }
                showMessage('Путь и параметры сохранены', 'success');
                loadLifeList().then(function () {
                    var found = null;
                    lifeLayers.eachLayer(function (layer) {
                        if (layer.lifeData && String(layer.lifeData.id) === String(keepId)) found = layer;
                    });
                    if (found) selectLifeLayer(found);
                });
            })
            .catch(function (err) {
                lifeSaving = false;
                setControlsBusy(false);
                showMessage((err && err.message) || 'Ошибка сети при сохранении', 'error');
            });
    }

    function deleteSelectedLife() {
        if (isBusy() || lifeSaving || editorMode !== 'life') return;
        if (lifeDraft) {
            discardLifeDraft();
            clearLifeSelection();
            showMessage('Черновик удалён', 'info');
            return;
        }
        if (!selectedLifeLayer || !selectedLifeLayer.lifeData || !selectedLifeLayer.lifeData.id) {
            showMessage('Выберите сохранённый трек', 'warning');
            return;
        }
        if (!confirmDialog('Удалить этот путь и агента?')) return;
        var trackId = selectedLifeLayer.lifeData.id;
        lifeSaving = true;
        setControlsBusy(true);
        var body = new URLSearchParams({
            kvartal_id: String(cfg.kvartalId),
            track_id: String(trackId)
        });
        fetch(cfg.ajaxBase + '&act=life_delete_track', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: body.toString()
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                lifeSaving = false;
                setControlsBusy(false);
                if (!res || !res.success) {
                    showMessage((res && res.message) || 'Не удалось удалить', 'error');
                    return;
                }
                clearLifeSelection();
                showMessage('Путь удалён', 'success');
                loadLifeList();
            })
            .catch(function () {
                lifeSaving = false;
                setControlsBusy(false);
                showMessage('Ошибка сети при удалении', 'error');
            });
    }

    function bindObjectClick(layer) {
        layer.on('click', function (e) {
            if (editorMode !== 'labels') return;
            if (drawToolActive || deleteModeActive || addPointMode) return;
            if (e.originalEvent) {
                L.DomEvent.stopPropagation(e.originalEvent);
            }
            selectLayer(layer);
            showMessage('Объект выбран — заполните подпись / дом / ссылку', 'info');
        });
    }

    function bindPolygonClick(layer) {
        bindObjectClick(layer);
    }

    function defaultGenplanData(item) {
        return {
            id: item.id || null,
            kind: item.kind || ((item.points && item.points.length) ? 'polygon' : 'point'),
            homeId: item.homeId != null ? item.homeId : null,
            title: item.title || '',
            content: item.content || '',
            showTitleDesktop: item.showTitleDesktop !== false,
            showTitleMobile: item.showTitleMobile !== false,
            showAptLinks: !!item.showAptLinks,
            linkUrl: item.linkUrl || '',
            labelX: item.labelX != null ? item.labelX : null,
            labelY: item.labelY != null ? item.labelY : null,
            points: clonePoints(item.points || []),
            isNew: !item.id,
            geometryDirty: !!item.geometryDirty,
            metaDirty: false
        };
    }

    function addPointLayer(item) {
        var data = defaultGenplanData(item);
        data.kind = 'point';
        data.points = [];
        if (data.labelX == null || data.labelY == null) {
            data.labelX = Math.round((cfg.imageWidth || 0) / 2);
            data.labelY = Math.round((cfg.imageHeight || 0) / 2);
        }
        var latlng = L.latLng(data.labelY, data.labelX);
        var layer = L.circleMarker(latlng, {
            radius: 8,
            color: '#3388ff',
            weight: 2,
            fillColor: '#3388ff',
            fillOpacity: 0.35
        });
        layer.genplanData = data;
        layer.edited = false;
        bindObjectClick(layer);
        layer.on('mousedown', function (e) {
            if (editorMode !== 'labels') return;
            layer._genplanDragging = true;
            if (map.dragging && map.dragging.enabled()) map.dragging.disable();
            L.DomEvent.stopPropagation(e);
            if (e.originalEvent) L.DomEvent.preventDefault(e.originalEvent);
        });
        pointLayers.addLayer(layer);
        editableLayers.addLayer(layer);
        return layer;
    }

    function addPolygonLayer(item) {
        var points = item.points || [];
        var latlngs = points.map(function (p) {
            return [p[1], p[0]];
        });
        var layer = L.polygon(latlngs, {
            color: STYLE_DEFAULT.color,
            weight: STYLE_DEFAULT.weight,
            fillColor: STYLE_DEFAULT.fillColor,
            fillOpacity: STYLE_DEFAULT.fillOpacity,
            opacity: STYLE_DEFAULT.opacity
        });

        var data = defaultGenplanData(item);
        data.kind = 'polygon';
        ensureLabelDefaults(data, points);
        layer.genplanData = data;
        layer.edited = false;
        bindPolygonClick(layer);
        polygons.addLayer(layer);
        editableLayers.addLayer(layer);
        applyDefaultStyle(layer);
        return layer;
    }

    function removeLayerFromMap(layer) {
        if (!layer) return;
        if (layer.editing && layer.editing.enabled && layer.editing.enabled()) {
            try { layer.editing.disable(); } catch (e) { /* ignore */ }
        }
        if (selectedLayer === layer) {
            selectedLayer = null;
            clearLabelMarker();
            clearMetaForm();
        }
        if (polygons.hasLayer(layer)) polygons.removeLayer(layer);
        if (pointLayers.hasLayer(layer)) pointLayers.removeLayer(layer);
        if (editableLayers.hasLayer(layer)) editableLayers.removeLayer(layer);
    }

    function clearAllMapPolygons() {
        var all = [];
        eachObject(function (l) { all.push(l); });
        all.forEach(removeLayerFromMap);
        editableLayers.clearLayers();
        selectedLayer = null;
        pendingDeletes = {};
        clearLabelMarker();
        clearMetaForm();
    }

    function stashDeletedLayer(layer, confirmed) {
        if (!layer || !layer.genplanData) return false;
        if (layer.genplanData.isNew || !layer.genplanData.id) return false;
        var id = String(layer.genplanData.id);
        pendingDeletes[id] = {
            id: layer.genplanData.id,
            confirmed: !!confirmed
        };
        return true;
    }

    function commitLeafletDeleteIfNeeded() {
        var toolbars = drawControl && drawControl._toolbars;
        var tb = toolbars && toolbars.edit;
        if (!tb || !tb._modes) return;

        Object.keys(tb._modes).forEach(function (mode) {
            var handler = tb._modes[mode].handler;
            if (!handler || !handler.enabled || !handler.enabled()) return;
            if (!handler._deletedLayers || !handler._deletedLayers.eachLayer) return;

            var any = false;
            handler._deletedLayers.eachLayer(function (layer) {
                if (stashDeletedLayer(layer, true)) any = true;
            });
            try {
                if (typeof handler.save === 'function') handler.save();
            } catch (e) { /* ignore */ }
            try { handler.disable(); } catch (e2) { /* ignore */ }

            deleteModeActive = false;
            if (any) markGeometryDirty();
        });
    }

    // ─── Modes ───────────────────────────────────────────────

    function setMode(mode) {
        if (mode !== 'labels' && mode !== 'life') mode = 'poly';
        if (editorMode === mode) {
            updateModeUi();
            return;
        }
        if (editorMode === 'life') {
            disableLifeDrawer();
            discardLifeDraft();
            clearLifeSelection();
            clearPerspectiveUi();
        }
        if (mode === 'labels') {
            forceExitVertexEdit();
            syncMetaFromFormToLayer();
        } else if (mode === 'life') {
            forceExitVertexEdit();
            syncMetaFromFormToLayer();
            clearLabelMarker();
            selectLayer(null);
        } else {
            syncMetaFromFormToLayer();
            clearLabelMarker();
        }
        editorMode = mode;
        if (mode !== 'labels') {
            addPointMode = false;
        }
        updateModeUi();
        updateAddPointUi();
        syncDrawAvailability();
        if (mode === 'labels') {
            ensureHomesOptions();
            if (selectedLayer) {
                fillMetaForm(selectedLayer);
                updateLabelMarkerFromSelection();
            }
            showMessage('Режим подписей: клик по объекту — форма; «Точка» — маркер без полигона', 'info');
        } else if (mode === 'life') {
            if (!hasImage) {
                showMessage('Вначале загрузите файл плана', 'info');
            } else {
                showMessage('Режим «Жизнь»: Машина / Человек — open-линия (≥2 точки). Сохраняется сразу.', 'info');
            }
            loadLifeList();
        } else {
            if (!hasImage) {
                showMessage('Вначале загрузите файл плана', 'info');
            } else {
                showMessage('Режим полигонов: рисуйте и правьте фигуры', 'info');
            }
        }
    }

    function updateModeUi() {
        if (modePolyBtn) {
            if (editorMode === 'poly') modePolyBtn.classList.add('is-active');
            else modePolyBtn.classList.remove('is-active');
        }
        if (modeLabelsBtn) {
            if (editorMode === 'labels') modeLabelsBtn.classList.add('is-active');
            else modeLabelsBtn.classList.remove('is-active');
        }
        if (modeLifeBtn) {
            if (editorMode === 'life') modeLifeBtn.classList.add('is-active');
            else modeLifeBtn.classList.remove('is-active');
        }
        if (lifeToolsEl) {
            lifeToolsEl.style.display = editorMode === 'life' ? '' : 'none';
        }
        updateMetaPanelVisibility();
        updateLifePanelVisibility();
        updateAddPointUi();
        refreshAllStyles();
        var el = map.getContainer();
        if (el) {
            if (editorMode === 'labels') L.DomUtil.addClass(el, 'is-pick-mode');
            else L.DomUtil.removeClass(el, 'is-pick-mode');
            if (editorMode === 'life') L.DomUtil.addClass(el, 'is-life-mode');
            else L.DomUtil.removeClass(el, 'is-life-mode');
        }
        if (editorMode === 'life') {
            lifeLayers.eachLayer(function (layer) {
                if (layer.bringToFront) layer.bringToFront();
            });
        }
    }

    // ─── Homes options / autofill ────────────────────────────

    function ensureHomesOptions() {
        if (homesLoaded || !homeSelect) return Promise.resolve();
        return fetch(cfg.ajaxBase + '&act=homes_options&kvartal_id=' + encodeURIComponent(cfg.kvartalId), {
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res || !res.success || !Array.isArray(res.homes)) return;
                var cur = homeSelect.value;
                homeSelect.innerHTML = '';
                var empty = document.createElement('option');
                empty.value = '';
                empty.textContent = '— не выбран —';
                homeSelect.appendChild(empty);
                res.homes.forEach(function (h) {
                    var opt = document.createElement('option');
                    opt.value = String(h.id);
                    opt.textContent = h.title || ('#' + h.id);
                    homeSelect.appendChild(opt);
                });
                if (cur) homeSelect.value = cur;
                homesLoaded = true;
            })
            .catch(function () { /* ignore */ });
    }

    function fetchHomeAutofill(homeId, opts) {
        opts = opts || {};
        if (!homeId) {
            if (homePreview) {
                homePreview.innerHTML = '';
                homePreview.style.display = 'none';
            }
            return;
        }
        fetch(
            cfg.ajaxBase + '&act=home_autofill&kvartal_id=' + encodeURIComponent(cfg.kvartalId) +
            '&home_id=' + encodeURIComponent(homeId),
            { credentials: 'same-origin' }
        )
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res || !res.success) {
                    if (homePreview) {
                        homePreview.textContent = (res && res.message) || 'Не удалось загрузить данные дома';
                        homePreview.style.display = '';
                    }
                    return;
                }
                if (homePreview) {
                    var parts = [];
                    if (res.statusText) parts.push(res.statusText);
                    if (res.metaDelivery) parts.push(res.metaDelivery);
                    if (res.metaAddress) parts.push(res.metaAddress);
                    if (res.floorsLabel) parts.push(res.floorsLabel);
                    if (res.sectionsLabel) parts.push(res.sectionsLabel);
                    homePreview.innerHTML = '';
                    var label = document.createElement('div');
                    label.className = 'genplan-editor__home-preview-title';
                    label.textContent = 'На сайте (живые данные дома)';
                    homePreview.appendChild(label);
                    parts.forEach(function (p) {
                        var row = document.createElement('div');
                        row.className = 'genplan-editor__home-preview-row';
                        row.textContent = p;
                        homePreview.appendChild(row);
                    });
                    homePreview.style.display = '';
                }
            })
            .catch(function () {
                if (homePreview) {
                    homePreview.textContent = 'Ошибка сети при загрузке данных дома';
                    homePreview.style.display = '';
                }
            });
    }

    // ─── Save / delete / cancel / load ───────────────────────

    function deletePolygonApi(id) {
        id = String(id);
        return fetch(cfg.ajaxBase + '&act=delete_polygon', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                genplan_polygon_id: id,
                kvartal_id: String(cfg.kvartalId)
            }).toString()
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res && res.success) {
                    delete pendingDeletes[id];
                    return { ok: true };
                }
                return { ok: false, message: (res && res.message) || 'Ошибка удаления' };
            })
            .catch(function () {
                return { ok: false, message: 'Ошибка сети при удалении' };
            });
    }

    function flushPendingDeletes() {
        var ids = Object.keys(pendingDeletes);
        if (!ids.length) return Promise.resolve({ ok: true });
        if (flushingDeletes) return Promise.resolve({ ok: false, message: 'Удаление уже идёт' });

        flushingDeletes = true;
        var okCount = 0;
        var failMessages = [];
        var i = 0;

        function next() {
            if (i >= ids.length) {
                flushingDeletes = false;
                if (failMessages.length) {
                    return { ok: false, message: failMessages.join('; ') };
                }
                return { ok: true, count: okCount };
            }
            var id = ids[i++];
            return deletePolygonApi(id).then(function (res) {
                if (res.ok) okCount++;
                else failMessages.push('id ' + id + ': ' + (res.message || 'ошибка'));
                return next();
            });
        }
        return next();
    }

    function savePolygon(layer) {
        var data = layer.genplanData || {};
        var points;
        var isPoint = isPointLayer(layer);

        if (isPoint) {
            points = [];
            var ll = layer.getLatLng ? layer.getLatLng() : null;
            if (ll) {
                data.labelX = Math.round(ll.lng);
                data.labelY = Math.round(ll.lat);
            }
            if (data.labelX == null || data.labelY == null) {
                return Promise.resolve({ ok: false, message: 'Укажите позицию точки' });
            }
        } else {
            points = layerToPoints(layer);
            if (points.length < 3) {
                return Promise.resolve({ ok: false, message: 'Меньше 3 точек' });
            }
            ensureLabelDefaults(data, points);
        }

        var homeId = data.homeId ? parseInt(data.homeId, 10) : 0;
        var title = data.title || '';
        var plain = String(title).replace(/<[^>]*>/g, '').replace(/\s+/g, ' ').trim();
        if (homeId < 1 && plain === '') {
            return Promise.resolve({ ok: false, message: 'Укажите заголовок (или выберите дом)' });
        }

        var body = new URLSearchParams({
            kvartal_id: String(cfg.kvartalId),
            genplan_polygon_id: (data.id && !data.isNew) ? String(data.id) : '0',
            points: JSON.stringify(points),
            title: title,
            content: data.content || '',
            show_title_desktop: data.showTitleDesktop !== false ? '1' : '0',
            show_title_mobile: data.showTitleMobile !== false ? '1' : '0',
            show_apt_links: data.showAptLinks ? '1' : '0',
            home_id: homeId > 0 ? String(homeId) : '',
            link_url: data.linkUrl || '',
            label_x: data.labelX != null ? String(data.labelX) : '',
            label_y: data.labelY != null ? String(data.labelY) : ''
        });

        return fetch(cfg.ajaxBase + '&act=save_polygon', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res.success) {
                    return { ok: false, message: res.message || 'Ошибка сохранения' };
                }
                data.id = res.id;
                data.points = clonePoints(points);
                data.isNew = false;
                data.geometryDirty = false;
                data.metaDirty = false;
                layer.edited = false;
                return { ok: true, id: res.id };
            })
            .catch(function () {
                return { ok: false, message: 'Ошибка сети при сохранении' };
            });
    }

    function layerNeedsSave(layer) {
        if (!layer || !layer.genplanData) return false;
        var d = layer.genplanData;
        if (d.isNew || d.geometryDirty || d.metaDirty) return true;
        if (isPointLayer(layer)) {
            var ll = layer.getLatLng ? layer.getLatLng() : null;
            if (ll && (Math.round(ll.lng) !== d.labelX || Math.round(ll.lat) !== d.labelY)) return true;
            return false;
        }
        var current = layerToPoints(layer);
        if (!pointsEqual(current, d.points)) return true;
        return false;
    }

    function saveAllChanges() {
        if (isBusy()) {
            return Promise.resolve({ ok: false, message: 'Операция уже выполняется' });
        }

        syncMetaFromFormToLayer();
        savingGeometry = true;
        setControlsBusy(true);
        showMessage('Сохранение…', 'info');
        commitLeafletDeleteIfNeeded();

        return flushPendingDeletes().then(function (delRes) {
            if (delRes && delRes.ok === false) {
                forceExitVertexEdit();
                savingGeometry = false;
                setControlsBusy(false);
                showMessage(delRes.message || 'Ошибка удаления', 'error');
                updateDirtyUi();
                return { ok: false, message: delRes.message };
            }

            forceExitVertexEdit();

            var toSave = [];
            eachObject(function (layer) {
                if (layerNeedsSave(layer)) toSave.push(layer);
            });

            if (!toSave.length && !hasPendingDeletes()) {
                savingGeometry = false;
                clearDirtyFlags();
                setControlsBusy(false);
                showMessage('Сохранено', 'success');
                return { ok: true };
            }

            var okAll = true;
            var failMsg = '';
            var chain = Promise.resolve();

            toSave.forEach(function (layer) {
                chain = chain.then(function () {
                    if (!okAll) return;
                    return savePolygon(layer).then(function (res) {
                        if (!res.ok) {
                            okAll = false;
                            failMsg = res.message || 'Ошибка сохранения';
                        }
                    });
                });
            });

            return chain.then(function () {
                savingGeometry = false;
                setControlsBusy(false);
                if (okAll) {
                    clearDirtyFlags();
                    showMessage('Сохранено', 'success');
                    return { ok: true };
                }
                showMessage(failMsg || 'Ошибка при сохранении', 'error');
                updateDirtyUi();
                return { ok: false, message: failMsg };
            });
        }, function (err) {
            savingGeometry = false;
            setControlsBusy(false);
            throw err;
        });
    }

    function remountFromList(list) {
        forceExitVertexEdit();
        clearAllMapPolygons();
        pendingDeletes = {};
        if (!Array.isArray(list)) list = [];
        list.forEach(function (item) {
            if (item.kind === 'point' || !item.points || !item.points.length) {
                addPointLayer(item);
            } else {
                addPolygonLayer(item);
            }
        });
        clearDirtyFlags();
        selectedLayer = null;
        clearLabelMarker();
        clearMetaForm();
        refreshAllStyles();
        updateMetaPanelVisibility();
    }

    function loadPolygons() {
        return fetch(cfg.ajaxBase + '&act=get_polygons&kvartal_id=' + encodeURIComponent(cfg.kvartalId), {
            credentials: 'same-origin'
        })
            .then(function (r) { return r.json(); })
            .then(function (list) {
                remountFromList(list);
                restorePageTextSelection();
                if (!hasImage) {
                    showMessage('Вначале загрузите файл плана', 'info');
                }
                return { ok: true };
            })
            .catch(function () {
                showMessage('Не удалось загрузить полигоны (проверьте миграцию БД)', 'error');
                return { ok: false };
            });
    }

    function cancelChanges() {
        if (isBusy()) return Promise.resolve({ ok: false });
        reverting = true;
        setControlsBusy(true);
        showMessage('Отмена правок…', 'info');
        return loadPolygons().then(function (res) {
            reverting = false;
            setControlsBusy(false);
            if (res && res.ok) {
                showMessage('Правки отменены', 'info');
            }
            return res;
        }, function (err) {
            reverting = false;
            setControlsBusy(false);
            throw err;
        });
    }

    // ─── Upload / clear ──────────────────────────────────────

    function localExtOk(fileName) {
        var m = /\.([a-z0-9]+)$/i.exec(fileName || '');
        if (!m) return false;
        return UPLOAD_ALLOWED_EXT.indexOf(m[1].toLowerCase()) !== -1;
    }

    function doUploadImage(file) {
        uploadInProgress = true;
        setControlsBusy(true);
        showMessage('Загрузка плана…', 'info');

        var body = new FormData();
        body.append('kvartal_id', String(cfg.kvartalId));
        body.append('file', file);

        return fetch(cfg.ajaxBase + '&act=upload_image', {
            method: 'POST',
            credentials: 'same-origin',
            body: body
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                uploadInProgress = false;
                setControlsBusy(false);
                if (!res || !res.success) {
                    showMessage((res && res.message) || 'Ошибка загрузки файла', 'error');
                    return { ok: false };
                }
                setGenplanImage(res.imageUrl, res.imageWidth, res.imageHeight);
                showMessage('План сохранён (.' + (res.ext || '') + ')', 'success');
                return { ok: true };
            })
            .catch(function () {
                uploadInProgress = false;
                setControlsBusy(false);
                showMessage('Сеть/сервер недоступен — попробуйте ещё раз', 'error');
                return { ok: false };
            });
    }

    function uploadGenplanImage(file) {
        if (isBusy() || !file) return;

        if (!localExtOk(file.name)) {
            showMessage('Неподдерживаемый формат файла. Разрешены: PNG, JPG, WEBP', 'error');
            return;
        }
        if (cfg.maxUploadBytes && file.size > cfg.maxUploadBytes) {
            showMessage(
                'Файл больше ' + Math.round(cfg.maxUploadBytes / 1024 / 1024) + ' МБ — уменьшите размер и попробуйте снова',
                'error'
            );
            return;
        }

        function go() {
            if (hasImage) {
                var proceed = confirmDialog(
                    'Файл плана уже есть. Он будет перезаписан.\n' +
                    'Существующие объекты придётся сверить (полигоны не удалятся).\n\n' +
                    'Продолжить?'
                );
                if (!proceed) return;
            }
            doUploadImage(file);
        }

        if (!isDirty()) {
            go();
            return;
        }

        var action = confirmDialog(
            'Есть несохранённые изменения.\n\n' +
            'OK — сохранить, затем загрузить.\n' +
            'Отмена — прервать загрузку (сначала сохраните или отмените правки).'
        );
        if (action) {
            saveAllChanges().then(function (res) {
                if (res && res.ok) go();
            });
        }
    }

    function doClearAll() {
        clearingMarkup = true;
        setControlsBusy(true);
        showMessage('Очистка плана…', 'info');

        return fetch(cfg.ajaxBase + '&act=clear_all', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ kvartal_id: String(cfg.kvartalId) }).toString()
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                clearingMarkup = false;
                setControlsBusy(false);
                if (!res || !res.success) {
                    showMessage((res && res.message) || 'Ошибка очистки плана', 'error');
                    return { ok: false };
                }
                forceExitVertexEdit();
                clearAllMapPolygons();
                clearDirtyFlags();
                setGenplanImage('', 0, 0);
                showMessage(
                    'План очищен: полигонов ' + (res.cleared || 0) +
                    ', файлов ' + (res.removedFiles || 0) + '.',
                    'success'
                );
                return { ok: true };
            })
            .catch(function () {
                clearingMarkup = false;
                setControlsBusy(false);
                showMessage('Ошибка сети при очистке плана', 'error');
                return { ok: false };
            });
    }

    function clearWholeGenplan() {
        if (isBusy()) return;

        var hasAny = false;
        eachObject(function () { hasAny = true; });
        if (!hasAny && !hasImage) {
            showMessage('Нечего очищать — нет ни файла плана, ни объектов', 'info');
            return;
        }

        var proceed = confirmDialog(
            'Очистить весь интерактивный план?\n' +
            'Будут удалены все полигоны И файл изображения.\n\n' +
            'Это действие нельзя отменить.'
        );
        if (proceed) doClearAll();
    }

    // ─── Draw events ─────────────────────────────────────────

    map.on('draw:drawstart', function () {
        if (!hasImage || editorMode !== 'poly') {
            forceExitVertexEdit();
            showMessage('Вначале загрузите файл плана', 'info');
            return;
        }
        drawToolActive = true;
        // Тулбар Leaflet стартует Draw.Polygon — вешаем видимую «резинку» поверх фона
        var toolbars = drawControl && drawControl._toolbars;
        var modes = toolbars && toolbars.draw && toolbars.draw._modes;
        var handler = modes && modes.polygon && modes.polygon.handler;
        if (handler && handler.enabled && handler.enabled()) {
            attachDrawCursorGuide(handler);
        }
    });
    map.on('draw:drawstop', function () {
        drawToolActive = false;
        restorePageTextSelection();
    });
    map.on('draw:editstart', function () {
        drawToolActive = true;
    });
    map.on('draw:deletestart', function () {
        drawToolActive = true;
        deleteModeActive = true;
        showMessage('Удаление: клик по полигону → галочка ✓. В БД — только после «Сохранить» в форме.', 'info');
    });
    map.on(L.Draw.Event.DELETED, function (e) {
        var any = false;
        e.layers.eachLayer(function (layer) {
            if (stashDeletedLayer(layer, true)) any = true;
            if (selectedLayer === layer) {
                selectedLayer = null;
                clearLabelMarker();
                clearMetaForm();
            }
        });
        if (any || e.layers.getLayers().length) {
            markGeometryDirty();
        }
        updateDirtyUi();
        showMessage('Полигон удалён локально (не в БД) — нажмите «Сохранить», чтобы применить, или «Отменить»', 'warning');
    });
    map.on('draw:deletestop', function () {
        drawToolActive = false;
        deleteModeActive = false;
        restorePageTextSelection();
    });

    polygons.on('layerremove', function (e) {
        var layer = e.layer;
        if (!deleteModeActive || !layer || !layer.genplanData) return;
        if (stashDeletedLayer(layer, false)) {
            markGeometryDirty();
        }
    });
    polygons.on('layeradd', function (e) {
        var layer = e.layer;
        if (!deleteModeActive) return;
        if (!layer || !layer.genplanData || !layer.genplanData.id) return;
        var id = String(layer.genplanData.id);
        var stashed = pendingDeletes[id];
        if (stashed && stashed.confirmed) return;
        delete pendingDeletes[id];
        updateDirtyUi();
    });

    pointLayers.on('layerremove', function (e) {
        var layer = e.layer;
        if (!deleteModeActive || !layer || !layer.genplanData) return;
        if (stashDeletedLayer(layer, false)) {
            markGeometryDirty();
        }
    });
    pointLayers.on('layeradd', function (e) {
        var layer = e.layer;
        if (!deleteModeActive) return;
        if (!layer || !layer.genplanData || !layer.genplanData.id) return;
        var id = String(layer.genplanData.id);
        var stashed = pendingDeletes[id];
        if (stashed && stashed.confirmed) return;
        delete pendingDeletes[id];
        updateDirtyUi();
    });

    map.on(L.Draw.Event.CREATED, function (e) {
        var layer = e.layer;
        if (editorMode === 'life' || e.layerType === 'polyline') {
            handleLifeCreated(layer);
            return;
        }
        var points = layerToPoints(layer);
        var c = centroidOfPoints(points);

        layer.genplanData = {
            id: null,
            homeId: null,
            title: '',
            linkUrl: '',
            labelX: c[0],
            labelY: c[1],
            points: clonePoints(points),
            isNew: true,
            geometryDirty: true,
            metaDirty: false
        };
        layer.edited = false;
        bindPolygonClick(layer);
        polygons.addLayer(layer);
        editableLayers.addLayer(layer);
        applyDefaultStyle(layer);
        selectLayer(layer);
        markGeometryDirty();
        showMessage('Новый объект (не сохранён) — заполните подпись и нажмите «Сохранить»', 'warning');
    });

    map.on(L.Draw.Event.EDITED, function (e) {
        e.layers.eachLayer(function (layer) {
            layer.edited = true;
            if (!layer.genplanData) return;
            var current = layerToPoints(layer);
            if (layer.genplanData.isNew || !pointsEqual(current, layer.genplanData.points)) {
                layer.genplanData.geometryDirty = true;
                markGeometryDirty();
            }
        });
        if (isDirty()) {
            showMessage('Изменения на карте — «Сохранить» / «Отменить» в форме', 'warning');
        }
        if (selectedLayer) updateLabelMarkerFromSelection();
    });

    map.on('draw:editstop', function () {
        drawToolActive = false;
        restorePageTextSelection();
    });

    // ─── UI bindings ─────────────────────────────────────────

    if (modePolyBtn) {
        modePolyBtn.addEventListener('click', function () {
            if (isBusy()) return;
            setMode('poly');
        });
    }
    if (modeLabelsBtn) {
        modeLabelsBtn.addEventListener('click', function () {
            if (isBusy()) return;
            setMode('labels');
        });
    }
    if (modeLifeBtn) {
        modeLifeBtn.addEventListener('click', function () {
            if (isBusy()) return;
            setMode('life');
        });
    }
    if (lifeCarBtn) {
        lifeCarBtn.addEventListener('click', function () {
            if (isBusy()) return;
            startLifeDraw('car');
        });
    }
    if (lifePersonBtn) {
        lifePersonBtn.addEventListener('click', function () {
            if (isBusy()) return;
            startLifeDraw('person');
        });
    }
    if (lifePerspBtn) {
        lifePerspBtn.addEventListener('click', function () {
            if (isBusy()) return;
            startPerspectiveTool();
        });
    }
    if (lifePerspClearBtn) {
        // User wants: only auto recalculation, no "reset perspective".
        // Hide button and do not bind click handler.
        try { lifePerspClearBtn.style.display = 'none'; } catch (e) { /* ignore */ }
    }
    if (lifeScaleNearInput) {
        // Keep values for autosave logic, but prevent manual "save perspective".
        try { lifeScaleNearInput.disabled = true; } catch (e) { /* ignore */ }
    }
    if (lifeScaleFarInput) {
        // Keep values for autosave logic, but prevent manual "save perspective".
        try { lifeScaleFarInput.disabled = true; } catch (e) { /* ignore */ }
    }
    if (birdsSaveBtn) {
        birdsSaveBtn.addEventListener('click', function () {
            saveBirdSettings();
        });
    }
    if (cloudsSaveBtn) {
        cloudsSaveBtn.addEventListener('click', function () {
            saveCloudSettings();
        });
    }
    if (sunSaveBtn) {
        sunSaveBtn.addEventListener('click', function () {
            saveSunSettings();
        });
    }
    if (recalcPerspectiveBtn) {
        recalcPerspectiveBtn.addEventListener('click', function () {
            if (isBusy() || editorMode !== 'life') return;
            if (!cfg.imageWidth || !cfg.imageHeight) return;
            if (!confirmDialog('Пересчитать наклон и перспективу по домам?')) return;

            var autoPersp = autoPerspectiveFromHomesPolygons();
            if (!autoPersp || !autoPersp.points) {
                showMessage('Не удалось найти дома для автоподбора перспективы', 'error');
                return;
            }

            // Keep scale values from UI (so editor user controls "how strong").
            var scaleNear = lifeScaleNearInput ? parseFloat(lifeScaleNearInput.value) : 1;
            var scaleFar = lifeScaleFarInput ? parseFloat(lifeScaleFarInput.value) : 0.35;
            if (!(scaleNear > 0) || !(scaleFar > 0)) {
                showMessage('Масштабы перспективы должны быть > 0', 'error');
                return;
            }

            lifePerspective = {
                enabled: true,
                points: autoPersp.points,
                scaleNear: scaleNear,
                scaleFar: scaleFar
            };

            perspSaving = true;
            var body = new URLSearchParams({
                kvartal_id: String(cfg.kvartalId),
                enabled: '1',
                points: JSON.stringify(autoPersp.points),
                scaleNear: String(scaleNear),
                scaleFar: String(scaleFar)
            });

            fetch(cfg.ajaxBase + '&act=life_save_perspective', {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: body.toString()
            })
                .then(function (r) { return r.json(); })
                .then(function (res) {
                    perspSaving = false;
                    if (!res || !res.success) {
                        showMessage((res && res.message) || 'Не удалось пересчитать перспективу', 'error');
                        return;
                    }
                    lifePerspective = res.perspective || lifePerspective;

                    // Hide the triangle/lines overlay; we keep the saved values.
                    clearPerspectiveUi();
                    updatePerspFieldsVisibility();

                    // Recompute and persist ground defaults.
                    var auto = autoGroundFromHomesPolygons(lifePerspective);
                    if (auto && groundPitchInput && groundSkewInput) {
                        groundPitchInput.value = String(auto.groundPitch.toFixed(3)).replace(/\.?0+$/, '');
                        groundSkewInput.value = String(auto.groundSkew.toFixed(3)).replace(/\.?0+$/, '');
                        saveGroundSettings();
                    } else {
                        showMessage('Перспектива пересчитана, но наклон не найден по домам', 'warning');
                    }
                    showMessage('Пересчёт выполнен', 'success');
                })
                .catch(function () {
                    perspSaving = false;
                    showMessage('Ошибка сети при пересчёте', 'error');
                });
        });
    }
    if (lightFromInput) {
        lightFromInput.addEventListener('input', updateSunPreview);
        lightFromInput.addEventListener('change', updateSunPreview);
    }
    if (shadowLenInput) {
        shadowLenInput.addEventListener('input', updateSunPreview);
        shadowLenInput.addEventListener('change', updateSunPreview);
    }
    updateSunPreview();
    if (lifeApplyBtn) {
        lifeApplyBtn.addEventListener('click', function () {
            applyLifeForm();
        });
    }
    if (lifeDeleteBtn) {
        lifeDeleteBtn.addEventListener('click', function () {
            deleteSelectedLife();
        });
    }

    if (titleInput) {
        titleInput.addEventListener('input', function () {
            if (suppressMetaSync) return;
            if (selectedLayer && selectedLayer.genplanData) {
                selectedLayer.genplanData.title = titleInput.value;
            }
            markMetaDirty();
        });
    }
    if (contentInput) {
        contentInput.addEventListener('input', function () {
            if (suppressMetaSync) return;
            if (selectedLayer && selectedLayer.genplanData) {
                selectedLayer.genplanData.content = contentInput.value;
            }
            markMetaDirty();
        });
    }
    if (showTitleDesktopInput) {
        showTitleDesktopInput.addEventListener('change', function () {
            if (suppressMetaSync) return;
            if (selectedLayer && selectedLayer.genplanData) {
                selectedLayer.genplanData.showTitleDesktop = !!showTitleDesktopInput.checked;
            }
            markMetaDirty();
        });
    }
    if (showTitleMobileInput) {
        showTitleMobileInput.addEventListener('change', function () {
            if (suppressMetaSync) return;
            if (selectedLayer && selectedLayer.genplanData) {
                selectedLayer.genplanData.showTitleMobile = !!showTitleMobileInput.checked;
            }
            markMetaDirty();
        });
    }
    if (showAptLinksInput) {
        showAptLinksInput.addEventListener('change', function () {
            if (suppressMetaSync) return;
            if (selectedLayer && selectedLayer.genplanData) {
                selectedLayer.genplanData.showAptLinks = !!showAptLinksInput.checked;
            }
            markMetaDirty();
        });
    }
    if (addPointBtn) {
        addPointBtn.addEventListener('click', function () {
            if (isBusy() || editorMode !== 'labels' || !hasImage) return;
            addPointMode = !addPointMode;
            updateAddPointUi();
            showMessage(addPointMode ? 'Кликните по карте, чтобы поставить точку' : 'Режим точки отменён', 'info');
        });
    }
    if (linkInput) {
        linkInput.addEventListener('input', function () {
            if (suppressMetaSync) return;
            if (selectedLayer && selectedLayer.genplanData) {
                selectedLayer.genplanData.linkUrl = (linkInput.value || '').trim();
            }
            markMetaDirty();
        });
    }
    if (homeSelect) {
        homeSelect.addEventListener('change', function () {
            if (suppressMetaSync) return;
            var hid = homeSelect.value ? parseInt(homeSelect.value, 10) : 0;
            if (selectedLayer && selectedLayer.genplanData) {
                selectedLayer.genplanData.homeId = hid > 0 ? hid : null;
            }
            markMetaDirty();
            updateAptLinksUi();
            if (hid > 0) {
                fetchHomeAutofill(hid, { onlyPreview: false });
            } else if (homePreview) {
                homePreview.innerHTML = '';
                homePreview.style.display = 'none';
            }
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener('click', function () {
            if (isBusy()) return;
            saveAllChanges();
        });
    }
    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            if (isBusy()) return;
            cancelChanges();
        });
    }

    if (uploadBtn && uploadInput) {
        uploadBtn.addEventListener('click', function () {
            if (isBusy()) return;
            uploadInput.value = '';
            uploadInput.click();
        });
        uploadInput.addEventListener('change', function () {
            var file = uploadInput.files && uploadInput.files[0];
            uploadInput.value = '';
            if (file) uploadGenplanImage(file);
        });
    }

    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function () {
            if (isBusy()) return;
            clearWholeGenplan();
        });
    }

    window.addEventListener('beforeunload', function (e) {
        if (!isDirty()) return;
        e.preventDefault();
        e.returnValue = '';
        return '';
    });

    map.on('click', function (e) {
        if (!addPointMode || editorMode !== 'labels' || !hasImage) return;
        if (drawToolActive || deleteModeActive) return;
        var layer = addPointLayer({
            labelX: Math.round(e.latlng.lng),
            labelY: Math.round(e.latlng.lat),
            isNew: true,
            geometryDirty: true
        });
        addPointMode = false;
        updateAddPointUi();
        selectLayer(layer);
        markMetaDirty();
        showMessage('Точка добавлена — заполните подпись и сохраните', 'success');
    });

    // ─── Init ────────────────────────────────────────────────

    updateDirtyUi();
    updateModeUi();
    updateAddPointUi();
    syncDrawAvailability();
    ensureHomesOptions();
    fillLifeSpriteOptions('car', 'gray');
    loadPolygons();
})();
