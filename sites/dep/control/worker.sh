# Input variables/arrays are supplied by the PHP controller as quoted Bash literals.
# FD 3: final machine-readable response. All ordinary output uses SSH stderr.
exec 3>&1 1>&2
set -Eeuo pipefail
shopt -s inherit_errexit
export LC_ALL=C GIT_TERMINAL_PROMPT=0 GIT_OPTIONAL_LOCKS=0
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
    bind|sync|deploy)
        # Serialize only writers sharing the index/state. Never reserve the UI or the directory for reads.
        exec 9>>"$home/lock"; flock -n 9 || fail 'Запись не начата: уже выполняется другая операция записи или предыдущий обработчик. Просмотр и контроль доступны.' ;;
    *) fail 'Неизвестная операция.' ;;
esac
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
    printf 'Сравнение Git → сайт: %s @ %s\n' "$branch" "$dst"
    read_consistent;cat "$tmp/diff"; state=clean; [[ ! -s $tmp/diff ]] || state=changed
    if [[ $state == changed ]];then reply ok "$(cat "$tmp/diff")" "$state";else reply ok "Расхождений нет." "$state";fi;exit
}
# Fast path: when the live index describes the selected remote tip there is no tree to reconstruct.
if [[ $action == check && -n $head ]];then
    [[ -n $branch && $branch != -* && $branch != ftp-snapshots/* ]] && g check-ref-format "refs/heads/$branch" || fail 'Недопустимая ветка.'
    refline=$(g -C "$live" ls-remote --exit-code --heads origin "refs/heads/$branch") || fail 'Не удалось подтвердить вершину выбранной ветки.'
    if [[ $refline == "$head"$'\t'"refs/heads/$branch" ]];then
        # Ordinary status uses the working copy's ignore files. Only use it if these are
        # the repository rules, not FTP overrides or rules in .git/info/exclude.
        excludes=$live/.git/info/exclude
        plumb -C "$live" ls-files --others -z -- .gitignore ':(glob)**/.gitignore' > "$tmp/new-ignore"
        if [[ ! -s $tmp/new-ignore ]] && { [[ ! -s $excludes ]] || ! grep -qvE '^(#|$)' "$excludes"; } &&
           plumb -C "$live" diff --quiet HEAD -- .gitignore ':(glob)**/.gitignore';then
            cmd git -C "$live" -c core.excludesFile=/dev/null -c core.fsmonitor=false -c core.untrackedCache=false -c core.quotePath=false -c status.renames=false status --porcelain=v1 --untracked-files=all
            plumb -C "$live" -c core.excludesFile=/dev/null -c core.fsmonitor=false -c core.untrackedCache=false -c core.quotePath=false -c status.renames=false status --porcelain=v1 --untracked-files=all > "$tmp/status"
            : > "$tmp/diff"
            while IFS= read -r line;do
                if [[ ${line:0:2} == '??' ]];then mark=A;else mark=${line:1:1}; [[ $mark != ' ' ]] || mark=${line:0:1};fi
                printf '%s\t%s\n' "$mark" "${line:3}" >> "$tmp/diff"
            done < "$tmp/status"
            [[ $(plumb -C "$live" rev-parse HEAD) == "$head" ]] || fail 'HEAD изменился во время контроля.'
            dst=$head;finish_check
        fi
    fi
fi
[[ -d $repo/.git ]] || g init -q "$repo"; cd -- "$repo"
[[ -n $branch && $branch != -* && $branch != ftp-snapshots/* ]] && g check-ref-format "refs/heads/$branch" || fail 'Недопустимая ветка.'
if [[ $action == check && ( -n $base || -z $head ) ]];then
    g fetch --progress --no-tags "$url" "+refs/heads/$branch:refs/remotes/source/$branch"
else
    g fetch --progress --no-tags --prune "$url" '+refs/heads/*:refs/remotes/source/*'
fi
{
    dst=$(g rev-parse --verify "refs/remotes/source/$branch^{commit}")
    if [[ -n $base_oid && ( $action == preview || $action == bind ) ]]; then [[ $base_oid =~ ^[a-fA-F0-9]{40}$ ]] && g merge-base --is-ancestor "$base_oid" "$dst" || fail 'База не принадлежит ветке.'; dst=$(g rev-parse "${base_oid}^{commit}"); fi
}
if [[ -z $base && -n $head ]]; then [[ -n $(g branch -r --contains "$head") ]] || fail 'HEAD не входит в историю remote.'; base=$head;snap=$head;((readonly_run)) || save_state;fi
if [[ $action == preview || $action == bind ]]; then [[ -z $base ]] || fail 'Уже привязано.'; compare=$dst;else [[ -n $base ]] || fail 'Сначала привяжите цель к Git.'; compare=$snap; [[ $action != check ]] || compare=$dst; fi
# Validate Git trees, then build a raw-byte snapshot using a private index; no clean/smudge filters.
validate_tree(){
    local sha=$1 rec n meta mode
    plumb ls-tree -rz "$sha" > "$tmp/tree"
    while IFS= read -r -d '' rec; do n=${rec#*$'\t'};meta=${rec%%$'\t'*}; mode=${meta%% *};validpath "$n" && [[ $mode == 100644 || $mode == 100755 ]] || fail "Неподдерживаемый путь: $n";done < "$tmp/tree"
}
declare -A livevals=() finalvals=()
validate_tree "$compare"
# Resolve ignores from the selected Git revision, even on a site without a .git directory.
# A private, empty-template repo isolates check-ignore from info/exclude and host-level rules.
policy=$tmp/policy; policy_tree=''; declare -A policy_tracked=()
prepare_policy(){
    [[ $policy_tree != "$dst" ]] || return 0
    local rec n mode oid f
    rm -rf -- "$policy";plumb init -q --template= "$policy";policy_tracked=()
    plumb ls-tree -rz "$dst" > "$tmp/policy-list"
    while IFS= read -r -d '' rec;do
        n=${rec#*$'\t'};policy_tracked["$n"]=1
        [[ $n == .gitignore || $n == */.gitignore ]] || continue
        mode=${rec%% *};[[ $mode == 100644 || $mode == 100755 ]] || continue
        validpath "$n" || fail 'Недопустимый путь .gitignore.'
        oid=${rec%%$'\t'*};oid=${oid##* };f=$policy/$n
        mkdir -p -- "${f%/*}";plumb cat-file blob "$oid" > "$f"
    done < "$tmp/policy-list"
    policy_tree=$dst
}
scan(){
    local n p oid mode rc=0 scanned=0 next_progress=$((SECONDS+5));local -A ignored=();livevals=(); : > "$tmp/index-info"
    prepare_policy
    printf 'Контроль файлов: %s\n' "$live"
    GIT_INDEX_FILE="$tmp/listidx" plumb -c core.sparseCheckout=false read-tree "$compare"
    (cd -- "$live"; GIT_INDEX_FILE="$tmp/listidx" plumb --git-dir="$repo/.git" --work-tree="$live" ls-files --others -z) > "$tmp/new"
    # Prefix ./ so a leading colon in a filename cannot become Git pathspec magic.
    while IFS= read -r -d '' n;do printf './%s\0' "$n";done < "$tmp/new" > "$tmp/query"
    plumb -C "$policy" -c core.excludesFile=/dev/null check-ignore --no-index --stdin -z < "$tmp/query" > "$tmp/ignored" || rc=$?
    ((rc<=1)) || fail 'Не удалось применить .gitignore из репозитория.'
    while IFS= read -r -d '' n;do ignored["${n#./}"]=1;done < "$tmp/ignored"
    GIT_INDEX_FILE="$tmp/listidx" plumb ls-files --cached -z > "$tmp/list"
    while IFS= read -r -d '' n;do
        if [[ -z ${ignored[$n]+yes} || -n ${policy_tracked[$n]+yes} ]];then printf '%s\0' "$n";fi
    done < "$tmp/new" >> "$tmp/list"
    while IFS= read -r -d '' n;do
        if ((SECONDS >= next_progress));then printf 'Проверено путей: %d\n' "$scanned";next_progress=$((SECONDS+5));fi
        scanned=$((scanned+1))
        validpath "$n" && [[ $n != */ ]] || fail 'Неподдерживаемое имя или вложенный Git-каталог.'
        p=$live/$n
        canonical -m "$p" || fail "Путь содержит ссылку: $n"
        [[ -e $p || -L $p ]] || continue
        [[ -f $p && ! -L $p ]] && canonical -e "$p" || fail "Неподдерживаемый файл: $n"
        mode=100644;[[ ! -x $p ]] || mode=100755;oid=$(plumb hash-object -w --no-filters -- "$p");livevals["$n"]="$mode $oid";printf '%s %s\t%s\0' "$mode" "$oid" "$n" >> "$tmp/index-info"
    done < "$tmp/list"
    GIT_INDEX_FILE="$tmp/idx" plumb read-tree --empty;GIT_INDEX_FILE="$tmp/idx" plumb update-index -z --index-info < "$tmp/index-info";tree=$(GIT_INDEX_FILE="$tmp/idx" plumb write-tree)
}
scan
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
if [[ $action == check ]];then finish_check;fi
[[ $action == deploy || $action == sync ]] && ((maintenance)) || fail 'Требуется окно обслуживания.'
printf 'Правки сайта для ветки %s:\n%s\n' "$branch" "${report:-Правок нет.}"
if (( ! empty )) && [[ -s $tmp/diff ]];then
    newsnap=$(g commit-tree "$tree" -p "$snap" -m "Server edits: $target"); ref=ftp-snapshots/$key/$newsnap
    g push --progress "$url" "$newsnap:refs/heads/$ref";snap=$newsnap;active=1;save_state
fi
ref=ftp-snapshots/$key/$snap
if ((active)) && ! g merge-base --is-ancestor "$snap" "$dst";then
    g checkout --force --detach "$dst"
    if ! g merge --no-edit --no-ff "$snap";then conflicts=$(g diff --name-only --diff-filter=U);g merge --abort || :;fail "Разрешите конфликт merge-коммитом в $branch с $ref ($snap). Сайт не изменён."$'\n'"$conflicts";fi
    dst=$(g rev-parse HEAD);g push --progress "$url" "$dst:refs/heads/$branch" || fail "Push отклонён; снимок сохранён: $ref"
fi
if [[ $action == sync ]];then reply ok "Синхронизация завершена: $branch @ $dst";exit;fi
# Prepare the exact objects outside the site before creating the interrupted-publication marker.
validate_tree "$dst";mkdir "$tmp/stage";declare -a changed=() removed=()
while IFS= read -r -d '' rec;do
    n=${rec#*$'\t'};meta=${rec%%$'\t'*};mode=${meta%% *};oid=${meta##* };finalvals["$n"]="$mode $oid"
    [[ ${livevals[$n]-} != "$mode $oid" ]] || continue;p=$live/$n
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
    printf 'Публикация: %q\n' "$n";(umask 022;mkdir -p -- "${p%/*}");f=$(mktemp "${p%/*}/.dep-tmp-XXXXXXXX");cat -- "$tmp/stage/$n" > "$f"
    perm=644;[[ ! -f $p ]] || perm=$(stat -c %a -- "$p");bits=$((8#$perm & 0666));[[ ${finalvals[$n]} != 100755* ]] || bits=$((bits | 0111));chmod "$(printf '%o' "$bits")" "$f";mv -fT -- "$f" "$p"
done
for n in "${removed[@]}";do p=$live/$n;canonical -m "$p" || fail 'Путь изменился.';printf 'Удаление: %q\n' "$n";rm -- "$p";done
if [[ -n $head && $head != "$dst" ]];then g -C "$live" fetch --no-tags "$repo" "$dst";g -C "$live" update-ref --no-deref HEAD "$dst" "$head";g -C "$live" read-tree "$dst";head=$dst;fi
compare=$dst
scan;[[ $tree == "$(plumb rev-parse "$dst^{tree}")" ]] || fail 'Итоговое дерево не совпадает с выбранным коммитом.'
base=$dst;snap=$dst;active=0;empty=0;published=$branch;save_state;rm -- "$home/incomplete.json";reply ok "Выложено и проверено: $branch @ $dst"
