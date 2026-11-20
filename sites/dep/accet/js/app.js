$(document).ready(function () {
    let statusVersion = 0, waitingTarget = null;
    const terminalNames={directory_statuses:'Ветки в каталогах',get_branches:'Список веток',fc_status:'Привязка',fc_branches:'Ветки remote',fc_preview:'Предпросмотр',fc_bind:'Привязка',fc_check:'Контроль',fc_sync:'Синхронизация',deploy:'Деплой'};
    let terminalStarted=false;
    function terminalLine(context,text,time,kind) {
        const box=document.getElementById('log_box');if(!box || typeof text!=='string') return;
        const follow=!terminalStarted || box.scrollHeight-box.scrollTop-box.clientHeight<40;
        if(!terminalStarted){box.textContent='';terminalStarted=true;}
        const type=kind || (/^(?:Ошибка|Нет подтверждения|Push заблокирован)/i.test(text)?'error':
            /^(?:Готово|Предпросмотр готов|Расхождений нет)/i.test(text)?'success':
            /^(?:Контроль файлов|Проверено путей|Хеширование файлов|Этап:)/i.test(text)?'progress':
            /^\$ /.test(text)?'command':context?'heading':'output');
        const row=document.createElement('span');row.className='terminal-'+type;
        row.textContent=(context?'['+(time || new Date().toISOString().slice(11,19))+' UTC] '+context+'  ':'')+text+'\n';
        box.appendChild(row);
        if(follow)box.scrollTop=box.scrollHeight;
    }
    const duration=started=>((performance.now()-started)/1000).toFixed(1)+' с';
    let workTimer;
    function showWork(label, update) {
        const box=$('#terminal_work'), bar=document.getElementById('terminal_work_bar');
        box.prop('hidden',false);
        $('#terminal_work_label').text(label);
        if(update===undefined)bar.removeAttribute('value');else bar.value=update;
    }
    function startWork() {
        clearInterval(workTimer);
        const started=performance.now();
        $('#terminal_work_time').text('0 с');
        showWork('Ожидаем ответ панели…');
        workTimer=setInterval(()=>$('#terminal_work_time').text(Math.floor((performance.now()-started)/1000)+' с'),1000);
    }
    function stopWork() {
        clearInterval(workTimer);
        $('#terminal_work').prop('hidden',true);
    }
    function terminalStatus(text,color) {
        $('#status_text').text(text).removeClass('bg-secondary bg-primary bg-success bg-danger bg-warning text-dark')
            .addClass(color==='warning'?'bg-warning text-dark':'bg-'+color);
    }
    function requestError(xhr,kind) {
        if(kind==='timeout')return 'Таймаут: панель не подтвердила результат.';
        if(kind==='parsererror')return 'Панель вернула некорректный ответ. Проверьте журнал PHP.';
        if(xhr.status)return 'Ошибка запроса к панели (HTTP '+xhr.status+').';
        return 'Связь прервана; результат операции неизвестен.';
    }
    function terminalConfirm({title,text,yes='Да',no='Нет'}) {
        terminalLine(title,text,undefined,'warning');
        const log=document.getElementById('log_box');log.scrollTop=log.scrollHeight;
        const area=$('#terminal_confirm').empty().prop('hidden',false);
        stopWork();
        terminalStatus('Ждёт подтверждения','warning');
        return new Promise(resolve=>{
            const finish=value=>{
                area.empty().prop('hidden',true);
                if(value)terminalLine(title,'Подтверждено.',undefined,'warning');
                resolve(value);
            };
            $('<button type="button" class="btn btn-success btn-sm me-2">').text(yes).appendTo(area).one('click',()=>finish(true));
            $('<button type="button" class="btn btn-outline-light btn-sm">').text(no).appendTo(area).one('click',()=>finish(false)).trigger('focus');
        });
    }
    // All operations use the existing API; trace frames and result share that one response.
    $.ajaxPrefilter('json',function(opts){
        if(opts.url!=='ajax.php')return;
        const params=new URLSearchParams(opts.data || ''), op=params.get('action');
        if(!Object.prototype.hasOwnProperty.call(terminalNames,op))return;
        const label=terminalNames[op]+(op!=='directory_statuses' && params.get('target')?' · '+params.get('target'):'');
        let offset=0, started=false;
        function consume(raw) {
            let end;
            while((end=raw.indexOf('\n',offset))>=0){
                const line=raw.slice(offset,end);let f;
                try{f=JSON.parse(line);}catch(_){break;}
                if(f.event!=='trace')break;
                offset=end+1;
                // Keep status polling out of the console unless its HTTP request fails.
                if(op==='directory_statuses' || op==='fc_status')continue;
                if(!started){terminalLine(label,'Панель отвечает — выполнение начато.',f.time,'progress');started=true;
                    terminalStatus('Выполняется','primary');showWork('Панель выполняет запрос…');}
                const count=/^Проверено путей: (\d+) из (\d+)$/.exec(f.text);
                if(count)showWork(f.text,Number(count[2])?100*Number(count[1])/Number(count[2]):0);
                else if(/^(Этап:|Хеширование файлов:)/.test(f.text))showWork(f.text);
                else if(/^(Сравнение с базой|Завершение SSH)/.test(f.text))showWork('Формируем итог…');
                const noisy=/^(?:remote:\s*)?(?:Counting objects|Compressing objects|Receiving objects|Resolving deltas|Writing objects):\s*\d+%/;
                if(noisy.test(f.text) && !/done\.\s*$/.test(f.text))continue;
                if(op==='fc_check' && /^[ADMTGC]\t/.test(f.text))continue;
                if(!/^(?:Начало операции$|Ошибка:|Готово ·|Предпросмотр готов, push не выполнялся ·)/.test(f.text))terminalLine('',f.text);
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
        clientIssue(op,kind,xhr.status,params.get('target'));
    });
    // Read all visible destinations together, independently of the selected branch or target.
    function refreshDestinations(manual=false) {
        const started=performance.now();
        if(manual)terminalLine('Ветки в каталогах','Запрос отправлен. Ожидаем ответ панели…',undefined,'progress');
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
                const reason=item.reason || item.message || error || (!item.state
                    ? 'Сервер не вернул статус этой цели. Проверьте запись DIRS и права на цель.'
                    : item.state==='unavailable' ? 'Хостинг не передал причину. Проверьте версию control/inspect.sh.'
                    : !Object.prototype.hasOwnProperty.call(labels,item.state) && item.state!=='branch' ? 'Получен неизвестный статус от сервера.' : '');
                $(this).find('.dest-branch').text(text+(reason?' — '+reason:'')).attr('title',
                    item.sha ? (item.source==='publish'?'Подтверждённая выкладка: ':'HEAD каталога: ')+item.sha : text);
                // Recover via the existing read-only refresh, never by replaying an operation.
                if(waitingTarget===target && target===choice().val() && !busy && !error && item.state && item.state!=='busy') {
                    if(item.state==='unavailable') {$('#fc_info').text(reason || text);return;}
                    applyTargetState(item);
                    if(typeof bound==='boolean')terminalLine(target,'Каталог освободился. Можно продолжить.');
                }
            });
        }
        $.ajax({url:'ajax.php',method:'POST',dataType:'json',timeout:20000,
            data:{action:'directory_statuses',project:currentProject,target:$('.target-radio:checked').val() || '',csrf:fcCsrf}})
            .done(r=>{
                const ok=r && r.status==='ok' && Array.isArray(r.targets);
                const error=ok?'':(r && r.message || 'Не удалось прочитать состояние каталогов.');
                paint(ok?r.targets:[],error);
                const warnings=ok && r.targets.some(item=>['unknown','unavailable','incomplete'].includes(item.state));
                if(manual)terminalLine('Ветки в каталогах',(error || (warnings?'Обновлено с замечаниями — см. карточки целей.':'Обновлено.'))+' · '+duration(started),undefined,ok?(warnings?'warning':'success'):'error');
            })
            .fail((xhr,kind)=>{const error=requestError(xhr,kind);paint([],error);
                if(manual)terminalLine('Ветки в каталогах',error+' · '+duration(started),undefined,'error');})
            .always(()=>{if(version===statusVersion) $('#dest_refresh').prop('disabled',false);});
    }
    $('#dest_refresh').on('click',()=>refreshDestinations(true));
    document.addEventListener('visibilitychange',()=>{if(!document.hidden) refreshDestinations();});
    refreshDestinations();
    let nextRefresh=Date.now()+60000;
    setInterval(()=>{if(!document.hidden && (waitingTarget || Date.now()>=nextRefresh) && !$('#dest_refresh').prop('disabled')) {
        nextRefresh=Date.now()+60000;refreshDestinations();
    }},10000);

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
    function paintCheck(state='unknown') {
        const styles={unknown:['btn-outline-secondary','Контроль'],pending:['btn-outline-secondary','Контроль: проверка…'],
            changed:['btn-warning','Контроль: есть различия'],clean:['btn-success','Контроль: без различий'],error:['btn-danger','Контроль: ошибка']};
        $('#fc_check').removeClass('btn-outline-primary btn-outline-secondary btn-success btn-warning btn-danger')
            .addClass(styles[state][0]).text(styles[state][1]);
    }
    async function checkSelection() {
        paintCheck('pending');
        try {
            const result=await request('fc_check');
            if(typeof result.changed!=='boolean')throw Error('Панель не подтвердила результат сравнения.');
            paintCheck(result.changed?'changed':'clean');
            const lines=['Ветка для деплоя: '+$('#branch_select').val()+(result.selected?' @ '+result.selected.slice(0,12):''), 'База выкладки'+(result.baseline?' @ '+result.baseline.slice(0,12):'')];
            if(!result.changed) {
                lines.push('Расхождений нет. Нет ручных правок, изменений из Git или коллизий.');
            } else {
                const groups=new Map([['C',['Коллизии — проверьте перед деплоем',[]]],['G',['Изменения из выбранной ветки',[]]],['M',['Ручные правки',[]]],['D',['Ручные удаления',[]]],
                    ['A',['Новые файлы — только на сайте',[]]],['T',['Изменился тип файла',[]]],['?',['Другие отличия',[]]]]);
                for(const row of result.message.split('\n')) {
                    if(!row)continue;
                    const m=/^([ADMTGC])\t(.*)$/.exec(row);
                    groups.get(m ? m[1] : '?')[1].push(m ? m[2] : row);
                }
                for(const [label,paths] of groups.values()) {
                    if(!paths.length)continue;
                    lines.push('',label+' ('+paths.length+'):');
                    for(const path of paths)lines.push('    '+path);
                }
            }
            const summary=lines.join('\n');
            terminalLine('Итог контроля',summary,undefined,result.changed?'result-warning':'success');
            const log=document.getElementById('log_box');
            log.scrollTop=log.scrollHeight;
            return result;
        } catch(e){paintCheck('error');throw e;}
    }
    function rules() {
        $('#fc_box').prop('hidden', !guarded());
        const hasBranch = !!$('#branch_select').val();
        $('#fc_bind').prop('disabled', busy || bound !== false || !hasBranch)
            .toggleClass('btn-success', bound === true).toggleClass('btn-outline-secondary', bound !== true)
            .text(bound === true ? 'Привязано к Git' : 'Привязать к Git');
        $('#fc_check').prop('disabled', busy || bound !== true || !hasBranch);
        $('#fc_sync').prop('disabled', busy || bound !== true || !hasBranch);
        $('#btn_deploy').prop('disabled', busy || !hasBranch || (guarded() && bound !== true));
    }
    function request(action, fields = {}) {
        if(action!=='fc_status') {
            startWork();
            terminalStatus('Запрос отправлен','primary');
            terminalLine((terminalNames[action] || action)+' · '+(choice().val() || currentProject),'Запрос отправлен. Ожидаем ответ панели…',undefined,'progress');
        }
        return $.ajax({url:'ajax.php',method:'POST',dataType:'json',timeout:195000,data:Object.assign({project:currentProject,target:choice().val(),csrf:fcCsrf,
            action,branch:$('#branch_select').val() || ''},fields)})
            .then(res => { if(res.status !== 'ok') throw Error(res.message); return res; }, (xhr,kind)=>{throw Error(requestError(xhr,kind));});
    }
    function busyGuard(on) {
        busy=on; toggleUiLock(on); rules();
    }
    function feedback(msg, ok, started, cancelled=false) {
        stopWork();
        const state=cancelled?'Отменено':ok?'Готово':'Ошибка';
        const elapsed=started===undefined?'':' · '+duration(started);
        terminalLine(choice().val() || 'Панель',state+(msg?' — '+msg:'')+elapsed,undefined,cancelled?'warning':ok?'success':'error');
        terminalStatus(state+elapsed,cancelled?'secondary':ok?'success':'danger');
        if(!cancelled)updateGlobalStatus(ok?'Завершено':'Операция не подтверждена',ok?'success':'error');
    }
    async function act(which) {
        if(busy || (which!=='deploy' && !guarded())) return;
        const started=performance.now();
        busyGuard(true);
        try {
            let r;
            if(which==='bind') {
                const base=prompt('Базовая ревизия существующего сайта: укажите полный SHA или оставьте пустым для вершины выбранной ветки.', '');
                if(base===null) {feedback(null,false,started,true);return;}
                r=await request('fc_preview',{base_oid:base.trim()});
                if(!await terminalConfirm({title:'Привязка к Git',text:r.message+'\nПодтвердить базовую ревизию? Файлы сайта не изменятся.'})){feedback(null,false,started,true);return;}
                if(r.missing && !await terminalConfirm({title:'Подтверждение удалений',text:'Отсутствующие отслеживаемые файлы будут считаться удалениями. Принять?'})){feedback(null,false,started,true);return;}
                r=await request('fc_bind',{token:r.token,base_oid:base.trim(),accept_missing:r.missing?'1':'0'}); bound=true; $('#fc_info').text(r.message);
            } else if(which==='check') {
                r=await checkSelection();
            } else {
                paintCheck();
                const action=which==='deploy'?'deploy':'fc_'+which;
                const fields=guarded()?{maintenance:'1'}:{};
                r=await request(action,fields);
                if(r.confirmation) {
                    if(!await terminalConfirm({title:which==='deploy'?'Подтверждение деплоя':'Подтверждение синхронизации',text:r.message,yes:'Да, продолжить',no:'Нет, отменить'})){feedback(null,false,started,true);return;}
                    r=await request(action,Object.assign({},fields,{confirmation:r.confirmation}));
                }
            }
            feedback(which==='check'?null:r.message,true,started);
        } catch(e) {feedback(e.message || 'Связь прервана; результат неизвестен.',false,started);}
        finally {busyGuard(false);if(which==='deploy' || which==='bind') refreshDestinations();}
    }
    function applyTargetState(s) {
        waitingTarget=s.state==='busy'?choice().val():null;
        const blocked=s.state==='busy' || s.state==='incomplete';
        bound=blocked?null:s.bound;
        const msg=s.state==='busy'?(s.message || 'Каталог занят другой операцией.')+' Повторная проверка статуса через 10 с.':
            s.state==='incomplete'?'Выкладка не завершена. Нужна ручная сверка перед продолжением.':(s.message || (bound?'Привязано к Git':'Не привязано'));
        $('#fc_info').text(msg);rules();
        if(blocked)terminalLine(choice().val(),msg);
    }
    async function loadTarget() {
        waitingTarget=null; bound=null; paintCheck(); rules();
        const started=performance.now();
        const select=$('#branch_select');select.empty();
        busyGuard(true);
        try {
            // Neither reading binding status nor taking a target lock is a prerequisite for the catalogue.
            const statusTask=guarded() ? (async()=>{
                $('#fc_info').text('Проверка состояния…');
                try {applyTargetState(await request('fc_status'));}
                catch(e){const msg=e.message || 'Нет подтверждения состояния каталога.';$('#fc_info').text(msg);return msg;}
            })() : Promise.resolve();
            const branchesTask=(async()=>{
                const res=await request('get_branches');
                res.data.forEach(b=>select.append(new Option(`${b.name} - ${b.hash} - ${b.date} (${b.author})`,b.name)));
                if(res.data.length)select.prop('selectedIndex',0);
            })();
            try {await branchesTask;} finally {await statusTask;}
            const statusError=await statusTask;
            feedback(statusError || 'Список веток загружен.',!statusError,started);
        } catch(e) {feedback(e.message || 'Не удалось проверить хостинг.',false,started);}
        finally {busyGuard(false);}
    }
    $('.target-radio').on('change',()=>loadTarget()); $('#branch_select').on('change',()=>{paintCheck();rules();});
    $('#fc_bind').click(()=>act('bind')); $('#fc_check').click(()=>act('check')); $('#fc_sync').click(()=>act('sync'));
    loadTarget();
    $('#btn_deploy').click(()=>act('deploy'));
});
