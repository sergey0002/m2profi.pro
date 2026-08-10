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
    var uploadInput = document.getElementById('genplan_upload_input');
    var uploadBtn = document.getElementById('genplan_upload_btn');
    var clearAllBtn = document.getElementById('genplan_clear_all');
    var metaPanel = document.getElementById('genplan_meta_panel');
    var titleInput = document.getElementById('genplan_title_input');
    var homeSelect = document.getElementById('genplan_home_select');
    var linkInput = document.getElementById('genplan_link_input');
    var homePreview = document.getElementById('genplan_home_preview');

    var hasImage = !!(cfg.imageUrl && cfg.imageWidth && cfg.imageHeight);
    var currentOverlay = null;
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
    var editorMode = 'poly'; // 'poly' | 'labels'
    var selectedLayer = null;
    var labelMarker = null;
    var suppressMetaSync = false;
    var homesLoaded = false;

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

    function hasPendingNews() {
        var found = false;
        eachPolygon(function (layer) {
            if (layer.genplanData && layer.genplanData.isNew) found = true;
        });
        return found;
    }

    function hasLayerGeometryDirty() {
        var found = false;
        eachPolygon(function (layer) {
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
        eachPolygon(function (layer) {
            if (layer.genplanData) {
                layer.genplanData.geometryDirty = false;
                layer.genplanData.metaDirty = false;
            }
        });
        updateDirtyUi();
    }

    function isBusy() {
        return savingGeometry || flushingDeletes || reverting || uploadInProgress || clearingMarkup;
    }

    function setControlsBusy(busy) {
        busy = !!busy;
        if (saveBtn) saveBtn.disabled = busy;
        if (cancelBtn) cancelBtn.disabled = busy;
        if (uploadBtn) uploadBtn.disabled = busy;
        if (clearAllBtn) clearAllBtn.disabled = busy;
        if (modePolyBtn) modePolyBtn.disabled = busy;
        if (modeLabelsBtn) modeLabelsBtn.disabled = busy;
        if (titleInput) titleInput.disabled = busy;
        if (homeSelect) homeSelect.disabled = busy;
        if (linkInput) linkInput.disabled = busy;
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
    var labelGroup = new L.FeatureGroup();
    map.addLayer(polygons);
    map.addLayer(labelGroup);

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
        edit: { featureGroup: polygons, remove: true },
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
        drawToolActive = false;
        deleteModeActive = false;
        restorePageTextSelection();
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
        if (!hasImage) {
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
        layer.setStyle(STYLE_DEFAULT);
    }

    function applySelectedStyle(layer) {
        if (!layer) return;
        layer.setStyle(STYLE_SELECTED);
        if (layer.bringToFront) layer.bringToFront();
    }

    function refreshAllStyles() {
        eachPolygon(function (layer) {
            if (layer === selectedLayer) applySelectedStyle(layer);
            else applyDefaultStyle(layer);
        });
    }

    var labelDragBound = false;

    function onLabelMapMouseMove(e) {
        if (!labelMarker || !labelMarker._genplanDragging) return;
        labelMarker.setLatLng(e.latlng);
    }

    function onLabelMapMouseUp() {
        if (!labelMarker || !labelMarker._genplanDragging) return;
        labelMarker._genplanDragging = false;
        if (map.dragging && !map.dragging.enabled()) map.dragging.enable();
        if (!selectedLayer || !selectedLayer.genplanData) return;
        var ll = labelMarker.getLatLng();
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
        if (linkInput) linkInput.value = data.linkUrl || '';
        if (homeSelect) {
            homeSelect.value = data.homeId ? String(data.homeId) : '';
        }
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
        if (linkInput) linkInput.value = '';
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
        data.linkUrl = linkInput ? (linkInput.value || '').trim() : '';
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

    function bindPolygonClick(layer) {
        layer.on('click', function (e) {
            if (editorMode !== 'labels') return;
            if (drawToolActive || deleteModeActive) return;
            if (e.originalEvent) {
                L.DomEvent.stopPropagation(e.originalEvent);
            }
            selectLayer(layer);
            showMessage('Объект выбран — заполните подпись / дом / ссылку', 'info');
        });
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

        var data = {
            id: item.id || null,
            homeId: item.homeId != null ? item.homeId : null,
            title: item.title || '',
            linkUrl: item.linkUrl || '',
            labelX: item.labelX != null ? item.labelX : null,
            labelY: item.labelY != null ? item.labelY : null,
            points: clonePoints(points),
            isNew: !item.id,
            geometryDirty: !!item.geometryDirty,
            metaDirty: false
        };
        ensureLabelDefaults(data, points);
        layer.genplanData = data;
        layer.edited = false;
        bindPolygonClick(layer);
        polygons.addLayer(layer);
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
    }

    function clearAllMapPolygons() {
        var all = [];
        eachPolygon(function (l) { all.push(l); });
        all.forEach(removeLayerFromMap);
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
        mode = mode === 'labels' ? 'labels' : 'poly';
        if (editorMode === mode) {
            updateModeUi();
            return;
        }
        if (mode === 'labels') {
            forceExitVertexEdit();
            syncMetaFromFormToLayer();
        } else {
            syncMetaFromFormToLayer();
            clearLabelMarker();
        }
        editorMode = mode;
        updateModeUi();
        syncDrawAvailability();
        if (mode === 'labels') {
            ensureHomesOptions();
            if (selectedLayer) {
                fillMetaForm(selectedLayer);
                updateLabelMarkerFromSelection();
            }
            showMessage('Режим подписей: клик по полигону — форма; перетащите красную точку — якорь label', 'info');
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
        updateMetaPanelVisibility();
        refreshAllStyles();
        var el = map.getContainer();
        if (el) {
            if (editorMode === 'labels') L.DomUtil.addClass(el, 'is-pick-mode');
            else L.DomUtil.removeClass(el, 'is-pick-mode');
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
                if (!opts.onlyPreview && titleInput && !(titleInput.value || '').trim() && res.titleSuggest) {
                    titleInput.value = res.titleSuggest;
                    if (selectedLayer && selectedLayer.genplanData) {
                        selectedLayer.genplanData.title = res.titleSuggest;
                    }
                    markMetaDirty();
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
        var points = layerToPoints(layer);
        if (points.length < 3) {
            return Promise.resolve({ ok: false, message: 'Меньше 3 точек' });
        }

        ensureLabelDefaults(data, points);

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
            eachPolygon(function (layer) {
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
            addPolygonLayer(item);
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
        eachPolygon(function () { hasAny = true; });
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

    map.on(L.Draw.Event.CREATED, function (e) {
        var layer = e.layer;
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

    if (titleInput) {
        titleInput.addEventListener('input', function () {
            if (suppressMetaSync) return;
            if (selectedLayer && selectedLayer.genplanData) {
                selectedLayer.genplanData.title = titleInput.value;
            }
            markMetaDirty();
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

    // ─── Init ────────────────────────────────────────────────

    updateDirtyUi();
    updateModeUi();
    syncDrawAvailability();
    ensureHomesOptions();
    loadPolygons();
})();
