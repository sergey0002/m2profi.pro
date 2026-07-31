<script>
$(document).ready(function() {
    var tree = $('#doc_tree');
    var searchInput = $('#doc-search-input');
    var searchClear = $('#doc-search-clear');
    var showDeletedCheckbox = $('#show-deleted-checkbox');
    var dateFrom = $('#date-from');
    var dateTo = $('#date-to');
    var searchTimeout = false;

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

    function highlightNode(nodeId) {
        var node = tree.jstree(true).get_node(nodeId);
        if (node) {
            var el = $('#' + nodeId);
            if (el.length) {
                $('html, body').animate({
                    scrollTop: el.offset().top - 100
                }, 500);
                el.addClass('highlight-node');
                setTimeout(function() {
                    el.removeClass('highlight-node');
                }, 2000);
            }
        }
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
        
        return '/sahmatka/ajax_router.php?' + params;
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
            'check_callback': true,
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
                'url': '/sahmatka/ajax_router.php?ctr=doc&act=search_tree',
                'dataType': 'json',
                'data': function (str) {
                    return { 'search_query': str };
                }
            },
            'show_only_matches': true,
            'search_leaves_only': true
        }
    }).on('move_node.jstree', function (e, data) {
        $.ajax({
            type: 'POST',
            url: '/sahmatka/ajax_router.php?ctr=doc&act=move_node',
            data: {
                'id': data.node.id,
                'parent': data.parent,
                'position': data.position
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
            $.magnificPopup.open({
                items: {
                    src: '/sahmatka/iframe_router.php?ctr=doc&act=card&id=' + fileId
                },
                type: 'iframe'
            });
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
    }).on('redraw.jstree open_node.jstree', function() {
        addNodeElements();
    });

    // ######### ДЕЛЕГИРОВАННЫЕ ОБРАБОТЧИКИ #########

    tree.on('click', '.add-folder-btn', function(e) {
        e.stopPropagation(); e.preventDefault();
        var nodeId = $(this).closest('.jstree-node').attr('id');
        var node = tree.jstree(true).get_node(nodeId);
        var folderName = prompt("Введите название новой папки:", "Новая папка");
        if (folderName) {
            $.ajax({
                type: 'POST',
                url: '/sahmatka/ajax_router.php?ctr=doc&act=create_folder',
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
        $.magnificPopup.open({
            items: { src: '/sahmatka/iframe_router.php?ctr=doc&act=edit&dir_id=' + dirId },
            type: 'iframe',
            callbacks: { close: function() { tree.jstree(true).refresh(); } }
        });
    });

    tree.on('click', '.rename-btn', function(e) {
        e.stopPropagation(); e.preventDefault();
        var nodeId = $(this).closest('.jstree-node').attr('id');
        var node = tree.jstree(true).get_node(nodeId);
        
        // For files: open edit form in Magnific Popup
        if (node && node.type === 'file') {
            var fileId = nodeId.replace('file_', '');
            $.magnificPopup.open({
                items: { 
                    src: '/sahmatka/iframe_router.php?ctr=doc&act=edit&id=' + fileId 
                },
                type: 'iframe',
                callbacks: { 
                    close: function() { 
                        tree.jstree(true).refresh(); 
                    } 
                }
            });
        }
        // For folders: use prompt dialog (existing behavior)
        else if (node && node.type === 'folder') {
            var currentName = tree.jstree(true).get_text(node);
            var newName = prompt("Введите новое название:", currentName);
            if (newName && newName !== currentName) {
                $.ajax({
                    type: 'POST',
                    url: '/sahmatka/ajax_router.php?ctr=doc&act=rename_node',
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
        if (confirm("Вы уверены, что хотите удалить этот элемент?")) {
            var nodeEl = $('#' + node.id);
            nodeEl.fadeOut(500, function() {
                $.ajax({
                    type: 'POST',
                    url: '/sahmatka/ajax_router.php?ctr=doc&act=delete_node',
                    data: { 'id': node.id },
                    success: function(response) {
                        if (response.status === 'success') {
                            tree.jstree(true).delete_node(node);
                        } else {
                            nodeEl.show();
                            alert('Ошибка: ' + (response.message || 'Не удалось удалить элемент'));
                        }
                    }, error: function() { nodeEl.show(); alert('Ошибка соединения с сервером'); }
                });
            });
        }
    });

    tree.on('click', '.restore-btn', function(e) {
        e.stopPropagation(); e.preventDefault();
        var nodeId = $(this).closest('.jstree-node').attr('id');
        if (confirm("Восстановить этот элемент?")) {
            $.ajax({
                type: 'POST',
                url: '/sahmatka/ajax_router.php?ctr=doc&act=restore_node',
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
