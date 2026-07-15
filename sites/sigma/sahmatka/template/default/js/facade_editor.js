(function () {
    'use strict';

    var cfg = window.FACADE_CONFIG;
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
                    edit: 'Редактировать выбранный этаж',
                    editDisabled: 'Выберите размеченный этаж',
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

    var messagesEl = document.getElementById('facade_messages');
    var sectionSelect = document.getElementById('facade_section_select');
    var floorSelect = document.getElementById('facade_floor_select');
    var labelInput = document.getElementById('facade_label_input');
    var saveBtn = document.getElementById('facade_floor_save');
    var cancelBtn = document.getElementById('facade_floor_cancel');
    var dirtyActions = document.getElementById('facade_dirty_actions');
    var uploadInput = document.getElementById('facade_upload_input');
    var uploadBtn = document.getElementById('facade_upload_btn');
    var clearFloorBtn = document.getElementById('facade_clear_floor');
    var clearAllBtn = document.getElementById('facade_clear_all');

    var drawToolActive = false;
    var deleteModeActive = false;
    var savingGeometry = false;
    var reverting = false;
    var uploadInProgress = false;
    var clearingMarkup = false;
    var hasImage = !!(cfg.imageUrl && cfg.imageWidth && cfg.imageHeight);
    var currentOverlay = null;
    /** @type {Object.<string, {id:number,section:number,floor:number,label:string,color:string,points:Array}>} */
    var pendingDeletes = {};
    var flushingDeletes = false;
    var selectedLayers = [];
    var polygonDrawer = null;
    var suppressFloorChange = false;
    var suppressSectionChange = false;
    var geometryDirty = false;
    var metaDirty = false;
    var sections = Array.isArray(cfg.sections) && cfg.sections.length
        ? cfg.sections
        : [{ id: 1, caption: 'Секция 1', maxFloor: cfg.maxFloor || 30 }];
    var activeSection = parseInt(sections[0].id, 10) || 1;
    var activeFloor = 1;
    var savedLabel = '';

    function getSectionMeta(sectionId) {
        sectionId = parseInt(sectionId, 10);
        for (var i = 0; i < sections.length; i++) {
            if (parseInt(sections[i].id, 10) === sectionId) return sections[i];
        }
        return sections[0];
    }

    function getSectionMaxFloor(sectionId) {
        var meta = getSectionMeta(sectionId);
        var max = meta && meta.maxFloor ? parseInt(meta.maxFloor, 10) : (cfg.maxFloor || 30);
        return max > 0 ? max : 30;
    }

    function getSectionCaption(sectionId) {
        var meta = getSectionMeta(sectionId);
        return (meta && meta.caption) ? meta.caption : ('Секция ' + sectionId);
    }

    var STYLE_DEFAULT = {
        color: '#3388ff',
        weight: 2,
        fillColor: '#3388ff',
        fillOpacity: 0.35,
        opacity: 1,
        dashArray: null
    };
    var STYLE_OTHER_SECTION = {
        color: '#94a3b8',
        weight: 1,
        fillColor: '#94a3b8',
        fillOpacity: 0.2,
        opacity: 0.85,
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

    /**
     * Универсальный API сообщений формы (заменяет отдельные setStatus/setFloorState) —
     * единый компактный блок под панелью этажа, в той же рамке. Поддерживает типы
     * info / success / warning / error, у каждого свой цвет/иконка (см. CSS).
     * @param {string} text
     * @param {'info'|'success'|'warning'|'error'} [type='info']
     */
    function showMessage(text, type) {
        if (!messagesEl) return;
        if (MESSAGE_TYPES.indexOf(type) === -1) type = 'info';

        messagesEl.innerHTML = '';
        if (!text) {
            messagesEl.className = 'facade-editor__messages';
            return;
        }

        messagesEl.className = 'facade-editor__messages is-' + type;

        var icon = document.createElement('span');
        icon.className = 'facade-editor__messages-icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.textContent = MESSAGE_ICONS[type];

        var textEl = document.createElement('span');
        textEl.className = 'facade-editor__messages-text';
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
            if (Math.abs(a[i][0] - b[i][0]) > POINTS_EPS || Math.abs(a[i][1] - b[i][1]) > POINTS_EPS) return false;
        }
        return true;
    }

    function hasPendingDeletes() {
        return Object.keys(pendingDeletes).length > 0;
    }

    function hasPendingNews() {
        var found = false;
        eachPolygon(function (layer) {
            if (layer.facadeData && layer.facadeData.isNew) found = true;
        });
        return found;
    }

    function isDirty() {
        return geometryDirty || metaDirty || hasPendingDeletes() || hasPendingNews();
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
        updateDirtyUi();
    }

    /**
     * Save (fetch к серверу) или Cancel (revert геометрии) выполняются асинхронно —
     * пока один из них не закончился, второй запускать нельзя (H3): иначе гонка
     * remount/restore vs. in-flight save может задублировать слои или потерять id.
     */
    function isBusy() {
        return savingGeometry || flushingDeletes || reverting || uploadInProgress || clearingMarkup;
    }

    function setControlsBusy(busy) {
        if (saveBtn) saveBtn.disabled = !!busy;
        if (cancelBtn) cancelBtn.disabled = !!busy;
        if (sectionSelect) sectionSelect.disabled = !!busy;
        if (floorSelect) floorSelect.disabled = !!busy;
        if (uploadBtn) uploadBtn.disabled = !!busy;
        if (clearFloorBtn) clearFloorBtn.disabled = !!busy;
        if (clearAllBtn) clearAllBtn.disabled = !!busy;
    }

    /**
     * Модалка вместо window.confirm (только OK/Cancel) — нужен третий исход
     * «Остаться», иначе случайный клик по этажу вынуждает либо сохранить,
     * либо discard-нуть черновик (C1).
     * @return {Promise<'save'|'discard'|'stay'>}
     */
    function showUnsavedChangesDialog(message) {
        return new Promise(function (resolve) {
            var overlay = document.createElement('div');
            overlay.className = 'facade-editor__modal-overlay';

            var box = document.createElement('div');
            box.className = 'facade-editor__modal-box';

            var text = document.createElement('p');
            text.className = 'facade-editor__modal-text';
            text.textContent = message;
            box.appendChild(text);

            var actions = document.createElement('div');
            actions.className = 'facade-editor__modal-actions';

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
                b.className = 'facade-editor__btn' + (btn.primary ? ' facade-editor__btn--primary' : '');
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

    function stashDeletedLayer(layer, confirmed) {
        if (!layer || !layer.facadeData) return false;
        // новый (ещё не в БД) — просто убираем с карты, в pending не кладём
        if (layer.facadeData.isNew || !layer.facadeData.id) {
            return false;
        }
        var id = String(layer.facadeData.id);
        pendingDeletes[id] = {
            id: layer.facadeData.id,
            section: parseInt(layer.facadeData.section, 10) || activeSection,
            floor: layer.facadeData.floor,
            label: layer.facadeData.label || '',
            color: layer.facadeData.color || STYLE_DEFAULT.color,
            points: clonePoints(layer.facadeData.savedPoints && layer.facadeData.savedPoints.length
                ? layer.facadeData.savedPoints
                : layerToPoints(layer)),
            confirmed: !!confirmed
        };
        return true;
    }

    /**
     * Если режим удаления Leaflet ещё активен — зафиксировать удалённые слои
     * (иногда пользователь жмёт «Сохранить» в форме, не нажав ✓ в тулбаре).
     */
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
            try {
                handler.disable();
            } catch (e2) { /* ignore */ }

            deleteModeActive = false;
            if (any) markGeometryDirty();
        });
    }

    /**
     * Leaflet.Draw (и Draggable) вызывают DomUtil.disableTextSelection() —
     * это вешает preventDefault на window "selectstart" и блокирует
     * выделение текста на всей странице (в т.ч. подсказка над картой).
     * Возвращаем выделение после инструментов и не оставляем блокировку.
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
        if (!layer.facadeData) layer.facadeData = {};
        layer.facadeData.savedPoints = clonePoints(layerToPoints(layer));
        layer.edited = false;
    }

    /**
     * Выключить карандаш/удаление Leaflet.Draw и editing на слоях —
     * иначе после setLatLngs остаются «зависшие» маркеры вершин.
     */
    function stopLeafletEditHandlers() {
        forceExitVertexEdit();
    }

    function revertActiveDeleteHandler() {
        var toolbars = drawControl && drawControl._toolbars;
        var tb = toolbars && toolbars.edit;
        if (!tb || !tb._modes) return;

        Object.keys(tb._modes).forEach(function (mode) {
            var handler = tb._modes[mode].handler;
            if (!handler || !handler.enabled || !handler.enabled()) return;
            // revertLayers только для режима удаления — иначе Edit вернёт
            // промежуточный backup, а маркеры останутся рассинхронены.
            if (handler.type === 'remove' && typeof handler.revertLayers === 'function') {
                try { handler.revertLayers(); } catch (e) { /* ignore */ }
            }
            try { handler.disable(); } catch (e2) { /* ignore */ }
        });

        Object.keys(pendingDeletes).forEach(function (id) {
            if (!pendingDeletes[id].confirmed) {
                delete pendingDeletes[id];
            }
        });
        deleteModeActive = false;
        removeOrphanEditMarkers();
    }

    function restoreLayerFromSnapshot(layer) {
        if (!layer || !layer.facadeData || !layer.facadeData.savedPoints) return layer;
        // пересоздаём слой — setLatLngs при активном editing даёт рассинхрон маркеров
        var toEditable = editablePolygons.hasLayer(layer);
        return remountPolygonFromPoints(layer, layer.facadeData.savedPoints, toEditable);
    }

    function baseColorOf(layer) {
        return (layer && layer.facadeData && layer.facadeData.color) || STYLE_DEFAULT.color;
    }

    function applyDefaultStyle(layer) {
        if (!layer || !layer.setStyle) return;
        var section = layer.facadeData && parseInt(layer.facadeData.section, 10);
        if (section && section !== activeSection) {
            layer.setStyle(STYLE_OTHER_SECTION);
            return;
        }
        var c = baseColorOf(layer);
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

    /**
     * Принудительно выйти из режима правки вершин Leaflet.Draw.
     * Иначе setLatLngs двигает линии, а маркеры остаются на старых координатах.
     */
    function forceExitVertexEdit() {
        stopPolygonDraw();
        drawToolActive = false;
        deleteModeActive = false;

        // сначала снять editing с каждого слоя (убирает vertex markers)
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
            // сбросить пунктир режима правки
            if (layer.setStyle) {
                var section = layer.facadeData && parseInt(layer.facadeData.section, 10);
                if (section && section === activeSection && selectedLayers.indexOf(layer) !== -1) {
                    applySelectedStyle(layer);
                } else {
                    applyDefaultStyle(layer);
                }
            }
        });

        var toolbars = drawControl && drawControl._toolbars;
        if (toolbars && toolbars.edit) {
            var tb = toolbars.edit;
            // handler.disable без toolbar.disable — без повторного revertLayers
            if (tb._modes) {
                Object.keys(tb._modes).forEach(function (mode) {
                    var handler = tb._modes[mode].handler;
                    if (!handler) return;
                    if (handler.enabled && handler.enabled()) {
                        try { handler.disable(); } catch (e) { /* ignore */ }
                    }
                });
            }
            // сбросить активную кнопку тулбара, если зависла
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
            if (cls.indexOf('leaflet-editing-icon') !== -1 || cls.indexOf('facade-edit-vertex') !== -1) {
                orphan.push(layer);
            }
        });
        orphan.forEach(function (m) {
            try { map.removeLayer(m); } catch (e) { /* ignore */ }
        });

        // на всякий случай — DOM-маркеры без leaflet-слоя
        var pane = map.getPanes && map.getPanes().markerPane;
        if (pane) {
            var nodes = pane.querySelectorAll('.leaflet-editing-icon, .facade-edit-vertex');
            Array.prototype.forEach.call(nodes, function (el) {
                if (el && el.parentNode) el.parentNode.removeChild(el);
            });
        }
    }

    /**
     * Пересоздать полигон из точек — единственный надёжный способ
     * избежать рассинхрона path/маркеров после отмены.
     */
    function remountPolygonFromPoints(layer, points, toEditable) {
        if (!layer || !layer.facadeData) return null;
        if (layer.editing) {
            try { layer.editing.disable(); } catch (e) { /* ignore */ }
        }
        var data = {
            id: layer.facadeData.id,
            section: layer.facadeData.section,
            floor: layer.facadeData.floor,
            label: layer.facadeData.label || '',
            color: layer.facadeData.color || STYLE_DEFAULT.color,
            points: clonePoints(points || layer.facadeData.savedPoints || layerToPoints(layer))
        };
        removeLayerFromMap(layer);
        return addPolygonLayer(data, !!toEditable);
    }

    var map = L.map('facade_map', {
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

    /**
     * Минимальный zoom = «вписать картинку в контейнер» (contain).
     * Ниже нельзя: обе оси картинки становятся меньше контейнера,
     * все грани «оторваны» от краёв (как на скрине).
     */
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

    function setFacadeImage(url, w, h) {
        if (currentOverlay) {
            try { map.removeLayer(currentOverlay); } catch (e) { /* ignore */ }
            currentOverlay = null;
        }
        cfg.imageUrl = url;
        cfg.imageWidth = w;
        cfg.imageHeight = h;
        hasImage = !!(url && w && h);
        if (!hasImage) {
            imageBounds = L.latLngBounds([[0, 0], [1000, 1000]]);
            map.setMaxBounds(null);
            return;
        }
        imageBounds = L.latLngBounds([[0, 0], [h, w]]);
        currentOverlay = L.imageOverlay(url, imageBounds).addTo(map);
        map.fitBounds(imageBounds);
        updateMinZoomFromImage();
        map.setMaxBounds(imageBounds.pad(0.5));
    }

    if (hasImage) {
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
        edit: { featureGroup: editablePolygons, remove: true },
        draw: {
            polygon: { allowIntersection: false, showArea: false },
            polyline: false,
            rectangle: false,
            circle: false,
            marker: false,
            circlemarker: false
        }
    });
    map.addControl(drawControl);

    function eachPolygon(fn) {
        otherPolygons.eachLayer(fn);
        editablePolygons.eachLayer(fn);
    }

    function getMarkedFloors(section) {
        section = parseInt(section, 10) || activeSection;
        var floors = {};
        eachPolygon(function (layer) {
            if (!layer.facadeData || !layer.facadeData.floor) return;
            if (parseInt(layer.facadeData.section, 10) !== section) return;
            floors[layer.facadeData.floor] = true;
        });
        return floors;
    }

    function findLayersBySectionFloor(section, floor) {
        var list = [];
        section = parseInt(section, 10);
        floor = parseInt(floor, 10);
        eachPolygon(function (layer) {
            if (!layer.facadeData) return;
            if (parseInt(layer.facadeData.section, 10) === section &&
                parseInt(layer.facadeData.floor, 10) === floor) {
                list.push(layer);
            }
        });
        return list;
    }

    function findLayersByFloor(floor) {
        return findLayersBySectionFloor(activeSection, floor);
    }

    function rebuildFloorSelect(selectedFloor) {
        if (!floorSelect) return;
        var marked = getMarkedFloors(activeSection);
        var max = getSectionMaxFloor(activeSection);
        var sel = selectedFloor ? parseInt(selectedFloor, 10) : parseInt(floorSelect.value, 10) || 1;
        if (sel < 1) sel = 1;
        if (sel > max) sel = max;

        suppressFloorChange = true;
        floorSelect.innerHTML = '';
        for (var i = 1; i <= max; i++) {
            var opt = document.createElement('option');
            opt.value = String(i);
            var isMarked = !!marked[i];
            opt.textContent = isMarked ? ('Этаж ' + i + ' ✓') : ('Этаж ' + i);
            if (isMarked) {
                opt.className = 'is-marked';
                opt.style.fontWeight = 'bold';
            }
            if (i === sel) opt.selected = true;
            floorSelect.appendChild(opt);
        }
        suppressFloorChange = false;
    }

    function refreshOtherSectionStyles() {
        eachPolygon(function (layer) {
            if (!selectedLayers.length || selectedLayers.indexOf(layer) === -1) {
                applyDefaultStyle(layer);
            }
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

    function startPolygonDraw() {
        if (!hasImage) {
            showMessage('Вначале загрузите файл фасада', 'info');
            return;
        }
        stopPolygonDraw();
        polygonDrawer = new L.Draw.Polygon(map, drawControl.options.draw.polygon);
        polygonDrawer.enable();
        drawToolActive = true;
        // Draw.Feature.addHooks() глушит выделение на всём document — снимаем сразу
        restorePageTextSelection();
    }

    function setEditableLayers(layers) {
        var keep = {};
        (layers || []).forEach(function (l) {
            keep[L.stamp(l)] = true;
        });

        var toOther = [];
        editablePolygons.eachLayer(function (l) {
            if (!keep[L.stamp(l)]) toOther.push(l);
        });
        toOther.forEach(function (l) {
            editablePolygons.removeLayer(l);
            otherPolygons.addLayer(l);
            applyDefaultStyle(l);
        });

        (layers || []).forEach(function (l) {
            if (otherPolygons.hasLayer(l)) {
                otherPolygons.removeLayer(l);
            }
            if (!editablePolygons.hasLayer(l)) {
                editablePolygons.addLayer(l);
            }
            applySelectedStyle(l);
        });

        selectedLayers = layers || [];
    }

    function clearSelectionVisual() {
        eachPolygon(function (layer) {
            applyDefaultStyle(layer);
        });
        var move = [];
        editablePolygons.eachLayer(function (l) { move.push(l); });
        move.forEach(function (l) {
            editablePolygons.removeLayer(l);
            otherPolygons.addLayer(l);
        });
        selectedLayers = [];
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
            if (drawToolActive || deleteModeActive) return;
            if (e.originalEvent) {
                L.DomEvent.stopPropagation(e.originalEvent);
                if (e.originalEvent.target && e.originalEvent.target.blur) {
                    e.originalEvent.target.blur();
                }
            }
            if (!layer.facadeData || !layer.facadeData.floor) return;
            var sec = parseInt(layer.facadeData.section, 10) || 1;
            var fl = parseInt(layer.facadeData.floor, 10);
            requestSectionFloorChange(sec, fl, false);
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
        layer.facadeData = {
            id: item.id || null,
            section: parseInt(item.section, 10) || 1,
            floor: item.floor,
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
    }

    function clearAllMapPolygons() {
        var all = [];
        eachPolygon(function (l) { all.push(l); });
        all.forEach(removeLayerFromMap);
        selectedLayers = [];
        pendingDeletes = {};
    }

    function savePolygon(layer, section, floor, id, label) {
        var points = layerToPoints(layer);
        if (points.length < 3) {
            return Promise.resolve({ ok: false, message: 'Меньше 3 точек' });
        }

        section = parseInt(section, 10) || activeSection;

        var body = new URLSearchParams({
            home_id: String(cfg.homeId),
            section: String(section),
            floor: String(floor),
            facade_polygon_id: id ? String(id) : '0',
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
                layer.facadeData = layer.facadeData || {};
                layer.facadeData.id = res.id;
                layer.facadeData.section = section;
                layer.facadeData.floor = floor;
                layer.facadeData.label = label || '';
                layer.facadeData.color = baseColorOf(layer);
                layer.facadeData.savedPoints = clonePoints(points);
                layer.facadeData.isNew = false;
                layer.edited = false;
                return { ok: true, id: res.id };
            })
            .catch(function () {
                return { ok: false, message: 'Ошибка сети при сохранении' };
            });
    }

    function deletePolygonApi(id) {
        id = String(id);
        return fetch(cfg.ajaxBase + '&act=delete_polygon', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ facade_polygon_id: id, home_id: String(cfg.homeId) }).toString()
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

    /**
     * Минимальная защита от неатомарности delete+save (C2): при ошибке
     * (частичный успех сохранения слоёв, ошибка удаления) подтягиваем
     * актуальное состояние из БД, чтобы bookkeeping (savedPoints/id/label/
     * pendingDeletes) не расходился с сервером. Геометрию слоёв на карте не
     * трогаем — пользователь может повторить «Сохранить» или нажать «Отменить».
     */
    function resyncFloorFromServer() {
        return fetch(cfg.ajaxBase + '&act=get_polygons&home_id=' + cfg.homeId, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (list) {
                if (!Array.isArray(list)) list = [];
                var byId = {};
                list.forEach(function (item) { byId[String(item.id)] = item; });

                Object.keys(pendingDeletes).forEach(function (id) {
                    if (!byId[id]) delete pendingDeletes[id];
                });

                findLayersByFloor(activeFloor).forEach(function (layer) {
                    var id = layer.facadeData && layer.facadeData.id;
                    var item = id ? byId[String(id)] : null;
                    if (item) {
                        layer.facadeData.savedPoints = clonePoints(item.points);
                        layer.facadeData.label = item.label || '';
                        layer.facadeData.color = item.color || layer.facadeData.color;
                        layer.facadeData.section = parseInt(item.section, 10) || layer.facadeData.section;
                        layer.facadeData.isNew = false;
                    } else if (id && !layer.facadeData.isNew) {
                        // считался сохранённым, но в БД такого id нет — по факту это несохранённый черновик
                        layer.facadeData.isNew = true;
                        layer.facadeData.id = null;
                    }
                });

                rebuildFloorSelect(activeFloor);
                updateDirtyUi();
            })
            .catch(function () { /* сеть недоступна — оставляем локальное состояние как есть */ });
    }

    function refreshFloorPanelAfterStructureChange() {
        var layers = findLayersByFloor(activeFloor);
        selectedLayers = layers.filter(function (l) {
            return editablePolygons.hasLayer(l) || otherPolygons.hasLayer(l);
        });
        if (layers.length) {
            setEditableLayers(layers);
            savedLabel = layers[0].facadeData.label || savedLabel;
            if (labelInput && !metaDirty) labelInput.value = layers[0].facadeData.label || '';
        } else {
            clearSelectionVisual();
            refreshOtherSectionStyles();
        }
        if (hasPendingDeletes() || hasPendingNews() || geometryDirty) {
            markGeometryDirty();
        }
        rebuildFloorSelect(activeFloor);
        updateDirtyUi();
    }

    function saveCurrentFloorChanges() {
        var floor = activeFloor;
        var label = labelInput ? labelInput.value.trim() : '';

        if (isBusy()) {
            return Promise.resolve({ ok: false, message: 'Операция уже выполняется' });
        }

        savingGeometry = true;
        setControlsBusy(true);
        showMessage('Сохранение…', 'info');

        // Сначала зафиксировать удаления Leaflet (если ✓ в тулбаре не нажали),
        // потом flush в БД — и только потом глушить хендлеры.
        commitLeafletDeleteIfNeeded();

        var resultPromise = flushPendingDeletes().then(function (delRes) {
            if (delRes && delRes.ok === false) {
                stopLeafletEditHandlers();
                showMessage(delRes.message || 'Ошибка удаления', 'error');
                // C2: удаление и сохранение геометрии — не одна транзакция,
                // поэтому при ошибке подтягиваем актуальное состояние из БД.
                return resyncFloorFromServer().then(function () {
                    savingGeometry = false;
                    updateDirtyUi();
                    return { ok: false, message: delRes.message };
                });
            }

            stopLeafletEditHandlers();

            var layers = findLayersByFloor(floor);
            if (!layers.length) {
                savingGeometry = false;
                savedLabel = '';
                clearDirtyFlags();
                showMessage('Сохранено (полигоны удалены: ' + getSectionCaption(activeSection) + ', этаж ' + floor + ')', 'success');
                rebuildFloorSelect(floor);
                applyFloorSelection(floor, true);
                return { ok: true };
            }

            var okAll = true;
            var failMsg = '';
            var chain = Promise.resolve();

            layers.forEach(function (layer) {
                chain = chain.then(function () {
                    if (!okAll) return;
                    var id = layer.facadeData && layer.facadeData.id && !layer.facadeData.isNew
                        ? layer.facadeData.id
                        : null;
                    return savePolygon(layer, activeSection, floor, id, label).then(function (res) {
                        if (!res.ok) {
                            okAll = false;
                            failMsg = res.message || 'Ошибка сохранения';
                        }
                    });
                });
            });

            return chain.then(function () {
                if (okAll) {
                    savingGeometry = false;
                    savedLabel = label;
                    clearDirtyFlags();
                    showMessage('Сохранено (' + getSectionCaption(activeSection) + ', этаж ' + floor + ')', 'success');
                    rebuildFloorSelect(floor);
                    applyFloorSelection(floor, false);
                    return { ok: true };
                }
                showMessage(failMsg || 'Ошибка при сохранении', 'error');
                // C2: часть слоёв этажа может быть уже сохранена в БД, а часть — нет.
                // Подтягиваем реальное состояние, чтобы Save/Cancel дальше не расходились.
                return resyncFloorFromServer().then(function () {
                    savingGeometry = false;
                    updateDirtyUi();
                    return { ok: false, message: failMsg };
                });
            });
        });

        return resultPromise.then(function (res) {
            setControlsBusy(false);
            return res;
        }, function (err) {
            savingGeometry = false;
            setControlsBusy(false);
            throw err;
        });
    }

    function revertCurrentFloorChanges() {
        if (isBusy()) {
            return Promise.resolve({ ok: false });
        }
        reverting = true;
        setControlsBusy(true);

        // сначала полностью выйти из карандаша/удаления — иначе маркеры «отвяжутся» от линий
        revertActiveDeleteHandler();
        forceExitVertexEdit();
        showMessage('Отмена изменений…', 'info');

        // убрать несохранённые новые полигоны
        var news = [];
        eachPolygon(function (layer) {
            if (layer.facadeData && layer.facadeData.isNew) news.push(layer);
        });
        news.forEach(removeLayerFromMap);

        // вернуть удалённые (ещё не в БД) из stash
        Object.keys(pendingDeletes).forEach(function (id) {
            var item = pendingDeletes[id];
            if (!item || !item.points || !item.points.length) return;
            addPolygonLayer({
                id: item.id,
                section: item.section || 1,
                floor: item.floor,
                label: item.label,
                color: item.color,
                points: item.points
            }, false);
        });
        pendingDeletes = {};

        function finishLocal(msg) {
            clearDirtyFlags();
            rebuildFloorSelect(activeFloor);
            applyFloorSelection(activeFloor, false);
            forceExitVertexEdit();
            showMessage(msg || 'Изменения отменены', 'info');
            reverting = false;
            setControlsBusy(false);
        }

        return fetch(cfg.ajaxBase + '&act=get_polygons&home_id=' + cfg.homeId, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (list) {
                if (!Array.isArray(list)) list = [];
                var byId = {};
                list.forEach(function (item) {
                    byId[String(item.id)] = item;
                });

                // пересоздать полигоны текущего этажа из БД (без setLatLngs в edit-режиме)
                var current = findLayersByFloor(activeFloor).slice();
                current.forEach(function (layer) {
                    var id = layer.facadeData && layer.facadeData.id;
                    var item = id ? byId[String(id)] : null;
                    var points = item && item.points
                        ? clonePoints(item.points)
                        : (layer.facadeData.savedPoints || layerToPoints(layer));
                    if (item) {
                        layer.facadeData.section = parseInt(item.section, 10) || layer.facadeData.section || 1;
                        layer.facadeData.label = item.label || '';
                        layer.facadeData.color = item.color || layer.facadeData.color;
                        layer.facadeData.savedPoints = points;
                        layer.facadeData.isNew = false;
                    } else {
                        layer.facadeData.savedPoints = points;
                    }
                    remountPolygonFromPoints(layer, points, false);
                });

                forceExitVertexEdit();
                finishLocal('Изменения отменены, геометрия восстановлена из базы');
            })
            .catch(function () {
                findLayersByFloor(activeFloor).slice().forEach(function (layer) {
                    remountPolygonFromPoints(layer, layer.facadeData.savedPoints, false);
                });
                forceExitVertexEdit();
                finishLocal('Изменения отменены (локальный снимок)');
            });
    }

    function applyFloorSelection(floor, autoDraw) {
        floor = parseInt(floor, 10);
        if (!floor) return;

        stopLeafletEditHandlers();
        activeFloor = floor;

        var layers = findLayersBySectionFloor(activeSection, floor);

        suppressSectionChange = true;
        if (sectionSelect) sectionSelect.value = String(activeSection);
        suppressSectionChange = false;

        suppressFloorChange = true;
        if (floorSelect) floorSelect.value = String(floor);
        suppressFloorChange = false;

        refreshOtherSectionStyles();

        if (layers.length) {
            setEditableLayers(layers);
            layers.forEach(function (layer) {
                if (!layer.facadeData.savedPoints || !layer.facadeData.savedPoints.length) {
                    snapshotLayer(layer);
                }
            });
            var dbLabel = layers[0].facadeData.label || '';
            // C3: не перетирать локально введённую (несохранённую) подпись —
            // savedLabel обновляем всегда (это «эталон из БД»), но input/clearDirtyFlags
            // трогаем только если правок реально нет.
            savedLabel = dbLabel;
            if (!hasPendingDeletes() && !hasPendingNews() && !geometryDirty && !metaDirty) {
                if (labelInput) labelInput.value = savedLabel;
                clearDirtyFlags();
                showMessage(getSectionCaption(activeSection) + ', этаж ' + floor + ' размечен — карандаш правит только этот этаж секции.', 'success');
            } else {
                updateDirtyUi();
                showMessage('Есть несохранённые правки', 'warning');
            }
        } else {
            clearSelectionVisual();
            refreshOtherSectionStyles();
            if (!hasPendingDeletes() && !hasPendingNews() && !geometryDirty && !metaDirty) {
                savedLabel = '';
                if (labelInput) labelInput.value = '';
                clearDirtyFlags();
                if (!hasImage) {
                    showMessage('Вначале загрузите файл фасада', 'info');
                } else if (autoDraw !== false) {
                    startPolygonDraw();
                    showMessage(getSectionCaption(activeSection) + ', этаж ' + floor + ' не размечен — включено рисование полигона на фасаде.', 'info');
                } else {
                    showMessage(getSectionCaption(activeSection) + ', этаж ' + floor + ' не размечен — рисуйте полигон на фасаде.', 'info');
                }
            } else {
                updateDirtyUi();
                showMessage('Есть несохранённые удаления/добавления — «Сохранить» или «Отменить»', 'warning');
            }
        }
    }

    function applySectionSelection(section, floor, autoDraw) {
        section = parseInt(section, 10) || activeSection;
        activeSection = section;
        rebuildFloorSelect(floor || 1);
        var max = getSectionMaxFloor(activeSection);
        var fl = parseInt(floor, 10) || 1;
        if (fl > max) fl = 1;
        applyFloorSelection(fl, autoDraw);
    }

    function revertSelectValuesToActive() {
        suppressSectionChange = true;
        if (sectionSelect) sectionSelect.value = String(activeSection);
        suppressSectionChange = false;
        suppressFloorChange = true;
        if (floorSelect) floorSelect.value = String(activeFloor);
        suppressFloorChange = false;
    }

    /**
     * Смена секции и/или этажа с confirm при несохранённых правках.
     * 3 исхода (C1): Сохранить и перейти / Не сохранять и перейти / Остаться.
     */
    function requestSectionFloorChange(newSection, newFloor, autoDraw) {
        newSection = parseInt(newSection, 10) || activeSection;
        newFloor = parseInt(newFloor, 10) || 1;

        if (isBusy()) {
            // Save/Cancel уже выполняются — select мог визуально измениться, вернуть.
            revertSelectValuesToActive();
            return;
        }

        if (newSection === activeSection && newFloor === activeFloor) {
            // C3: применяем повторный выбор того же этажа (например, клик по другому
            // полигону того же этажа), но applyFloorSelection сама не трогает
            // подпись/dirty-флаги, если есть несохранённые правки (см. её код).
            applyFloorSelection(newFloor, autoDraw);
            return;
        }

        function go() {
            applySectionSelection(newSection, newFloor, autoDraw !== false);
        }

        if (!isDirty()) {
            go();
            return;
        }

        // select уже мог визуально смениться (событие change) до этого confirm — сразу вернуть,
        // а переход выполнить только по явному выбору пользователя в диалоге.
        revertSelectValuesToActive();

        showUnsavedChangesDialog(
            'Есть несохранённые изменения (' + getSectionCaption(activeSection) + ', этаж ' + activeFloor + ').\n\n' +
            '«Сохранить и перейти» — записать правки в БД и открыть выбранный этаж.\n' +
            '«Не сохранять» — отменить правки (восстановить из БД) и перейти.\n' +
            '«Остаться» — вернуться к текущему этажу, ничего не менять.'
        ).then(function (action) {
            if (action === 'save') {
                saveCurrentFloorChanges().then(function (res) {
                    if (res && res.ok) {
                        go();
                    } else {
                        revertSelectValuesToActive();
                    }
                });
            } else if (action === 'discard') {
                revertCurrentFloorChanges().then(function () {
                    go();
                });
            }
            // 'stay' — select уже возвращены на activeSection/activeFloor, больше ничего не делаем
        });
    }

    function requestFloorChange(newFloor, autoDraw) {
        requestSectionFloorChange(activeSection, newFloor, autoDraw);
    }

    function requestSectionChange(newSection) {
        newSection = parseInt(newSection, 10);
        if (!newSection) return;
        // H4: при смене секции остаёмся на текущем этаже, если он есть в новой секции,
        // иначе переходим на последний доступный этаж новой секции.
        var max = getSectionMaxFloor(newSection);
        var fl = Math.min(activeFloor, max);
        if (fl < 1) fl = 1;
        requestSectionFloorChange(newSection, fl, true);
    }

    map.on('draw:drawstart', function () {
        drawToolActive = true;
        restorePageTextSelection();
    });
    map.on('draw:drawstop', function () {
        drawToolActive = false;
        polygonDrawer = null;
        restorePageTextSelection();
    });
    map.on('mouseup', restorePageTextSelection);
    map.on('dragend', restorePageTextSelection);
    map.on('draw:editstart', function () {
        drawToolActive = true;
        if (!selectedLayers.length && !findLayersByFloor(activeFloor).length) {
            showMessage('Сначала выберите размеченный этаж в списке', 'error');
            return;
        }
        showMessage('Правка точек — затем «Применить точки» в панели Leaflet, а «Сохранить» в форме запишет их в БД', 'info');
    });
    map.on('draw:editvertex', function () {
        markGeometryDirty();
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
        });
        selectedLayers = selectedLayers.filter(function (l) {
            return editablePolygons.hasLayer(l) || otherPolygons.hasLayer(l);
        });
        if (any || e.layers.getLayers().length) {
            markGeometryDirty();
        }
        // M4: единственный refresh на само удаление — draw:deletestop больше не дублирует его.
        refreshFloorPanelAfterStructureChange();
        updateDirtyUi();
        showMessage('Полигон удалён локально (не в БД) — нажмите «Сохранить», чтобы применить, или «Отменить»', 'warning');
    });

    map.on('draw:deletestop', function () {
        drawToolActive = false;
        deleteModeActive = false;
        restorePageTextSelection();
    });

    editablePolygons.on('layerremove', function (e) {
        var layer = e.layer;
        if (!deleteModeActive || !layer || !layer.facadeData) return;
        if (stashDeletedLayer(layer, false)) {
            markGeometryDirty();
        }
    });
    editablePolygons.on('layeradd', function (e) {
        var layer = e.layer;
        // Снимать из pending только если Leaflet вернул слой при Отмене режима удаления.
        // После ✓ (confirmed) и при обычном setEditableLayers — НЕ трогаем pendingDeletes.
        if (!deleteModeActive) return;
        if (!layer || !layer.facadeData || !layer.facadeData.id) return;
        var id = String(layer.facadeData.id);
        var stashed = pendingDeletes[id];
        if (stashed && stashed.confirmed) return;
        delete pendingDeletes[id];
        updateDirtyUi();
    });

    map.on(L.Draw.Event.CREATED, function (e) {
        var floor = parseInt(floorSelect.value, 10) || activeFloor;
        var section = activeSection;
        var label = labelInput ? labelInput.value.trim() : '';
        var layer = e.layer;

        layer.facadeData = {
            id: null,
            section: section,
            floor: floor,
            label: label,
            color: STYLE_DEFAULT.color,
            savedPoints: clonePoints(layerToPoints(layer)),
            isNew: true
        };
        layer.edited = false;
        bindPolygonClick(layer);

        // H2: разрешаем несколько несохранённых черновиков на одном этаже —
        // делаем редактируемыми ВСЕ полигоны этажа (включая уже существующие
        // и другие черновики), а не только только что нарисованный.
        clearSelectionVisual();
        var floorLayers = findLayersBySectionFloor(section, floor);
        if (floorLayers.indexOf(layer) === -1) floorLayers.push(layer);
        setEditableLayers(floorLayers);
        selectedLayers = floorLayers;

        stopPolygonDraw();
        markGeometryDirty();
        rebuildFloorSelect(floor);
        showMessage('Новый полигон (не сохранён): ' + getSectionCaption(section) + ', этаж ' + floor + ' — «Сохранить» запишет в БД, «Отменить» — уберёт черновик', 'warning');
    });

    map.on(L.Draw.Event.EDITED, function (e) {
        e.layers.eachLayer(function (layer) {
            layer.edited = true;
            var current = layerToPoints(layer);
            var saved = layer.facadeData && layer.facadeData.savedPoints;
            if (layer.facadeData && layer.facadeData.isNew) {
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
        selectedLayers.forEach(function (layer) {
            if (layer.facadeData && layer.facadeData.isNew) {
                stillDirty = true;
                return;
            }
            var current = layerToPoints(layer);
            var saved = layer.facadeData && layer.facadeData.savedPoints;
            if (!pointsEqual(current, saved)) {
                layer.edited = true;
                stillDirty = true;
            } else {
                layer.edited = false;
            }
        });
        if (stillDirty || hasPendingDeletes() || hasPendingNews()) {
            markGeometryDirty();
        } else if (!metaDirty) {
            geometryDirty = false;
            updateDirtyUi();
            if (selectedLayers.length) {
                showMessage('Размечен — карандаш правит только этот этаж', 'success');
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
            requestFloorChange(parseInt(floorSelect.value, 10), true);
        });
    }

    if (labelInput) {
        labelInput.addEventListener('input', function () {
            var cur = labelInput.value.trim();
            metaDirty = cur !== savedLabel;
            updateDirtyUi();
            if (metaDirty) {
                showMessage('Есть несохранённые правки', 'warning');
            } else if (!geometryDirty && !hasPendingDeletes() && !hasPendingNews() && selectedLayers.length) {
                showMessage('Размечен — карандаш правит только этот этаж', 'success');
            }
        });
    }

    if (saveBtn) {
        saveBtn.addEventListener('click', function () {
            if (isBusy()) return;
            saveCurrentFloorChanges();
        });
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', function () {
            if (isBusy()) return;
            revertCurrentFloorChanges();
        });
    }

    var UPLOAD_ALLOWED_EXT = ['png', 'jpg', 'jpeg', 'webp'];

    function localExtOk(fileName) {
        var m = /\.([a-z0-9]+)$/i.exec(fileName || '');
        if (!m) return false;
        return UPLOAD_ALLOWED_EXT.indexOf(m[1].toLowerCase()) !== -1;
    }

    function doUploadFacadeImage(file) {
        uploadInProgress = true;
        setControlsBusy(true);
        showMessage('Загрузка фасада…', 'info');

        var body = new FormData();
        body.append('home_id', String(cfg.homeId));
        body.append('file', file);

        return new Promise(function (resolve) {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', cfg.ajaxBase + '&act=upload_facade_image');
            xhr.withCredentials = true;

            xhr.onload = function () {
                uploadInProgress = false;
                setControlsBusy(false);
                var res = null;
                try { res = JSON.parse(xhr.responseText); } catch (e) { /* ignore */ }
                if (!res || !res.success) {
                    showMessage((res && res.message) || 'Ошибка загрузки файла', 'error');
                    resolve({ ok: false });
                    return;
                }
                setFacadeImage(res.imageUrl, res.imageWidth, res.imageHeight);
                showMessage('Фасад сохранён (.' + res.ext + ')', 'success');
                applyFloorSelection(activeFloor, false);
                resolve({ ok: true });
            };

            xhr.onerror = function () {
                uploadInProgress = false;
                setControlsBusy(false);
                showMessage('Сеть/сервер недоступен — попробуйте ещё раз', 'error');
                resolve({ ok: false });
            };

            xhr.send(body);
        });
    }

    function uploadFacadeImage(file) {
        if (isBusy() || !file) return;

        if (!localExtOk(file.name)) {
            showMessage('Неподдерживаемый формат файла. Разрешены: PNG, JPG, WEBP', 'error');
            return;
        }
        if (cfg.maxUploadBytes && file.size > cfg.maxUploadBytes) {
            showMessage('Файл больше ' + Math.round(cfg.maxUploadBytes / 1024 / 1024) + ' МБ — уменьшите размер и попробуйте снова', 'error');
            return;
        }

        function go() {
            if (hasImage) {
                var proceed = window.confirm(
                    'Файл фасада уже есть. Он будет перезаписан.\n' +
                    'Существующую разметку этажей придётся сверить (полигоны не удалятся).\n\n' +
                    'Продолжить?'
                );
                if (!proceed) return;
            }
            doUploadFacadeImage(file);
        }

        if (!isDirty()) {
            go();
            return;
        }

        showUnsavedChangesDialog(
            'Есть несохранённые изменения.\n\n' +
            '«Сохранить и перейти» — записать правки, затем загрузить фасад.\n' +
            '«Не сохранять» — отменить правки и загрузить фасад.\n' +
            '«Остаться» — отменить загрузку.'
        ).then(function (action) {
            if (action === 'save') {
                saveCurrentFloorChanges().then(function (res) {
                    if (res && res.ok) go();
                });
            } else if (action === 'discard') {
                revertCurrentFloorChanges().then(function () { go(); });
            }
        });
    }

    function doClearFloorPolygons() {
        clearingMarkup = true;
        setControlsBusy(true);
        showMessage('Очистка разметки этажа…', 'info');

        var body = new URLSearchParams({
            home_id: String(cfg.homeId),
            section: String(activeSection),
            floor: String(activeFloor)
        });

        return fetch(cfg.ajaxBase + '&act=clear_floor_polygons', {
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
                    showMessage((res && res.message) || 'Ошибка очистки этажа', 'error');
                    return { ok: false };
                }
                findLayersByFloor(activeFloor).slice().forEach(removeLayerFromMap);
                Object.keys(pendingDeletes).forEach(function (id) {
                    var d = pendingDeletes[id];
                    if (d && parseInt(d.section, 10) === activeSection && parseInt(d.floor, 10) === activeFloor) {
                        delete pendingDeletes[id];
                    }
                });
                clearDirtyFlags();
                rebuildFloorSelect(activeFloor);
                applyFloorSelection(activeFloor, true);
                showMessage('Разметка этажа очищена (' + (res.cleared || 0) + ' полигон(ов)). Файл фасада остался.', 'success');
                return { ok: true };
            })
            .catch(function () {
                clearingMarkup = false;
                setControlsBusy(false);
                showMessage('Ошибка сети при очистке этажа', 'error');
                return { ok: false };
            });
    }

    function clearCurrentFloorMarkup() {
        if (isBusy()) return;

        var layers = findLayersByFloor(activeFloor);
        if (!layers.length && !hasPendingDeletes() && !hasPendingNews()) {
            showMessage('На этом этаже нет разметки для очистки', 'info');
            return;
        }

        function go() {
            var proceed = window.confirm(
                'Очистить разметку этажа ' + activeFloor + ' (' + getSectionCaption(activeSection) + ')?\n' +
                'Полигоны этого этажа будут удалены. Файл фасада останется.'
            );
            if (proceed) doClearFloorPolygons();
        }

        if (!isDirty()) {
            go();
            return;
        }

        showUnsavedChangesDialog(
            'Есть несохранённые изменения. Перед очисткой этажа их нужно либо сохранить, либо отменить.\n\n' +
            '«Сохранить и перейти» — записать правки, затем подтверждение очистки.\n' +
            '«Не сохранять» — отменить правки, затем подтверждение очистки.\n' +
            '«Остаться» — не очищать.'
        ).then(function (action) {
            if (action === 'save') {
                saveCurrentFloorChanges().then(function (res) {
                    if (res && res.ok) go();
                });
            } else if (action === 'discard') {
                revertCurrentFloorChanges().then(function () { go(); });
            }
        });
    }

    function doClearFacade() {
        clearingMarkup = true;
        setControlsBusy(true);
        showMessage('Очистка всего фасада…', 'info');

        return fetch(cfg.ajaxBase + '&act=clear_facade', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ home_id: String(cfg.homeId) }).toString()
        })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                clearingMarkup = false;
                setControlsBusy(false);
                if (!res || !res.success) {
                    showMessage((res && res.message) || 'Ошибка очистки фасада', 'error');
                    return { ok: false };
                }
                stopPolygonDraw();
                clearAllMapPolygons();
                clearDirtyFlags();
                setFacadeImage('', 0, 0);
                rebuildFloorSelect(activeFloor);
                applyFloorSelection(activeFloor, false);
                showMessage(
                    'Весь фасад очищен: полигонов ' + (res.cleared || 0) +
                    ', файлов ' + (res.removedFiles || 0) + '.',
                    'success'
                );
                return { ok: true };
            })
            .catch(function () {
                clearingMarkup = false;
                setControlsBusy(false);
                showMessage('Ошибка сети при очистке фасада', 'error');
                return { ok: false };
            });
    }

    function clearWholeFacade() {
        if (isBusy()) return;

        var hasAny = false;
        eachPolygon(function () { hasAny = true; });
        if (!hasAny && !hasImage) {
            showMessage('Нечего очищать — нет ни файла фасада, ни разметки', 'info');
            return;
        }

        function go() {
            var proceed = window.confirm(
                'Очистить ВЕСЬ фасад дома?\n' +
                'Будут удалены все полигоны ВСЕХ секций/этажей И файл фасада.\n\n' +
                'Это действие нельзя отменить.'
            );
            if (proceed) doClearFacade();
        }

        if (!isDirty()) {
            go();
            return;
        }

        showUnsavedChangesDialog(
            'Есть несохранённые изменения. Перед полной очисткой фасада их нужно либо сохранить, либо отменить.\n\n' +
            '«Сохранить и перейти» — записать правки, затем подтверждение очистки.\n' +
            '«Не сохранять» — отменить правки, затем подтверждение очистки.\n' +
            '«Остаться» — не очищать.'
        ).then(function (action) {
            if (action === 'save') {
                saveCurrentFloorChanges().then(function (res) {
                    if (res && res.ok) go();
                });
            } else if (action === 'discard') {
                revertCurrentFloorChanges().then(function () { go(); });
            }
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
            if (file) uploadFacadeImage(file);
        });
    }

    if (clearFloorBtn) {
        clearFloorBtn.addEventListener('click', function () {
            if (isBusy()) return;
            clearCurrentFloorMarkup();
        });
    }

    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function () {
            if (isBusy()) return;
            clearWholeFacade();
        });
    }

    // M1: F5/закрытие вкладки без предупреждения при несохранённых правках.
    window.addEventListener('beforeunload', function (e) {
        if (!isDirty()) return;
        e.preventDefault();
        e.returnValue = '';
        return '';
    });

    function loadPolygons() {
        fetch(cfg.ajaxBase + '&act=get_polygons&home_id=' + cfg.homeId, { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (list) {
                if (!Array.isArray(list)) list = [];
                list.forEach(function (item) {
                    addPolygonLayer(item, false);
                });
                activeSection = parseInt(sections[0].id, 10) || 1;
                if (sectionSelect) {
                    suppressSectionChange = true;
                    sectionSelect.value = String(activeSection);
                    suppressSectionChange = false;
                }
                rebuildFloorSelect(1);
                applyFloorSelection(1, true);
                restorePageTextSelection();
            })
            .catch(function () {
                showMessage('Не удалось загрузить полигоны (проверьте миграцию БД)', 'error');
                activeSection = parseInt(sections[0].id, 10) || 1;
                rebuildFloorSelect(1);
                applyFloorSelection(1, true);
                restorePageTextSelection();
            });
    }

    loadPolygons();
})();
