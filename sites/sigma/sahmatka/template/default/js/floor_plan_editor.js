/**
 * Редактор поэтажных планов (Stage 1, задача 4).
 * Синхронизирован с паттернами facade_editor.js (dirty Save/Cancel, showMessage,
 * unsaved confirm dialog, forceExitVertexEdit, pointsEqual eps, setControlsBusy),
 * но единица разметки — КВАРТИРА (не этаж): у квартиры максимум 1 активный полигон,
 * поэтому вместо словаря pendingDeletes/нескольких editable-слоёв — один активный
 * слой на всё время (activeLayer). Плюс: смена этажа перегружает JPG-подложку
 * (у фасада картинка одна на весь редактор, здесь — своя на каждый этаж).
 */
(function () {
    'use strict';

    var cfg = window.FLOOR_PLAN_CONFIG;
    if (!cfg || !window.L) {
        return;
    }

    var unitLabelCap = cfg.unitLabelNomCap || 'Квартира';

    L.Icon.Default.imagePath = '/sahmatka/template/default/libs/leaflet/images/';

    L.drawLocal = {
        draw: {
            toolbar: {
                actions: { title: 'Отменить рисование', text: 'Отмена' },
                finish: { title: 'Завершить рисование', text: 'Готово' },
                undo: { title: 'Удалить последнюю точку', text: 'Удалить точку' },
                buttons: {
                    polyline: 'Линия',
                    polygon: 'Многоугольник',
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
                        start: 'Кликните, чтобы начать рисовать фигуру.',
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
                    edit: 'Редактировать выбранную квартиру',
                    editDisabled: 'Выберите размеченную квартиру',
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

    var messagesEl = document.getElementById('fp_messages');
    var sectionSelect = document.getElementById('fp_section_select');
    var floorSelect = document.getElementById('fp_floor_select');
    var apartmentSelect = document.getElementById('fp_apartment_select');
    var labelInput = document.getElementById('fp_label_input');
    var saveBtn = document.getElementById('fp_save');
    var cancelBtn = document.getElementById('fp_cancel');
    var dirtyActions = document.getElementById('fp_dirty_actions');
    var uploadInput = document.getElementById('fp_upload_input');
    var uploadBtn = document.getElementById('fp_upload_btn');
    var copyFloorBtn = document.getElementById('fp_copy_floor');
    var clearApartmentBtn = document.getElementById('fp_clear_apartment');
    var clearMarkupBtn = document.getElementById('fp_clear_markup');
    var clearPlanBtn = document.getElementById('fp_clear_plan');

    var drawToolActive = false;
    var deleteModeActive = false;
    var moveModeActive = false;
    var fpToolMoveBtn = null;
    var fpToolCopyBtn = null;
    var savingGeometry = false;
    var reverting = false;
    var loadingFloor = false;
    var uploadInProgress = false;
    var clearingMarkup = false;
    var polygonDrawer = null;
    var suppressSectionChange = false;
    var suppressFloorChange = false;
    var suppressApartmentChange = false;
    var geometryDirty = false;
    var metaDirty = false;
    var pendingDelete = false;
    var pendingDeleteData = null;

    var sections = Array.isArray(cfg.sections) && cfg.sections.length
        ? cfg.sections
        : [{ id: 1, caption: 'Секция 1', maxFloor: 30 }];
    var activeSection = parseInt(sections[0].id, 10) || 1;
    var activeFloor = 1;
    var activeApartmentId = 0;
    var activeLayer = null;
    var hasImage = false;
    var currentApartments = [];
    var floorJpgMapCache = {};
    var floorAptMapCache = {};
    var savedLabel = '';

    var URL_PARAM_SECTION = 'section';
    var URL_PARAM_FLOOR = 'floor';
    var URL_PARAM_APARTMENT = 'apartment';

    function readUrlSelection() {
        var params = new URLSearchParams(window.location.search);
        return {
            section: parseInt(params.get(URL_PARAM_SECTION), 10) || 0,
            floor: parseInt(params.get(URL_PARAM_FLOOR), 10) || 0,
            apartment: parseInt(params.get(URL_PARAM_APARTMENT), 10) || 0
        };
    }

    function isValidSection(sectionId) {
        sectionId = parseInt(sectionId, 10);
        if (!sectionId) return false;
        for (var i = 0; i < sections.length; i++) {
            if (parseInt(sections[i].id, 10) === sectionId) return true;
        }
        return false;
    }

    /** Обновляет GET-параметры section/floor/apartment без перезагрузки страницы. */
    function syncUrlSelection() {
        if (!window.history || typeof window.history.replaceState !== 'function') return;
        try {
            var url = new URL(window.location.href);
            url.searchParams.set(URL_PARAM_SECTION, String(activeSection));
            url.searchParams.set(URL_PARAM_FLOOR, String(activeFloor));
            if (activeApartmentId) {
                url.searchParams.set(URL_PARAM_APARTMENT, String(activeApartmentId));
            } else {
                url.searchParams.delete(URL_PARAM_APARTMENT);
            }
            var next = url.pathname + url.search + url.hash;
            if (next !== window.location.pathname + window.location.search + window.location.hash) {
                window.history.replaceState(null, '', next);
            }
        } catch (e) { /* ignore */ }
    }

    function resolveInitialSelection() {
        var urlSel = readUrlSelection();
        var section = isValidSection(urlSel.section)
            ? urlSel.section
            : (parseInt(sections[0].id, 10) || 1);
        var maxFloor = getSectionMaxFloor(section);
        var floor = urlSel.floor >= 1 && urlSel.floor <= maxFloor ? urlSel.floor : 1;
        var apartment = urlSel.apartment > 0 ? urlSel.apartment : 0;
        return { section: section, floor: floor, apartment: apartment };
    }

    function apartmentExistsOnFloor(apartamentId) {
        apartamentId = parseInt(apartamentId, 10);
        if (!apartamentId) return false;
        for (var i = 0; i < currentApartments.length; i++) {
            if (parseInt(currentApartments[i].apartamentId, 10) === apartamentId) return true;
        }
        return false;
    }

    function getSectionMeta(sectionId) {
        sectionId = parseInt(sectionId, 10);
        for (var i = 0; i < sections.length; i++) {
            if (parseInt(sections[i].id, 10) === sectionId) return sections[i];
        }
        return sections[0];
    }

    function getSectionMaxFloor(sectionId) {
        var meta = getSectionMeta(sectionId);
        var max = meta && meta.maxFloor ? parseInt(meta.maxFloor, 10) : 30;
        return max > 0 ? max : 30;
    }

    function getApartmentMeta(apartamentId) {
        apartamentId = parseInt(apartamentId, 10);
        for (var i = 0; i < currentApartments.length; i++) {
            if (parseInt(currentApartments[i].apartamentId, 10) === apartamentId) return currentApartments[i];
        }
        return null;
    }

    var STYLE_DEFAULT = {
        color: '#4da3ff',
        weight: 2,
        fillColor: '#4da3ff',
        fillOpacity: 0.35,
        opacity: 1,
        dashArray: null
    };
    var STYLE_SELECTED = {
        color: '#e67e22',
        weight: 3,
        fillColor: '#e67e22',
        fillOpacity: 0.45,
        opacity: 1,
        dashArray: null
    };

    var MESSAGE_TYPES = ['info', 'success', 'warning', 'error'];
    var MESSAGE_ICONS = { info: 'i', success: '\u2713', warning: '!', error: '\u2715' };

    function showMessage(text, type) {
        if (!messagesEl) return;
        if (MESSAGE_TYPES.indexOf(type) === -1) type = 'info';

        // Восстанавливаем aria-live после progress-режима (аудит H3 — там временно 'off').
        messagesEl.setAttribute('aria-live', 'polite');
        messagesEl.innerHTML = '';
        if (!text) {
            messagesEl.className = 'fp-editor__messages';
            return;
        }

        messagesEl.className = 'fp-editor__messages is-' + type;

        var icon = document.createElement('span');
        icon.className = 'fp-editor__messages-icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = MESSAGE_ICONS[type];

        var textEl = document.createElement('span');
        textEl.className = 'fp-editor__messages-text';
        textEl.textContent = text;

        messagesEl.appendChild(icon);
        messagesEl.appendChild(textEl);
    }

    /**
     * Stage 2 (аудит H1/H3): прогресс аплоада — в том же универсальном блоке `#fp_messages`,
     * не отдельным виджетом. Троттлинг обновлений + временный aria-live="off" на время
     * частых событий xhr.upload.onprogress, чтобы не спамить screen reader.
     */
    var UPLOAD_PROGRESS_THROTTLE_MS = 150;
    var lastProgressRenderAt = 0;

    function showUploadProgress(percent, fileName) {
        if (!messagesEl) return;
        percent = Math.max(0, Math.min(100, Math.round(percent)));

        var now = Date.now();
        if (percent < 100 && now - lastProgressRenderAt < UPLOAD_PROGRESS_THROTTLE_MS) {
            return;
        }
        lastProgressRenderAt = now;

        messagesEl.setAttribute('aria-live', 'off');
        messagesEl.className = 'fp-editor__messages is-progress';
        messagesEl.innerHTML = '';

        var icon = document.createElement('span');
        icon.className = 'fp-editor__messages-icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = '\u23f3';

        var textEl = document.createElement('span');
        textEl.className = 'fp-editor__messages-text';
        textEl.textContent = 'Загрузка \u00ab' + fileName + '\u00bb\u2026 ' + percent + '%';

        var track = document.createElement('div');
        track.className = 'fp-editor__messages-progress-track';
        var bar = document.createElement('div');
        bar.className = 'fp-editor__messages-progress-bar';
        bar.style.width = percent + '%';
        track.appendChild(bar);

        messagesEl.appendChild(icon);
        messagesEl.appendChild(textEl);
        messagesEl.appendChild(track);
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
            if (Math.abs(a[i][0] - b[i][0]) > POINTS_EPS || Math.abs(a[i][1] - b[i][1]) > POINTS_EPS) return false;
        }
        return true;
    }

    function isDirty() {
        return geometryDirty || metaDirty || pendingDelete;
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

    function clearDirtyFlags() {
        geometryDirty = false;
        metaDirty = false;
        pendingDelete = false;
        pendingDeleteData = null;
        updateDirtyUi();
    }

    function isBusy() {
        return savingGeometry || reverting || loadingFloor || uploadInProgress || clearingMarkup;
    }

    function setControlsBusy(busy) {
        if (saveBtn) saveBtn.disabled = !!busy;
        if (cancelBtn) cancelBtn.disabled = !!busy;
        if (sectionSelect) sectionSelect.disabled = !!busy;
        if (floorSelect) floorSelect.disabled = !!busy;
        if (apartmentSelect) apartmentSelect.disabled = !!busy;
        if (uploadBtn) uploadBtn.disabled = !!busy;
        if (copyFloorBtn) copyFloorBtn.disabled = !!busy;
        if (clearApartmentBtn) clearApartmentBtn.disabled = !!busy;
        if (clearMarkupBtn) clearMarkupBtn.disabled = !!busy;
        if (clearPlanBtn) clearPlanBtn.disabled = !!busy;
    }

    function showUnsavedChangesDialog(message) {
        return new Promise(function (resolve) {
            var overlay = document.createElement('div');
            overlay.className = 'fp-editor__modal-overlay';

            var box = document.createElement('div');
            box.className = 'fp-editor__modal-box';

            var text = document.createElement('p');
            text.className = 'fp-editor__modal-text';
            text.textContent = message;
            box.appendChild(text);

            var actions = document.createElement('div');
            actions.className = 'fp-editor__modal-actions';

            function cleanup(result) {
                document.removeEventListener('keydown', onKeyDown);
                if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
                resolve(result);
            }

            function onKeyDown(e) {
                if (e.key === 'Escape') cleanup('stay');
            }

            [
                { id: 'stay', label: 'Остаться' },
                { id: 'discard', label: 'Не сохранять' },
                { id: 'save', label: 'Сохранить и перейти', primary: true }
            ].forEach(function (btn) {
                var b = document.createElement('button');
                b.type = 'button';
                b.textContent = btn.label;
                b.className = 'fp-editor__btn' + (btn.primary ? ' fp-editor__btn--primary' : '');
                b.addEventListener('click', function () { cleanup(btn.id); });
                actions.appendChild(b);
            });

            box.appendChild(actions);
            overlay.appendChild(box);
            overlay.addEventListener('mousedown', function (e) {
                if (e.target === overlay) cleanup('stay');
            });
            document.addEventListener('keydown', onKeyDown);
            document.body.appendChild(overlay);

            var firstBtn = actions.querySelector('button');
            if (firstBtn) firstBtn.focus();
        });
    }

    /**
     * Leaflet.Draw (и Draggable) вешают preventDefault на window "selectstart" —
     * блокирует выделение текста на всей странице. Возвращаем его после инструментов.
     */
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

    function snapshotLayer(layer) {
        if (!layer) return;
        if (!layer.fpData) layer.fpData = {};
        layer.fpData.savedPoints = clonePoints(layerToPoints(layer));
        layer.edited = false;
    }

    function stopLeafletEditHandlers() {
        forceExitVertexEdit();
    }

    /** Если тулбар удаления ещё активен, но не подтверждён «✓» — зафиксировать удаление активного слоя. */
    function commitLeafletDeleteIfNeeded() {
        var toolbars = drawControl && drawControl._toolbars;
        var tb = toolbars && toolbars.edit;
        if (!tb || !tb._modes) return;

        Object.keys(tb._modes).forEach(function (mode) {
            var handler = tb._modes[mode].handler;
            if (!handler || !handler.enabled || !handler.enabled()) return;
            if (!handler._deletedLayers || !handler._deletedLayers.eachLayer) return;

            handler._deletedLayers.eachLayer(function (layer) {
                stashDeletedLayer(layer);
            });

            try {
                if (typeof handler.save === 'function') handler.save();
            } catch (e) { /* ignore */ }
            try {
                handler.disable();
            } catch (e2) { /* ignore */ }

            deleteModeActive = false;
        });
    }

    function revertActiveDeleteHandler() {
        var toolbars = drawControl && drawControl._toolbars;
        var tb = toolbars && toolbars.edit;
        if (!tb || !tb._modes) return;

        Object.keys(tb._modes).forEach(function (mode) {
            var handler = tb._modes[mode].handler;
            if (!handler || !handler.enabled || !handler.enabled()) return;
            if (handler.type === 'remove' && typeof handler.revertLayers === 'function') {
                try { handler.revertLayers(); } catch (e) { /* ignore */ }
            }
            try { handler.disable(); } catch (e2) { /* ignore */ }
        });
        deleteModeActive = false;
        removeOrphanEditMarkers();
    }

    function stashDeletedLayer(layer) {
        if (!layer || !layer.fpData) return;
        if (layer.fpData.isNew || !layer.fpData.id) {
            // черновик, не сохранён — просто пропал с карты, ничего удалять в БД не нужно
            if (activeLayer === layer) activeLayer = null;
            updatePolygonDrawButton();
            return;
        }
        pendingDelete = true;
        pendingDeleteData = {
            id: layer.fpData.id,
            apartamentId: layer.fpData.apartamentId,
            apartmentNum: layer.fpData.apartmentNum,
            label: layer.fpData.label || '',
            color: layer.fpData.color || STYLE_DEFAULT.color,
            points: clonePoints(layer.fpData.savedPoints && layer.fpData.savedPoints.length
                ? layer.fpData.savedPoints
                : layerToPoints(layer))
        };
        if (activeLayer === layer) activeLayer = null;
        markGeometryDirty();
        updatePolygonDrawButton();
        showMessage('Полигон удалён локально (не в БД) — нажмите «Сохранить», чтобы применить, или «Отменить»', 'warning');
    }

    /**
     * Принудительно выйти из режима правки вершин Leaflet.Draw —
     * иначе setLatLngs двигает линии, а маркеры остаются на старых координатах.
     */
    function forceExitVertexEdit() {
        stopPolygonDraw();
        drawToolActive = false;
        deleteModeActive = false;

        eachPolygon(function (layer) {
            if (layer.editing) {
                try {
                    if (layer.editing.enabled && layer.editing.enabled()) {
                        layer.editing.disable();
                    } else if (typeof layer.editing.disable === 'function') {
                        layer.editing.disable();
                    }
                } catch (e) { /* ignore */ }
            }
            layer.edited = false;
        });

        var toolbars = drawControl && drawControl._toolbars;
        if (toolbars && toolbars.edit) {
            var tb = toolbars.edit;
            if (tb._modes) {
                Object.keys(tb._modes).forEach(function (mode) {
                    var handler = tb._modes[mode].handler;
                    if (!handler) return;
                    if (handler.enabled && handler.enabled()) {
                        try { handler.disable(); } catch (e) { /* ignore */ }
                    }
                });
            }
            if (tb._activeMode) {
                try {
                    if (tb._activeMode.button) {
                        L.DomUtil.removeClass(tb._activeMode.button, 'leaflet-draw-toolbar-button-enabled');
                    }
                    tb._activeMode = null;
                } catch (e2) { /* ignore */ }
            }
        }

        removeOrphanEditMarkers();
        restorePageTextSelection();
    }

    function removeOrphanEditMarkers() {
        var orphan = [];
        map.eachLayer(function (layer) {
            if (!(layer instanceof L.Marker)) return;
            var icon = layer.options && layer.options.icon;
            var cls = (icon && icon.options && icon.options.className) || '';
            if (cls.indexOf('leaflet-editing-icon') !== -1 || cls.indexOf('fp-edit-vertex') !== -1) {
                orphan.push(layer);
            }
        });
        orphan.forEach(function (m) {
            try { map.removeLayer(m); } catch (e) { /* ignore */ }
        });

        var pane = map.getPanes && map.getPanes().markerPane;
        if (pane) {
            var nodes = pane.querySelectorAll('.leaflet-editing-icon, .fp-edit-vertex');
            Array.prototype.forEach.call(nodes, function (el) {
                if (el && el.parentNode) el.parentNode.removeChild(el);
            });
        }
    }

    /** Пересоздать полигон из точек — надёжный способ избежать рассинхрона path/маркеров. */
    function remountPolygonFromPoints(layer, points, toEditable) {
        if (!layer || !layer.fpData) return null;
        if (layer.editing) {
            try { layer.editing.disable(); } catch (e) { /* ignore */ }
        }
        var data = {
            id: layer.fpData.id,
            apartamentId: layer.fpData.apartamentId,
            apartmentNum: layer.fpData.apartmentNum,
            label: layer.fpData.label || '',
            color: layer.fpData.color || STYLE_DEFAULT.color,
            points: clonePoints(points || layer.fpData.savedPoints || layerToPoints(layer))
        };
        removeLayerFromMap(layer);
        return addPolygonLayer(data, !!toEditable);
    }

    var map = L.map('fp_map', {
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

    var currentOverlay = null;
    var imageBounds = L.latLngBounds([[0, 0], [1, 1]]);
    var imgW = 1;
    var imgH = 1;

    function updateMinZoomFromImage() {
        var size = map.getSize();
        if (!size.x || !size.y) return;
        var minZ = map.getBoundsZoom(imageBounds, false);
        if (!isFinite(minZ)) return;
        map.setMinZoom(minZ);
        if (map.getZoom() < minZ) {
            map.setZoom(minZ);
        }
    }

    function setFloorImage(url, w, h) {
        imgW = w;
        imgH = h;
        map.setMaxBounds(null);
        if (currentOverlay) {
            map.removeLayer(currentOverlay);
            currentOverlay = null;
        }
        imageBounds = L.latLngBounds([[0, 0], [h, w]]);
        currentOverlay = L.imageOverlay(url, imageBounds).addTo(map);
        map.fitBounds(imageBounds);
        updateMinZoomFromImage();
        map.setMaxBounds(imageBounds.pad(0.5));
    }

    map.on('resize', updateMinZoomFromImage);
    map.on('zoom', function () {
        var minZ = map.getMinZoom();
        if (map.getZoom() < minZ) {
            map.setZoom(minZ);
        }
    });

    var otherPolygons = new L.FeatureGroup();
    var editablePolygons = new L.FeatureGroup();
    map.addLayer(otherPolygons);
    map.addLayer(editablePolygons);

    var vertexIcon = new L.DivIcon({
        iconSize: new L.Point(8, 8),
        iconAnchor: new L.Point(4, 4),
        className: 'leaflet-div-icon leaflet-editing-icon fp-edit-vertex'
    });
    var vertexTouchIcon = new L.DivIcon({
        iconSize: new L.Point(12, 12),
        iconAnchor: new L.Point(6, 6),
        className: 'leaflet-div-icon leaflet-editing-icon leaflet-touch-icon fp-edit-vertex'
    });
    if (L.Edit && L.Edit.PolyVerticesEdit) {
        L.Edit.PolyVerticesEdit.mergeOptions({ icon: vertexIcon, touchIcon: vertexTouchIcon });
    }
    if (L.Draw && L.Draw.Polyline) {
        L.Draw.Polyline.mergeOptions({ icon: vertexIcon, touchIcon: vertexTouchIcon });
    }

    var drawControl = new L.Control.Draw({
        edit: { featureGroup: editablePolygons, remove: true },
        draw: {
            polygon: {
                allowIntersection: false,
                showArea: false,
                guidelineDistance: 12,
                shapeOptions: {
                    color: '#3388ff',
                    weight: 3,
                    opacity: 0.9,
                    fillColor: '#3388ff',
                    fillOpacity: 0.15
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
     * Перетаскивание полигона целиком (Leaflet.Path.Drag).
     * Кнопка ✥ → тянете мышью без «Готово» / лишних кликов.
     */
    function onPolygonDragEnd(e) {
        var layer = (e && e.target) || this;
        if (!layer || !layer.fpData) {
            markGeometryDirty();
            return;
        }
        layer.edited = true;
        var aptId = parseInt(layer.fpData.apartamentId, 10) || 0;
        if (aptId) {
            activeApartmentId = aptId;
            suppressApartmentChange = true;
            if (apartmentSelect) apartmentSelect.value = String(aptId);
            suppressApartmentChange = false;
            setEditableLayer(layer);
            updatePolygonDrawButton();
            syncUrlSelection();
        }
        markGeometryDirty();
    }

    function setLayerDraggable(layer, on) {
        if (!layer || !L.Handler || !L.Handler.PathDrag) return;
        if (on) {
            if (!layer.options.interactive) {
                layer.options.interactive = true;
                if (layer._path) L.DomUtil.addClass(layer._path, 'leaflet-interactive');
            }
            if (!layer.dragging) {
                L.Handler.PathDrag.makeDraggable(layer);
            }
            if (layer.dragging && !layer.dragging.enabled()) {
                layer.dragging.enable();
            }
            if (!layer._fpDragBound) {
                layer._fpDragBound = true;
                layer.on('dragend', onPolygonDragEnd);
            }
            if (layer.bringToFront) layer.bringToFront();
        } else if (layer.dragging && layer.dragging.enabled()) {
            layer.dragging.disable();
        }
    }

    function syncMoveModeOnLayers() {
        eachPolygon(function (layer) {
            setLayerDraggable(layer, false);
        });
        if (!moveModeActive) {
            if (map && map.dragging && !map.dragging.enabled()) {
                map.dragging.enable();
            }
            return;
        }
        eachPolygon(function (layer) {
            setLayerDraggable(layer, true);
        });
    }

    function setMoveMode(on) {
        on = !!on;
        if (on === moveModeActive) {
            syncMoveModeOnLayers();
            return;
        }
        if (on) {
            if (!L.Handler || !L.Handler.PathDrag) {
                showMessage('Плагин перетаскивания полигонов не загружен', 'error');
                return;
            }
            stopPolygonDraw();
            forceExitVertexEdit();
            var any = false;
            eachPolygon(function () { any = true; });
            if (!any) {
                showMessage('На этаже нет полигонов — нечего двигать', 'info');
                return;
            }
            moveModeActive = true;
            if (fpToolMoveBtn) {
                L.DomUtil.addClass(fpToolMoveBtn, 'leaflet-draw-toolbar-button-enabled');
            }
            if (map.dragging && map.dragging.enabled()) {
                map.dragging.disable();
            }
            showMessage('Тяните полигон мышью. Выход — снова ✥ или «Сохранить»', 'info');
        } else {
            moveModeActive = false;
            if (fpToolMoveBtn) {
                L.DomUtil.removeClass(fpToolMoveBtn, 'leaflet-draw-toolbar-button-enabled');
            }
            if (map.dragging && !map.dragging.enabled()) {
                map.dragging.enable();
            }
        }
        syncMoveModeOnLayers();
    }

    function injectFpToolbarTools() {
        var container = drawControl && drawControl._container;
        if (!container || container.querySelector('.fp-draw-extra')) return;

        var sections = container.querySelectorAll('.leaflet-draw-section');
        var editSection = sections.length > 1 ? sections[1] : null;

        var section = L.DomUtil.create('div', 'leaflet-draw-section fp-draw-extra');
        if (editSection) {
            container.insertBefore(section, editSection);
        } else {
            container.appendChild(section);
        }

        var toolbar = L.DomUtil.create('div', 'leaflet-draw-toolbar leaflet-bar', section);

        fpToolCopyBtn = L.DomUtil.create('a', 'leaflet-draw-draw-copy fp-tool-btn fp-tool-copy', toolbar);
        fpToolCopyBtn.href = '#';
        fpToolCopyBtn.title = 'Копировать разметку этажа';
        fpToolCopyBtn.setAttribute('role', 'button');
        fpToolCopyBtn.setAttribute('aria-label', 'Копировать разметку этажа');

        fpToolMoveBtn = L.DomUtil.create('a', 'leaflet-draw-draw-move fp-tool-btn fp-tool-move', toolbar);
        fpToolMoveBtn.href = '#';
        fpToolMoveBtn.title = 'Двигать полигон целиком (тянуть мышью)';
        fpToolMoveBtn.setAttribute('role', 'button');
        fpToolMoveBtn.setAttribute('aria-label', 'Двигать полигон');

        L.DomEvent.on(fpToolCopyBtn, 'click', L.DomEvent.stop)
            .on(fpToolCopyBtn, 'mousedown', L.DomEvent.stop)
            .on(fpToolCopyBtn, 'dblclick', L.DomEvent.stop)
            .on(fpToolCopyBtn, 'click', function () {
                if (isBusy()) return;
                setMoveMode(false);
                requestCopyFloorMarkup();
            });

        L.DomEvent.on(fpToolMoveBtn, 'click', L.DomEvent.stop)
            .on(fpToolMoveBtn, 'mousedown', L.DomEvent.stop)
            .on(fpToolMoveBtn, 'dblclick', L.DomEvent.stop)
            .on(fpToolMoveBtn, 'click', function () {
                if (isBusy()) return;
                setMoveMode(!moveModeActive);
            });
    }

    injectFpToolbarTools();

    function parseAptArea(raw) {
        if (raw == null || raw === '') return null;
        var n = parseFloat(String(raw).replace(',', '.').replace(/[^\d.-]/g, ''));
        return isFinite(n) ? n : null;
    }

    function areasMatch(a, b) {
        var x = parseAptArea(a);
        var y = parseAptArea(b);
        if (x == null || y == null) return false;
        return Math.abs(x - y) < 0.05;
    }

    function formatAptOption(apt) {
        var label = '№' + apt.apartmentNum;
        if (apt.rooms) label += ' — ' + apt.rooms;
        if (apt.area) label += ', ' + apt.area + ' м²';
        if (apt.marked) label += ' ✓';
        return label;
    }

    function collectSourceMarkedRows() {
        var rows = [];
        currentApartments.forEach(function (apt) {
            var layer = findLayerByApartment(apt.apartamentId);
            if (!layer) return;
            rows.push({
                apt: apt,
                layer: layer,
                points: clonePoints(layerToPoints(layer)),
                label: (layer.fpData && layer.fpData.label) || '',
                color: (layer.fpData && layer.fpData.color) || STYLE_DEFAULT.color
            });
        });
        return rows;
    }

    function requestCopyFloorMarkup() {
        if (isBusy()) return;
        if (!isDirty()) {
            openCopyFloorMarkupDialog();
            return;
        }
        var apt = getApartmentMeta(activeApartmentId);
        showUnsavedChangesDialog(
            'Есть несохранённые изменения (квартира №' + (apt ? apt.apartmentNum : activeApartmentId) + ').\n\n' +
            '«Сохранить и перейти» — записать правки, затем открыть копирование.\n' +
            '«Не сохранять» — отменить правки и открыть копирование.\n' +
            '«Остаться» — не копировать.'
        ).then(function (action) {
            if (action === 'save') {
                saveCurrentApartmentChanges().then(function (res) {
                    if (res && res.ok) openCopyFloorMarkupDialog();
                });
            } else if (action === 'discard') {
                revertCurrentApartmentChanges().then(function () {
                    openCopyFloorMarkupDialog();
                });
            }
        });
    }

    function openCopyFloorMarkupDialog() {
        if (!hasImage) {
            showMessage('Вначале загрузите файл плана этажа', 'info');
            return;
        }
        var sourceRows = collectSourceMarkedRows();
        if (!sourceRows.length) {
            showMessage('На текущем этаже нет размеченных квартир для копирования', 'error');
            return;
        }

        var max = getSectionMaxFloor(activeSection);
        var targetFloors = [];
        for (var f = 1; f <= max; f++) {
            if (f !== activeFloor) targetFloors.push(f);
        }
        if (!targetFloors.length) {
            showMessage('Нет другого этажа в секции для копирования', 'error');
            return;
        }

        var defaultTarget = targetFloors.indexOf(activeFloor + 1) !== -1
            ? (activeFloor + 1)
            : targetFloors[0];

        var overlay = document.createElement('div');
        overlay.className = 'fp-editor__modal-overlay';

        var box = document.createElement('div');
        box.className = 'fp-editor__modal-box fp-editor__modal-box--wide';

        var title = document.createElement('p');
        title.className = 'fp-editor__modal-text';
        title.textContent = 'Копировать разметку с этажа ' + activeFloor + ' на этаж:';
        box.appendChild(title);

        var floorField = document.createElement('label');
        floorField.className = 'fp-editor__modal-field';
        var floorSelect = document.createElement('select');
        floorSelect.className = 'fp-editor__modal-select';
        targetFloors.forEach(function (f) {
            var opt = document.createElement('option');
            opt.value = String(f);
            opt.textContent = 'Этаж ' + f;
            if (f === defaultTarget) opt.selected = true;
            floorSelect.appendChild(opt);
        });
        floorField.appendChild(floorSelect);
        box.appendChild(floorField);

        var warn = document.createElement('p');
        warn.className = 'fp-editor__modal-warn';
        warn.textContent = 'Вся существующая разметка выбранного этажа будет очищена перед копированием.';
        box.appendChild(warn);

        var tableWrap = document.createElement('div');
        tableWrap.className = 'fp-editor__copy-table-wrap';
        var table = document.createElement('table');
        table.className = 'fp-editor__copy-table';
        table.innerHTML = '<thead><tr>' +
            '<th title="Копировать">✓</th>' +
            '<th>Квартира (этаж ' + activeFloor + ')</th>' +
            '<th>Квартира на целевом этаже</th>' +
            '</tr></thead>';
        var tbody = document.createElement('tbody');
        table.appendChild(tbody);
        tableWrap.appendChild(table);
        box.appendChild(tableWrap);

        var status = document.createElement('p');
        status.className = 'fp-editor__modal-status';
        status.textContent = 'Загрузка квартир целевого этажа…';
        box.appendChild(status);

        var actions = document.createElement('div');
        actions.className = 'fp-editor__modal-actions';
        var cancelBtn = document.createElement('button');
        cancelBtn.type = 'button';
        cancelBtn.className = 'fp-editor__btn';
        cancelBtn.textContent = 'Отмена';
        var okBtn = document.createElement('button');
        okBtn.type = 'button';
        okBtn.className = 'fp-editor__btn fp-editor__btn--primary';
        okBtn.textContent = 'Копировать';
        okBtn.disabled = true;
        actions.appendChild(cancelBtn);
        actions.appendChild(okBtn);
        box.appendChild(actions);
        overlay.appendChild(box);
        document.body.appendChild(overlay);

        var rowControls = [];
        var targetApts = [];

        function cleanup() {
            document.removeEventListener('keydown', onKeyDown);
            if (overlay.parentNode) overlay.parentNode.removeChild(overlay);
        }

        function onKeyDown(e) {
            if (e.key === 'Escape') cleanup();
        }
        document.addEventListener('keydown', onKeyDown);
        overlay.addEventListener('mousedown', function (e) {
            if (e.target === overlay) cleanup();
        });
        cancelBtn.addEventListener('click', cleanup);

        function syncCheckboxFromArea(ctrl) {
            var tgtId = parseInt(ctrl.select.value, 10) || 0;
            var tgt = null;
            for (var i = 0; i < targetApts.length; i++) {
                if (parseInt(targetApts[i].apartamentId, 10) === tgtId) {
                    tgt = targetApts[i];
                    break;
                }
            }
            ctrl.checkbox.checked = !!(tgt && areasMatch(ctrl.source.apt.area, tgt.area));
        }

        function rebuildMappingRows() {
            tbody.innerHTML = '';
            rowControls = [];
            sourceRows.forEach(function (src, idx) {
                var tr = document.createElement('tr');

                var tdCheck = document.createElement('td');
                var cb = document.createElement('input');
                cb.type = 'checkbox';
                tdCheck.appendChild(cb);
                tr.appendChild(tdCheck);

                var tdSrc = document.createElement('td');
                tdSrc.textContent = formatAptOption(src.apt);
                tr.appendChild(tdSrc);

                var tdSel = document.createElement('td');
                var sel = document.createElement('select');
                sel.className = 'fp-editor__modal-select';
                if (!targetApts.length) {
                    var empty = document.createElement('option');
                    empty.value = '';
                    empty.textContent = '— нет квартир —';
                    sel.appendChild(empty);
                    sel.disabled = true;
                    cb.disabled = true;
                } else {
                    targetApts.forEach(function (apt) {
                        var opt = document.createElement('option');
                        opt.value = String(apt.apartamentId);
                        opt.textContent = formatAptOption(apt);
                        sel.appendChild(opt);
                    });
                    // авто по порядку шахматки слева направо (apartment_num ASC)
                    var autoIdx = Math.min(idx, targetApts.length - 1);
                    sel.value = String(targetApts[autoIdx].apartamentId);
                }
                tdSel.appendChild(sel);
                tr.appendChild(tdSel);
                tbody.appendChild(tr);

                var ctrl = { checkbox: cb, select: sel, source: src };
                rowControls.push(ctrl);
                sel.addEventListener('change', function () { syncCheckboxFromArea(ctrl); });
                syncCheckboxFromArea(ctrl);
            });
        }

        function loadTargetFloorApts() {
            var targetFloor = parseInt(floorSelect.value, 10);
            okBtn.disabled = true;
            status.textContent = 'Загрузка квартир этажа ' + targetFloor + '…';
            return fetchFloorMeta(activeSection, targetFloor).then(function (meta) {
                targetApts = (meta && meta.apartments) || [];
                // порядок как в шахматках: уже apartment_num ASC с сервера
                targetApts = targetApts.slice().sort(function (a, b) {
                    return (parseInt(a.apartmentNum, 10) || 0) - (parseInt(b.apartmentNum, 10) || 0);
                });
                rebuildMappingRows();
                if (!targetApts.length) {
                    status.textContent = 'На этаже ' + targetFloor + ' нет квартир в БД.';
                    okBtn.disabled = true;
                } else {
                    status.textContent = 'Галочка по умолчанию только если площадь совпадает. Можно изменить вручную.';
                    okBtn.disabled = false;
                }
            });
        }

        floorSelect.addEventListener('change', loadTargetFloorApts);
        loadTargetFloorApts();

        okBtn.addEventListener('click', function () {
            var targetFloor = parseInt(floorSelect.value, 10);
            var mappings = [];
            var usedTargets = {};
            for (var i = 0; i < rowControls.length; i++) {
                var ctrl = rowControls[i];
                if (!ctrl.checkbox.checked) continue;
                var tid = parseInt(ctrl.select.value, 10) || 0;
                if (!tid) continue;
                if (usedTargets[tid]) {
                    showMessage('Две квартиры назначены на одну цель — исправьте сопоставление', 'error');
                    return;
                }
                usedTargets[tid] = true;
                var tgtApt = null;
                for (var j = 0; j < targetApts.length; j++) {
                    if (parseInt(targetApts[j].apartamentId, 10) === tid) {
                        tgtApt = targetApts[j];
                        break;
                    }
                }
                if (!tgtApt) continue;
                mappings.push({
                    points: ctrl.source.points,
                    label: ctrl.source.label,
                    color: ctrl.source.color,
                    targetApt: tgtApt
                });
            }
            if (!mappings.length) {
                showMessage('Отметьте хотя бы одну квартиру для копирования', 'error');
                return;
            }

            var confirmed = window.confirm(
                'Вся разметка этажа ' + targetFloor + ' будет очищена.\n' +
                'Затем будет скопировано ' + mappings.length + ' полигон(ов) с этажа ' + activeFloor + '.\n\n' +
                'Продолжить?'
            );
            if (!confirmed) return;

            cleanup();
            executeCopyFloorMarkup(targetFloor, mappings);
        });
    }

    function savePolygonToApartment(section, floor, apartamentId, points, label, color) {
        var body = new URLSearchParams({
            home_id: String(cfg.homeId),
            section: String(section),
            floor: String(floor),
            apartament_id: String(apartamentId),
            floor_plan_polygon_id: '0',
            label: label || '',
            color: color || STYLE_DEFAULT.color,
            points: JSON.stringify(points)
        });
        return fetch(cfg.ajaxBase + '&act=save_polygon', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res || !res.success) {
                    return { ok: false, message: (res && res.message) || 'Ошибка сохранения' };
                }
                return { ok: true, id: res.id };
            })
            .catch(function () {
                return { ok: false, message: 'Ошибка сети при сохранении' };
            });
    }

    function clearFloorPolygonsApi(section, floor) {
        var body = new URLSearchParams({
            home_id: String(cfg.homeId),
            section: String(section),
            floor: String(floor)
        });
        return fetch(cfg.ajaxBase + '&act=clear_polygons', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (!res || !res.success) {
                    return { ok: false, message: (res && res.message) || 'Ошибка очистки' };
                }
                return { ok: true, cleared: res.cleared || 0 };
            })
            .catch(function () {
                return { ok: false, message: 'Ошибка сети при очистке' };
            });
    }

    function executeCopyFloorMarkup(targetFloor, mappings) {
        if (isBusy()) return;
        clearingMarkup = true;
        setControlsBusy(true);
        showMessage('Копирование разметки на этаж ' + targetFloor + '…', 'info');

        var firstAptId = mappings[0] && mappings[0].targetApt
            ? mappings[0].targetApt.apartamentId
            : 0;

        function finishFail(msg) {
            clearingMarkup = false;
            setControlsBusy(false);
            showMessage(msg || 'Ошибка копирования', 'error');
        }

        clearFloorPolygonsApi(activeSection, targetFloor).then(function (clr) {
            if (!clr.ok) {
                finishFail(clr.message);
                return;
            }

            var chain = Promise.resolve({ ok: true });
            var failMsg = '';
            mappings.forEach(function (m) {
                chain = chain.then(function (prev) {
                    if (!prev || !prev.ok) return prev;
                    return savePolygonToApartment(
                        activeSection,
                        targetFloor,
                        m.targetApt.apartamentId,
                        m.points,
                        m.label,
                        m.color
                    ).then(function (res) {
                        if (!res.ok) {
                            failMsg = res.message || 'Ошибка сохранения полигона';
                            return res;
                        }
                        return { ok: true };
                    });
                });
            });

            return chain.then(function (res) {
                clearingMarkup = false;
                setControlsBusy(false);
                invalidateFloorMapsCache(activeSection);
                if (!res || !res.ok) {
                    showMessage(failMsg || (res && res.message) || 'Копирование прервано с ошибкой', 'error');
                    loadFloor(activeSection, targetFloor, {
                        autoDraw: false,
                        initialApartmentId: firstAptId
                    });
                    return;
                }
                var okMsg = 'Скопировано ' + mappings.length + ' полигон(ов) на этаж ' + targetFloor;
                return loadFloor(activeSection, targetFloor, {
                    autoDraw: false,
                    initialApartmentId: firstAptId,
                    silent: true
                }).then(function () {
                    showMessage(okMsg, 'success');
                });
            });
        }).catch(function () {
            finishFail('Ошибка сети при копировании');
        });
    }

    /**
     * Leaflet.Draw «резинка» — мелкие div в overlayPane, часто под ImageOverlay.
     * Дублируем пунктиром SVG в отдельном pane поверх картинки.
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
            if (drawer._fwCursorGuide) return;
            drawer._fwCursorGuide = L.polyline([], {
                pane: 'drawGuidePane',
                color: (drawer.options.shapeOptions && drawer.options.shapeOptions.color) || '#3388ff',
                weight: 2,
                dashArray: '8 8',
                opacity: 0.95,
                interactive: false,
                className: 'fp-draw-cursor-guide'
            }).addTo(map);
        }

        function clearPreview() {
            if (drawer._fwCursorGuide) {
                try { map.removeLayer(drawer._fwCursorGuide); } catch (err) { /* ignore */ }
                drawer._fwCursorGuide = null;
            }
        }

        function updatePreview() {
            if (!drawer.enabled || !drawer.enabled()) return;
            ensurePreview();
            if (drawer._markers && drawer._markers.length && drawer._currentLatLng) {
                var last = drawer._markers[drawer._markers.length - 1].getLatLng();
                drawer._fwCursorGuide.setLatLngs([last, drawer._currentLatLng]);
            } else {
                drawer._fwCursorGuide.setLatLngs([]);
            }
            if (drawer._guidesContainer) {
                var pane = map.getPane('drawGuidePane');
                if (pane && drawer._guidesContainer.parentNode !== pane) {
                    pane.appendChild(drawer._guidesContainer);
                }
            }
        }

        ensurePreview();

        if (!drawer._fwGuideMoveBound) {
            drawer._fwGuideMoveBound = true;
            drawer._fwGuideOnMove = function () { updatePreview(); };
            map.on('mousemove', drawer._fwGuideOnMove);
        }

        if (!drawer._fwDisableWrapped) {
            drawer._fwDisableWrapped = true;
            var origDisable = drawer.disable;
            drawer.disable = function () {
                clearPreview();
                if (this._fwGuideOnMove) {
                    map.off('mousemove', this._fwGuideOnMove);
                    this._fwGuideOnMove = null;
                    this._fwGuideMoveBound = false;
                }
                return origDisable.call(this);
            };
        }

        return drawer;
    }

    function eachPolygon(fn) {
        otherPolygons.eachLayer(fn);
        editablePolygons.eachLayer(fn);
    }

    function findLayerByApartment(apartamentId) {
        apartamentId = parseInt(apartamentId, 10);
        var found = null;
        eachPolygon(function (layer) {
            if (layer.fpData && parseInt(layer.fpData.apartamentId, 10) === apartamentId) found = layer;
        });
        return found;
    }

    function applyDefaultStyle(layer) {
        if (!layer || !layer.setStyle) return;
        var c = (layer.fpData && layer.fpData.color) || STYLE_DEFAULT.color;
        layer.setStyle({
            color: c,
            weight: STYLE_DEFAULT.weight,
            fillColor: c,
            fillOpacity: STYLE_DEFAULT.fillOpacity,
            opacity: 1,
            dashArray: null
        });
    }

    function applySelectedStyle(layer) {
        if (!layer || !layer.setStyle) return;
        layer.setStyle({
            color: STYLE_SELECTED.color,
            weight: STYLE_SELECTED.weight,
            fillColor: STYLE_SELECTED.fillColor,
            fillOpacity: STYLE_SELECTED.fillOpacity,
            opacity: 1,
            dashArray: null
        });
        if (layer.bringToFront) layer.bringToFront();
    }

    function refreshOtherStyles() {
        otherPolygons.eachLayer(function (layer) {
            applyDefaultStyle(layer);
        });
    }

    function stopPolygonDraw() {
        if (polygonDrawer) {
            try { polygonDrawer.disable(); } catch (e) { /* ignore */ }
            polygonDrawer = null;
        }
        drawToolActive = false;
        restorePageTextSelection();
    }

    /** Можно ли добавить полигон текущей квартире (ещё нет слоя на карте). */
    function canAddPolygon() {
        return !!(hasImage && activeApartmentId && !findLayerByApartment(activeApartmentId));
    }

    /**
     * Прячет кнопку «многоугольник», если у выбранной квартиры полигон уже есть.
     * После локального удаления / для неразмеченной — снова показывает.
     */
    function updatePolygonDrawButton() {
        var allow = canAddPolygon();
        var container = drawControl && drawControl._container;
        if (container) {
            var sections = container.querySelectorAll('.leaflet-draw-section');
            // Первая секция — инструменты рисования (только polygon); правка/удаление — вторая.
            if (sections[0]) {
                sections[0].style.display = allow ? '' : 'none';
            }
        }
        if (!allow) {
            var toolbars = drawControl && drawControl._toolbars;
            var modes = toolbars && toolbars.draw && toolbars.draw._modes;
            var handler = modes && modes.polygon && modes.polygon.handler;
            if (handler && handler.enabled && handler.enabled()) {
                try { handler.disable(); } catch (e) { /* ignore */ }
            }
            stopPolygonDraw();
        }
    }

    function startPolygonDraw() {
        if (!canAddPolygon()) {
            updatePolygonDrawButton();
            return;
        }
        stopPolygonDraw();
        polygonDrawer = new L.Draw.Polygon(map, drawControl.options.draw.polygon);
        polygonDrawer.enable();
        // После enable: наш mousemove должен идти после обновления _currentLatLng в Draw
        attachDrawCursorGuide(polygonDrawer);
        drawToolActive = true;
        restorePageTextSelection();
        updatePolygonDrawButton();
    }

    /** Ровно один активный (editable) слой — остальные (если попали) уходят в otherPolygons. */
    function setEditableLayer(layer) {
        var toOther = [];
        editablePolygons.eachLayer(function (l) {
            if (l !== layer) toOther.push(l);
        });
        toOther.forEach(function (l) {
            editablePolygons.removeLayer(l);
            if (l.fpData && l.fpData.isNew) {
                removeLayerFromMap(l);
            } else {
                otherPolygons.addLayer(l);
                applyDefaultStyle(l);
            }
        });

        if (layer) {
            if (otherPolygons.hasLayer(layer)) otherPolygons.removeLayer(layer);
            if (!editablePolygons.hasLayer(layer)) editablePolygons.addLayer(layer);
            applySelectedStyle(layer);
        }
        activeLayer = layer || null;
        syncMoveModeOnLayers();
    }

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

    function bindPolygonClick(layer) {
        layer.on('click', function (e) {
            if (drawToolActive || deleteModeActive || moveModeActive) return;
            if (e.originalEvent) {
                L.DomEvent.stopPropagation(e.originalEvent);
                if (e.originalEvent.target && e.originalEvent.target.blur) {
                    e.originalEvent.target.blur();
                }
            }
            if (!layer.fpData || !layer.fpData.apartamentId) return;
            requestApartmentChange(layer.fpData.apartamentId, false);
        });
    }

    function addPolygonLayer(item, toEditable) {
        var color = item.color || STYLE_DEFAULT.color;
        var latlngs = item.points.map(function (p) {
            return [p[1], p[0]];
        });
        var layer = L.polygon(latlngs, {
            color: color,
            weight: STYLE_DEFAULT.weight,
            fillColor: color,
            fillOpacity: STYLE_DEFAULT.fillOpacity,
            opacity: 1
        });
        layer.fpData = {
            id: item.id || null,
            apartamentId: parseInt(item.apartamentId, 10) || 0,
            apartmentNum: parseInt(item.apartmentNum, 10) || 0,
            label: item.label || '',
            color: color,
            savedPoints: clonePoints(item.points),
            isNew: !item.id
        };
        layer.edited = false;
        bindPolygonClick(layer);
        if (toEditable) {
            editablePolygons.addLayer(layer);
            applySelectedStyle(layer);
        } else {
            otherPolygons.addLayer(layer);
            applyDefaultStyle(layer);
        }
        return layer;
    }

    function removeLayerFromMap(layer) {
        if (!layer) return;
        if (layer.editing && layer.editing.enabled && layer.editing.enabled()) {
            try { layer.editing.disable(); } catch (e) { /* ignore */ }
        }
        if (editablePolygons.hasLayer(layer)) editablePolygons.removeLayer(layer);
        if (otherPolygons.hasLayer(layer)) otherPolygons.removeLayer(layer);
        if (activeLayer === layer) activeLayer = null;
    }

    function clearMapLayers() {
        var all = [];
        eachPolygon(function (l) { all.push(l); });
        all.forEach(removeLayerFromMap);
        activeLayer = null;
    }

    /* -------------------------------------------------- API -------------------------------------------------- */

    function fetchFloorMeta(section, floor) {
        return fetch(cfg.ajaxBase + '&act=get_floor_meta&home_id=' + cfg.homeId + '&section=' + section + '&floor=' + floor, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .catch(function () { return { success: false, message: 'Ошибка сети при загрузке метаданных этажа', apartments: [] }; });
    }

    function fetchPolygons(section, floor) {
        return fetch(cfg.ajaxBase + '&act=get_polygons&home_id=' + cfg.homeId + '&section=' + section + '&floor=' + floor, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (list) { return Array.isArray(list) ? list : []; })
            .catch(function () { return []; });
    }

    function fetchFloorJpgMap(section) {
        var cacheKey = String(section);
        if (floorJpgMapCache[cacheKey] && floorAptMapCache[cacheKey]) {
            return Promise.resolve({
                jpg: floorJpgMapCache[cacheKey],
                apts: floorAptMapCache[cacheKey]
            });
        }
        var maxFloor = getSectionMaxFloor(section);
        return fetch(cfg.ajaxBase + '&act=floor_jpg_map&home_id=' + cfg.homeId + '&section=' + section + '&max_floor=' + maxFloor, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                var jpg = (res && res.success && res.floors) || {};
                var apts = (res && res.success && res.apartments) || {};
                floorJpgMapCache[cacheKey] = jpg;
                floorAptMapCache[cacheKey] = apts;
                return { jpg: jpg, apts: apts };
            })
            .catch(function () { return { jpg: {}, apts: {} }; });
    }

    function savePolygonApi(layer, id, label) {
        var points = layerToPoints(layer);
        if (points.length < 3) {
            return Promise.resolve({ ok: false, message: 'Меньше 3 точек' });
        }
        var body = new URLSearchParams({
            home_id: String(cfg.homeId),
            section: String(activeSection),
            floor: String(activeFloor),
            apartament_id: String(layer.fpData.apartamentId),
            floor_plan_polygon_id: id ? String(id) : '0',
            label: label || '',
            points: JSON.stringify(points)
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
                layer.fpData.id = res.id;
                layer.fpData.label = label || '';
                layer.fpData.savedPoints = clonePoints(points);
                layer.fpData.isNew = false;
                layer.edited = false;
                return { ok: true, id: res.id };
            })
            .catch(function () {
                return { ok: false, message: 'Ошибка сети при сохранении' };
            });
    }

    function deletePolygonApi(id) {
        return fetch(cfg.ajaxBase + '&act=delete_polygon', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ floor_plan_polygon_id: String(id), home_id: String(cfg.homeId) }).toString()
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res && res.success) return { ok: true };
                return { ok: false, message: (res && res.message) || 'Ошибка удаления' };
            })
            .catch(function () {
                return { ok: false, message: 'Ошибка сети при удалении' };
            });
    }

    /* ------------------------------------------- Stage 2: upload / clear --------------------------------------- */

    function invalidateFloorMapsCache(section) {
        var key = String(section);
        delete floorJpgMapCache[key];
        delete floorAptMapCache[key];
    }

    var UPLOAD_ALLOWED_EXT = ['png', 'jpg', 'jpeg', 'svg', 'webp'];

    function localExtOk(fileName) {
        var m = /\.([a-z0-9]+)$/i.exec(fileName || '');
        if (!m) return false;
        return UPLOAD_ALLOWED_EXT.indexOf(m[1].toLowerCase()) !== -1;
    }

    /**
     * XHR (не fetch — обязателен xhr.upload.onprogress) с прогрессом в % внутри
     * универсального #fp_messages (аудит H1). Тип файла на сервере определяется
     * по содержимому, а не по имени (аудит C3) — здесь только быстрая локальная
     * UX-проверка расширения до отправки запроса.
     */
    function doUploadFloorImage(file) {
        uploadInProgress = true;
        setControlsBusy(true);

        var body = new FormData();
        body.append('home_id', String(cfg.homeId));
        body.append('section', String(activeSection));
        body.append('floor', String(activeFloor));
        body.append('file', file);

        var uploadedSection = activeSection;
        var uploadedFloor = activeFloor;

        return new Promise(function (resolve) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', cfg.ajaxBase + '&act=upload_floor_image');
            xhr.withCredentials = true;

            xhr.upload.onprogress = function (evt) {
                if (!evt.lengthComputable) return;
                showUploadProgress((evt.loaded / evt.total) * 100, file.name);
            };

            function finish(result) {
                uploadInProgress = false;
                setControlsBusy(false);
                resolve(result);
            }

            xhr.onload = function () {
                var res = null;
                try { res = JSON.parse(xhr.responseText); } catch (e) { /* ignore */ }

                if (!res || !res.success) {
                    showMessage((res && res.message) || 'Ошибка загрузки файла', 'error');
                    finish({ ok: false });
                    return;
                }

                invalidateFloorMapsCache(uploadedSection);
                showMessage('Фон сохранён: секция ' + uploadedSection + ', этаж ' + uploadedFloor + ' (.' + res.ext + ')', 'success');
                // silent: не даём тихой перезагрузке затереть сообщение об успехе (аудит H2).
                loadFloor(uploadedSection, uploadedFloor, { autoDraw: false, silent: true }).then(function () {
                    finish({ ok: true });
                });
            };

            xhr.onerror = function () {
                showMessage('Сеть/сервер недоступен — попробуйте ещё раз', 'error');
                finish({ ok: false });
            };

            xhr.send(body);
        });
    }

    function uploadFloorImage(file) {
        if (isBusy() || !file) return;

        if (!localExtOk(file.name)) {
            showMessage('Неподдерживаемый формат файла. Разрешены: PNG, JPG, SVG, WEBP', 'error');
            return;
        }
        if (cfg.maxUploadBytes && file.size > cfg.maxUploadBytes) {
            showMessage('Файл больше ' + Math.round(cfg.maxUploadBytes / 1024 / 1024) + ' МБ — уменьшите размер и попробуйте снова', 'error');
            return;
        }

        function go() {
            if (hasImage) {
                var proceed = window.confirm(
                    'На этом этаже уже есть фон. Файл будет перезаписан.\n' +
                    'Существующую разметку квартир придётся сверить/возможно нарисовать заново (полигоны не удалятся).\n\n' +
                    'Продолжить?'
                );
                if (!proceed) return;
            }
            doUploadFloorImage(file);
        }

        if (!isDirty()) {
            go();
            return;
        }

        var apt = getApartmentMeta(activeApartmentId);
        showUnsavedChangesDialog(
            'Есть несохранённые изменения (квартира №' + (apt ? apt.apartmentNum : activeApartmentId) + ').\n\n' +
            '«Сохранить и перейти» — записать правки в БД, затем загрузить фон.\n' +
            '«Не сохранять» — отменить правки и загрузить фон.\n' +
            '«Остаться» — отменить загрузку фона.'
        ).then(function (action) {
            if (action === 'save') {
                saveCurrentApartmentChanges().then(function (res) {
                    if (res && res.ok) go();
                });
            } else if (action === 'discard') {
                revertCurrentApartmentChanges().then(function () { go(); });
            }
        });
    }

    function doClearFloorMarkup() {
        clearingMarkup = true;
        setControlsBusy(true);
        showMessage('Очистка разметки…', 'info');

        var body = new URLSearchParams({
            home_id: String(cfg.homeId),
            section: String(activeSection),
            floor: String(activeFloor)
        });

        return fetch(cfg.ajaxBase + '&act=clear_polygons', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                clearingMarkup = false;
                setControlsBusy(false);
                if (!res || !res.success) {
                    showMessage((res && res.message) || 'Ошибка при очистке разметки', 'error');
                    return { ok: false };
                }
                clearMapLayers();
                pendingDelete = false;
                pendingDeleteData = null;
                currentApartments.forEach(function (apt) { apt.marked = false; });
                rebuildApartmentSelect(activeApartmentId);
                clearDirtyFlags();
                invalidateFloorMapsCache(activeSection);
                updatePolygonDrawButton();
                showMessage('Разметка этажа очищена (' + (res.cleared || 0) + ' полигон(ов)). Фон остался.', 'success');
                applyApartmentSelection(activeApartmentId, true, { silent: true });
                return { ok: true };
            })
            .catch(function () {
                clearingMarkup = false;
                setControlsBusy(false);
                showMessage('Ошибка сети при очистке разметки', 'error');
                return { ok: false };
            });
    }

    function doClearFloorPlan() {
        clearingMarkup = true;
        setControlsBusy(true);
        showMessage('Очистка плана этажа…', 'info');

        var body = new URLSearchParams({
            home_id: String(cfg.homeId),
            section: String(activeSection),
            floor: String(activeFloor)
        });

        return fetch(cfg.ajaxBase + '&act=clear_floor_plan', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: body.toString()
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                clearingMarkup = false;
                setControlsBusy(false);
                if (!res || !res.success) {
                    showMessage((res && res.message) || 'Ошибка при очистке плана', 'error');
                    return { ok: false };
                }
                clearMapLayers();
                pendingDelete = false;
                pendingDeleteData = null;
                currentApartments.forEach(function (apt) { apt.marked = false; });
                rebuildApartmentSelect(activeApartmentId);
                clearDirtyFlags();
                invalidateFloorMapsCache(activeSection);
                return loadFloor(activeSection, activeFloor, { autoDraw: false, silent: true }).then(function () {
                    showMessage(
                        'План этажа очищен: удалено полигонов ' + (res.cleared || 0) +
                        ', файлов фона ' + (res.removedFiles || 0) + '.',
                        'success'
                    );
                    return { ok: true };
                });
            })
            .catch(function () {
                clearingMarkup = false;
                setControlsBusy(false);
                showMessage('Ошибка сети при очистке плана', 'error');
                return { ok: false };
            });
    }

    function clearApartmentMarkup() {
        if (isBusy()) return;

        var apt = getApartmentMeta(activeApartmentId);
        var layer = findLayerByApartment(activeApartmentId);
        if (!layer && !(pendingDelete && pendingDeleteData && parseInt(pendingDeleteData.apartamentId, 10) === activeApartmentId)) {
            showMessage('У выбранной квартиры нет разметки для очистки', 'info');
            return;
        }

        function go() {
            var proceed = window.confirm(
                'Удалить разметку ' + unitLabelCap + ' №' + (apt ? apt.apartmentNum : activeApartmentId) + '?\n' +
                'Полигон будет удалён из базы. Остальные квартиры этажа не затронутся.'
            );
            if (!proceed) return;

            clearingMarkup = true;
            setControlsBusy(true);
            showMessage('Удаление разметки квартиры…', 'info');

            var finishOk = function () {
                clearingMarkup = false;
                setControlsBusy(false);
                pendingDelete = false;
                pendingDeleteData = null;
                if (apt) apt.marked = false;
                clearDirtyFlags();
                rebuildApartmentSelect(activeApartmentId);
                invalidateFloorMapsCache(activeSection);
                applyApartmentSelection(activeApartmentId, true, { silent: true });
                updatePolygonDrawButton();
                showMessage('Разметка ' + unitLabelCap + ' №' + (apt ? apt.apartmentNum : activeApartmentId) + ' удалена', 'success');
            };

            if (pendingDelete && pendingDeleteData && parseInt(pendingDeleteData.apartamentId, 10) === activeApartmentId) {
                pendingDelete = false;
                pendingDeleteData = null;
                finishOk();
                return;
            }

            if (layer && (layer.fpData.isNew || !layer.fpData.id)) {
                removeLayerFromMap(layer);
                finishOk();
                return;
            }

            var id = layer && layer.fpData ? layer.fpData.id : null;
            if (!id) {
                clearingMarkup = false;
                setControlsBusy(false);
                showMessage('Не найден id полигона для удаления', 'error');
                return;
            }

            deletePolygonApi(id).then(function (res) {
                if (!res.ok) {
                    clearingMarkup = false;
                    setControlsBusy(false);
                    showMessage(res.message || 'Ошибка удаления', 'error');
                    return;
                }
                removeLayerFromMap(layer);
                finishOk();
            });
        }

        if (!isDirty()) {
            go();
            return;
        }

        showUnsavedChangesDialog(
            'Есть несохранённые изменения. Перед очисткой квартиры их нужно либо сохранить, либо отменить.\n\n' +
            '«Сохранить и перейти» — записать правки, затем подтверждение очистки.\n' +
            '«Не сохранять» — отменить правки, затем подтверждение очистки.\n' +
            '«Остаться» — не очищать.'
        ).then(function (action) {
            if (action === 'save') {
                saveCurrentApartmentChanges().then(function (res) {
                    if (res && res.ok) go();
                });
            } else if (action === 'discard') {
                revertCurrentApartmentChanges().then(function () { go(); });
            }
        });
    }

    function clearFloorMarkup() {
        if (isBusy()) return;

        var hasAnyPolygon = false;
        eachPolygon(function () { hasAnyPolygon = true; });
        if (!hasAnyPolygon) {
            showMessage('На этом этаже нет разметки для очистки', 'info');
            return;
        }

        function go() {
            var proceed = window.confirm(
                'Очистить разметку этажа ' + activeFloor + ' (секция ' + activeSection + ')?\n' +
                'Все полигоны квартир этого этажа будут удалены. Файл плана останется.'
            );
            if (proceed) doClearFloorMarkup();
        }

        if (!isDirty()) {
            go();
            return;
        }

        showUnsavedChangesDialog(
            'Есть несохранённые изменения. Перед очисткой разметки этажа их нужно либо сохранить, либо отменить.\n\n' +
            '«Сохранить и перейти» — записать правки в БД, затем откроется подтверждение очистки.\n' +
            '«Не сохранять» — отменить правки, затем откроется подтверждение очистки.\n' +
            '«Остаться» — не очищать разметку.'
        ).then(function (action) {
            if (action === 'save') {
                saveCurrentApartmentChanges().then(function (res) {
                    if (res && res.ok) go();
                });
            } else if (action === 'discard') {
                revertCurrentApartmentChanges().then(function () { go(); });
            }
        });
    }

    function clearWholeFloorPlan() {
        if (isBusy()) return;

        var hasAnyPolygon = false;
        eachPolygon(function () { hasAnyPolygon = true; });
        if (!hasAnyPolygon && !hasImage) {
            showMessage('На этом этаже нечего очищать — нет ни файла плана, ни разметки', 'info');
            return;
        }

        function go() {
            var proceed = window.confirm(
                'Очистить ВЕСЬ план этажа ' + activeFloor + ' (секция ' + activeSection + ')?\n' +
                'Будут удалены все полигоны И файл плана этажа.\n\n' +
                'Это действие нельзя отменить.'
            );
            if (proceed) doClearFloorPlan();
        }

        if (!isDirty()) {
            go();
            return;
        }

        showUnsavedChangesDialog(
            'Есть несохранённые изменения. Перед полной очисткой плана их нужно либо сохранить, либо отменить.\n\n' +
            '«Сохранить и перейти» — записать правки, затем подтверждение очистки.\n' +
            '«Не сохранять» — отменить правки, затем подтверждение очистки.\n' +
            '«Остаться» — не очищать.'
        ).then(function (action) {
            if (action === 'save') {
                saveCurrentApartmentChanges().then(function (res) {
                    if (res && res.ok) go();
                });
            } else if (action === 'discard') {
                revertCurrentApartmentChanges().then(function () { go(); });
            }
        });
    }

    /* ------------------------------------------------ Selects ------------------------------------------------ */

    function rebuildFloorSelect(section, selectedFloor) {
        if (!floorSelect) return;
        var max = getSectionMaxFloor(section);
        var sel = selectedFloor ? parseInt(selectedFloor, 10) : (parseInt(floorSelect.value, 10) || 1);
        if (sel < 1) sel = 1;
        if (sel > max) sel = max;

        suppressFloorChange = true;
        floorSelect.innerHTML = '';
        for (var i = 1; i <= max; i++) {
            var opt = document.createElement('option');
            opt.value = String(i);
            opt.textContent = 'Этаж ' + i;
            if (i === sel) opt.selected = true;
            floorSelect.appendChild(opt);
        }
        suppressFloorChange = false;

        fetchFloorJpgMap(section).then(function (maps) {
            var jpgMap = maps.jpg || {};
            var aptMap = maps.apts || {};
            Array.prototype.forEach.call(floorSelect.options, function (opt2) {
                var fl = parseInt(opt2.value, 10);
                var parts = ['Этаж ' + fl];
                var aptN = parseInt(aptMap[String(fl)] || aptMap[fl] || 0, 10) || 0;
                if (aptN > 0) {
                    parts.push(aptN + ' кв.');
                } else {
                    parts.push('нет кв.');
                    opt2.classList.add('is-empty-apts');
                }
                if (jpgMap && jpgMap[String(fl)] === false) {
                    opt2.classList.add('is-missing');
                    parts.push('нет файла');
                }
                opt2.textContent = parts.join(' · ');
            });
        });

        return sel;
    }

    function rebuildApartmentSelect(selectedApartmentId) {
        if (!apartmentSelect) return;
        var sel = selectedApartmentId ? parseInt(selectedApartmentId, 10) : (parseInt(apartmentSelect.value, 10) || 0);

        suppressApartmentChange = true;
        apartmentSelect.innerHTML = '';
        if (!currentApartments.length) {
            var empty = document.createElement('option');
            empty.value = '';
            empty.textContent = '— нет квартир —';
            apartmentSelect.appendChild(empty);
            suppressApartmentChange = false;
            return;
        }
        currentApartments.forEach(function (apt) {
            var opt = document.createElement('option');
            opt.value = String(apt.apartamentId);
            var label = '№' + apt.apartmentNum;
            if (apt.rooms) label += ' — ' + apt.rooms + 'к';
            if (apt.area) label += ', ' + apt.area + ' м²';
            opt.textContent = apt.marked ? (label + ' ✓') : label;
            if (apt.marked) {
                opt.className = 'is-marked';
                opt.style.fontWeight = 'bold';
            }
            if (parseInt(apt.apartamentId, 10) === sel) opt.selected = true;
            apartmentSelect.appendChild(opt);
        });
        suppressApartmentChange = false;
    }

    function revertSectionFloorSelectValues() {
        suppressSectionChange = true;
        if (sectionSelect) sectionSelect.value = String(activeSection);
        suppressSectionChange = false;
        suppressFloorChange = true;
        if (floorSelect) floorSelect.value = String(activeFloor);
        suppressFloorChange = false;
    }

    function revertApartmentSelectValue() {
        suppressApartmentChange = true;
        if (apartmentSelect) apartmentSelect.value = String(activeApartmentId);
        suppressApartmentChange = false;
    }

    /* --------------------------------------------- Selection logic -------------------------------------------- */

    /**
     * @param {object} [opts] — opts.silent=true глушит собственные info/success сообщения
     * (используется при тихой перезагрузке этажа после upload, аудит H2 — чтобы не
     * затирать «Фон сохранён» промежуточным «Квартира №N размечена…»).
     */
    function applyApartmentSelection(apartamentId, autoDraw, opts) {
        opts = opts || {};
        var silent = !!opts.silent;

        apartamentId = parseInt(apartamentId, 10);
        if (!apartamentId) return;
        stopLeafletEditHandlers();
        activeApartmentId = apartamentId;
        revertApartmentSelectValue();

        var apt = getApartmentMeta(apartamentId);
        var layer = findLayerByApartment(apartamentId);
        refreshOtherStyles();

        if (layer) {
            setEditableLayer(layer);
            if (!layer.fpData.savedPoints || !layer.fpData.savedPoints.length) snapshotLayer(layer);
            savedLabel = layer.fpData.label || '';
            if (labelInput) labelInput.value = savedLabel;
            clearDirtyFlags();
            if (!silent) {
                showMessage(unitLabelCap + ' №' + (apt ? apt.apartmentNum : apartamentId) + ' размечена — карандаш правит только её полигон.', 'success');
            }
        } else {
            setEditableLayer(null);
            savedLabel = '';
            if (labelInput) labelInput.value = '';
            clearDirtyFlags();
            if (!hasImage) {
                if (!silent) showMessage('Вначале загрузите файл плана этажа', 'info');
            } else if (autoDraw !== false) {
                startPolygonDraw();
                if (!silent) showMessage(unitLabelCap + ' №' + (apt ? apt.apartmentNum : apartamentId) + ' не размечена — включено рисование полигона на плане.', 'info');
            } else {
                if (!silent) showMessage(unitLabelCap + ' №' + (apt ? apt.apartmentNum : apartamentId) + ' не размечена — рисуйте полигон на плане.', 'info');
            }
        }
        updatePolygonDrawButton();
        syncUrlSelection();
    }

    /**
     * Смена квартиры с confirm при несохранённых правках — 3 исхода (как на фасаде):
     * Сохранить и перейти / Не сохранять и перейти / Остаться.
     */
    function requestApartmentChange(newApartamentId, autoDraw) {
        newApartamentId = parseInt(newApartamentId, 10);
        if (!newApartamentId) return;

        if (isBusy()) {
            revertApartmentSelectValue();
            return;
        }

        if (newApartamentId === activeApartmentId) {
            applyApartmentSelection(newApartamentId, autoDraw);
            return;
        }

        function go() {
            applyApartmentSelection(newApartamentId, autoDraw !== false);
        }

        if (!isDirty()) {
            go();
            return;
        }

        revertApartmentSelectValue();

        var apt = getApartmentMeta(activeApartmentId);
        showUnsavedChangesDialog(
            'Есть несохранённые изменения (квартира №' + (apt ? apt.apartmentNum : activeApartmentId) + ').\n\n' +
            '«Сохранить и перейти» — записать правки в БД и открыть выбранную квартиру.\n' +
            '«Не сохранять» — отменить правки (восстановить из БД) и перейти.\n' +
            '«Остаться» — вернуться к текущей квартире, ничего не менять.'
        ).then(function (action) {
            if (action === 'save') {
                saveCurrentApartmentChanges().then(function (res) {
                    if (res && res.ok) go();
                    else revertApartmentSelectValue();
                });
            } else if (action === 'discard') {
                revertCurrentApartmentChanges().then(function () {
                    go();
                });
            }
        });
    }

    /**
     * @param {boolean|object} [opts] — bool (обратная совместимость, = autoDraw) либо
     * { autoDraw?: bool, silent?: bool }. silent (аудит H2) глушит собственные info/warning
     * сообщения этой конкретной перезагрузки (используется после upload фона, чтобы не
     * перетирать «Фон сохранён»); ошибки показываются всегда, даже в silent-режиме.
     */
    function loadFloor(section, floor, opts) {
        if (typeof opts === 'boolean' || typeof opts === 'undefined') {
            opts = { autoDraw: opts !== false };
        }
        var autoDrawFirst = opts.autoDraw !== false;
        var silent = !!opts.silent;
        var initialApartmentId = parseInt(opts.initialApartmentId, 10) || 0;

        loadingFloor = true;
        setControlsBusy(true);
        setMoveMode(false);
        stopLeafletEditHandlers();
        activeSection = section;
        activeFloor = floor;
        activeApartmentId = 0;
        clearMapLayers();
        if (!silent) {
            showMessage('Загрузка плана этажа…', 'info');
        }

        return fetchFloorMeta(section, floor).then(function (meta) {
            currentApartments = (meta && meta.apartments) || [];
            rebuildApartmentSelect();

            if (!meta || !meta.success) {
                hasImage = false;
                showMessage('Вначале загрузите файл плана этажа', 'info');
                loadingFloor = false;
                setControlsBusy(false);
                updateDirtyUi();
                syncUrlSelection();
                return;
            }

            hasImage = true;
            setFloorImage(meta.imageUrl, meta.imageWidth, meta.imageHeight);

            return fetchPolygons(section, floor).then(function (list) {
                list.forEach(function (item) { addPolygonLayer(item, false); });
                var pickId = 0;
                if (initialApartmentId && apartmentExistsOnFloor(initialApartmentId)) {
                    pickId = initialApartmentId;
                } else if (currentApartments[0]) {
                    pickId = currentApartments[0].apartamentId;
                }
                if (pickId) {
                    applyApartmentSelection(pickId, autoDrawFirst, { silent: silent });
                } else {
                    activeApartmentId = 0;
                    syncUrlSelection();
                    if (!silent) {
                        showMessage(
                            'На этаже ' + floor + ' нет квартир в БД (тот же ключ, что шахматка: home_id=' +
                            cfg.homeId + ', секция ' + section + '). Выберите этаж, где в списке этажей есть «N кв.»',
                            'warning'
                        );
                    }
                }
                loadingFloor = false;
                setControlsBusy(false);
                restorePageTextSelection();
            });
        }).catch(function () {
            showMessage('Ошибка сети при загрузке плана', 'error');
            loadingFloor = false;
            setControlsBusy(false);
            syncUrlSelection();
        });
    }

    function requestFloorChange(newSection, newFloor, autoDraw) {
        newSection = parseInt(newSection, 10) || activeSection;
        newFloor = parseInt(newFloor, 10) || 1;

        if (isBusy()) {
            revertSectionFloorSelectValues();
            return;
        }

        if (newSection === activeSection && newFloor === activeFloor) {
            return;
        }

        function go() {
            rebuildFloorSelect(newSection, newFloor);
            loadFloor(newSection, newFloor, autoDraw !== false);
        }

        if (!isDirty()) {
            go();
            return;
        }

        revertSectionFloorSelectValues();

        var apt = getApartmentMeta(activeApartmentId);
        showUnsavedChangesDialog(
            'Есть несохранённые изменения (квартира №' + (apt ? apt.apartmentNum : activeApartmentId) + ', этаж ' + activeFloor + ').\n\n' +
            '«Сохранить и перейти» — записать правки в БД и открыть выбранный этаж.\n' +
            '«Не сохранять» — отменить правки (восстановить из БД) и перейти.\n' +
            '«Остаться» — вернуться к текущему этажу, ничего не менять.'
        ).then(function (action) {
            if (action === 'save') {
                saveCurrentApartmentChanges().then(function (res) {
                    if (res && res.ok) go();
                    else revertSectionFloorSelectValues();
                });
            } else if (action === 'discard') {
                revertCurrentApartmentChanges().then(function () {
                    go();
                });
            }
        });
    }

    function requestSectionChange(newSection) {
        newSection = parseInt(newSection, 10);
        if (!newSection) return;
        requestFloorChange(newSection, 1, true);
    }

    /* ------------------------------------------------ Save/Cancel ---------------------------------------------- */

    function saveCurrentApartmentChanges() {
        if (isBusy()) {
            return Promise.resolve({ ok: false, message: 'Операция уже выполняется' });
        }
        var apartamentId = activeApartmentId;
        var apt = getApartmentMeta(apartamentId);
        var label = labelInput ? labelInput.value.trim() : '';

        savingGeometry = true;
        setControlsBusy(true);
        showMessage('Сохранение…', 'info');

        commitLeafletDeleteIfNeeded();
        stopLeafletEditHandlers();
        setMoveMode(false);

        var chain;
        if (pendingDelete && pendingDeleteData) {
            chain = deletePolygonApi(pendingDeleteData.id).then(function (res) {
                if (!res.ok) return res;
                pendingDelete = false;
                pendingDeleteData = null;
                if (apt) apt.marked = false;
                return { ok: true, deleted: true };
            });
        } else {
            var dirtyLayers = [];
            eachPolygon(function (layer) {
                if (!layer.fpData) return;
                if (layer.fpData.isNew || layer.edited) {
                    dirtyLayers.push(layer);
                    return;
                }
                var cur = layerToPoints(layer);
                var saved = layer.fpData.savedPoints || [];
                if (!pointsEqual(cur, saved)) dirtyLayers.push(layer);
            });
            if (!dirtyLayers.length && activeLayer) {
                dirtyLayers = [activeLayer];
            }

            var okAll = true;
            var failMsg = '';
            chain = Promise.resolve({ ok: true });
            dirtyLayers.forEach(function (layer) {
                chain = chain.then(function (prev) {
                    if (!prev || !prev.ok) return prev;
                    var layerId = layer.fpData.id && !layer.fpData.isNew ? layer.fpData.id : null;
                    var layerLabel = (layer === activeLayer)
                        ? label
                        : ((layer.fpData && layer.fpData.label) || '');
                    return savePolygonApi(layer, layerId, layerLabel).then(function (res) {
                        if (!res.ok) {
                            okAll = false;
                            failMsg = res.message || 'Ошибка сохранения';
                            return res;
                        }
                        layer.edited = false;
                        var meta = getApartmentMeta(layer.fpData.apartamentId);
                        if (meta) meta.marked = true;
                        return { ok: true };
                    });
                });
            });
            chain = chain.then(function (res) {
                if (!okAll) return { ok: false, message: failMsg || (res && res.message) };
                return { ok: true };
            });
        }

        return chain.then(function (res) {
            savingGeometry = false;
            setControlsBusy(false);
            if (!res.ok) {
                showMessage(res.message || 'Ошибка при сохранении', 'error');
                return res;
            }
            clearDirtyFlags();
            rebuildApartmentSelect(apartamentId);
            updatePolygonDrawButton();
            showMessage(res.deleted
                ? ('Полигон удалён (квартира №' + (apt ? apt.apartmentNum : apartamentId) + ')')
                : ('Сохранено (квартира №' + (apt ? apt.apartmentNum : apartamentId) + ')'), 'success');
            return { ok: true };
        }, function (err) {
            savingGeometry = false;
            setControlsBusy(false);
            throw err;
        });
    }

    function revertCurrentApartmentChanges() {
        if (isBusy()) {
            return Promise.resolve({ ok: false });
        }
        reverting = true;
        setControlsBusy(true);

        revertActiveDeleteHandler();
        forceExitVertexEdit();
        showMessage('Отмена изменений…', 'info');

        var apartamentId = activeApartmentId;

        return fetchPolygons(activeSection, activeFloor).then(function (list) {
            clearMapLayers();
            list.forEach(function (item) { addPolygonLayer(item, false); });
            pendingDelete = false;
            pendingDeleteData = null;
            applyApartmentSelection(apartamentId, false);
            showMessage('Изменения отменены, геометрия восстановлена из базы', 'info');
            reverting = false;
            setControlsBusy(false);
            return { ok: true };
        }).catch(function () {
            showMessage('Изменения отменены (не удалось обновить из БД)', 'warning');
            reverting = false;
            setControlsBusy(false);
            return { ok: true };
        });
    }

    /* ------------------------------------------------- Events --------------------------------------------------- */

    map.on('draw:drawstart', function () {
        setMoveMode(false);
        if (!canAddPolygon()) {
            var toolbars = drawControl && drawControl._toolbars;
            var modes = toolbars && toolbars.draw && toolbars.draw._modes;
            var handler = modes && modes.polygon && modes.polygon.handler;
            if (handler && handler.enabled && handler.enabled()) {
                try { handler.disable(); } catch (e) { /* ignore */ }
            }
            stopPolygonDraw();
            updatePolygonDrawButton();
            showMessage(unitLabelCap + ' уже размечена — правьте полигон карандашом или удалите и нарисуйте заново', 'error');
            return;
        }
        drawToolActive = true;
        restorePageTextSelection();
        var toolbarsOk = drawControl && drawControl._toolbars;
        var modesOk = toolbarsOk && toolbarsOk.draw && toolbarsOk.draw._modes;
        var handlerOk = modesOk && modesOk.polygon && modesOk.polygon.handler;
        if (handlerOk && handlerOk.enabled && handlerOk.enabled()) {
            attachDrawCursorGuide(handlerOk);
            if (!polygonDrawer) polygonDrawer = handlerOk;
        } else if (polygonDrawer) {
            attachDrawCursorGuide(polygonDrawer);
        }
    });
    map.on('draw:drawstop', function () {
        drawToolActive = false;
        polygonDrawer = null;
        restorePageTextSelection();
        updatePolygonDrawButton();
    });
    map.on('mouseup', restorePageTextSelection);
    map.on('dragend', restorePageTextSelection);
    map.on('draw:editstart', function () {
        setMoveMode(false);
        drawToolActive = true;
        if (!activeLayer) {
            showMessage('Сначала выберите размеченную квартиру в списке', 'error');
            return;
        }
        showMessage('Правка точек — затем «Применить точки» в панели Leaflet, а «Сохранить» в форме запишет их в БД', 'info');
    });
    map.on('draw:editvertex', function () {
        markGeometryDirty();
    });
    map.on('draw:deletestart', function () {
        setMoveMode(false);
        drawToolActive = true;
        deleteModeActive = true;
        showMessage('Удаление: клик по полигону → галочка ✓. В БД — только после «Сохранить» в форме.', 'info');
    });
    map.on(L.Draw.Event.DELETED, function (e) {
        e.layers.eachLayer(function (layer) {
            stashDeletedLayer(layer);
        });
        updateDirtyUi();
    });
    map.on('draw:deletestop', function () {
        drawToolActive = false;
        deleteModeActive = false;
        restorePageTextSelection();
    });

    map.on(L.Draw.Event.CREATED, function (e) {
        var apt = getApartmentMeta(activeApartmentId);
        var existing = findLayerByApartment(activeApartmentId);
        if (existing) {
            showMessage(
                unitLabelCap + ' №' + (apt ? apt.apartmentNum : activeApartmentId) +
                ' уже размечена — правьте существующий полигон или удалите его и нарисуйте заново',
                'error'
            );
            updatePolygonDrawButton();
            return;
        }

        var label = labelInput ? labelInput.value.trim() : '';
        var layer = e.layer;

        layer.fpData = {
            id: null,
            apartamentId: activeApartmentId,
            apartmentNum: apt ? apt.apartmentNum : 0,
            label: label,
            color: STYLE_DEFAULT.color,
            savedPoints: clonePoints(layerToPoints(layer)),
            isNew: true
        };
        layer.edited = false;
        bindPolygonClick(layer);

        setEditableLayer(layer);
        stopPolygonDraw();
        markGeometryDirty();
        updatePolygonDrawButton();
        showMessage('Новый полигон (не сохранён): квартира №' + (apt ? apt.apartmentNum : activeApartmentId) + ' — «Сохранить» запишет в БД, «Отменить» — уберёт черновик', 'warning');
    });

    map.on(L.Draw.Event.EDITED, function (e) {
        e.layers.eachLayer(function (layer) {
            layer.edited = true;
            var current = layerToPoints(layer);
            var saved = layer.fpData && layer.fpData.savedPoints;
            if (layer.fpData && layer.fpData.isNew) {
                markGeometryDirty();
                return;
            }
            if (!pointsEqual(current, saved)) {
                markGeometryDirty();
            }
        });
        if (isDirty()) {
            showMessage('Изменения на карте — «Сохранить» / «Отменить» в форме', 'warning');
        }
    });

    map.on('draw:editstop', function () {
        drawToolActive = false;
        restorePageTextSelection();
        var stillDirty = false;
        if (activeLayer) {
            if (activeLayer.fpData && activeLayer.fpData.isNew) {
                stillDirty = true;
            } else {
                var current = layerToPoints(activeLayer);
                var saved = activeLayer.fpData && activeLayer.fpData.savedPoints;
                if (!pointsEqual(current, saved)) {
                    activeLayer.edited = true;
                    stillDirty = true;
                } else {
                    activeLayer.edited = false;
                }
            }
        }
        if (stillDirty || pendingDelete) {
            markGeometryDirty();
        } else if (!metaDirty) {
            geometryDirty = false;
            updateDirtyUi();
            if (activeLayer) {
                showMessage('Размечена — карандаш правит только эту квартиру', 'success');
            }
        } else {
            geometryDirty = false;
            updateDirtyUi();
        }
    });

    if (sectionSelect) {
        sectionSelect.addEventListener('change', function () {
            if (suppressSectionChange) return;
            requestSectionChange(parseInt(sectionSelect.value, 10));
        });
    }

    if (floorSelect) {
        floorSelect.addEventListener('change', function () {
            if (suppressFloorChange) return;
            requestFloorChange(activeSection, parseInt(floorSelect.value, 10), true);
        });
    }

    if (apartmentSelect) {
        apartmentSelect.addEventListener('change', function () {
            if (suppressApartmentChange) return;
            requestApartmentChange(parseInt(apartmentSelect.value, 10), true);
        });
    }

    if (labelInput) {
        labelInput.addEventListener('input', function () {
            var cur = labelInput.value.trim();
            metaDirty = cur !== savedLabel;
            updateDirtyUi();
            if (metaDirty) {
                showMessage('Есть несохранённые правки', 'warning');
            } else if (!geometryDirty && !pendingDelete && activeLayer) {
                showMessage('Размечена — карандаш правит только эту квартиру', 'success');
            }
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener('click', function () {
            if (isBusy()) return;
            saveCurrentApartmentChanges();
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            if (isBusy()) return;
            revertCurrentApartmentChanges();
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
            if (file) uploadFloorImage(file);
        });
    }

    if (copyFloorBtn) {
        copyFloorBtn.addEventListener('click', function () {
            if (isBusy()) return;
            setMoveMode(false);
            requestCopyFloorMarkup();
        });
    }

    if (clearApartmentBtn) {
        clearApartmentBtn.addEventListener('click', function () {
            if (isBusy()) return;
            clearApartmentMarkup();
        });
    }

    if (clearMarkupBtn) {
        clearMarkupBtn.addEventListener('click', function () {
            if (isBusy()) return;
            clearFloorMarkup();
        });
    }

    if (clearPlanBtn) {
        clearPlanBtn.addEventListener('click', function () {
            if (isBusy()) return;
            clearWholeFloorPlan();
        });
    }

    window.addEventListener('beforeunload', function (e) {
        if (!isDirty()) return;
        e.preventDefault();
        e.returnValue = '';
        return '';
    });

    // Стартовая загрузка: section/floor/apartment из GET, если есть.
    var initial = resolveInitialSelection();
    activeSection = initial.section;
    activeFloor = initial.floor;
    rebuildFloorSelect(initial.section, initial.floor);
    loadFloor(initial.section, initial.floor, {
        autoDraw: true,
        initialApartmentId: initial.apartment
    });
})();
