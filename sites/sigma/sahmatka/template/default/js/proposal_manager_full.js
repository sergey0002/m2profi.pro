/**
 * Полный менеджер управления предложениями (избранное)
 */

// Глобальный объект для управления предложениями
var ProposalManager = {
    proposalSessionKey: 'selected_objects',
    
    init: function() {
        this.loadProposal();
        this.bindEvents();
        this.updateUI();
    },
    
    bindEvents: function() {
        // Открытие модального окна
        $(document).on('click', '#create-proposal-btn', function() {
            var proposalData = ProposalManager.getProposalData();
            if(proposalData && proposalData.objects && proposalData.objects.length > 0) {
                // Если есть добавленные объекты, переходим на страницу редактирования
                window.location.href = '/sahmatka/proposal_edit.php';
            } else {
                ProposalManager.openModal();
            }
        });
        
        // Сохранение названия предложения
        $(document).on('click', '#save-proposal-btn', function() {
            ProposalManager.saveProposal();
        });
        
        // Очистка предложения
        $(document).on('click', '#clear-proposal-btn', function() {
            ProposalManager.clearProposal();
        });
        
        // Добавление объекта в предложение
        $(document).on('click', '.add-to-proposal', function() {
            var objectType = $(this).data('object-type');
            var objectId = $(this).data('object-id');
            ProposalManager.addObject(objectType, objectId, $(this));
        });
        
        // Удаление объекта из предложения
        $(document).on('click', '.remove-from-proposal', function() {
            var objectType = $(this).data('object-type');
            var objectId = $(this).data('object-id');
            ProposalManager.removeObject(objectType, objectId);
        });
    },
    
    openModal: function() {
        $.magnificPopup.open({
            items: {
                src: '#proposal-modal',
                type: 'inline'
            },
            modal: true
        });
        
        // Загрузка текущего названия, если предложение уже создано
        var proposalData = this.getProposalData();
        if(proposalData && proposalData.name) {
            $('#proposal-name').val(proposalData.name);
            $('#clear-proposal-btn').show();
            $('#view-proposal-link').attr('href', '/sahmatka/proposal_edit.php').show();
        } else {
            $('#proposal-name').val('');
            $('#clear-proposal-btn').hide();
            $('#view-proposal-link').hide();
        }
    },
    
    saveProposal: function() {
        var proposalName = $('#proposal-name').val().trim();
        if(!proposalName) {
            alert('Введите название предложения');
            return;
        }
        
        var proposalData = this.getProposalData();
        if(!proposalData) {
            proposalData = {};
        }
        
        proposalData.name = proposalName;
        proposalData.hash = this.generateHash(proposalName);
        proposalData.timestamp = Date.now();
        
        this.setProposalData(proposalData);
        this.updateUI();
        $.magnificPopup.close();
    },
    
    addObject: function(objectType, objectId, element) {
        // Отправляем запрос на сервер для добавления объекта
        $.ajax({
            url: 'ajax_actions.php',
            method: 'POST',
            data: {
                action: 'add_to_proposal',
                object_type: objectType,
                object_id: objectId
            },
            success: function(response) {
                if(response.success) {
                    // Обновляем интерфейс
                    ProposalManager.animateAdd(element);
                    ProposalManager.updateCounterDisplay(response.count);
                    
                    // Меняем иконку на активную
                    element.removeClass('add-to-proposal').addClass('add-to-proposal active');
                    element.find('i').text('✓');
                }
            },
            error: function() {
                console.error('Ошибка при добавлении объекта в предложение');
            }
        });
    },
    
    removeObject: function(objectType, objectId) {
        $.ajax({
            url: 'ajax_actions.php',
            method: 'POST',
            data: {
                action: 'remove_from_proposal',
                object_type: objectType,
                object_id: objectId
            },
            success: function(response) {
                if(response.success) {
                    ProposalManager.updateCounter();
                }
            },
            error: function() {
                console.error('Ошибка при удалении объекта из предложения');
            }
        });
    },
    
    updateObjectNote: function(objectType, objectId, note) {
        $.ajax({
            url: 'ajax_actions.php',
            method: 'POST',
            data: {
                action: 'update_proposal_note',
                object_type: objectType,
                object_id: objectId,
                note: note
            },
            success: function(response) {
                if(!response.success) {
                    console.error('Ошибка при обновлении примечания');
                }
            },
            error: function() {
                console.error('Ошибка при обновлении примечания');
            }
        });
    },
    
    animateAdd: function(element) {
        var $element = $(element);
        $element.find('.added-message').fadeIn(200).delay(1000).fadeOut(200);
    },
    
    updateCounter: function() {
        $.ajax({
            url: 'ajax_actions.php',
            method: 'POST',
            data: {
                action: 'get_proposal_count'
            },
            success: function(response) {
                if(response.count !== undefined) {
                    ProposalManager.updateCounterDisplay(response.count);
                }
            },
            error: function() {
                console.error('Ошибка при получении количества объектов в предложении');
            }
        });
    },
    
    updateCounterDisplay: function(count) {
        var $counter = $('#proposal-counter');
        if(count > 0) {
            $counter.text(count).show();
        } else {
            $counter.hide();
        }
    },
    
    getProposalData: function() {
        var data = sessionStorage.getItem(this.proposalSessionKey);
        return data ? JSON.parse(data) : null;
    },
    
    setProposalData: function(data) {
        sessionStorage.setItem(this.proposalSessionKey, JSON.stringify(data));
    },
    
    loadProposal: function() {
        // Загрузка данных из сессии при инициализации
        this.updateCounter(); // Обновляем счетчик при загрузке
    },
    
    updateUI: function() {
        var proposalData = this.getProposalData();
        if(proposalData && proposalData.name) {
            $('#create-proposal-btn').text(proposalData.name);
        } else {
            $('#create-proposal-btn').text('Сформировать предложение');
        }
        this.updateCounter();
    },
    
    generateHash: function(str) {
        var hash = 0;
        for (var i = 0; i < str.length; i++) {
            var char = str.charCodeAt(i);
            hash = ((hash<<5)-hash)+char;
            hash = hash & hash; // Convert to 32bit integer
        }
        return hash.toString();
    },
    
    // Функция для получения постоянной ссылки
    getPermalink: function() {
        var proposalData = this.getProposalData();
        if(proposalData && proposalData.hash) {
            // Сохраняем предложение в JSON файл
            this.saveProposalToFile(proposalData);
            return window.location.origin + '/personaloffer/' + proposalData.hash + '/';
        }
        return null;
    },
    
    // Сохранение предложения в JSON файл
    saveProposalToFile: function(proposalData) {
        // Отправляем данные на сервер для сохранения
        $.ajax({
            url: 'ajax_actions.php',
            method: 'POST',
            data: {
                action: 'save_permanent_proposal',
                proposal_data: JSON.stringify(proposalData)
            },
            success: function(response) {
                console.log('Постоянное предложение сохранено');
            },
            error: function() {
                console.error('Ошибка при сохранении постоянного предложения');
            }
        });
    }
};

// Инициализация при загрузке документа
$(document).ready(function() {
    // Проверяем, существует ли ProposalManager, и инициализируем его
    if(typeof ProposalManager !== 'undefined') {
        ProposalManager.init();
    }
    
    // Добавляем CSS стили динамически, если они не подключены
    if(!$('link[href*="proposal_elements.css"]').length) {
        $('head').append('<link rel="stylesheet" type="text/css" href="/sahmatka/template/default/css/proposal_elements.css">');
    }
});