#!/bin/bash
# Скрипт деплоя
# Этот скрипт будет передаваться по SSH на целевой сервер и исполняться там.
FOLDER=$1
BRANCH=$2
USER_LOGIN=$3
ALLOWED_USERS=$4
IS_PROD=$5
PROD_ALLOWED_USERS=$6

if [ -z "$FOLDER" ] || [ -z "$BRANCH" ] || [ -z "$USER_LOGIN" ]; then
    echo "Ошибка: Не переданы обязательные параметры (папка, ветка или пользователь)."
    exit 1
fi

echo "Инициализация деплоя: ветка $BRANCH -> $FOLDER (Инициатор: $USER_LOGIN)"
echo "------------------------------------------------------"

# 1. Общая проверка доступа к проекту
if [ -n "$ALLOWED_USERS" ]; then
    allowed=false
    IFS=',' read -ra ADDR <<< "$ALLOWED_USERS"
    for i in "${ADDR[@]}"; do
        if [ "$i" == "$USER_LOGIN" ]; then
            allowed=true
            break
        fi
    done
    if [ "$allowed" = false ]; then
        echo "КРИТИЧЕСКАЯ ОШИБКА БЕЗОПАСНОСТИ: Пользователю '$USER_LOGIN' отказано в доступе к проекту."
        exit 1
    fi
fi

# 2. Проверка доступа к PROD окружению
if [ "$IS_PROD" == "1" ]; then
    echo "==> Проверка прав на PROD деплой..."
    prod_allowed=false
    IFS=',' read -ra PADDR <<< "$PROD_ALLOWED_USERS"
    for j in "${PADDR[@]}"; do
        if [ "$j" == "$USER_LOGIN" ]; then
            prod_allowed=true
            break
        fi
    done
    if [ "$prod_allowed" = false ]; then
        echo "ОТКАЗАНО В ДОСТУПЕ: Пользователь '$USER_LOGIN' не имеет прав на деплой в PROD."
        exit 1
    fi
    echo "==> Права подтверждены."
fi

echo "==> Переход в директорию $FOLDER"
cd "$FOLDER" || { echo "Ошибка: Директория не найдена"; exit 1; }

echo "==> Получение свежих изменений (depth=1)"
git fetch --depth 1 origin "$BRANCH" || { echo "Ошибка git fetch"; exit 1; }

echo "==> Принудительная синхронизация с origin/$BRANCH"
git checkout -f -B "$BRANCH" "origin/$BRANCH" || { echo "Ошибка git checkout"; exit 1; }
git reset --hard "origin/$BRANCH" || { echo "Ошибка git reset"; exit 1; }

echo "------------------------------------------------------"
echo "==> Деплой успешно завершен!"
