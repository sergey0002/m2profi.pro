#!/bin/bash
# Скрипт получения веток из репозитория
# Использование: ./get_branches.sh <путь-к-локальному-репо> <repo_url> <github_token>

REPO_PATH=$1
REPO_URL=$2
GITHUB_TOKEN=$3

if [ ! -d "$REPO_PATH" ]; then
    echo "Ошибка: путь $REPO_PATH не найден"
    exit 1
fi

cd "$REPO_PATH" || exit 1

# Если передан токен, делаем удаленный запрос к GitHub
if [ -n "$GITHUB_TOKEN" ] && [ -n "$REPO_URL" ]; then
    # Делаем URL вида https://TOKEN@github.com/user/repo.git
    AUTH_URL=$(echo "$REPO_URL" | sed "s|https://|https://$GITHUB_TOKEN@|")
    
    # Получаем ветки напрямую с удаленного сервера без изменения локального репозитория
    git ls-remote --heads "$AUTH_URL" 2>/dev/null | grep -v "\^{}" | while read -r hash ref; do
        branch=${ref#refs/heads/}
        
        # Пытаемся получить дату и автора из локального кэша, если коммит есть
        date=""
        author=""
        subject=""
        if git merge-base --is-ancestor "$hash" HEAD 2>/dev/null || git log -1 "$hash" >/dev/null 2>&1; then
             date=$(git log -1 --format="%ci" "$hash" | cut -d' ' -f1)
             author=$(git log -1 --format="%an" "$hash")
             subject=$(git log -1 --format="%s" "$hash")
        fi
        
        [ -n "$subject" ] && out_hash="$subject" || out_hash="$hash"
        
        echo "$branch|$out_hash|$date|$author"
    done
else
    # Репозиторий приватный и нет токена, берем данные из локального кэша удаленных веток (origin) и локальных веток
    git for-each-ref --sort=-committerdate refs/heads/ refs/remotes/origin/ \
        --format="%(refname:short)|%(subject)|%(committerdate:short)|%(authorname)" | \
        sed 's|^origin/||' | grep -v '^HEAD$' | awk -F'|' '!seen[$1]++'
fi
