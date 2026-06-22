# Задача 2: Аудит состава репозитория `sites/em`

**Дата:** 2026-06-21  
**Область:** только файлы, **отслеживаемые git** (`git ls-files sites/em`)  
**Всего в git:** 2244 файла  
**Цель:** найти мёртвый код, резервные копии, vendor-мусор и пути-кандидаты на удаление или вынос из репозитория.

---

## 1. Краткий вывод

| Категория | Файлов (оценка) | Риск удаления | Комментарий |
|-----------|-----------------|---------------|-------------|
| Имя с `!` / `!!` / `!!!` | **23** | Низкий | Резервные копии, роутер их **не грузит** |
| Числовые суффиксы (`.php11`, `.php0`, `.js1`) | **5** | Низкий | Черновики рядом с боевыми файлами |
| Прототипы `.doc/tasks/1/*.php` | **8** | Низкий | Заменены реальным compred; на прод не ссылаются |
| `test.php`, `Ctr__Test.php` | **2** | Низкий | Тестовые заглушки |
| C3 charts: samples/docs/spec | **~428** | Средний | Демо библиотеки, не приложение |
| C3: остальное (src, htdocs кроме samples) | **~126** | Средний | Нужны только `c3.min.js` + `c3.css` (~4 файла) |
| jBox / treegrid demos & tests | **~15** | Низкий | Демо и QUnit |
| `sr/old/` | **24** | Низкий | Старые JPG, ссылок нет |
| `thumbs/cache/` | **554** | **Высокий для git** | Кэш превью — не исходники |
| `actions/*` без входа из `user.php` | **~7** | Средний | См. §5 |
| `uni_table_*.php!` | **2** | Низкий | Боевых файлов без `!` в git нет |
| Дубликаты `modal-pict*.jpg` | **3** | Низкий | Используется `modal-pict.jpg` без `!` |

**Ориентир по «жиру» репозитория:** до **~1000+ файлов** можно рассмотреть к удалению из git или переносу в `.gitignore` без потери функционала EM (после ручной проверки stat-страниц и C3).

---

## 2. Методология

1. `git ls-files sites/em` — полный список в репозитории (не весь диск: `pbplans/*`, `oferta/` и т.д. в gitignore).
2. Поиск подозрительных имён: `!`, `!!`, цифровые суффиксы, `test`, `demo`, `samples`, `old`.
3. Проверка связей: `rg` по `sites/em` и `core` — `include`, `ctr=`, `href`, `src`, `actions/`.
4. Сверка с документацией: `sites/em/.doc/ctr_legacy.md` (правило про `!` в контроллерах).

**Ограничение:** статически не видны динамические `include($var)` и внешние закладки; такие файлы помечены «проверить вручную».

---

## 3. Файлы с `!` в имени (полный список в git)

Роутер (`core/classes/router.php`) подключает только `fw/controllers/ctr__{name}.php` **без** `!` в имени.  
Шаблоны подключаются через `$this->tpl()` по каноническому пути.

| Путь | Тип | Ссылки в коде | Вердикт |
|------|-----|---------------|---------|
| `captcha/.htaccess!` | конфиг | нет | резерв, удалить после сравнения с рабочим `.htaccess` |
| `sahmatka/actions/objects_index.php!` | action | нет | резерв; боевой — `objects_index.php` |
| `sahmatka/fw/controllers/!ctr__rentobjects.php` | контроллер | нет | черновик |
| `sahmatka/fw/controllers/!ctr__stat_econom.php` | контроллер | нет | черновик |
| `sahmatka/fw/controllers/ctr__agfiles.php!` | контроллер | нет | резерв |
| `sahmatka/fw/controllers/ctr__rentobjects.php!!!` | контроллер | нет | резерв (тройной `!`) |
| `sahmatka/fw/controllers/ctr__zapisx2.php!-до бага с по` | контроллер | нет | снимок до бага |
| `sahmatka/fw/templates/ajax_wiget/wiget!.php` | шаблон | нет | черновик |
| `sahmatka/fw/templates/apartments/form!_broni_ag.php` | шаблон | нет | черновик; боевой — `form_broni_ag.php` |
| `sahmatka/fw/templates/core/edit_form_panel.php!` | шаблон | нет | резерв |
| `sahmatka/images/!!modal-pict.jpg` | картинка | нет | дубликат |
| `sahmatka/images/!modal-pict.jpg` | картинка | нет | дубликат |
| `sahmatka/images/modal-pict!!.jpg` | картинка | нет | дубликат |
| `sahmatka/template/default/css/admin.css!` | стиль | нет | резерв |
| `sahmatka/template/default/css/iframe.css!` | стиль | нет | резерв |
| `sahmatka/template/default/images/add.svg!` | иконка | нет | резерв |
| `sahmatka/template/default/images/auth-bg.jpg!` | картинка | нет | резерв |
| `sahmatka/template/default/images/excel.svg!` | иконка | нет | резерв |
| `sahmatka/template/default/images/pdf.svg!` | иконка | нет | резерв |
| `sahmatka/template/default/images/pdf.svg!!` | иконка | нет | резерв |
| `sahmatka/.../pdfmake-unicode.js!` | JS | нет | резерв |
| `sahmatka/uni_table_editor.php!` | скрипт | нет | единственная копия в git |
| `sahmatka/uni_table_import.php!` | скрипт | нет | единственная копия в git |

**Итог по `!`:** все 23 файла — **кандидаты на удаление** из git после бэкапа (не используются роутером и не импортируются).

---

## 4. Числовые и «странные» имена (без `!`)

| Путь | Ссылки | Вердикт |
|------|--------|---------|
| `fw/controllers/ctr__apartments.php11` | нет | удалить |
| `fw/controllers/ctr__objects.php111` | нет | удалить |
| `actions/objects_index.php0` | нет | удалить |
| `js/myfw_iframe.js1` | нет | удалить |
| `actions/docs - рез 12.03.2026.php` | нет | резерв с датой, удалить |

---

## 5. Мёртвый / неподключённый код

### 5.1 Тестовые страницы

| Файл | Назначение | Ссылки |
|------|------------|--------|
| `sahmatka/test.php` | проверка legacy-маршрутизации | **нет** в коде и .htaccess |
| `la/Controllers/Ctr__Test.php` | заглушка LA-фреймворка | **нет** маршрутов `ctr=la` |

### 5.2 Прототипы задачи 1 (уже реализован compred)

Папка `sites/em/.doc/tasks/1/inc/`, `pr.php`, `pr_edit.php` — **только внутренние require** между прототипами.  
Продакшен использует `fw/controllers/ctr__compred.php` и `fw/templates/compred/*`.  
**Вердикт:** можно удалить из git (документацию `doc.md`, `plan.md` оставить).

### 5.3 `actions/` без входа из `user.php`

Боевой роутинг legacy-кабинета — `user.php?action=...` → `include('actions/...')`.

**Не подключаются** (кандидаты на удаление):

- `stat_sale_new.php` — C3-график, нет `action=` в `user.php`
- `stat_saler.php` — то же
- `objects_index.php!`, `objects_index.php0` — резервы
- `agadmin_users_t.php` — нет include
- `sdan2.txt`, `todo.txt` — служебные заметки в репозитории
- `docs - рез 12.03.2026.php` — резерв

> `stat_sale2.php` — **используется** (`action=stat_sale2`). C3 в проде нужен минимум: `c3/c3.css`, `c3/c3.min.js`, `d3` (если подключается рядом).

### 5.4 `sahmatka/x.txt`

Заголовок CSV (`home_id;image;section_id;...`) — похоже на **образец импорта**, не подключается кодом. Кандидат на перенос в `.doc/` или удаление.

### 5.5 `tools/rotate_compass_images.php`

CLI-утилита (комментарий: `php sites/em/sahmatka/tools/rotate_compass_images.php`).  
Не веб-эндпоинт; **оставить** как dev-tool или вынести в `tools/` вне git.

### 5.6 `custom_appart/*` (3 файла)

Используются через `$custom_apparts` / `form_order_custom.php` — **не удалять**.

### 5.7 Контроллеры с нулевыми внешними ссылками (проверить меню)

Автопоиск дал мало ссылок для: `stat_lifecycle`, `stat_sales_velocity`, `uniajax`.  
Возможны пункты меню в `in_head.php` / `ctrind.php` с другим паттерном — **удалять только после проверки UI**.

---

## 6. Vendor / библиотеки с избыточным составом

### 6.1 `sahmatka/c3/` (~556 файлов в git)

**Используется приложением:**

- `actions/stat_sale2.php` → `c3/c3.css`, `c3/c3.min.js`
- Аналогично в `stat_sale_new.php`, `stat_saler.php` (сами actions не в меню)

**Не используется приложением:**

- `c3/htdocs/samples/*` — демо-страницы
- `c3/docs/*`, `c3/spec/*` — документация и тесты upstream
- `c3/src/*`, `c3/.github/*` — исходники для сборки

**Рекомендация:** оставить в git 2–4 файла (`c3.min.js`, `c3.css`, при необходимости `d3.min.js`), остальное удалить или заменить на npm/CDN.

### 6.2 `template/default/jBox-1.3.3/demo/`

Демо HTML/JS — не подключаются из `sahmatka`. Удалить из git.

### 6.3 `libs/treegrid/tests/`

QUnit-тесты treegrid — удалить из git.

### 6.4 `fancybox-3.0/`

Полный пакет (gulp, src) — проверить, что в шаблонах подключается только `dist`; исходники сборки — кандидат на удаление.

---

## 7. Кэш и генерируемые файлы в git (критично)

| Путь | Файлов | Проблема |
|------|--------|----------|
| `thumbs/cache/` | **554** | Кэш ресайза; должен генерироваться на сервере |
| `thumbs/index.php`, `resize_class.php` | 2 | **нужны** — точка входа |

**Рекомендация:** добавить `sites/em/thumbs/cache/*` в `.gitignore`, оставить пустой `.gitkeep`, удалить кэш из истории git отдельной задачей.

`pbplans/`, `pbplans_jpg/` — в git только `.gitkeep` (корректно).

---

## 8. Документация и бинарники вне кода

| Путь | Назначение | Действие |
|------|------------|----------|
| `.doc/card_appartament.md`, `ctr_legacy.md` | документация | оставить |
| `.doc/tasks/1/doc.md`, `plan.md` | задача 1 | оставить |
| `agent_docs/*.docx` | юр. документы | не код; рассмотреть вынос из git |
| `keysbase/arh.csv` | архив ключей? | **проверить чувствительность данных** |
| `config.js.php.example` | шаблон | оставить |

---

## 9. Опечатка в продакшен-пути

`sahmatka/incudes_/` (header, foother) — **используется** из `user.php`, `ctrind.php`, `index.php`.  
Не мёртвый код, но техдолг: переименование потребует массовой правки include.

---

## 10. План действий (приоритет)

### Фаза A — безопасно (резервные копии)

1. Удалить все 23 файла с `!` в имени.
2. Удалить `*.php11`, `*.php111`, `*.php0`, `*.js1`, `docs - рез 12.03.2026.php`.
3. Удалить `test.php`, `la/Controllers/Ctr__Test.php`.
4. Удалить прототипы `.doc/tasks/1/*.php` (кроме документации `.md`).
5. Удалить неиспользуемые `actions/stat_sale_new.php`, `stat_saler.php`, `todo.txt`, `sdan2.txt`.

### Фаза B — очистка репозитория

1. `.gitignore` + удаление `thumbs/cache/*` из git.
2. Урезать `sahmatka/c3/` до минимального dist.
3. Удалить `sr/old/`, jBox demo, treegrid tests.

### Фаза C — ручная проверка

1. Меню статистики: `stat_lifecycle`, `stat_sales_velocity`.
2. `agent_docs/`, `keysbase/arh.csv` на предмет секретов.
3. `uni_table_*.php!` — выяснить, нужен ли вообще uni_table редактор (боевых файлов нет).

---

## 11. Связь с задачей 1 (compred)

Файлы задачи 1 **не входят** в список мёртвого кода:

- `compred/`, `ctr__compred.php`, `fw/templates/compred/*`, `compred_*` helpers, migrations — **оставить**.
- Правки в `ctr__apartments.php`, `form_broni_*.php`, `iframe_router.php`, `in_head.php` — **боевой код**.

Удалять только прототипы в `.doc/tasks/1/inc/` и `pr*.php`.

---

*Детальное дерево каталогов и таблица путей — в [files.md](./files.md).*
