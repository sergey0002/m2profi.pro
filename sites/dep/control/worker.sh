# Input variables/arrays are supplied by the PHP controller as quoted Bash literals.
# FD 3: final machine-readable response. All ordinary output uses SSH stderr.
exec 3>&1 1>&2
set -Eeuo pipefail
shopt -s inherit_errexit
export LC_ALL=C GIT_TERMINAL_PROMPT=0 GIT_OPTIONAL_LOCKS=0
unset GIT_CURL_VERBOSE GIT_TRACE GIT_TRACE_CURL GIT_TRACE_PACKET GIT_TRACE2 GIT_TRACE2_EVENT GIT_TRACE2_PERF
export GIT_AUTHOR_NAME="$name" GIT_COMMITTER_NAME="$name" GIT_AUTHOR_EMAIL="$email" GIT_COMMITTER_EMAIL="$email"
umask 077
owner=$BASHPID; tmp=''; responded=0
reply(){ printf '%s\0' "$1" "$2" "${3:-}" "${4:-0}" >&3; responded=1; }
fail(){ printf 'Ошибка: %s\n' "$1" >&2; if [[ $BASHPID == "$owner" ]]; then reply error "$1"; fi; exit 1; }
finish(){ local rc=$?; trap - EXIT; if [[ -n $tmp ]]; then rm -rf -- "$tmp"; fi; if (( ! responded )); then reply error "Операция прервана (код $rc). См. терминал; проверьте состояние цели перед повтором."; fi; exit "$rc"; }
trap finish EXIT
cmd(){ printf '$' >&2; printf ' %q' "$@" >&2; printf '\n' >&2; }
g(){
    cmd git "$@"
    if [[ /dev/fd/1 -ef /dev/fd/2 ]];then
        command git -c core.hooksPath=/dev/null -c commit.gpgsign=false -c gc.auto=0 "$@" 9>&-
    else
        # Text output used by the caller is also visible in the terminal. The separate
        # plumb() path handles object bytes and private index data without printing them.
        command git -c core.hooksPath=/dev/null -c commit.gpgsign=false -c gc.auto=0 "$@" 9>&- | tee >(cat >&2)
    fi
}
# Internal object/index plumbing is redirected, not displayed as file contents.
plumb(){ command git -c core.hooksPath=/dev/null -c gc.auto=0 "$@" 9>&-; }
validpath(){ [[ -n $1 && $1 != /* && /$1/ != */../* && /$1/ != */./* && /${1,,}/ != */.git/* ]]; }
# Compare NUL-terminated canonical paths; command substitution would strip trailing newlines.
canonical(){ local resolved; IFS= read -r -d '' resolved < <(realpath "$1" -z -- "$2") && [[ $resolved == "$2" ]]; }
[[ $live == /* && $live != / && -d $live && $(realpath -e -- "$live") == "$live" ]] || fail 'Цель недоступна или содержит ссылку.'
for p in "${other[@]}"; do p=$(realpath -m -- "$p"); [[ "$live/" != "$p/"* && "$p/" != "$live/"* ]] || fail 'Пересечение путей целей.'; done
root=$HOME/.dep-control
[[ "$root/" != "$live/"* && "$live/" != "$root/"* && $(realpath -m -- "$root") == "$root" ]] || fail 'Недопустимый служебный каталог.'
key=$(printf %s "$live" | sha256sum); key=${key:0:24}; home=$root/$key
mkdir -p -- "$home"; [[ $(realpath -e -- "$home") == "$home" ]] || fail 'Служебный каталог — ссылка.'
readonly_run=0
case $action in
    check|preview) readonly_run=1 ;;
    sync|deploy) [[ -n ${confirmed:-} ]] || readonly_run=1 ;;
    bind) ;;
    *) fail 'Неизвестная операция.' ;;
esac
if (( ! readonly_run ));then
    exec 9>>"$home/lock"; flock -n 9 || fail 'Запись не начата: другая операция ещё выполняется. Контроль и предпросмотр доступны.'
fi
[[ ! -e $home/incomplete.json ]] || fail 'Незавершённая выкладка. Нужна ручная сверка.'
[[ ! -e $home/state.json || -f $home/state.v2 ]] || fail 'Найдено состояние предыдущего контроллера. Не выполняйте повторную привязку: требуется перенос базы.'
tmp=$(mktemp -d "$home/run.XXXXXXXX"); sf=$home/state.v2; repo=$home/repo
if ((readonly_run));then
    # Temporary refs, objects and index for this reader. No checkout/fetch against the writer's repo.
    repo=$tmp/readrepo
    if [[ -f $sf ]];then cp -- "$sf" "$tmp/start-state";fi
fi
read_consistent(){
    [[ ! -e $home/incomplete.json ]] && { [[ ! -f $sf && ! -f $tmp/start-state ]] || cmp -s "$sf" "$tmp/start-state"; } || fail 'Состояние изменилось во время проверки. Повторите контроль.'
}
# One line per value; no sourcing/evaluation of stored state.
save_state(){ printf '%s\n' "$url" "$base" "$snap" "$head" "$empty" "$active" "$published" > "$tmp/newstate"; mv -f -- "$tmp/newstate" "$sf"; }
base=''; snap=''; head=''; empty=0; active=0; published=''
if [[ -e $live/.git || -L $live/.git ]]; then
    [[ -d $live/.git && ! -L $live/.git ]] || fail 'Связанная рабочая копия не поддерживается.'
    [[ $(g -C "$live" rev-parse --show-toplevel) == "$live" ]] || fail 'Чужой корень Git.'
    origin=$(g -C "$live" config --get remote.origin.url); [[ ${origin%.git} == "${url%.git}" ]] || fail 'Чужой origin.'
    for f in MERGE_HEAD CHERRY_PICK_HEAD rebase-merge rebase-apply index.lock; do [[ ! -e $live/.git/$f ]] || fail 'В рабочей копии идёт другая операция.'; done
    g -C "$live" diff --cached --quiet || fail 'Есть отдельные изменения в индексе.'
    head=$(g -C "$live" rev-parse HEAD)
elif plumb -C "$live" rev-parse --git-dir >/dev/null 2>&1; then fail 'Цель находится внутри другой рабочей копии.'; fi
if [[ -f $sf ]]; then
    mapfile -t s < "$sf"; [[ ${#s[@]} == 7 && ${s[0]} == "$url" && ${s[3]} == "$head" && ${s[1]} =~ ^[a-f0-9]{40}$ && ${s[2]} =~ ^[a-f0-9]{40}$ && ${s[4]} =~ ^[01]$ && ${s[5]} =~ ^[01]$ ]] || fail 'Конфигурация или HEAD не совпадает с сохранённым состоянием.'
    base=${s[1]};snap=${s[2]};empty=${s[4]};active=${s[5]};published=${s[6]}
fi
finish_check(){
    printf 'База выкладки: %s; выбрана ветка %s @ %s\n' "$compare" "$branch" "$dst"
    read_consistent
    if [[ -n $head ]];then [[ $(plumb -C "$live" rev-parse HEAD) == "$head" ]] || fail 'HEAD изменился во время контроля.';fi
    cat "$tmp/diff"; state=clean; [[ ! -s $tmp/diff ]] || state=changed
    if [[ $state == changed ]];then reply ok "$(cat "$tmp/diff")" "$state" "$compare:$dst";else reply ok "Расхождений нет." "$state" "$compare:$dst";fi;exit
}
[[ -d $repo/.git ]] || g init -q "$repo"; cd -- "$repo"
if ((readonly_run));then
    for objects in "$home/repo/.git/objects" "$live/.git/objects";do
        if [[ -d $objects ]];then printf '%s\n' "$objects" >> "$repo/.git/objects/info/alternates";fi
    done
fi
if [[ $action == check ]];then
    printf 'Этап: поиск базового коммита на хостинге (без загрузки с GitHub)\n'
    compare=${base:-$head}; [[ -n $compare ]] || fail 'Сначала привяжите цель к Git.'
    (( ! empty )) || fail 'Пустая цель: сначала выполните первую выкладку.'
    plumb cat-file -e "$compare^{commit}" || fail 'Базовый коммит отсутствует на хостинге. Контроль не выполнен.'
    [[ -n $branch && $branch != -* && $branch != ftp-snapshots/* ]] && g check-ref-format "refs/heads/$branch" || fail 'Выберите допустимую ветку для сравнения.'
    printf 'Этап: обновление локального кэша выбранной ветки\n'
    cache=$home/control-cache
    [[ $(realpath -m -- "$cache") == "$cache" ]] || fail 'Кэш контроля не должен быть ссылкой.'
    exec 7>>"$home/control-cache.lock"
    flock -w 15 7 || fail 'Кэш веток занят другой проверкой. Повторите контроль.'
    [[ -d $cache/.git ]] || g init -q --template= "$cache"
    for objects in "$home/repo/.git/objects" "$live/.git/objects";do
        if [[ -d $objects ]];then printf '%s\n' "$objects";fi
    done > "$cache/.git/objects/info/alternates"
    fetch_started=$SECONDS
    g -C "$cache" fetch --progress --no-auto-gc --no-tags "$url" "+refs/heads/$branch:refs/remotes/source/$branch"
    dst=$(plumb -C "$cache" rev-parse --verify "refs/remotes/source/$branch^{commit}")
    printf '%s\n' "$cache/.git/objects" >> "$repo/.git/objects/info/alternates"
    exec 7>&-
    printf 'Этап: ветка получена за %d с\n' "$((SECONDS-fetch_started))"
else
    [[ -n $branch && $branch != -* && $branch != ftp-snapshots/* ]] && g check-ref-format "refs/heads/$branch" || fail 'Недопустимая ветка.'
    g fetch --progress --no-tags --prune "$url" '+refs/heads/*:refs/remotes/source/*'
    dst=$(g rev-parse --verify "refs/remotes/source/$branch^{commit}")
    if [[ -n $base_oid && ( $action == preview || $action == bind ) ]]; then [[ $base_oid =~ ^[a-fA-F0-9]{40}$ ]] && g merge-base --is-ancestor "$base_oid" "$dst" || fail 'База не принадлежит ветке.'; dst=$(g rev-parse "${base_oid}^{commit}"); fi
    if [[ -z $base && -n $head ]]; then [[ -n $(g branch -r --contains "$head") ]] || fail 'HEAD не входит в историю remote.'; base=$head;snap=$head;((readonly_run)) || save_state;fi
    if [[ $action == preview || $action == bind ]]; then [[ -z $base ]] || fail 'Уже привязано.'; compare=$dst;else [[ -n $base ]] || fail 'Сначала привяжите цель к Git.'; compare=$snap; fi
fi
# Validate Git trees, then build a raw-byte snapshot using a private index; no clean/smudge filters.
validate_tree(){
    local sha=$1 rec n meta mode
    plumb ls-tree -rz "$sha" > "$tmp/tree"
    while IFS= read -r -d '' rec; do n=${rec#*$'\t'};meta=${rec%%$'\t'*}; mode=${meta%% *};validpath "$n" && [[ $mode == 100644 || $mode == 100755 ]] || fail "Неподдерживаемый путь: $n";done < "$tmp/tree"
}
declare -A livevals=() finalvals=()
if [[ $action == check ]];then printf 'Этап: проверка дерева базового коммита\n';fi
validate_tree "$compare"
# A reverse commit is restricted to paths already managed by Git.
verify_publication=0
declare -A baseline_paths=() selected_paths=() collisions=()
load_check_tree(){
    local revision=$1 entry path meta mode oid
    local -n result_map=$2
    validate_tree "$revision"
    while IFS= read -r -d '' entry;do
        path=${entry#*$'\t'};meta=${entry%%$'\t'*};mode=${meta%% *};oid=${meta##* }
        result_map["$path"]="$mode $oid"
    done < "$tmp/tree"
}
check_report(){
    local n old selected actual quoted
    : > "$tmp/diff"
    while IFS= read -r -d '' n;do
        old=${baseline_paths[$n]-};selected=${selected_paths[$n]-};actual=${livevals[$n]-}
        quoted=$n
        if [[ $n == *[[:cntrl:]]* ]];then printf -v quoted '%q' "$n";fi
        if [[ ${collisions[$n]-} ]];then printf 'C\t%s\n' "$quoted"
        elif [[ -z $old && -n $actual && $actual != "$selected" ]];then printf 'C\t%s\n' "$quoted"
        elif [[ -n $old && $actual != "$old" ]];then
            if [[ -z $actual ]];then printf 'D\t%s\n' "$quoted";else printf 'M\t%s\n' "$quoted";fi
        elif [[ $actual != "$selected" ]];then printf 'G\t%s\n' "$quoted"
        fi
    done < "$tmp/list" > "$tmp/diff"
}
scan(){
    local n p oid mode scanned=0 next_progress=$((SECONDS+5));livevals=(); : > "$tmp/index-info"
    printf 'Контроль файлов: %s\n' "$live"
    if [[ $action == check ]];then
        printf 'Этап: список путей базы и выбранной ветки\n'
        load_check_tree "$compare" baseline_paths
        load_check_tree "$dst" selected_paths
        { for n in "${!baseline_paths[@]}" "${!selected_paths[@]}";do printf '%s\0' "$n";done; } | sort -zu > "$tmp/list"
    else
        GIT_INDEX_FILE="$tmp/listidx" plumb -c core.sparseCheckout=false read-tree "$compare"
        GIT_INDEX_FILE="$tmp/listidx" plumb ls-files --cached -z > "$tmp/tracked-paths"
        if (( ! verify_publication )) && [[ $action == sync || $action == deploy ]] && [[ $base != "$compare" ]];then
            plumb ls-tree -rz --name-only "$base" >> "$tmp/tracked-paths"
        fi
        sort -zu "$tmp/tracked-paths" > "$tmp/list"
    fi
    local total=0 resolved
    local -a hash_flags=(-w)
    if [[ $action == check ]];then hash_flags=();fi
    while IFS= read -r -d '' n;do
        validpath "$n" && [[ $n != */ ]] || fail 'Неподдерживаемое имя или вложенный Git-каталог.'
        printf '%s\0' "$live/$n"
        total=$((total+1))
    done < "$tmp/list" > "$tmp/absolute-paths"
    printf 'Этап: проверка безопасности путей (%d)\n' "$total"
    # GNU realpath preserves argument order; NUL records preserve every legal file name.
    paths_started=$SECONDS
    xargs -0 -r realpath -mz -- < "$tmp/absolute-paths" > "$tmp/resolved-paths" || fail 'Не удалось проверить пути файлов.'
    : > "$tmp/hash-paths"; : > "$tmp/hash-meta"
    next_progress=$((SECONDS+5))
    printf 'Проверено путей: 0 из %d\n' "$total"
    while IFS= read -r -d '' n;do
        scanned=$((scanned+1))
        if ((SECONDS >= next_progress));then printf 'Проверено путей: %d из %d\n' "$scanned" "$total";next_progress=$((SECONDS+5));fi
        p=$live/$n
        IFS= read -r -d '' resolved <&8 || fail 'Не получен результат проверки пути.'
        if [[ $action == check ]];then
            if [[ $resolved != "$p" || -L $p || ( -e $p && ! -f $p ) ]];then collisions["$n"]=1;continue;fi
            if [[ -n ${selected_paths[$n]-} && ! -e $p ]];then
                local parent=${p%/*}
                while [[ $parent != "$live" && ! -d $parent ]];do
                    if [[ -e $parent || -L $parent ]];then collisions["$n"]=1;break;fi
                    parent=${parent%/*}
                done
            fi
        fi
        [[ $resolved == "$p" ]] || fail "Путь содержит ссылку: $n"
        [[ -e $p || -L $p ]] || continue
        [[ -f $p && ! -L $p ]] || fail "Неподдерживаемый файл: $n"
        mode=100644;[[ ! -x $p ]] || mode=100755
        if [[ $p == *$'\n'* ]];then
            oid=$(plumb hash-object "${hash_flags[@]}" --no-filters -- "$p")
            livevals["$n"]="$mode $oid";printf '%s %s\t%s\0' "$mode" "$oid" "$n" >> "$tmp/index-info"
        else
            printf '%s\n' "$p" >> "$tmp/hash-paths"
            printf '%s%s\0' "$mode" "$n" >> "$tmp/hash-meta"
        fi
    done < "$tmp/list" 8< "$tmp/resolved-paths"
    printf 'Этап: проверка путей заняла %d с\n' "$((SECONDS-paths_started))"
    printf 'Хеширование файлов: %s путей проверено\n' "$scanned"
    hash_started=$SECONDS
    plumb hash-object "${hash_flags[@]}" --no-filters --stdin-paths < "$tmp/hash-paths" > "$tmp/hash-oids"
    while IFS= read -r -d '' p;do
        IFS= read -r oid <&8 || fail 'Не получен хеш файла.'
        mode=${p:0:6};n=${p:6}
        livevals["$n"]="$mode $oid";printf '%s %s\t%s\0' "$mode" "$oid" "$n" >> "$tmp/index-info"
    done < "$tmp/hash-meta" 8< "$tmp/hash-oids"
    if [[ $action == check ]];then printf 'Этап: хеширование и чтение результатов заняли %d с\n' "$((SECONDS-hash_started))";return;fi
    GIT_INDEX_FILE="$tmp/idx" plumb read-tree --empty;GIT_INDEX_FILE="$tmp/idx" plumb update-index -z --index-info < "$tmp/index-info";tree=$(GIT_INDEX_FILE="$tmp/idx" plumb write-tree)
}
scan
if [[ $action == check ]];then check_report;finish_check;fi
initial=$tree
plumb -c core.quotePath=false diff-tree --no-renames -r --name-status "$compare^{tree}" "$tree" > "$tmp/diff"
report=$(cat "$tmp/diff"); missing=0;grep -q $'^D\t' "$tmp/diff" && missing=1
actual_empty=0;[[ -n $(find "$live" -mindepth 1 -maxdepth 1 -printf x -quit) ]] || actual_empty=1
if [[ $action == preview || $action == bind ]];then
    check=$(printf '%s\0' "$live" "$url" "$branch" "$dst" "$tree" "$actual_empty" | sha256sum);check=${check%% *}
    if ((actual_empty));then message='Пустая цель: заполнение только при деплое.';missing=0;else message=${report:-'Отличий нет.'};fi
    if [[ $action == preview ]];then read_consistent;reply ok "$message"$'\n'"База: $dst" "$check" "$missing";exit;fi
    [[ $check == "$token" ]] || fail 'Состояние изменилось. Повторите предпросмотр.'
    (( ! missing || accept_missing )) || fail 'Подтвердите первоначальные удаления отдельно.'
    base=$dst;snap=$dst;empty=$actual_empty;save_state;reply ok "Привязано к Git: $dst";exit
fi
if ((empty));then ((actual_empty)) || fail 'В пустой цели появились файлы. Нужна ручная сверка.';[[ $action != sync ]] || fail 'Пустая цель: сначала выполните деплой.';report='Пустая цель: ожидает первой выкладки.';fi
[[ $action == deploy || $action == sync ]] && ((maintenance)) || fail 'Требуется окно обслуживания.'
# Unmanaged paths must never be silently taken over by a deployment.
if [[ $action == deploy ]] && (( ! empty ));then
    load_check_tree "$base" baseline_paths
    load_check_tree "$dst" selected_paths
    for n in "${!selected_paths[@]}";do
        [[ -z ${baseline_paths[$n]-} ]] || continue
        p=$live/$n
        if [[ -e $p || -L $p ]];then
            canonical -m "$p" && [[ -f $p && ! -L $p ]] || fail "Коллизия выкладки: путь $n занят. Файлы сайта не изменены."
            mode=100644;[[ ! -x $p ]] || mode=100755
            live_entry=${livevals[$n]-}
            if [[ -z $live_entry ]];then live_entry="$mode $(plumb hash-object --no-filters -- "$p")";fi
            [[ $live_entry == "${selected_paths[$n]}" ]] || fail "Коллизия выкладки: на сайте уже существует другая версия $n. Файлы сайта не изменены."
        else
            parent=${p%/*}
            while [[ $parent != "$live" && ! -d $parent ]];do
                [[ ! -e $parent && ! -L $parent ]] || fail "Коллизия выкладки: родительский путь для $n занят."
                parent=${parent%/*}
            done
        fi
    done
fi
# Bind approval to both inputs and the exact candidate tree; recompute under the writer lock.
pending=0
if ((active)) && ! plumb merge-base --is-ancestor "$snap" "$dst";then pending=1;fi
if [[ $action == sync && ! -s $tmp/diff ]] && (( ! pending ));then
    [[ -z ${confirmed:-} ]] || fail 'Состояние изменилось. Повторите предпросмотр.'
    read_consistent;reply ok 'Отправлять в GitHub нечего.';exit
fi
check_push_paths(){
    plumb diff-tree --no-renames -r -z --diff-filter=ACMT --name-only "$1" "$2" > "$tmp/push-paths"
    local path leaf
    while IFS= read -r -d '' path;do
        leaf=${path##*/};leaf=${leaf,,}
        case $leaf in
            .env.example|.env.sample|.env.template) ;;
            .env|.env.*|*.pem|*.key|*.p12|*.pfx|id_rsa|id_ed25519|id_ecdsa|id_dsa|*.log|*.sql|*.sql.gz|*.dump)
                printf -v path '%q' "$path"; fail "Push заблокирован: потенциально чувствительный файл $path. Проверьте файл и правила исключения." ;;
        esac
    done < "$tmp/push-paths"
}
if (( ! empty ));then check_push_paths "$snap" "$tree";fi
if ((pending));then
    common=$(plumb merge-base "$dst" "$snap") || fail 'Не найдена общая база для сохранённого снимка.'
    check_push_paths "$common" "$snap"
fi
approval=$(printf '%s\0' "$action" "$user" "$live" "$url" "$branch" "$dst" "$tree" "$base" "$snap" "$head" "$empty" "$active" | sha256sum);approval=${approval%% *}
if [[ -z ${confirmed:-} ]];then
    message=$(printf 'Цель: %s → %s @ %s\nФайлы нового FTP-снимка:\n%s' "$target" "$branch" "$dst" "${report:-Новых правок нет.}")
    if ((pending));then
        pending_diff=$(plumb -c core.quotePath=false diff-tree --no-renames -r --name-status "$common" "$snap")
        message+=$'\n'"Ранее сохранённый снимок для объединения: $snap"$'\n'"${pending_diff:-Нет отличий в файлах.}"
    fi
    message+=$'\n'"Подтверждаете? Перед продолжением остановите FTP, cron и другие записи в каталог."
    read_consistent;reply ok "$message" "$approval";exit
fi
[[ $confirmed == "$approval" ]] || fail 'Файлы, база снимка или ветка изменились. Запустите предпросмотр заново.'
printf 'Правки сайта для ветки %s:\n%s\n' "$branch" "${report:-Правок нет.}"
push_git(){
    if [[ -z ${push_token:-} ]];then g push --progress "$url" "$1"; return; fi
    # Git receives the secret only in the push process environment. No cache/store action.
    # The helper only answers GitHub credential lookups; it never logs its input or output.
    cmd git push --progress "$url" "$1"
    DEP_GITHUB_PUSH_TOKEN="$push_token" \
        command git -c core.hooksPath=/dev/null -c gc.auto=0 -c http.followRedirects=false \
        -c credential.helper= -c 'credential.helper=!f() { [ "$1" = get ] || exit 0; protocol= host=; while IFS= read -r line && [ -n "$line" ]; do case "$line" in protocol=*) protocol=${line#*=};; host=*) host=${line#*=};; esac; done; if [ "$protocol" = https ] && [ "$host" = github.com ]; then printf "username=x-access-token\npassword=%s\n" "$DEP_GITHUB_PUSH_TOKEN"; fi; }; f' \
        push --progress "$url" "$1" 9>&-
}
push_checked(){
    local spec=$1 stage=$2 detail=$3 reason rc
    if push_git "$spec" 2>&1 | tee "$tmp/push.log";then return 0;else rc=$?;fi
    if grep -Eqi 'write access to repository not granted|returned error: 403|permission to .* denied' "$tmp/push.log";then
        if [[ -n ${push_token:-} ]];then
            reason='GitHub отказал в записи (403 или Permission denied). Проверьте, что GITHUB_TOKEN в конфиге проекта панели даёт право записи в этот репозиторий.'
        else
            reason='GitHub отказал в записи (403 или Permission denied). GITHUB_TOKEN не передан; проверьте HTTPS-учётные данные Git на SSH-хосте.'
        fi
    elif grep -Eqi 'GH006|GH013|protected branch|repository rule' "$tmp/push.log";then
        reason='GitHub отклонил обновление из-за защиты ветки или правил репозитория.'
    elif grep -Eqi 'non-fast-forward|fetch first' "$tmp/push.log";then
        reason='Удалённая ветка успела измениться. Проверьте её состояние перед повтором.'
    elif grep -Eqi 'authentication failed|could not read Username|returned error: 401' "$tmp/push.log";then
        if [[ -n ${push_token:-} ]];then
            reason='GitHub не принял GITHUB_TOKEN из конфига панели. Проверьте его срок действия и доступ к репозиторию.'
        else
            reason='Не прошла HTTPS-аутентификация GitHub. Проверьте credential helper на SSH-хосте.'
        fi
    else
        reason='Git push не подтверждён. Причина может быть в сети, доступе или правилах GitHub; см. вывод команды выше.'
    fi
    fail "$stage (код $rc). $reason $detail Файлы сайта не изменены."
}
if (( ! empty )) && [[ -s $tmp/diff ]];then
    newsnap=$(g commit-tree "$tree" -p "$snap" -m "Server edits: $target"); ref=ftp-snapshots/$key/$newsnap
    push_checked "$newsnap:refs/heads/$ref" "Не удалось отправить служебный снимок $ref" "Целевая ветка $branch не обновлялась.";snap=$newsnap;active=1;save_state
fi
ref=ftp-snapshots/$key/$snap
if ((active)) && ! g merge-base --is-ancestor "$snap" "$dst";then
    g checkout --force --detach "$dst"
    if ! g merge --no-edit --no-ff "$snap";then conflicts=$(g diff --name-only --diff-filter=U);g merge --abort || :;fail "Разрешите конфликт merge-коммитом в $branch с $ref ($snap). Сайт не изменён."$'\n'"$conflicts";fi
    dst=$(g rev-parse HEAD);push_checked "$dst:refs/heads/$branch" "Не удалось обновить ветку $branch" "Служебный снимок сохранён: $ref."
fi
if [[ $action == sync ]];then reply ok "Синхронизация завершена: $branch @ $dst";exit;fi
# Prepare the exact objects outside the site before creating the interrupted-publication marker.
validate_tree "$dst";mkdir "$tmp/stage";declare -a changed=() removed=()
while IFS= read -r -d '' rec;do
    n=${rec#*$'\t'};meta=${rec%%$'\t'*};mode=${meta%% *};oid=${meta##* };finalvals["$n"]="$mode $oid"
    [[ ${livevals[$n]-} != "$mode $oid" ]] || continue;p=$live/$n
    if [[ -z ${livevals[$n]+present} && ( -e $p || -L $p ) ]];then
        canonical -m "$p" && [[ -f $p && ! -L $p ]] || fail "Коллизия пути: $n"
        existing_mode=100644;[[ ! -x $p ]] || existing_mode=100755
        [[ "$existing_mode $(plumb hash-object --no-filters -- "$p")" == "$mode $oid" ]] || fail "Коллизия пути: $n"
        continue
    fi
    canonical -m "$p" && [[ ( ! -e $p || -n ${livevals[$n]+present} ) && ! -L $p ]] || fail "Коллизия пути: $n"
    parent=${p%/*};while [[ $parent != "$live" ]];do [[ ! -e $parent || -d $parent ]] || fail "Путь занят: $parent";parent=${parent%/*};done
    changed+=("$n");stagefile=$tmp/stage/$n;mkdir -p -- "${stagefile%/*}";plumb cat-file blob "$oid" > "$stagefile"
done < "$tmp/tree"
for n in "${!livevals[@]}";do [[ -n ${finalvals[$n]+present} ]] || removed+=("$n");done
scan;[[ $tree == "$initial" ]] || fail 'Каталог изменился; публикация отменена.'
if ((empty));then [[ -z $(find "$live" -mindepth 1 -maxdepth 1 -printf x -quit) ]] || fail 'Пустая цель изменилась.';fi
printf '{"desired":"%s","before":"%s"}\n' "$dst" "$snap" > "$tmp/pending";mv -- "$tmp/pending" "$home/incomplete.json"
for n in "${changed[@]}";do
    p=$live/$n;canonical -m "$p" || fail 'Путь изменился.'
    if [[ -z ${livevals[$n]+present} && ( -e $p || -L $p ) ]];then fail "На новом пути появился файл: $n";fi
    printf 'Публикация: %q\n' "$n";(umask 022;mkdir -p -- "${p%/*}");f=$(mktemp "${p%/*}/.dep-tmp-XXXXXXXX");cat -- "$tmp/stage/$n" > "$f"
    perm=644;[[ ! -f $p ]] || perm=$(stat -c %a -- "$p");bits=$((8#$perm & 0666));[[ ${finalvals[$n]} != 100755* ]] || bits=$((bits | 0111));chmod "$(printf '%o' "$bits")" "$f";mv -fT -- "$f" "$p"
done
for n in "${removed[@]}";do p=$live/$n;canonical -m "$p" || fail 'Путь изменился.';printf 'Удаление: %q\n' "$n";rm -- "$p";done
if [[ -n $head && $head != "$dst" ]];then g -C "$live" fetch --no-tags "$repo" "$dst";g -C "$live" update-ref --no-deref HEAD "$dst" "$head";g -C "$live" read-tree "$dst";head=$dst;fi
compare=$dst
verify_publication=1
scan;[[ $tree == "$(plumb rev-parse "$dst^{tree}")" ]] || fail 'Итоговое дерево не совпадает с выбранным коммитом.'
base=$dst;snap=$dst;active=0;empty=0;published=$branch;save_state;rm -- "$home/incomplete.json";reply ok "Выложено и проверено: $branch @ $dst"
