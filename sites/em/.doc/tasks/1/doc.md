# Задача 1: Коммерческие предложения (compred)

**Дата документации:** 21.06.2025  
**Область:** `sites/em/sahmatka`, БД `m2profi_em`  
**Статус:** реализовано, изменения **не закоммичены**

---

## 1. Цель задачи

Риэлтор собирает **подборку квартир** с персональными **примечаниями** и **вводным текстом**, получает **короткую публичную ссылку** и отправляет её клиенту. Клиент видит аккуратную страницу без доступа в кабинет.

**MVP:** только квартиры (`obj_type = apartment`).

---

## 2. Архитектура решения

```
Риэлтор (кабинет)
  ├─ Меню «Мои предложения» → ctrind.php?ctr=compred&act=index
  ├─ Карточка квартиры → блок «Добавить к предложению» → ajax add_item
  └─ Редактирование → save_details, save_comment, remove_item, del

Клиент (публично, без авторизации)
  └─ /compred/{32hex_token} → iframe_router → act=public
```

**Маршрутизация:**

| URL | Обработчик |
|-----|------------|
| `/sahmatka/ctrind.php?ctr=compred&act=index` | Список предложений |
| `/sahmatka/ctrind.php?ctr=compred&act=edit&id=N` | Редактирование |
| `/compred/{token}` | Публичная страница (ЧПУ через `.htaccess`) |
| `/sahmatka/ajax_router.php?ctr=compred&act=…` | AJAX-действия |

---

## 3. База данных

### 3.1. `migrations/001_compred.sql`

**Зачем:** создаёт схему хранения предложений и объектов в них.

**Таблица `compred`:**
- `compred_id` — PK
- `caption` — название предложения (до 255 символов)
- `intro_text` — вводный текст для клиента (TEXT, nullable)
- `user_id` — владелец (риэлтор)
- `share_token` — 32-символьный hex-токен для публичной ссылки (UNIQUE)
- `created_at`, `updated_at`
- `del` — мягкое удаление (0/1)

**Таблица `compred_obj`:**
- `compred_obj_id` — PK
- `compred_id` — FK на предложение
- `obj_type` — тип объекта (`apartment` в MVP)
- `obj_id` — ID квартиры (`apartament_id`)
- `comment` — примечание риэлтора для клиента
- `sort_order` — порядок сортировки (зарезервировано)
- UNIQUE `(compred_id, obj_type, obj_id)` — одна квартира не дублируется в предложении

**Почему две таблицы:** предложение — контейнер с метаданными; объекты — отдельные строки с комментариями, что позволяет масштабировать на парковки/аренду позже.

### 3.2. `migrations/002_compred_intro.sql`

**Зачем:** отдельная миграция для добавления `intro_text`, если таблица была создана до появления поля в `001`.

**Дополнительно в коде:** `compred_ensure_intro_column()` в `compred_helpers.php` автоматически выполняет `ALTER TABLE`, если колонки нет — защита от «Ошибка сети» при сохранении на старых БД.

---

## 4. Новые файлы

### 4.1. `inc/compred_config.php`

**Зачем:** конфигурация типов объектов для будущего расширения.

```php
$GLOBALS['compred_obj_types'] = [
    'apartment' => ['label' => 'КВАРТИРА', 'enabled' => true],
    'parking'   => ['enabled' => false],
    'rent'      => ['enabled' => false],
];
```

**Почему:** MVP только квартиры; структура готова для парковок и аренды без рефакторинга.

---

### 4.2. `inc/compred_helpers.php`

**Зачем:** общая бизнес-логика, не привязанная к контроллеру — группировка, URL, OG-meta, share-ссылки.

| Функция | Назначение |
|---------|------------|
| `compred_kvartal_meta()` | Мета микрорайона из JOIN-данных квартиры |
| `compred_home_meta()` | Мета дома: название, адрес, этажность, отделка, срок сдачи |
| `compred_apartment_line()` | Строка «Секция N, этаж X, квартира №Y» |
| `compred_apartment_card_vars()` | Переменные для шаблона карточки (цена, площадь, фото) |
| `compred_row_to_object()` | SQL-строка → структура `{compred_obj_id, apartment: {...}}` |
| `compred_group_objects()` | Группировка: **микрорайон → дом → квартиры** |
| `compred_build_home_url()` | Ссылка на дом: `/sahmatka/user.php?action=objects&home={id}` |
| `compred_build_public_url()` | ЧПУ: `{APP_URL}/compred/{token}` |
| `compred_share_links()` | URL для Telegram, WhatsApp, MAX, VK, OK |
| `compred_public_page_meta()` | Open Graph / Twitter / JSON-LD |
| `compred_bootstrap_public_meta()` | Загрузка meta для `<head>` до рендера (в `iframe_router`) |
| `compred_ensure_intro_column()` | Автомиграция колонки `intro_text` |

**Почему отдельный файл:** helpers используются и в контроллере, и в `iframe_router.php` (OG-meta до вывода HTML).

**Исправления в ходе задачи:**
- JOIN через `apartaments` без дублирования квартир
- `compred_build_home_url` — исправлена с `ctrind.php?ctr=objects` на `user.php?action=objects`

---

### 4.3. `fw/controllers/ctr__compred.php`

**Зачем:** основной контроллер модуля compred.

**Приватные методы:**
- `assert_auth()` — проверка `$_SESSION['sh_id']`
- `json_response()` — JSON-ответ + `exit` (исправление «пустого ответа» AJAX)
- `load_compred()` / `load_compred_assert_owner()` — загрузка с проверкой владельца
- `resolve_apartment()` — проверка существования квартиры
- `load_objects_resolved()` — объекты предложения с JOIN homes/sections/kvartal
- `ensure_share_token()` — генерация `share_token` при первом обращении

**Actions (экшены):**

| Action | Метод | Описание |
|--------|-------|----------|
| `index` | HTML | Список предложений текущего пользователя |
| `edit` | HTML | Редактирование: название, intro, ссылка, квартиры |
| `view` | HTML | Просмотр для авторизованного (коллеги) |
| `public` | HTML | Публичная страница по token (без авторизации) |
| `add_item` | POST | Добавить квартиру в предложение (или создать новое) |
| `save_details` | AJAX | Сохранить название + intro_text одним запросом |
| `save_caption` | AJAX | Обёртка над `save_details` (обратная совместимость) |
| `save_intro` | AJAX | Обёртка над `save_details` |
| `save_comment` | AJAX | Автосохранение примечания к квартире (debounce 600ms) |
| `remove_item` | AJAX | Удалить квартиру из предложения |
| `generate_link` | AJAX | Получить/сгенерировать публичную ссылку |
| `del` | AJAX | Мягкое удаление предложения (`del=1`) |

**Почему `save_details` вместо раздельных save_caption/save_intro:** пользователь просил одну кнопку «Сохранить»; автосохранение intro вызывало «Ошибка сети» при отсутствии колонки в БД.

**Безопасность:** все изменяющие action проверяют `user_id === $_SESSION['sh_id']`; публичный `act__public` доступен только по валидному 32-символьному token.

---

### 4.4. `sites/em/compred/.htaccess`

**Зачем:** ЧПУ для клиентских ссылок.

```
/compred/abc123...32hex → /sahmatka/iframe_router.php?ctr=compred&act=public&token=abc123...
```

**Почему:** короткая ссылка для мессенджеров вместо длинного iframe URL.

---

### 4.5. Шаблоны `fw/templates/compred/`

#### `_layout_assets.php`
Подключает `compred.css?v=22` — изолированные стили с префиксом `cp-`.

#### `index.php`
- Пустой список → инструкция (4 шага) + кнопка «Перейти к объектам»
- Список → карточки с названием, кол-вом квартир, датой обновления, кнопки «Открыть» / «Удалить»
- Термин «комментарии» заменён на «примечания»

#### `edit.php`
Страница редактирования:
- Шапка: заголовок, счётчик квартир, кнопки **Сохранить** + **Удалить** (справа)
- Блок ссылки (`_share_panel.php`) — **на всю ширину**, ниже шапки
- Форма: название + вводный текст → одна кнопка «Сохранить» (AJAX `save_details`)
- Список квартир через `_objects_grouped.php` или empty-state

#### `public.php`
Публичная страница для клиента:
- H1 = название предложения
- Вводный текст — **жирный, на всю ширину**, без рамки
- Группированный список квартир
- Без блока «Поделиться» (только просмотр)

#### `view.php`
Внутренний просмотр для авторизованных (аналог public с intro).

#### `_share_panel.php`
Два режима (`$share_panel_mode`):
- **`edit`:** поле URL + Копировать / Смотреть / Поделиться (dropdown: Telegram, WhatsApp, MAX, VK, OK)
- **`public`:** не используется на public (убран по запросу)

#### `_objects_grouped.php`
Рендер иерархии **микрорайон → дом → квартиры**:
- Микрорайон: зелёная рамка **2px** со всех сторон
- Дом: фон `#f1f5f9`, рамка, зелёная полоска слева; **название дома — ссылка** на `user.php?action=objects&home={id}`
- Квартиры: include `_card_apartment.php`

#### `_card_apartment.php`
Карточка квартиры в двух режимах:
- **`edit`:** планировка слева, данные + textarea «Примечание» + «Удалить из списка» справа
- **`public`:** планировка + данные + блок примечания (если не пусто)

#### `_card_apartment_body.php`
Общая часть карточки: строка квартиры, цена, площадь/комнаты.

#### `_public_meta.php`
Open Graph, Twitter Card, canonical, JSON-LD для превью в мессенджерах.

---

### 4.6. `fw/templates/apartments/compred_block.php`

**Зачем:** блок «Добавить к предложению» на карточке квартиры.

**Содержимое:**
- Select существующих предложений или «Создать новое»
- Поле названия (показывается только при создании нового)
- Textarea «Примечание (необязательно)»
- Кнопка «Добавить к предложению»
- Ссылка «Смотреть предложение» (обновляется при выборе из списка)

**Почему отдельная `<form>`:** вложенные формы HTML невалидны; форма compred **вне** формы бронирования.

**POST →** `ajax_router.php?ctr=compred&act=add_item` с `return_url` для redirect обратно в iframe карточки.

---

### 4.7. `template/default/css/compred.css`

**Зачем:** изолированные стили (префикс `cp-`), не раздувают `admin.css`.

**Ключевые блоки:**
- CSS-переменные: `--cp-accent: #00CDAD` (фирменный EM)
- `.cp-public` — публичная страница, max-width 1500px
- `.cp-public__intro` — жирный вводный текст, full width
- `.cp-group__header--kvartal` — рамка 2px accent
- `.cp-group__header--home` — фон `#f1f5f9`, рамка, ссылка `.cp-home-link`
- `.cp-card` — карточки квартир (edit/public), планировки **без обрезки** (`object-fit: contain`)
- `.cp-edit-page` — без скруглений, без max-width (по запросу)
- `.cp-share-link-row` — блок ссылки на всю ширину
- `.cp-details-edit` — форма названия + intro
- Адаптив `@media (max-width: 768px)`

**Версия:** v=22 (cache bust в `_layout_assets.php`).

---

### 4.8. `template/default/js/compred.js`

**Зачем:** клиентская логика compred (jQuery).

| Функция | Где используется |
|---------|------------------|
| `cpInitBlock()` | Блок на карточке квартиры: toggle новое/существующее предложение |
| `cpInitEdit()` | Страница edit: save_details, delete, autosave примечаний, remove |
| `cpInitIndex()` | Список: удаление предложений |
| `cpInitShareControls()` | Копировать ссылку, dropdown «Поделиться» |

**AJAX base:** `/sahmatka/ajax_router.php?ctr=compred&act=`

**Исправления:**
- Единый `save_details` вместо autosave intro
- `cpFailAlert()` — парсит JSON-ошибку из responseText
- `.attr('data-compred-obj-id')` вместо `.data()` (fix удаления)

**Версия:** v=8 на edit, v=3 на index/block.

---

## 5. Изменённые существующие файлы

### 5.1. `fw/controllers/ctr__apartments.php`

**Что добавлено в `act__order()`** (после early return, ~строка 707):

```php
// compred: блок «Добавить к предложению»
$compred_list = [];      // список предложений пользователя
$compred_msg / $compred_err  // flash после redirect
$compred_selected_id    // какое предложение выбрано после add
$compred_return_url     // URL для возврата в iframe карточки
```

Данные передаются в `$tpl_data`:
- `compred_list`, `compred_msg`, `compred_err`, `compred_selected_id`
- `apartament_id`, `return_url`

**Почему здесь:** карточка квартиры рендерится через `act__order`; блок compred нужен только авторизованным с `apartament_id`.

---

### 5.2. `fw/templates/apartments/form_broni_ag.php`

**Изменение:** после формы бронирования добавлен include:

```php
if (!empty($_SESSION['sh_id']) && !empty($data['apartament_id'])) {
    include __DIR__ . '/compred_block.php';
}
```

**Почему:** админская/агентская карточка квартиры — точка добавления в предложение.

---

### 5.3. `fw/templates/apartments/form_broni_pub.php`

**Изменение:** аналогичный include `compred_block.php` после формы брони.

**Почему:** публичная карточка в iframe (order) — основной сценарий риэлтора на шахматке.

---

### 5.4. `iframe_router.php`

**Изменения:**

1. `require_once compred_helpers.php`
2. Для `ctr=compred&act=public` — загрузка `$compred_page_meta` **до** вывода HTML
3. В `<head>`:
   - если compred public → include `_public_meta.php` (OG, title, description)
   - иначе → стандартные noindex meta

**Почему:** OG-теги должны быть в `<head>` до body; публичная страница compred не индексируется как noindex, а получает нормальный title/description для шаринга.

---

### 5.5. `template/default/in_head.php`

**Изменение:** пункт меню **«Мои предложения»** добавлен **сразу под «Квартиры»** (ранее был после «Брони»).

```php
<li><a href="/sahmatka/ctrind.php?ctr=compred&act=index" ...>Мои предложения</a></li>
```

**Условие видимости:** все пользователи кроме `keys1`, `keys2`, `em_nsv`, `director`.

**Active state:** `class="active"` при `$_GET['ctr'] === 'compred'`.

---

## 6. UX/UI — итоговые решения по запросам

| Запрос | Решение |
|--------|---------|
| Публичная страница без обрезания планировок | `object-fit: contain` в CSS |
| max-width 1500px на public | `.cp-public { max-width: 1500px }` |
| Группировка по микрорайонам/домам | `compred_group_objects()` + `_objects_grouped.php` |
| Вводный текст `intro_text` | Поле в БД + textarea на edit + блок на public |
| Единая кнопка «Сохранить» | `save_details` — название + intro за один AJAX |
| Ошибка сети при вводе intro | Убран autosave; `compred_ensure_intro_column()` |
| Блок ссылки на всю ширину | Share panel вынесен из `.cp-edit-header__main` |
| Вводный текст жирный, full width | CSS `.cp-public__intro` |
| «Комментарий» → «Примечание» | В шаблонах compred_block, _card_apartment, index |
| Ссылка на дом | `compred_build_home_url()` → `user.php?action=objects&home=` |
| Блок дома выделен | Фон `#f1f5f9`, рамка, accent слева |
| Микрорайон — зелёная рамка 2px | `.cp-group__header--kvartal { border: 2px solid var(--cp-accent) }` |
| Меню под «Квартиры» | `in_head.php` |
| ЧПУ `/compred/{token}` | `sites/em/compred/.htaccess` |

---

## 7. Полный список незакоммиченных файлов

### Новые (untracked)

```
sites/em/compred/.htaccess
sites/em/sahmatka/fw/controllers/ctr__compred.php
sites/em/sahmatka/fw/templates/apartments/compred_block.php
sites/em/sahmatka/fw/templates/compred/
  ├── _card_apartment.php
  ├── _card_apartment_body.php
  ├── _layout_assets.php
  ├── _objects_grouped.php
  ├── _public_meta.php
  ├── _share_panel.php
  ├── edit.php
  ├── index.php
  ├── public.php
  └── view.php
sites/em/sahmatka/inc/compred_config.php
sites/em/sahmatka/inc/compred_helpers.php
sites/em/sahmatka/migrations/001_compred.sql
sites/em/sahmatka/migrations/002_compred_intro.sql
sites/em/sahmatka/template/default/css/compred.css
sites/em/sahmatka/template/default/js/compred.js
```

### Изменённые (modified)

```
sites/em/sahmatka/fw/controllers/ctr__apartments.php   (+33 строк)
sites/em/sahmatka/fw/templates/apartments/form_broni_ag.php   (+6 строк)
sites/em/sahmatka/fw/templates/apartments/form_broni_pub.php  (+6 строк)
sites/em/sahmatka/iframe_router.php                           (+12 строк)
sites/em/sahmatka/template/default/in_head.php                (+7 строк)
```

---

## 8. Развёртывание

### 8.1. Миграция БД

```sql
-- Выполнить на m2profi_em:
source sites/em/sahmatka/migrations/001_compred.sql
-- Если таблица уже без intro_text:
source sites/em/sahmatka/migrations/002_compred_intro.sql
```

Либо колонка добавится автоматически при первом сохранении/edit через `compred_ensure_intro_column()`.

### 8.2. Apache

Убедиться, что `sites/em/compred/.htaccess` обрабатывается (mod_rewrite, AllowOverride).

### 8.3. Проверка

1. Войти как риэлтор → меню «Мои предложения» под «Квартиры»
2. Открыть квартиру → «Добавить к предложению»
3. Edit → сохранить название + intro → скопировать ссылку
4. Открыть `/compred/{token}` без авторизации
5. Проверить OG-preview (Telegram/VK)
6. Клик по названию дома → `user.php?action=objects&home=N`

---

## 9. Известные ограничения MVP

- Только квартиры; парковки/аренда отключены в `compred_config.php`
- `sort_order` не редактируется в UI (сортировка по `compred_obj_id`)
- Публичная ссылка на дом ведёт в кабинет (`user.php`) — нужна авторизация
- `act__view` — заготовка для просмотра коллегами, не основной сценарий
- Cache bust CSS/JS разный на страницах (v=3 index/block, v=8 edit, v=22 css) — при деплое синхронизировать

---

## 10. Связанные документы

- [plan.md](./plan.md) — исходный план реализации v2
- [card_appartament.md](../../card_appartament.md) — карточка квартиры
- Прототипы в `.doc/tasks/1/` (pr.php, pr_edit.php и др.) — черновики до реализации
