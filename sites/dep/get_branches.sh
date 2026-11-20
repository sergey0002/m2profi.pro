#!/usr/bin/env bash
# Sourced into the fixed stdin script. Output: five NUL-terminated fields per row.
# HTTP credentials are scoped to the GitHub origin and never printed or placed in argv.
set -eu -o pipefail
export GIT_TERMINAL_PROMPT=0 GIT_OPTIONAL_LOCKS=0 LC_ALL=C
# Also keep the standalone <folder> <url> <token> text interface.
standalone=0
if [[ ${folder+x} != x ]];then
    folder=${1:?repo directory required};repo=${2:-};token=${3:-};standalone=1
fi
cache=0
if [[ -d "$folder/.git" ]];then
    origin=$(git -C "$folder" config --get remote.origin.url || :)
    [[ -n $repo ]] || repo=$origin
    [[ ${origin%.git} != "${repo%.git}" ]] || cache=1
fi
if [[ -n $token && $repo == https://github.com/* ]];then export GIT_CONFIG_COUNT=1 GIT_CONFIG_KEY_0=http.https://github.com/.extraHeader
    GIT_CONFIG_VALUE_0="Authorization: Basic $(printf 'x-access-token:%s' "$token" | base64 | tr -d '\n')";export GIT_CONFIG_VALUE_0;fi
printf '$ git ls-remote --heads %q\n' "$repo" >&2
if refs=$(git ls-remote --heads "$repo");then source=remote
elif ((cache));then
    printf 'Remote недоступен. Используются кэшированные refs проекта.\n' >&2
    refs=$(git -C "$folder" for-each-ref --format='%(objectname)%09%(refname)' refs/remotes/origin refs/heads);source=cache
else exit 3;fi
unset token GIT_CONFIG_VALUE_0 GIT_CONFIG_COUNT GIT_CONFIG_KEY_0
if ((!standalone));then printf '%s\0' "$source";fi
declare -A seen=()
while IFS=$'\t' read -r oid ref;do
    [[ -n $oid && -n $ref ]] || continue
    name=${ref#refs/heads/};name=${name#refs/remotes/origin/}
    [[ $name != HEAD && $name != ftp-snapshots/* && -z ${seen[$name]+x} ]] || continue;seen["$name"]=1
    printf '%s\t%s\n' "$oid" "$ref" >&2
    if ((standalone));then
        if ((cache)) && git -C "$folder" cat-file -e "${oid}^{commit}" 2>/dev/null;then
            printf '%s|' "$name";git -C "$folder" show -s --format='%s|%cs|%an' "$oid"
        else printf '%s|%s||\n' "$name" "$oid";fi
        continue
    fi
    printf '%s\0%s\0' "$name" "$oid"
    if ((cache)) && git -C "$folder" cat-file -e "${oid}^{commit}" 2>/dev/null;then
        git -C "$folder" --no-pager show -s --no-notes --format='format:%s%x00%an%x00%ct%x00' "$oid"
    else printf '\0\0%s\0' 0;fi
done <<< "$refs"
