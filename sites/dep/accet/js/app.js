$(document).ready(function () {
    let logInterval, statusVersion = 0;
    // Read all visible destinations together, independently of the selected branch or target.
    function refreshDestinations() {
        const version=++statusVersion, rows=$('.dest-row');
        if (!rows.length) return;
        $('#dest_refresh').prop('disabled',true);
        function paint(list) {
            if(version!==statusVersion) return;
            const values=new Map(list.map(x=>[x.name,x]));
            const labels={none:'нет',unknown:'не определена',busy:'цель занята',incomplete:'выкладка не завершена',unavailable:'не удалось проверить'};
            rows.each(function(){
                const item=values.get($(this).find('input').val()) || {};
                const text=item.state==='branch'?item.branch:(labels[item.state] || labels.unavailable);
                $(this).find('.dest-branch').text(text).attr('title',
                    item.sha ? (item.source==='publish'?'Подтверждённая выкладка: ':'HEAD каталога: ')+item.sha : text);
            });
        }
        $.post('ajax.php',{action:'directory_statuses',project:currentProject,csrf:fcCsrf},null,'json')
            .done(r=>paint(r.status==='ok' && Array.isArray(r.targets)?r.targets:[]))
            .fail(()=>paint([])).always(()=>{if(version===statusVersion) $('#dest_refresh').prop('disabled',false);});
    }
    $('#dest_refresh').on('click',refreshDestinations);
    document.addEventListener('visibilitychange',()=>{if(!document.hidden) refreshDestinations();});
    refreshDestinations();
    setInterval(()=>{if(!document.hidden && !$('#dest_refresh').prop('disabled')) refreshDestinations();},60000);

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
                if(action==='deploy') refreshDestinations();
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

    const choice = () => $('.target-radio:checked');
    const guarded = () => choice().attr('data-guard') === '1';
    let bound = null, busy = false;
    function rules() {
        $('#fc_box').prop('hidden', !guarded());
        if (!guarded()) return;
        const hasBranch = !!$('#branch_select').val();
        $('#fc_bind').prop('disabled', busy || bound !== false || !hasBranch)
            .toggleClass('btn-success', bound === true).toggleClass('btn-outline-secondary', bound !== true)
            .text(bound === true ? 'Привязано к Git' : 'Привязать к Git');
        $('#fc_check').prop('disabled', busy || bound !== true);
        $('#fc_sync,#btn_deploy').prop('disabled', busy || bound !== true || !hasBranch);
    }
    function fc(action, fields = {}) {
        return $.post('ajax.php', Object.assign({project:currentProject,target:choice().val(),csrf:fcCsrf,
            action:action === 'deploy' ? 'deploy' : 'fc_'+action,branch:$('#branch_select').val() || ''},fields),null,'json')
            .then(res => { if(res.status !== 'ok') throw Error(res.message); return res; });
    }
    function busyGuard(on) {
        busy=on; toggleUiLock(on); rules();
    }
    function feedback(msg, ok) {
        $('#log_box').text(msg); $('#status_text').text(ok?'Готово':'Остановлено')
            .removeClass('bg-secondary bg-success bg-danger bg-primary').addClass(ok?'bg-success':'bg-danger');
        updateGlobalStatus(ok?'Завершено':'Операция не подтверждена',ok?'success':'error');
    }
    async function act(which) {
        if(busy || !guarded()) return;
        busyGuard(true); $('#status_text').text('В процессе…');
        try {
            let r;
            if(which==='bind') {
                const base=prompt('Базовая ревизия существующего сайта: укажите полный SHA или оставьте пустым для вершины выбранной ветки.', '');
                if(base===null) {$('#status_text').text('Отменено');return;}
                r=await fc('preview',{base_oid:base.trim()}); $('#log_box').text(r.message);
                if(!confirm(r.message+'\nПодтвердить исходную ревизию и перечисленные отличия? Файлы сайта не изменятся.')) {$('#status_text').text('Отменено');return;}
                if(r.missing && !confirm('Отдельное подтверждение: отсутствующие в каталоге отслеживаемые файлы из списка D будут считаться удалениями при синхронизации. Принять?')) {$('#status_text').text('Отменено');return;}
                r=await fc('bind',{token:r.token,base_oid:base.trim(),accept_missing:r.missing?'1':'0'}); bound=true; $('#fc_info').text(r.message);
            } else {
                if(which!=='check' && !confirm(choice().val()+' → '+$('#branch_select').val()+'\nОстановлены внешние записи (FTP, редактор, cron)? '+(which==='deploy'?'Подтвердить выкладку в окно обслуживания.':'Сохранить и объединить правки в выбранной ветке.'))) {$('#status_text').text('Отменено');return;}
                r=await fc(which,{maintenance:'1'});
            }
            feedback(r.message,true);
        } catch(e) {feedback(e.message || 'Связь прервана; результат неизвестен.',false);}
        finally {busyGuard(false);if(which==='deploy' || which==='bind') refreshDestinations();}
    }
    async function loadTarget() {
        bound=null; rules(); $('#log_box').text('');
        const select=$('#branch_select');select.empty();
        if(guarded()) {
            busyGuard(true); $('#fc_info').text('Проверка состояния…');
            try {const s=await fc('status'); bound=s.bound; $('#fc_info').text(s.message);
                const b=await fc('branches'); b.branches.forEach(n=>select.append(new Option(n,n)));
                if(b.branches.length) select.prop('selectedIndex',0);
            } catch(e) {feedback(e.message || 'Не удалось проверить хостинг.',false);$('#fc_info').text('Проверка не завершена.');}
            finally {busyGuard(false);}
        } else callApi('get_branches', {}, 'Загрузка списка веток...', function (res) {
            res.data.forEach(b=>select.append(new Option(`${b.name} - ${b.hash} - ${b.date} (${b.author})`, b.name)));
        });
    }
    let wasGuarded=guarded(); $('.target-radio').on('change',()=>{const previous=wasGuarded;wasGuarded=guarded();if(previous || wasGuarded) loadTarget();}); $('#branch_select').on('change',rules);
    $('#fc_bind').click(()=>act('bind')); $('#fc_check').click(()=>act('check')); $('#fc_sync').click(()=>act('sync'));
    loadTarget();
    // 2. Действие по кнопке Деплой
    $('#btn_deploy').click(function () {
        if(guarded()) {act('deploy'); return;}
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
