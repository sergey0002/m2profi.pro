# Задача 2: Дерево `sites/em` и подозрительные пути

**Источник:** `git ls-files sites/em` (2244 файла)  
**Дата:** 2026-06-21

Легенда:

| Метка | Значение |
|-------|----------|
| OK | штатный боевой код / ассеты |
| REVIEW | проверить вручную перед удалением |
| DEAD | не подключён, кандидат на удаление |
| BACKUP | резервная копия (`!`, дата, цифровой суффикс) |
| CACHE | генерируемый кэш, не место в git |
| VENDOR | сторонняя библиотека; часто избыточный состав |
| DOC | документация / прототип |

---

## 1. Корень `sites/em/`

```
sites/em/
├── .doc/                          DOC — внутренняя документация
├── agent_docs/                    REVIEW — docx, не код (8 файлов)
├── captcha/                       OK — капча форм
│   └── .htaccess!                 BACKUP — DEAD
├── compred/                       OK — ЧПУ задачи 1
│   └── .htaccess
├── config.js.php.example          OK — шаблон конфига
├── edit_rooms.php                 REVIEW — проверить вызовы
├── favicon/                       OK — иконки PWA (26)
├── keys/                          OK — .htaccess защита
├── keysbase/                      REVIEW — arh.csv (чувствительные данные?)
├── la/                            DEAD? — LA-фреймворк, 2 файла
│   ├── Controllers/Ctr__Test.php  DEAD
│   └── themes/default/fonts/...   REVIEW
├── restapi/                       OK — API точка входа
├── robots.txt                     OK
├── sahmatka/                      OK — основное legacy-приложение (1589)
├── svg2png/                       REVIEW — утилита конвертации (5)
├── thumbs/                        MIX — см. §3
├── wiget_*.css/js                 OK — виджеты каталога/аренды
└── *.png, site.webmanifest        OK — статика корня
```

---

## 2. `sites/em/sahmatka/` — основное дерево

```
sahmatka/
├── actions/                 39 файлов — legacy-страницы user.php
├── c3/                      556 — VENDOR (см. §4)
├── css/                     OK — глобальные стили
├── custom_appart/           OK — 1.svg, 2.svg, x.jpg (form_order_custom)
├── fancybox-3.0/            VENDOR — 21 файл
├── fonts/                   OK — 80
├── fw/
│   ├── controllers/         42 — 35 боевых + 7 BACKUP
│   └── templates/           54+ — шаблоны ctr__*
├── images/                  129 — ассеты + 3 BACKUP modal-pict
├── inc/                     OK — compred_config, compred_helpers
├── incudes_/                OK* — опечатка, но используется (header/foother)
├── js/                      13 — incl. myfw_iframe.js1 BACKUP
├── libs/                    88 — incl. treegrid/tests DEAD
├── migrations/              OK — compred SQL
├── pbplans/                 OK — только .gitkeep в git
├── pbplans_jpg/             OK — только .gitkeep в git
├── sr/
│   └── old/                 24 JPG — DEAD
├── template/default/        448 — тема + libs + jBox demo DEAD
├── tools/
│   └── rotate_compass_images.php  REVIEW — CLI dev-tool
├── test.php                 DEAD
├── x.txt                    DEAD — образец CSV
├── uni_table_editor.php!    BACKUP/DEAD
├── uni_table_import.php!    BACKUP/DEAD
├── user.php                 OK — главный роутер legacy
├── ctrind.php               OK
├── iframe_router.php        OK — compred public
├── ajax_router.php          OK
├── router.php               OK
├── avito_feedx.php          OK — фид
├── yandex_feedx.php         OK — фид
├── form_order_custom.php    OK
└── … (print_*, upload*, rent/, parking/ — в основном .gitkeep)
```

---

## 3. `sites/em/thumbs/` — кэш в git

```
thumbs/
├── index.php              OK — вход
├── resize_class.php       OK
├── .htaccess              OK
├── 2.txt                  REVIEW
└── cache/                 554 файла — CACHE, DEAD для git
```

**Рекомендация:** `cache/*` → `.gitignore`, не удалять на проде без бэкапа.

---

## 4. `sites/em/sahmatka/c3/` — разбор vendor

```
c3/
├── c3.css, c3.min.js      OK — используются stat_sale2.php
├── htdocs/samples/        ~100+ HTML — VENDOR DEAD
├── docs/                  ~200+ — VENDOR DEAD
├── spec/                  тесты upstream — VENDOR DEAD
├── src/                   исходники — VENDOR DEAD
├── extensions/            REVIEW
└── package.json, bower…   VENDOR DEAD
```

---

## 5. `sites/em/.doc/`

```
.doc/
├── card_appartament.md    DOC OK
├── ctr_legacy.md          DOC OK — правило про файлы с !
└── tasks/
    ├── 1/                 DOC OK + прототипы PHP → DEAD
    │   ├── doc.md, plan.md
    │   ├── pr.php, pr_edit.php              DEAD
    │   └── inc/*.php                        DEAD (8 файлов)
    └── 2/                 эта задача
        ├── audit.md
        └── files.md
```

---

## 6. Таблица: файлы с `!` в пути (все в git)

| # | Путь | Метка |
|---|------|-------|
| 1 | `sites/em/captcha/.htaccess!` | BACKUP |
| 2 | `sites/em/sahmatka/actions/objects_index.php!` | BACKUP |
| 3 | `sites/em/sahmatka/fw/controllers/!ctr__rentobjects.php` | BACKUP |
| 4 | `sites/em/sahmatka/fw/controllers/!ctr__stat_econom.php` | BACKUP |
| 5 | `sites/em/sahmatka/fw/controllers/ctr__agfiles.php!` | BACKUP |
| 6 | `sites/em/sahmatka/fw/controllers/ctr__rentobjects.php!!!` | BACKUP |
| 7 | `sites/em/sahmatka/fw/controllers/ctr__zapisx2.php!-до бага с по` | BACKUP |
| 8 | `sites/em/sahmatka/fw/templates/ajax_wiget/wiget!.php` | BACKUP |
| 9 | `sites/em/sahmatka/fw/templates/apartments/form!_broni_ag.php` | BACKUP |
| 10 | `sites/em/sahmatka/fw/templates/core/edit_form_panel.php!` | BACKUP |
| 11 | `sites/em/sahmatka/images/!!modal-pict.jpg` | BACKUP |
| 12 | `sites/em/sahmatka/images/!modal-pict.jpg` | BACKUP |
| 13 | `sites/em/sahmatka/images/modal-pict!!.jpg` | BACKUP |
| 14 | `sites/em/sahmatka/template/default/css/admin.css!` | BACKUP |
| 15 | `sites/em/sahmatka/template/default/css/iframe.css!` | BACKUP |
| 16 | `sites/em/sahmatka/template/default/images/add.svg!` | BACKUP |
| 17 | `sites/em/sahmatka/template/default/images/auth-bg.jpg!` | BACKUP |
| 18 | `sites/em/sahmatka/template/default/images/excel.svg!` | BACKUP |
| 19 | `sites/em/sahmatka/template/default/images/pdf.svg!` | BACKUP |
| 20 | `sites/em/sahmatka/template/default/images/pdf.svg!!` | BACKUP |
| 21 | `sites/em/sahmatka/template/default/libs/ultimate-export/libs/pdfmake/pdfmake-unicode.js!` | BACKUP |
| 22 | `sites/em/sahmatka/uni_table_editor.php!` | BACKUP |
| 23 | `sites/em/sahmatka/uni_table_import.php!` | BACKUP |

---

## 7. Таблица: прочие подозрительные пути

| Путь | Метка | Почему |
|------|-------|--------|
| `sahmatka/test.php` | DEAD | тест маршрутизации, нет ссылок |
| `la/Controllers/Ctr__Test.php` | DEAD | заглушка LA |
| `sahmatka/x.txt` | DEAD | заголовок CSV, не импортируется |
| `sahmatka/fw/controllers/ctr__apartments.php11` | BACKUP | нет ссылок |
| `sahmatka/fw/controllers/ctr__objects.php111` | BACKUP | нет ссылок |
| `sahmatka/actions/objects_index.php0` | BACKUP | нет ссылок |
| `sahmatka/js/myfw_iframe.js1` | BACKUP | нет ссылок |
| `sahmatka/actions/docs - рез 12.03.2026.php` | BACKUP | резерв с датой |
| `sahmatka/actions/stat_sale_new.php` | DEAD | C3-страница, нет в user.php |
| `sahmatka/actions/stat_saler.php` | DEAD | то же |
| `sahmatka/actions/agadmin_users_t.php` | DEAD | нет include |
| `sahmatka/actions/todo.txt` | DEAD | заметка |
| `sahmatka/actions/sdan2.txt` | DEAD | дубликат sdan.txt? |
| `sahmatka/sr/old/*.jpg` (24) | DEAD | нет ссылок |
| `thumbs/cache/*` (554) | CACHE | генерируемые превью |
| `.doc/tasks/1/inc/*.php` | DEAD | прототип до compred |
| `.doc/tasks/1/pr.php`, `pr_edit.php` | DEAD | прототип |
| `sahmatka/c3/htdocs/samples/*` | VENDOR DEAD | демо C3 |
| `sahmatka/c3/docs/*`, `spec/*` | VENDOR DEAD | upstream |
| `template/default/jBox-1.3.3/demo/*` | VENDOR DEAD | демо |
| `libs/treegrid/tests/*` | VENDOR DEAD | QUnit |
| `agent_docs/*.docx` | REVIEW | не исходники |
| `keysbase/arh.csv` | REVIEW | возможны персональные данные |

---

## 8. Таблица: боевые файлы compred (задача 1) — НЕ удалять

| Путь | Метка |
|------|-------|
| `compred/.htaccess` | OK |
| `sahmatka/fw/controllers/ctr__compred.php` | OK |
| `sahmatka/fw/controllers/ctr__apartments.php` | OK (есть compred-блок) |
| `sahmatka/fw/templates/apartments/compred_block.php` | OK |
| `sahmatka/fw/templates/apartments/form_broni_ag.php` | OK |
| `sahmatka/fw/templates/apartments/form_broni_pub.php` | OK |
| `sahmatka/fw/templates/compred/*` | OK |
| `sahmatka/inc/compred_config.php` | OK |
| `sahmatka/inc/compred_helpers.php` | OK |
| `sahmatka/migrations/001_compred.sql` | OK |
| `sahmatka/migrations/002_compred_intro.sql` | OK |
| `sahmatka/template/default/css/compred.css` | OK |
| `sahmatka/template/default/js/compred.js` | OK |
| `sahmatka/template/default/in_head.php` | OK (меню) |
| `sahmatka/iframe_router.php` | OK (public meta) |

---

## 9. Сводка по верхнему уровню (файлов в git)

| Каталог | Файлов | Комментарий |
|---------|--------|-------------|
| `sahmatka/` | 1589 | ядро EM |
| `thumbs/` | 558 | 99% — cache |
| `favicon/` | 26 | OK |
| `.doc/` | 12 | документация + прототипы |
| `captcha/` | 11 | + 1 BACKUP |
| `agent_docs/` | 8 | docx |
| `svg2png/` | 5 | утилита |
| `restapi/` | 2 | OK |
| `la/` | 2 | тест |
| корневые wiget_*, png | ~15 | OK |

---

## 10. Что не вошло в git (на диске, но не в репозитории)

По `.gitignore` (для контекста, не входят в аудит git):

- `sites/em/oferta/`
- `sites/em/sahmatka/pbplans/*` (кроме `.gitkeep`)
- `sites/em/sahmatka/pbplans_jpg/*`
- `sites/em/sahmatka/pbplans -до мебели/`
- `config.php`, uploads, vendor и др.

---

*Полный анализ и план очистки — в [audit.md](./audit.md).*
