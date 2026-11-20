# Read-only functions, sent on stdin to bash -s. Output is parsed by PHP, not evaluated.
export LC_ALL=C GIT_TERMINAL_PROMPT=0 GIT_OPTIONAL_LOCKS=0
git() {
    printf '$ git' >&3;printf ' %q' "$@" >&3;printf '\n' >&3
    # Preserve stdout for the protocol and show its textual value in the terminal.
    local out rc=0;out=$(command git "$@") || rc=$?
    if [[ -n $out ]];then printf '%s\n' "$out" >&3;printf '%s\n' "$out";fi
    return "$rc"
}
exec 3>&2
emit() { printf '%s\0' "$1" "${2-}" "${3-}" "${4-}" "${5-}"; }
inspect() (
    local path key root sha='' branch='' state='' origin='' top
    printf 'DEP_STEP directory\n' >&3
    path=$(printf '%s' "$1" | base64 -d) || exit 2
    [[ -d "$path" && -r "$path" && -x "$path" && "$(cd -- "$path" && pwd -P)" == "$path" ]] || { emit path; return; }
    if [[ $2 != '-' ]]; then
        key=$2; root="$HOME/.dep-control/$key"
        [[ ! -e "$root/incomplete.json" ]] || { emit incomplete; return; }
        if [[ -f "$root/state.v2" ]]; then
            mapfile -t parts < "$root/state.v2" || { emit denied;return; }
            [[ ${#parts[@]} == 7 ]] || { emit denied;return; }
            printf -v state '%s\n' 'v2:' "${parts[@]}";state=${state%$'\n'}
        elif [[ -e "$root/state.json" ]]; then
            [[ -f "$root/state.json" && -r "$root/state.json" && $(wc -c < "$root/state.json") -le 16384 ]] || { emit denied; return; }
            state=$(cat -- "$root/state.json") && [[ -n "$state" ]] || { emit denied; return; }
        fi
    fi
    if [[ -e "$path/.git" ]]; then
        command -v git >/dev/null || { emit nogit; return; }
        top=$(git -C "$path" rev-parse --show-toplevel) && [[ "$top" == "$path" ]] || { emit giterror; return; }
        sha=$(git -C "$path" rev-parse --verify 'HEAD^{commit}' 2>/dev/null) || {
            local ref rc=0; ref=$(git -C "$path" symbolic-ref -q HEAD) || { emit giterror; return; }
            git -C "$path" show-ref --verify --quiet "$ref" || rc=$?
            if [[ $rc == 1 ]]; then emit unborn; else emit giterror; fi; return;
        }
        branch=$(git -C "$path" symbolic-ref -q --short HEAD) || branch=''
        if [[ $2 != '-' ]]; then origin=$(git -C "$path" config --get remote.origin.url) || origin=''; fi
    fi
    emit ok "$sha" "$branch" "$state" "$origin"
)
