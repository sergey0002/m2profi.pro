$(document).ready(function () {
    let statusVersion = 0;
    const terminalNames={directory_statuses:'Ветки в каталогах',get_branches:'Список веток',fc_status:'Привязка',fc_branches:'Ветки remote',fc_preview:'Предпросмотр',fc_bind:'Привязка',fc_check:'Контроль',fc_sync:'Синхронизация',deploy:'Деплой'};
    let terminalStarted=false;
    function terminalLine(context,text,time) {
        const box=document.getElementById('log_box');if(!box || typeof text!=='string') return;
        const follow=!terminalStarted || box.scrollHeight-box.scrollTop-box.clientHeight<40;
        if(!terminalStarted){box.textContent='';terminalStarted=true;}
        box.appendChild(document.createTextNode('['+(time || new Date().toISOString().slice(11,19))+' UTC] '+context+'  '+text+'\n'));
        if(follow)box.scrollTop=box.scrollHeight;
    }
    // All operations use the existing API; trace frames and result share that one response.
    $.ajaxPrefilter('json',function(opts){
        if(opts.url!=='ajax.php')return;
        const params=new URLSearchParams(opts.data || ''), op=params.get('action');
        if(!Object.prototype.hasOwnProperty.call(terminalNames,op))return;
        const label=terminalNames[op]+(op!=='directory_statuses' && params.get('target')?' · '+params.get('target'):'');
        let offset=0;
        function consume(raw) {
            let end;
            while((end=raw.indexOf('\n',offset))>=0){
                const line=raw.slice(offset,end);let f;
                try{f=JSON.parse(line);}catch(_){break;}
                if(f.event!=='trace')break;
                offset=end+1;terminalLine(label+' #'+String(f.id).slice(0,6),f.text,f.time);
            }
        }
        opts.data+=(opts.data?'&':'')+'terminal=1';
        opts.xhrFields=Object.assign({},opts.xhrFields,{onprogress(e){consume(e.currentTarget.responseText || '');}});
        opts.converters=Object.assign({},opts.converters,{'text json':function(raw){consume(raw);return JSON.parse(raw.slice(offset));}});
    });
    // Report browser-only failures when the panel is reachable; never send request bodies or responses.
    function clientIssue(operation,kind,http,target) {
        $.ajax({url:'ajax.php',type:'POST',global:false,timeout:5000,data:{action:'client_error',csrf:fcCsrf,
            project:currentProject,target:target || '',operation,kind,http:http || 0}});
    }
    $(document).ajaxError(function(_event,xhr,settings,status){
        if(settings.url!=='ajax.php') return;
        const params=new URLSearchParams(settings.data || ''); const op=params.get('action');
        if(!op || op==='client_error') return;
        const kind=xhr.statusText==='timeout'?'timeout':!xhr.status?'network':xhr.status>=400?'http':'json';
        if(terminalNames[op])terminalLine(terminalNames[op],'Нет подтверждения: '+kind+(xhr.status?' · HTTP '+xhr.status:''));
        clientIssue(op,kind,xhr.status,params.get('target'));
    });
    // Read all visible destinations together, independently of the selected branch or target.
    function refreshDestinations() {
        const version=++statusVersion, rows=$('.dest-row');
        if (!rows.length) return;
        $('#dest_refresh').prop('disabled',true);
        function paint(list, error = '') {
            if(version!==statusVersion) return;
            const values=new Map(list.map(x=>[x.name,x]));
            const labels={none:'нет',unknown:'не определена',busy:'цель занята',incomplete:'выкладка не завершена',unavailable:'не удалось проверить'};
            rows.each(function(){
                const target=$(this).find('input').val(), item=values.get(target) || {};
                if (!item.state && !error) clientIssue('directory_statuses','missing_target',200,target);
                const text=item.state==='branch'?item.branch:(labels[item.state] || labels.unavailable);
                const reason=item.reason || error || (!item.state
                    ? 'Сервер не вернул статус этой цели. Проверьте запись DIRS и права на цель.'
                    : item.state==='unavailable' ? 'Хостинг не передал причину. Проверьте версию control/inspect.sh.'
                    : !Object.prototype.hasOwnProperty.call(labels,item.state) && item.state!=='branch' ? 'Получен неизвестный статус от сервера.' : '');
                $(this).find('.dest-branch').text(text+(reason?' — '+reason:'')).attr('title',
                    item.sha ? (item.source==='publish'?'Подтверждённая выкладка: ':'HEAD каталога: ')+item.sha : text);
            });
        }
        $.ajax({url:'ajax.php',method:'POST',dataType:'json',timeout:20000,
            data:{action:'directory_statuses',project:currentProject,target:$('.target-radio:checked').val() || '',csrf:fcCsrf}})
            .done(r=>{if(r && r.status==='ok' && Array.isArray(r.targets)) paint(r.targets);
                else paint([],r && r.message || 'Проверьте версию ajax.php на сервере панели.');})
            .fail((xhr,kind)=>paint([],kind==='timeout'?'Панель не ответила за 20 секунд.':
                kind==='parsererror'?'Панель вернула не JSON. Проверьте журнал PHP.':'Ошибка запроса к панели'+(xhr.status?' (HTTP '+xhr.status+')':'')+'.')).always(()=>{if(version===statusVersion) $('#dest_refresh').prop('disabled',false);});
    }
    $('#dest_refresh').on('click',refreshDestinations);
    document.addEventListener('visibilitychange',()=>{if(!document.hidden) refreshDestinations();});
    refreshDestinations();
    setInterval(()=>{if(!document.hidden && !$('#dest_refresh').prop('disabled')) refreshDestinations();},60000);

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
        const hasBranch = !!$('#branch_select').val();
        $('#fc_bind').prop('disabled', busy || bound !== false || !hasBranch)
            .toggleClass('btn-success', bound === true).toggleClass('btn-outline-secondary', bound !== true)
            .text(bound === true ? 'Привязано к Git' : 'Привязать к Git');
        $('#fc_check').prop('disabled', busy || bound !== true);
        $('#fc_sync').prop('disabled', busy || bound !== true || !hasBranch);
        $('#btn_deploy').prop('disabled', busy || !hasBranch || (guarded() && bound !== true));
    }
    function request(action, fields = {}) {
        return $.post('ajax.php', Object.assign({project:currentProject,target:choice().val(),csrf:fcCsrf,
            action,branch:$('#branch_select').val() || ''},fields),null,'json')
            .then(res => { if(res.status !== 'ok') throw Error(res.message); return res; });
    }
    function busyGuard(on) {
        busy=on; toggleUiLock(on); rules();
    }
    function feedback(msg, ok) {
        terminalLine(choice().val() || 'Панель',msg); $('#status_text').text(ok?'Готово':'Остановлено')
            .removeClass('bg-secondary bg-success bg-danger bg-primary').addClass(ok?'bg-success':'bg-danger');
        updateGlobalStatus(ok?'Завершено':'Операция не подтверждена',ok?'success':'error');
    }
    async function act(which) {
        if(busy || (which!=='deploy' && !guarded())) return;
        busyGuard(true); $('#status_text').text('В процессе…');
        try {
            let r;
            if(which==='bind') {
                const base=prompt('Базовая ревизия существующего сайта: укажите полный SHA или оставьте пустым для вершины выбранной ветки.', '');
                if(base===null) {$('#status_text').text('Отменено');return;}
                r=await request('fc_preview',{base_oid:base.trim()}); terminalLine('Предпросмотр',r.message);
                if(!confirm(r.message+'\nПодтвердить исходную ревизию и перечисленные отличия? Файлы сайта не изменятся.')) {$('#status_text').text('Отменено');return;}
                if(r.missing && !confirm('Отдельное подтверждение: отсутствующие в каталоге отслеживаемые файлы из списка D будут считаться удалениями при синхронизации. Принять?')) {$('#status_text').text('Отменено');return;}
                r=await request('fc_bind',{token:r.token,base_oid:base.trim(),accept_missing:r.missing?'1':'0'}); bound=true; $('#fc_info').text(r.message);
            } else {
                if(guarded() && which!=='check' && !confirm(choice().val()+' → '+$('#branch_select').val()+'\nОстановлены внешние записи (FTP, редактор, cron)? '+(which==='deploy'?'Подтвердить выкладку в окно обслуживания.':'Сохранить и объединить правки в выбранной ветке.'))) {$('#status_text').text('Отменено');return;}
                r=await request(which==='deploy'?'deploy':'fc_'+which,guarded()?{maintenance:'1'}:{});
            }
            feedback(r.message,true);
        } catch(e) {feedback(e.message || 'Связь прервана; результат неизвестен.',false);}
        finally {busyGuard(false);if(which==='deploy' || which==='bind') refreshDestinations();}
    }
    async function loadTarget() {
        bound=null; rules();
        const select=$('#branch_select');select.empty();
        busyGuard(true);
        try {
            if(guarded()) {
                $('#fc_info').text('Проверка состояния…');
                const s=await request('fc_status'); bound=s.bound; $('#fc_info').text(s.message);
            }
            const res=await request('get_branches');
            res.data.forEach(b=>select.append(new Option(`${b.name} - ${b.hash} - ${b.date} (${b.author})`,b.name)));
            if(res.data.length)select.prop('selectedIndex',0);
        } catch(e) {feedback(e.message || 'Не удалось проверить хостинг.',false);}
        finally {busyGuard(false);}
    }
    $('.target-radio').on('change',loadTarget); $('#branch_select').on('change',rules);
    $('#fc_bind').click(()=>act('bind')); $('#fc_check').click(()=>act('check')); $('#fc_sync').click(()=>act('sync'));
    loadTarget();
    $('#btn_deploy').click(()=>act('deploy'));
});
