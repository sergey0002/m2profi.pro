<script>
$(document).ready(function() {
    var cfg = window.M2PROFI_CONFIG || {};
    var siteBase = cfg.baseUrl || '';
    var ajaxRouter = cfg.ajaxRouter || (siteBase + '/sahmatka/ajax_router.php');
    var tree = $('#doc_tree');
    var searchInput = $('#doc-search-input');
    var searchClear = $('#doc-search-clear');
    var showDeletedCheckbox = $('#show-deleted-checkbox');
    var dateFrom = $('#date-from');
    var dateTo = $('#date-to');
    var searchTimeout = false;
    var pendingReveal = null;
    var highlightTimer = null;
    var deletingIds = {};

    // Инициализация jQuery UI Datepicker с русской локализацией
    $.datepicker.regional['ru'] = {
        closeText: 'Закрыть',
        prevText: 'Предыдущий',
        nextText: 'Следующий',
        currentText: 'Сегодня',
        monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
        monthNamesShort: ['Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'],
        dayNames: ['воскресенье','понедельник','вторник','среда','четверг','пятница','суббота'],
        dayNamesShort: ['вск','пнд','втр','срд','чтв','птн','сбт'],
        dayNamesMin: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
        weekHeader: 'Нед',
        dateFormat: 'dd.mm.yy',
        firstDay: 1,
        isRTL: false,
        showMonthAfterYear: false,
        yearSuffix: ''
    };
    $.datepicker.setDefaults($.datepicker.regional['ru']);

    // Инициализация datepicker для поля "от"
    dateFrom.datepicker({
        dateFormat: 'dd.mm.yy',
        changeMonth: true,
        changeYear: true,
        yearRange: '2000:2050',
        onSelect: function(selectedDate) {
            dateTo.datepicker('option', 'minDate', selectedDate);
            tree.jstree(true).refresh();
        }
    });

    // Инициализация datepicker для поля "до"
    dateTo.datepicker({
        dateFormat: 'dd.mm.yy',
        changeMonth: true,
        changeYear: true,
        yearRange: '2000:2050',
        onSelect: function(selectedDate) {
            dateFrom.datepicker('option', 'maxDate', selectedDate);
            tree.jstree(true).refresh();
        }
    });

    function getNodeEl(nodeId) {
        if (!nodeId) {
            return null;
        }
        var el = document.getElementById(nodeId);
        if (el) {
            return el;
        }
        var safe = String(nodeId).replace(/(:|\.|\[|\]|,|=|@)/g, '\\$1');
        return tree.find('#' + safe)[0] || null;
    }

    function highlightNode(nodeId) {
        var inst = tree.jstree(true);
        if (!inst || !nodeId || !inst.get_node(nodeId)) {
            return;
        }
        addNodeElements();
        var el = getNodeEl(nodeId);
        if (!el) {
            return;
        }
        var top = $(el).offset().top - Math.max(80, Math.round($(window).height() / 4));
        $('html, body').stop(true).animate({ scrollTop: Math.max(0, top) }, 400);
        tree.find('.highlight-node').removeClass('highlight-node');
        $(el).addClass('highlight-node');
        if (highlightTimer) {
            clearTimeout(highlightTimer);
        }
        highlightTimer = setTimeout(function() {
            $(el).removeClass('highlight-node');
        }, 3500);
    }

    function animateDeleteSuccess(node, keepVisible, onDone) {
        var el = getNodeEl(node && node.id);
        var inst = tree.jstree(true);
        var finishGuard = function() {
            if (typeof onDone === 'function') {
                onDone();
            }
        };
        if (!el || !inst) {
            if (keepVisible) {
                inst && inst.refresh();
            } else if (node && inst) {
                inst.delete_node(node);
                addNodeElements();
            }
            finishGuard();
            return;
        }
        $(el).addClass('highlight-delete');
        setTimeout(function() {
            if (keepVisible) {
                inst.refresh();
                finishGuard();
                return;
            }
            var done = false;
            var finish = function() {
                if (done) {
                    return;
                }
                done = true;
                if (inst.get_node(node)) {
                    inst.delete_node(node);
                }
                addNodeElements();
                finishGuard();
            };
            el.style.display = 'block';
            el.style.overflow = 'hidden';
            el.style.height = el.offsetHeight + 'px';
            void el.offsetHeight;
            $(el).addClass('doc-node-removing');
            el.style.height = '0px';
            setTimeout(finish, 500);
        }, 400);
    }

    function isMagnificOpen() {
        return !!(window.jQuery && $.magnificPopup && $.magnificPopup.instance && $.magnificPopup.instance.isOpen);
    }

    function isDocModalOpen() {
        return $('#doc-modal-overlay').length > 0;
    }

    function whenLayoutReady(cb) {
        var tries = 0;
        function tick() {
            if ((isMagnificOpen() || isDocModalOpen()) && tries < 40) {
                tries += 1;
                setTimeout(tick, 50);
                return;
            }
            setTimeout(function() {
                if (window.requestAnimationFrame) {
                    requestAnimationFrame(function() { requestAnimationFrame(cb); });
                } else {
                    cb();
                }
            }, 150);
        }
        tick();
    }

    function captureInitialFilex($overlay) {
        var $form = $overlay.find('form');
        if (!$form.length) {
            $overlay.removeAttr('data-initial-filex');
            return;
        }
        var val = $form.find('input[name="filex"]').val() || '';
        $overlay.attr('data-initial-filex', val);
        $form.attr('data-initial-filex', val);
    }

    function isDocModalDirty() {
        var $overlay = $('#doc-modal-overlay');
        var $form = $overlay.find('form');
        if (!$form.length) {
            return false;
        }
        var initial = $form.attr('data-initial-filex');
        if (initial === undefined) {
            initial = $overlay.attr('data-initial-filex') || '';
        }
        var current = $form.find('input[name="filex"]').val() || '';
        if (String(current) !== String(initial)) {
            return true;
        }
        var $submit = $form.find('[type=submit]');
        if ($submit.prop('disabled')) {
            return true;
        }
        var submitVal = String($submit.val() || $submit.text() || '');
        return submitVal.indexOf('Загрузка') !== -1;
    }

    function finishDocModalRemove($overlay) {
        $(document).off('keydown.docModal');
        $overlay.remove();
        $('body').css('overflow', '');
    }

    function closeDocModal(opts) {
        opts = opts || {};
        var $overlay = $('#doc-modal-overlay');
        if (!$overlay.length) {
            return;
        }
        if ($overlay.hasClass('is-closing')) {
            return;
        }
        if (!opts.force && isDocModalDirty()) {
            if (!window.confirm('Файл ещё не сохранён в архив. Закрыть?')) {
                return;
            }
        }
        $(document).off('keydown.docModal');
        $overlay.removeClass('is-open').addClass('is-closing');
        var done = false;
        function finish() {
            if (done) {
                return;
            }
            done = true;
            finishDocModalRemove($overlay);
        }
        $overlay.one('transitionend', function(e) {
            if (e.target === $overlay[0]) {
                finish();
            }
        });
        setTimeout(finish, 320);
    }

    function loadDocModalBody($overlay, url) {
        $overlay.find('.doc-modal__body').html('<div style="padding:24px;color:#666;">Загрузка…</div>');
        $overlay.removeAttr('data-initial-filex');
        $.ajax({
            url: url,
            type: 'GET',
            dataType: 'html',
            success: function(html) {
                $overlay.find('.doc-modal__body').html(html);
                captureInitialFilex($overlay);
            },
            error: function() {
                $overlay.find('.doc-modal__body').html(
                    '<div style="padding:24px;color:#c00;">Не удалось загрузить форму</div>'
                );
            }
        });
    }

    function bindDocModalChrome($overlay) {
        $overlay.on('click.docModalBg', function(e) {
            if (e.target === $overlay[0]) {
                closeDocModal();
            }
        });
        $overlay.find('.doc-modal__close').on('click', function() {
            closeDocModal();
        });
        $(document).off('keydown.docModal').on('keydown.docModal', function(e) {
            if (e.key === 'Escape' || e.keyCode === 27) {
                closeDocModal();
            }
        });
    }

    function openDocModal(url) {
        var $existing = $('#doc-modal-overlay');
        if ($existing.hasClass('is-closing')) {
            finishDocModalRemove($existing);
            $existing = $('#doc-modal-overlay');
        }
        if ($existing.length) {
            loadDocModalBody($existing, url);
            return;
        }
        var $overlay = $(
            '<div id="doc-modal-overlay" class="doc-modal-overlay" role="dialog" aria-modal="true">' +
                '<div class="doc-modal">' +
                    '<button type="button" class="doc-modal__close" title="Закрыть" aria-label="Закрыть">&times;</button>' +
                    '<div class="doc-modal__body"><div style="padding:24px;color:#666;">Загрузка…</div></div>' +
                '</div>' +
            '</div>'
        );
        $('body').append($overlay).css('overflow', 'hidden');
        bindDocModalChrome($overlay);
        loadDocModalBody($overlay, url);
        var markOpen = function() {
            $overlay.addClass('is-open');
        };
        if (window.requestAnimationFrame) {
            requestAnimationFrame(function() {
                requestAnimationFrame(markOpen);
            });
        } else {
            setTimeout(markOpen, 16);
        }
    }

    function openDocEditModal(opts) {
        var q = 'ctr=doc&act=edit&modal=1';
        if (opts && opts.fileId) {
            q += '&id=' + encodeURIComponent(opts.fileId);
        }
        if (opts && opts.dirId) {
            q += '&dir_id=' + encodeURIComponent(opts.dirId);
        }
        openDocModal(ajaxRouter + '?' + q);
    }

    function openDocCardModal(fileId) {
        openDocModal(ajaxRouter + '?ctr=doc&act=card&modal=1&id=' + encodeURIComponent(fileId));
    }

    function applySaveResult(response) {
        if (!response || response.status !== 'success') {
            alert((response && response.message) ? response.message : 'Не удалось сохранить');
            return;
        }
        var fileId = parseInt(response.file_id, 10) || 0;
        var dirId = parseInt(response.dir_id, 10) || 0;
        pendingReveal = {
            fileId: fileId ? ('file_' + fileId) : null,
            dirId: dirId ? ('dir_' + dirId) : null,
            warnDateFilter: !!(dateFrom.val() || dateTo.val()) && !!fileId && !response.deleted
        };
        if (response.deleted) {
            if (!showDeletedCheckbox.is(':checked')) {
                pendingReveal.fileId = null;
            }
            pendingReveal.warnDateFilter = false;
        }
        closeDocModal({ force: true });
        refreshTree();
    }

    function openAncestors(inst, nodeId, done) {
        var node = inst.get_node(nodeId);
        if (!node) {
            done(false);
            return;
        }
        var path = [];
        (node.parents || []).slice().reverse().forEach(function(id) {
            if (id && id !== '#') {
                path.push(id);
            }
        });
        if (node.type === 'folder') {
            path.push(node.id);
        } else if (node.parent && node.parent !== '#') {
            path.push(node.parent);
        }

        function openNext(i) {
            if (i >= path.length) {
                done(true);
                return;
            }
            inst.open_node(path[i], function() {
                openNext(i + 1);
            }, false);
        }
        openNext(0);
    }

    function revealPendingNode() {
        if (!pendingReveal) {
            return;
        }
        var inst = tree.jstree(true);
        if (!inst) {
            return;
        }
        var fileId = pendingReveal.fileId;
        var dirId = pendingReveal.dirId;
        var warnDateFilter = !!pendingReveal.warnDateFilter;
        var fileMissing = !!(warnDateFilter && fileId && !inst.get_node(fileId));
        var targetId = (fileId && inst.get_node(fileId)) ? fileId : dirId;

        function showDateFilterToast() {
            var toast = $('<div class="doc-modal__toast doc-filter-toast" style="margin:8px 0;">Сохранено, но скрыто фильтром дат</div>');
            $('.doc-filter-toast').remove();
            var $box = $('.doc-controls-wrapper').first();
            if ($box.length) {
                $box.after(toast);
                setTimeout(function() { toast.fadeOut(400, function() { toast.remove(); }); }, 5000);
            }
        }

        if (fileMissing) {
            showDateFilterToast();
        }

        if (!targetId || !inst.get_node(targetId)) {
            pendingReveal = null;
            return;
        }
        pendingReveal = null;

        openAncestors(inst, targetId, function() {
            if (typeof inst.save_state === 'function') {
                inst.save_state();
            }
            addNodeElements();
            whenLayoutReady(function() {
                highlightNode(fileId && inst.get_node(fileId) ? fileId : targetId);
            });
        });
    }

    function refreshTree() {
        tree.one('refresh.jstree', function() {
            revealPendingNode();
        });
        tree.jstree(true).refresh();
    }

    function getTreeDataUrl() {
        var showDeleted = showDeletedCheckbox.is(':checked') ? 1 : 0;
        var params = 'ctr=doc&act=get_tree_data&show_deleted=' + showDeleted;
        
        var dateFromVal = dateFrom.val();
        var dateToVal = dateTo.val();
        
        if (dateFromVal) {
            params += '&date_from=' + encodeURIComponent(dateFromVal);
        }
        if (dateToVal) {
            params += '&date_to=' + encodeURIComponent(dateToVal);
        }
        
        return ajaxRouter + '?' + params;
    }

    function addNodeElements() {
        tree.find('.jstree-node').each(function() {
            var node = tree.jstree(true).get_node(this.id);
            var anchor = $(this).find('.jstree-anchor').first();

            // Добавляем HTML кнопок, если его нет
            if (node && node.data && node.data.actions_html && anchor.find('.tree-actions').length === 0) {
                anchor.append(node.data.actions_html);
            }

            // Добавляем даты для файлов, если их нет
            if (node.type === 'file' && node.data && (node.data.docdate || node.data.uptime)) {
                if (anchor.find('.doc-dates').length === 0) {
                    var datesText = '';
                    if (node.data.docdate) datesText += 'Документ от: ' + node.data.docdate;
                    if (node.data.uptime) {
                        if (datesText) datesText += ' / ';
                        datesText += 'Обновлен: ' + node.data.uptime;
                    }
                    if (datesText) {
                        anchor.append('<span class="doc-dates">' + datesText + '</span>');
                    }
                }
            }

            // Добавляем кнопку мобильного меню только на мобильных устройствах для ВСЕХ узлов
            if (window.innerWidth <= 768 && anchor.find('.mobile-menu-btn').length === 0) {
                anchor.append('<span class="mobile-menu-btn">⋮</span>');
            }
        });
    }

    var plugins = ["types", "wholerow", "search", "state"];
    if (window.innerWidth > 768) {
        plugins.push("dnd");
    }

    tree.jstree({
        'core': {
            'data': {
                'url': function() {
                    return getTreeDataUrl();
                },
                'dataType': 'json'
            },
            'check_callback': function (operation, node, parent, position, more) {
                if (operation !== 'move_node' && operation !== 'copy_node') {
                    return true;
                }
                // Программные перестановки (нормализация папки→файлы) не ограничиваем
                if (more && (more.core || more.origin === false || more.dnd === false)) {
                    return true;
                }

                var parentNode = this.get_node(parent);
                if (!parentNode) {
                    return false;
                }

                var movingId = node.id;
                var folderCount = 0;
                (parentNode.children || []).forEach(function (cid) {
                    if (cid === movingId) {
                        return;
                    }
                    var child = this.get_node(cid);
                    if (child && child.type === 'folder') {
                        folderCount++;
                    }
                }.bind(this));

                // Папки только среди папок, файлы только среди файлов
                if (node.type === 'folder') {
                    return position <= folderCount;
                }
                return position >= folderCount;
            },
            'themes': {
                'name': 'default',
                'responsive': true,
                'stripes': false
            }
        },
        'plugins': plugins,
        'state': {
            'key': 'doc_tree_state',
            'preserve_loaded': false
        },
        'types': {
            'folder': { 'icon': 'jstree-icon jstree-themeicon-custom jstree-themeicon-folder' },
            'file': { 'icon': 'jstree-icon jstree-themeicon-custom jstree-themeicon-file' }
        },
        'search': {
            'ajax': {
                'url': ajaxRouter + '?ctr=doc&act=search_tree',
                'dataType': 'json',
                'data': function (str) {
                    return { 'search_query': str };
                }
            },
            'show_only_matches': true,
            'search_leaves_only': true
        }
    }).on('move_node.jstree', function (e, data) {
        var inst = data.instance;
        var parentNode = inst.get_node(data.parent);
        var dirs = [];
        var files = [];

        (parentNode.children || []).forEach(function (cid) {
            var child = inst.get_node(cid);
            if (child && child.type === 'folder') {
                dirs.push(cid);
            } else {
                files.push(cid);
            }
        });

        var children = dirs.concat(files);

        // Если DnD смешал типы — выравниваем: сначала папки, потом файлы
        if ((parentNode.children || []).join(',') !== children.join(',')) {
            parentNode.children = children;
            inst.redraw(true);
        }

        $.ajax({
            type: 'POST',
            url: ajaxRouter + '?ctr=doc&act=move_node',
            data: {
                'id': data.node.id,
                'parent': data.parent,
                'children': children
            },
            success: function(response) {
                 highlightNode(data.node.id);
            },
            error: function () {
                $.jstree.reference('#doc_tree').refresh();
            }
        });
    }).on('select_node.jstree', function(e, data) {
        // Prevent action if clicking on menu buttons
        if (data.event && $(data.event.target).closest('.tree-actions, .mobile-menu-btn').length) {
            data.instance.deselect_node(data.node);
            return;
        }

        // Toggle folder on single click
        if (data.node && data.node.type === 'folder') {
            data.instance.toggle_node(data.node);
            data.instance.deselect_node(data.node);
        } 
        // Open file popup on click
        else if (data.node && data.node.type === 'file') {
            var fileId = data.node.id.replace('file_', '');
            openDocCardModal(fileId);
            tree.jstree(true).deselect_node(data.node);
        }
    }).on('ready.jstree', function() {
        // Open level 1 folders by default if no state is saved
        var hasState = localStorage.getItem('doc_tree_state');
        if (!hasState) {
            tree.find('.jstree-node').each(function() {
                var node = tree.jstree(true).get_node(this.id);
                // Open only level 1 folders (direct children of root)
                if (node && node.type === 'folder' && node.parent === '#') {
                    tree.jstree(true).open_node(node);
                }
            });
        }
        addNodeElements();
    }).on('redraw.jstree open_node.jstree create_node.jstree rename_node.jstree', function() {
        addNodeElements();
    }).on('delete_node.jstree', function() {
        // delete_node fires before redraw_node(parent); restore buttons after DOM rebuild
        setTimeout(function() { addNodeElements(); }, 0);
    });

    // ######### ДЕЛЕГИРОВАННЫЕ ОБРАБОТЧИКИ #########

    window.addEventListener('message', function(event) {
        var data = event.data;
        if (!data || data.source !== 'm2profi-doc' || data.type !== 'doc-file-saved') {
            return;
        }
        var fileId = parseInt(data.fileId, 10) || 0;
        var dirId = parseInt(data.dirId, 10) || 0;
        pendingReveal = {
            fileId: fileId ? ('file_' + fileId) : null,
            dirId: dirId ? ('dir_' + dirId) : null
        };
        if (data.deleted) {
            if (!showDeletedCheckbox.is(':checked')) {
                pendingReveal.fileId = null;
            }
        }
        closeDocModal({ force: true });
        if ($.magnificPopup && $.magnificPopup.instance && $.magnificPopup.instance.isOpen) {
            $.magnificPopup.close();
        } else {
            refreshTree();
        }
    });

    // Modal form save → JSON act=save (do not POST to ctrind)
    $(document).on('submit', '#doc-modal-overlay form', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var $form = $(this);
        var $wrap = $form.closest('.doc-edit-form-wrap');
        var fileId = parseInt($wrap.attr('data-file-id'), 10) || 0;
        var dirId = parseInt($wrap.attr('data-dir-id'), 10) || 0;
        var saveUrl = ajaxRouter + '?ctr=doc&act=save';
        if (fileId) {
            saveUrl += '&id=' + encodeURIComponent(fileId);
        }
        if (dirId) {
            saveUrl += '&dir_id=' + encodeURIComponent(dirId);
        }
        var $submit = $form.find('[type=submit]');
        $submit.prop('disabled', true);
        $.ajax({
            url: saveUrl,
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            success: function(response) {
                applySaveResult(response);
            },
            error: function() {
                alert('Ошибка соединения с сервером');
            },
            complete: function() {
                $submit.prop('disabled', false);
            }
        });
        return false;
    });

    // Card → edit inside same modal
    $(document).on('click', '#doc-modal-overlay .doc-modal-edit-link', function(e) {
        e.preventDefault();
        e.stopPropagation();
        var fileId = $(this).attr('data-file-id') || '';
        if (!fileId) {
            var m = ($(this).attr('href') || '').match(/[?&]id=(\d+)/);
            fileId = m ? m[1] : '';
        }
        if (fileId) {
            openDocEditModal({ fileId: fileId });
        }
        return false;
    });

    tree.on('click', '.add-folder-btn', function(e) {
        e.stopPropagation(); e.preventDefault();
        var nodeId = $(this).closest('.jstree-node').attr('id');
        var node = tree.jstree(true).get_node(nodeId);
        var folderName = prompt("Введите название новой папки:", "Новая папка");
        if (folderName) {
            $.ajax({
                type: 'POST',
                url: ajaxRouter + '?ctr=doc&act=create_folder',
                data: { 'parent_id': node.id, 'title': folderName },
                success: function(response) {
                    if (response.status === 'success') {
                        tree.jstree(true).create_node(node, { id: response.id, text: folderName, type: 'folder' }, 'last', function(new_node) {
                            tree.jstree(true).open_node(node);
                            setTimeout(function() { highlightNode(response.id); }, 100);
                        });
                    } else { alert('Ошибка: ' + (response.message || 'Не удалось создать папку')); }
                }, error: function() { alert('Ошибка соединения с сервером'); }
            });
        }
    });

    tree.on('click', '.add-doc-btn', function(e) {
        e.stopPropagation(); e.preventDefault();
        var nodeId = $(this).closest('.jstree-node').attr('id');
        var dirId = nodeId.replace('dir_', '');
        openDocEditModal({ dirId: dirId });
    });

    tree.on('click', '.rename-btn', function(e) {
        e.stopPropagation(); e.preventDefault();
        var nodeId = $(this).closest('.jstree-node').attr('id');
        var node = tree.jstree(true).get_node(nodeId);
        
        // For files: open edit form in page modal
        if (node && node.type === 'file') {
            var fileId = nodeId.replace('file_', '');
            openDocEditModal({ fileId: fileId });
        }
        // For folders: use prompt dialog (existing behavior)
        else if (node && node.type === 'folder') {
            var currentName = tree.jstree(true).get_text(node);
            var newName = prompt("Введите новое название:", currentName);
            if (newName && newName !== currentName) {
                $.ajax({
                    type: 'POST',
                    url: ajaxRouter + '?ctr=doc&act=rename_node',
                    data: { 'id': node.id, 'title': newName },
                    success: function(response) {
                        if (response.status === 'success') {
                            tree.jstree(true).rename_node(node, newName);
                            highlightNode(node.id);
                        } else { alert('Ошибка: ' + (response.message || 'Не удалось переименовать')); }
                    }, error: function() { alert('Ошибка соединения с сервером'); }
                });
            }
        }
    });

    tree.on('click', '.delete-btn', function(e) {
        e.stopPropagation(); e.preventDefault();
        var nodeId = $(this).closest('.jstree-node').attr('id');
        var node = tree.jstree(true).get_node(nodeId);
        if (!node || !confirm("Вы уверены, что хотите удалить этот элемент?")) {
            return;
        }
        if (deletingIds[node.id]) {
            return;
        }
        deletingIds[node.id] = true;
        $.ajax({
            type: 'POST',
            url: ajaxRouter + '?ctr=doc&act=delete_node',
            dataType: 'json',
            data: { 'id': node.id },
            success: function(response) {
                if (!response || response.status !== 'success') {
                    alert('Ошибка: ' + ((response && response.message) || 'Не удалось удалить элемент'));
                    delete deletingIds[node.id];
                    return;
                }
                animateDeleteSuccess(node, showDeletedCheckbox.is(':checked'), function() {
                    delete deletingIds[node.id];
                });
            },
            error: function() {
                alert('Ошибка соединения с сервером');
                delete deletingIds[node.id];
            }
        });
    });

    tree.on('click', '.restore-btn', function(e) {
        e.stopPropagation(); e.preventDefault();
        var nodeId = $(this).closest('.jstree-node').attr('id');
        if (confirm("Восстановить этот элемент?")) {
            $.ajax({
                type: 'POST',
                url: ajaxRouter + '?ctr=doc&act=restore_node',
                data: { 'id': nodeId },
                success: function(response) {
                    if (response.status === 'success') {
                        tree.jstree(true).refresh();
                    } else { alert('Ошибка: ' + (response.message || 'Не удалось восстановить элемент')); }
                }, error: function() { alert('Ошибка соединения с сервером'); }
            });
        }
    });

    // ######### ОБРАБОТЧИКИ ИНТЕРФЕЙСА #########

    $('body').on('click', '.mobile-menu-btn', function(e) {
        e.preventDefault(); e.stopPropagation();
        var actions = $(this).siblings('.tree-actions');
        $('.tree-actions.show-mobile').not(actions).removeClass('show-mobile');
        actions.toggleClass('show-mobile');
    });

    $(document).on('click', function(e) {
        if (!$(e.target).closest('.mobile-menu-btn, .tree-actions').length) {
            $('.tree-actions.show-mobile').removeClass('show-mobile');
        }
    });

    showDeletedCheckbox.on('change', function() { tree.jstree(true).refresh(); });
    dateFrom.on('change', function() { if ($(this).val()) tree.jstree(true).refresh(); });
    dateTo.on('change', function() { if ($(this).val()) tree.jstree(true).refresh(); });

    $(document).on('click', '.date-filter-clear', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        var targetId = $(this).data('target');
        var input = $('#' + targetId);
        
        if (input.length) {
            // Clear the input value
            input.val('');
            
            // Try to clear datepicker if it's initialized
            try {
                if (input.datepicker('instance')) {
                    input.datepicker('setDate', null);
                }
            } catch(err) {
                console.log('Datepicker not initialized or error:', err);
            }
            
            // Reset min/max date constraints
            if (targetId === 'date-from') {
                try {
                    dateTo.datepicker('option', 'minDate', null);
                } catch(err) {}
            } else if (targetId === 'date-to') {
                try {
                    dateFrom.datepicker('option', 'maxDate', null);
                } catch(err) {}
            }
            
            // Refresh the tree
            tree.jstree(true).refresh();
        }
    });

    searchInput.on('keyup', function () {
        if (searchTimeout) clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function () {
            tree.jstree(true).search(searchInput.val());
        }, 300);
    });

    searchClear.on('click', function () {
        tree.jstree(true).clear_search();
        searchInput.val('').focus();
    });
});
</script>
