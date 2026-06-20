$(document).ready(function () {
    let logInterval;

    /**
     * Централизованная функция для вызова API
     * @param {string} action - Действие (get_branches, deploy, etc)
     * @param {object} data - Дополнительные данные
     * @param {string} label - Текст для статус-бара
     * @param {function} successCallback 
     */
    function callApi(action, data, label, successCallback) {
        // Подготовка UI
        toggleUiLock(true);
        updateGlobalStatus(label, 'loading');

        const postData = Object.assign({ action: action, project: currentProject }, data);

        $.post('ajax.php', postData, function (res) {
            if (res.status === 'ok') {
                if (successCallback) successCallback(res);
                updateGlobalStatus('Успешно завершено', 'success');
            } else {
                updateGlobalStatus('Ошибка: ' + res.message, 'error');
                alert('Ошибка: ' + res.message);
            }
        }, 'json')
            .fail(function (xhr, status, err) {
                updateGlobalStatus('Критическая ошибка сервера', 'error');
                console.error("API Fail:", status, err, xhr.responseText);
                alert('Произошла системная ошибка. Проверьте консоль.');
            })
            .always(function () {
                // Разблокировка интерфейса через небольшую паузу для плавности
                setTimeout(() => {
                    toggleUiLock(false);
                }, 500);
            });
    }

    /**
     * Управление блокировкой элементов управления
     */
    function toggleUiLock(isLocked) {
        $('#branch_select, #btn_deploy, .target-radio').prop('disabled', isLocked);
    }

    /**
     * Обновление состояния глобального прогрессбара
     */
    function updateGlobalStatus(text, state) {
        const wrapper = $('#global_status_wrapper');
        const bar = $('#global_progress_bar');
        const statusText = $('#global_status_text');

        statusText.text(text);
        wrapper.stop(true, true).fadeIn(200);

        bar.removeClass('bg-success bg-danger bg-primary progress-bar-animated progress-bar-striped');

        if (state === 'loading') {
            bar.addClass('bg-primary progress-bar-animated progress-bar-striped').css('width', '100%');
        } else if (state === 'success') {
            bar.addClass('bg-success').css('width', '100%');
            // Скрываем через 3 секунды
            setTimeout(() => {
                wrapper.fadeOut(1000);
            }, 3000);
        } else if (state === 'error') {
            bar.addClass('bg-danger').css('width', '100%');
        }
    }

    // 1. Первичная загрузка веток
    callApi('get_branches', {}, 'Загрузка списка веток...', function (res) {
        const select = $('#branch_select');
        select.empty();
        res.data.forEach(function (branch) {
            let text = `${branch.name} - ${branch.hash} - ${branch.date} (${branch.author})`;
            select.append(new Option(text, branch.name));
        });
    });

    // 2. Действие по кнопке Деплой
    $('#btn_deploy').click(function () {
        let branch = $('#branch_select').val();
        let target = $('input[name="deploy_target"]:checked').val();

        if (!branch || !target) {
            alert('Выберите ветку и папку для деплоя!');
            return;
        }

        $('#status_text').text('В процессе...').removeClass('bg-secondary bg-success bg-danger').addClass('bg-primary');
        $('#log_box').text('Инициализация деплоя на сервере...\n');

        // Запуск поллинга логов
        if (logInterval) clearInterval(logInterval);
        logInterval = setInterval(updateLog, 1200);

        callApi('deploy', { branch: branch, target: target }, 'Выполняется деплой...', function (res) {
            clearInterval(logInterval);
            updateLog(); // Финальное обновление лога
            $('#status_text').text('Завершено').removeClass('bg-primary').addClass('bg-success');
        });
    });

    /**
     * Обновление лога из файла lastlog.log
     */
    function updateLog() {
        $.post('ajax.php', { action: 'get_log' }, function (res) {
            if (res.status === 'ok' && res.log) {
                let logBox = $('#log_box');
                logBox.text(res.log);
                logBox.scrollTop(logBox[0].scrollHeight);
            }
        }, 'json');
    }
});
