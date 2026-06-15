<script>
$(document).ready(function() {
    var tree = $('#doc_tree');
    var searchInput = $('#doc-search-input');
    var searchClear = $('#doc-search-clear');
    var showDeletedCheckbox = $('#show-deleted-checkbox');
    var dateFrom = $('#date-from');
    var dateTo = $('#date-to');
    var dateClear = $('#date-clear');
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
            // Устанавливаем минимальную дату для поля "до"
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
            // Устанавливаем максимальную дату для поля "от"
            dateFrom.datepicker('option', 'maxDate', selectedDate);
            tree.jstree(true).refresh();
        }
    });

    // Функция подсветки узла
    function highlightNode(nodeId) {
        var node = tree.jstree(true).get_node(nodeId);
        if (node) {
            // Скроллим к узлу
            var el = $('#' + nodeId);
            if (el.length) {
                $('html, body').animate({
                    scrollTop: el.offset().top - 100
                }, 500);
                
                // Добавляем класс анимации
                el.addClass('highlight-node');
                setTimeout(function() {
                    el.removeClass('highlight-node');
                }, 2000);
            }
        }
    }

    // Функция для получения URL данных дерева
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

    tree.jstree({
        'core': {
            'data': {
                'url': function() {
                    return getTreeDataUrl();
                },
                'dataType': 'json'
            },
            'check_callback': function (operation, node, node_parent, node_position, more) {
                if (operation === "move_node") {
                    if (node_parent.type === 'folder' || (node.type === 'folder' && node_parent.id === '#')) {
                        return true;
                    }
                    return false;
                }
                return true;
            },
            'themes': {
                'name': 'default',
                'responsive': true,
                'stripes': false
            }
        },
        'plugins': ["dnd", "state", "types", "wholerow", "search"],
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
                 // Подсвечиваем перенесенный узел
                 highlightNode(data.node.id);
            },
            error: function (xhr, status, error) {
                console.error('Ошибка AJAX запроса при перемещении:', error);
                $.jstree.reference('#doc_tree').refresh();
            }
        });
    }).on('select_node.jstree', function(e, data) {
        if (data.node && data.node.type === 'file') {
            var fileId = data.node.id.replace('file_', '');
            $.magnificPopup.open({
                items: {
                    src: '/sahmatka/iframe_router.php?ctr=doc&act=card&id=' + fileId
                },
                type: 'iframe',
                iframe: {
                    markup: '<div class="mfp-iframe-scaler">'+
                            '<div class="mfp-close"></div>'+
                            '<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>'+
                            '</div>'
                }
            });
            tree.jstree(true).deselect_node(data.node);
        }
    });

    // Кастомный индикатор места вставки при drag-and-drop
    var dropIndicator = null;
    
    // Создаем элемент индикатора
    function createDropIndicator() {
        if (!dropIndicator) {
            dropIndicator = $('<div class="custom-drop-indicator"></div>');
            dropIndicator.css({
                'position': 'absolute',
                'height': '4px',
                'background': '#4CAF50',
                'z-index': '9999',
                'pointer-events': 'none',
                'display': 'none',
                'box-shadow': '0 0 8px rgba(76, 175, 80, 0.6)'
            });
            
            // Добавляем круглые маркеры на концах
            var leftMarker = $('<div></div>').css({
                'position': 'absolute',
                'left': '0',
                'top': '-4px',
                'width': '12px',
                'height': '12px',
                'background': '#4CAF50',
                'border-radius': '50%',
                'border': '2px solid #fff',
                'box-shadow': '0 0 4px rgba(0,0,0,0.3)'
            });
            
            var rightMarker = $('<div></div>').css({
                'position': 'absolute',
                'right': '0',
                'top': '-4px',
                'width': '12px',
                'height': '12px',
                'background': '#4CAF50',
                'border-radius': '50%',
                'border': '2px solid #fff',
                'box-shadow': '0 0 4px rgba(0,0,0,0.3)'
            });
            
            dropIndicator.append(leftMarker, rightMarker);
            $('body').append(dropIndicator);
        }
        return dropIndicator;
    }

    // Отслеживаем события drag-and-drop
    tree.on('dnd_start.vakata', function(e, data) {
        createDropIndicator();
    });

    tree.on('dnd_move.vakata', function(e, data) {
        var indicator = createDropIndicator();
        
        // Находим элемент над которым находится курсор
        var target = $(data.event.target).closest('.jstree-node');
        
        if (target.length) {
            var offset = target.offset();
            var height = target.outerHeight();
            var mouseY = data.event.pageY;
            var relativeY = mouseY - offset.top;
            
            // Определяем позицию: сверху или снизу элемента
            var position;
            if (relativeY < height / 2) {
                position = 'before';
            } else {
                position = 'after';
            }
            
            // Позиционируем индикатор
            var indicatorTop = position === 'before' ? offset.top - 2 : offset.top + height - 2;
            var treeOffset = tree.offset();
            
            indicator.css({
                'display': 'block',
                'top': indicatorTop + 'px',
                'left': treeOffset.left + 'px',
                'width': tree.width() + 'px'
            });
        }
    });

    tree.on('dnd_stop.vakata', function(e, data) {
        if (dropIndicator) {
            dropIndicator.hide();
        }
    });

    function addTreeActions() {
        tree.find('.jstree-node').each(function() {
            var node = tree.jstree(true).get_node(this.id);
            var el = $(this);
            var anchor = el.find('.jstree-anchor').first();

            // Добавляем даты для файлов
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

            if (anchor.find('.tree-actions').length === 0) {
                var actionsContainer = $('<span class="tree-actions"></span>');

                if (node.type === 'folder') {
                    var dirId = node.id.replace('dir_', '');
                    
                    if (node.data && node.data.deleted) {
                        // Кнопка "Восстановить" для папок
                        var restoreBtn = $('<a href="#" class="action-btn restore-btn" title="Восстановить">♻️</a>');
                        restoreBtn.on('click', function(e) {
                            e.stopPropagation();
                            e.preventDefault();
                            if (confirm("Восстановить этот элемент?")) {
                                $.ajax({
                                    type: 'POST',
                                    url: '/sahmatka/ajax_router.php?ctr=doc&act=restore_node',
                                    data: { 'id': node.id },
                                    success: function(response) {
                                        if (response.status === 'success') {
                                            tree.jstree(true).refresh();
                                        } else {
                                            alert('Ошибка: ' + (response.message || 'Не удалось восстановить элемент'));
                                        }
                                    },
                                    error: function() {
                                        alert('Ошибка соединения с сервером');
                                    }
                                });
                            }
                        });
                        actionsContainer.append(restoreBtn);
                    } else {
                        // Кнопка "Добавить папку"
                        var addFolderBtn = $('<a href="#" class="action-btn add-folder-btn" title="Создать папку">📂</a>');
                        addFolderBtn.on('click', function(e) {
                            e.stopPropagation();
                            e.preventDefault();
                            var folderName = prompt("Введите название новой папки:", "Новая папка");
                            if (folderName) {
                                $.ajax({
                                    type: 'POST',
                                    url: '/sahmatka/ajax_router.php?ctr=doc&act=create_folder',
                                    data: {
                                        'parent_id': node.id,
                                        'title': folderName
                                    },
                                    success: function(response) {
                                        if (response.status === 'success') {
                                            // Вместо полного рефреша добавляем узел вручную
                                            var newNodeId = response.id;
                                            tree.jstree(true).create_node(node, {
                                                id: newNodeId,
                                                text: folderName,
                                                type: 'folder',
                                                li_attr: { 'class': 'type-folder' }
                                            }, 'last', function(new_node) {
                                                // Открываем родительскую папку
                                                tree.jstree(true).open_node(node);
                                                // Подсвечиваем новый узел
                                                setTimeout(function() {
                                                    highlightNode(newNodeId);
                                                }, 100);
                                            });
                                        } else {
                                            alert('Ошибка: ' + (response.message || 'Не удалось создать папку'));
                                        }
                                    },
                                    error: function() {
                                        alert('Ошибка соединения с сервером');
                                    }
                                });
                            }
                        });
                        actionsContainer.append(addFolderBtn);

                        // Кнопка "Добавить документ"
                        var addDocBtn = $('<a href="#" class="action-btn add-doc-btn" title="Добавить документ">📄</a>');
                        addDocBtn.on('click', function(e) {
                            e.stopPropagation();
                            e.preventDefault();
                            $.magnificPopup.open({
                                items: {
                                    src: '/sahmatka/iframe_router.php?ctr=doc&act=edit&dir_id=' + dirId
                                },
                                type: 'iframe',
                                iframe: {
                                    markup: '<div class="mfp-iframe-scaler">'+
                                            '<div class="mfp-close"></div>'+
                                            '<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>'+
                                            '</div>'
                                },
                                callbacks: {
                                    close: function() {
                                        // При закрытии попапа обновляем дерево
                                        tree.jstree(true).refresh();
                                    }
                                }
                            });
                        });
                        actionsContainer.append(addDocBtn);

                        // Кнопка "Переименовать"
                        var renameBtn = $('<a href="#" class="action-btn rename-btn" title="Переименовать">✏️</a>');
                        renameBtn.on('click', function(e) {
                            e.stopPropagation();
                            e.preventDefault();
                            var currentName = tree.jstree(true).get_text(node);
                            var newName = prompt("Введите новое название:", currentName);
                            if (newName && newName !== currentName) {
                                $.ajax({
                                    type: 'POST',
                                    url: '/sahmatka/ajax_router.php?ctr=doc&act=rename_node',
                                    data: {
                                        'id': node.id,
                                        'title': newName
                                    },
                                    success: function(response) {
                                        if (response.status === 'success') {
                                            tree.jstree(true).rename_node(node, newName);
                                            highlightNode(node.id);
                                        } else {
                                            alert('Ошибка: ' + (response.message || 'Не удалось переименовать'));
                                        }
                                    },
                                    error: function() {
                                        alert('Ошибка соединения с сервером');
                                    }
                                });
                            }
                        });
                        actionsContainer.append(renameBtn);
                    }
                }

                if (node.data && node.data.deleted) {
                     if (node.type !== 'folder') {
                        // Кнопка "Восстановить" для файлов
                        var restoreBtn = $('<a href="#" class="action-btn restore-btn" title="Восстановить">♻️</a>');
                        restoreBtn.on('click', function(e) {
                            e.stopPropagation();
                            e.preventDefault();
                            if (confirm("Восстановить этот элемент?")) {
                                $.ajax({
                                    type: 'POST',
                                    url: '/sahmatka/ajax_router.php?ctr=doc&act=restore_node',
                                    data: { 'id': node.id },
                                    success: function(response) {
                                        if (response.status === 'success') {
                                            tree.jstree(true).refresh();
                                        } else {
                                            alert('Ошибка: ' + (response.message || 'Не удалось восстановить элемент'));
                                        }
                                    },
                                    error: function() {
                                        alert('Ошибка соединения с сервером');
                                    }
                                });
                            }
                        });
                        actionsContainer.append(restoreBtn);
                     }
                } else {
                    // Кнопка "Удалить" (для всех активных)
                    var deleteBtn = $('<a href="#" class="action-btn delete-btn" title="Удалить">🗑</a>');
                    deleteBtn.on('click', function(e) {
                        e.stopPropagation();
                        e.preventDefault();
                        if (confirm("Вы уверены, что хотите удалить этот элемент?")) {
                            // Анимация удаления
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
                                            nodeEl.show(); // Возвращаем если ошибка
                                            alert('Ошибка: ' + (response.message || 'Не удалось удалить элемент'));
                                        }
                                    },
                                    error: function() {
                                        nodeEl.show();
                                        alert('Ошибка соединения с сервером');
                                    }
                                });
                            });
                        }
                    });
                    actionsContainer.append(deleteBtn);
                }

                anchor.append(actionsContainer);
            }
        });
    }

    function syncWholerowHeight() {
        tree.find('.jstree-node').each(function() {
            var el = $(this);
            var anchor = el.children('.jstree-anchor').first();
            var wholerow = el.children('.jstree-wholerow').first();
            
            if (anchor.length && wholerow.length) {
                wholerow.css('height', 'auto');
                var anchorHeight = anchor.outerHeight();
                var minHeight = el.hasClass('type-folder') ? 36 : 30;
                var finalHeight = Math.max(anchorHeight, minHeight);
                wholerow.css('height', finalHeight + 'px');
            }
        });
    }

    tree.on('ready.jstree redraw.jstree open_node.jstree close_node.jstree create_node.jstree rename_node.jstree delete_node.jstree', function() {
        setTimeout(function() {
            addTreeActions();
            syncWholerowHeight();
        }, 200);
    });

    // Обработчик чекбокса "Показывать удаленные элементы"
    showDeletedCheckbox.on('change', function() {
        tree.jstree(true).refresh();
    });

    // Обработчики изменения дат
    dateFrom.on('change', function() {
        if ($(this).val()) {
            tree.jstree(true).refresh();
        }
    });

    dateTo.on('change', function() {
        if ($(this).val()) {
            tree.jstree(true).refresh();
        }
    });

    // Кнопка очистки дат
    dateClear.on('click', function(e) {
        e.preventDefault();
        dateFrom.datepicker('setDate', null).datepicker('option', 'maxDate', null);
        dateTo.datepicker('setDate', null).datepicker('option', 'minDate', null);
        // На всякий случай очищаем value, если datepicker не сработал (хотя setDate null должен работать)
        dateFrom.val('');
        dateTo.val('');
        tree.jstree(true).refresh();
    });

    searchInput.on('keyup', function () {
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }
        searchTimeout = setTimeout(function () {
            var v = searchInput.val();
            tree.jstree(true).search(v);
        }, 300);
    });

    searchClear.on('click', function () {
        tree.jstree(true).clear_search();
        searchInput.val('').focus();
    });
});
</script>
