/**
 * Менеджер управления предложениями (избранное) 
 * Версия 2.5 (Единый центр управления: Каталог + Редактор)
 */
var ProposalManager = {
    initialized: false,
    noteTimeouts: {},
    
    init: function() {
        if (this.initialized) return;
        this.initialized = true;
        
        console.log('ProposalManager: Initializing v2.5...');
        this.bindEvents();
        this.updateCounter();
    },
    
    bindEvents: function() {
        var self = this;
        var $doc = $(document);
        
        // --- 1. КАТАЛОГ И ОБЩИЕ ЭЛЕМЕНТЫ ---

        // Кнопка в шапке (Сформировать предложение / Название)
        $doc.off('click', '#create-proposal-btn').on('click', '#create-proposal-btn', function() {
            window.location.href = 'user.php?action=pr_edit';
        });

        // Добавление/Удаление объекта (Toggle)
        $doc.off('click', '.add-to-proposal').on('click', '.add-to-proposal', function(e) {
            e.preventDefault();
            var $btn = $(this);
            var objectType = $btn.data('object-type');
            var objectId = $btn.data('object-id');
            
            if ($btn.hasClass('active')) {
                // Если уже активно - удаляем
                self.removeObject(objectId, function(response) {
                    $btn.removeClass('active').find('i').text('+');
                    $btn.find('.added-message').hide();
                });
            } else {
                // Если не активно - добавляем
                self.addObject(objectType, objectId, function(response) {
                    $btn.addClass('active').find('i').text('✓');
                    $btn.find('.added-message').fadeIn().delay(1000).fadeOut();
                });
            }
        });

        // --- 2. РЕДАКТОР (action=pr_edit) ---

        // Переименование предложения
        $doc.off('click', '#save-proposal-name-btn').on('click', '#save-proposal-name-btn', function() {
            var newName = $('#edit-proposal-name').val().trim();
            if(!newName) return alert('Введите название');
            
            var $btn = $(this);
            $btn.prop('disabled', true).text('...');
            
            $.post('ajax_proposals.php', { action: 'rename_proposal', name: newName }, function(response) {
                $btn.prop('disabled', false).text('Сохранить название');
                if(response.success) {
                    $('#proposal-main-title').text(newName);
                    $btn.css('background', '#047857');
                    setTimeout(function() { $btn.css('background', '#10b981'); }, 1000);
                }
            }, 'json');
        });

        // Автосохранение заметок (input)
        $doc.off('input', '.note-input').on('input', '.note-input', function() {
            var $textarea = $(this);
            var $item = $textarea.closest('.proposal-card');
            var objectId = $item.data('object-id');
            var $status = $item.find('.note-status');
            
            $status.hide();
            clearTimeout(self.noteTimeouts[objectId]);
            
            self.noteTimeouts[objectId] = setTimeout(function() {
                $.post('ajax_proposals.php', {
                    action: 'update_proposal_note',
                    object_id: objectId,
                    note: $textarea.val()
                }, function(response) {
                    if(response.success) { $status.fadeIn(200).delay(2000).fadeOut(500); }
                }, 'json');
            }, 1000);
        });

        // Удаление одного объекта в редакторе
        $doc.off('click', '.remove-object-btn').on('click', '.remove-object-btn', function() {
            var $card = $(this).closest('.proposal-card');
            var objectId = $card.data('object-id');
            
            if(confirm('Удалить этот объект из предложения?')) {
                self.removeObject(objectId, function(response) {
                    $card.fadeOut(400, function() {
                        $(this).remove();
                        $('#objects-count-badge').text($('.proposal-card').length);
                        if($('.proposal-card').length === 0) location.reload();
                    });
                });
            }
        });

        // Полная очистка предложения
        $doc.off('click', '#clear-proposal-btn').on('click', '#clear-proposal-btn', function() {
            if(confirm('Вы уверены, что хотите ПОЛНОСТЬЮ очистить текущее предложение?')) {
                $.post('ajax_proposals.php', {action: 'clear_proposal'}, function(response) {
                    location.reload();
                });
            }
        });

        // Генерация ссылки
        $doc.off('click', '#generate-link-btn').on('click', '#generate-link-btn', function() {
            var $btn = $(this);
            var originalText = $btn.text();
            $btn.prop('disabled', true).text('Генерация...');
            
            $.post('ajax_proposals.php', { action: 'generate_permalink' }, function(response) {
                $btn.prop('disabled', false).text(originalText);
                if(response.success && response.permalink) {
                    $('#permalink-result').val(response.permalink);
                    $('#permalink-container').fadeIn();
                    $('html, body').animate({ scrollTop: $('#permalink-container').offset().top - 100 }, 500);
                } else {
                    alert('Ошибка: ' + (response.message || 'не удалось создать ссылку'));
                }
            }, 'json');
        });

        // Копирование ссылки
        $doc.off('click', '#copy-link-btn').on('click', '#copy-link-btn', function() {
            var $input = $('#permalink-result');
            $input.select();
            document.execCommand('copy');
            var $btn = $(this);
            var oldText = $btn.text();
            $btn.text('Скопировано!');
            setTimeout(function() { $btn.text(oldText); }, 2000);
        });
    },

    // --- МЕТОДЫ API ---

    addObject: function(type, id, callback) {
        var self = this;
        $.post('ajax_proposals.php', { action: 'add_to_proposal', object_type: type, object_id: id }, function(response) {
            if(response.success) {
                self.updateCounterDisplay(response.count);
                if(typeof callback === 'function') callback(response);
            }
        }, 'json');
    },

    removeObject: function(id, callback) {
        var self = this;
        $.post('ajax_proposals.php', { action: 'remove_from_proposal', object_id: id }, function(response) {
            if(response.success) {
                self.updateCounterDisplay(response.count);
                if(typeof callback === 'function') callback(response);
            }
        }, 'json');
    },

    updateCounter: function() {
        var self = this;
        $.post('ajax_proposals.php', {action: 'get_proposal_count'}, function(response) {
            self.updateCounterDisplay(response.count);
        }, 'json');
    },

    updateCounterDisplay: function(count) {
        var $counter = $('#proposal-counter');
        var $block = $('#proposal-block');
        
        if($counter.length) {
            $counter.text(count);
            if(count > 0) $counter.show();
            else $counter.hide();
        }
        
        if($block.length) {
            if(count > 0) $block.show();
            else $block.hide();
        }
    }
};

$(document).ready(function() {
    ProposalManager.init();
});