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
        command git -c core.hooksPath=/dev/null -c commit.gpgsign=false -c gc.auto=0 "$@"
    else
        # Text output used by the caller is also visible in the terminal. The separate
        # plumb() path handles object bytes and private index data without printing them.
        command git -c core.hooksPath=/dev/null -c commit.gpgsign=false -c gc.auto=0 "$@" | tee >(cat >&2)
    fi
}
# Internal object/index plumbing is redirected, not displayed as file contents.
plumb(){ command git -c core.hooksPath=/dev/null -c gc.auto=0 "$@"; }
validpath(){ [[ -n $1 && $1 != /* && $1 != *[[:cntrl:]]* && /$1/ != */../* && /$1/ != */./* && /${1,,}/ != */.git/* ]]; }
protected(){ case "/${1,,}/" in */.env/*|*/.env.*/*|*/.ssh/*|*/.htpasswd/*|*/.netrc/*|*/.npmrc/*|*/uploads/*|*/cache/*|*/backups/*|*/logs/*|*/vendor/*|*/node_modules/*|*.key/*|*.pem/*|*.p12/*|*.bak/*|*.sql/*|*.zip/*|*.log/*) return 0;; *) return 1;; esac; }
matchlist(){ local pattern value=$1; shift; for pattern do [[ -n $pattern && $value == $pattern ]] && return 0; done; return 1; }
[[ $live == /* && $live != / && -d $live && $(realpath -e -- "$live") == "$live" ]] || fail 'Цель недоступна или содержит ссылку.'
for p in "${other[@]}"; do p=$(realpath -m -- "$p"); [[ "$live/" != "$p/"* && "$p/" != "$live/"* ]] || fail 'Пересечение путей целей.'; done
root=$HOME/.dep-control
[[ "$root/" != "$live/"* && "$live/" != "$root/"* && $(realpath -m -- "$root") == "$root" ]] || fail 'Недопустимый служебный каталог.'
key=$(printf %s "$live" | sha256sum); key=${key:0:24}; home=$root/$key
mkdir -p -- "$home"; [[ $(realpath -e -- "$home") == "$home" ]] || fail 'Служебный каталог — ссылка.'
exec 9>>"$home/lock"; flock -n 9 || fail 'Цель занята.'
[[ ! -e $home/incomplete.json ]] || fail 'Незавершённая выкладка. Нужна ручная сверка.'
[[ ! -e $home/state.json || -f $home/state.v2 ]] || fail 'Найдено состояние предыдущего контроллера. Не выполняйте повторную привязку: требуется перенос базы.'
tmp=$(mktemp -d "$home/run.XXXXXXXX"); sf=$home/state.v2; repo=$home/repo
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
[[ -d $repo/.git ]] || g init -q "$repo"; cd -- "$repo"
if [[ $action != check || -z $base ]]; then g fetch --no-tags --prune "$url" '+refs/heads/*:refs/remotes/source/*'; fi
if [[ $action != check ]]; then
    [[ -n $branch && $branch != -* && $branch != ftp-snapshots/* ]] && g check-ref-format "refs/heads/$branch" || fail 'Недопустимая ветка.'
    dst=$(g rev-parse --verify "refs/remotes/source/$branch^{commit}")
    if [[ -n $base_oid && ( $action == preview || $action == bind ) ]]; then [[ $base_oid =~ ^[a-fA-F0-9]{40}$ ]] && g merge-base --is-ancestor "$base_oid" "$dst" || fail 'База не принадлежит ветке.'; dst=$(g rev-parse "${base_oid}^{commit}"); fi
fi
if [[ -z $base && -n $head ]]; then [[ -n $(g branch -r --contains "$head") ]] || fail 'HEAD не входит в историю remote.'; base=$head;snap=$head;save_state;fi
if [[ $action == preview || $action == bind ]]; then [[ -z $base ]] || fail 'Уже привязано.'; compare=$dst;else [[ -n $base ]] || fail 'Сначала привяжите цель к Git.'; compare=$snap; fi
# Validate Git trees, then build a raw-byte snapshot using a private index; no clean/smudge filters.
validate_tree(){
    local sha=$1 rec n meta mode
    plumb ls-tree -rz "$sha" > "$tmp/tree"
    while IFS= read -r -d '' rec; do n=${rec#*$'\t'};meta=${rec%%$'\t'*}; mode=${meta%% *};validpath "$n" && ! protected "$n" && [[ $mode == 100644 || $mode == 100755 ]] || fail "Защищённый или неподдерживаемый путь: $n";done < "$tmp/tree"
}
declare -A tracked=() livevals=() finalvals=()
validate_tree "$compare"
while IFS= read -r -d '' rec;do n=${rec#*$'\t'};tracked["$n"]=1;done < "$tmp/tree"
scan(){
    local n p oid mode rec rc;livevals=(); : > "$tmp/blocked";: > "$tmp/index-info";: > "$tmp/skipped"
    printf 'Контроль файлов: %s\n' "$live"
    (cd -- "$live";find . -mindepth 1 \( -path './.git' -o -iname uploads -o -iname cache -o -iname backups -o -iname logs -o -iname vendor -o -iname node_modules -o -iname .ssh \) -prune -o ! -type d -print0 -o -type d -iname .git -print0 -prune) > "$tmp/list"
    while IFS= read -r -d '' rec;do
        n=${rec#./};p=$live/$n
        if ! validpath "$n";then fail 'Неподдерживаемое имя или вложенный Git-каталог.';fi
        if protected "$n";then printf '%s\n' "$n" >> "$tmp/skipped";continue;fi
        if [[ -z ${tracked[$n]+present} ]];then
            rc=0;(cd -- "$live";plumb --git-dir="$repo/.git" --work-tree="$live" check-ignore --no-index -q -- "$n") || rc=$?
            if (( rc==0 ));then printf '%s\n' "$n" >> "$tmp/skipped";continue;fi
            (( rc==1 )) || fail 'Ошибка правил игнорирования.'
            if ! matchlist "$n" "${allow[@]}";then printf 'БЛОК  %s\n' "$n" >> "$tmp/blocked";continue;fi
        fi
        [[ -f $p && ! -L $p && $(realpath -e -- "$p") == "$p" && $(stat -c %s -- "$p") -le 33554432 ]] || fail "Неподдерживаемый файл: $n"
        mode=100644;[[ ! -x $p ]] || mode=100755;oid=$(plumb hash-object -w --no-filters -- "$p");livevals["$n"]="$mode $oid";printf '%s %s\t%s\0' "$mode" "$oid" "$n" >> "$tmp/index-info"
    done < "$tmp/list"
    GIT_INDEX_FILE="$tmp/idx" plumb read-tree --empty;GIT_INDEX_FILE="$tmp/idx" plumb update-index -z --index-info < "$tmp/index-info";tree=$(GIT_INDEX_FILE="$tmp/idx" plumb write-tree)
}
scan
initial=$tree
plumb -c core.quotePath=false diff-tree --no-renames -r --name-status "$compare^{tree}" "$tree" > "$tmp/diff"
report=$(cat "$tmp/diff" "$tmp/blocked"); missing=0;grep -q $'^D\t' "$tmp/diff" && missing=1
actual_empty=0;[[ -n $(find "$live" -mindepth 1 -maxdepth 1 -printf x -quit) ]] || actual_empty=1
if [[ $action == preview || $action == bind ]];then
    [[ ! -s $tmp/blocked ]] || fail "$report"
    check=$(printf '%s\0' "$live" "$url" "$branch" "$dst" "$tree" "$actual_empty" | sha256sum);check=${check%% *}
    if ((actual_empty));then message='Пустая цель: заполнение только при деплое.';missing=0;else message=${report:-'Отличий нет.'};fi
    if [[ $action == preview ]];then reply ok "$message"$'\n'"База: $dst" "$check" "$missing";exit;fi
    [[ $check == "$token" ]] || fail 'Состояние изменилось. Повторите предпросмотр.'
    (( ! missing || accept_missing )) || fail 'Подтвердите первоначальные удаления отдельно.'
    base=$dst;snap=$dst;empty=$actual_empty;save_state;reply ok "Привязано к Git: $dst";exit
fi
if ((empty));then ((actual_empty)) || fail 'В пустой цели появились файлы. Нужна ручная сверка.';[[ $action != sync ]] || fail 'Пустая цель: сначала выполните деплой.';report='Пустая цель: ожидает первой выкладки.';fi
if [[ $action == check ]];then reply ok "${report:-Правок нет.}";exit;fi
[[ $action == deploy || $action == sync ]] && ((maintenance)) || fail 'Требуется окно обслуживания.'
[[ ! -s $tmp/blocked ]] || fail "$report"
if (( ! empty )) && [[ -s $tmp/diff ]];then
    matchlist "$branch" "${write_branches[@]}" || fail 'Нет разрешения FTP_WRITE_BRANCHES.'
    newsnap=$(g commit-tree "$tree" -p "$snap" -m "Server edits: $target"); ref=ftp-snapshots/$key/$newsnap
    g push "$url" "$newsnap:refs/heads/$ref";snap=$newsnap;active=1;save_state
fi
ref=ftp-snapshots/$key/$snap
if ((active)) && ! g merge-base --is-ancestor "$snap" "$dst";then
    matchlist "$branch" "${write_branches[@]}" || fail 'Нет разрешения FTP_WRITE_BRANCHES.'
    g checkout --force --detach "$dst"
    if ! g merge --no-edit --no-ff "$snap";then conflicts=$(g diff --name-only --diff-filter=U);g merge --abort || :;fail "Разрешите конфликт merge-коммитом в $branch с $ref ($snap). Сайт не изменён."$'\n'"$conflicts";fi
    dst=$(g rev-parse HEAD);g push "$url" "$dst:refs/heads/$branch" || fail "Push отклонён; снимок сохранён: $ref"
fi
if [[ $action == sync ]];then reply ok "Синхронизация завершена: $branch @ $dst";exit;fi
# Prepare the exact objects outside the site before creating the interrupted-publication marker.
validate_tree "$dst";mkdir "$tmp/stage";declare -a changed=() removed=()
while IFS= read -r -d '' rec;do
    n=${rec#*$'\t'};meta=${rec%%$'\t'*};mode=${meta%% *};oid=${meta##* };finalvals["$n"]="$mode $oid"
    [[ ${livevals[$n]-} != "$mode $oid" ]] || continue;p=$live/$n
    [[ $(realpath -m -- "$p") == "$p" && ( ! -e $p || -n ${livevals[$n]+present} ) && ! -L $p ]] || fail "Коллизия пути: $n"
    parent=${p%/*};while [[ $parent != "$live" ]];do [[ ! -e $parent || -d $parent ]] || fail "Путь занят: $parent";parent=${parent%/*};done
    [[ $(plumb cat-file -s "$oid") -le 33554432 ]] || fail "Файл превышает 32 МиБ: $n"
    changed+=("$n");mkdir -p -- "$tmp/stage/$(dirname -- "$n")";plumb cat-file blob "$oid" > "$tmp/stage/$n"
done < "$tmp/tree"
for n in "${!livevals[@]}";do [[ -n ${finalvals[$n]+present} ]] || removed+=("$n");done
scan;[[ $tree == "$initial" && ! -s $tmp/blocked ]] || fail 'Каталог изменился; публикация отменена.'
if ((empty));then [[ -z $(find "$live" -mindepth 1 -maxdepth 1 -printf x -quit) ]] || fail 'Пустая цель изменилась.';fi
printf '{"desired":"%s","before":"%s"}\n' "$dst" "$snap" > "$tmp/pending";mv -- "$tmp/pending" "$home/incomplete.json"
for n in "${changed[@]}";do
    p=$live/$n;[[ $(realpath -m -- "$p") == "$p" ]] || fail 'Путь изменился.'
    printf 'Публикация: %s\n' "$n";(umask 022;mkdir -p -- "${p%/*}");f=$(mktemp "${p%/*}/.dep-tmp-XXXXXXXX");cat -- "$tmp/stage/$n" > "$f"
    perm=644;[[ ! -f $p ]] || perm=$(stat -c %a -- "$p");bits=$((8#$perm & 0666));[[ ${finalvals[$n]} != 100755* ]] || bits=$((bits | 0111));chmod "$(printf '%o' "$bits")" "$f";mv -fT -- "$f" "$p"
done
for n in "${removed[@]}";do p=$live/$n;[[ $(realpath -m -- "$p") == "$p" ]] || fail 'Путь изменился.';printf 'Удаление: %s\n' "$n";rm -- "$p";done
if [[ -n $head && $head != "$dst" ]];then g -C "$live" fetch --no-tags "$repo" "$dst";g -C "$live" update-ref --no-deref HEAD "$dst" "$head";g -C "$live" read-tree "$dst";head=$dst;fi
tracked=();for n in "${!finalvals[@]}";do tracked["$n"]=1;done
scan;[[ ! -s $tmp/blocked && $tree == "$(plumb rev-parse "$dst^{tree}")" ]] || fail 'Итоговое дерево не совпадает с выбранным коммитом.'
base=$dst;snap=$dst;active=0;empty=0;published=$branch;save_state;rm -- "$home/incomplete.json";reply ok "Выложено и проверено: $branch @ $dst"
