# Задача 1 (sigma): Перенос «Коммерческих предложений» (compred) с EM

**Дата документации:** 21.06.2025  
**Область:** `sites/sigma/` ← эталон `sites/em/`  
**Статус:** **не реализовано** — только документация и план переноса  
**Эталон:** [sites/em/.doc/tasks/1/doc.md](../../../em/.doc/tasks/1/doc.md), [plan.md](../../../em/.doc/tasks/1/plan.md)

---

## 1. Цель

Перенести на **sigma** тот же функционал compred, что уже реализован на **em**: риэлтор собирает подборку квартир/апартаментов с примечаниями и вводным текстом, получает короткую публичную ссылку `/compred/{token}` и отправляет клиенту.

**MVP:** только объекты типа `apartment` (`obj_type = apartment` в БД).

**На данный момент:** в `sites/sigma` нет ни одного файла или упоминания `compred` в коде.

---

## 2. Сводка сравнения EM ↔ sigma (21.06.2025)

| Категория | EM | Sigma | Действие |
|-----------|:--:|:-----:|----------|
| Новые файлы compred (19 шт.) | ✓ | ✗ | **Скопировать** из EM → sigma |
| `fw/controllers/ctr__compred.php` | ✓ | ✗ | Скопировать |
| Интеграция `ctr__apartments.php` | +33 строки compred | нет compred | **Дополнить** по образцу EM |
| `form_broni_pub.php` | +include compred_block | нет include | **Дополнить** |
| `form_broni_ag.php` | +include compred_block | нет include | **Дополнить** |
| `iframe_router.php` | OG-meta для public | стандартный noindex | **Дополнить** |
| `in_head.php` | пункт «Мои предложения» | нет пункта | **Дополнить** |
| `sites/*/compred/.htaccess` | ✓ | ✗ | Скопировать |
| `sahmatka/migrations/` | 2 SQL | папки нет | Создать + скопировать SQL |
| `sahmatka/inc/` | compred_* + window_orient | пусто (в git) | Скопировать compred_* |
| `config.php` | include `compred_config.php` (на сервере) | не в git | **Дополнить на сервере** |
| БД `compred`, `compred_obj` | на `m2profi_em` | нет таблиц | **Миграция** на БД sigma |
| `menu-icon-8.svg` | ✓ | ✗ | Скопировать с EM |

**Размеры ключевых файлов (строки):**

| Файл | EM | Sigma | Δ |
|------|----|-------|---|
| `ctr__apartments.php` | 1173 | 1118 | EM шире на ~55 строк (в т.ч. compred) |
| `form_broni_pub.php` | 92 | 73 | EM +19 (compred_block) |
| `form_broni_ag.php` | 90 | 55 | EM +35 (compred_block + доработки) |
| `iframe_router.php` | 125 | 115 | EM +10 (compred meta) |
| `in_head.php` | 1079 | 783 | разная ветка меню/конфига |

---

## 3. Архитектура (как на EM, без изменений логики)

```
Риэлтор (кабинет sigma)
  ├─ Меню «Мои предложения» → ctrind.php?ctr=compred&act=index
  ├─ Карточка апартамента → блок «Добавить к предложению» → ajax add_item
  └─ Редактирование → save_details, save_comment, remove_item, del

Клиент (публично)
  └─ https://{subdomain}.m2profi.pro/compred/{32hex} → iframe_router → act=public
```

**Маршрутизация на sigma** (после переноса):

| URL | Обработчик |
|-----|------------|
| `/sahmatka/ctrind.php?ctr=compred&act=index` | Список предложений |
| `/sahmatka/ctrind.php?ctr=compred&act=edit&id=N` | Редактирование |
| `/compred/{token}` | ЧПУ → `iframe_router.php?ctr=compred&act=public&token=…` |
| `/sahmatka/ajax_router.php?ctr=compred&act=…` | AJAX |

Публичный URL формируется в `compred_helpers.php` → `compred_build_public_url()` → `{APP_URL}/compred/{token}`. На sigma `APP_URL` должен указывать на домен тенанта (например `https://sigma.m2profi.pro`).

---

## 4. Файлы для полного копирования (19 шт.)

Источник: `sites/em/` → назначение: `sites/sigma/` (те же относительные пути).

### 4.1. Корень тенанта

```
sites/sigma/compred/.htaccess                    (224 B)
```

ЧПУ: `/compred/[a-f0-9]{32}` → `/sahmatka/iframe_router.php?ctr=compred&act=public&token=…`

### 4.2. Контроллер и inc

```
sites/sigma/sahmatka/fw/controllers/ctr__compred.php     (17 832 B)
sites/sigma/sahmatka/inc/compred_config.php              (285 B)
sites/sigma/sahmatka/inc/compred_helpers.php             (13 373 B)
```

### 4.3. Миграции БД

```
sites/sigma/sahmatka/migrations/001_compred.sql          (1 311 B)
sites/sigma/sahmatka/migrations/002_compred_intro.sql    (190 B)
```

Папку `migrations/` на sigma **создать** — в репозитории её нет.

### 4.4. Шаблоны compred (10 файлов)

```
sites/sigma/sahmatka/fw/templates/compred/
  _card_apartment.php          (2 400 B)
  _card_apartment_body.php     (502 B)
  _layout_assets.php           (79 B)
  _objects_grouped.php         (2 733 B)
  _public_meta.php             (1 276 B)
  _share_panel.php             (3 874 B)
  edit.php                     (3 864 B)
  index.php                    (3 573 B)
  public.php                   (720 B)
  view.php                     (944 B)
```

### 4.5. Блок в карточке апартамента

```
sites/sigma/sahmatka/fw/templates/apartments/compred_block.php   (2 815 B)
```

### 4.6. Статика

```
sites/sigma/sahmatka/template/default/css/compred.css    (23 410 B)
sites/sigma/sahmatka/template/default/js/compred.js      (8 330 B)
```

### 4.7. Дополнительный ассет (нет на sigma)

```
sites/sigma/sahmatka/template/default/images/menu-icon-8.svg
```

Скопировать с EM — используется в пункте меню «Мои предложения».

**Итого к копированию:** 19 файлов compred + 1 иконка меню.

---

## 5. Файлы для дополнения (патчи по образцу EM)

На sigma файлы **существуют**, но **без compred**. Правки — перенос тех же фрагментов, что в EM (см. [plan.md](./plan.md) §3–§4).

### 5.1. `sahmatka/fw/controllers/ctr__apartments.php`

**Метод:** `act__order()`  
**Место:** сразу после `if (!$data) { … return; }`, **до** `// Извлекаем нужные части` (sigma ~стр. 706, EM ~стр. 707).

**Добавить (~25 строк):**
- загрузку `$compred_list`, flash `$compred_msg` / `$compred_err`, `$compred_selected_id`
- `$compred_apartament_id`, `$compred_return_url`

**В `$tpl_data` (~стр. 871 sigma):** ключи  
`compred_list`, `compred_msg`, `compred_err`, `compred_selected_id`, `apartament_id`, `return_url`.

На EM эти ключи есть в основном `$tpl_data`; на sigma их нет.

### 5.2. `sahmatka/fw/templates/apartments/form_broni_pub.php`

**После** закрывающего `</form>` бронирования (sigma ~стр. 69), **внутри** правой колонки `.xxx`:

```php
<?php
if (!empty($_SESSION['sh_id']) && !empty($data['apartament_id'])) {
    include __DIR__ . '/compred_block.php';
}
?>
```

На EM — строки 85–89. На sigma блок отсутствует.

### 5.3. `sahmatka/fw/templates/apartments/form_broni_ag.php`

**После** `</form>` смены статуса (sigma ~стр. 52):

```php
<?php
if (!empty($_SESSION['sh_id']) && !empty($data['apartament_id'])) {
    include __DIR__ . '/compred_block.php';
}
?>
```

На EM — строки 83–87.

### 5.4. `sahmatka/iframe_router.php`

**После** `include('config.php');`:**

```php
require_once __DIR__ . '/inc/compred_helpers.php';
```

**После** `$_GET=$_REQUEST;`:**

```php
$compred_page_meta = null;
if (($_GET['ctr'] ?? '') === 'compred' && ($_GET['act'] ?? '') === 'public') {
    $compred_page_meta = compred_bootstrap_public_meta((string)($_GET['token'] ?? ''));
}
```

**В `<head>`:** условный блок meta — если `$compred_page_meta`, include `_public_meta.php`, иначе стандартные noindex + title.

Точный diff EM vs sigma: **10 строк** (см. `git diff --no-index sites/em/... sites/sigma/...`).

### 5.5. `sahmatka/template/default/in_head.php`

**Отличие sigma от EM в меню:**
- первый пункт: **«Апартаменты»** (`user.php?action=objects&home=60&sdan=0`), не «Квартиры»
- в шапке: `$m2SiteBaseUrl`, `$m2ClientSiteUrl` из `$GLOBALS['config']`

**Вставка** (по аналогии с EM, строки 40–46): **сразу после** пункта «Апартаменты», **до** блока «Парковки»:

```php
<?
if ($_SESSION['sh_login'] != 'keys1' && $_SESSION['sh_login'] != 'keys2' && $_SESSION['sh_login'] != 'em_nsv' && $_SESSION['sh_login'] != 'director') {
?>
<li><a href="/sahmatka/ctrind.php?ctr=compred&act=index"<?= (($_GET['ctr'] ?? '') === 'compred') ? ' class="active"' : '' ?>><i><img src="template/default/images/menu-icon-8.svg" alt=""></i>Мои предложения</a></li>
<?
}
?>
```

Условие видимости — **как на EM** (исключены keys1, keys2, em_nsv, director). При необходимости уточнить список логинов sigma у заказчика.

### 5.6. `sahmatka/config.php` (только на сервере, не в git)

После подключения прочих `inc/*.php` добавить:

```php
include(__DIR__ . '/inc/compred_config.php');
```

Убедиться, что `putenv("APP_URL=…")` задаёт **домен sigma** (например `https://sigma.m2profi.pro`), иначе публичные ссылки и OG-url будут неверными.

---

## 6. База данных

### 6.1. Таблицы (как на EM)

Выполнить на **БД тенанта sigma** (имя уточнить в `config.php` на сервере; в git не хранится):

```sql
source sites/sigma/sahmatka/migrations/001_compred.sql
source sites/sigma/sahmatka/migrations/002_compred_intro.sql
```

Либо полагаться на `compred_ensure_intro_column()` при первом сохранении.

### 6.2. Схема

- **`compred`:** caption, intro_text, user_id, share_token (UNIQUE), del, timestamps
- **`compred_obj`:** compred_id, obj_type, obj_id, comment, sort_order, UNIQUE (compred_id, obj_type, obj_id)

Данные **не переносятся** с EM — отдельный тенант, отдельная БД.

---

## 7. Отличия sigma от EM (учесть при переносе)

| Тема | EM | Sigma | Рекомендация при переносе |
|------|----|-------|---------------------------|
| Терминология UI | «Квартиры» | «Апартаменты», `unit_label_cap()` | **MVP:** копировать compred как на EM; при желании позже заменить «квартира» в helpers/шаблонах |
| Accent CSS | `#00CDAD` | тот же в form_broni_* | `compred.css` копировать без смены `--cp-accent` |
| Домен / APP_URL | em-nsk.ru / em.m2profi.pro | `{subdomain}.m2profi.pro` | Проверить `APP_URL` в config sigma |
| `in_head.php` | статичный backlink em-nsk.ru | `$m2ClientSiteUrl` | Пункт compred не зависит от backlink |
| `inc/window_orient.php` | есть на EM | нет в git sigma | **не требуется** для compred |
| Папка `migrations/` | есть | нет | создать при копировании |
| Deploy | `sites/dep/projects/m2profi.env` | отдельный тенант? | После реализации — отдельный deploy-проект или ручной выклад |

---

## 8. Файлы, которые НЕ трогать

| Файл | Причина |
|------|---------|
| `fw/templates/apartments/public_card.php` | compred не показывается на public_card (как на EM) |
| `template/default/header.php` | compred.css подключается точечно, не глобально |
| `ctrind.php`, `ajax_router.php`, `router.php` | маршрутизация через `ctr=compred` уже поддерживается ядром |

---

## 9. Чеклист приёмки (после реализации)

1. Миграция БД на sigma выполнена, таблицы `compred` / `compred_obj` есть
2. `config.php` на сервере подключает `compred_config.php`, `APP_URL` корректен
3. Меню «Мои предложения» под «Апартаменты»
4. Карточка апартамента (iframe order) — блок «Добавить к предложению»
5. Edit → сохранение названия + intro → публичная ссылка
6. `/compred/{token}` открывается без авторизации
7. OG-preview в мессенджерах (title, description)
8. Apache: `sites/sigma/compred/.htaccess` активен (mod_rewrite)

---

## 10. Связанные документы

- [plan.md](./plan.md) — пошаговый план переноса с полными патчами
- [EM doc.md](../../../em/.doc/tasks/1/doc.md) — описание реализованного функционала
- [EM plan.md](../../../em/.doc/tasks/1/plan.md) — исходный план реализации v2

---

*Документ подготовлен по сравнению репозитория 21.06.2025. Код на sigma не менялся.*
